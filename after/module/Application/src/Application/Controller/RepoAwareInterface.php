<?php
namespace Application\Controller;

use Application\Repository\EventRepo;
use Application\Repository\AttendeeRepo;
use Application\Repository\RegistrationRepo;

interface RepoAwareInterface
{
    public function setEventRepo(EventRepo $repo);
    public function setAttendeeRepo(AttendeeRepo $repo);
    public function setRegistrationRepo(RegistrationRepo $repo);
}