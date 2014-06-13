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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

class delete  extends \phpbb\console\command\command
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/**
	* phpBB root path
	* @var string
	*/
	protected $phpbb_root_path;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface $db Database connection
	* @param \phpbb\user $user The user object (used to get language information)
	* @param string $phpbb_root_path Root path
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, $phpbb_root_path)
	{
		$this->db = $db;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		parent::__construct();
	}

	/**
	* Sets the command name and description
	*
	* @return null
	*/
	protected function configure()
	{
		$this
			->setName('thumbnail:delete')
			->setDescription($this->user->lang('CLI_DESCRIPTION_THUMBNAIL_DELETE'))
		;
	}

	/**
	* Executes the command thumbnail:delete.
	*
	* Delete all the existing thumbnails (and update the database in consequences).
	*
	* @param InputInterface $input The input stream used to get the argument and verbose option.
	* @param OutputInterface $output The output stream, used for printing verbose-mode and error information.
	*
	* @return int 0 if all is ok, 1 if a thumbnail couldn't be deleted.
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$sql = 'SELECT attach_id, physical_filename, extension, real_filename, mimetype
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE thumbnail = 1';
		$result = $this->db->sql_query($sql);

		$thumbnail_deleted = array();
		$return = 0;
		while ($row = $this->db->sql_fetchrow($result))
		{
			$thumbnail_path = $this->phpbb_root_path . 'files/thumb_' . $row['physical_filename'];

			if (@unlink($thumbnail_path))
			{
				$thumbnail_deleted[] = $row['attach_id'];
				if ($input->getOption('verbose'))
				{
					$output->writeln($this->user->lang('THUMBNAIL_DELETED', $row['real_filename'], $row['physical_filename']));
				}
			}
			else
			{
				if ($input->getOption('verbose'))
				{
					$return = 1;
					$output->writeln('<error>' . $this->user->lang('THUMBNAIL_SKIPPED', $row['real_filename'], $row['physical_filename']) . '</error>');
				}
			}
		}
		$this->db->sql_freeresult($result);

		if (sizeof($thumbnail_deleted))
		{
			$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
				SET thumbnail = 0
				WHERE ' . $this->db->sql_in_set('attach_id', $thumbnail_deleted);
			$this->db->sql_query($sql);
		}

		return $return;
	}
}
