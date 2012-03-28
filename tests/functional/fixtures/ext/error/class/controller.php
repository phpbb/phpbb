<?php

class phpbb_ext_foobar_controller extends phpbb_extension_controller
{
	public function handle()
	{
		$this->template->set_ext_dir_prefix($this->phpbb_root_path . 'ext/error/class/');

		$this->template->set_filenames(array(
			'body' => 'index_body.html'
		));

		page_header('Test extension');
		page_footer();
	}
}
