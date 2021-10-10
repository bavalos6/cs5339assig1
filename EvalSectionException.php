<?php
    class EvalSectionException extends Exception{
        public function _construct($m){
            echo "Exception: ". $m .PHP_EOL;
        }
    }
?>