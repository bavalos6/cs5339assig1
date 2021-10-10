<?php

include_once('TokenType.php');
include_once('Token.php');
include_once('Tokenizer.php');
include_once('EvalSectionException.php');


$oneIndent = "   ";
$EOL = PHP_EOL;
$inputSource = "fall21Testing.txt";
$filecontents = file_get_contents($inputSource);

$header = "<html>" . $EOL
    . "  <head>" . $EOL
    . "    <title>CS 4339/5339 PHP assignment</title>" . $EOL
    . "  </head>" . $EOL
    . "  <body>" . $EOL
    . "    <pre>";
$footer = "    </pre>" . $EOL
    . "  </body>" . $EOL
    . "</html>";

$inputLine;
$inputFile = "";
$t = new Tokenizer($filecontents);
echo $header.PHP_EOL;
$currentToken = $t->nextToken();
$sec_num = 0;

// Loop through all sections, for each section printing result
// If a section causes exception, catch and jump to next section
while($currentToken->type != TokenType::EOF){
    echo "section " . ++$sec_num.PHP_EOL;
	if($sec_num == 25){
        break;
    }

    try{
        $result = evalSection();
        echo "Section result:".PHP_EOL;
        echo $result.PHP_EOL ;
    }
    catch(EvalSectionException $ex){
		// skip to the end of section
        while (($currentToken -> type != TokenType::RSQUAREBRACKET) && ($currentToken->type != TokenType::EOF)){
            $currentToken = $t->nextToken();
        }
		$currentToken = $t->nextToken();
    }
}
echo $footer .PHP_EOL ;

function evalSection(){
	// <section> ::= [ <statement>* ]
    global $map;
	$map = array();
	$result = "";

    if($currentToken->type != TokenType::LSQUAREBRACKET){
        throw new EvalSectionException("The section must be proceeded \"[\"");
    }
    echo "[".PHP_EOL;
    $currentToken = $t->nextToken();
	while(($currentToken->type != TokenType::RSQUAREBRACKET) && ($currentToken->type != TokenType::EOF)){
		$exec = TRUE;
		evalStatement($oneIndent, $exec);
	}
    echo "]".PHP_EOL;
    $currentToken = $t->nextToken();
}

function evalStatement($indent, $exec){
	// exec it true if we are executing the statements in addition to parsing
    // <statement> ::= STRING | <assignment> | <conditional>
	global $result;

    switch ($currentToken->type){
        case TokenType::ID;
			evalAssignment($indent, $exec);
            break;
        case TokenType::IF:
			evalConditional($indent, $exec);
            break;
		case TokenType::STRING:
			if($exec){
				$result .= $currentToken->value.PHP_EOL;
			}
			echo $indent."\"".$currentToken->value."\"".PHP_EOL;
			$currentToken = $t->nextToken();
            break;
		default:
            throw new EvalSectionException("invalid statement");
    }
}


function evalAssignment($indent, $exec) {
	// <assignment> ::= ID '=' INT
    // we know currentToken is ID 
    global $currentToken;
    global $oneIndent;
    global $t; 
    global $value; 
    global $map;

	$key = $currentToken->value;
	echo $indent.$key;
    $currentToken = $t->nextToken();

	if ($currentToken->type != TokenType::EQUAL){
		echo PHP_EOL;
        throw new EvalSectionException("equal sign expected");
	}

	echo "=";
	$currentToken = $t->nextToken();

	if ($currentToken->type != TokenType::INT){
		echo PHP_EOL;
        throw new EvalSectionException("integer sign expected");
	}

	$value = intval($currentToken->value);
	echo $value.PHP_EOL;
	$testToken = $currentToken;
	$currentToken = $t->nextToken();
	if ($exec){
		$map[$key] = $value;
	}
}

function evalConditional($indent, $exec){
	// <conditional> ::= 'if' <condition> '{' <statement>* '}' [ 'else' '{'
    // We know currentToken is "if"
	echo $indent."if ".PHP_EOL;
	$currentToken = $t->nextToken();
	$trueCondition = evalCondition($exec);

	if($currentToken->type != TokenType::LBRACKET){
		throw new EvalSectionException("left bracket extected");
	}
	echo " {".PHP_EOL;

	$currentToken = $t->nextToken();
	while(($currentToken->type != TokenType::RBRACKET) && ($currentToken->type != TokenType::EOF)){
		if($trueCondition){
			evalStatement($indent.$oneIndent, $exec);
		}
		else{
			evalStatement($indent.$oneIndent, FALSE);
		}
	}

	if($currentToken->type == TokenType::RBRACKET){
		echo $indent."}".PHP_EOL;
		$currentToken = $t->nextToken();
	}
	else{
		throw new EvalSectionException("right bracket extected");
	}

	if($currentToken->type == TokenType::ELSE){
		echo $indent."else".PHP_EOL;
		$currentToken = $t->nextToken();

		if($currentToken->type != TokenType::LBRACKET){
			throw new EvalSectionException("left bracket extected");
		}
		echo " {".PHP_EOL;
		$currentToken = $t->nextToken();

		while(($currentToken->type != TokenType::RBRACKET) && ($currentToken->type != TokenType::EOF)){
			if($trueCondition){
				evalStatement($indent.$oneIndent, FALSE);
			}
			else{
				evalStatement($indent.$oneIndent, $exec);
			}
		}

		if($currentToken->type == TokenType::RBRACKET){
			echo $indent."}".PHP_EOL;
			$currentToken = $t->nextToken();
		}
		else{
			throw new EvalSectionException("right bracket extected");
		}
	}

}

function evalCondition($exec){
	// <condition> ::= ID ('<' | '>' | '=') INT
	$v1 = null; // value associated with ID
	if($currentToken->type != TokenType::ID){
		throw new EvalSectionException("identifier expected");
	}
	$key = $currentToken->value;
	echo $key.PHP_EOL;
	if($exec){
		$v1 = $map[$key];
		if($v1 == null){
			throw new EvalSectionException("undefined variable");
		}
	}
	$currentToken = $t->nextToken();
	$operator = $currentToken->type;
	if($currentToken->type != TokenType::EQUAL && $currentToken->type != TokenType::LESS && $currentToken->type != TokenType::GREATER){
		throw new EvalSectionException("comparison operator expected");
	}
	echo $currentToken->value.PHP_EOL;
	$currentToken = $t->nextToken();
	if($currentToken->type != TokenType::INT){
		throw new EvalSectionException("integer expected");
	}
	$value = intval($currentToken->value);
	echo $value." ".PHP_EOL;
	$currentToken = $t->nextToken();
	
	// compute return value
	if($exec){
		return FALSE;
	}
	$trueResult = FALSE;
	switch($operator){
		case LESS:
			$trueResult = $v1 < $value;
			break;
		case GREATER:
			$trueResult = $v1 > $value;
			break;
		case EQUEAL:
			$trueResult = $v1 == $value;
	}
	return $trueResult;
}
