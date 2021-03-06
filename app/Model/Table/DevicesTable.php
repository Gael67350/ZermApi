<?php
/**
 *
 *  ZermThings : An API for an IOT manager system (https://www.zermthings.fr)
 *  Copyright (c) 2018 SCION Gael (https://www.gael67350.eu)
 *
 *  Licensed under The MIT License
 *  For full copyright and license information, please see the LICENSE.txt
 *  Redistributions of files must retain the above copyright notice.
 *
 * @copyright  Copyright (c) 2018 SCION Gael (https://www.gael67350.eu)
 * @link       https://api.zermthings.fr ZermThings Project
 * @since      1.0
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 *
 */

namespace App\Model\Table;


use BlakeGardner\MacAddress;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class DevicesTable extends Table {

    public function initialize(array $config) {
        $this->setEntityClass('App\Model\Entity\Device');
        $this->setPrimaryKey('uuid');
        $this->addBehavior('Timestamp');

        $this->belongsTo('Rooms');
        $this->hasMany('DeviceFeatures')->setForeignKey('device_uuid');
    }

    public function validationDefault(Validator $validator) {
        $validator
            ->uuid('uuid', 'UUID is invalid')
            ->notEmpty('uuid', 'An UUID must be filled')
            ->add('uuid', [
                'isUnique' => [
                    'rule' => 'validateUnique',
                    'provider' => 'table',
                    'message' => 'This UUID is already used'
                ]
            ])
            ->add('security_token', [
                'isToken' => [
                    'rule' => array('custom', '/[a-z0-9]{32}/'),
                    'message' => 'This token is invalid'
                ],
                'isUnique' => [
                    'rule' => 'validateUnique',
                    'provider' => 'table',
                    'message' => 'This token is already used'
                ]
            ])
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
            ->add('macAddress', [
                'isValidMacAddress' => [
                    'rule' => function ($value, $context) {
                        return MacAddress::validateMacAddress($value);
                    },
                    'message' => 'This MAC address is invalid'
                ]
            ])
            ->notEmpty('macAddress', 'A MAC address must be filled')
            ->dateTime('created', 'Y-m-d H:i:s', 'This date is invalid')
            ->dateTime('modified', 'Y-m-d H:i:s', 'This date is invalid')
            ->dateTime('jwt_expire_at', 'Y-m-d H:i:s', 'This date is invalid')
            ->requirePresence('modified', 'update', 'A modification date must be specified')
            ->notEmpty('modified', 'A modification date must be specified');

        return $validator;
    }

}