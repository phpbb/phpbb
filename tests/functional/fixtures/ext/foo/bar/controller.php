<?php

class phpbb_ext_foo_bar_controller implements phpbb_extension_controller_interface
{
	public function handle()
	{
		global $template;
		$template->set_ext_dir_prefix($phpbb_root_path . 'ext/foo/bar/');

		$template->set_filenames(array(
			'body' => 'index_body.html'
		));

		page_header('Test extension');
		page_footer();
	}
}
