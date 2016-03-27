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

namespace phpbb\console\command\user;

use phpbb\config\config;
use phpbb\console\command\command;
use phpbb\db\driver\driver_interface;
use phpbb\language\language;
use phpbb\log\log_interface;
use phpbb\notification\manager;
use phpbb\user;
use phpbb\user_loader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class activate extends command
{
	/** @var driver_interface */
	protected $db;

	/** @var config */
	protected $config;

	/** @var language */
	protected $language;

	/** @var log_interface */
	protected $log;

	/** @var manager */
	protected $notifications;

	/** @var user_loader */
	protected $user_loader;

	/**
	 * phpBB root path
	 *
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * PHP extension.
	 *
	 * @var string
	 */
	protected $php_ext;

	/**
	 * Construct method
	 *
	 * @param user             $user
	 * @param driver_interface $db
	 * @param config           $config
	 * @param language         $language
	 * @param log_interface    $log
	 * @param manager          $notifications
	 * @param user_loader      $user_loader
	 * @param string           $phpbb_root_path
	 * @param string           $php_ext
	 */
	public function __construct(user $user, driver_interface $db, config $config, language $language, log_interface $log, manager $notifications, user_loader $user_loader, $phpbb_root_path, $php_ext)
	{
		$this->db = $db;
		$this->config = $config;
		$this->language = $language;
		$this->log = $log;
		$this->notifications = $notifications;
		$this->user_loader = $user_loader;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->language->add_lang('acp/users');
		parent::__construct($user);
	}

	/**
	 * Sets the command name and description
	 *
	 * @return null
	 */
	protected function configure()
	{
		$this
			->setName('user:activate')
			->setDescription($this->language->lang('CLI_DESCRIPTION_USER_ACTIVATE'))
			->setHelp($this->language->lang('CLI_HELP_USER_ACTIVATE'))
			->addArgument(
				'username',
				InputArgument::REQUIRED,
				$this->language->lang('CLI_DESCRIPTION_USER_ACTIVATE_USERNAME')
			)
			->addOption(
				'deactivate',
				'd',
				InputOption::VALUE_NONE,
				$this->language->lang('CLI_DESCRIPTION_USER_ACTIVATE_DEACTIVATE')
			)
			->addOption(
				'send-email',
				null,
				InputOption::VALUE_NONE,
				$this->language->lang('CLI_DESCRIPTION_USER_ADD_OPTION_NOTIFY')
			)
		;
	}

	/**
	 * Executes the command user:activate
	 *
	 * Activate (or deactivate) a user account
	 *
	 * @param InputInterface  $input  The input stream used to get the options
	 * @param OutputInterface $output The output stream, used to print messages
	 *
	 * @return int 0 if all is well, 1 if any errors occurred
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		$name = $input->getArgument('username');
		$mode = ($input->getOption('deactivate')) ? 'deactivate' : 'activate';

		$user_id  = $this->user_loader->load_user_by_username($name);
		$user_row = $this->user_loader->get_user($user_id);

		if ($user_row['user_id'] == ANONYMOUS)
		{
			$io->error($this->language->lang('NO_USER'));
			return 1;
		}

		// Check if the user is already active (or inactive)
		if ($mode == 'activate' && $user_row['user_type'] != USER_INACTIVE)
		{
			$io->error($this->language->lang('CLI_DESCRIPTION_USER_ACTIVATE_ACTIVE'));
			return 1;
		}
		else if ($mode == 'deactivate' && $user_row['user_type'] == USER_INACTIVE)
		{
			$io->error($this->language->lang('CLI_DESCRIPTION_USER_ACTIVATE_INACTIVE'));
			return 1;
		}

		// Activate the user account
		if (!function_exists('user_active_flip'))
		{
			require($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		}

		user_active_flip($mode, $user_row['user_id']);

		// Notify the user upon activation
		if ($mode == 'activate' && $this->config['require_activation'] == USER_ACTIVATION_ADMIN)
		{
			$this->send_notification($user_row, $input);
		}

		// Log and display the result
		$msg = ($mode == 'activate') ? 'USER_ADMIN_ACTIVATED' : 'USER_ADMIN_DEACTIVED';
		$log = ($mode == 'activate') ? 'LOG_USER_ACTIVE' : 'LOG_USER_INACTIVE';

		$this->log->add('admin', ANONYMOUS, '', $log, false, array($user_row['username']));
		$this->log->add('user', ANONYMOUS, '', $log . '_USER', false, array(
			'reportee_id' => $user_row['user_id']
		));

		$io->success($this->language->lang($msg));

		return 0;
	}

	/**
	 * Send account activation notification to user
	 *
	 * @param array           $user_row The user data array
	 * @param InputInterface  $input    The input stream used to get the options
	 * @return null
	 */
	protected function send_notification($user_row, InputInterface $input)
	{
		$this->notifications->delete_notifications('notification.type.admin_activate_user', $user_row['user_id']);

		if ($input->getOption('send-email'))
		{
			if (!class_exists('messenger'))
			{
				require($this->phpbb_root_path . 'includes/functions_messenger.' . $this->php_ext);
			}

			$messenger = new \messenger(false);
			$messenger->template('admin_welcome_activated', $user_row['user_lang']);
			$messenger->set_addresses($user_row);
			$messenger->anti_abuse_headers($this->config, $this->user);
			$messenger->assign_vars(array(
					'USERNAME'	=> htmlspecialchars_decode($user_row['username']))
			);

			$messenger->send(NOTIFY_EMAIL);
		}
	}
}
