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
namespace phpbb\console\command\fixup;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class update_hashes extends \phpbb\console\command\command
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\passwords\manager */
	protected $passwords_manager;

	/** @var string Default hashing type */
	protected $default_type;

	/**
	 * Update_hashes constructor
	 *
	 * @param \phpbb\config\config $config
	 * @param \phpbb\user $user
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\passwords\manager $passwords_manager
	 * @param array $hashing_algorithms Hashing driver
	 *			service collection
	 * @param array $defaults Default password types
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\user $user,
								\phpbb\db\driver\driver_interface $db, \phpbb\passwords\manager $passwords_manager,
								$hashing_algorithms, $defaults)
	{
		$this->config = $config;
		$this->db = $db;

		$this->passwords_manager = $passwords_manager;

		foreach ($defaults as $type)
		{
			if ($hashing_algorithms[$type]->is_supported())
			{
				$this->default_type = $type;
				break;
			}
		}

		parent::__construct($user);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName('fixup:update-hashes')
			->setDescription($this->user->lang('CLI_DESCRIPTION_UPDATE_HASH_BCRYPT'))
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// Get count to be able to display progress
		$sql = 'SELECT COUNT(user_id) AS count
				FROM ' . USERS_TABLE . '
				WHERE user_password ' . $this->db->sql_like_expression('$H$' . $this->db->get_any_char()) . '
					OR user_password ' . $this->db->sql_like_expression('$CP$' . $this->db->get_any_char());
		$result = $this->db->sql_query($sql);
		$total_update_passwords = $this->db->sql_fetchfield('count');
		$this->db->sql_freeresult($result);

		// Create progress bar
		$progress_bar = new ProgressBar($output, $total_update_passwords);
		$progress_bar->start();

		$sql = 'SELECT user_id, user_password
				FROM ' . USERS_TABLE . '
				WHERE user_password ' . $this->db->sql_like_expression('$H$' . $this->db->get_any_char()) . '
					OR user_password ' . $this->db->sql_like_expression('$CP$' . $this->db->get_any_char());
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$old_hash = preg_replace('/^\$CP\$/', '', $row['user_password']);
			$new_hash = $this->passwords_manager->hash($old_hash, array($this->default_type));

			$sql = 'UPDATE ' . USERS_TABLE . "
					SET user_password = '" . $this->db->sql_escape($new_hash) . "'
					WHERE user_id = " . (int) $row['user_id'];
			$this->db->sql_query($sql);
			$progress_bar->advance();
		}

		$this->config->set('update_hashes_last_cron', time());

		$progress_bar->finish();

		$output->writeln('<info>' . $this->user->lang('CLI_FIXUP_UPDATE_HASH_BCRYPT_SUCCESS') . '</info>');
	}
}
