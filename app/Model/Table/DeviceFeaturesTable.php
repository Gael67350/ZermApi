<?php

namespace App\Model\Table;


use Cake\ORM\Table;
use Cake\Validation\Validator;

class DeviceFeaturesTable extends Table {

    public function initialize(array $config) {
        $this->setEntityClass('App\Model\Entity\DeviceFeature');

        $this->belongsTo('Devices')->setForeignKey('device_uuid');
        $this->belongsTo('Units');
        $this->hasMany('DeviceStates');
    }

    public function validationDefault(Validator $validator) {
        $validator
            ->numeric('id', 'An ID must be an integer')
            ->allowEmpty('id', 'create')
            ->alphaNumeric('name', 'A name must consist only of letters and numbers')
            ->lengthBetween('name', [4, 45], 'A name must be between 4 and 45 characters long')
            ->add('name', [
                'isUnique' => [
                    'rule' => 'validateUnique',
                    'provider' => 'table',
                    'message' => 'This name is already used'
                ]])
            ->requirePresence('name', 'create', 'A name must be filled')
            ->notEmpty('name', 'A name must be filled')
            ->boolean('sensor', 'The type of device feature must be a boolean value')
            ->numeric('minValue', 'The minimum value must be numeric')
            ->numeric('maxValue', 'The maximum value must be numeric');

        return $validator;
    }

}