<?php

class template_validator
{
	protected $code;
	protected $stack;
	protected $blocks;

	/**
	* Constructor
	*
	* @param string $filename File to preload
	* @param string $code Code to preload
	*/
	public function __construct($filename = false, $code = false)
	{
		if ($code !== false)
		{
			$this->set_code($code);
		}
		elseif ($filename !== false)
		{
			$this->load_file($filename);
		}
	}

	/**
	* Load code
	*
	* @param string $code Code to load
	*/
	public function set_code($code)
	{
		$this->code = $code;
	}

	/**
	* Load file
	*
	* @param string $filename File to load
	*/
	public function load_file($filename)
	{
		$this->code = @file_get_contents($filename);
	}

	/**
	* Validate code
	*
	* @return string|false Error message or false if no error
	*/
	public function validate()
	{
		if (!is_string($this->code))
		{
			return 'Nothing to validate';
		}

		$this->stacks = array();
		$this->blocks = array();
		preg_match_all('#<!-- (([^<]*?) (.*?)?) ?-->#', $this->code, $this->blocks, PREG_SET_ORDER);

		foreach ($this->blocks as $block)
		{
			list(, $code, $tag, $params) = $block;

			switch ($tag)
			{
				case 'INCLUDE':
				case 'INCLUDEJS':
					// Do nothing
					break;

				case 'IF':
					// Add stack
					$this->stack[] = array(
						'tag'	=> $tag,
					);
					break;

				case 'BEGIN':
					$var = $this->begin_var($params);
					if (!$var)
					{
						return $this->error('Invalid ' . $tag, $code);
					}
					$this->stack[] = array(
						'tag'	=> $tag,
						'var'	=> $var,
					);
					break;

				case 'ELSE':
				case 'ELSEIF':
					$stack = $this->last_stack();
					if (!$stack || $stack['tag'] !== 'IF')
					{
						return $this->error($tag . ' without IF', $code);
					}
					break;

				case 'ENDIF':
					$stack = $this->last_stack();
					if (!$stack || $stack['tag'] !== 'IF')
					{
						return $this->error($tag . ' without IF', $code);
					}

					$this->remove_stack();
					break;

				case 'END':
					$var = $this->begin_var($params);
					if (!$var)
					{
						return $this->error('Invalid ' . $tag, $code);
					}

					$stack = $this->last_stack();
					if (!$stack || $stack['tag'] !== 'BEGIN')
					{
						return $this->error($tag . ' without BEGIN', $code);
					}

					if ($stack['var'] !== $var)
					{
						return $this->error('Loop variable "' . $var . '" should be "' . $stack['var'] . '"', $code);
					}

					$this->remove_stack();
					break;

				case 'BEGINEELSE':
					$stack = $this->last_stack();
					if (!$stack || $stack['tag'] !== 'BEGIN')
					{
						return $this->error($tag . ' without BEGIN', $code);
					}
					break;
			}
		}
		return false;
	}

	/**
	* Get variable from BEGIN/END
	*
	* @param string $params Parameters of BEGIN or END
	*
	* @return string Variable
	*/
	protected function begin_var($params)
	{
		return preg_replace('/(^[a-z]+)(.*)$/i', '$1', $params);
	}

	/**
	* Get last stack
	*
	* @return array|false Last stack
	*/
	protected function last_stack()
	{
		return count($this->stack) ? $this->stack[count($this->stack) - 1] : false;
	}

	/**
	* Remove last stack
	*/
	protected function remove_stack()
	{
		array_pop($this->stack);
	}

	/**
	* Generate error message
	*
	* @param string $error Error message
	* @param string $tag Template tag
	*
	* @return string Error message
	*/
	protected function error($error, $tag)
	{
		return $error . ': ' . $tag;
	}
}
