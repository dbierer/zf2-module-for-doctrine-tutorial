<?php

namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;

class AttendeeTable extends TableGateway
{
    const TABLE_NAME = 'attendee';
    public function findAllForRegistration($registrationId, $withAttendees = false)
    {
        $rows = $this->select(array('registration_id' => $registrationId));

        if (!$rows) {
            return false;
        }

        return $rows->toArray();
    }

    public function persist($registrationId, $nameOnTicket)
    {
        $this->insert(array('registration_id' => $registrationId, 'name_on_ticket' => $nameOnTicket));
    }

}
