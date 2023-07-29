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

use phpbb\exception\runtime_exception;
use Iconify\JSONTools\Collection;
use Symfony\Component\Finder\Finder;

class iconify_bundler
{
	protected $db;

	protected $ext_manager;

	protected $root_path = '';
	protected $icons_list = [];

	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\extension\manager $ext_manager, string $root_path)
	{
		$this->db = $db;
		$this->ext_manager = $ext_manager;
		$this->root_path = $root_path;
	}

	public function run()
	{
		// Sort icons first
		sort($this->icons_list, SORT_NATURAL);

		$organized_icons = $this->organize_icons_list();

		$output = $this->load_icons_data($organized_icons);

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
	 * @param array $paths Icon paths
	 *
	 * @return void
	 */
	public function find_icons(array $paths): void
	{
		if (!count($paths))
		{
			return;
		}

		$finder = new Finder();
		$finder->files();

		foreach ($paths as $cur_path)
		{
			$finder->in($cur_path);
		}

		$finder->name('*.html')
			->name('*.twig')
			->contains("Icon('iconify',");

		foreach ($finder as $file)
		{
			$contents = $file->getContents();
			$matches = [];
			preg_match_all("/Icon\('iconify', *(?:'(?<text>[^']+(?<content_flow>' ~ S_CONTENT_FLOW_(?:BEGIN|END))?)|(?<json>{[^}]+}))/m", $contents, $matches, PREG_SET_ORDER);
			foreach ($matches as $match_data)
			{
				if (!empty($match_data['content_flow']))
				{
					$base_icon_name = str_replace($match_data['content_flow'], '', $match_data['text']);
					$this->add_icon($base_icon_name . 'left');
					$this->add_icon($base_icon_name . 'right');
				}
				else if (!empty($match_data['json']))
				{
					preg_match_all("/\s'(?<text>[^']+)'/", $match_data['json'], $icons_array, PREG_SET_ORDER);
					foreach ($icons_array as $icon)
					{
						$this->add_icon($icon['text']);
					}
				}
				else if (!empty($match_data['text']))
				{
					$this->add_icon($match_data['text']);
				}
				else
				{
					throw new runtime_exception('Found unexpected icon name `%1$s` in `%2$s`', [$match_data[0], $file->getPath()]);
				}
			}
		}
	}

	public function with_extensions(): iconify_bundler
	{
		$extensions = $this->ext_manager->all_enabled();

		$search_paths = [];

		foreach ($extensions as $path)
		{
			if (file_exists($path))
			{
				$search_paths[] = $path;
			}
		}

		$this->find_icons($search_paths);

		return $this;
	}

	public function with_styles(): iconify_bundler
	{
		$sql = 'SELECT *
			FROM ' . STYLES_TABLE;
		$result = $this->db->sql_query($sql);

		$style_paths = [];

		while ($row = $this->db->sql_fetchrow($result))
		{
			$style_paths[] = $this->root_path . 'styles/' . $row['style_path'];
		}
		$this->db->sql_freeresult($result);

		$this->find_icons($style_paths);

		return $this;
	}

	protected function add_icon(string $icon_name): void
	{
		if (!in_array($icon_name, $this->icons_list))
		{
			$this->icons_list[] = $icon_name;
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
			if ($icon === null || $icon['provider'] !== '')
			{
				// Invalid name or icon name has provider.
				// All icons in this example are from Iconify, so providers are not supported.
				throw new \Error('Invalid icon name: ' . $icon_name);
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

	protected function load_icons_data($icons): string
	{
		// Load icons data
		$output = '';
		foreach ($icons as $prefix => $iconsList)
		{
			// Load icon set
			$collection = new Collection($prefix);
			if (!$collection->loadIconifyCollection($prefix)) {
				throw new \Error(
					'Icons with prefix "' . $prefix . '" do not exist in Iconify. Update iconify/json?'
				);
			}

			// Make sure all icons exist
			foreach ($iconsList as $name) {
				if (!$collection->iconExists($name)) {
					// Uncomment next line to throw error if an icon does not exist
					// throw new Error('Could not find icon: "' . $prefix . ':' . $name . '"');
					echo 'Could not find icon: "', $prefix, ':', $name, "\"\n";
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
