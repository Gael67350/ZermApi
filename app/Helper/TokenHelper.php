<?php

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