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

require_once dirname(__FILE__) . '/common_groups_test_case.php';

/**
* @group functional
*/
class phpbb_functional_acp_groups_test extends phpbb_functional_common_groups_test_case
{
	protected $form_data;

	protected function get_url()
	{
		return 'adm/index.php?i=groups&mode=manage&action=edit';
	}

	public function acp_group_test_data()
	{
		return array(
			'both_yes' => array(
				5,
				true,
				true,
			),
			'legend_no_teampage' => array(
				5,
				true,
				false,
			),
			'no_legend_teampage' => array(
				5,
				false,
				true,
			),
			'both_no' => array(
				5,
				false,
				false,
			),
			'no_change' => array(
				5,
				NULL,
				NULL,
			),
			'back_to_default' => array(
				5,
				true,
				true,
			),
			// Remove and add moderators back in order to reset
			// group order to default one
			'mods_both_no' => array(
				4,
				false,
				false,
			),
			'mods_back_to_default' => array(
				4,
				true,
				true,
			),
		);
	}

	/**
	* @dataProvider acp_group_test_data
	*/
	public function test_acp_groups_teampage($group_id, $tick_legend, $tick_teampage)
	{
		$this->group_manage_login();

		// Manage Administrators group
		$form = $this->get_group_manage_form($group_id);
		$this->form_data[0] = $form->getValues();

		if (isset($tick_legend) && isset($tick_teampage))
		{
			if ($tick_legend)
			{
				$form['group_legend']->tick();
			}
			else
			{
				$form['group_legend']->untick();
			}

			if ($tick_teampage)
			{
				$form['group_teampage']->tick();
			}
			else
			{
				$form['group_teampage']->untick();
			}
		}
		$crawler = self::submit($form);
		$this->assertContains($this->lang('GROUP_UPDATED'), $crawler->text());

		$form = $this->get_group_manage_form($group_id);
		if (!isset($tick_legend) && !isset($tick_teampage))
		{
			$this->form_data[1] = $form->getValues();
			unset($this->form_data[0]['creation_time'], $this->form_data[0]['form_token'], $this->form_data[1]['creation_time'], $this->form_data[1]['form_token']);
			$this->assertEquals($this->form_data[0], $this->form_data[1]);
		}
		else
		{
			$this->form_data = $form->getValues();
			$this->assertEquals($tick_legend, $this->form_data['group_legend']);
			$this->assertEquals($tick_teampage, $this->form_data['group_teampage']);
		}
	}
}
