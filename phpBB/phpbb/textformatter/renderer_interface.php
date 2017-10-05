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

interface renderer_interface
{
	/**
	* Render given text
	*
	* @param  string $text Text, as parsed by something that implements \phpbb\textformatter\parser
	* @return string
	*/
	public function render($text);

	/**
	* Set the smilies' path
	*
	* @return null
	*/
	public function set_smilies_path($path);

	/**
	* Return the value of the "viewcensors" option
	*
	* @return bool Option's value
	*/
	public function get_viewcensors();

	/**
	* Return the value of the "viewflash" option
	*
	* @return bool Option's value
	*/
	public function get_viewflash();

	/**
	* Return the value of the "viewimg" option
	*
	* @return bool Option's value
	*/
	public function get_viewimg();

	/**
	* Return the value of the "viewsmilies" option
	*
	* @return bool Option's value
	*/
	public function get_viewsmilies();

	/**
	* Set the "viewcensors" option
	*
	* @param  bool $value Option's value
	* @return null
	*/
	public function set_viewcensors($value);

	/**
	* Set the "viewflash" option
	*
	* @param  bool $value Option's value
	* @return null
	*/
	public function set_viewflash($value);

	/**
	* Set the "viewimg" option
	*
	* @param  bool $value Option's value
	* @return null
	*/
	public function set_viewimg($value);

	/**
	* Set the "viewsmilies" option
	*
	* @param  bool $value Option's value
	* @return null
	*/
	public function set_viewsmilies($value);
}
