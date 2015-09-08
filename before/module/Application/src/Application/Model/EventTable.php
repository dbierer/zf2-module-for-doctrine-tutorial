<?php

namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;

class EventTable extends TableGateway
{
    const TABLE_NAME = 'event';

    public function findAll()
    {
        $rows = $this->select();
        return $rows->toArray();
    }

    public function findById($id)
    {
        $rows = $this->select(array('id' => $id));
        if (!$rows) {
            return false;
        }
        $row = $rows->current();
        return $row->getArrayCopy();
    }

}
