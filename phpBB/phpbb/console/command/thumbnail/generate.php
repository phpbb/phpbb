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
use Symfony\Component\Console\Style\SymfonyStyle;

class generate extends \phpbb\console\command\command
{
	/**
	* @var \phpbb\config\config
	*/
	protected $config;

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
	* @param \config\config $config The config
	* @param \phpbb\user $user The user object (used to get language information)
	* @param \phpbb\db\driver\driver_interface $db Database connection
	* @param \phpbb\cache\service $cache The cache service
	* @param string $phpbb_root_path Root path
	* @param string $php_ext PHP extension
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\user $user, \phpbb\db\driver\driver_interface $db, \phpbb\cache\service $cache, $phpbb_root_path, $php_ext)
	{
		$this->config = $config;
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
		$io = new SymfonyStyle($input, $output);

		$io->section($this->user->lang('CLI_THUMBNAIL_GENERATING'));

		$sql = 'SELECT COUNT(*) AS nb_missing_thumbnails
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE thumbnail = 0';
		$result = $this->db->sql_query($sql);
		$nb_missing_thumbnails = (int) $this->db->sql_fetchfield('nb_missing_thumbnails');
		$this->db->sql_freeresult($result);

		if ($nb_missing_thumbnails === 0)
		{
			$io->warning($this->user->lang('CLI_THUMBNAIL_NOTHING_TO_GENERATE'));
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

		$progress = $this->create_progress_bar($nb_missing_thumbnails, $io, $output);

		$progress->setMessage($this->user->lang('CLI_THUMBNAIL_GENERATING'));

		$progress->start();

		$thumbnail_created = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (isset($extensions[$row['extension']]['display_cat']) && $extensions[$row['extension']]['display_cat'] == ATTACHMENT_CATEGORY_IMAGE)
			{
				$source = $this->phpbb_root_path . $this->config['upload_path'] . '/' . $row['physical_filename'];
				$destination = $this->phpbb_root_path . $this->config['upload_path'] . '/thumb_' . $row['physical_filename'];

				if (create_thumbnail($source, $destination, $row['mimetype']))
				{
					$thumbnail_created[] = (int) $row['attach_id'];

					if (count($thumbnail_created) === 250)
					{
						$this->commit_changes($thumbnail_created);
						$thumbnail_created = array();
					}

					$progress->setMessage($this->user->lang('CLI_THUMBNAIL_GENERATED', $row['real_filename'], $row['physical_filename']));
				}
				else
				{
					$progress->setMessage('<info>' . $this->user->lang('CLI_THUMBNAIL_SKIPPED', $row['real_filename'], $row['physical_filename']) . '</info>');
				}
			}

			$progress->advance();
		}
		$this->db->sql_freeresult($result);

		if (!empty($thumbnail_created))
		{
			$this->commit_changes($thumbnail_created);
		}

		$progress->finish();

		$io->newLine(2);
		$io->success($this->user->lang('CLI_THUMBNAIL_GENERATING_DONE'));

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
