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

namespace phpbb\db\migration\data\v33x;

class topic_views_update extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v33x\v3314',
		];
	}

	public function update_schema(): array
	{
		// This extends the topic view count field so we can support much larger values.
		return [
			'change_columns' => [
				$this->table_prefix . 'topics' => [
					'topic_views'  => ['ULINT', 0],
				],
			]
		];
	}

	public function revert_schema(): array
	{
		return [
			'change_columns' => [
				$this->table_prefix . 'topics' => [
					'topic_views'  => ['UINT', 0],
				],
			]
		];
	}
}
