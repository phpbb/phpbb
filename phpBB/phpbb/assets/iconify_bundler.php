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

namespace phpbb\assets;

use Iconify\JSONTools\Collection;
use phpbb\log\log_interface;

class iconify_bundler
{
	/** @var log_interface */
	protected $log;

	/** @var string[] Icons list */
	protected $icons_list = [];

	/**
	 * Constructor for iconify bundler
	 *
	 * @param log_interface|null $log Logger
	 */
	public function __construct(?log_interface $log)
	{
		$this->log = $log;
	}

	/**
	 * Run iconify bundler
	 *
	 * @return string Iconify bundle
	 */
	public function run()
	{
		// Sort icons first
		sort($this->icons_list, SORT_NATURAL);

		$organized_icons = $this->organize_icons_list();

		$output = $this->load_icons_data($organized_icons);
		if (!$output)
		{
			return '';
		}

		$output = '(function() {
    function add(data) {
        try {
            if (typeof self.Iconify === \'object\' && self.Iconify.addCollection) {
                self.Iconify.addCollection(data);
                return;
            }
            if (typeof self.IconifyPreload === \'undefined\') {
                self.IconifyPreload = [];
            }
            self.IconifyPreload.push(data);
        } catch (err) {
        }
    }
    ' . $output . '
})();' . "\n";

		return $output;
	}

	/**
	 * Add icon to icons list
	 *
	 * @param string $icon_name
	 * @return void
	 */
	protected function add_icon(string $icon_name): void
	{
		if (!in_array($icon_name, $this->icons_list))
		{
			$this->icons_list[] = $icon_name;
		}
	}

	/**
	 * Add multiple icons to icons list
	 *
	 * @param array $icons
	 * @return void
	 */
	public function add_icons(array $icons): void
	{
		foreach ($icons as $icon)
		{
			$this->add_icon($icon);
		}
	}

	/**
	 * Organize icons list by prefix
	 *
	 * Result is an object, where key is prefix, value is array of icon names
	 *
	 * @return array Organized icons list
	 */
	protected function organize_icons_list(): array
	{
		$results = [];

		foreach ($this->icons_list as $icon_name)
		{
			// Split icon to prefix and name
			$icon = $this->name_to_icon($icon_name);
			if ($icon === null)
			{
				// Invalid name or icon name does not have provider
				if ($this->log)
				{
					$this->log->add('critical', ANONYMOUS, '', 'LOG_ICON_INVALID', false, [$icon_name]);
				}
				continue;
			}

			$prefix = $icon['prefix'];
			$name = $icon['name'];

			// Add icon to results
			if (!isset($results[$prefix]))
			{
				$results[$prefix] = [$name];
				continue;
			}
			if (!in_array($name, $results[$prefix]))
			{
				$results[$prefix][] = $name;
			}
		}

		return $results;
	}

	/**
	 * Convert icon name from string to object.
	 *
	 * Object properties:
	 * - provider (ignored in this example)
	 * - prefix
	 * - name
	 *
	 * This function was converted to PHP from @iconify/utils/src/icon/name.ts
	 * See https://github.com/iconify/iconify/blob/master/packages/utils/src/icon/name.ts
	 *
	 * @param string $icon_name Icon name
	 * @return array|null Icon data or null if icon is invalid
	 */
	protected function name_to_icon(string $icon_name): ?array
	{
		$provider = '';
		$colonSeparated = explode(':', $icon_name);

		// Check for provider with correct '@' at start
		if (substr($icon_name, 0, 1) === '@')
		{
			// First part is provider
			if (count($colonSeparated) < 2 || count($colonSeparated) > 3)
			{
				return null;
			}
			$provider = substr(array_shift($colonSeparated), 1);
		}

		// Check split by colon: "prefix:name", "provider:prefix:name"
		if (!$colonSeparated || count($colonSeparated) > 3)
		{
			return null;
		}

		if (count($colonSeparated) > 1)
		{
			// "prefix:name"
			$name = array_pop($colonSeparated);
			$prefix = array_pop($colonSeparated);
			return [
				// Allow provider without '@': "provider:prefix:name"
				'provider' => count($colonSeparated) > 0 ? $colonSeparated[0] : $provider,
				'prefix' => $prefix,
				'name' => $name,
			];
		}

		// Attempt to split by dash: "prefix-name"
		$dashSeparated = explode('-', $colonSeparated[0]);
		if (count($dashSeparated) > 1)
		{
			return [
				'provider' => $provider,
				'prefix' => array_shift($dashSeparated),
				'name' => implode('-', $dashSeparated),
			];
		}

		return null;
	}

	/**
	 * Load icons date for supplied icons array
	 *
	 * @param array $icons
	 * @return string
	 */
	protected function load_icons_data(array $icons): string
	{
		// Load icons data
		$output = '';
		foreach ($icons as $prefix => $iconsList)
		{
			// Load icon set
			$collection = new Collection($prefix);
			$collection_file = Collection::findIconifyCollection($prefix);
			if (!file_exists($collection_file) || !$collection->loadFromFile($collection_file))
			{
				$this->log?->add('critical', ANONYMOUS, '', 'LOG_ICON_COLLECTION_INVALID', false, [$prefix]);
				continue;
			}

			// Make sure all icons exist
			foreach ($iconsList as $key => $name)
			{
				if (!$collection->iconExists($name))
				{
					$this->log?->add('critical', ANONYMOUS, '', 'LOG_ICON_INVALID', false, [$prefix . ':' . $name]);
					unset($iconsList[$key]);
				}
			}

			// Get data for all icons as string
			$output .= $collection->scriptify([
				'icons' => $iconsList,
				'callback' => 'add',
				'optimize' => true,
			]);
		}

		return $output;
	}
}
