<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

class phpbb_textformatter_s9e_link_helper_test extends phpbb_test_case
{
	public function test_does_not_override_autoimage()
	{
		$container    = $this->get_test_case_helpers()->set_s9e_services();
		$configurator = $container->get('text_formatter.s9e.factory')->get_configurator();

		$configurator->Autoimage;
		extract($configurator->finalize());

		$original = 'http://localhost/path_to_long_image_filename_0123456789.png';
		$expected = '<r>
			<URL url="http://localhost/path_to_long_image_filename_0123456789.png">
				<IMG src="http://localhost/path_to_long_image_filename_0123456789.png">
					<LINK_TEXT text="http://localhost/path_to_long_image_fil ... 456789.png">http://localhost/path_to_long_image_filename_0123456789.png</LINK_TEXT>
				</IMG>
			</URL>
		</r>';

		$this->assertXmlStringEqualsXmlString($expected, $parser->parse($original));
	}
}
