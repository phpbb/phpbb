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

class phpbb_mock_controller_provider extends \phpbb\controller\provider
{
	public function get_url_generator(\phpbb\extension\manager $manager, \Symfony\Component\Routing\RequestContext $context)
	{
		if ($this->routes == null || empty($this->routing_files))
		{
			$this->find_routing_files($manager->get_finder());
			$this->find($this->phpbb_root_path);
		}

		return $this->create_url_generator($context);
	}
}
