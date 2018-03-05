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
            ->numeric('logical_id', 'A logical ID must be an integer between 1 and 255')
            ->range('logical_id', [1, 255], 'A logical ID must be an integer between 1 and 255')
            ->notEmpty('logical_id', 'A logical ID must be filled')
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