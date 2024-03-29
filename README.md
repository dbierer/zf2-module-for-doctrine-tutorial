# TableGateway to Doctrine Tutorial

## Setup Infrastructure
###  Create a MySQL database
- Call the database `registrator`
- Restore from `/before/registrator.sql`

###  Setup working directory structure
- Make a directory `/working`
- Copy everything in `/before/*` to `/working`

###  Create a directory which will hold entity classes
- Make a directory `/working/module/Application/src/Application/Entity`

## Install and Configure Doctrine
###  Install Doctrine module
- Add to the `/working/composer.json` file under `require {}`
```
"doctrine/doctrine-orm-module":"*"
```
- Run composer update
```
php composer.phar update
```

###  Configure Doctrine
- Update `/working/config/autoload/db.local.php`
  - Add a new key 'doctrine' => [ ]
  - Add a sub-key 'connection' => [ ]
  - Add a sub-key 'orm_default' => [ ]
  - Add the following information under 'orm_default'
```
    'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
    'params' => [
        'driver'         => 'pdo_mysql',
        'host'           => 'localhost',
        'dbname'         => 'registrator',
        'user'           => 'test',       // or any appropriate user
        'password'       => 'password',   // or any appropriate password
    ]
```
- Update `/working/config/application.config.php`
  - Under 'modules' key add:
```
    'DoctrineModule',
    'DoctrineORMModule',
```
- Update `/working/module/Application/config/module.config.php`
  - Add a new key `doctrine` => [ ]
  - Add a sub-key `driver` => [ ]
  - Add a sub-key `application_annotation_driver` => [ ]
```
    'application_annotation_driver' => [
        'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
        'cache' => 'array',
        'paths' => [__DIR__ . '/../src/Application/Entity'],
    ],
```
  - Add a sub-key `orm_default` => [ ]
```
    'orm_default' => [
        'drivers' => [
            // register `application_annotation_driver` for any entity 
            // under namespace `Application\Entity`
            'Application\Entity' => 'application_annotation_driver'
        ]
    ],
```
### Test the configuration
- Use the doctrine command line tool
- If you see the help screen from the command line tool, Doctrine is installed and the configuration is working
- Fix any errors before proceeding
```
/working/vendor/bin/doctrine-module
/working/vendor/bin/doctrine-module  dbal:run-sql 'select * from event'
```

