<?php

class phpbb_revisions_diff
{

	private $from;
	private $to;
	private $diff;
	private $rdiff;

	/**
	* Constructor method
	*
	* @param phpbb_revisions_revision $from Starting revision for comparison
	* @param phpbb_revisions_revision $to Ending revision for comparison
	*/
	public function __construct(phpbb_revisions_revision $from, phpbb_revisions_revision $to)
	{
		global $phpbb_root_path, $phpEx;

		if (!class_exists('diff'))
		{
			include("{$phpbb_root_path}includes/diff/diff.{$phpEx}");
			include("{$phpbb_root_path}includes/diff/engine.{$phpEx}");
			include("{$phpbb_root_path}includes/diff/renderer.{$phpEx}");
		}

		$this->to = $to;
		$this->from = $from;
	}

	/**
	* Render a diff between two properties
	*
	* @param string $property Which class property to compare
	* @return string HTML diff representation
	*/
	public function render($property)
	{
		$from_property = $this->from->get($property);
		$to_property = $this->to->get($property);

		$this->diff = new diff($from_property, $to_property, false);
		$this->rdiff = new diff_renderer_inline();

		return $this->rdiff->render($this->diff);
	}
}
