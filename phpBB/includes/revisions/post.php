<?php
/**
*
* @package phpbb_revisions
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* A class representing a single post, containing the revisions made to that post
*
* @package phpbb_revisions
*/
class phpbb_revisions_post
{
	/**
	* phpBB DBAL Object
	* @var dbal
	*/
	private $db;

	/**
	* Config array
	* @var array
	*/
	private $config;

	/**
	* Post ID number
	* @var int
	*/
	private $post_id;

	/**
	* Array of data for the post
	* @var array
	*/
	private $post_data;

	/**
	* Array of revisions for the post
	* @var array
	*/
	private $revisions;
	
	/**
	* Constructor, initialize some class properties
	*/
	public function __construct($id = 0)
	{
		global $db;
		$this->db = $db;

		$this->post_id = (int) $id;

		$this->get_post_data();
	}

	/**
	* Load post data into an array
	*
	* @param bool $refresh If true, the data will be reloaded whether it has been loaded already or not
	* @return array Array of post data from database
	*/
	public function get_post_data($refresh = false)
	{
		return $refresh || empty($post_data) ? $this->load_post_data() : $post_data;
	}

	/**
	* Load post data into an array
	*
	* @return array Array of post data from database
	*/
	private function load_post_data()
	{
		if (!$this->post_id)
		{
			return false;
		}

		$sql_ary = array(
			'SELECT'	=> 'p.*, u.*, r.*',

			'FROM'		=> array(
				POSTS_TABLE		=> 'p',
			),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'u.user_id = p.poster_id',
				),
				array(
					'FROM'	=> array(RANKS_TABLE => 'r'),
					'ON'	=> 'r.rank_id = u.user_rank',
				),
			),

			'WHERE'		=> 'p.post_id = ' . (int) $this->post_id,
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		$this->post_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->post_data['user_sig_options'] = ($this->post_data['enable_bbcode'] ? OPTION_FLAG_BBCODE : 0) +
										($this->post_data['enable_smilies'] ? OPTION_FLAG_SMILIES : 0) +
										($this->post_data['enable_magic_url'] ? OPTION_FLAG_LINKS : 0);
		$this->post_data['user_sig_parsed'] = generate_text_for_display($this->post_data['user_sig'], $this->post_data['user_sig_bbcode_uid'], $this->post_data['user_sig_bbcode_bitfield'], $this->post_data['user_sig_options']);

		return $this->post_data;
	}

	/**
	* Return revision array
	*
	* @param bool $refresh If true, the data will be reloaded whether it has been loaded already or not
	* @return array Array of phpbb_revisions_revision objects containing data about revisions to the specified post
	*/
	public function get_revisions($refresh = false)
	{
		return $refresh || empty($this->revisions) ? $this->load_revisions() : $this->revisions;
	}

	/**
	* Retrieve all revisions from a specified post from database
	*
	* @return array Array of phpbb_revisions_revision objects containing data about revisions to the specified post
	*/
	private function load_revisions()
	{
		if (!$this->post_id)
		{
			return false;
		}

		$sql_ary = array(
			'SELECT'	=> 'r.*, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height',

			'FROM'		=> array(
				POST_REVISIONS_TABLE	=> 'r',
			),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'u.user_id = r.user_id',
				),
			),

			'WHERE'		=> 'r.post_id = ' . (int) $this->post_id,

			'ORDER_BY'	=> 'r.revision_id DESC',
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
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

		uasort($this->revisions, array('phpbb_revisions_post', 'sort_post_revisions'));

		return $this->revisions;
	}

	/**
	* Revert the post to a different revision. The revision must be linked to this object's post id
	*
	* @param int $new_revision_id ID of the revision to switch to
	* @return int Numerical error code based on error (see constants.php for number values)
	*/
	public function revert($new_revision_id)
	{
		if (!$this->post_id || empty($this->revisions) || empty($this->revisions[$new_revision_id]))
		{
			return REVISION_NOT_FOUND;
		}

		$this->db->sql_transaction('begin');

		// We need to create a new revision with the current post information.
		// We do this even when revision tracking is off; otherwise, the current
		// version of the post will be lost when reverting because it is not already
		// stored as a revision, but as the post itself
		$sql_insert_ary = array(
			'post_id'				=> $this->post_data['post_id'],
			'user_id'				=> $this->post_data['poster_id'],
			'revision_time'			=> time(),
			'revision_subject'		=> $this->post_data['post_subject'],
			'revision_text'			=> $this->post_data['post_text'],
			'revision_checksum'		=> $this->post_data['post_checksum'],
			'revision_attachment'	=> $this->post_data['post_attachment'],
			'bbcode_bitfield'		=> $this->post_data['bbcode_bitfield'],
			'bbcode_uid'			=> $this->post_data['bbcode_uid'],
			'revision_reason'		=> $this->post_data['post_edit_reason'],
		);

		$sql = 'INSERT INTO ' . POST_REVISIONS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_insert_ary);
		if (!$this->db->sql_query($sql))
		{
			return REVISION_INSERT_FAIL;
		}

		// But we do want to make sure we only have the maximum number of revisions allowed on a post
		if ($config['max_revisions_per_post'] && ($remove_amount = sizeof($this->revisions) - $config['max_revisions_per_post']))
		{
			// Delete the oldest one(s) until there aren't more than the max amount
			$sql = 'DELETE FROM ' . POST_REVISIONS_TABLE . '
				WHERE post_id = ' . (int) $this->post_id . '
				ORDER BY revision_time ASC';
			$db->sql_query_limit($sql, $remove_amount);
		}

		$new_revision = $this->revisions[$new_revision_id];

		// Next, we update the post table with the information from the new revision
		$sql_update_ary = array(
			'post_edit_user'	=> (int) $new_revision->get('user'),
			'post_edit_time'	=> (int) $new_revision->get('time'),
			'post_subject'		=> $new_revision->get('subject'),
			'post_text'			=> $new_revision->get('text_raw'),
			'post_checksum'		=> $new_revision->get('checksum'),
			'post_attachment'	=> (int) $new_revision->get('attachment'),
			'bbcode_bitfield'	=> $new_revision->get('bitfield'),
			'bbcode_uid'		=> $new_revision->get('uid'),
			'post_edit_reason'	=> $new_revision->get('reason'),
			'post_edit_count'	=> (int) $this->post_data['post_edit_count'] + 1,
		);

		$sql = 'UPDATE ' . POSTS_TABLE . '
			SET ' . $this->db->sql_build_array('UPDATE', $sql_update_ary) . '
			WHERE post_id = ' . (int) $this->post_id;
		if (!$this->db->sql_query($sql))
		{
			return REVISION_POST_UPDATE_FAIL;
		}

		$this->db->sql_transaction('commit');

		return REVISION_REVERT_SUCCESS;
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
