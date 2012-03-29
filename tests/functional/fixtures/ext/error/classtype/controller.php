<?php

class phpbb_ext_error_classtype_controller
{
	public function handle()
	{
		global $template;
		$template->set_ext_dir_prefix($phpbb_root_path . 'ext/error/classtype/');

		$template->set_filenames(array(
			'body' => 'index_body.html'
		));

		page_header('Test extension');
		page_footer();
	}
}
