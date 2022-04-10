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

namespace phpbb\cron\task;

use phpbb\routing\helper;

/**
* Cron task wrapper class.
* Enhances cron tasks with convenience methods that work identically for all tasks.
*/
class wrapper
{
	/**
	 * @var helper
	 */
	protected $routing_helper;

	/**
	 * @var task
	 */
	protected $task;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * @var string
	 */
	protected $php_ext;

	/**
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	* Constructor.
	*
	* Wraps a task $task, which must implement cron_task interface.
	*
	* @param task	$task				The cron task to wrap.
	* @param helper	$routing_helper		Routing helper for route generation
	* @param string	$phpbb_root_path	Relative path to phpBB root
	* @param string	$php_ext			PHP file extension
	* @param \phpbb\template\template	$template
	*/
	public function __construct(task $task, helper $routing_helper, $phpbb_root_path, $php_ext, $template)
	{
		$this->task = $task;
		$this->routing_helper = $routing_helper;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->template = $template;
	}

	/**
	* Returns whether the wrapped task is parametrised.
	*
	* Parametrized tasks accept parameters during initialization and must
	* normally be scheduled with parameters.
	*
	* @return bool		Whether or not this task is parametrized.
	*/
	public function is_parametrized()
	{
		return $this->task instanceof parametrized;
	}

	/**
	* Returns whether the wrapped task is ready to run.
	*
	* A task is ready to run when it is runnable according to current configuration
	* and enough time has passed since it was last run.
	*
	* @return bool		Whether the wrapped task is ready to run.
	*/
	public function is_ready()
	{
		return $this->task->is_runnable() && $this->task->should_run();
	}

	/**
	* Returns a url through which this task may be invoked via web.
	*
	* When system cron is not in use, running a cron task is accomplished
	* by outputting an image with the url returned by this function as
	* source.
	*
	* @return string		URL through which this task may be invoked.
	*/
	public function get_url()
	{
		$params['cron_type'] = $this->get_name();
		if ($this->is_parametrized())
		{
			$params = array_merge($params, $this->task->get_parameters());
		}

		return $this->routing_helper->route('phpbb_cron_run', $params);
	}

	/**
	 * Returns HTML for an invisible `img` tag that can be displayed on page
	 * load to trigger a request to the relevant cron task endpoint.
	 *
	 * @return string       HTML to render to trigger cron task
	 */
	public function get_html_tag()
	{
		$this->template->set_filenames([
			'cron_html_tag' => 'cron.html',
		]);

		$this->template->assign_var('CRON_TASK_URL', $this->get_url());

		return $this->template->assign_display('cron_html_tag');
	}

	/**
	* Forwards all other method calls to the wrapped task implementation.
	*
	* @return mixed
	*/
	public function __call($name, $args)
	{
		return call_user_func_array(array($this->task, $name), $args);
	}
}
