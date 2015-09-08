<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Entity\Attendee;

/**
 * @ORM\Entity("Application\Entity\Registration")
 * @ORM\Table("registration")
 */
class Registration
{
    /**
     * @ORM\Column(type="integer", length=11)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=255, name="first_name")
     */
    protected $firstName;
    
    /**
     * @ORM\Column(type="string", length=255, name="last_name")
     */
    protected $lastName;
  
    /**
     * @ORM\Column(type="datetime", name="registration_time")
     */
    protected $registrationTime;
    
    /**
     * @ORM\OneToMany(targetEntity="Application\Entity\Attendee", indexBy="id", mappedBy="registration")
     */
    protected $attendees = array();
    
    // can be removed after manually copying data to eventLink
    // UPDATE registration SET eventLink_id = event_id
    /**
     * @ORM\Column(type="integer", length=11)
     */
    protected $event_id;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Event", inversedBy="registrations")
     */
    protected $eventLink;
    
    public function __construct()
    {
        $this->attendees = new ArrayCollection();
    }
	/**
	 * @return the $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return the $firstName
	 */
	public function getFirstName() {
		return $this->firstName;
	}

	/**
	 * @return the $lastName
	 */
	public function getLastName() {
		return $this->lastName;
	}

	/**
	 * @return the $registrationTime
	 */
	public function getRegistrationTime() {
		return $this->registrationTime;
	}

	/**
	 * @return the $attendees
	 */
	public function getAttendees() {
		return $this->attendees;
	}

	/**
	 * @return the $event ID (NOT the entity)
	 */
	public function getEvent() {
		return $this->event_id;
	}

	/**
	 * @param field_type $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @param field_type $firstName
	 */
	public function setFirstName($firstName) {
		$this->firstName = $firstName;
	}

	/**
	 * @param field_type $lastName
	 */
	public function setLastName($lastName) {
		$this->lastName = $lastName;
	}

	/**
	 * @param field_type $registrationTime
	 */
	public function setRegistrationTime($registrationTime = NULL) {
	    if ($registrationTime == NULL) {
	        $registrationTime = new \DateTime('now');
	    }
		$this->registrationTime = $registrationTime;
	}

	/**
	 * @param multitype: $attendees
	 */
	public function setAttendees(Attendee $attendee) {
		$this->attendees[] = $attendee;
	}

	/**
	 * @param int $event 
	 */
	public function setEvent($event) {
		$this->event_id = $event;
	}

	/**
     * @return the Application\Entity\Event $eventLink
     */
    public function getEventLink()
    {
        return $this->eventLink;
    }

    /**
     * @param Application\Entity\Event $eventLink
     */
    public function setEventLink($eventLink)
    {
        $this->eventLink = $eventLink;
    }


    
        
}
