<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_v301rc1 extends phpbb_db_migration
{
	function depends_on()
	{
		return array();
	}

	function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'forums' => array(
					'display_subforum_list' => array('BOOL', 1),
				),
				$this->table_prefix . 'sessions' => array(
					'session_forum_id' => array('UINT', 0),
				),
			),
			'drop_keys' => array(
				$this->table_prefix . 'groups' => array('group_legend'),
			),
			'add_index' => array(
				$this->table_prefix . 'sessions' => array(
					'session_forum_id' => array('session_forum_id'),
				),
				$this->table_prefix . 'groups' => array(
					'group_legend_name' => array('group_legend', 'group_name'),
				),
			),
		);
	}

	function update_data()
	{
		return array(
			array('custom', array(array(&$this, 'fix_unset_last_view_time'))),
			array('custom', array(array(&$this, 'reset_smiley_size'))),
		);
	}

	function fix_unset_last_view_time()
	{
		$sql = 'UPDATE ' . $this->table_prefix . "topics
			SET topic_last_view_time = topic_last_post_time
			WHERE topic_last_view_time = 0";
		$this->sql_query($sql);
	}

	function reset_smiley_size()
	{
		// Update smiley sizes
		$smileys = array('icon_e_surprised.gif', 'icon_eek.gif', 'icon_cool.gif', 'icon_lol.gif', 'icon_mad.gif', 'icon_razz.gif', 'icon_redface.gif', 'icon_cry.gif', 'icon_evil.gif', 'icon_twisted.gif', 'icon_rolleyes.gif', 'icon_exclaim.gif', 'icon_question.gif', 'icon_idea.gif', 'icon_arrow.gif', 'icon_neutral.gif', 'icon_mrgreen.gif', 'icon_e_ugeek.gif');

		foreach ($smileys as $smiley)
		{
			if (file_exists($this->phpbb_root_path . 'images/smilies/' . $smiley))
			{
				list($width, $height) = getimagesize($this->phpbb_root_path . 'images/smilies/' . $smiley);

				$sql = 'UPDATE ' . SMILIES_TABLE . '
					SET smiley_width = ' . $width . ', smiley_height = ' . $height . "
					WHERE smiley_url = '" . $this->db->sql_escape($smiley) . "'";

				$this->sql_query($sql);
			}
		}
	}
}
