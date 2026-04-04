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

namespace phpbb\update;

use phpbb\config\config;
use phpbb\filesystem\filesystem_interface;
use phpbb\language\language_file_helper;
use phpbb\language\language_file_loader;
use phpbb\template\template;
use Symfony\Component\VarExporter\Exception\ExceptionInterface;
use Symfony\Component\VarExporter\VarExporter;

/**
 * Class to generate and store the static maintenance page.
 */
class maintenance_generator
{
	/**
	 * Constructor for maintenance_generator
	 *
	 * @param config $config
	 * @param filesystem_interface $filesystem
	 * @param language_file_helper $lang_helper
	 * @param language_file_loader $lang_loader
	 * @param template $template
	 * @param string $phpbb_root_path
	 * @param string $php_ext
	 */
	public function __construct(protected config $config, protected filesystem_interface $filesystem, protected language_file_helper $lang_helper,
								protected language_file_loader $lang_loader, protected template $template, protected string $phpbb_root_path, protected string $php_ext)
	{
	}

	/**
	 * Generates the maintenance page and writes it to the store directory.
	 *
	 * @param array $social_links Social links to pass to the template
	 * @return void
	 * @throws ExceptionInterface If writing of maintenance lock file fails
	 */
	public function write_maintenance_lock(array $social_links = []): void
	{
		$this->assign_template_vars($social_links);

		$template_content = $this->template->assign_display('maintenance_page.html');

		$file_content = '<?php' . "\n";
		$file_content .= '/**' . "\n";
		$file_content .= ' * phpBB Maintenance Lock File' . "\n";
		$file_content .= ' *' . "\n";
		$file_content .= ' * @copyright (c) phpBB Limited <https://www.phpbb.com>' . "\n";
		$file_content .= ' * @license GNU General Public License, version 2 (GPL-2.0)' . "\n";
		$file_content .= ' */' . "\n\n";

		$file_content .= 'if (!defined(\'IN_PHPBB\'))' . "\n";
		$file_content .= '{' . "\n";
		$file_content .= "\t" . 'exit;' . "\n";
		$file_content .= '}' . "\n\n";

		$exported_data = VarExporter::export([
			'content'	=> $template_content,
		]);

		$file_content .= 'return ' . $exported_data . ';' . "\n";

		$this->filesystem->dump_file($this->phpbb_root_path . 'store/UPDATE_LOCK.' . $this->php_ext, $file_content);
	}

	protected function assign_template_vars(array $social_links): void
	{
		$maintenance_start = time();

		foreach ($social_links as $social_link)
		{
			$this->template->assign_block_vars('links', [
				'ICON'	=> $social_link['ICON'],
				'URL'	=> $social_link['URL'],
				'NAME'	=> $social_link['NAME'],
			]);
		}

		// Assign general data for maintenance page
		$this->template->assign_vars([
			'DEFAULT_LANG'				=> $this->config['default_lang'],
			'LANG_DATA'					=> json_encode($this->get_language_vars()),
			'MAINTENANCE_INITIATED'		=> $maintenance_start,
			'SITENAME'					=> $this->config['sitename'],
		]);
	}

	/**
	 * Get board maintenance language variables for all enabled languages
	 *
	 * @return array
	 */
	protected function get_language_vars(): array
	{
		// Get all enabled languages
		$languages = $this->lang_helper->get_available_languages();

		$language_vars = [];
		foreach ($languages as $lang_data)
		{
			$current_lang = [];

			// Load the necessary language file for this language
			$this->lang_loader->load('common', $lang_data['iso'], $current_lang);

			// Get the required language variables
			$language_vars[$lang_data['iso']] = [
				'BOARD_MAINTENANCE'			=> $current_lang['BOARD_MAINTENANCE'] ?? '',
				'BOARD_MAINTENANCE_START'	=> $current_lang['BOARD_MAINTENANCE_START'] ?? '',
				'BOARD_MAINTENANCE_TITLE'	=> $current_lang['BOARD_MAINTENANCE_TITLE'] ?? '',
			];
		}

		return $language_vars;
	}
}
