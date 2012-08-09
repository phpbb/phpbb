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
* A class representing a single post revision
*
* @package phpbb_revisions
*/
class phpbb_revisions_revision
{
	/**
	* Database object
	* @var dbal
	*/
	private $db;

	/**
	* Revision ID
	* @var int
	*/
	private $revision_id;

	/**
	* Post ID
	* @var int
	*/
	private $post_id;

	/**
	* Revision subject
	* @var string
	*/
	private $subject;

	/**
	* Unparsed text
	* @var string
	*/
	private $text_raw;

	/**
	* Text that has been run through decode_message()
	* @var string
	*/
	private $text_decoded;

	/**
	* Parsed text
	* @var string
	*/
	private $text;

	/**
	* Revision time
	* @var int
	*/
	private $time;

	/**
	* BBCode UID
	* @var string
	*/
	private $uid;

	/**
	* BBCode Bitfield
	* @var string
	*/
	private $bitfield;

	/**
	* BBCode options
	* @var int
	*/
	private $options;

	/**
	* Reason for revision
	* @var string
	*/
	private $reason;

	/**
	* ID of user who made revision
	* @var int
	*/
	private $user_id;

	/**
	* Checksum of post text
	* @var string
	*/
	private $checksum;

	/**
	* Revision has attachment?
	* @var int
	*/
	private $attachment;

	/**
	* ID of the poster (not necessarily who made the revision)
	* @var int
	*/
	private $poster_id;

	/**
	* ID of the forum
	* @var int
	*/
	private $forum_id;

	/**
	* Username of user who made the revision
	* @var string
	*/
	private $username;

	/**
	* Avatar of user who made the revision
	* @var string
	*/
	private $avatar;

	/**
	* Whether or not the revision contains the current version of the post
	* @var bool
	*/
	private $is_current = false;

	/**
	* Whether or not the revision is marked as protected, i.e. will not be deleted in pruning functions
	* @var bool
	*/
	private $is_protected = false;

	/**
	* Constructor method
	*
	* @param int $revision_id ID of the revision to instantiate
	* @param bool $autoload Whether or not to automatically load data if constructor receives ID
	*				This is helpful if we already have the data and don't want to load it but still
	*				wish to set the data to this instance
	*/
	public function __construct($revision_id, dbal $db, $autoload = true)
	{
		$this->db = $db;

		$this->revision_id = (int) $revision_id;
		if ($this->revision_id && $autoload)
		{
			$this->load();
		}
	}

