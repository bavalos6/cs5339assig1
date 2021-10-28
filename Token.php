<?php

class Token{

    public $type;
    public $value;

    function __construct($theType, $theValue){
        $this->type =  $theType;
        $this->value = $theValue;
    }
}

?>