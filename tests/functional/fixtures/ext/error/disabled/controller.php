<?php

class phpbb_ext_error_disabled_controller extends phpbb_extension_controller
{
	public function handle()
	{		
		$this->template->set_filenames(array(
			'body' => 'index_body.html'
		));

		page_header('Test extension');
		page_footer();
	}
}
