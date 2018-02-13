<?php

namespace App\Model\Entity;


use Cake\ORM\Entity;

class DeviceState extends Entity {

    private $created;

    protected $_accessible = [
        'value' => true
    ];

    protected function _setName($name) {
        return strtolower($name);
    }

    protected function _getName($name) {
        return ucfirst($name);
    }

    protected function _getCreatedFormat() {
        return $this->created->format('Y-m-d H:i:s');
    }

}