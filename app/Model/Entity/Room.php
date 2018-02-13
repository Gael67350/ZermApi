<?php

namespace App\Model\Entity;


use Cake\ORM\Entity;

class Room extends Entity {

    protected $_accessible = [
        'name' => true
    ];

    protected function _setName($name) {
        return strtolower($name);
    }

    protected function _getName($name) {
        return ucfirst($name);
    }

}