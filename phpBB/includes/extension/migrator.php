<?php
/**
*
* @package extension
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* The extension manager provides means to activate/deactivate extensions.
*
* @package extension
*/
class phpbb_extension_migrator
{
	protected $db;
	protected $config;
	protected $php_ext;
	protected $phpbb_root_path;

	/**
	* Creates a manager and loads information from database
	*
	* @param phpbb_extension_manager $manager The extension manager
	* @param phpbb_db_migrator $migrator Migrator to perform the extension changes
	*/
	public function __construct(phpbb_extension_manager $manager, phpbb_db_migrator $migrator)
	{
		$this->manager = $manager;
		$this->migrator = $migrator;
	}

	/**
	* Runs a step of the extension enabling process.
	*
	* Allows the exentension to enable in a long running script that works
	* in multiple steps across requests. State is kept for the extension
	* in the extensions table.
	*
	* @param	string	$name	The extension's name
	* @return	bool			False if enabling is finished, true otherwise
	*/
	public function enable_step($name)
	{
		$extension_data = $this->manager->get_extension_data($name);

		// ignore extensions that are already enabled
		if (isset($extension_data) && $extension_data['ext_active'])
		{
			return false;
		}

		$old_state = (isset($extension_data['ext_state'])) ? unserialize($extension_data['ext_state']) : false;

		// Returns false if not completed
		if (!$this->handle_migrations($name, 'enable'))
		{
			return true;
		}

		$extension = $this->manager->get_extension($name);
		$state = $extension->enable_step($old_state);

		$active = ($state === false);

		$extension_data = array(
			'ext_name'		=> $name,
			'ext_active'	=> $active,
			'ext_state'		=> serialize($state),
		);

		$this->manager->set_extension_data($name, $extension_data, false);

		return !$active;
	}

	/**
	* Enables an extension
	*
	* This method completely enables an extension. But it could be long running
	* so never call this in a script that has a max_execution time.
	*
	* @param string $name The extension's name
	* @return null
	*/
	public function enable($name)
	{
		while ($this->enable_step($name));
	}

	/**
	* Disables an extension
	*
	* Calls the disable method on the extension's meta class to allow it to
	* process the event.
	*
	* @param string $name The extension's name
	* @return bool False if disabling is finished, true otherwise
	*/
	public function disable_step($name)
	{
		$extension_data = $this->manager->get_extension_data($name);

		// ignore extensions that are already disabled
		if (!isset($extension_data) || !$extension_data['ext_active'])
		{
			return false;
		}

		$old_state = unserialize($extension_data['ext_state']);

		$extension = $this->manager->get_extension($name);
		$state = $extension->disable_step($old_state);

		// continue until the state is false
		if ($state !== false)
		{
			$this->manager->set_extension_data($name, array(
				'ext_state'		=> serialize($state),
			));

			return true;
		}

		$this->manager->set_extension_data($name, array(
			'ext_active'	=> false,
			'ext_state'		=> serialize(false),
		));

		return false;
	}

	/**
	* Disables an extension
	*
	* Disables an extension completely at once. This process could run for a
	* while so never call this in a script that has a max_execution time.
	*
	* @param string $name The extension's name
	* @return null
	*/
	public function disable($name)
	{
		while ($this->disable_step($name));
	}

	/**
	* Purge an extension
	*
	* Disables the extension first if active, and then calls purge on the
	* extension's meta class to delete the extension's database content.
	*
	* @param string $name The extension's name
	* @return bool False if purging is finished, true otherwise
	*/
	public function purge_step($name)
	{
		$extension_data = $this->manager->get_extension_data($name);

		// ignore extensions that do not exist
		if (!isset($extension_data))
		{
			return false;
		}

		// disable first if necessary
		if ($extension_data['ext_active'])
		{
			$this->disable($name);
		}

		$old_state = unserialize($extension_data['ext_state']);

		// Returns false if not completed
		if (!$this->handle_migrations($name, 'purge'))
		{
			return true;
		}

		$extension = $this->manager->get_extension($name);
		$state = $extension->purge_step($old_state);

		// continue until the state is false
		if ($state !== false)
		{

			$this->manager->set_extension_data($name, array(
				'ext_state'		=> serialize($state),
			));

			return true;
		}

		$this->manager->set_extension_data($name, null);

		return false;
	}

	/**
	* Purge an extension
	*
	* Purges an extension completely at once. This process could run for a while
	* so never call this in a script that has a max_execution time.
	*
	* @param string $name The extension's name
	* @return null
	*/
	public function purge($name)
	{
		while ($this->purge_step($name));
	}

	/**
	* Handle installing/reverting migrations
	*
	* @param string $extension_name Name of the extension
	* @param string $mode enable or purge
	* @return bool True if completed, False if not completed
	*/
	protected function handle_migrations($extension_name, $mode)
	{
		$extensions = array(
			$extension_name => $this->manager->get_extension_path($extension_name, true),
		);

		$finder = $this->manager->get_finder();
		$migrations = array();
		$file_list = $finder
			->extension_directory('/migrations')
			->find_from_paths($extensions);

		if (empty($file_list))
		{
			return true;
		}

		foreach ($file_list as $file)
		{
			$migrations[$file['named_path']] = $file['ext_name'];
		}
		$migrations = $finder->get_classes_from_files($migrations);
		$this->migrator->set_migrations($migrations);

		// What is a safe limit of execution time? Half the max execution time should be safe.
		$safe_time_limit = (ini_get('max_execution_time') / 2);
		$start_time = time();

		if ($mode == 'enable')
		{
			while (!$this->migrator->finished())
			{
				$this->migrator->update();

				// Are we approaching the time limit? If so we want to pause the update and continue after refreshing
				if ((time() - $start_time) >= $safe_time_limit)
				{
					return false;
				}
			}
		}
		else if ($mode == 'purge')
		{
			foreach ($migrations as $migration)
			{
				while ($this->migrator->migration_state($migration) !== false)
				{
					$this->migrator->revert($migration);

					// Are we approaching the time limit? If so we want to pause the update and continue after refreshing
					if ((time() - $start_time) >= $safe_time_limit)
					{
						return false;
					}
				}
			}
		}

		return true;
	}
}
