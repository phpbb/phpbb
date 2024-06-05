<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\messenger\method;

use phpbb\config\config;
use phpbb\di\service_collection;
use phpbb\event\dispatcher;
use phpbb\extension\manager;
use phpbb\language\language;
use phpbb\log\log_interface;
use phpbb\path_helper;
use phpbb\request\request;
use phpbb\messenger\queue;
use phpbb\template\assets_bag;
use phpbb\template\twig\lexer;
use phpbb\user;

/**
 * Messenger base class
 */
abstract class base
{
	/** @var array */
	protected $additional_headers = [];

	/** @var assets_bag */
	protected $assets_bag;

	/** @var config */
	protected $config;

	/** @var dispatcher */
	protected $dispatcher;

	/** @var manager */
	protected $ext_manager;

	/** @var language */
	protected $language;

	/** @var log_interface */
	protected $log;

	/** @var string */
	protected $msg = '';

	/** @var queue */
	protected $queue;

	/** @var  path_helper */
	protected $path_helper;

	/** @var string */
	protected $root_path;

	/** @var  request */
	protected $request;

	/** @var string */
	protected $subject = '';

	/** @var \phpbb\template\template */
	protected $template;

	/** @var string */
	protected $template_cache_path;

	/** @var service_collection */
	protected $twig_extensions_collection;

	/** @var lexer */
	protected $twig_lexer;

	/** @var bool */
	protected $use_queue = true;

	/** @var user */
	protected $user;

	/**
	 * Messenger base class constructor
	 *
	 * @param assets_bag $assets_bag
	 * @param config $config
	 * @param dispatcher $dispatcher
	 * @param language $language
	 * @param log_interface $log
	 * @param request $request
	 * @param user $user
	 * @param queue $queue
	 * @param path_helper $path_helper
	 * @param manager $ext_manager
	 * @param service_collection $twig_extensions_collection
	 * @param lexer $twig_lexer
	 * @param string $template_cache_path
	 * @param string $phpbb_root_path
	 */
	function __construct(
		assets_bag $assets_bag,
		config $config,
		dispatcher $dispatcher,
		language $language,
		log_interface $log,
		request $request,
		user $user,
		queue $queue,
		path_helper $path_helper,
		manager $ext_manager,
		service_collection $twig_extensions_collection,
		lexer $twig_lexer,
		$template_cache_path,
		$phpbb_root_path
	)
	{
		$this->assets_bag = $assets_bag;
		$this->config = $config;
		$this->dispatcher = $dispatcher;
		$this->language = $language;
		$this->log = $log;
		$this->request = $request;
		$this->user = $user;
		$this->queue = $queue;
		$this->path_helper = $path_helper;
		$this->ext_manager = $ext_manager;
		$this->twig_extensions_collection = $twig_extensions_collection;
		$this->twig_lexer = $twig_lexer;
		$this->template_cache_path = $template_cache_path;
		$this->root_path = $phpbb_root_path;

		$this->set_use_queue();
	}

	/**
	 * Get messenger method id
	 * @return mixed
	 */
	abstract public function get_id();

	/**
	 * Check if the messenger method is enabled
	 * @return bool
	 */
	abstract public function is_enabled();

	/**
	 * Sets the use of messenger queue flag
	 *
	 * @return void
	 */
	public function set_use_queue($use_queue = true)
	{
		$this->use_queue = $use_queue;
	}

	/**
	 * Resets all the data (address, template file, etc etc) to default
	 *
	 * @return void
	 */
	abstract public function reset();

	/**
	 * Set addresses for to/im as available
	 *
	 * @param array $user User row
	 * @return void
	 */
	abstract public function set_addresses($user);

	/**
	 * Get messenger method fie queue object name
	 * @return string
	 */
	abstract public function get_queue_object_name();

	/**
	 * Set up subject for mail
	 *
	 * @param string	$subject	Email subject
	 * @return void
	 */
	public function subject($subject = '')
	{
		$this->subject = $subject;
	}

	/**
	 * Adds antiabuse headers
	 *
	 * @param config	$config		Config object
	 * @param user		$user		User object
	 * @return void
	 */
	public function anti_abuse_headers($config, $user)
	{
	}

	/**
	 * Set up extra headers
	 *
	 * @param string	$header_name	Email header name
	 * @param string	$header_value	Email header body
	 * @return void
	 */
	public function header($header_name, $header_value)
	{
	}

