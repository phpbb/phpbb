<?php
/**
*
* @package migration
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class prune_shadow_topics extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_schema()
	{
		return array(
			'add_columns'		=> array(
				$this->table_prefix . 'forums'		=> array(
					'enable_shadow_topic_prune'	=> array('BOOL', 0, 'after' => 'prune_freq'),
					'prune_shadow_topic_days'	=> array('UINT', 7, 'after' => 'enable_shadow_topic_prune'),
					'prune_shadow_topic_freq'	=> array('UINT', 1, 'after' => 'prune_shadow_topic_freq'),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'		=> array(
				$this->table_prefix . 'forums'		=> array(
					'enable_shadow_topic_prune',
					'prune_shadow_topic_days',
					'prune_shadow_topic_freq',
				),
			),
		);
	}
}
