<?php

class Token{

    public $type;
    public $value;

    public function _construct($theType, $theValue){
        $this->type =  $theType;
        $this->value = $theValue;
    }   

}

?>