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
use s9e\TextFormatter\Configurator\Items\AttributeFilters\RegexpFilter;
use s9e\TextFormatter\Configurator\Items\UnsafeTemplate;

/**
* Creates s9e\TextFormatter objects
*/
class factory implements \phpbb\textformatter\cache_interface
{
	/**
	* @var \phpbb\textformatter\s9e\link_helper
	*/
	protected $link_helper;

	/**
	* @var \phpbb\cache\driver\driver_interface
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
	* @var \phpbb\config\config
	*/
	protected $config;

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
	protected $data_access;

	/**
	* @var array Default BBCode definitions
	*/
	protected $default_definitions = array(
		'attachment' => '[ATTACHMENT index={NUMBER} filename={TEXT;useContent}]',
		'b'     => '[B]{TEXT}[/B]',
		'code'  => '[CODE lang={IDENTIFIER;optional}]{TEXT}[/CODE]',
		'color' => '[COLOR={COLOR}]{TEXT}[/COLOR]',
		'email' => '[EMAIL={EMAIL;useContent} subject={TEXT1;optional;postFilter=rawurlencode} body={TEXT2;optional;postFilter=rawurlencode}]{TEXT}[/EMAIL]',
		'flash' => '[FLASH={NUMBER1},{NUMBER2} width={NUMBER1;postFilter=#flashwidth} height={NUMBER2;postFilter=#flashheight} url={URL;useContent} /]',
		'i'     => '[I]{TEXT}[/I]',
		'img'   => '[IMG src={IMAGEURL;useContent}]',
		'list'  => '[LIST type={HASHMAP=1:decimal,a:lower-alpha,A:upper-alpha,i:lower-roman,I:upper-roman;optional;postFilter=#simpletext} #createChild=LI]{TEXT}[/LIST]',
		'li'    => '[* $tagName=LI]{TEXT}[/*]',
		'quote' =>
			"[QUOTE
				author={TEXT1;optional}
				post_id={UINT;optional}
				post_url={URL;optional;postFilter=#false}
				profile_url={URL;optional;postFilter=#false}
				time={UINT;optional}
				url={URL;optional}
				user_id={UINT;optional}
				author={PARSE=/^\\[url=(?'url'.*?)](?'author'.*)\\[\\/url]$/i}
				author={PARSE=/^\\[url](?'author'(?'url'.*?))\\[\\/url]$/i}
				author={PARSE=/(?'url'https?:\\/\\/[^[\\]]+)/i}
			]{TEXT2}[/QUOTE]",
		'size'  => '[SIZE={FONTSIZE}]{TEXT}[/SIZE]',
		'u'     => '[U]{TEXT}[/U]',
		'url'   => '[URL={URL;useContent} $forceLookahead=true]{TEXT}[/URL]',
	);

	/**
	* @var array Default templates, taken from bbcode::bbcode_tpl()
	*/
	protected $default_templates = array(
		'b'     => '<span style="font-weight: bold"><xsl:apply-templates/></span>',
		'i'     => '<span style="font-style: italic"><xsl:apply-templates/></span>',
		'u'     => '<span style="text-decoration: underline"><xsl:apply-templates/></span>',
		'img'   => '<img src="{IMAGEURL}" class="postimage" alt="{L_IMAGE}"/>',
		'size'  => '<span style="font-size: {FONTSIZE}%; line-height: normal"><xsl:apply-templates/></span>',
		'color' => '<span style="color: {COLOR}"><xsl:apply-templates/></span>',
		'email' => '<a>
			<xsl:attribute name="href">
				<xsl:text>mailto:</xsl:text>
				<xsl:value-of select="@email"/>
				<xsl:if test="@subject or @body">
					<xsl:text>?</xsl:text>
					<xsl:if test="@subject">subject=<xsl:value-of select="@subject"/></xsl:if>
					<xsl:if test="@body"><xsl:if test="@subject">&amp;</xsl:if>body=<xsl:value-of select="@body"/></xsl:if>
				</xsl:if>
			</xsl:attribute>
			<xsl:apply-templates/>
		</a>',
	);

	/**
	* @var \phpbb\event\dispatcher_interface
	*/
	protected $dispatcher;

	/**
	* @var \phpbb\log\log_interface
	*/
	protected $log;

	/**
	* Constructor
	*
	* @param \phpbb\textformatter\data_access $data_access
	* @param \phpbb\cache\driver\driver_interface $cache
	* @param \phpbb\event\dispatcher_interface $dispatcher
	* @param \phpbb\config\config $config
	* @param \phpbb\textformatter\s9e\link_helper $link_helper
	* @param \phpbb\log\log_interface $log
	* @param string $cache_dir          Path to the cache dir
	* @param string $cache_key_parser   Cache key used for the parser
	* @param string $cache_key_renderer Cache key used for the renderer
	*/
	public function __construct(\phpbb\textformatter\data_access $data_access, \phpbb\cache\driver\driver_interface $cache, \phpbb\event\dispatcher_interface $dispatcher, \phpbb\config\config $config, \phpbb\textformatter\s9e\link_helper $link_helper, \phpbb\log\log_interface $log, $cache_dir, $cache_key_parser, $cache_key_renderer)
	{
		$this->link_helper = $link_helper;
		$this->cache = $cache;
		$this->cache_dir = $cache_dir;
		$this->cache_key_parser = $cache_key_parser;
		$this->cache_key_renderer = $cache_key_renderer;
		$this->config = $config;
		$this->data_access = $data_access;
		$this->dispatcher = $dispatcher;
		$this->log = $log;
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

		/**
		* Modify the s9e\TextFormatter configurator before the default settings are set
		*
		* @event core.text_formatter_s9e_configure_before
		* @var \s9e\TextFormatter\Configurator configurator Configurator instance
		* @since 3.2.0-a1
		*/
		$vars = array('configurator');
		extract($this->dispatcher->trigger_event('core.text_formatter_s9e_configure_before', compact($vars)));

		// Reset the list of allowed schemes
		foreach ($configurator->urlConfig->getAllowedSchemes() as $scheme)
		{
			$configurator->urlConfig->disallowScheme($scheme);
		}
		foreach (explode(',', $this->config['allowed_schemes_links']) as $scheme)
		{
			$configurator->urlConfig->allowScheme(trim($scheme));
		}

		// Convert newlines to br elements by default
		$configurator->rootRules->enableAutoLineBreaks();

		// Don't automatically ignore text in places where text is not allowed
		$configurator->rulesGenerator->remove('IgnoreTextIfDisallowed');

		// Don't remove comments and instead convert them to xsl:comment elements
		$configurator->templateNormalizer->remove('RemoveComments');
		$configurator->templateNormalizer->add('TransposeComments');

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
			->markAsSafeAsURL()
			->setJS('UrlFilter.filter');

		// Add default BBCodes
		foreach ($this->get_default_bbcodes($configurator) as $bbcode)
		{
			$this->add_bbcode($configurator, $bbcode['usage'], $bbcode['template']);
		}
		if (isset($configurator->tags['QUOTE']))
		{
			// Remove the nesting limit and let other services remove quotes at parsing time
			$configurator->tags['QUOTE']->nestingLimit = PHP_INT_MAX;
		}

		// Modify the template to disable images/flash depending on user's settings
		foreach (array('FLASH', 'IMG') as $name)
		{
			$tag = $configurator->tags[$name];
			$tag->template = '<xsl:choose><xsl:when test="$S_VIEW' . $name . '">' . $tag->template . '</xsl:when><xsl:otherwise><xsl:apply-templates/></xsl:otherwise></xsl:choose>';
		}

		// Load custom BBCodes
		foreach ($this->data_access->get_bbcodes() as $row)
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
			$this->add_bbcode($configurator, $row['bbcode_match'], $tpl);
		}

		// Load smilies
		foreach ($this->data_access->get_smilies() as $row)
		{
			$configurator->Emoticons->set(
				$row['code'],
				'<img class="smilies" src="{$T_SMILIES_PATH}/' . $this->escape_html_attribute($row['smiley_url']) . '" width="' . $row['smiley_width'] . '" height="' . $row['smiley_height'] . '" alt="{.}" title="' . $this->escape_html_attribute($row['emotion']) . '"/>'
			);
		}

		if (isset($configurator->Emoticons))
		{
			// Force emoticons to be rendered as text if $S_VIEWSMILIES is not set
			$configurator->Emoticons->notIfCondition = 'not($S_VIEWSMILIES)';

			// Only parse emoticons at the beginning of the text or if they're preceded by any
			// one of: a new line, a space, a dot, or a right square bracket
			$configurator->Emoticons->notAfter = '[^\\n .\\]]';

			// Ignore emoticons that are immediately followed by a "word" character
			$configurator->Emoticons->notBefore = '\\w';
		}

		// Load the censored words
		$censor = $this->data_access->get_censored_words();
		if (!empty($censor))
		{
			// Use a namespaced tag to avoid collisions
			$configurator->plugins->load('Censor', array('tagName' => 'censor:tag'));
			foreach ($censor as $row)
			{
				$configurator->Censor->add($row['word'], $row['replacement']);
			}
		}

		// Load the magic links plugins. We do that after BBCodes so that they use the same tags
		$this->configure_autolink($configurator);

		// Register some vars with a default value. Those should be set at runtime by whatever calls
		// the parser
		$configurator->registeredVars['max_font_size'] = 0;
		$configurator->registeredVars['max_img_height'] = 0;
		$configurator->registeredVars['max_img_width'] = 0;

		// Load the Emoji plugin and modify its tag's template to obey viewsmilies
		$tag = $configurator->Emoji->getTag();
		$tag->template = '<xsl:choose><xsl:when test="$S_VIEWSMILIES">' . str_replace('class="emoji"', 'class="emoji smilies"', $tag->template) . '</xsl:when><xsl:otherwise><xsl:value-of select="."/></xsl:otherwise></xsl:choose>';

		/**
		* Modify the s9e\TextFormatter configurator after the default settings are set
		*
		* @event core.text_formatter_s9e_configure_after
		* @var \s9e\TextFormatter\Configurator configurator Configurator instance
		* @since 3.2.0-a1
		*/
		$vars = array('configurator');
		extract($this->dispatcher->trigger_event('core.text_formatter_s9e_configure_after', compact($vars)));

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

		$objects = $configurator->finalize();

		/**
		* Access the objects returned by finalize() before they are saved to cache
		*
		* @event core.text_formatter_s9e_configure_finalize
		* @var array objects Array containing a "parser" object, a "renderer" object and optionally a "js" string
		* @since 3.2.2-RC1
		*/
		$vars = array('objects');
		extract($this->dispatcher->trigger_event('core.text_formatter_s9e_configure_finalize', compact($vars)));

		$parser   = $objects['parser'];
		$renderer = $objects['renderer'];

		// Cache the parser as-is
		$this->cache->put($this->cache_key_parser, $parser);

		// We need to cache the name of the renderer's generated class
		$renderer_data = array('class' => get_class($renderer));
		if (isset($censor))
		{
			$renderer_data['censor'] = $censor;
		}
		$this->cache->put($this->cache_key_renderer, $renderer_data);

		return array('parser' => $parser, 'renderer' => $renderer);
	}

