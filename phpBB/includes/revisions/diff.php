<?php

class phpbb_revisions_diff
{

	private $from;
	private $to;
	private $diff;
	private $rdiff;

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

	public function render()
	{
		$from_text = $this->from->get('text');
		$to_text = $this->to->get('text');

		$this->diff = new diff($from_text, $to_text, false);
		$this->rdiff = new diff_renderer_inline();

		return $this->rdiff->render($this->diff);
	}
}
