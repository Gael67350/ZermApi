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