	/**
	* Add a BBCode to given configurator
	*
	* @param  Configurator $configurator
	* @param  string       $usage
	* @param  string       $template
	* @return void
	*/
	protected function add_bbcode(Configurator $configurator, $usage, $template)
	{
		try
		{
			$configurator->BBCodes->addCustom($usage, new UnsafeTemplate($template));
		}
		catch (\Exception $e)
		{
			$this->log->add('critical', null, null, 'LOG_BBCODE_CONFIGURATION_ERROR', false, [$usage, $e->getMessage()]);
		}
	}

	/**
	* Configure the Autolink / Autoemail plugins used to linkify text
	*
	* @param  \s9e\TextFormatter\Configurator $configurator
	* @return void
	*/
	protected function configure_autolink(Configurator $configurator)
	{
		$configurator->plugins->load('Autoemail');
		$configurator->plugins->load('Autolink', array('matchWww' => true));

		// Add a tag filter that creates a tag that stores and replace the
		// content of a link created by the Autolink plugin
		$configurator->Autolink->getTag()->filterChain
			->add(array($this->link_helper, 'generate_link_text_tag'))
			->resetParameters()
			->addParameterByName('tag')
			->addParameterByName('parser');

		// Create a tag that will be used to display the truncated text by
		// replacing the original content with the content of the @text attribute
		$tag = $configurator->tags->add('LINK_TEXT');
		$tag->attributes->add('text');
		$tag->template = '<xsl:value-of select="@text"/>';

		$tag->filterChain
			->add(array($this->link_helper, 'truncate_local_url'))
			->resetParameters()
			->addParameterByName('tag')
			->addParameterByValue(generate_board_url() . '/');
		$tag->filterChain
			->add(array($this->link_helper, 'truncate_text'))
			->resetParameters()
			->addParameterByName('tag');
		$tag->filterChain
			->add(array($this->link_helper, 'cleanup_tag'))
			->resetParameters()
			->addParameterByName('tag')
			->addParameterByName('parser');
	}

