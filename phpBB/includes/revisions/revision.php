<?php

class phpbb_revisions_revision
{
	/**
	* Database object
	* @var dbal
	*/
	private $db;

	/**
	* Template object
	* @var phpbb_template
	*/
	private $template;

	/**
	* Relative root path of phpBB directory
	* @var string
	*/
	private $phpbb_root_path;

	/**
	* PHP file extension
	* @var string
	*/
	private $phpEx;

	/**
	* Revision ID
	* @var int
	*/
	private $id;

	/**
	* Post ID
	* @var int
	*/
	private $post;

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
	private $user;

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
	* Constructor method
	*
	* @param int $revision_id ID of the revision to instantiate
	* @param bool $autoload Whether or not to automatically load data if constructor receives ID
	*				This is helpful if we already have the data and don't want to load it but still
	*				wish to set the data to this instance
	*/
	public function __construct($revision_id = 0, $autoload = true)
	{
		global $db, $template, $phpbb_root_path, $phpEx;

		$this->db = $db;
		$this->template = $template;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;

		$this->id = (int) $revision_id;
		if ($this->id && $autoload)
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
		if (!$this->id)
		{
			return false;
		}

		$sql = 'SELECT r.*, p.*, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height
			FROM ' . POST_REVISIONS_TABLE . ' r
			LEFT JOIN ' . POSTS_TABLE . ' p
				ON p.post_id = r.post_id
			LEFT JOIN ' . USERS_TABLE . ' u
				ON u.user_id = r.user_id
			WHERE r.revision_id = ' . (int) $this->id . '
			ORDER BY r.revision_id DESC';
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
	* Load the revision directly following this revision.
	*
	* @return phpbb_revisions_revision Next revision object
	*/
	public function load_next()
	{
		$sql = 'SELECT revision_id
			FROM ' . POST_REVISIONS_TABLE . '
			WHERE revision_id > ' . (int) $this->id . '
			ORDER BY revision_id ASC
			LIMIT 1';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		
		return new phpbb_revisions_revision($row['revision_id']);
	}

	/**
	* Loads the revision directly before the current revision
	*
	* @return phpbb_revisions_revision Previous revision object
	*/
	public function load_previous()
	{
		$sql = 'SELECT revision_id
			FROM ' . POST_REVISIONS_TABLE . '
			WHERE revision_id < ' . (int) $this->id . '
			ORDER BY revision_id DESC
			LIMIT 1';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return new phpbb_revisions_revision($row['revision_id']);;
	}

	/**
	* Set data from database directly to class properties
	* 	NOTE: Expects data from revisions table left joined on posts table, like the query in load()
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
		$this->text_for_edit = generate_text_for_edit($row['revision_text'], $row['bbcode_uid'], $this->options);
		$this->text_decoded = $this->text_for_edit['text'];
		$this->text = generate_text_for_display($row['revision_text'], $row['bbcode_bitfield'], $row['bbcode_uid'], $this->options);


		$this->attachment = $row['revision_attachment'];
		$this->checksum = md5($this->text_raw);
		$this->reason = $row['revision_reason'];
		
		$this->user = $row['user_id'];
		$this->username = get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);
		
		$this->avatar = $row['user_avatar'];
		$this->avatar_type = $row['user_avatar_type'];
		$this->avatar_height = $row['user_avatar_height'];
		$this->avatar_width = $row['user_avatar_width'];

		$this->poster_id = $row['poster_id'];
		$this->forum_id = $row['forum_id'];
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
		$height = $height ?: $this->get('avatar_height');
		$width = $width ?: $this->get('avatar_width');
		return get_user_avatar($this->get('avatar'), $this->get('avatar_type'), $width, $height);
	}

	/**
	* Returns the ID of the revision.
	*
	* @return int Revision ID
	*/
	public function get_id()
	{
		return $this->get('id');
	}

	/**
	* Returns the ID of the poster.
	*
	* @return int Revision ID
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
	* Sets the ID of the revision
	*
	* @return null
	*/
	public function set_id($id = 0)
	{
		$this->id = $id;
	}
}
