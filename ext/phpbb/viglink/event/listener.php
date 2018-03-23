<?php
/**
 *
 * VigLink extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\viglink\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener
 */
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config $config Config object */
	protected $config;

	/** @var \phpbb\template\template $template Template object */
	protected $template;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config     $config   Config object
	 * @param \phpbb\template\template $template Template object
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\template\template $template)
	{
		$this->config = $config;
		$this->template = $template;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'core.viewtopic_post_row_after'		=> 'display_viglink',
		);
	}

	/**
	 * Insert the VigLink JS code into forum pages
	 *
	 * @return void
	 */
	public function display_viglink()
	{
		$viglink_key = '';

		if ($this->config['allow_viglink_phpbb'] && $this->config['phpbb_viglink_api_key'])
		{
			// Use phpBB API key if VigLink is allowed for phpBB
			$viglink_key = $this->config['phpbb_viglink_api_key'];
		}

		$this->template->assign_vars(array(
			'VIGLINK_ENABLED'	=> $this->config['viglink_enabled'] && $viglink_key,
			'VIGLINK_API_KEY'	=> $viglink_key,
			'VIGLINK_SUB_ID'	=> md5(urlencode($this->config['viglink_api_siteid']) . $this->config['questionnaire_unique_id']),
		));
	}
}
