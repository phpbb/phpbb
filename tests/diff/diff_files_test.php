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

include_once(__DIR__ . '/../../phpBB/includes/diff/diff.php');
include_once(__DIR__ . '/../../phpBB/includes/diff/engine.php');

class diff_files_test extends phpbb_test_case
{
	/**
	 * @var string
	 */
	protected $filename;

	/**
	 * @var phpbb_mock_container_builder
	 */
	protected $old_path;

	/**
	 * @var \phpbb\install\helper\config
	 */
	protected $new_path;

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var array
	 */
	protected $update_files = [];

	protected function setUp(): void
	{
		$this->filename = 'test_files_diff.php';
		$this->path = __DIR__ . '/fixtures/';
		$this->old_path = $this->path . 'install/update/old/';
		$this->new_path = $this->path . 'install/update/new/';
		$this->update_files = [
			$this->filename,
		];
	}

	public function test_diff_files()
	{
		foreach ($this->update_files as $key => $filename)
		{
			$merge_conflicts = $file_contents = [];

			$file_to_diff = $this->old_path . $filename;
			$file_contents[0] = file_get_contents($file_to_diff);
			$this->assertNotFalse($file_contents[0], "File $file_to_diff is empty");

			$filenames = [
				$this->path . $filename,
				$this->new_path . $filename
			];

			foreach ($filenames as $file_to_diff)
			{
				$file_contents[] = file_get_contents($file_to_diff);
				$this->assertNotFalse($file_contents[count($file_contents) - 1], "File $file_to_diff is empty");
			}

			$diff = new \diff3($file_contents[0], $file_contents[1], $file_contents[2]);

			$file_is_merged = $diff->merged_output() === $file_contents[1];

			// Handle conflicts
			if ($diff->get_num_conflicts() !== 0)
			{
				// Check if current file content is merge of new or original file
				$tmp = [
					'file1'		=> $file_contents[1],
					'file2'		=> implode("\n", $diff->merged_new_output()),
				];

				$diff2 = new \diff($tmp['file1'], $tmp['file2']);
				$empty = $diff2->is_empty();

				if (!$empty)
				{
					unset($tmp, $diff2);

					// We check if the user merged with his output
					$tmp = [
						'file1'		=> $file_contents[1],
						'file2'		=> implode("\n", $diff->merged_orig_output()),
					];

					$diff2 = new \diff($tmp['file1'], $tmp['file2']);
					$empty = $diff2->is_empty();
				}

				unset($diff2);

				if (!$empty && in_array($filename, $merge_conflicts))
				{
					$merge_conflicts[] = $filename;
				}
				else
				{
					$file_is_merged = true;
				}
			}

			if ($file_is_merged)
			{
				unset($this->update_files[$key]);
			}

			unset($file_contents);
			unset($diff);
		}

		$this->assertEquals([], $this->update_files);
	}
}
