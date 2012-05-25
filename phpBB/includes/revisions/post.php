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

		$sql = 'SELECT *
			FROM ' . POSTS_TABLE . '
			WHERE post_id = ' . (int) $this->post_id;
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

		return $this->revisions;
	}

	/**
	* Returns the ID of the post
	*
	* @return int Post ID
	*/
	public function get_post_id()
	{
		return $this->get('post_id');
	}

	/**
	* Returns the ID of the poster of the post
	*
	* @return int Poster ID
	*/
	public function get_poster_id()
	{
		return $this->get('poster_id');
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

		return null;
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
		$a_order = $a->get('id');
		$b_order = $b->get('id');

		if ($a_order == $b_order)
		{
			return 0;
		}

		return ($a_order > $b_order) ? 1 : -1;
	}
}
