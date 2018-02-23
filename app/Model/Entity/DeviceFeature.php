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