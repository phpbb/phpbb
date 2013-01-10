<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_style_update_p2 extends phpbb_db_migration
{
	public function depends_on()
	{
		return array('phpbb_db_migration_data_style_update_p1');
	}

	public function update_schema()
	{
		return array(
			'drop_columns'	=> array(
				STYLES_TABLE		=> array(
					'imageset_id',
					'template_id',
					'theme_id',
				),
			),

			'drop_tables'	=> array(
				STYLES_IMAGESET_TABLE,
				STYLES_IMAGESET_DATA_TABLE,
				STYLES_TEMPLATE_TABLE,
				STYLES_TEMPLATE_DATA_TABLE,
				STYLES_THEME_TABLE,
			),
		);
	}

	public function update_data()
	{
		return array();
	}
}
