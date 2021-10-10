<?php

class Tokenizer{
    private $e = array(); //char array containing input file characters
    private $i; //index of the current charcater
    public $currentChar; //the actual current character 

    //constructor
    public function _construct($s){
        $this->e = str_split($s);
        $this->i = 0;
    }

    public function nextToken(){
        //skip blanklike characters
        while(($this->i < count($this->e)) && (strpos(" \n\t\r", $this->e[$this->i]) >= 0)){    
            $this->i++;
        }

        if($this->i >= count($this->e)){
            return new Token(TokenType::EOF, "");
        }

        //check for INT
        $inputString = "";
        while(($this->i < count($this->e)) && (strpos("0123456789", $this->e[$this->i]) >= 0)){
            $inputString .= $this->e[$this->i++];
        }

        if("" !== $inputString){
            return new Token($TokenType->INT, $inputString);
        }

        //check for ID or reserved word
        while(($this->i < count($this->e)) && (strpos("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_", $this->e[$this->i]) >= 0)){
            $inputString .= $this->e[$this->i++];
        }

        if("" !== $inputString){
            if("if" == $inputString){
                return new Token(TokenType::IF);
            }
            if("else" == $inputString){
                return new Token(TokenType::ELSE);
            }
            return new Token(TokenType::ID, $inputString);
        }

        // We're left with strings or one character tokens
        switch($this->e[$this->i++]){
            case '{':
                return new Token(TokenType::LBRACKET, '{');
            case '}':
                return new Token(TokenType::RBRACKET, '}');
            case '[':
                return new Token(TokenType::LSQUAREBRACKET, '[');
            case ']':
                return new Token(TokenType::RSQUAREBRACKET, ']');
            case '<':
                return new Token(TokenType::LESS, '<');
            case '>':
                return new Token(TokenType::GREATER, '>');
            case '=':
                return new Token(TokenType::EQUAL, '=');
            case '"':
                $value = "";
                while(($this->i < count($this->e)) && ($this->e[$this->i] != '"')){
                    $c = $this->e[$this->i++];
                    if($this->i >= count($this->e)){
                        return new Token(TokenType::OTHER);
                    }
                    // check for escaped double quote
                    if(($c == '\\') && ($this->e[$this->i] == '"')){
                        $c = '"';
                        $this->i++;
                    }
                    $value .= $c;
                }
                $this->i++;
                return new Token(TokenType::STRING, $value);
            default:
                // OTHER should result in exception
                return new Token(TokenType::OTHER);
        }
    }
    
}

?>