<?php
/**
 * BabblingBrook_Sniffs_Whitespace_ScopeIndentSniff.
 *
 * Adapted in 26/03/2012 to allow switch statements to follow jSLint standards and 
 * to allow code to be commented out at the margin.
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2011 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * BabblingBrook_Sniffs_Whitespace_ScopeIndentSniff.
 *
 * Checks that control structures are structured correctly, and their content
 * is indented correctly. This sniff will throw errors if tabs are used
 * for indentation rather than spaces.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2011 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   Release: 1.3.3
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class BabblingBrook_Sniffs_Whitespace_ScopeIndentSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * The number of spaces code should be indented.
     *
     * @var int
     */
    public $indent = 4;

    /**
     * Does the indent need to be exactly right.
     *
     * If TRUE, indent needs to be exactly $ident spaces. If FALSE,
     * indent needs to be at least $ident spaces (but can be more).
     *
     * @var bool
     */
    public $exact = true;

    /**
     * Any scope openers that should not cause an indent.
     *
     * @var array(int)
     */
    protected $nonIndentingScopes = array(T_SWITCH);
	
	/**
	 * An array of tokens that hold lines of sql. Used to verify the indenting of a multiline sql statement
	 * 
	 * @var array(array)
	 */
	private $sql_tokens = array();
	
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return PHP_CodeSniffer_Tokens::$scopeOpeners;

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile All the tokens found in the document.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // If this is an inline condition (ie. there is no scope opener),
        // and it is not a multiline array or function call then
		// this is not a valid indent.
		$scope_opener = true;
        //if (isset($tokens[$stackPtr]['scope_opener']) === false) {
		//    return;
		//}
		
		// work out the open position of the last php open tag. This needs appending to the 
		// to the line indent.
		$open_column = 0;
		for($i = $stackPtr; $i > 0; $i--) {
			if($tokens[$i]['code'] === T_OPEN_TAG) {
				$open_column = $tokens[$i]['column'] - 1;
				break;				
			}
		}
		
		if(array_key_exists('scope_opener', $tokens[$stackPtr]) === false
			|| array_key_exists('scope_closer', $tokens[$stackPtr]) === false
		) {
			return;
		}
		
		$scopeOpener = $tokens[$stackPtr]['scope_opener'];
		$scopeCloser = $tokens[$stackPtr]['scope_closer'];		

		// If this scope contains an open and close php statment  on the same line then ignore it.
		for($i = $stackPtr; $i > 0 && $tokens[$i]['line'] === $tokens[$stackPtr]['line']; $i--) {
			if($tokens[$i]['code'] === T_OPEN_TAG) {
				return;
			}
		}		
		
        if ($tokens[$stackPtr]['code'] === T_ELSE) {
            $next = $phpcsFile->findNext(
                PHP_CodeSniffer_Tokens::$emptyTokens,
                ($stackPtr + 1),
                null,
                true
            );

            // We will handle the T_IF token in another call to process.
            if ($tokens[$next]['code'] === T_IF) {
                return;
            }
        }

        // Find the first token on this line.
        $firstToken = $stackPtr;
        for ($i = $stackPtr; $i >= 0; $i--) {
            // Record the first code token on the line.
            if (in_array($tokens[$i]['code'], PHP_CodeSniffer_Tokens::$emptyTokens) === false) {
                $firstToken = $i;
            }

            // It's the start of the line, so we've found our first php token.
            if ($tokens[$i]['column'] === 1) {
                break;
            }
        }	
		
        // Based on the conditions that surround this token, determine the
        // indent that we expect this current content to be.
        $expectedIndent = $this->calculateExpectedIndent($tokens, $firstToken);

		// Case statements should be indented in line with the switch statement.
		//if($tokens[$stackPtr]['code'] === T_CASE || $tokens[$stackPtr]['code'] === T_DEFAULT) {
		//	$expectedIndent -= 4;
		//}			
		
        // Don't process the first token if it is a closure because they have
        // different indentation rules as they are often used as function arguments
        // for multi-line function calls. But continue to process the content of the
        // closure because it should be indented as normal.
        if ($tokens[$firstToken]['code'] !== T_CLOSURE
            && $tokens[$firstToken]['column'] !== $expectedIndent + $open_column
        ) {

            $error = '(a) Line indented incorrectly; expected %s spaces, found %s';
            $data  = array(
                      ($expectedIndent - 1  + $open_column),
                      ($tokens[$firstToken]['column'] - 1),
                     );
            $phpcsFile->addError($error, $stackPtr, 'Incorrect', $data);
        }

        // Some scopes are expected not to have indents.
        if (in_array($tokens[$firstToken]['code'], $this->nonIndentingScopes) === false) {
            $indent = ($expectedIndent + $this->indent);		
        } else {
            $indent = $expectedIndent;			
        }

        $newline     = false;
        $commentOpen = false;
        $inHereDoc   = false;
		$array_indent = 0;
		$in_string_line = 0;
		$unclosed_in_line = 0;
		$string_start_column;
		$column = 0;
		$possibly_sql = false;
		$is_sql = false;
		$value_indent = 0;
		
        // Only loop over the content between the opening and closing brace, not
        // the braces themselves.
        for ($i = ($scopeOpener + 1); $i < $scopeCloser; $i++) {

			// Search for multiline strings and report an error if they are not indented correctly.
			if($in_string_line > 0) {
				if($tokens[$i]['line'] !== $in_string_line && $tokens[$i]['code'] === T_CONSTANT_ENCAPSED_STRING) {
				
					if($unclosed_in_line === 0 || $unclosed_in_line !== $tokens[$i]['line']) {
			
						// This is an unclosed string.
						
						// This is used to indicate we are in an unclosed string if the string opens and closes within the same line.
						$unclosed_in_line = $tokens[$i]['line'];	
						
						$string_minus_space = trim($tokens[$i]['content']);
						$text_start = strpos($tokens[$i]['content'], trim($tokens[$i]['content']));
						if($string_start_column > $text_start) {
							$type  = 'MultilineStringIndent';
							$error = '(b)Line indented incorrectly; expected ';
							$error .= '%s spaces, found %s';
							$data = array(
									  ($string_start_column),
									  ($text_start),
									);
							$phpcsFile->addError($error, $i, $type, $data);
						}
						// Check if this is a sql statement.
						if($possibly_sql) {				
							$is_sql = $this->isSql($tokens[$i]['content']);					
							$possibly_sql = false;	// Only check the first line.				
						}
						if($is_sql) {
							array_push($this->sql_tokens, $tokens[$i]);
						}
					}
					
				} else if($unclosed_in_line !== $tokens[$i]['line']){
	
					// The string has closed.
					// If the string was sql then pass it to the sql validator.
					if($is_sql) {
					    $this->sqlValidator($string_start_column, $phpcsFile, $i);
					}					
					$in_string_line = 0;
					$possibly_sql = false;
					$is_sql = false;
					$this->sql_tokens = array();
					$unclosed_in_line = 0;
				}
			
			}

			// Is this the start of a multiline string. If so then record the starting column.
			if($tokens[$i]['code'] === T_CONSTANT_ENCAPSED_STRING && $in_string_line === 0) {
				$in_string_line = $tokens[$i]['line'];
				// Is the string empty - in which case string should be indented 4 spaces from start of this line.
				if (trim($tokens[$i]['content']) === '"') {
					$string_start_column = $column + 3;
					$possibly_sql = true;
				} else {
					// Else the string should be indented to the column after the quotation mark.
					$string_start_column = $tokens[$i]['column'];							
				}
			}			

			
            // If this token is another scope, skip it as it will be handled by
            // another call to this sniff.
            if (in_array($tokens[$i]['code'], PHP_CodeSniffer_Tokens::$scopeOpeners) === true) {
                if (isset($tokens[$i]['scope_opener']) === true) {
                    $i = $tokens[$i]['scope_closer'];

                    // If the scope closer is followed by a semi-colon, the semi-colon is part
                    // of the closer and should also be ignored. This most commonly happens with
                    // CASE statements that end with "break;", where we don't want to stop
                    // ignoring at the break, but rather at the semi-colon.
                    $nextToken = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($i + 1), null, true);
                    if ($tokens[$nextToken]['code'] === T_SEMICOLON) {
                        $i = $nextToken;
                    }
                }

                continue;
            }//end if
			
			// If this token is a multi line array or function call. 
			// Need to see if it is closed on this line. 
			// If not then an extra indent needs adding until it is closed.
			if($tokens[$i]['code'] === T_OPEN_PARENTHESIS) {
				$opener_line = $tokens[$i]["line"];
				$closer_line = $tokens[$tokens[$i]['parenthesis_closer']]['line'];
				if($opener_line !== $closer_line) {			
					$array_indent += 4;
					$value_indent = 0;
				}
			}
			
			// If this token is the closer of a multi line array or function call,
			// Then we need to check to move the indent back.
			if($tokens[$i]['code'] === T_CLOSE_PARENTHESIS) {
				$closer_line = $tokens[$i]["line"];
				$opener_line = $tokens[$tokens[$i]['parenthesis_opener']]['line'];
				if($opener_line !== $closer_line) {
					$array_indent -= 4;		
					$value_indent = 0;
				}
			}
			
			
			
            // If this is a HEREDOC then we need to ignore it as the
            // whitespace before the contents within the HEREDOC are
            // considered part of the content.
            if ($tokens[$i]['code'] === T_START_HEREDOC) {
                $inHereDoc = true;
                continue;
            } else if ($inHereDoc === true) {
                if ($tokens[$i]['code'] === T_END_HEREDOC) {
                    $inHereDoc = false;
                }

                continue;
            }

            if ($tokens[$i]['column'] === 1) {
				// We started a newline.
				$newline = true;		
            }

			// Special case for comments after closing braces which for some 
			// reason get included in the wrong group.
			if($newline === true && $tokens[$i]['code'] === T_COMMENT) {				
				for($j = $i; $tokens[$j]['line'] === $tokens[$i]['line']; $j--){
					if($tokens[$j]['code'] === T_CLOSE_CURLY_BRACKET) {						
						$newline = false;
					}
				}
			}			
			
            if ($newline === true && $tokens[$i]['code'] !== T_WHITESPACE) {
                // If we started a newline and we find a token that is not
                // whitespace, then this must be the first token on the line that
                // must be indented.
                $newline    = false;		
				
                $firstToken = $i;

                $column = $tokens[$firstToken]['column'];
                // Special case for non-PHP code.
                if ($tokens[$firstToken]['code'] === T_INLINE_HTML) {
                    $trimmedContentLength
                        = strlen(ltrim($tokens[$firstToken]['content']));
                    if ($trimmedContentLength === 0) {
                        continue;
                    }

                    $contentLength = strlen($tokens[$firstToken]['content']);
                    $column        = ($contentLength - $trimmedContentLength + 1);
                }

                // Check to see if this constant string spans multiple lines.
                // If so, then make sure that the strings on lines other than the
                // first line are indented appropriately, based on their whitespace.
                if (in_array($tokens[$firstToken]['code'], PHP_CodeSniffer_Tokens::$stringTokens) === true) {
                    if (in_array($tokens[($firstToken - 1)]['code'], PHP_CodeSniffer_Tokens::$stringTokens) === true) {
                        // If we find a string that directly follows another string
                        // then its just a string that spans multiple lines, so we
                        // don't need to check for indenting.
                        continue;
                    }				
                }

                // This is a special condition for T_DOC_COMMENT and C-style
                // comments, which contain whitespace between each line.
                $comments = array(
                             T_COMMENT,
                             T_DOC_COMMENT
                            );
							
                if (in_array($tokens[$firstToken]['code'], $comments) === true) {			
                    $content = trim($tokens[$firstToken]['content']);
                    if (preg_match('|^/\*|', $content) !== 0) {
                        // Check to see if the end of the comment is on the same line
                        // as the start of the comment. If it is, then we don't
                        // have to worry about opening a comment.
                        if (preg_match('|\*/$|', $content) === 0) {
                            // We don't have to calculate the column for the
                            // start of the comment as there is a whitespace
                            // token before it.
                            $commentOpen = true;
                        }
                    } else if ($commentOpen === true) {
					
                        if ($content === '') {
                            // We are in a comment, but this line has nothing on it
                            // so let's skip it.
                            continue;
                        }

                        $contentLength = strlen($tokens[$firstToken]['content']);
						$trimmedContent = ltrim($tokens[$firstToken]['content']);
                        $trimmedContentLength = strlen($trimmedContent);

                        $column = ($contentLength - $trimmedContentLength + 1);
                        if (preg_match('|\*/$|', $content) !== 0) {
                            $commentOpen = false;
                        }
						
						// Doc comments are nested an extra space after the opening comment.
						if(substr($trimmedContent, 0, 1) === "*") {
							$column -= 1;
						}						
						
                    }//end if
                }//end if
							
				// If the first token is an equals sign or string concatenator then indent an extra four spaces.
				$concat_indent = 0;
				if ($tokens[$firstToken]['code'] === T_EQUAL || $tokens[$firstToken]['code'] === T_STRING_CONCAT) {		
					$concat_indent = 4;
				}

				//$value_indent = 0;
				// If this is a mutiline array, we need to check that this is not a value part of an assignment.
				// If it is, then it needs an extra 4 spaces.		
				if($array_indent > 0) {
					$j = 0;
					for($j = $firstToken; $tokens[$firstToken]['line'] === $tokens[$j]['line']; $j--) {
						// Locates the start of the line.
					}					
					if ($tokens[$j - 1]['code'] === T_DOUBLE_ARROW) {
						$value_indent = 4;				
					}
					if ($tokens[$j - 1]['code'] === T_COMMA) {
						$value_indent = 0;
					}
				}
				
                // The token at the start of the line, needs to have its' column
                // greater than the relative indent we set above. If it is less,
                // an error should be shown.			
				$full_indent = $indent + $array_indent + $concat_indent + $value_indent + $open_column;
                if ($column !== $full_indent) {
							
					$type  = 'IncorrectExact';
					$error = 'Line indented incorrectly; expected ';
					if ($this->exact === false) {
						$error .= 'at least ';
						$type   = 'Incorrect';
					}
					$error .= '%s spaces, found %s';
					$data = array(
							  ($indent - 1 + $array_indent + $concat_indent + $value_indent),
							  ($column - 1),
							);
					$phpcsFile->addError($error, $firstToken, $type, $data);
                }//end if
            }//end if
        }//end for

    }//end process()


    /**
     * Calculates the expected indent of a token.
     *
     * Returns the column at which the token should be indented to, so 1 means
     * that the token should not be indented at all.
     *
     * @param array $tokens   The stack of tokens for this file.
     * @param int   $stackPtr The position of the token to get indent for.
     *
     * @return int
     */
    protected function calculateExpectedIndent(array $tokens, $stackPtr)
    {
        $conditionStack = array();

        // Empty conditions array (top level structure).
        if (empty($tokens[$stackPtr]['conditions']) === true) {
            if (isset($tokens[$stackPtr]['nested_parenthesis']) === true
                && empty($tokens[$stackPtr]['nested_parenthesis']) === false
            ) {
                // Wrapped in parenthesis means it is probably in a
                // function call (like a closure) so we have to assume indent
                // is correct here and someone else will check it more
                // carefully in another sniff.
                return $tokens[$stackPtr]['column'];
            } else {
                return 1;
            }
        }

        $tokenConditions = $tokens[$stackPtr]['conditions'];
	
        foreach ($tokenConditions as $id => $condition) {
            // If it's an indenting scope ie. it's not in our array of
            // scopes that don't indent, add it to our condition stack.
            if (in_array($condition, $this->nonIndentingScopes) === false) {
                $conditionStack[$id] = $condition;
            }
        }
		
        $indent = ((count($conditionStack) * $this->indent) + 1);
        return $indent;

    }//end calculateExpectedIndent()
	
	/**
	 * Is this the start of a sql statement
	 * @param string $str
	 * 
	 * @return boolean
	 */
	private function isSql ($str) {
		$str = trim($str); 	
		if(substr($str, 0, 6) === "SELECT") {
			return true;
		}else if(substr($str, 0, 6) === "UPDATE") {
			return true;
		}else if(substr($str, 0, 6) === "INSERT") {
			return true;
		}
		return false;
	}
	
	/**
	 * Adds a line to the current sql check and verifies it's accuracy.
	 *
	 * @param integer $base_indent What the indent should be at the start of the sql statement.
	 * @param object $phpcsFile
	 * @param integer $token_start_index The index of the last token in the multiline sql statement.
	 */
	private function sqlValidator ($base_indent, $phpcsFile, $token_end_index) {
		
		// Remove parenthesees.
		$this->sql_tokens = $this->removeParenthesesInArray($this->sql_tokens);
		if($this->sql_tokens === "error") {
			$type  = 'MultilineSQLParentheses';
			$error = 'Parentheses incorrectly nested. ';
			$data = array();
			// number of tokens should match number of strings as each line contains nothing else.
			$phpcsFile->addError($error, $token_end_index, $type, $data);
			return;
		}
		
		// set the type of statement.
		$sql_type = $this->sqlType($this->sql_tokens[0]['content']);
		
		$line_error = true;
		switch ($sql_type) {
		case "select":
			$line_error = $this->checkSelect($this->sql_tokens, $base_indent);
			break;
			
		case "update":
			$line_error = $this->checkUpdate($this->sql_tokens, $base_indent);
			break;
			
		case "insert":
			$line_error = $this->checkInsert($this->sql_tokens, $base_indent);
			break;
		}
		
		if($line_error !== false) {	
			$type  = 'MultilineSQL';
			$error = 'Multiline SQL format error. ' . $line_error[0];
			$data = array();
			// number of tokens should match number of strings as each line contains nothing else.
			$token_id = $token_end_index - count($this->sql_tokens) + $line_error[1];
			$phpcsFile->addError($error, $token_id, $type, $data);
		}
	}
	
	/**
	 * Remove parentheses from the content of a list of tokens without loosing the 
	 * seperatoin between the tokens.
	 * 
	 * @param array(array) $tokens
	 * 
	 * @return array The tokens with parentheses content removed.
	 */	  
	private function removeParenthesesInArray($tokens) {
		$uid = "304689t2gsd07g3hkhdgv098dsghdfkg34g";
		$content = "";
		
		// Join the content into one string.
		$first = true;
		foreach( $tokens as $token) {
			if (!$first) {
				$content .= $uid;
			}		
			$content .= $token['content'];
			$first = false;
		}
		
		// Remove the parentheses
		$content = $this->removeParentheses($content, $uid);

		if($content === "error") {	
			return "error";
		}
		
		// Split the string again.
		$split_content = explode($uid, $content);
		for ($i = 0; $i < count($tokens); $i++) {
			$tokens[$i]['content'] = $split_content[$i];
		}

		return $tokens;	
	}
	
	private function removeParentheses($content, $uid) {
		
		// Escape from the recurssion function.
		if(strpos($content, ")") === false) {
			return $content;
		}
		
		if(substr_count($content, "(") !== substr_count($content, ")")) {
			return "error";
		}

		// Find the first close parenthesis and work back from it to find its opening.
		$closed_position = strpos($content, ")");		
		$upto_closed = substr($content, 0, $closed_position);		
		$open_position = strrpos($upto_closed, "(");	
		if($open_position === false) {
			return "error";
		}

		// Add new lines outside of the parentheses to keep the total number of lines the same.
		$in_parentheses = substr($content, $open_position, $closed_position - $open_position + 1);
		$line_count = substr_count($in_parentheses, $uid);

		// Replace the parentheses with lines that are being replaced in the parentheses.
		$content = substr_replace($content, str_repeat($uid, $line_count), $open_position, $closed_position - $open_position + 1);
		
		// Call recursively until no more parentheses are found.
		
		$content = $this->removeParentheses($content, $uid);
		
		return $content;
	}
	
	/**
	 * Checks the validity of the passed in select array
	 * 
	 * @param array(array) $sql_tokens
	 * @param integer $base_indent What the indent should be at the start of the sql statement.	 
	 *
	 * @return array|boolean False if no error, otherswise an array.
	 *                       First element is an error message
     *                       Second is the line number in the sql statement that the error occurred on.	 
	 */
	private function checkSelect($sql_tokens, $base_indent) {

		$select_found = false;
		$from_found = false;
		$first_param_found = false;
		$where_found = false;
		$extras_found = false;

		for( $i = 0; $i < count($sql_tokens); $i++) {
		
			$line = $sql_tokens[$i]['content'];

			// Skip empty lines. They are probably due to removing parentheses.
			if(trim($line) === "") {
				continue;
			}
			
			$start_pos = strpos($line, trim($line));	
			
			if (!$select_found) {			
				if ($start_pos != $base_indent) {	
					return array("SELECT statement is not correctly indented. Expected $base_indent spaces, found $start_pos", $i);
				}
				$select_found = true;
				continue;
			}

			if(!$from_found) {
				if(substr(trim($line), 0, 4) === "FROM") {
					if ($start_pos !== $base_indent) {
						return array("FROM is not correctly indented. Expected $base_indent spaces, found $start_pos", $i);
					} else {
						$from_found = true;
						continue;
					}
				}
			}
			
			if(!$first_param_found && !$from_found) {
				if($start_pos != $base_indent + 5) {
					return array("The first param must be indented at 5 spaces. Found " . ($start_pos - $base_indent), $i);
				}
				$first_param_found = true;
				continue;
			}

			if($first_param_found && !$from_found) {
				// This must be another param
				if($start_pos != $base_indent + 4) {
					return array("Paramaters other than the first must be indented at 4 spaces. Found " . ($start_pos - $base_indent), $i);
				}
				if(substr(trim($line), 0, 1) !== ","){
					return array("Paramaters other than the first must be preceded by a comma", $i);
				}
				continue;
			}

			if(!$where_found) {					
				if(substr(trim($line), 0, 5) === "WHERE") {		
					if($start_pos != $base_indent) {
						return array("WHERE statments must not be indented. Found " . ($start_pos - $base_indent), $i);
					}
					$where_found = true;
					continue;
				}
			}
			
			if($from_found && !$where_found) {
				// this is a table or join.
				if(substr(trim($line), 0, 2) === "ON") {
					if($start_pos != $base_indent + 8) {
						return array("Nested ON statments must be indented at 8 spaces. Found " . ($start_pos - $base_indent), $i);
					}
					continue;
				}
				if(substr(trim($line), 0, 3) === "AND" || substr(trim($line), 0, 2) === "OR") {
					if($start_pos != $base_indent + 12) {
						return array("Nested join conditions must be indented at 12 spaces. Found " . ($start_pos - $base_indent), $i);
					}
					continue;
				}
				// Must be a table or join			
				if($start_pos != $base_indent + 4) {
					return array("Tables and joins must be indented at 4 spaces. Found " . ($start_pos - $base_indent), $i);
				}
				continue;
			}

			if($where_found) {		
				// Is this a where condition or an extra.
				if(substr(trim($line), 0, 8) === "ORDER BY") {		
					if($start_pos != $base_indent) {
						return array("ORDER BY statments must not be indented. Found " . ($start_pos - $base_indent), $i);
					}
					$extras_found = true;
					continue;
				}				
				if(substr(trim($line), 0, 8) === "GROUP BY") {		
					if($start_pos != $base_indent) {
						return array("GROUP BY statments must not be indented. Found " . ($start_pos - $base_indent), $i);
					}
					$extras_found = true;
					continue;
				}
				if(substr(trim($line), 0, 6) === "HAVING") {		
					if($start_pos != $base_indent) {
						return array("HAVING statments must not be indented. Found " . ($start_pos - $base_indent), $i);
					}
					$extras_found = true;
					continue;
				}
				if(substr(trim($line), 0, 5) === "LIMIT") {		
					if($start_pos != $base_indent) {
						return array("LIMIT statments must not be indented. Found " . ($start_pos - $base_indent), $i);
					}
					$extras_found = true;
					continue;
				}
				if(substr(trim($line), 0, 9) === "PROCEDURE") {		
					if($start_pos != $base_indent) {
						return array("PROCEDURE statments must not be indented. Found " . ($start_pos - $base_indent), $i);
					}
					$extras_found = true;
					continue;
				}
				if(substr(trim($line), 0, 12) === "INTO OUTFILE") {		
					if($start_pos != $base_indent) {
						return array("INTO OUTFILE statments must not be indented. Found " . ($start_pos - $base_indent), $i);
					}
					$extras_found = true;
					continue;
				}
				if(substr(trim($line), 0, 10) === "FOR UPDATE") {		
					if($start_pos != $base_indent) {
						return array("FOR UPDATE statments must not be indented. Found " . ($start_pos - $base_indent), $i);
					}
					$extras_found = true;
					continue;
				}
				// No extras found, must be a where condition.
				if(!$extras_found) {
					if($start_pos != $base_indent + 4) {
						return array("WHERE conditions must be indented 4 spaces. Found " . ($start_pos - $base_indent), $i);
					}
					continue;
				}
			}
			
			// Should not reach here
			return array("Unknown code found", $i);
		
		}
	
		return false;
	}
	
	/**
	 * Checks the validity of the passed in update array
	 * 
	 * @param array(string) $sql_tokens
	 * @param integer $base_indent What the indent should be at the start of the sql statement.	 
	 *
	 * @return array|boolean False if no error, otherswise an array.
	 *                       First element is an error message
     *                       Second is the line number in the sql statement that the error occurred on.	 
	 */
	private function checkUpdate($sql_tokens, $base_indent) {
		$update_found = false;
		$set_found = false;		
		$first_param_found = false;
		$where_found = false;
		$extras_found = false;

		for( $i = 0; $i < count($sql_tokens); $i++) {
		
			$line = $sql_tokens[$i]['content'];

			// Skip empty lines. They are probably due to removing parentheses.
			if(trim($line) === "") {
				continue;
			}
			
			$start_pos = strpos($line, trim($line));	
			
			if (!$update_found) {			
				if ($start_pos != $base_indent) {	
					return array("UPDATE statement is not correctly indented. Expected $base_indent spaces, found $start_pos", $i);
				}
				$update_found = true;
				continue;
			}
			
			if(!$set_found) {
				if(substr(trim($line), 0, 3) === "SET") {
					if($start_pos != $base_indent) {
						return array("SET must not be indented. Found " . ($start_pos - $base_indent), $i);
					}
					$set_found = true;
					continue;
				}
				// Must be a table reference
				if(substr(trim($line), 0, 2) === "ON") {
					if($start_pos != $base_indent + 8) {
						return array("Nested ON statments must be indented at 8 spaces. Found " . ($start_pos - $base_indent), $i);
					}
					continue;
				}
				if(substr(trim($line), 0, 3) === "AND" || substr(trim($line), 0, 2) === "OR") {
					if($start_pos != $base_indent + 12) {
						return array("Nested join conditions must be indented at 12 spaces. Found " . ($start_pos - $base_indent), $i);
					}
					continue;
				}
				// Must be a table or join			
				if($start_pos != $base_indent + 4) {
					return array("Tables and joins must be indented at 4 spaces. Found " . ($start_pos - $base_indent), $i);
				}
				continue;

			}
			
			if(!$where_found) {					
				if(substr(trim($line), 0, 5) === "WHERE") {		
					if($start_pos != $base_indent) {
						return array("WHERE statments must not be indented. Found " . ($start_pos - $base_indent), $i);
					}
					$where_found = true;
					continue;
				}
			}			
			
			if(!$first_param_found && !$where_found) {
				if($start_pos != $base_indent + 5) {
					return array("The first SET column must be indented at 5 spaces. Found " . ($start_pos - $base_indent), $i);
				}
				$first_param_found = true;
				continue;
			}

			if($first_param_found && !$where_found) {
				// This must be another param
				if($start_pos != $base_indent + 4) {
					return array("SET columns other than the first must be indented at 4 spaces. Found " . ($start_pos - $base_indent), $i);
				}
				if(substr(trim($line), 0, 1) !== ","){
					return array("SET columns other other than the first must be preceded by a comma", $i);
				}
				continue;
			}
			

			if($where_found) {		
				// Is this a where condition or an extra.
				if(substr(trim($line), 0, 8) === "ORDER BY") {		
					if($start_pos != $base_indent) {
						return array("ORDER BY statments must not be indented. Found " . ($start_pos - $base_indent), $i);
					}
					$extras_found = true;
					continue;
				}
				if(substr(trim($line), 0, 5) === "LIMIT") {		
					if($start_pos != $base_indent) {
						return array("LIMIT statments must not be indented. Found " . ($start_pos - $base_indent), $i);
					}
					$extras_found = true;
					continue;
				}
				// No extras found, must be a where condition.
				if(!$extras_found) {
					if($start_pos != $base_indent + 4) {
						return array("WHERE conditions must be indented 4 spaces. Found " . ($start_pos - $base_indent), $i);
					}
					continue;
				}
			}
			
			// Should not reach here
			return array("Unknown code found", $i);
		
		}
	
		return false;
	}
	
	/**
	 * Checks the validity of the passed in insert array
	 * Due the removal of parentheses, only the outer structure can be tested.
	 * 
	 * @param array(string) $sql_tokens
	 * @param integer $base_indent What the indent should be at the start of the sql statement.	 
	 *
	 * @return array|boolean False if no error, otherswise an array.
	 *                       First element is an error message
     *                       Second is the line number in the sql statement that the error occurred on.	 
	 */
	private function checkInsert($sql_tokens, $base_indent) {
		$insert_found = false;
		$values_found = false;
		$where_found = false;
		$extras_found = false;

		for( $i = 0; $i < count($sql_tokens); $i++) {
		
			$line = $sql_tokens[$i]['content'];

			// Skip empty lines. They are probably due to removing parentheses.
			if(trim($line) === "") {
				continue;
			}
			
			$start_pos = strpos($line, trim($line));	
			
			if (!$insert_found) {			
				if ($start_pos != $base_indent) {	
					return array("INSERT statement is not correctly indented. Expected $base_indent spaces, found $start_pos", $i);
				}
				$insert_found = true;
				continue;
			}
			
			if (!$values_found ) {
				// Must be values or select
				if (substr(trim($line), 0, 6) !== "VALUES" && substr(trim($line), 0, 6) !== "SELECT") {		
					return array("Must be a VALUES or SELECT statement.", $i);
				}
				if (substr(trim($line), 0, 6) === "SELECT") {
					// Run this as a seperate query in the select method.
					return $this->checkSelect(array_slice($sql_tokens, $i), $base_indent);
				}
				$values_found = true;
				continue;
			}
			
			// Due to parentheses beeing removed, there may be a close quotation mark here.
			if($line = '"') {
				continue;
			}
			
			// Should not reach here
			return array("Unknown code found :" . $line, $i);
		}
		return false;
	}

	/**
	 * What type of sql statement is this
	 * @param string $str
	 */
	private function sqlType ($str) {
		$str = trim($str); 
		if(substr($str, 0, 6) === "SELECT") {
			return "select";
		}else if(substr($str, 0, 6) === "UPDATE") {
			return  "update";
		}else if(substr($str, 0, 6) === "INSERT") {
			return  "insert";
		}
	}

}//end class

?>
