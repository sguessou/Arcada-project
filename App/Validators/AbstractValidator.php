<?php
namespace App\Validators;

abstract class AbstractValidator{

    // contains array of error messages from the validator
    protected $messages = array();


    public function getMessages(){
        return $this->messages;
    }

}
