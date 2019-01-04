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

namespace phpbb\viewtopic;

/*
 * @todo
 */
class parameter_handler
{
	/**
	 * @var \phpbb\event\dispatcher_interface
	 */
	protected $dispatcher;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/*
	 * @todo
	 */
	public function __construct(\phpbb\event\dispatcher_interface $dispatcher, \phpbb\user $user)
	{
		$this->dispatcher = $dispatcher;
		$this->user = $user;
	}

	/*
	 * @todo
	 */
	public function decode($parameter_array)
	{
		$decoded_values = $this->get_defaults();

		/*
		 * @todo
		 */
		$vars = ['parameter_array', 'decoded_values'];
		extract($this->dispatcher->trigger_event('core.viewtopic_parameter_decode_before', compact($vars)));

		$decoded_values = array_merge($this->decode_parameters($parameter_array), $decoded_values);

		/*
		 * @todo
		 */
		$vars = ['parameter_array', 'decoded_values'];
		extract($this->dispatcher->trigger_event('core.viewtopic_parameter_decode_before', compact($vars)));

		return $decoded_values;
	}

	/*
	 * @todo
	 */
	protected function decode_parameters($parameter_array)
	{
		$decoded_parameters = [];

		while (!empty($parameter_array))
		{
			$key = array_shift($parameter_array);
			switch ($key)
			{
				case 'page':
					$decoded_parameters['page'] = $this->get_page_number($parameter_array);
				break;

				case 'sort':
					$decoded_parameters = array_merge(
						$decoded_parameters,
						$this->get_sort_parameters($parameter_array)
					);
				break;

				// @todo: Should we throw an exception for invalid keys?
			}
		}

		return $decoded_parameters;
	}

	/*
	 * @todo
	 */
	protected function get_page_number(&$parameter_array)
	{
		$value = (int) array_shift($parameter_array);
		return ($value >= 1) ? $value : 1;
	}

	/*
	 * @todo
	 */
	protected function get_sort_parameters(&$parameter_array)
	{
		$sort_keys = ['by', 'order'];
		$sort_order = ['asc', 'desc'];
		$sort_by = ['author', 'time', 'subject'];
		$sort_params = [];

		while (!empty($parameter_array) && in_array($parameter_array[0], $sort_keys))
		{
			$key = array_shift($parameter_array);
			switch ($key)
			{
				case 'by':
					if (!in_array($parameter_array[0], $sort_by))
					{
						return $sort_params;
					}

					$sort_keys['sort_by'] = (array_shift($parameter_array))[0];
				break;

				case 'order':
					if (!in_array($parameter_array[0], $sort_order))
					{
						return $sort_params;
					}

					$sort_keys['sort_order'] = (array_shift($parameter_array))[0];
				break;
			}
		}

		return $sort_params;
	}

	/*
	 * @todo
	 */
	protected function get_defaults()
	{
		$parameters = [
			'show_days' => (!empty($this->user->data['user_post_show_days'])) ? $this->user->data['user_post_show_days'] : 0,
			'sort_by' => (!empty($this->user->data['user_post_sortby_type'])) ? $this->user->data['user_post_sortby_type'] : 't',
			'sort_order' => (!empty($this->user->data['user_post_sortby_dir'])) ? $this->user->data['user_post_sortby_dir'] : 'a',
			'page' => 1
		];

		return $parameters;
	}
}
