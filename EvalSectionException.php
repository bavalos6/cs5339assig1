<?php
class EvalSectionException extends Exception
{
    public $error;

    function __construct($m)
    {
        $this->error = $m;
        echo PHP_EOL . "Parsing or execution Exception: " . $this->error . PHP_EOL . PHP_EOL;
    }
}
?>