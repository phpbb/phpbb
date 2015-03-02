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

namespace phpbb\textformatter\s9e;

use s9e\TextFormatter\Configurator;
use s9e\TextFormatter\Configurator\Items\AttributeFilters\Regexp as RegexpFilter;

/**
* Creates s9e\TextFormatter objects
* @package phpBB3
*/
class factory implements \phpbb\textformatter\cache
{
	/**
	* @var phpbb_cache_driver_interface $cache
	*/
	protected $cache;

	/**
	* @var string Path to the cache dir
	*/
	protected $cache_dir;

	/**
	* @var string Cache key used for the parser
	*/
	protected $cache_key_parser;

	/**
	* @var string Cache key used for the renderer
	*/
	protected $cache_key_renderer;

	/**
	* @var array Custom tokens used in bbcode.html and their corresponding token from the definition
	*/
	protected $custom_tokens = array(
		'email' => array('{DESCRIPTION}' => '{TEXT}'),
		'flash' => array('{WIDTH}' => '{NUMBER1}', '{HEIGHT}' => '{NUMBER2}'),
		'img'   => array('{URL}' => '{IMAGEURL}'),
		'list'  => array('{LIST_TYPE}' => '{HASHMAP}'),
		'quote' => array('{USERNAME}' => '{TEXT1}'),
		'size'  => array('{SIZE}' => '{FONTSIZE}'),
		'url'   => array('{DESCRIPTION}' => '{TEXT}'),
	);

	/**
	* @var \phpbb\textformatter\data_access
	*/
	protected $dal;

