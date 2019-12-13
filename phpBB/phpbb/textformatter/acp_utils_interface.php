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
	* Analyse given BBCode definition for issues and safeness
	*
	* Required elements in the return array:
	*  - status:
	*    - "safe"               The BBCode is valid and can be safely used by anyone.
	*    - "unsafe"             The BBCode is valid but may be unsafe to use.
	*    - "invalid_definition" There is an issue with the definition.
	*    - "invalid_template"   There is an issue with the template.
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
