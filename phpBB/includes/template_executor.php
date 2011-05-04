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
	*/
	public function execute();
}