## Generate Entities
- Make a temp directory `/working/temp`
- Review the help information for mapping conversions
```
vendor/bin/doctrine-module orm:convert-mapping --help
```
- Use the command line tool to convert mapping from the database to annotation format
```
vendor/bin/doctrine-module orm:convert-mapping --from-database annotation ./temp
```
- Add `namespace Application\Entity;` at the top of the newly created entity mapping files
- Copy the revised files to `/working/module/Application/src/Application/Entity`.  The reason why you need to do this is because the doctrine configuration for the `Application` module indicates the entities are in the `Application\Entity` namespace (which matches the `/working/module/Application/src/Application/Entity` folder.
- Use the command line tool to generate getters and setters
```
vendor/bin/doctrine-module orm:generate-entities --generate-methods=GENERATE-METHODS --generate-annotations=GENERATE-ANNOTATIONS ./temp
```
- Copy the files created under `/working/temp/Application/Entity` to `/working/module/Application/src/Application/Entity`.
- Test the entity by issuing a "DQL" (Doctrine Query Language) command
```
vendor/bin/doctrine-module orm:run-dql 'select e from Application\Entity\Event e'
```
- Fix any errors before proceeding

## Define Repositories
### Create repository classes for each entity
- Create a new folder `/working/module/Application/src/Application/Repository`
- Create a repository class for each entity, which extends `Doctrine\ORM\EntityRepository`.  You do not need to define any methods for these classes at this point.
### Define repositories as services
- Create service manager factories in `/working/module/Application/Module.php` which build instances of the repositories using the entity manager.
```
use Application\Repository;
public function getServiceConfig()
{
    return [
        'factories' => [
            'application-repo-event' => function ($sm) {
                $em = $sm->get('doctrine.entitymanager.orm_default');
                return new Repository\EventRepo($em, $em->getClassMetadata('Application\Entity\Event'));
            },
            'application-event-registration' => function ($sm) {
                $em = $sm->get('doctrine.entitymanager.orm_default');
                return new Repository\RegistrationRepo($em, $em->getClassMetadata('Application\Entity\Registration'));
            },
            'application-repo-attendee' => function ($sm) {
                $em = $sm->get('doctrine.entitymanager.orm_default');
                return new Repository\AttendeeRepo($em, $em->getClassMetadata('Application\Entity\Attendee'));
            },
        ],
    ];
}
```
### Test the repository class
- Rewrite `Application\Controller\AdminController::indexAction()` to use the Event repository class to find all events
```
$events = $this->getServiceLocator()->get('application-repo-event')->findAll();
```
- Rewrite the corresponding view template to use entities
```
// view/application/admin/index.phtml
<a href="/admin/<?php echo $event->getId() ?>"><?php echo $event->getName() ?></a><br />
```
- Rewrite `Application\Controller\SignupController::indexAction()` and `view/signup/index.phtml` to use the Event repository class to find all events
- Run the built-in PHP webserver to test:
```
cd /path/to/working
php -S localhost:8080 -t public
```
- From the browser, access `localhost:8080`
- Click on `Go To Admin Area`
- You should see a list of events.  Do not attempt to list events as relationships have not yet been defined!
- Fix any errors before proceeding


## Define Relationships
### Define 1:N between Event and Registration
NOTE: doctrine distinguishes between the "owning" side (i.e parent), and "inverse" (i.e. child).  In this case we are configuring the "owning" side.
- Make the following changes in the `Application\Entity\Event` class
- Add:
```
/**
 * @ORM\OneToMany(targetEntity="Application\Entity\Registration", mappedBy="event")
 */
private $registrations = array();
```
- You will also need to add a constructor which defines the new property as a doctrine `ArrayCollection`
```    
use Doctrine\Common\Collections\ArrayCollection;
public function __construct()
{
    $this->registrations = new ArrayCollection();
}
```
- And, of course, add the appropriate getters and setters.  Note that the setter adds an item to the array.
```   
public function getRegistrations()
{
    return $this->registrations;
}

/**
 * @param Application\Entity\Registration $registration
 */
public function setRegistrations($registration)
{
    $this->registrations[] = $registration;
}
```

### Define N:1 between Registration and Event
You are now ready the "inverse" side of the relationship
- Make the following changes in the `Application\Entity\Registration` class
- Change:
```
@ORM\Column(name="event_id", type="integer", precision=0, scale=0, nullable=false, unique=false)
```
   to:
```
@ORM\ManyToOne(targetEntity="Application\Entity\Event", inversedBy="registrations")
```
- Change:
```
private $eventId;
```
   to:
```
private $event;
```
NOTE: a suffix of "_id" is significant to doctrine, and indicates a column which defines a foreign key relationship to another table. Thus, if doctrine sees a property `$event`, which is defined as a relationship, it will look for a column `event_id`.

### Define 1:N between Registration and Attendee
- First we configure the "owning" side of the relationship
- Make the following changes in the `Application\Entity\Registration` class
- Add:
```
/**
 * @ORM\OneToMany(targetEntity="Application\Entity\Attendee", indexBy="id", mappedBy="registration")
 */
private $attendees = array();   
```
- You will also need to add a constructor which defines the new property as a doctrine `ArrayCollection`
```    
use Doctrine\Common\Collections\ArrayCollection;
public function __construct()
{
    $this->attendees = new ArrayCollection();
}
```
- And, of course, add the appropriate getters and setters.  Note that the setter adds an item to the array.
```   
public function getAttendees() {
    return $this->attendees;
}

/**
 * @param Application\Entity\Attendee $attendee
 */
public function setAttendees(Attendee $attendee) {
    $this->attendees[] = $attendee;
}
```

### Define N:1 between Attendee and Registration
- Next we configure the "inverse" side of the relationship
- Make the following changes in the `Application\Entity\Attendee` class
- Change:
```
@ORM\Column(name="registration_id", type="integer", precision=0, scale=0, nullable=false, unique=false)
```
   to:
```
@ORM\ManyToOne(targetEntity="Application\Entity\Registration", inversedBy="attendees")
```
- Change:
```
private $registrationId;
```
   to:
```
private $registration;
```
  
### Update the schema
- At this point the schema will be out of sync with the entity definitions.  You can use the command line tool to view the validation status as follows:
```
vendor/bin/doctrine-module orm:validate-schema
```
- This would be a very good point to backup the database!  If the schema update process fails, all you need to do is to restore, adjust, and try again.
- You can now use the doctrine ORM schema tool to view what database changes are proposed
```
vendor/bin/doctrine-module orm:schema-tool:update --dump-sql
```
- To implement these changes, change the flag to `--force`
```
vendor/bin/doctrine-module orm:schema-tool:update --force
```
- Have a look at the database tables using your favorite tool and review the changes

### Test the relationships
- Rewrite `Application\Controller\AdminController::listRegistrations()` to lookup the event based on the $eventId parameter, and pass the Event entity to the view.
```
protected function listRegistrations($eventId)
{
    $event = $this->getServiceLocator()->get('application-repo-event')->find($eventId);
    $vm = new ViewModel(array('event' => $event));
    $vm->setTemplate('application/admin/list.phtml');
    return $vm;
}
```
- Rewrite the corresponding view template to use the Event entity to lookup registrations, and from registrations, attendees.  Note that the registration time will be returned as a `DateTime` instance.  This means you will need to use the `format()` method to produce output.
```
// view/application/admin/list.phtml
<?php if (!isset($this->event)) : ?>
Sorry! This event is not found.
<?php else : ?>
<table class="table table-striped">
    <?php foreach ($this->event->getRegistrations() as $reg): ?>
    <tr>
        <td><?= $reg->getId() ?></td>
        <td><?= $reg->getFirstName() ?></td>
        <td><?= $reg->getLastName() ?></td>
        <td><?= count($reg->getAttendees()) . ' tickets' ?></td>
        <td>Tickets:
            <table class="table table-striped">
                <?php foreach ($reg->getAttendees() as $attendee): ?>
                <tr>
                    <td><?= $attendee->getNameOnTicket() ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </td>
        <td><?= $reg->getRegistrationTime()->format('d M Y') ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<hr />
<?php endif; ?>
```
- Run the built-in PHP webserver to test:
```
cd /path/to/working
php -S localhost:8080 -t public
```
- From the browser, access `localhost:8080`
- Click on `Go To Admin Area`
- Click on one of the events listed
- You should see information on registrations and attendees for this event
- Fix any errors before proceeding

## Collect and "Persist" Information
So far doctrine has only been used for information retrieval (i.e. SELECT and SELECT ... FROM ... JOIN ...).  Now it is time to refactor the sign-up process and to use doctrine for storage.
### Define a repo method to save registration data
- Add a new method `save()` to `Application\Repository\RegistrationRepo`
- Accept an instance of `Application\Entity\Event`, as well as first and last name strings
- Be sure to assign a DateTime instance to the `$registrationTime` property
- Use the entity manager `persist()` and `flush()` methods to save the data
- Return the newly saved `Registration` instance
```
<?php
namespace Application\Repository;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Event;
use Application\Entity\Registration;

class RegistrationRepo extends EntityRepository
{
    public function save(Event $event, $first, $last)
    {
        $reg = new Registration();
        $reg->setEvent($event);
        $reg->setFirstName($first);
        $reg->setLastName($last);
        $reg->setRegistrationTime(new DateTime('now'));
        $this->getEntityManager()->persist($reg);
        $this->getEntityManager()->flush();
        return $reg;
    }
} 
```
### Define a repo method to save attendee data
- Add a new method `save()` to `Application\Repository\AttendeeRepo`
- Accept an instance of `Application\Entity\Registration`, as well as a name string
- Return the newly saved `Attendee` instance
```
<?php
namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Registration;
use Application\Entity\Attendee;

class AttendeeRepo extends EntityRepository
{
    public function save(Registration $reg, $nameOnTicket)
    {
        $attendee = new Attendee();
        $attendee->setNameOnTicket($nameOnTicket);
        $attendee->setRegistration($reg);
        $this->getEntityManager()->persist($attendee);
        $this->getEntityManager()->flush();
        return $attendee;
    }
} 
```
### Refactor the event signup process
- In `Application\Controller\SignupController::eventSignup()` to use the event repository to lookup the event based on the `$eventId` property
```
protected function eventSignup($eventId)
{
    /** @var \Application\Model\EventTable $eventTable */
    //$eventTable = $this->serviceLocator->get('EventTable');
    //$event = $eventTable->findById($eventId);
    $event = $this->getServiceLocator()->get('application-repo-event')->find($eventId);

    if (!$event) {
        // better 404 experience?
        return $this->notFoundAction();
    }

    if ($this->request->isPost()) {
        $this->processForm($this->params()->fromPost(), $event);
        return $this->redirect()->toUrl('/thank-you');
    }

    $vm = new ViewModel(array('event' => $event));
    $vm->setTemplate('application/signup/form.phtml');
    return $vm;
}
```
- Rewrite `application/signup/index.phtml` to use the event entity.  You can use the code in `application/admin/index.phtml` as a guide.
```
<?php foreach ($events as $event): ?>
    <a href="/signup/<?php echo $event->getId() ?>"><?php echo $event->getName() ?></a><br />
<?php endforeach; ?>
```
- Rewrite `application/signup/form.phtml` to use the event entity
```
// change the form action
<form class="form-horizontal" action="/signup/<?= $event->getId() ?>" method="POST">
// change how the event name is displayed
<span class=""><?= $event->getName() ?></span>
```
- Rewrite `Application\Controller\SignupController::processForm()` to store entity information
- Use the `save()` method of the registration repo to save the initial registration data
- In the loop for ticket names, use the `save()` method of the attendee repo to save attendee info
- Add each `Attendee` entity to the list of attendees for the `Registration` instance
- When the loop has complete, use the entity manager to persist and flush the updated `Registration` instance
```
protected function processForm(array $formData, $event)
{
    $formData = $this->sanitizeData($formData);
    $regRepo = $this->getServiceLocator()->get('application-repo-registration');
    $reg = $regRepo->save($event, $formData['first_name'], $formData['last_name']);

    $ticketData = $formData['ticket'];
    $attendeeRepo = $this->getServiceLocator()->get('application-repo-attendee');
    foreach ($ticketData as $nameOnTicket) {
        $attendee = $attendeeRepo->save($reg, $nameOnTicket);
        $reg->setAttendees($attendee);
    }
    $em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    $em->persist($reg);
    $em->flush();
    
    return true;
}
```
