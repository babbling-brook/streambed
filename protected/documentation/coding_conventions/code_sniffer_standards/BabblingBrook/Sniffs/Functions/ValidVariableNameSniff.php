<?php
/**
 * BabblingBrook_Sniffs_Functions_ValidVariableNameSniff.
 *
 * Adapted on 26/03/2012 to remove the underscore for private members.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2011 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

if (class_exists('PHP_CodeSniffer_Standards_AbstractVariableSniff', true) === false) {
    $error = 'Class PHP_CodeSniffer_Standards_AbstractVariableSniff not found';
    throw new PHP_CodeSniffer_Exception($error);
}

/**
 * BabblingBrook_Sniffs_Functions_ValidVariableNameSniff.
 *
 * Checks the naming of member variables.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2011 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   Release: 1.3.3
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class BabblingBrook_Sniffs_Functions_ValidVariableNameSniff extends PHP_CodeSniffer_Standards_AbstractVariableSniff
{


    /**
     * Processes class member variables.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    protected function processMemberVar(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $memberProps = $phpcsFile->getMemberProperties($stackPtr);
        if (empty($memberProps) === true) {
            return;
        }

        $memberName     = ltrim($tokens[$stackPtr]['content'], '$');
        $isPublic       = ($memberProps['scope'] === 'private') ? false : true;
        $scope          = $memberProps['scope'];
        $scopeSpecified = $memberProps['scope_specified'];

        // If it's a private member, it must have an underscore on the front.
        //if ($isPublic === false && $memberName{0} !== '_') {
        //    $error = 'Private member variable "%s" must be prefixed with an underscore';
        //    $data  = array($memberName);
        //    $phpcsFile->addError($error, $stackPtr, 'PrivateNoUnderscore', $data);
        //    return;
        //}

        // If it's not a private member, it must not have an underscore on the front.
        //if ($isPublic === true && $scopeSpecified === true && $memberName{0} === '_') {
		if ($scopeSpecified === true && $memberName{0} === '_') {
            $error = '%s member variable "%s" must not be prefixed with an underscore';
            $data  = array(
                      ucfirst($scope),
                      $memberName,
                     );
            $phpcsFile->addError($error, $stackPtr, 'FuncitonMemberUnderscore', $data);
            return;
        }
		
		if($scopeSpecified === true && !preg_match("/^[a-z]{1}[a-z0-9]+(_?[a-z0-9]*)*$/", $memberName)){
            $error = '%s member variable "%s" must be in lowercase underscore format. ';
			$error .= 'Digits are allowed, but not in the first character';
            $data  = array(
                      ucfirst($scope),
                      $memberName,
                     );
            $phpcsFile->addError($error, $stackPtr, 'FuncitonMemberLowercaseUnderscore', $data);
            return;
		}

    }//end processMemberVar()


    /**
     * Processes normal variables.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param int                  $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processVariable(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        // We don't care about normal variables.
        return;

    }//end processVariable()


    /**
     * Processes variables in double quoted strings.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param int                  $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processVariableInString(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        // We don't care about normal variables.
        return;

    }//end processVariableInString()


}//end class

?>
