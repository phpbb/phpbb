<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * The phpBB version of the BBCode parser
 *
 */
class phpbb_bbcode_parser extends phpbb_bbcode_parser_base
{
	private $list_stack = array();
	protected $tags = array();

	public function __construct()
	{
		$this->tags = array(
			// The exact B BBcode from phpBB
			'b' => array(
				'replace' => '<span style="font-weight: bold">',
				'close' => '</span>',
				'attributes' => array(),
				'children' => array(true, 'quote' => true, 'code' => true, 'list' => true),
				'parents' => array(true),
			),
			// The exact I BBcode from phpBB
			'i' => array(
				'replace' => '<span style="font-style: italic">',
				'close' => '</span>',
				'attributes' => array(),
				'children' => array(true, 'quote' => true, 'code' => true, 'list' => true),
				'parents' => array(true),
			),
			// The exact U BBcode from phpBB
			'u' => array(
				'replace' => '<span style="text-decoration: underline">',
				'close' => '</span>',
				'attributes' => array(),
				'children' => array(true, 'quote' => true, 'code' => true, 'list' => true),
				'parents' => array(true),
			),

			// Quote tag attempt.
			'quote' => array(
				'replace' => '<div class="quotetitle">{_}</div><div class="quotecontent">',
				'close' => '</div>',
				'attributes' => array(
					'_' => array(
						'replace' => '%s wrote:',
					),
				),
				'children' => array(true),
				'parents' => array(true),
			),

			// code tag (without the =php functionality)
			'code' => array(
				'replace' => '<div class="codetitle"><b>Code:</b></div><div class="codecontent">',
				'close' => '</div>',
				'attributes' => array(),
				'children' => array(false),
				'parents' => array(true),
			),

			// list tag
			'list' => array(
				'replace' => '',
				'replace_func' => array($this, 'list_open'),
				'close' => '',
				'close_func' => array($this, 'list_close'),
				'attributes' => array(
					'_' => array(
						'replace' => '',
					),
				),
				'children' => array(false, 'li' => true),
				'parents' => array(true),
			),

			// The exact * tag from phpBB. "*" is not a valid tag name in this parser... introducing li from HTML!
			'li' => array(
				'replace' => '<li>',
				'close' => '</li>',
				'close_shadow' => true,
				'attributes' => array(),
				'children' => array(true, 'li' => true),
				'parents' => array(false, 'list' => true),
			),

			// Almost exact img tag from phpBB...
			'img' => array(
				'replace' => '<img alt="Image" src="',
				'close' => '" />',
				'attributes' => array(
					'__' => array(
						'replace' => '%s',
					),
				),
				'children' => array(false),
				'parents' => array(true),

			),

			'url' => array(
				'replace' => '',
				'replace_func' => array($this, 'url_tag'),
				'close' => '</a>',
				'attributes' => array(
					// The replace value is not important empty because the replace_func handles this.
					'_' => array(
						'replace' => '',
					),
					'__' => array(
						'replace' => '',
					),
				),
				'children' => array(false),
				'parents' => array(true),

			),

			'color' => array(
				'replace' => '<span style="color: {_}">',
				'close' => '</span>',
				'attributes' => array(
					'_' => array(
						'replace' => '%s',
						'required' => true
					),
				),
				'children' => array(true, 'color' => true),
				'parents' => array(true),
			),

			'size' => array(
				'replace' => '<span style="font-size: {_}px; line-height: normal">',
				'close' => '</span>',
				'attributes' => array(
					'_' => array(
						'replace' => '%s',
						'required' => true
					),
				),
				'children' => array(true, 'size' => true),
				'parents' => array(true),
			),


			// FLASH tag implementation attempt.
			'flash' => array(
				'replace' => '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0"{w}{h}>
<param name="movie" value="{m}" />
<param name="quality" value="high" />
<embed src="{m}" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"{w}{h}>
</embed>
</object>',
				'close' => false,
				'attributes' => array(
					'm' => array(
						'replace' => '%s',
						'required' => true,
					),
					'w' => array(
						'replace' => ' width="%s"',
						'type_check' => 'ctype_digit',
					),
					'h' => array(
						'replace' => ' height="%s"',
						'type_check' => 'ctype_digit',
					),
				),
				'children' => array(false),
				'parents' => array(true),
			),
			// The spoiler tag from area51.phpbb.com :p
			'spoiler' => array(
				'replace' => '<span class="quotetitle"><b>Spoiler:</b></span><span style="background-color:white;color:white;">',
				'close' => '</span>',
				'attributes' => array(),
				'children' => array(false),
				'parents' => array(true),
			),
			// a noparse tag
			'noparse' => array(
				'replace' => '',
				'close' => '',
				'attributes' => array(),
				'children' => array(false),
				'parents' => array(true),
			),
		);
		$this->smilies = array(
			':)' => '<img src="http://area51.phpbb.com/phpBB/images/smilies/icon_e_smile.gif" />',
			':(' => '<img src="http://area51.phpbb.com/phpBB/images/smilies/icon_e_sad.gif" />',
		);
		
//		$this->text_callback = 'strtoupper';
		parent::__construct();
	}


 	protected function url_tag(array $attributes = array(), array $definition = array())
	{
		if (isset($attributes['_']))
		{
			return '<a href="' . $attributes['_'] . '">';
		}
		return '<a href="' . $attributes['__'] . '">';
	}

 	protected function list_open(array $attributes = array(), array $definition = array())
	{
		if (isset($attributes['_']))
		{
			return '<ol style="list-style-type: ' . $attributes['_'] . '">';
		}
		return '<ul>';
	}

	protected function list_close(array $attributes = array())
	{
		if (isset($attributes['_']))
		{
			return '</ol>';
		}
		return '</ul>';
	}
}
