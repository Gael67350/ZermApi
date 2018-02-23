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


class TokenHelper {

    private static $_salt = "54db4d28a46e27da7e16b66e97e659dfe21f11942c1c8411233e5b8975f8440870ae59d958165d5d4f2406e19cac2660b6dfcc26554c1441dd1a46e634461f63";

    public function __construct() {
        // Empty constructor
    }

    public function generate() {
        $timestampKey = strtolower(substr(sha1(md5(time())), 3, 10));
        $randomKey = substr(strtolower(md5(substr(self::$_salt, rand(1, 128), 1))), 1, 10);

        $token = $timestampKey . $randomKey;
        $token = substr(sha1($token), rand(0, 7), 32);

        return $token;
    }

}