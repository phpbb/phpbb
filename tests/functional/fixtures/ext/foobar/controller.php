<?php

class phpbb_ext_foobar_controller extends phpbb_extension_controller
{
	public function handle()
	{
		$this->template->set_filenames(array(
			'body' => 'foobar_body.html'
		));

		page_header('Test extension');
		page_footer();
	}
}
