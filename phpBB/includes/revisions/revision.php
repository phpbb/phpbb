<?php

class phpbb_revisions_revision
{
	/**
	 * @var dbal Database object
	 */
	private $db;
	/**
	 * @var phpbb_template Template object
	 */
	private $template;
	/**
	 * @var string Relative root path of phpBB directory
	 */
	private $phpbb_root_path;
	/**
	 * @var string PHP file extension
	 */
	private $phpEx;

	/**
	* @var int Revision ID
	*/
	private $id;
	/**
	* @var int Post ID
	*/
	private $post;
	/**
	* @var string Revision subject
	*/
	private $subject;
	/**
	* @var string Unparsed text
	*/
	private $text_raw;
	/**
	* @var string Text that has been run through decode_message()
	*/
	/**
	 * @var string Parsed text
	 */
	private $text;
	/**
	 * @var int Revision time
	 */
	private $time;
	/**
	 * @var string BBCode UID
	 */
	private $uid;
	/**
	 * @var string BBCode Bitfield
	 */
	private $bitfield;
	/**
	 * @var int BBCode options
	 */
	private $options;
	/**
	 * @var string Reason for revision
	 */
	private $reason;
	/**
	 * @var int ID of user who made revision
	 */
	private $user;
	/**
	 * @var string Checksum of post text
	 */
	private $checksum;
	/**
	 * @var int Revision has attachment?
	 */
	private $attachment;

	public function __construct($revision_id = 0)
	{
		global $db, $template, $phpbb_root_path, $phpEx;

		$this->db = $db;
		$this->template = $template;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;

		$this->id = (int) $revision_id;
		if ($this->id)
		{
			$this->load();
		}
	}

	/**
	* Load a revision from the database
	*
	* @return null;
	*/
	public function load()
	{
		if (!$this->id)
		{
			return $false;
		}

		$sql = 'SELECT r.*, p.*
			FROM ' . POST_REVISIONS_TABLE . ' r
			LEFT JOIN ' . POSTS_TABLE . ' p
				ON p.post_id = r.post_id
			WHERE r.revision_id = ' . (int) $this->id . '
			ORDER BY r.revision_id DESC';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->set_data($row);
	}

	/**
	* Load the revision directly following this revision.
	*
	* @return phpbb_revisions_revision Next revision object
	*/
	public function load_next()
	{
		$sql = 'SELECT r.*, p.*
			FROM ' . POST_REVISIONS_TABLE . ' r
			LEFT JOIN ' . POSTS_TABLE . " p
				ON p.post_id = r.post_id
			WHERE r.revision_id > {$this->id}
			ORDER BY r.revision_id ASC
			LIMIT 1";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$next = new phpbb_revisions_revision($row['revision_id']);
		$next->set_data($row);
		return $next;
	}

	/**
	* Loads the revision directly before the current revision
	*
	* @return phpbb_revisions_revision Previous revision object
	*/
	public function load_previous()
	{
		$sql = 'SELECT r.*, p.*
			FROM ' . POST_REVISIONS_TABLE . ' r
			LEFT JOIN ' . POSTS_TABLE . " p
				ON p.post_id = r.post_id
			WHERE r.revision_id < {$this->id}
			ORDER BY r.revision_id DESC
			LIMIT 1";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$previous = new phpbb_revisions_revision($row['revision_id']);
		$previous->set_data($row);
		return $previous;
	}

	/**
	* Set data from database directly to class properties
	* 	NOTE: Expects data from revisions table left joined on posts table, like the query in load()
	*
	* @param array $row Data from database
	* @return null
	*/
	public function set_data(array $row)
	{
		$this->subject = $row['revision_subject'];
		$this->bitfield = $row['bbcode_bitfield'];
		$this->uid = $row['bbcode_uid'];

		$this->text_raw = $row['revision_text'];
		$this->text_decoded = decode_message($row['revision_text'], $row['bbcode_uid']);
		$this->options = (($row['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) +
				(($row['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) + 
				(($row['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);
		$this->text = generate_text_for_display($row['revision_text'], $this->bitfield, $this->uid, $this->options);

		$this->attachment = $row['revision_attachment'];
		$this->checksum = md5($this->text_raw);
		$this->reason = $row['revision_reason'];
		$this->user = $row['user_id'];
	}


	/**
	* Calculate and return the diff between two post revisions
	*
	* @return mixed
	*/
	public function compare_to(phpbb_revisions_revision $revision)
	{
		if (empty($this->text_decoded) || empty($revision->text_decoded))
		{
			return false;
		}

		if (!class_exists('diff'))
		{
			include("{$phpbb_root_path}includes/diff/diff.$phpEx");
		}

		$diff = new diff($revision->text_decoded, $this->text_decoded, false);
		return $diff;
	}

	/**
	* Returns the ID of the revision.
	*
	* @return int Revision ID
	*/
	public function get_id()
	{
		return $this->id;
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
