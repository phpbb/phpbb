<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

abstract class phpbb_template_base implements phpbb_template
{
	/**
	* Template context.
	* Stores template data used during template rendering.
	*
	* @var phpbb_template_context
	*/
	protected $context;

	/**
	* Array of filenames assigned to set_filenames
	*
	* @var array
	*/
	protected $filenames = array();

	/**
	* {@inheritdoc}
	*/
	public function set_filenames(array $filename_array)
	{
		$this->filenames = array_merge($this->filenames, $filename_array);

		return $this;
	}

	/**
	* Get a filename from the handle
	*
	* @param string $handle
	* @return string
	*/
	protected function get_filename_from_handle($handle)
	{
		return (isset($this->filenames[$handle])) ? $this->filenames[$handle] : $handle;
	}

	/**
	* {@inheritdoc}
	*/
	public function destroy()
	{
		$this->context->clear();

		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function destroy_block_vars($blockname)
	{
		$this->context->destroy_block_vars($blockname);

		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function assign_vars(array $vararray)
	{
		foreach ($vararray as $key => $val)
		{
			$this->assign_var($key, $val);
		}

		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function assign_var($varname, $varval)
	{
		$this->context->assign_var($varname, $varval);

		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function append_var($varname, $varval)
	{
		$this->context->append_var($varname, $varval);

		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function assign_block_vars($blockname, array $vararray)
	{
		$this->context->assign_block_vars($blockname, $vararray);

		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function alter_block_array($blockname, array $vararray, $key = false, $mode = 'insert')
	{
		return $this->context->alter_block_array($blockname, $vararray, $key, $mode);
	}

	/**
	* Calls hook if any is defined.
	*
	* @param string $handle Template handle being displayed.
	* @param string $method Method name of the caller.
	*/
	protected function call_hook($handle, $method)
	{
		global $phpbb_hook;

		if (!empty($phpbb_hook) && $phpbb_hook->call_hook(array(__CLASS__, $method), $handle, $this))
		{
			if ($phpbb_hook->hook_return(array(__CLASS__, $method)))
			{
				$result = $phpbb_hook->hook_return_result(array(__CLASS__, $method));
				return array($result);
			}
		}

		return false;
	}
}
