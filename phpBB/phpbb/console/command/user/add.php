<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\console\command\user;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class add extends \phpbb\console\command\command
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\passwords\manager */
	protected $password_manager;

	/**
	* Construct method
	*
	* @param \phpbb\user $user The user object used for language information
	* @param \phpbb\db\driver\driver_interface $db The database in wich will be inserted the user
	* @param \phpbb\config\config $config The config object used to get default language and timezone
	* @param \phpbb\passwords\manager $password_manager The password manager used to store the user's password
	*/
	public function __construct(\phpbb\user $user, \phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\passwords\manager $password_manager)
	{
		$this->db = $db;
		$this->config = $config;
		$this->password_manager = $password_manager;

		$user->add_lang('ucp');
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
			->setName('user:add')
			->setDescription($this->user->lang('CLI_DESCRIPTION_USER_ADD'))
			->addOption('username', null, InputOption::VALUE_REQUIRED, $this->user->lang('CLI_DESCRIPTION_USER_ADD_OPTION_USERNAME'))
			->addOption('password', null, InputOption::VALUE_REQUIRED, $this->user->lang('CLI_DESCRIPTION_USER_ADD_OPTION_PASSWORD'))
			->addOption('email', null, InputOption::VALUE_REQUIRED, $this->user->lang('CLI_DESCRIPTION_USER_ADD_OPTION_EMAIL'))
		;
	}

	/**
	* Executes the command user:add
	*
	* If not given in option, asks the username, password and email.
	* Then a new user is added in the database, with language and timezone found in the $config passed to the constructor, and the group_id found in the database.
	*
	* @param InputInterface $input The input stream used to get the options
	* @param OutputInterface $output The output stream, used to print messages
	*
	* @return int 0 if all is well, 1 if a database error occured while trying to get the group_id
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$dialog = $this->getHelperSet()->get('dialog');

		$username = $input->getOption('username');
		if (!$username) {
			$username = $dialog->ask(
				$output,
				$this->user->lang('USERNAME') . $this->user->lang('COLON') . ' ',
				null
			);
		}

		$password = $input->getOption('password');
		if (!$password)
		{
			$password = $this->get_password($output, $dialog);
		}

		$email = $input->getOption('email');
		if (!$email)
		{
			$email = $dialog->ask(
				$output,
				$this->user->lang('EMAIL_ADDRESS') . $this->user->lang('COLON') . ' ',
				null
			);
		}

		try
		{
			$group_id = $this->get_group_id();
		}
		catch (\RunTimeException $e)
		{
			$output->writeln($e->getMessage());
			return 1;
		}

		$user_row = array(
			'username'				=> $username,
			'user_password'			=> $this->password_manager->hash($password),
			'user_email'			=> $email,
			'group_id'				=> $group_id,
			'user_timezone'			=> $this->config['board_timezone'],
			'user_lang'				=> $this->config['default_lang'],
			'user_type'				=> USER_NORMAL,
			'user_regdate'			=> time(),
		);

		if (!function_exists('user_add'))
		{
			require_once dirname(__FILE__) . '/../../../../includes/functions_user.php';
		}
		user_add($user_row);

		$output->writeln('<info>' . $this->user->lang('SUCCESS_ADD_USER', $username) . '</info>');
		return 0;
	}

	/**
	* Get the password
	*
	* Asks a password to the user and asks for confirmation.
	* This is repeted while the two are not the same
	*
	* @param OutputInterface $output The output stream, where messages are printed
	* @param \Symfony\Component\Console\Helper\DialogHelper $dialog The dialog helper used to get answers to questions asked to the user
	*
	* @return null
	*/
	protected function get_password($output, $dialog)
	{
		$current_user = $this->user;
		return $dialog->askHiddenResponseAndValidate(
			$output,
			$current_user->lang('PASSWORD') . $current_user->lang('COLON') . ' ',
			function ($answer) use ($dialog, $output, $current_user)
			{
				$confirm = $dialog->askHiddenResponse(
					$output,
					$current_user->lang('CONFIRM_PASSWORD') . $current_user->lang('COLON') . ' ',
					null
				);
				if ($confirm != $answer)
				{
					throw new \RunTimeException($current_user->lang('NEW_PASSWORD_ERROR'));
				}
				return $answer;
			},
			false,
			null
		);
	}

	/**
	* Get the group id
	*
	* Go and find in the database the group_id corresponding to 'REGISTERED'
	*
	* @throws \RunTimeException if the group id does not exist in database.
	* @return null
	*/
	protected function get_group_id()
	{
		$sql = 'SELECT group_id
			FROM ' . GROUPS_TABLE . "
			WHERE group_name = '" . $this->db->sql_escape('REGISTERED') . "'
				AND group_type = " . GROUP_SPECIAL;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row || !$row['group_id'])
		{
			throw new \RunTimeException($this->user->lang('NO_GROUP'));
		}

		return $row['group_id'];
	}
}
