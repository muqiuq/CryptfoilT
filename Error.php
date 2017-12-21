<?php

class CftError {
    public $errorMessage = "";

    public function __construct($msg)
    {
        $this->errorMessage = $msg;
    }

}