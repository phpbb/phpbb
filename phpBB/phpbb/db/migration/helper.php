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

namespace phpbb\db\migration;

/**
* The migrator is responsible for applying new migrations in the correct order.
*/
class helper
{
	/**
	 * Get the schema steps from an array of schema changes
	 *
	 * This splits up $schema_changes into individual changes so that the
	 * changes can be chunked
	 *
	 * @param array $schema_changes from migration
	 * @return array
	 */
	public function get_schema_steps($schema_changes)
	{
		$steps = array();

		// Nested level of data (only supports 1/2 currently)
		$nested_level = array(
			'drop_tables'		=> 1,
			'add_tables'		=> 1,
			'change_columns'	=> 2,
			'add_columns'		=> 2,
			'drop_keys'			=> 2,
			'drop_columns'		=> 2,
			'add_primary_keys'	=> 2, // perform_schema_changes only uses one level, but second is in the function
			'add_unique_index'	=> 2,
			'add_index'			=> 2,
		);

		foreach ($nested_level as $change_type => $data_depth)
		{
			if (!empty($schema_changes[$change_type]))
			{
				foreach ($schema_changes[$change_type] as $key => $value)
				{
					if ($data_depth === 1)
					{
						$steps[] = array(
							'dbtools.perform_schema_changes', array(array(
									$change_type	=> array(
										(!is_int($key)) ? $key : 0	=> $value,
								),
							)),
						);
					}
					else if ($data_depth === 2)
					{
						foreach ($value as $key2 => $value2)
						{
							$steps[] = array(
								'dbtools.perform_schema_changes', array(array(
									$change_type	=> array(
										$key => array(
											$key2	=> $value2,
										),
									),
								)),
							);
						}
					}
				}
			}
		}

		return $steps;
	}
}
