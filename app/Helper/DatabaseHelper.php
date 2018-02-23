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

namespace App\Helper;


use Cake\ORM\TableRegistry;

class DatabaseHelper {

    public static function homeTableRegistry() {
        return TableRegistry::get('Homes', ['className' => 'App\Model\Table\HomesTable']);
    }

    public static function roomTableRegistry() {
        return TableRegistry::get('Rooms', ['className' => 'App\Model\Table\RoomsTable']);
    }

    public static function deviceTableRegistry() {
        return TableRegistry::get('Devices', ['className' => 'App\Model\Table\DevicesTable']);
    }

    public static function deviceFeatureTableRegistry() {
        return TableRegistry::get('DeviceFeatures', ['className' => 'App\Model\Table\DeviceFeaturesTable']);
    }

    public static function deviceStateTableRegistry() {
        return TableRegistry::get('DeviceStates', ['className' => 'App\Model\Table\DeviceStatesTable']);
    }

    public static function unitTableRegistry() {
        return TableRegistry::get('Units', ['className' => 'App\Model\Table\UnitsTable']);
    }

}