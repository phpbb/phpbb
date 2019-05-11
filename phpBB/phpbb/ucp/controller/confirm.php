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

namespace phpbb\mcp\controller;

/**
 * ucp_confirm
 * Visual confirmation
 *
 * Note to potential users of this code ...
 *
 * Remember this is released under the _GPL_ and is subject
 * to that licence. Do not incorporate this within software
 * released or distributed in any way under a licence other
 * than the GPL. We will be watching ... ;)
 */
class confirm
{
	/** @var \phpbb\captcha\factory */
	protected $captcha_factory;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request */
	protected $request;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\captcha\factory	$captcha_factory	Captcha factory object
	 * @param \phpbb\config\config		$config				Config object
	 * @param \phpbb\request\request	$request			Request object
	 */
	public function __construct(
		\phpbb\captcha\factory $captcha_factory,
		\phpbb\config\config $config,
		\phpbb\request\request $request
	)
	{
		$this->captcha_factory	= $captcha_factory;
		$this->config			= $config;
		$this->request			= $request;
	}

	function main($id, $mode)
	{
		$captcha = $this->captcha_factory->get_instance($this->config['captcha_plugin']);
		$captcha->init($this->request->variable('type', 0));
		$captcha->execute();

		garbage_collection();
		exit_handler();
	}
}
