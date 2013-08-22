<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../template/template_test_case.php';

class phpbb_pagination_start_parameter_test extends phpbb_template_template_test_case
{
	protected $test_path = 'tests/pagination';

	public function phpbb_generate_template_pagination_data()
	{
		return array(
			array(
				'page.php',
				'start',
				95,
				10,
				10,
				'pagination
				:previous::page.php
				:else:1:page.php
				:current:2:page.php?start=10
				:else:3:page.php?start=20
				:else:4:page.php?start=30
				:else:5:page.php?start=40
				:ellipsis:9:page.php?start=80
				:else:10:page.php?start=90
				:next::page.php?start=20
				:u_prev:page.php
				:u_next:page.php?start=20',
			),
			array(
				'page.php',
				'start',
				95,
				10,
				20,
				'pagination
				:previous::page.php?start=10
				:else:1:page.php
				:else:2:page.php?start=10
				:current:3:page.php?start=20
				:else:4:page.php?start=30
				:else:5:page.php?start=40
				:else:6:page.php?start=50
				:ellipsis:9:page.php?start=80
				:else:10:page.php?start=90
				:next::page.php?start=30
				:u_prev:page.php?start=10
				:u_next:page.php?start=30',
			),
			array(
				'test/page/%d',
				'/page/%d',
				95,
				10,
				10,
				'pagination
				:previous::test
				:else:1:test
				:current:2:test/page/2
				:else:3:test/page/3
				:else:4:test/page/4
				:else:5:test/page/5
				:ellipsis:9:test/page/9
				:else:10:test/page/10
				:next::test/page/3
				:u_prev:test
				:u_next:test/page/3',
			),
			array(
				'test/page/%d',
				'/page/%d',
				95,
				10,
				20,
				'pagination
				:previous::test/page/2
				:else:1:test
				:else:2:test/page/2
				:current:3:test/page/3
				:else:4:test/page/4
				:else:5:test/page/5
				:else:6:test/page/6
				:ellipsis:9:test/page/9
				:else:10:test/page/10
				:next::test/page/4
				:u_prev:test/page/2
				:u_next:test/page/4',
			),
		);
	}

	/**
	* @dataProvider phpbb_generate_template_pagination_data
	*/
	public function test_phpbb_generate_template_pagination($base_url, $start_name, $num_items, $per_page, $start_item, $expect)
	{
		phpbb_generate_template_pagination($this->template, $base_url, 'pagination', $start_name, $num_items, $per_page, $start_item);
		$this->template->set_filenames(array('test' => 'pagination.html'));

		$this->assertEquals(str_replace("\t", '', $expect), $this->display('test'));
	}
}
