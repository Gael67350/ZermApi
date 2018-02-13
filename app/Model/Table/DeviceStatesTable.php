<?php

namespace App\Model\Table;


use Cake\ORM\Table;
use Cake\Validation\Validator;

class DeviceStatesTable extends Table {

    public function initialize(array $config) {
        $this->setEntityClass('App\Model\Entity\DeviceState');
        $this->addBehavior('Timestamp');

        $this->belongsTo('DeviceFeatures');
    }

    public function validationDefault(Validator $validator) {
        $validator
            ->numeric('id', 'An ID must be an integer')
            ->allowEmpty('id', 'create')
            ->notEmpty('value', 'A device state value must be specified')
            ->numeric('value', 'A device state value must be numeric')
            ->dateTime('created', 'Y-m-d H:i:s', 'This date is invalid');

        return $validator;
    }

}