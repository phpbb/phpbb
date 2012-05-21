<?php

class phpbb_revisions_core
{
	private $db;
	private $template;

	public function __construct()
	{
		global $db, $template;
		$this->db = $db;
		$this->template = $template;
	}

	/**
	* Retrieve all revisions from a specified post from database
	*
	* @return array Array of phpbb_revisions_revision objects containing data about revisions to the specified post
	*/
	public function load_post_revisions($post_id = 0)
	{
		if (!$post_id)
		{
			return false;
		}

		$revisions = array();

		$sql = 'SELECT r.*, p.*
			FROM ' . POST_REVISIONS_TABLE . ' r
			LEFT JOIN ' . POSTS_TABLE . ' p
				ON p.post_id = r.post_id
			ORDER BY revision_id DESC';
		$result = $this->db->sql_query($sql);
		while($row = $this->db->sql_fetchrow($result))
		{
			$rev = new phpbb_revisions_revision($row['revision_id']);
			$rev->set_data($row);
			$revisions[] = $rev;
		}
		$this->db->sql_freeresult($result);

		return $revisions;
	}
}
