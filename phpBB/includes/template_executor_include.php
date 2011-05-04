<?php

/**
* Template executor that stores path to php file with template code
* and evaluates it by including the file.
*/
class phpbb_template_executor_include implements phpbb_template_executor
{
	/**
	* Template path to be included.
	*/
	private $path;

	/**
	* Constructor. Stores path to the template for future inclusion.
	*
	* @param string $path path to the template
	*/
	public function __construct($path)
	{
		$this->path = $path;
	}

	/**
	* Executes the template managed by this executor by including
	* the php file containing the template.
	*/
	public function execute()
	{
		include($this->path);
	}
}
