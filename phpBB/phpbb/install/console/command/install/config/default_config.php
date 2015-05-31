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

namespace phpbb\install\console\command\install\config;

use phpbb\language\language;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class default_config extends \phpbb\console\command\command
{
	/**
	 * @var language
	 */
	protected $language;

	/**
	 * Constructor
	 *
	 * @param language $language
	 */
	public function __construct(language $language)
	{
		$this->language = $language;

		parent::__construct(new \phpbb\user($language, 'datetime'));
	}

	/**
	 *
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName('install:config:default')
		;
	}

	/**
	 * Display the default configuration
	 *
	 * @param InputInterface  $input  An InputInterface instance
	 * @param OutputInterface $output An OutputInterface instance
	 *
	 * @return null
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$default_config = <<<EOL
installer:
    admin:
        name: admin
        password: adminadmin
        email: admin@example.org

    board:
        lang: en
        name: My Board
        description: My amazing new phpBB board

    database:
        dbms: sqlite3
        dbhost: ~
        dbport: ~
        dbuser: ~
        dbpasswd: ~
        dbname: ~
        table_prefix: phpbb_

    email:
        enabled: false
        smtp_delivery : ~
        smtp_host: ~
        smtp_auth: ~
        smtp_user: ~
        smtp_pass: ~

    server:
        cookie_secure: false
        server_protocol: http://
        force_server_vars: false
        server_name: localhost
        server_port: 80
        script_path: /
EOL;

		$default_config = Yaml::parse($default_config);
		$default_config = Yaml::dump($default_config, 10);
		$output->writeln($default_config);
	}
}
