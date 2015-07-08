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
use Symfony\Component\Console\Output\OutputInterface;

class generate extends \phpbb\console\command\command
{
	/**
	* @var \phpbb\db\driver\driver_interface
	*/
	protected $db;

	/**
	* @var \phpbb\cache\service
	*/
	protected $cache;

	/**
	* phpBB root path
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
	* Constructor
	*
	* @param \phpbb\user $user The user object (used to get language information)
	* @param \phpbb\db\driver\driver_interface $db Database connection
	* @param \phpbb\cache\service $cache The cache service
	* @param string $phpbb_root_path Root path
	* @param string $php_ext PHP extension
	*/
	public function __construct(\phpbb\user $user, \phpbb\db\driver\driver_interface $db, \phpbb\cache\service $cache, $phpbb_root_path, $php_ext)
	{
		$this->db = $db;
		$this->cache = $cache;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

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
			->setName('thumbnail:generate')
			->setDescription($this->user->lang('CLI_DESCRIPTION_THUMBNAIL_GENERATE'))
		;
	}

	/**
	* Executes the command thumbnail:generate.
	*
	* Generate a thumbnail for all attachments which need one and don't have it yet.
	*
	* @param InputInterface $input The input stream used to get the argument and verboe option.
	* @param OutputInterface $output The output stream, used for printing verbose-mode and error information.
	*
	* @return int 0.
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$sql = 'SELECT COUNT(*) AS nb_missing_thumbnails
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE thumbnail = 0';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$nb_missing_thumbnails = (int) $row['nb_missing_thumbnails'];
		if ($nb_missing_thumbnails === 0)
		{
			$output->writeln('<info>' . $this->user->lang('NO_THUMBNAIL_TO_GENERATE') . '</info>');
			return 0;
		}

		$extensions = $this->cache->obtain_attach_extensions(true);

		$sql = 'SELECT attach_id, physical_filename, extension, real_filename, mimetype
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE thumbnail = 0';
		$result = $this->db->sql_query($sql);

		if (!function_exists('create_thumbnail'))
		{
			require($this->phpbb_root_path . 'includes/functions_posting.' . $this->php_ext);
		}

		if (!$input->getOption('verbose'))
		{
			$progress = $this->getHelper('progress');
			$progress->start($output, $nb_missing_thumbnails);
		}

		$thumbnail_created = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (isset($extensions[$row['extension']]['display_cat']) && $extensions[$row['extension']]['display_cat'] == ATTACHMENT_CATEGORY_IMAGE)
			{
				$source = $this->phpbb_root_path . 'files/' . $row['physical_filename'];
				$destination = $this->phpbb_root_path . 'files/thumb_' . $row['physical_filename'];

				if (create_thumbnail($source, $destination, $row['mimetype']))
				{
					$thumbnail_created[] = (int) $row['attach_id'];

					if (sizeof($thumbnail_created) === 250)
					{
						$this->commit_changes($thumbnail_created);
						$thumbnail_created = array();
					}

					if ($input->getOption('verbose'))
					{
						$output->writeln($this->user->lang('THUMBNAIL_GENERATED', $row['real_filename'], $row['physical_filename']));
					}
				}
				else
				{
					if ($input->getOption('verbose'))
					{
						$output->writeln('<info>' . $this->user->lang('THUMBNAIL_SKIPPED', $row['real_filename'], $row['physical_filename']) . '</info>');
					}
				}
			}

			if (!$input->getOption('verbose'))
			{
				$progress->advance();
			}
		}
		$this->db->sql_freeresult($result);

		if (!empty($thumbnail_created))
		{
			$this->commit_changes($thumbnail_created);
		}

		if (!$input->getOption('verbose'))
		{
			$progress->finish();
		}

		return 0;
	}

	/**
	* Commits the changes to the database
	*
	* @param array $thumbnail_created
	*/
	protected function commit_changes(array $thumbnail_created)
	{
		$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
				SET thumbnail = 1
				WHERE ' . $this->db->sql_in_set('attach_id', $thumbnail_created);
		$this->db->sql_query($sql);
	}
}
