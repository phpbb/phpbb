<?php

/**
* Template executor interface.
*
* Objects implementing this interface encapsulate a means of executing
* (i.e. rendering) a template.
*/
interface phpbb_template_executor
{
	/**
	* Executes the template managed by this executor.
	* @param phpbb_template_context $context Template context to use
	* @param array $lang Language entries to use
	*/
	public function execute($context, $lang);
}
