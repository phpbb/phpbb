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
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\cache\service */
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
	* @param \phpbb\db\driver\driver_interface $db Database connection
	* @param \phpbb\user $user The user object (used to get language information)
	* @param \phpbb\cache\service $cache The cache service
	* @param string $phpbb_root_path Root path
	* @param string $php_ext PHP extension
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\cache\service $cache, $phpbb_root_path, $php_ext)
	{
		$this->db = $db;
		$this->user = $user;
		$this->cache = $cache;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
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
			->setName('thumbnail:generate')
			->setDescription($this->user->lang('CLI_DESCRIPTION_THUMBNAIL_GENERATE'))
		;
	}

	/**
	* Executes the command thumbnail:generate.
	*
	* @param InputInterface $input The input stream used to get the argument and verboe option.
	* @param OutputInterface $output The output stream, used for printing verbose-mode and error information.
	*
	* @return int 0.
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$extensions = $this->cache->obtain_attach_extensions(true);

		$sql = 'SELECT attach_id, physical_filename, extension, real_filename, mimetype
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE thumbnail = 0';
		$result = $this->db->sql_query($sql);

		if (!function_exists('create_thumbnail'))
		{
			require($this->phpbb_root_path . 'includes/functions_posting.' . $this->php_ext);
		}

		$thumbnail_created = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (isset($extensions[$row['extension']]['display_cat']) && $extensions[$row['extension']]['display_cat'] == ATTACHMENT_CATEGORY_IMAGE)
			{
				$source = $this->phpbb_root_path . 'files/' . $row['physical_filename'];
				$destination = $this->phpbb_root_path . 'files/thumb_' . $row['physical_filename'];

				if (!create_thumbnail($source, $destination, $row['mimetype']))
				{
					if ($input->getOption('verbose'))
					{
						$output->writeln('<info>' . $this->user->lang('THUMBNAIL_SKIPPED', $row['real_filename'], $row['physical_filename']) . '</info>');
					}
				}
				else
				{
					$thumbnail_created[] = $row['attach_id'];
					if ($input->getOption('verbose'))
					{
						$output->writeln($this->user->lang('THUMBNAIL_GENERATED', $row['real_filename'], $row['physical_filename']));
					}
				}
			}
		}
		$this->db->sql_freeresult($result);

		if (sizeof($thumbnail_created))
		{
			$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
				SET thumbnail = 1
				WHERE ' . $this->db->sql_in_set('attach_id', $thumbnail_created);
			$this->db->sql_query($sql);
		}

		return 0;
	}
}
