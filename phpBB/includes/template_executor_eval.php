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
	* Template includes are delegated to template object $template.
	*
	* @param string $code php code of the template
	* @param phpbb_template $template template object
	*/
	public function __construct($code, $template)
	{
		$this->code = $code;
		$this->template = $template;
	}

	/**
	* Executes the template managed by this executor by eval'ing php code
	* of the template.
	* @param phpbb_template_context $context Template context to use
	* @param array $lang Language entries to use
	*/
	public function execute($context, $lang)
	{
		$_template = &$this->template;
		$_tpldata = &$context->get_data_ref();
		$_rootref = &$context->get_root_ref();
		$_lang = &$lang;

		eval($this->code);
	}
}
