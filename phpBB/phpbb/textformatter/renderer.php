<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\textformatter;

abstract class renderer
{
	/**
	* Render given text
	*
	* @param  string $text Text, as parsed by something that implements \phpbb\textformatter\parser
	* @return string
	*/
	abstract public function render($text);

	/**
	* Automatically set the smilies path based on config
	*
	* @param  phpbb\config\config $config
	* @param  phpbb\path_helper   $path_helper
	* @return null
	*/
	public function configure_smilies_path(\phpbb\config\config $config, \phpbb\path_helper $path_helper)
	{
		/**
		* @see smiley_text()
		*/
		$root_path = (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? generate_board_url() . '/' : $path_helper->get_web_root_path();

		$this->set_smilies_path($root_path . $config['smilies_path']);
	}

	/**
	* Configure this renderer as per the user's settings
	*
	* Should set the locale as well as the viewcensor/viewflash/viewimg/viewsmilies options.
	*
	* @param  phpbb\user          $user
	* @param  phpbb\config\config $config
	* @param  phpbb\auth\auth     $auth
	* @return null
	*/
	public function configure_user(\phpbb\user $user, \phpbb\config\config $config, \phpbb\auth\auth $auth)
	{
		$censor =  $user->optionget('viewcensors') || !$config['allow_nocensors'] || !$auth->acl_get('u_chgcensors');

		$this->set_viewcensors($censor);
		$this->set_viewflash($user->optionget('viewflash'));
		$this->set_viewimg($user->optionget('viewimg'));
		$this->set_viewsmilies($user->optionget('viewsmilies'));
	}

	/**
	* Set the smilies' path
	*
	* @return null
	*/
	abstract public function set_smilies_path($path);

	/**
	* Return the value of the "viewcensors" option
	*
	* @return bool Option's value
	*/
	abstract public function get_viewcensors();

	/**
	* Return the value of the "viewflash" option
	*
	* @return bool Option's value
	*/
	abstract public function get_viewflash();

	/**
	* Return the value of the "viewimg" option
	*
	* @return bool Option's value
	*/
	abstract public function get_viewimg();

	/**
	* Return the value of the "viewsmilies" option
	*
	* @return bool Option's value
	*/
	abstract public function get_viewsmilies();

	/**
	* Set the "viewcensors" option
	*
	* @param  bool $value Option's value
	* @return null
	*/
	abstract public function set_viewcensors($value);

	/**
	* Set the "viewflash" option
	*
	* @param  bool $value Option's value
	* @return null
	*/
	abstract public function set_viewflash($value);

	/**
	* Set the "viewimg" option
	*
	* @param  bool $value Option's value
	* @return null
	*/
	abstract public function set_viewimg($value);

	/**
	* Set the "viewsmilies" option
	*
	* @param  bool $value Option's value
	* @return null
	*/
	abstract public function set_viewsmilies($value);
}
