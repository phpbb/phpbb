<?php
/**
*
* @package phpBB3-Akismet
* @copyright (c) 2012 Nathaniel Guse
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (!class_exists('Akismet'))
{
	global $phpbb_root_path, $phpEx;

	include($phpbb_root_path . 'includes/akismet/Akismet.class.' . $phpEx);
}

class phpbb_akismet extends Akismet
{
	/**
	* Construct wrapper for phpBB
	*
	* @param mixed $phpbb_config phpBB's config array (normally the global $config)
	* @param mixed $phpbb_user phpBB's user object (normally the global $user)
	*/
	public function __construct()
	{
		global $config, $user;

		if (!isset($config['phpbb_akismet_version']) || !$config['phpbb_akismet_enabled'] || !$config['phpbb_akismet_key'])
		{
			return;
		}

		$host = (strpos($user->host, 'http://')) ? $user->host : 'http://' . $user->host;

		parent::__construct($host, $config['phpbb_akismet_key']);

		$this->reset();
	}

	/**
	* Reset the base comment data to what is supplied via phpBB
	*/
	public function reset()
	{
		$this->setUserIP(''); // IP address of the comment submitter.
		$this->setCommentUserAgent(''); // User agent string of the web browser submitting the comment - typically the HTTP_USER_AGENT cgi variable. Not to be confused with the user agent of your Akismet library.
		$this->setReferrer(''); // The content of the HTTP_REFERER header should be sent here.

		$this->setPermalink(''); // The permanent location of the entry the comment was submitted to.
		$this->setCommentType('comment'); // May be blank, comment, trackback, pingback, or a made up value like "registration".
		$this->setCommentAuthor(''); // Name submitted with the comment
		$this->setCommentAuthorEmail(''); // Email address submitted with the comment
		$this->setCommentAuthorURL(''); // URL submitted with comment
		$this->setCommentContent(''); // The content that was submitted.
	}

	/**
	* Set some variables from the browser's data automatically
	*/
	public function set_from_browser_data()
	{
		global $user;

		$this->setUserIP($user->ip); // IP address of the comment submitter.
		$this->setCommentUserAgent($user->browser); // User agent string of the web browser submitting the comment - typically the HTTP_USER_AGENT cgi variable. Not to be confused with the user agent of your Akismet library.
		$this->setReferrer($user->referer); // The content of the HTTP_REFERER header should be sent here.
	}

	/**
	* Check if a comment is spam or not
	*
	* @param string $comment
	* @return bool
	*/
	public function isCommentSpam($comment)
	{
		global $user;

		$this->reset();
		$this->set_from_browser_data();

		$this->setCommentAuthor((string) $user->data['username']); // Name submitted with the comment
		$this->setCommentAuthorEmail((string) $user->data['user_email']); // Email address submitted with the comment
		$this->setCommentContent((string) $comment); // The content that was submitted.

		return parent::isCommentSpam();
	}

	/**
	* Report posts as spam
	*
	* @param array $post_ids
	*/
	public function report_spam($post_ids)
	{
		$this->report('spam', $post_ids);
	}

	/**
	* Report posts as ham
	*
	* @param array $post_ids
	*/
	public function report_ham($post_ids)
	{
		$this->report('ham', $post_ids);
	}

	/**
	* Report posts
	*
	* @param array $post_ids
	*/
	private function report($mode, $post_ids)
	{
		global $db;

		if (!is_array($post_ids))
		{
			$post_ids = array($post_ids);
		}

		$post_ids = array_map('intval', $post_ids);

		$sql = 'SELECT * FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
			WHERE ' . $db->sql_in_set('post_id', $post_ids) . '
				AND ' . (($mode == 'spam') ? 'akismet_spam' : 'akismet_ham') . ' <> 1
				AND u.user_id = p.poster_id';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$comment = $row['post_text'];
			decode_message($comment, $row['bbcode_uid']);

			$this->reset();

			$this->setUserIP($row['poster_ip']);
			$this->setCommentAuthor($row['username']); // Name submitted with the comment
			$this->setCommentAuthorEmail($row['user_email']); // Email address submitted with the comment
			$this->setCommentContent($comment); // The content that was submitted.

			if ($mode == 'spam')
			{
				// Mark as spam in the database
				$this->akismet_spam($row['post_id']);

				$this->submitSpam();
			}
			else
			{
				// Mark as ham in the database
				$this->akismet_ham($row['post_id']);

				$this->submitHam();
			}
		}
		$db->sql_freeresult($result);
	}

	/**
	* Mark a post as spam
	*
	* @param mixed $post_id
	*/
	public function akismet_spam($post_id)
	{
		global $db;

		if (!$post_id)
		{
			return false;
		}

		$sql = 'UPDATE ' . POSTS_TABLE . '
			SET akismet_spam = 1,
				akismet_ham = 0
			WHERE post_id = ' . (int) $post_id;
		$db->sql_query($sql);

		return $db->sql_affectedrows();
	}

	/**
	* Mark a post as ham
	*
	* @param mixed $post_id
	*/
	public function akismet_ham($post_id)
	{
		global $db;

		if (!$post_id)
		{
			return false;
		}

		$sql = 'UPDATE ' . POSTS_TABLE . '
			SET akismet_ham = 1,
				akismet_spam = 0
			WHERE post_id = ' . (int) $post_id;
		$db->sql_query($sql);

		return $db->sql_affectedrows();
	}

	/**
	* Test an API key
	*
	* @param mixed $api_key
	* @return bool
	*/
	public function isKeyValid($api_key)
	{
		global $user;

		$host = (strpos($user->host, 'http://')) ? $user->host : 'http://' . $user->host;

		$akismet = new Akismet($host, $api_key);

		return $akismet->isKeyValid();
	}
}