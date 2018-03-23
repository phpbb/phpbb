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

namespace phpbb\textreparser\plugins;

class user_signature extends \phpbb\textreparser\row_based_plugin
{
	/**
	* @var array Bit numbers used for user options
	* @see \phpbb\user
	*/
	protected $keyoptions;

	/**
	* {@inheritdoc}
	*/
	protected function add_missing_fields(array $row)
	{
		if (!isset($this->keyoptions))
		{
			$this->save_keyoptions();
		}

		$options = $row['user_options'];
		$row += array(
			'enable_bbcode'    => phpbb_optionget($this->keyoptions['sig_bbcode'], $options),
			'enable_smilies'   => phpbb_optionget($this->keyoptions['sig_smilies'], $options),
			'enable_magic_url' => phpbb_optionget($this->keyoptions['sig_links'], $options),
		);

		return parent::add_missing_fields($row);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_columns()
	{
		return array(
			'id'           => 'user_id',
			'text'         => 'user_sig',
			'bbcode_uid'   => 'user_sig_bbcode_uid',
			'user_options' => 'user_options',
		);
	}

	/**
	* Save the keyoptions var from \phpbb\user
	*/
	protected function save_keyoptions()
	{
		$class_vars = get_class_vars('phpbb\\user');
		$this->keyoptions = $class_vars['keyoptions'];
	}
}
