<?php

class phpbb_revisions_post
{
	private $db;

	private $post_id;
	private $forum_id;
	private $poster_id;
	private $revisions = array();

	/**
	* Constructor, initialize some class properties
	*/
	public function __construct($id = 0)
	{
		global $db;
		$this->db = $db;

		$this->post_id = (int) $id;
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

		$sql = 'SELECT r.*, p.*
			FROM ' . POST_REVISIONS_TABLE . ' r
			LEFT JOIN ' . POSTS_TABLE . ' p
				ON p.post_id = r.post_id
			WHERE p.post_id = ' . (int) $this->post_id . '
			ORDER BY revision_id DESC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rev = new phpbb_revisions_revision($row['revision_id'], false);
			$rev->set_data($row);
			$this->revisions[] = $rev;
			$this->poster_id = $this->poster_id ?: $row['poster_id'];
			$this->forum_id = $this->forum_id ?: $row['forum_id'];
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
}
