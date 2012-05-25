<?php

class phpbb_revisions_diff extends diff
{
	/**
	* Render a diff between two properties
	*
	* @param string $property Which class property to compare
	* @return string HTML diff representation
	*/
	public function render()
	{
		$this->rdiff = new diff_renderer_inline();

		return $this->rdiff->render($this);
	}
}
