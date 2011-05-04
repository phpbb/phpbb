<?php

/**
* Template executor that stores compiled template's php code and
* evaluates it via eval.
*/
class phpbb_template_executor_eval implements phpbb_template_executor
{
	/**
	* Template code to be eval'ed.
	*/
	private $code;

	/**
	* Constructor. Stores provided code for future evaluation.
	*
	* @param string $code php code of the template
	*/
	public function __construct($code)
	{
		$this->code = $code;
	}

	/**
	* Executes the template managed by this executor by eval'ing php code
	* of the template.
	*/
	public function execute()
	{
		eval($this->code);
	}
}
