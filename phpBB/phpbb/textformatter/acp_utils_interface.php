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

namespace phpbb\textformatter;

interface acp_utils_interface
{
	/**
	* There is an issue with the definition
	*/
	const BBCODE_STATUS_INVALID_DEFINITION = 'invalid_definition';

	/**
	* There is an issue with the template
	*/
	const BBCODE_STATUS_INVALID_TEMPLATE = 'invalid_template';

	/**
	* The BBCode is valid and can be safely used by anyone
	*/
	const BBCODE_STATUS_SAFE = 'safe';

	/**
	* The BBCode is valid but may be unsafe to use
	*/
	const BBCODE_STATUS_UNSAFE = 'unsafe';

	/**
	* Analyse given BBCode definition for issues and safeness
	*
	* Required elements in the return array:
	*  - status: see BBCODE_STATUS_* constants
	*
	* Optional elements in the return array:
	*  - name:       Name of the BBCode based on the definition. Required if status is "safe".
	*  - error_text: Textual description of the issue in plain text or as a L_* string.
	*  - error_html: Visual description of the issue in HTML.
	*
	* @param  string $definition BBCode definition, e.g. [b]{TEXT}[/b]
	* @param  string $template   BBCode template, e.g. <b>{TEXT}</b>
	* @return array
	*/
	public function analyse_bbcode(string $definition, string $template): array;
}
