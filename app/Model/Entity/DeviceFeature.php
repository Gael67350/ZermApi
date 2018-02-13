<?php

namespace App\Model\Entity;


use Cake\ORM\Entity;

class DeviceFeature extends Entity {

    protected $_accessible = [
        'name' => true,
        'sensor' => true,
        'minValue' => true,
        'maxValue' => true
    ];

    protected function _setName($name) {
        return strtolower($name);
    }

    protected function _getName($name) {
        return ucfirst($name);
    }

}