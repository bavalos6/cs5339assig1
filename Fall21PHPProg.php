<?php
include('Tokenizer.php');
include('EvalSectionException.php');

class Fall21PHPProg{
	static Token $currentToken;
	static Tokenizer $t;
	static $map;
	static $oneIndent = "   ";
	static $result = "";

	public static function Fall21PHPProg(){
		$EOL = PHP_EOL;

		$header = "<html>" . $EOL
			. "  <head>" . $EOL
			. "    <title>CS 4339/5339 PHP assignment</title>" . $EOL
			. "  </head>" . $EOL
			. "  <body>" . $EOL
			. "    <pre>";
		$footer = "    </pre>" . $EOL
			. "  </body>" . $EOL
			. "</html>";
		
		$inputSource = "fall21Testing.txt";
		$filecontents = file_get_contents($inputSource);

		self::$t = new Tokenizer($filecontents);
		//print_r($t);
		//print_r($currentToken);
		echo $header.PHP_EOL;
		self::$currentToken = self::$t->nextToken();
		$section = 0;

		// Loop through all sections, for each section printing result
		// If a section causes exception, catch and jump to next section
		while(self::$currentToken->type != TokenType::EOF){
			echo "section " . ++$section.PHP_EOL;
			try{
				self::evalSection();
				echo "Section result:".PHP_EOL;
				echo self::$result.PHP_EOL ;
			}
			catch(EvalSectionException $ex){
				// skip to the end of section
				while (self::$currentToken -> type != TokenType::RSQUAREBRACKET && self::$currentToken->type != TokenType::EOF){
					self::$currentToken = self::$t->nextToken();
				}
				self::$currentToken = self::$t->nextToken();
			}
		}

		echo $footer .PHP_EOL ;
	}
	public static function evalSection(){
		// <section> ::= [ <statement>* ]
		self::$map = array();
		self::$result = "";

		if(self::$currentToken->type != TokenType::LSQUAREBRACKET){
			throw new EvalSectionException("The section must be proceeded \"[\"");
		}
		echo "[".PHP_EOL;
		self::$currentToken = self::$t->nextToken();
		while(self::$currentToken->type != TokenType::RSQUAREBRACKET && self::$currentToken->type != TokenType::EOF){
			self::evalStatement(self::$oneIndent, true);
		}
		echo "]".PHP_EOL;
		self::$currentToken = self::$t->nextToken();
	}

	public static function evalStatement($indent, $exec)
	{
		// exec it true if we are executing the statements in addition to parsing
		// <statement> ::= STRING | <assignment> | <conditional>
		switch (self::$currentToken->type) {
			case TokenType::ID;
				self::evalAssignment($indent, $exec);
				break;
			case TokenType::IF:
				self::evalConditional($indent, $exec);
				break;
			case TokenType::STRING:
				if ($exec) {
					self::$result .= self::$currentToken->value . PHP_EOL;
				}
				echo $indent . "\"" . self::$currentToken->value . "\"" . PHP_EOL;
				self::$currentToken = self::$t->nextToken();
				break;
			default:
				throw new EvalSectionException("invalid statement");
		}
	}


	public static function evalAssignment($indent, $exec) {
		// <assignment> ::= ID '=' INT
		// we know currentToken is ID 

		$key = self::$currentToken->value;
		echo $indent.$key;
		self::$currentToken = self::$t->nextToken();

		if (self::$currentToken->type != TokenType::EQUAL){
			throw new EvalSectionException("equal sign expected");
		}

		echo "=";
		self::$currentToken = self::$t->nextToken();

		if (self::$currentToken->type != TokenType::INT){
			throw new EvalSectionException("integer sign expected");
		}

		$value = intval(self::$currentToken->value);
		echo $value.PHP_EOL;
		self::$currentToken = self::$t->nextToken();
		if ($exec){
			self::$map[$key] = $value;
		}
	}

	public static function evalConditional($indent, $exec){
		// <conditional> ::= 'if' <condition> '{' <statement>* '}' [ 'else' '{'
		// We know currentToken is "if"
		echo $indent."if ";
		self::$currentToken = self::$t->nextToken();
		$trueCondition = self::evalCondition($exec);

		if(self::$currentToken->type != TokenType::LBRACKET){
			throw new EvalSectionException("left bracket extected");
		}
		echo " {".PHP_EOL;

		self::$currentToken = self::$t->nextToken();
		while(self::$currentToken->type != TokenType::RBRACKET && self::$currentToken->type != TokenType::EOF){
			if($trueCondition){
				self::evalStatement($indent.self::$oneIndent, $exec);
			}
			else{
				self::evalStatement($indent.self::$oneIndent, FALSE);
			}
		}

		if(self::$currentToken->type == TokenType::RBRACKET){
			echo $indent."}".PHP_EOL;
			self::$currentToken = self::$t->nextToken();
		}
		else{
			throw new EvalSectionException("right bracket extected");
		}

		if(self::$currentToken->type == TokenType::ELSE){
			echo $indent."else";
			self::$currentToken = self::$t->nextToken();

			if(self::$currentToken->type != TokenType::LBRACKET){
				throw new EvalSectionException("left bracket extected");
			}
			echo " {".PHP_EOL;
			self::$currentToken = self::$t->nextToken();

			while(self::$currentToken->type != TokenType::RBRACKET && self::$currentToken->type != TokenType::EOF){
				if($trueCondition){
					self::evalStatement($indent.self::$oneIndent, FALSE);
				}
				else{
					self::evalStatement($indent.self::$oneIndent, $exec);
				}
			}

			if(self::$currentToken->type == TokenType::RBRACKET){
				echo $indent."}".PHP_EOL;
				self::$currentToken = self::$t->nextToken();
			}
			else{
				throw new EvalSectionException("right bracket extected");
			}
		}

	}

	public static function evalCondition($exec){
		// <condition> ::= ID ('<' | '>' | '=') INT
		if(self::$currentToken->type != TokenType::ID){
			throw new EvalSectionException("identifier expected");
		}
		$key = self::$currentToken->value;
		echo $key;
		if($exec){
			if(!array_key_exists($key, self::$map)){
				throw new EvalSectionException("undefined variable");
			}
			else{
				$v1 = self::$map[$key];
			}
		}
		self::$currentToken = self::$t->nextToken();
		$operator = self::$currentToken->type;
		if(self::$currentToken->type != TokenType::EQUAL && self::$currentToken->type != TokenType::LESS && self::$currentToken->type != TokenType::GREATER){
			throw new EvalSectionException("comparison operator expected");
		}
		echo self::$currentToken->value;
		self::$currentToken = self::$t->nextToken();
		if(self::$currentToken->type != TokenType::INT){
			throw new EvalSectionException("integer expected");
		}
		$value = intval(self::$currentToken->value);
		echo $value." ";
		self::$currentToken = self::$t->nextToken();
		
		// compute return value
		if(!$exec){
			return FALSE;
		}
		$trueResult = FALSE;
		switch($operator){
			case TokenType::LESS:
				$trueResult = $v1 < $value;
				break;
			case TokenType::GREATER:
				$trueResult = $v1 > $value;
				break;
			case TokenType::EQUAL:
				$trueResult = $v1 == $value;
		}
		return $trueResult;
	}
}
Fall21PHPProg::Fall21PHPProg();
?>