	/**
	 * Set the reply to address
	 *
	 * @param string	$address	Email "Reply to" address
	 * @return void
	 */
	public function replyto($address)
	{
	}

	/**
	 * Send out messages
	 * @return bool
	 */
	abstract protected function send();

	/**
	 * Send messages from the queue
	 *
	 * @param array $queue_data Queue data array
	 * @return void
	 */
	abstract public function process_queue(&$queue_data);

	/**
	 * Set email template to use
	 *
	 * @param string	$template_file			Email template file name
	 * @param string	$template_lang			Email template language
	 * @param string	$template_path			Email template path
	 * @param string	$template_dir_prefix	Email template directory prefix
	 *
	 * @return bool
	 */
	public function template($template_file, $template_lang = '', $template_path = '', $template_dir_prefix = '')
	{
		$template_dir_prefix = (!$template_dir_prefix || $template_dir_prefix[0] === '/') ? $template_dir_prefix : '/' . $template_dir_prefix;

		$this->setup_template();

		if (!trim($template_file))
		{
			trigger_error('No template file for emailing set.', E_USER_ERROR);
		}

		if (!trim($template_lang))
		{
			// fall back to board default language if the user's language is
			// missing $template_file.  If this does not exist either,
			// $this->template->set_filenames will do a trigger_error
			$template_lang = basename($this->config['default_lang']);
		}

		$ext_template_paths = [
			[
				'name' 		=> $template_lang . '_email',
				'ext_path' 	=> 'language/' . $template_lang . '/email' . $template_dir_prefix,
			],
		];

		if ($template_path)
		{
			$template_paths = [
				$template_path . $template_dir_prefix,
			];
		}
		else
		{
			$template_path = (!empty($this->user->lang_path)) ? $this->user->lang_path : $this->root_path . 'language/';
			$template_path .= $template_lang . '/email';

			$template_paths = [
				$template_path . $template_dir_prefix,
			];

			$board_language = basename($this->config['default_lang']);

			// we can only specify default language fallback when the path is not a custom one for which we
			// do not know the default language alternative
			if ($template_lang !== $board_language)
			{
				$fallback_template_path = (!empty($this->user->lang_path)) ? $this->user->lang_path : $this->root_path . 'language/';
				$fallback_template_path .= $board_language . '/email';

				$template_paths[] = $fallback_template_path . $template_dir_prefix;

				$ext_template_paths[] = [
					'name'		=> $board_language . '_email',
					'ext_path'	=> 'language/' . $board_language . '/email' . $template_dir_prefix,
				];
			}
			// If everything fails just fall back to en template
			if ($template_lang !== 'en' && $board_language !== 'en')
			{
				$fallback_template_path = (!empty($this->user->lang_path)) ? $this->user->lang_path : $this->root_path . 'language/';
				$fallback_template_path .= 'en/email';

				$template_paths[] = $fallback_template_path . $template_dir_prefix;

				$ext_template_paths[] = [
					'name'		=> 'en_email',
					'ext_path'	=> 'language/en/email' . $template_dir_prefix,
				];
			}
		}

		$this->set_template_paths($ext_template_paths, $template_paths);

		$this->template->set_filenames([
			'body'		=> $template_file . '.txt',
		]);

		return true;
	}

	/**
	 * Assign variables to email template
	 *
	 * @param array	$vars	Array of VAR => VALUE to assign to email template
	 * @return void
	 */
	public function assign_vars($vars)
	{
		$this->setup_template();
		$this->template->assign_vars($vars);
	}

	/**
	 * Assign block of variables to email template
	 *
	 * @param string	$blockname	Template block name
	 * @param array		$vars		Array of VAR => VALUE to assign to email template block
	 * @return void
	 */
	public function assign_block_vars($blockname, $vars)
	{
		$this->setup_template();

		$this->template->assign_block_vars($blockname, $vars);
	}

