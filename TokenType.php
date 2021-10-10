<?php

abstract class TokenType{
    const LBRACKET = 0;
    const RBRACKET = 1;
    const LSQUAREBRACKET = 2;
    const RSQUAREBRACKET = 3;
    const STRING= 4;
    const EQUAL = 5;
    const LESS = 6;
    const GREATER = 7;
    const IF = 8;
    const ELSE = 9;
    const ID = 10;
    const INT = 11;
    const EOF = 12;
    const OTHER = 13;
}

?>