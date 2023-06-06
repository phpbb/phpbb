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
use phpbb\event\dispatcher;
use phpbb\language\language;
use phpbb\log\log_interface;
use phpbb\request\request;
use phpbb\messenger\queue;
use phpbb\template\template;
use phpbb\user;

/**
 * Messenger base class
 */
class base
{
	/** @var array */
	protected $additional_headers = [];

	/** @var array */
	protected $addresses = [];

	/** @var config */
	protected $config;

	/** @var dispatcher */
	protected $dispatcher;

	/** @var language */
	protected $language;

	/** @var log_interface */
	protected $log;

	/** @var string */
	protected $msg = '';

	/** @var queue */
	protected $queue;

	/** @var  request */
	protected $request;

	/** @var string */
	protected $subject = '';

	/** @var template */
	protected $template;

	/** @var bool */
	protected $use_queue = true;

	/** @var user */
	protected $user;

	/**
	 * Messenger base class constructor
	 *
	 * @param config $config
	 * @param dispatcher $dispatcher
	 * @param language $language
	 * @param log_interface $log
	 * @param request $request
	 * @param user $user
	 * @param queue $queue
	 */
	function __construct(config $config, dispatcher $dispatcher, language $language, log_interface $log, request $request, user $user, queue $queue)
	{
		$this->config = $config;
		$this->dispatcher = $dispatcher;
		$this->language = $language;
		$this->log = $log;
		$this->request = $request;
		$this->user = $user;
		$this->queue = $queue;

		$this->set_use_queue();
	}

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
	public function reset()
	{
		$this->addresses = [];
		$this->msg = '';
	}

	/**
	 * Set addresses for to/im as available
	 *
	 * @param array $user User row
	 * @return void
	 */
	public function set_addresses($user)
	{
	}

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
	 * @param \phpbb\config\config	$config		Config object
	 * @param \phpbb\user			$user		User object
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

		$subject = $this->email->getSubject();
		$template = $this->template;
		/**
		 * Event to modify the template before parsing
		 *
		 * @event core.modify_notification_template
		 * @var	string							subject		The message subject
		 * @var \phpbb\template\template 		template	The (readonly) template object
		 * @since 3.2.4-RC1
		 * @changed 4.0.0-a1 Added vars: email. Removed vars: method, break.
		 */
		$vars = ['subject', 'template'];
		extract($this->dispatcher->trigger_event('core.modify_notification_template', compact($vars)));

		// Parse message through template
		$message = trim($this->template->assign_display('body'));

		/**
		 * Event to modify notification message text after parsing
		 *
		 * @event core.modify_notification_message
		 * @var	string							message	The message text
		 * @var	string							subject	The message subject
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
	 * @param string	$type	Error type: EMAIL / etc
	 * @param string	$msg	Error message text
	 * @return void
	 */
	public function error($type, $msg)
	{
		// Session doesn't exist, create it
		if (!isset($this->user->session_id) || $this->user->session_id === '')
		{
			$this->user->session_begin();
		}

		$calling_page = html_entity_decode($this->request->server('PHP_SELF'), ENT_COMPAT);
		$message = '<strong>' . $type . '</strong><br><em>' . htmlspecialchars($calling_page, ENT_COMPAT) . '</em><br><br>' . $msg . '<br>';
		$this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'LOG_ERROR_' . $type, false, [$message]);
	}

	/**
	 * Save message data to the messemger file queue
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
		if ($this->template instanceof \phpbb\template\template)
		{
			return;
		}

		$template_environment = new \phpbb\template\twig\environment(
			$this->config,
			$this->phpbb_container->get('filesystem'),
			$this->phpbb_container->get('path_helper'),
			$this->phpbb_container->getParameter('core.template.cache_path'),
			$this->phpbb_container->get('ext.manager'),
			new \phpbb\template\twig\loader(),
			$this->dispatcher,
			[]
		);
		$template_environment->setLexer($this->phpbb_container->get('template.twig.lexer'));

		$this->template = new \phpbb\template\twig\twig(
			$this->phpbb_container->get('path_helper'),
			$this->config,
			new \phpbb\template\context(),
			$template_environment,
			$this->phpbb_container->getParameter('core.template.cache_path'),
			$this->user,
			$this->phpbb_container->get('template.twig.extensions.collection'),
			$this->phpbb_container->get('ext.manager')
		);
	}

	/**
	 * Set template paths to load
	 *
	 * @param string $path_name Email template path name
	 * @param string $paths 	Email template paths
	 * @return void
	 */
	protected function set_template_paths($path_name, $paths)
	{
		$this->setup_template();
		$this->template->set_custom_style($path_name, $paths);
	}
}
