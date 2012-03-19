<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_template_renderer_eval_test extends phpbb_test_case
{
	public function test_eval()
	{
		$compiled_code = '<a href="<?php echo \'Test\'; ?>">';
		$valid_code = '<a href="Test">';
		$context = new phpbb_template_context();
		$template = new phpbb_template_renderer_eval($compiled_code, NULL);
		ob_start();
		try
		{
			$template->render($context, array());
		}
		catch (Exception $exception)
		{
			ob_end_clean();
			throw $exception;
		}
		$output = ob_get_clean();
		$this->assertEquals($valid_code, $output);
	}
}
