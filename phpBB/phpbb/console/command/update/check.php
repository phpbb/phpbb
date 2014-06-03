<?php
/**
*
* @package phpBB3
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace phpbb\console\command\update;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class check extends \phpbb\console\command\command
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \Symfony\Component\DependencyInjection\ContainerBuilder */
	protected $phpbb_container;

	/**
	* Construct method
	*/
	public function __construct(\phpbb\user $user, \phpbb\config\config $config, \Symfony\Component\DependencyInjection\ContainerInterface $phpbb_container)
	{
		$this->user = $user;
		$this->config = $config;
		$this->phpbb_container = $phpbb_container;
		$this->user->add_lang(array('acp/common'));
		parent::__construct();
	}

	/**
	* Configures the service.
	*
	* Sets the name and description of the command.
	*
	* @return null
	*/
	protected function configure()
	{
		$this
			->setName('update:check')
			->setDescription($this->user->lang('CLI_DESCRIPTION_UPDATE_CHECK'))
		;
	}

	/**
	* Executes the command.
	*
	* Checks if an update is available.
	* If at least one is available, a message is printed and if verbose mode is set the list of possible updates is printed.
	* If their is none, nothing is printed unless verbose mode is set.
	*
	* @param InputInterface $input Input stream, used to get the options.
	* @param OutputInterface $output Output stream, used to print messages.
	* @return int 0 if the board is up to date, 1 if it is not and 2 if an error occured.
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$version_helper = $this->phpbb_container->get('version_helper');
		try
		{
			$recheck = true;
			$updates_available = $version_helper->get_suggested_updates($recheck);
		}
		catch (\RuntimeException $e)
		{
			$output->writeln('<error>' . $this->user->lang('VERSIONCHECK_FAIL') . '</error>');

			$updates_available = array();
			return 2;
		}

		if (!empty($updates_available))
		{
			$output->writeln('<info>' . $this->user->lang('UPDATE_NEEDED') . '</info>');

			if ($input->getOption('verbose'))
			{
				$output->writeln($this->user->lang('CURRENT_VERSION') . $this->user->lang('COLON') . ' ' . $this->config['version']);
				$output->writeln($this->user->lang('UPDATES_AVAILABLE'));
				foreach ($updates_available as $branch => $version_data)
				{
					$output->writeln($version_data);
				}
			}

			return 1;
		}
		else
		{
			if ($input->getOption('verbose'))
			{
				$output->writeln($this->user->lang('CURRENT_VERSION') . $this->user->lang('COLON') . ' ' . $this->config['version']);
				$output->writeln('<info>' . $this->user->lang('UPDATE_NOT_NEEDED') . '</info>');
			}

			return 0;
		}
	}
}