	/**
	* @var array Default BBCode definitions
	*/
	protected $default_definitions = array(
		'attachment' => '[ATTACHMENT index={NUMBER} filename={TEXT;useContent}]',
		'b'     => '[B]{TEXT}[/B]',
		'code'  => '[CODE]{TEXT}[/CODE]',
		'color' => '[COLOR={COLOR}]{TEXT}[/COLOR]',
		'email' => '[EMAIL={EMAIL;useContent}]{TEXT}[/EMAIL]',
		'flash' => '[FLASH={NUMBER1},{NUMBER2} width={NUMBER1;postFilter=#flashwidth} height={NUMBER2;postFilter=#flashheight} url={URL;useContent} /]',
		'i'     => '[I]{TEXT}[/I]',
		'img'   => '[IMG src={IMAGEURL;useContent}]',
		'list'  => '[LIST type={HASHMAP=1:decimal,a:lower-alpha,A:upper-alpha,i:lower-roman,I:upper-roman;optional;postFilter=#simpletext}]{TEXT}[/LIST]',
		'li'    => '[* $tagName=LI]{TEXT}[/*]',
		'quote' =>
			"[QUOTE
				author={TEXT1;optional}
				url={URL;optional}
				author={PARSE=/^\\[url=(?'url'.*?)](?'author'.*)\\[\\/url]$/i}
				author={PARSE=/^\\[url](?'author'(?'url'.*?))\\[\\/url]$/i}
				author={PARSE=/(?'url'https?:\\/\\/[^[\\]]+)/i}
			]{TEXT2}[/QUOTE]",
		'size'  => '[SIZE={FONTSIZE}]{TEXT}[/SIZE]',
		'u'     => '[U]{TEXT}[/U]',
		'url'   => '[URL={URL;useContent}]{TEXT}[/URL]',
	);

	/**
	* @var array Default templates, taken from bbcode::bbcode_tpl()
	*/
	protected $default_templates = array(
		'b'     => '<span style="font-weight: bold"><xsl:apply-templates/></span>',
		'i'     => '<span style="font-style: italic"><xsl:apply-templates/></span>',
		'u'     => '<span style="text-decoration: underline"><xsl:apply-templates/></span>',
		'img'   => '<img src="{IMAGEURL}" alt="{L_IMAGE}"/>',
		'size'  => '<span style="font-size: {FONTSIZE}%; line-height: normal"><xsl:apply-templates/></span>',
		'color' => '<span style="color: {COLOR}"><xsl:apply-templates/></span>',
		'email' => '<a href="mailto:{EMAIL}"><xsl:apply-templates/></a>',
	);

	/**
	* Constructor
	*
	* @param  phpbb\textformatter\data_access $dal
	* @param  phpbb\cache\driver\driver_interface $cache
	* @param  string $cache_dir          Path to the cache dir
	* @param  string $cache_key_parser   Cache key used for the parser
	* @param  string $cache_key_renderer Cache key used for the renderer
	* @return null
	*/
	public function __construct(\phpbb\textformatter\data_access $dal, \phpbb\cache\driver\driver_interface $cache, $cache_dir, $cache_key_parser, $cache_key_renderer)
	{
		$this->cache = $cache;
		$this->cache_dir = $cache_dir;
		$this->cache_key_parser = $cache_key_parser;
		$this->cache_key_renderer = $cache_key_renderer;

		$this->dal = $dal;
	}

	/**
	* {@inheritdoc}
	*/
	public function invalidate()
	{
		$this->regenerate();
	}

	/**
	* {@inheritdoc}
	*
	* Will remove old renderers from the cache dir but won't touch the current renderer
	*/
	public function tidy()
	{
		// Get the name of current renderer
		$renderer_data = $this->cache->get($this->cache_key_renderer);
		$renderer_file = ($renderer_data) ? $renderer_data['class'] . '.php' : null;

		foreach (glob($this->cache_dir . 's9e_*') as $filename)
		{
			// Only remove the file if it's not the current renderer
			if (!$renderer_file || substr($filename, -strlen($renderer_file)) !== $renderer_file)
			{
				unlink($filename);
			}
		}
	}

	/**
	* Generate and return a new configured instance of s9e\TextFormatter\Configurator
	*
	* @return Configurator
	*/
	public function get_configurator()
	{
		// Create a new Configurator
		$configurator = new Configurator;

		// Convert newlines to br elements by default
		$configurator->rootRules->enableAutoLineBreaks();

		// Don't automatically ignore text in places where text is not allowed
		$configurator->rulesGenerator->remove('IgnoreTextIfDisallowed');

		// Set the rendering engine and configure it to save to the cache dir
		$configurator->rendering->engine = 'PHP';
		$configurator->rendering->engine->cacheDir = $this->cache_dir;
		$configurator->rendering->engine->defaultClassPrefix = 's9e_renderer_';
		$configurator->rendering->engine->enableQuickRenderer = true;

		// Create custom filters for BBCode tokens that are supported in phpBB but not in
		// s9e\TextFormatter
		$filter = new RegexpFilter('#^' . get_preg_expression('relative_url') . '$#Du');
		$configurator->attributeFilters->add('#local_url', $filter);
		$configurator->attributeFilters->add('#relative_url', $filter);

		// INTTEXT regexp from acp_bbcodes
		$filter = new RegexpFilter('!^([\p{L}\p{N}\-+,_. ]+)$!Du');
		$configurator->attributeFilters->add('#inttext', $filter);

		// Create custom filters for Flash restrictions, which use the same values as the image
		// restrictions but have their own error message
		$configurator->attributeFilters
			->add('#flashheight', __NAMESPACE__ . '\\parser::filter_flash_height')
			->addParameterByName('max_img_height')
			->addParameterByName('logger');

		$configurator->attributeFilters
			->add('#flashwidth', __NAMESPACE__ . '\\parser::filter_flash_width')
			->addParameterByName('max_img_width')
			->addParameterByName('logger');

		// Create a custom filter for phpBB's per-mode font size limits
		$configurator->attributeFilters
			->add('#fontsize', __NAMESPACE__ . '\\parser::filter_font_size')
			->addParameterByName('max_font_size')
			->addParameterByName('logger')
			->markAsSafeInCSS();

		// Create a custom filter for image URLs
		$configurator->attributeFilters
			->add('#imageurl', __NAMESPACE__ . '\\parser::filter_img_url')
			->addParameterByName('urlConfig')
			->addParameterByName('logger')
			->addParameterByName('max_img_height')
			->addParameterByName('max_img_width')
			->markAsSafeAsURL();

		// Add default BBCodes
		foreach ($this->get_default_bbcodes($configurator) as $bbcode)
		{
			$configurator->BBCodes->addCustom($bbcode['usage'], $bbcode['template']);
		}

		// Modify the template to disable images/flash depending on user's settings
		foreach (array('FLASH', 'IMG') as $name)
		{
			$tag = $configurator->tags[$name];
			$tag->template = '<xsl:choose><xsl:when test="$S_VIEW' . $name . '">' . $tag->template . '</xsl:when><xsl:otherwise><xsl:apply-templates/></xsl:otherwise></xsl:choose>';
		}

		// Load custom BBCodes
		foreach ($this->dal->get_bbcodes() as $row)
		{
			// Insert the board's URL before {LOCAL_URL} tokens
			$tpl = preg_replace_callback(
				'#\\{LOCAL_URL\\d*\\}#',
				function ($m)
				{
					return generate_board_url() . '/' . $m[0];
				},
				$row['bbcode_tpl']
			);

			try
			{
				$configurator->BBCodes->addCustom($row['bbcode_match'], $tpl);
			}
			catch (\Exception $e)
			{
				/**
				* @todo log an error?
				*/
			}
		}

		// Load smilies
		foreach ($this->dal->get_smilies() as $row)
		{
			$configurator->Emoticons->add(
				$row['code'],
				'<img class="smilies" src="{$T_SMILIES_PATH}/' . htmlspecialchars($row['smiley_url']) . '" alt="{.}" title="' . htmlspecialchars($row['emotion']) . '"/>'
			);
		}

		if (isset($configurator->Emoticons))
		{
			// Force emoticons to be rendered as text if $S_VIEWSMILIES is not set
			$configurator->Emoticons->notIfCondition = 'not($S_VIEWSMILIES)';

			// Only parse emoticons at the beginning of the text or if they're preceded by any
			// one of: a new line, a space, a dot, or a right square bracket
			$configurator->Emoticons->notAfter = '[^\\n .\\]]';
		}

		// Load the censored words
		$censor = $this->dal->get_words();
		if (!empty($censor))
		{
			// Use a namespaced tag to avoid collisions
			$configurator->plugins->load('Censor', array('tagName' => 'censor:tag'));
			foreach ($censor as $row)
			{
				$configurator->Censor->add($row['word'],  $row['replacement']);
			}
		}

		// Load the magic links plugins. We do that after BBCodes so that they use the same tags
		$configurator->plugins->load('Autoemail');
		$configurator->plugins->load('Autolink', array('matchWww' => true));

		// Register some vars with a default value. Those should be set at runtime by whatever calls
		// the parser
		$configurator->registeredVars['max_font_size'] = 0;
		$configurator->registeredVars['max_img_height'] = 0;
		$configurator->registeredVars['max_img_width'] = 0;

		return $configurator;
	}

	/**
	* Regenerate and cache a new parser and renderer
	*
	* @return array Associative array with at least two elements: "parser" and "renderer"
	*/
	public function regenerate()
	{
		$configurator = $this->get_configurator();

		// Get the censor helper and remove the Censor plugin if applicable
		if (isset($configurator->Censor))
		{
			$censor = $configurator->Censor->getHelper();
			unset($configurator->Censor);
			unset($configurator->tags['censor:tag']);
		}

		// Create $parser and $renderer
		extract($configurator->finalize());

		// Cache the parser as-is
		$this->cache->put($this->cache_key_parser, $parser);

		// We need to cache the name of the renderer's generated class so that we can load the class
		// before the renderer is unserialized. That's why we save them together, with the renderer
		// in serialized form
		$renderer_data = array(
			'class'    => get_class($renderer),
			'renderer' => serialize($renderer)
		);
		if (isset($censor))
		{
			$renderer_data['censor'] = $censor;
		}
		$this->cache->put($this->cache_key_renderer, $renderer_data);

		return array('parser' => $parser, 'renderer' => $renderer);
	}

	/**
	* Return the default BBCodes configuration
	*
	* @return array 2D array. Each element has a 'usage' key, a 'template' key, and an optional 'options' key
	*/
	protected function get_default_bbcodes($configurator)
	{
		// For each BBCode, build an associative array matching style_ids to their template
		$templates = array();
		foreach ($this->dal->get_styles_templates() as $style_id => $data)
		{
			foreach ($this->extract_templates($data['template']) as $bbcode_name => $template)
			{
				$templates[$bbcode_name][$style_id] = $template;
			}

			// Add default templates wherever missing, or for BBCodes that were not specified in
			// this template's bitfield. For instance, prosilver has a custom template for b but its
			// bitfield does not enable it so the default template is used instead
			foreach ($this->default_templates as $bbcode_name => $template)
			{
				if (!isset($templates[$bbcode_name][$style_id]) || !in_array($bbcode_name, $data['bbcodes'], true))
				{
					$templates[$bbcode_name][$style_id] = $template;
				}
			}
		}

		// Replace custom tokens and normalize templates
		foreach ($templates as $bbcode_name => &$style_templates)
		{
			foreach ($style_templates as &$template)
			{
				if (isset($this->custom_tokens[$bbcode_name]))
				{
					$template = strtr($template, $this->custom_tokens[$bbcode_name]);
				}

				$template = $configurator->templateNormalizer->normalizeTemplate($template);
			}
			unset($template);
		}
		unset($style_templates);

		$bbcodes = array();
		foreach ($this->default_definitions as $bbcode_name => $usage)
		{
			$bbcodes[$bbcode_name] = array(
				'usage'    => $usage,
				'template' => $this->merge_templates($templates[$bbcode_name]),
			);
		}

		return $bbcodes;
	}

	/**
	* Extract and recompose individual BBCode templates from a style's template file
	*
	* @param  string $template Style template (bbcode.html)
	* @return array Associative array matching BBCode names to their template
	*/
	protected function extract_templates($template)
	{
		// Capture the template fragments
		preg_match_all('#<!-- BEGIN (.*?) -->(.*?)<!-- END .*? -->#s', $template, $matches, PREG_SET_ORDER);

		$fragments = array();
		foreach ($matches as $match)
		{
			// Normalize the whitespace
			$fragment = preg_replace('#>\\n\\t*<#', '><', trim($match[2]));

			$fragments[$match[1]] = $fragment;
		}

		// Automatically recompose templates split between *_open and *_close
		foreach ($fragments as $fragment_name => $fragment)
		{
			if (preg_match('#^(\\w+)_close$#', $fragment_name, $match))
			{
				$bbcode_name = $match[1];

				if (isset($fragments[$bbcode_name . '_open']))
				{
					$templates[$bbcode_name] = $fragments[$bbcode_name . '_open'] . '<xsl:apply-templates/>' . $fragment;
				}
			}
		}

		// Manually recompose and overwrite irregular templates
		$templates['list'] =
			'<xsl:choose>
				<xsl:when test="not(@type)">
					' . $fragments['ulist_open_default'] . '<xsl:apply-templates/>' . $fragments['ulist_close'] . '
				</xsl:when>
				<xsl:when test="contains(\'upperlowerdecim\',substring(@type,1,5))">
					' . $fragments['olist_open'] . '<xsl:apply-templates/>' . $fragments['olist_close'] . '
				</xsl:when>
				<xsl:otherwise>
					' . $fragments['ulist_open'] . '<xsl:apply-templates/>' . $fragments['ulist_close'] . '
				</xsl:otherwise>
			</xsl:choose>';

		$templates['li'] = $fragments['listitem'] . '<xsl:apply-templates/>' . $fragments['listitem_close'];

		$fragments['quote_username_open'] = str_replace(
			'{USERNAME}',
			'<xsl:choose>
				<xsl:when test="@url">' . str_replace('{DESCRIPTION}', '{USERNAME}', $fragments['url']) . '</xsl:when>
				<xsl:otherwise>{USERNAME}</xsl:otherwise>
			</xsl:choose>',
			$fragments['quote_username_open']
		);

		$templates['quote'] =
			'<xsl:choose>
				<xsl:when test="@author">
					' . $fragments['quote_username_open'] . '<xsl:apply-templates/>' . $fragments['quote_close'] . '
				</xsl:when>
				<xsl:otherwise>
					' . $fragments['quote_open'] . '<xsl:apply-templates/>' . $fragments['quote_close'] . '
				</xsl:otherwise>
			</xsl:choose>';

		// The [attachment] BBCode uses the inline_attachment template to output a comment that
		// is post-processed by parse_attachments()
		$templates['attachment'] = $fragments['inline_attachment_open'] . '<xsl:comment> ia<xsl:value-of select="@index"/> </xsl:comment><xsl:value-of select="@filename"/><xsl:comment> ia<xsl:value-of select="@index"/> </xsl:comment>' . $fragments['inline_attachment_close'];

		// Add fragments as templates
		foreach ($fragments as $fragment_name => $fragment)
		{
			if (preg_match('#^\\w+$#', $fragment_name))
			{
				$templates[$fragment_name] = $fragment;
			}
		}

		// Keep only templates that are named after an existing BBCode
		$templates = array_intersect_key($templates, $this->default_definitions);

		return $templates;
	}

	/**
	* Merge the templates from any number of styles into one BBCode template
	*
	* @param  array  $style_templates Associative array matching style_ids to their template
	* @return string
	*/
	protected function merge_templates(array $style_templates)
	{
		// Group identical templates together
		$grouped_templates = array();
		foreach ($style_templates as $style_id => $style_template)
		{
			$grouped_templates[$style_template][] = $style_id;
		}

		if (count($grouped_templates) === 1)
		{
			return $style_template;
		}

		// Sort templates by frequency descending
		$templates_cnt = array_map('sizeof', $grouped_templates);
		array_multisort($grouped_templates, $templates_cnt);

		// Remove the most frequent template from the list; It becomes the default
		reset($grouped_templates);
		$default_template = key($grouped_templates);
		unset($grouped_templates[$default_template]);

		// Build an xsl:choose switch
		$template = '<xsl:choose>';
		foreach ($grouped_templates as $style_template => $style_ids)
		{
			$template .= '<xsl:when test="$STYLE_ID=' . implode(' or $STYLE_ID=', $style_ids) . '">' . $style_template . '</xsl:when>';
		}
		$template .= '<xsl:otherwise>' . $default_template . '</xsl:otherwise></xsl:choose>';

		return $template;
	}
}
