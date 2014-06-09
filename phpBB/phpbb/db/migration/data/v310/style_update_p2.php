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

namespace phpbb\db\migration\data\v310;

class style_update_p2 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return !$this->db_tools->sql_table_exists($this->table_prefix . 'styles_imageset');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\style_update_p1');
	}

	public function update_schema()
	{
		return array(
			'drop_keys'	=> array(
				$this->table_prefix . 'styles'		=> array(
					'imageset_id',
					'template_id',
					'theme_id',
				),
			),

			'drop_columns'	=> array(
				$this->table_prefix . 'styles'		=> array(
					'imageset_id',
					'template_id',
					'theme_id',
				),
			),

			'drop_tables'	=> array(
				$this->table_prefix . 'styles_imageset',
				$this->table_prefix . 'styles_imageset_data',
				$this->table_prefix . 'styles_template',
				$this->table_prefix . 'styles_template_data',
				$this->table_prefix . 'styles_theme',
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'styles'		=> array(
					'imageset_id'	=> array('UINT', 0),
					'template_id'	=> array('UINT', 0),
					'theme_id'		=> array('UINT', 0),
				),
			),

			'add_index'		=> array(
				$this->table_prefix . 'styles'			=> array(
					'imageset_id'		=> array('imageset_id'),
					'template_id'		=> array('template_id'),
					'theme_id'			=> array('theme_id'),
				),
			),

			'add_tables'	=> array(
				$this->table_prefix . 'styles_imageset'		=> array(
					'COLUMNS'		=> array(
						'imageset_id'				=> array('UINT', null, 'auto_increment'),
						'imageset_name'				=> array('VCHAR_UNI:255', ''),
						'imageset_copyright'		=> array('VCHAR_UNI', ''),
						'imageset_path'				=> array('VCHAR:100', ''),
					),
					'PRIMARY_KEY'		=> 'imageset_id',
					'KEYS'				=> array(
						'imgset_nm'			=> array('UNIQUE', 'imageset_name'),
					),
				),
				$this->table_prefix . 'styles_imageset_data'	=> array(
					'COLUMNS'		=> array(
						'image_id'				=> array('UINT', null, 'auto_increment'),
						'image_name'			=> array('VCHAR:200', ''),
						'image_filename'		=> array('VCHAR:200', ''),
						'image_lang'			=> array('VCHAR:30', ''),
						'image_height'			=> array('USINT', 0),
						'image_width'			=> array('USINT', 0),
						'imageset_id'			=> array('UINT', 0),
					),
					'PRIMARY_KEY'		=> 'image_id',
					'KEYS'				=> array(
						'i_d'			=> array('INDEX', 'imageset_id'),
					),
				),
				$this->table_prefix . 'styles_template'		=> array(
					'COLUMNS'		=> array(
						'template_id'			=> array('UINT', null, 'auto_increment'),
						'template_name'			=> array('VCHAR_UNI:255', ''),
						'template_copyright'	=> array('VCHAR_UNI', ''),
						'template_path'			=> array('VCHAR:100', ''),
						'bbcode_bitfield'		=> array('VCHAR:255', 'kNg='),
						'template_storedb'		=> array('BOOL', 0),
						'template_inherits_id'		=> array('UINT:4', 0),
						'template_inherit_path'		=> array('VCHAR', ''),
					),
					'PRIMARY_KEY'	=> 'template_id',
					'KEYS'			=> array(
						'tmplte_nm'				=> array('UNIQUE', 'template_name'),
					),
				),
				$this->table_prefix . 'styles_template_data'	=> array(
					'COLUMNS'		=> array(
						'template_id'			=> array('UINT', 0),
						'template_filename'		=> array('VCHAR:100', ''),
						'template_included'		=> array('TEXT', ''),
						'template_mtime'		=> array('TIMESTAMP', 0),
						'template_data'			=> array('MTEXT_UNI', ''),
					),
					'KEYS'			=> array(
						'tid'					=> array('INDEX', 'template_id'),
						'tfn'					=> array('INDEX', 'template_filename'),
					),
				),
				$this->table_prefix . 'styles_theme'			=> array(
					'COLUMNS'		=> array(
						'theme_id'				=> array('UINT', null, 'auto_increment'),
						'theme_name'			=> array('VCHAR_UNI:255', ''),
						'theme_copyright'		=> array('VCHAR_UNI', ''),
						'theme_path'			=> array('VCHAR:100', ''),
						'theme_storedb'			=> array('BOOL', 0),
						'theme_mtime'			=> array('TIMESTAMP', 0),
						'theme_data'			=> array('MTEXT_UNI', ''),
					),
					'PRIMARY_KEY'	=> 'theme_id',
					'KEYS'			=> array(
						'theme_name'		=> array('UNIQUE', 'theme_name'),
					),
				),
			),
		);
	}
}
