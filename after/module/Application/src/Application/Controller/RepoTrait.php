<?php
namespace Application\Controller;

use Application\Repository\EventRepo;
use Application\Repository\AttendeeRepo;
use Application\Repository\RegistrationRepo;

trait RepoTrait
{
    protected $eventRepo;
    protected $attendeeRepo;
    protected $registrationRepo;
    
    public function setEventRepo(EventRepo $repo) {
        $this->eventRepo = $repo;
    }
    public function setAttendeeRepo(AttendeeRepo $repo) {
        $this->attendeeRepo = $repo;
    }
    public function setRegistrationRepo(RegistrationRepo $repo) {
        $this->registrationRepo = $repo;
    }
}