<?php
/** 
*
* @package code_sniffer
* @version $Id: $
* @copyright (c) 2007 phpBB Group 
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2 
*
*/

/**
* Checks that each source file contains the standard header.
* 
* Based on Coding Guidelines 1.ii File Header.
*
* @package code_sniffer
* @author Manuel Pichler <mapi@phpundercontrol.org>
*/
class phpbb_Sniffs_Commenting_FileCommentSniff implements PHP_CodeSniffer_Sniff
{
	/**
	* Returns an array of tokens this test wants to listen for.
	*
	* @return array
	*/
	public function register()
	{
		return array(T_OPEN_TAG);
	}

	/**
	* Processes this test, when one of its tokens is encountered.
	*
	* @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	* @param int				  $stackPtr  The position of the current token
	*										in the stack passed in $tokens.
	*
	* @return null
	*/
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
	{
		// We are only interested in the first file comment.
		if ($stackPtr !== 0)
		{
			if ($phpcsFile->findPrevious(T_OPEN_TAG, $stackPtr - 1) !== false)
			{
                return;
			}
		}
		
		// Fetch next non whitespace token
		$tokens = $phpcsFile->getTokens();
		$start  = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

		// Skip empty files
		if ($tokens[$start]['code'] === T_CLOSE_TAG)
		{
			return;
		}
		// Mark as error if this is not a doc comment
		else if ($start === false || $tokens[$start]['code'] !== T_DOC_COMMENT)
		{
			$phpcsFile->addError('Missing required file doc comment.', $stackPtr);
			return;
		}
		
		// Find comment end token
		$end = $phpcsFile->findNext(T_DOC_COMMENT, $start + 1, null, true) - 1;
		
		// If there is no end, skip processing here
		if ($end === false)
		{
			return;
		}
		
		// List of found comment tags
		$tags = array();
		
		// check comment lines without the first(/**) an last(*/) line
		for ($i = $start + 1, $c = ($end - $start); $i <= $c; ++$i)
		{
			$line = $tokens[$i]['content'];

			// Check that each line starts with a '*'
			if (substr($line, 0, 1) !== '*')
			{
                $message = 'The file doc comment should not be idented.';
				$phpcsFile->addWarning($message, $i);
			}
			else if (preg_match('/^\*\s+@([\w]+)\s+(.*)$/', $line, $match) !== 0)
			{
				$tags[$match[1]] = array($match[2], $i);
			}
		}
		
		// Check that the first and last line is empty
		if (trim($tokens[$start + 1]['content']) !== '*')
		{
			$message = 'The first file comment line should be empty.';
            $phpcsFile->addWarning($message, ($start + 1));			
		}
        if (trim($tokens[$end - $start]['content']) !== '*')
        {
        	$message = 'The last file comment line should be empty.';
            $phpcsFile->addWarning($message, ($end - $start));         
        }
        
        $this->processPackage($phpcsFile, $start, $tags);
        $this->processVersion($phpcsFile, $start, $tags);
        $this->processCopyright($phpcsFile, $start, $tags);
        $this->processLicense($phpcsFile, $start, $tags);
		
        //print_r($tags);
	}
	
	/**
	 * Checks that the tags array contains a valid package tag
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The context source file instance.
	 * @param integer The stack pointer for the first comment token.
	 * @param array(string=>array) $tags The found file doc comment tags.
	 * 
	 * @return null
	 */
	protected function processPackage(PHP_CodeSniffer_File $phpcsFile, $ptr, $tags)
	{
		if (!isset($tags['package']))
		{
			$message = 'Missing require @package tag in file doc comment.';
			$phpcsFile->addError($message, $ptr);
		}
		else if (preg_match('/^([\w]+)$/', $tags['package'][0]) === 0)
		{
            $message = 'Invalid content found for @package tag.';
            $phpcsFile->addWarning($message, $tags['package'][1]);			
		}
	}
	
	/**
     * Checks that the tags array contains a valid version tag
     *
     * @param PHP_CodeSniffer_File $phpcsFile The context source file instance.
     * @param integer The stack pointer for the first comment token.
     * @param array(string=>array) $tags The found file doc comment tags.
     * 
     * @return null
	 */
	protected function processVersion(PHP_CodeSniffer_File $phpcsFile, $ptr, $tags)
    {
        if (!isset($tags['version']))
        {
            $message = 'Missing require @version tag in file doc comment.';
            $phpcsFile->addError($message, $ptr);
        }
        else if (preg_match('/^\$Id:[^\$]+\$$/', $tags['version'][0]) === 0)
        {
            $message = 'Invalid content found for @version tag, use "$Id: $".';
            $phpcsFile->addError($message, $tags['version'][1]);            
        }
    }
    
    /**
     * Checks that the tags array contains a valid copyright tag
     *
     * @param PHP_CodeSniffer_File $phpcsFile The context source file instance.
     * @param integer The stack pointer for the first comment token.
     * @param array(string=>array) $tags The found file doc comment tags.
     * 
     * @return null
     */
    protected function processCopyright(PHP_CodeSniffer_File $phpcsFile, $ptr, $tags)
    {
        if (!isset($tags['copyright']))
        {
            $message = 'Missing require @copyright tag in file doc comment.';
            $phpcsFile->addError($message, $ptr);
        }
        else if (preg_match('/^\(c\) 2[0-9]{3} phpBB Group\s*$/', $tags['copyright'][0]) === 0)
        {
            $message = 'Invalid content found for @copyright tag, use "(c) <year> phpBB Group".';
            $phpcsFile->addError($message, $tags['copyright'][1]);            
        }
    }
    
    /**
     * Checks that the tags array contains a valid license tag
     *
     * @param PHP_CodeSniffer_File $phpcsFile The context source file instance.
     * @param integer The stack pointer for the first comment token.
     * @param array(string=>array) $tags The found file doc comment tags.
     * 
     * @return null
     */
    protected function processLicense(PHP_CodeSniffer_File $phpcsFile, $ptr, $tags)
    {
		$license = 'http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2';
    	
        if (!isset($tags['license']))
        {
            $message = 'Missing require @license tag in file doc comment.';
            $phpcsFile->addError($message, $ptr);
        }
        else if (trim($tags['license'][0]) !== $license)
        {
            $message = 'Invalid content found for @license tag, use ' 
                     . '"' . $license . '".';
            $phpcsFile->addError($message, $tags['license'][1]);            
        }
    }
}
