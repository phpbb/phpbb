<?php
/**
*
* @package phpBB3
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\feed;

/**
* Abstract class for feeds displaying attachments
*
* @package phpBB3
*/
abstract class attachments_base extends \phpbb\feed\base
{
	/**
	* Attachments that may be displayed
	*/
	protected $attachments = array();

	function open()
	{
		$this->fetch_attachments();
	}

	/**
	* Retrieve the list of attachments that may be displayed
	*/
	function fetch_attachments()
	{
		global $db;

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
			$sql_array['WHERE'] .= 'AND a.topic_id = ' . (int)$this->topic_id;
		}
		else if (isset($this->forum_id))
		{
			$sql_array['LEFT_JOIN'] = array(
				array(
					'FROM' => array(TOPICS_TABLE => 't'),
					'ON'   => 'a.topic_id = t.topic_id',
				)
			);
			$sql_array['WHERE'] .= 'AND t.forum_id = ' . (int)$this->forum_id;
		}

		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);

		// Set attachments in feed items
		while ($row = $db->sql_fetchrow($result))
		{
			$this->attachments[$row['post_msg_id']][] = $row;
		}
		$db->sql_freeresult($result);
	}

	/**
	* Get attachments related to a given post
	*
	* @param $post_id  Post id
	* @return mixed    Attachments related to $post_id
	*/
	function get_attachments($post_id)
	{
		return $this->attachments[$post_id];
	}
}
