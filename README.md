#TableGateway to Doctrine Tutorial

##Setup Infrastructure
###Create a MySQL database
- Call the database `registrator`
- Restore from `/before/registrator.sql`

###Setup working directory structure
- Make a directory `/working`
- Copy everything in `/before/*` to `/working`

##Install and Configure Doctrine
###Install Doctrine module
- Add to the `/working/composer.json` file under `require {}`
```
"doctrine/doctrine-orm-module":"*"
```
- Run composer update
```
php composer.phar update
```

###Configure Doctrine
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
  - Add a new key 'doctrine' => [ ]
  - Add a sub-key 'driver' => [ ]
  - Add a sub-key 'application_annotation_driver' => [ ]
```
    'application_annotation_driver' => [
        'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
        'cache' => 'array',
        'paths' => [__DIR__ . '/../src/Application/Entity'],
    ],
```
  - Add a sub-key 'orm_default' => [ ]
```
    'orm_default' => [
        'drivers' => [
            // register `application_annotation_driver` for any entity 
            // under namespace `Application\Entity`
            'Application\Entity' => 'application_annotation_driver'
        ]
    ],
```
###Test the configuration
- Use the doctrine command line tool
- If you see the help screen from the command line tool, Doctrine is installed and the configuration is working
- Fix any errors before proceeding
```
/working/vendor/bin/doctrine-module
/working/vendor/bin/doctrine-module  dbal:run-sql 'select * from event'
```

##Create Entities
###Generate entities
- Use the command line tool
```
