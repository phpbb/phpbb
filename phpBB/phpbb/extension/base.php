<?php
/**
*
* @package extension
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\extension;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* A base class for extensions without custom enable/disable/purge code.
*
* @package extension
*/
class base implements \phpbb\extension\extension_interface
{
	/** @var ContainerInterface */
	protected $container;

	/** @var \phpbb\extension\finder */
	protected $finder;

	/** @var \phpbb\db\migrator */
	protected $migrator;

	/** @var string */
	protected $extension_name;

	/** @var string */
	protected $extension_path;

	/**
	* Constructor
	*
	* @param ContainerInterface $container Container object
	* @param \phpbb\extension\finder $extension_finder
	* @param string $extension_name Name of this extension (from ext.manager)
	* @param string $extension_path Relative path to this extension
	*/
	public function __construct(ContainerInterface $container, \phpbb\extension\finder $extension_finder, \phpbb\db\migrator $migrator, $extension_name, $extension_path)
	{
		$this->container = $container;
		$this->extension_finder = $extension_finder;
		$this->migrator = $migrator;

		$this->extension_name = $extension_name;
		$this->extension_path = $extension_path;
	}

	/**
	* Single enable step that installs any included migrations
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return false Indicates no further steps are required
	*/
	public function enable_step($old_state)
	{
		$migrations = $this->get_migration_file_list();

		$this->migrator->set_migrations($migrations);

		$this->migrator->update();

		return !$this->migrator->finished();
	}

	/**
	* Single disable step that does nothing
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return false Indicates no further steps are required
	*/
	public function disable_step($old_state)
	{
		return false;
	}

	/**
	* Single purge step that reverts any included and installed migrations
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return false Indicates no further steps are required
	*/
	public function purge_step($old_state)
	{
		$migrations = $this->get_migration_file_list();

		$this->migrator->set_migrations($migrations);

		foreach ($migrations as $migration)
		{
			while ($this->migrator->migration_state($migration) !== false)
			{
				$this->migrator->revert($migration);

				return true;
			}
		}

		return false;
	}

	/**
	* Get the list of migration files from this extension
	*
	* @return array
	*/
	protected function get_migration_file_list()
	{
		static $migrations = false;

		if ($migrations !== false)
		{
			return $migrations;
		}

		// Only have the finder search in this extension path directory
		$migrations = $this->extension_finder
			->extension_directory('/migrations')
			->find_from_extension($this->extension_name, $this->extension_path);
		$migrations = $this->extension_finder->get_classes_from_files($migrations);

		return $migrations;
	}
}
