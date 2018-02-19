<?php

namespace App\Model\Entity;


use Cake\ORM\Entity;

class Device extends Entity {

    private $created;
    private $modified;

    protected $_accessible = [
        'name' => true
    ];

    protected $_hidden = [
        'security_token',
        'macAddress',
        'jwt_expire_at'
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

    protected function _getModifiedFormat() {
        return $this->modified->format('Y-m-d H:i:s');
    }

    protected function _getJwtExpireAtFormat() {
        return $this->created->format('Y-m-d H:i:s');
    }

}