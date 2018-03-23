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

namespace phpbb\template;

abstract class base implements template
{
	/**
	* Template context.
	* Stores template data used during template rendering.
	*
	* @var \phpbb\template\context
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
	public function retrieve_vars(array $vararray)
	{
		$result = array();
		foreach ($vararray as $varname)
		{
			$result[$varname] = $this->retrieve_var($varname);
		}
		return $result;
	}

	/**
	* {@inheritdoc}
	*/
	public function retrieve_var($varname)
	{
		return $this->context->retrieve_var($varname);
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
	public function assign_block_vars_array($blockname, array $block_vars_array)
	{
		$this->context->assign_block_vars_array($blockname, $block_vars_array);

		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function retrieve_block_vars($blockname, array $vararray)
	{
		return $this->context->retrieve_block_vars($blockname, $vararray);
	}

	/**
	* {@inheritdoc}
	*/
	public function alter_block_array($blockname, array $vararray, $key = false, $mode = 'insert')
	{
		return $this->context->alter_block_array($blockname, $vararray, $key, $mode);
	}

	/**
	* {@inheritdoc}
	*/
	public function find_key_index($blockname, $key)
	{
		return $this->context->find_key_index($blockname, $key);
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

		if (!empty($phpbb_hook) && $phpbb_hook->call_hook(array('template', $method), $handle, $this))
		{
			if ($phpbb_hook->hook_return(array('template', $method)))
			{
				$result = $phpbb_hook->hook_return_result(array('template', $method));
				return array($result);
			}
		}

		return false;
	}
}
