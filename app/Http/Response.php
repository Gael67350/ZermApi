<?php

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