	/**
	 * Prepare message before sending out to the recipients
	 *
	 * @return void
	 */
	public function prepare_message()
	{
		// We add some standard variables we always use, no need to specify them always
		$this->assign_vars([
			'U_BOARD'	=> generate_board_url(),
			'EMAIL_SIG'	=> str_replace('<br />', "\n", "-- \n" . html_entity_decode($this->config['board_email_sig'], ENT_COMPAT)),
			'SITENAME'	=> html_entity_decode($this->config['sitename'], ENT_COMPAT),
		]);

		$subject = $this->subject;
		$template = $this->template;
		/**
		 * Event to modify the template before parsing
		 *
		 * @event core.modify_notification_template
		 * @var	string	subject		The message subject
		 * @var string	template	The (readonly) template object
		 * @since 3.2.4-RC1
		 * @changed 4.0.0-a1 Removed vars: method, break.
		 */
		$vars = ['subject', 'template'];
		extract($this->dispatcher->trigger_event('core.modify_notification_template', compact($vars)));

		// Parse message through template
		$message = trim($this->template->assign_display('body'));

		/**
		 * Event to modify notification message text after parsing
		 *
		 * @event core.modify_notification_message
		 * @var	string	message	The message text
		 * @var	string	subject	The message subject
		 * @since 3.1.11-RC1
		 * @changed 4.0.0-a1 Removed vars: method, break.
		 */
		$vars = ['message', 'subject'];
		extract($this->dispatcher->trigger_event('core.modify_notification_message', compact($vars)));

		$this->subject = $subject;
		$this->msg = $message;
		unset($subject, $message, $template);

		// Because we use \n for newlines in the body message we need to fix line encoding errors for those admins who uploaded email template files in the wrong encoding
		$this->msg = str_replace("\r\n", "\n", $this->msg);

		// We now try and pull a subject from the email body ... if it exists,
		// do this here because the subject may contain a variable
		$drop_header = '';
		$match = [];
		if (preg_match('#^(Subject):(.*?)$#m', $this->msg, $match))
		{
			$this->subject = (trim($match[2]) != '') ? trim($match[2]) : (($this->subject != '') ? $this->subject : $this->language->lang('NO_EMAIL_SUBJECT'));
			$drop_header .= '[\r\n]*?' . preg_quote($match[0], '#');
		}
		else
		{
			$this->subject = (($this->subject != '') ? $this->subject : $this->language->lang('NO_EMAIL_SUBJECT'));
		}

		if (preg_match('#^(List-Unsubscribe):(.*?)$#m', $this->msg, $match))
		{
			$drop_header .= '[\r\n]*?' . preg_quote($match[0], '#');
			$this->additional_headers[$match[1]] = trim($match[2]);
		}

		if ($drop_header)
		{
			$this->msg = trim(preg_replace('#' . $drop_header . '#s', '', $this->msg));
		}
	}

	/**
	 * Add error message to log
	 *
	 * @param string	$msg	Error message text
	 * @return void
	 */
	public function error($msg)
	{
		// Session doesn't exist, create it
		if (!isset($this->user->session_id) || $this->user->session_id === '')
		{
			$this->user->session_begin();
		}

		$type = strtoupper($this->get_queue_object_name());
		$calling_page = html_entity_decode($this->request->server('PHP_SELF'), ENT_COMPAT);
		$message = '<strong>' . $type . '</strong><br><em>' . htmlspecialchars($calling_page, ENT_COMPAT) . '</em><br><br>' . $msg . '<br>';
		$this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'LOG_ERROR_' . $type, false, [$message]);
	}

	/**
	 * Save message data to the messenger file queue
	 * @return void
	 */
	public function save_queue()
	{
		if ($this->use_queue && !empty($this->queue))
		{
			$this->queue->save();
			return;
		}
	}

	/**
	 * Setup template engine
	 * @return void
	 */
	protected function setup_template()
	{
		if (isset($this->template) && $this->template instanceof \phpbb\template\template)
		{
			return;
		}

		$template_environment = new \phpbb\template\twig\environment(
			$this->assets_bag,
			$this->config,
			new \phpbb\filesystem\filesystem(),
			$this->path_helper,
			$this->template_cache_path,
			$this->ext_manager,
			new \phpbb\template\twig\loader(),
			$this->dispatcher,
			[]
		);
		$template_environment->setLexer($this->twig_lexer);

		$this->template = new \phpbb\template\twig\twig(
			$this->path_helper,
			$this->config,
			new \phpbb\template\context(),
			$template_environment,
			$this->template_cache_path,
			$this->user,
			$this->twig_extensions_collection,
			$this->ext_manager
		);
	}

	/**
	 * Set template paths to load
	 *
	 * @param string|array $path_name	Email template path name
	 * @param string|array $paths		Email template paths
	 * @return void
	 */
	protected function set_template_paths($path_name, $paths)
	{
		$this->setup_template();
		$this->template->set_custom_style($path_name, $paths);
	}
}
