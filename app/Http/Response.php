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

namespace App\Http;


class Response extends \Slim\Http\Response {

    private $data;
    private $message;

    public function getData() {
        return $this->data;
    }

    public function getMessage() {
        return $this->message;
    }

    public function withData($data = null) {
        if (!empty($data))
            $this->data = $data;
        return $this;
    }

    public function withMessage($message = null) {
        if (!empty($message))
            $this->message = $message;
        return $this;
    }

}