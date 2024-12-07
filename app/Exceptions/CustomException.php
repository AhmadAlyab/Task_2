<?php

namespace App\Exceptions;

use Exception;

class CustomException extends Exception
{
    protected $message;
    protected $code;

    public function __construct($message ="error in route", $code= 400)
    {
        $this->message =$message;
        $this->code = $code;
        parent::__construct($message,$code);
    }

    public function render($request)
    {
        return response()->json([
            'error' => $this->message,
        ],$this->code);
    }
}

