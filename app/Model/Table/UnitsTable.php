<?php

namespace App\Model\Table;


use Cake\ORM\Table;
use Cake\Validation\Validator;

class UnitsTable extends Table {

    public function initialize(array $config) {
        $this->setEntityClass('App\Model\Entity\Unit');

        $this->hasMany('DeviceFeatures');
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
            ->maxLength('symbol', 10, 'A symbol must have a maximum length of 10 characters');

        return $validator;
    }

}