	/**
	* Escape a literal to be used in an HTML attribute in an XSL template
	*
	* Escapes "HTML special chars" for obvious reasons and curly braces to avoid them
	* being interpreted as an attribute value template
	*
	* @param  string $value Original string
	* @return string        Escaped string
	*/
	protected function escape_html_attribute($value)
	{
		return htmlspecialchars(strtr($value, ['{' => '{{', '}' => '}}']), ENT_COMPAT | ENT_XML1, 'UTF-8');
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
		foreach ($this->data_access->get_styles_templates() as $style_id => $data)
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
		foreach ($templates as $bbcode_name => $style_templates)
		{
			foreach ($style_templates as $i => $template)
			{
				if (isset($this->custom_tokens[$bbcode_name]))
				{
					$template = strtr($template, $this->custom_tokens[$bbcode_name]);
				}

				$templates[$bbcode_name][$i] = $configurator->templateNormalizer->normalizeTemplate($template);
			}
		}

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
		// Allow either phpBB template or the Twig syntax
		preg_match_all('#<!-- BEGIN (.*?) -->(.*?)<!-- END .*? -->#s', $template, $matches, PREG_SET_ORDER) ?:
			preg_match_all('#{% for (.*?) in .*? %}(.*?){% endfor %}#s', $template, $matches, PREG_SET_ORDER);

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

		// Replace the regular quote template with the extended quote template if available
		if (isset($fragments['quote_extended']))
		{
			$templates['quote'] = $fragments['quote_extended'];
		}

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
	* When multiple templates are available for the same BBCode (because of multiple styles) we
	* merge them into a single template that uses an xsl:choose construct that determines which
	* style to use at rendering time.
	*
	* @param  array  $style_templates Associative array matching style_ids to their template
	* @return string
	*/
	protected function merge_templates(array $style_templates)
	{
		// Return the template as-is if there's only one style or all styles share the same template
		if (count(array_unique($style_templates)) === 1)
		{
			return end($style_templates);
		}

		// Group identical templates together
		$grouped_templates = array();
		foreach ($style_templates as $style_id => $style_template)
		{
			$grouped_templates[$style_template][] = '$STYLE_ID=' . $style_id;
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
		foreach ($grouped_templates as $style_template => $exprs)
		{
			$template .= '<xsl:when test="' . implode(' or ', $exprs) . '">' . $style_template . '</xsl:when>';
		}
		$template .= '<xsl:otherwise>' . $default_template . '</xsl:otherwise></xsl:choose>';

		return $template;
	}
}
