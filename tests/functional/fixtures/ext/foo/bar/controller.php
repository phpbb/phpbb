<?php

class phpbb_ext_foo_bar_controller extends phpbb_extension_controller
{
	public function handle()
	{
		$this->template->set_ext_dir_prefix($this->phpbb_root_path . 'ext/foo/bar/');

		$this->template->set_filenames(array(
			'body' => 'foobar_body.html'
		));

		page_header('Test extension');
		page_footer();
	}
}
