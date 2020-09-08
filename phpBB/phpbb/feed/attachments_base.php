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

namespace phpbb\feed;

/**
* Abstract class for feeds displaying attachments
*/
abstract class attachments_base extends base
{
	/**
	* Attachments that may be displayed
	*/
	protected $attachments = array();

	/**
	* Retrieve the list of attachments that may be displayed
	*
	* @param array $post_ids Specify for which post IDs to fetch the attachments (optional)
	* @param array $topic_ids Specify for which topic IDs to fetch the attachments (optional)
	*/
	protected function fetch_attachments($post_ids = array(), $topic_ids = array())
	{
		$sql_array = array(
			'SELECT'   => 'a.*',
			'FROM'     => array(
				ATTACHMENTS_TABLE => 'a'
			),
			'WHERE'    => 'a.in_message = 0 ',
			'ORDER_BY' => 'a.filetime DESC, a.post_msg_id ASC',
		);

		if (!empty($post_ids))
		{
			$sql_array['WHERE'] .= 'AND ' . $this->db->sql_in_set('a.post_msg_id', $post_ids);
		}
		else if (!empty($topic_ids))
		{
			if (isset($this->topic_id))
			{
				$topic_ids[] = $this->topic_id;
			}

			$sql_array['WHERE'] .= 'AND ' . $this->db->sql_in_set('a.topic_id', $topic_ids);
		}
		else if (isset($this->topic_id))
		{
			$sql_array['WHERE'] .= 'AND a.topic_id = ' . (int) $this->topic_id;
		}
		else if (isset($this->forum_id))
		{
			$sql_array['LEFT_JOIN'] = array(
				array(
					'FROM' => array(TOPICS_TABLE => 't'),
					'ON'   => 'a.topic_id = t.topic_id',
				)
			);
			$sql_array['WHERE'] .= 'AND t.forum_id = ' . (int) $this->forum_id;
		}
		else
		{
			// Do not allow querying the full attachments table
			throw new \RuntimeException($this->user->lang('INVALID_FEED_ATTACHMENTS'));
		}

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		// Set attachments in feed items
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->attachments[$row['post_msg_id']][] = $row;
		}
		$this->db->sql_freeresult($result);
	}

	/**
	* Get attachments related to a given post
	*
	* @param	int	$post_id		Post id
	* @return	mixed	Attachments related to $post_id
	*/
	public function get_attachments($post_id)
	{
		return $this->attachments[$post_id];
	}
}
