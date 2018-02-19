<?php

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