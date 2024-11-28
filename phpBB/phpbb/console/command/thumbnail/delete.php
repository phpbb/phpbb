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
namespace phpbb\console\command\thumbnail;

use phpbb\db\driver\driver_interface;
use phpbb\language\language;
use phpbb\storage\exception\storage_exception;
use phpbb\storage\storage;
use phpbb\user;
use Symfony\Component\Console\Command\Command as symfony_command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class delete extends \phpbb\console\command\command
{
	/**
	* @var driver_interface
	*/
	protected $db;

	/**
	 * @var language
	 */
	protected $language;

	/**
	 * @var storage
	 */
	protected $storage;

	/**
	* phpBB root path
	* @var string
	*/
	protected $phpbb_root_path;

	/**
	* Constructor
	*
	* @param user $user The user object (used to get language information)
	* @param driver_interface $db Database connection
	* @param language $language Language
	* @param storage $storage Storage
	*/
	public function __construct(user $user, driver_interface $db, language $language, storage $storage)
	{
		$this->db = $db;
		$this->language = $language;
		$this->storage = $storage;

		parent::__construct($user);
	}

	/**
	* Sets the command name and description
	*
	* @return void
	*/
	protected function configure()
	{
		$this
			->setName('thumbnail:delete')
			->setDescription($this->language->lang('CLI_DESCRIPTION_THUMBNAIL_DELETE'))
		;
	}

	/**
	* Executes the command thumbnail:delete.
	*
	* Deletes all existing thumbnails and updates the database accordingly.
	*
	* @param InputInterface $input The input stream used to get the argument and verbose option.
	* @param OutputInterface $output The output stream, used for printing verbose-mode and error information.
	*
	* @return int 0 if all is ok, 1 if a thumbnail couldn't be deleted.
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		$io->section($this->language->lang('CLI_THUMBNAIL_DELETING'));

		$sql = 'SELECT COUNT(*) AS nb_missing_thumbnails
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE thumbnail = 1';
		$result = $this->db->sql_query($sql);
		$nb_missing_thumbnails = (int) $this->db->sql_fetchfield('nb_missing_thumbnails');
		$this->db->sql_freeresult($result);

		if ($nb_missing_thumbnails === 0)
		{
			$io->warning($this->language->lang('CLI_THUMBNAIL_NOTHING_TO_DELETE'));
			return symfony_command::SUCCESS;
		}

		$sql = 'SELECT attach_id, physical_filename, extension, real_filename, mimetype
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE thumbnail = 1';
		$result = $this->db->sql_query($sql);

		$progress = $this->create_progress_bar($nb_missing_thumbnails, $io, $output);

		$progress->setMessage($this->language->lang('CLI_THUMBNAIL_DELETING'));

		$progress->start();

		$thumbnail_deleted = array();
		$return = symfony_command::SUCCESS;
		while ($row = $this->db->sql_fetchrow($result))
		{
			try
			{
				$this->storage->delete('thumb_' . $row['physical_filename']);

				$thumbnail_deleted[] = $row['attach_id'];

				if (count($thumbnail_deleted) === 250)
				{
					$this->commit_changes($thumbnail_deleted);
					$thumbnail_deleted = array();
				}

				$progress->setMessage($this->language->lang('CLI_THUMBNAIL_DELETED', $row['real_filename'], $row['physical_filename']));
			} catch (storage_exception $e) {
				$return = symfony_command::FAILURE;
				$progress->setMessage('<error>' . $this->language->lang('CLI_THUMBNAIL_SKIPPED', $row['real_filename'], $row['physical_filename']) . '</error>');

			}

			$progress->advance();
		}
		$this->db->sql_freeresult($result);

		if (!empty($thumbnail_deleted))
		{
			$this->commit_changes($thumbnail_deleted);
		}

		$progress->finish();

		$io->newLine(2);
		$io->success($this->language->lang('CLI_THUMBNAIL_DELETING_DONE'));

		return $return;
	}

	/**
	* Commits the changes to the database
	*
	* @param array $thumbnail_deleted
	*/
	protected function commit_changes(array $thumbnail_deleted)
	{
		$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
				SET thumbnail = 0
				WHERE ' . $this->db->sql_in_set('attach_id', $thumbnail_deleted);
		$this->db->sql_query($sql);
	}
}
