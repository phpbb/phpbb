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
abstract class attachments_base extends \phpbb\feed\base
{
	/**
	* Attachments that may be displayed
	*/
	protected $attachments = array();

	/**
	* Retrieve the list of attachments that may be displayed
	*/
	protected function fetch_attachments()
	{
		$sql_array = array(
			'SELECT'   => 'a.*',
			'FROM'     => array(
				ATTACHMENTS_TABLE => 'a'
			),
			'WHERE'    => 'a.in_message = 0 ',
			'ORDER_BY' => 'a.filetime DESC, a.post_msg_id ASC',
		);

		if (isset($this->topic_id))
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
	* {@inheritDoc}
	*/
	public function open()
	{
		parent::open();
		$this->fetch_attachments();
	}

	/**
	* Get attachments related to a given post
	*
	* @param $post_id  int  Post id
	* @return mixed Attachments related to $post_id
	*/
	public function get_attachments($post_id)
	{
		return $this->attachments[$post_id];
	}
}