	/**
	* Load a revision from the database
	*
	* @return phpbb_revisions_revision Current revision object
	*/
	public function load()
	{
		if (!$this->revision_id)
		{
			return false;
		}

		$sql_ary = array(
			'SELECT'	=> 'r.*, p.*, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height',

			'FROM'		=> array(
				POST_REVISIONS_TABLE	=> 'r',
			),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(POSTS_TABLE => 'p'),
					'ON'	=> 'p.post_id = r.post_id',
				),
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'u.user_id = r.user_id',
				),
			),

			'WHERE'		=> 'r.revision_id = ' . $this->revision_id,

			'ORDER_BY'	=> 'r.revision_id DESC',
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!empty($row))
		{
			$this->set_data($row);
		}

		return $this;
	}

	/**
	* Set data from database directly to class properties
	* 	NOTE: Expects data from revisions table, posts table, and users table, like the query in load()
	*
	* @param array $row Data from database
	* @return null
	*/
	public function set_data($row)
	{
		$this->subject = $row['revision_subject'];
		$this->text_raw = $row['revision_text'];
		$this->options = (($row['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) +
				(($row['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) + 
				(($row['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);
		$this->bitfield = $row['bbcode_bitfield'];
		$this->uid = $row['bbcode_uid'];

		$text_for_edit = generate_text_for_edit($this->text_raw, $this->uid, $this->options);
		$this->text_decoded = $text_for_edit['text'];
		$this->text = generate_text_for_display($this->text_raw, $this->uid, $this->bitfield, $this->options);

		$this->time = $row['revision_time'];
		$this->attachment = $row['revision_attachment'];
		$this->checksum = md5($this->text_raw);
		$this->reason = $row['revision_reason'];
		
		$this->user_id = $row['user_id'];
		$this->username = get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);
		
		$this->avatar = $row['user_avatar'];
		$this->avatar_type = $row['user_avatar_type'];
		$this->avatar_height = $row['user_avatar_height'];
		$this->avatar_width = $row['user_avatar_width'];

		$this->poster_id = $row['poster_id'];
		$this->forum_id = $row['forum_id'];

		$this->is_current = !empty($row['is_current']);
	}

	/**
	* Deletes the current revision. No authentication is checked,
	* so only call this after checking it yourself.
	*
	* @return null
	*/
	public function delete()
	{
		$this->db->sql_transaction('begin');

		$sql = 'DELETE FROM ' . POST_REVISIONS_TABLE . '
			WHERE revision_id = ' . $this->get_id();
		$this->db->sql_query($sql);

		$sql = 'UPDATE ' . POSTS_TABLE . '
			SET post_revision_count = post_revision_count - 1
			WHERE post_id = ' . $this->get_post_id() . '
				AND post_revision_count > 0';
		$this->db->sql_query($sql);

		$this->db->sql_transaction('commit');
	}

	/**
	* Marks this revision as protected. No authentication is checked,
	* so only call this after checking it yourself.
	*
	* @return null
	*/
	public function protect()
	{
		// If the revision is already protected, let's save a query
		if ($this->is_protected())
		{
			return;
		}

		$sql = 'UPDATE ' . POST_REVISIONS_TABLE . '
			SET revision_protected = 1
			WHERE revision_id = ' . $this->get_id();
		$this->db->sql_query($sql);
	}

	/**
	* Marks this revision as protected. No authentication is checked,
	* so only call this after checking it yourself.
	*
	* @return null
	*/
	public function unprotect()
	{
		// If the revision is already not protected, let's save a query
		if (!$this->is_protected())
		{
			return;
		}

		$sql = 'UPDATE ' . POST_REVISIONS_TABLE . '
			SET revision_protected = 0
			WHERE revision_id = ' . $this->get_id();
		$this->db->sql_query($sql);
	}

	/**
	* Returns the Avatar of the user who made the revision
	*
	* @param int $width Custom width
	* @param int $height Custom height
	* @return string Avatar image string
	*/
	public function get_avatar($width = 0, $height = 0)
	{
		$height = $height ?: $this->avatar_height;
		$width = $width ?: $this->avatar_width;
		return get_user_avatar($this->avatar, $this->avatar_type, $width, $height);
	}

	/**
	* Returns the ID of the revision.
	*
	* @return int Revision ID
	*/
	public function get_id()
	{
		return (int) $this->revision_id;
	}

	/**
	* Returns the post ID associated with this revision
	*
	* @return int Post ID
	*/
	public function get_post_id()
	{
		return (int) $this->post_id;
	}

	/**
	* Returns the ID of the poster.
	*
	* @return int Revision Poster ID
	*/
	public function get_user_id()
	{
		return (int) $this->user_id;
	}

	/**
	* Get the username of the poster.
	*
	* @return string Revision username
	*/
	public function get_username()
	{
		return $this->username;
	}

	/**
	* Returns the Subject of the revision.
	*
	* @return string Revision Subject
	*/
	public function get_subject()
	{
		return $this->subject;
	}

	/**
	* Returns the parsed text associated with this revision
	*
	* @return string Parsed text
	*/
	public function get_text()
	{
		return $this->text;
	}

	/**
	* Returns the decoded text (i.e. as you would see when editing)
	*
	* @return string Decoded text
	*/
	public function get_text_decoded()
	{
		return $this->text_decoded;
	}

	/**
	* Returns the text options (i.e. bbcode, smilies, urls) of the revision.
	*
	* @return int Revision text options
	*/
	public function get_options()
	{
		return (int) $this->options;
	}

	/**
	* Returns the time of the revision
	*
	* @return int Timestamp
	*/
	public function get_time()
	{
		return (int) $this->time;
	}

	/**
	* Returns the revision reason
	*
	* @return string Revision reason
	*/
	public function get_reason()
	{
		return $this->reason;
	}

	/**
	* Returns the revision checksum
	*
	* @return string Revision checksum
	*/
	public function get_checksum()
	{
		return $this->checksum;
	}

	/**
	* Returns the revision bitfield
	*
	* @return string Revision bitfield
	*/
	public function get_bitfield()
	{
		return $this->bitfield;
	}

	/**
	* Returns the revision uid
	*
	* @return string Revision uid
	*/
	public function get_uid()
	{
		return $this->uid;
	}

	/**
	* Returns the revision attachment
	*
	* @return bool Whether or not this revision has an attachment
	*/
	public function get_attachment()
	{
		return (bool) $this->attachment;
	}

	/**
	* Sets the ID of the revision
	*
	* @param int $revision_id Revision ID
	* @return null
	*/
	public function set_id($revision_id)
	{
		$this->revision_id = (int) $revision_id;
	}

	/**
	* Returns whether or not this revision contains the current version of the post
	*
	* @return bool
	*/
	public function is_current()
	{
		return (bool) $this->is_current;
	}

	/**
	* Returns whether or not this revision is marked as protected.
	* Protected revisions are not deleted by automatic prunes, and
	* can only be deleted manually, either en mass via the ACP or
	* individually when viewing a post's revisions.
	*
	* @return bool
	*/
	public function is_protected()
	{
		return (bool) $this->is_protected;
	}
}
