<?php

class phpbb_revisions_post
{
	private $db;

	private $post_id;
	private $post_data;
	private $revisions = array();

	/**
	* Constructor, initialize some class properties
	*/
	public function __construct($id = 0)
	{
		global $db;
		$this->db = $db;

		$this->post_id = (int) $id;

		$this->load_post_data();
	}

	/**
	* Load post data into an array
	*
	* @return array Array of post data from database
	*/
	public function load_post_data()
	{
		if (!$this->post_id)
		{
			return false;
		}

		$sql = 'SELECT p.*, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height
			FROM ' . POSTS_TABLE . ' p
			LEFT JOIN ' . USERS_TABLE . ' u
				ON u.user_id = p.poster_id
			WHERE p.post_id = ' . (int) $this->post_id;
		$result = $this->db->sql_query($sql);
		$this->post_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $this->post_data;
	}


	/**
	* Retrieve all revisions from a specified post from database
	*
	* @return array Array of phpbb_revisions_revision objects containing data about revisions to the specified post
	*/
	public function load_revisions()
	{
		if (!$this->post_id)
		{
			return false;
		}

		if (!empty($this->revisions))
		{
			return $this->revisions;
		}

		$sql = 'SELECT r.*, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height
			FROM ' . POST_REVISIONS_TABLE . ' r
			LEFT JOIN ' . USERS_TABLE . ' u
				ON u.user_id = r.user_id
			WHERE r.post_id = ' . (int) $this->post_id . '
			ORDER BY r.revision_id DESC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			// Some $post_data we already have from loading the post data can be put here
			// because we need it for set_data() in the revision object
			$row['poster_id'] = $this->post_data['poster_id'];
			$row['forum_id'] = $this->post_data['forum_id'];
			$row['enable_bbcode'] = $this->post_data['enable_bbcode'];
			$row['enable_smilies'] = $this->post_data['enable_smilies'];
			$row['enable_magic_url'] = $this->post_data['enable_magic_url'];
			
			$rev = new phpbb_revisions_revision($row['revision_id'], false);
			$rev->set_data($row);
			$this->revisions[$row['revision_id']] = $rev;
		}
		$this->db->sql_freeresult($result);

		// The final revision is the current post state, so we can just put the post data into a revision object
		// We store it as index 0 because it doesn't have a revision ID but we can always know how to access it
		$this->revisions[0] = new phpbb_revisions_revision();
		$this->revisions[0]->set_data(array(
			'revision_subject'		=> $this->post_data['post_subject'],
			'revision_text'			=> $this->post_data['post_text'],
			'revision_checksum'		=> $this->post_data['post_checksum'],
			'poster_id'				=> $this->post_data['poster_id'],
			'user_id'				=> $this->post_data['poster_id'],
			'username'				=> $this->post_data['username'],
			'user_colour'			=> $this->post_data['user_colour'],
			'forum_id'				=> $this->post_data['forum_id'],
			'enable_bbcode'			=> $this->post_data['enable_bbcode'],
			'enable_smilies'		=> $this->post_data['enable_smilies'],
			'enable_magic_url'		=> $this->post_data['enable_magic_url'],
			'bbcode_uid'			=> $this->post_data['bbcode_uid'],
			'bbcode_bitfield'		=> $this->post_data['bbcode_bitfield'],
			'revision_time'			=> $this->post_data['post_time'],
			'revision_attachment'	=> $this->post_data['post_attachment'],
			'user_avatar'			=> $this->post_data['user_avatar'],
			'user_avatar_type'		=> $this->post_data['user_avatar_type'],
			'user_avatar_width'		=> $this->post_data['user_avatar_width'],
			'user_avatar_height'	=> $this->post_data['user_avatar_height'],
			'enable_bbcode'			=> $this->post_data['enable_bbcode'],
			'enable_smilies'		=> $this->post_data['enable_smilies'],
			'enable_magic_url'		=> $this->post_data['enable_magic_url'],
			'revision_reason'		=> $this->post_data['post_edit_reason'],
		));

		return $this->revisions;
	}

	/**
	* Revert the post to a different revision. The revision must be linked to this object's post id
	*
	* @param int $new_revision_id ID of the revision to switch to
	* @return null
	*/
	public function revert($new_revision_id)
	{
		if (!$this->post_id || empty($this->revisions) || empty($this->revisions[$new_revision_id]))
		{
			return false;
		}

		// First, we create a new revision with the current post information
		$sql_insert_ary = array(
			'post_id'				=> $data['post_id'],
			'user_id'				=> $data['poster_id'],
			'revision_time'			=> time(),
			'revision_subject'		=> $data['post_subject'],
			'revision_text'			=> $data['post_text'],
			'revision_checksum'		=> $data['post_checksum'],
			'revision_attachment'	=> $data['post_attachment'],
			'bbcode_bitfield'		=> $data['bbcode_bitfield'],
			'bbcode_uid'			=> $data['bbcode_uid'],
			'revision_reason'		=> $data['post_edit_reason'],
		);
		$sql = 'INSERT INTO ' . POST_REVISIONS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_insert_ary);
		$db->sql_query($sql);

		// Next, we update the post table with the information from the new revision
		$sql_update_ary = array(
			'poster_id'			=> $this->revisions[$new_revision_id]['user_id'],
			'post_edit_time'	=> $this->revisions[$new_revision_id]['revision_time'],
			'post_subject'		=> $this->revisions[$new_revision_id]['revision_subject'],
			'post_text'			=> $this->revisions[$new_revision_id]['revision_text'],
			'post_checksum'		=> $this->revisions[$new_revision_id]['revision_checksum'],
			'post_attachment'	=> $this->revisions[$new_revision_id]['revision_attachment'],
			'bbcode_bitfield'	=> $this->revisions[$new_revision_id]['bbcode_bitfield'],
			'bbcode_uid'		=> $this->revisions[$new_revision_id]['bbcode_uid'],
			'post_reason'		=> $this->revisions[$new_revision_id]['revision_reason'],
		);
		$sql = 'UPDATE ' . POSTS_TABLE . '
			SET ' . $this->db->sql_build_array('UPDATE', $sql_update_ary) . '
			WHERE post_id = ' . $this->revisions[$new_revision_id]['post_id'];
		$db->sql_squery($sql);
	}

	/**
	* Return the value of a specified class property
	*
	* @return mixed Null if property not defined, otherwise the value of the property
	*/
	public function get($property)
	{
		if(isset($this->$property))
		{
			return $this->$property;
		}
	}

	/**
	 * Custom sort function used by usort() to order a post's revisions by ther ID's
	 *
	 * @param phpbb_revisions_revision $a First comparison argument
	 * @param phpbb_revisions_revision $b Second comparison argument
	 * @return int 0 for equal, 1 for a greater than b, -1 for b greater than a
	 */
	static public function sort_post_revisions(phpbb_revisions_revision $a, phpbb_revisions_revision $b)
	{
		$a_order = $a->get('time');
		$b_order = $b->get('time');

		if ($a_order == $b_order)
		{
			return 0;
		}

		return ($a_order > $b_order) ? 1 : -1;
	}
}
