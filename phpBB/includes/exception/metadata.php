<?php
/**
*
* @package extension
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * Exception class for metadata
 */
class phpbb_exception_metadata extends LogicException
{
	const NOT_SET = 10001;
	const INVALID = 10002;
	const FILE_GET_CONTENTS = 10003;
	const JSON_DECODE = 10004;
	const FILE_DOES_NOT_EXIST = 10005;

	public function __construct($code, $field_name)
	{
		$this->code = $code;
		$this->field_name = $field_name;
	}

	public function __toString()
	{
		return sprintf($this->getErrorMessage(), $this->field_name);
	}

	public function getErrorMessage()
	{
		switch ($this->code)
		{
			case self::NOT_SET:
				return 'The "%s" meta field has not been set.';
			break;

			case self::INVALID:
				return 'The "%s" meta field is not valid.';
			break;

			case self::FILE_GET_CONTENTS:
				return 'file_get_contents failed on %s';
			break;

			case self::JSON_DECODE:
				return 'json_decode failed on %s';
			break;

			case self::FILE_DOES_NOT_EXIST:
				return 'Required file does not exist at %s';
			break;

			default:
				return 'An unexpected error has occurred.';
			break;
		}
	}
}