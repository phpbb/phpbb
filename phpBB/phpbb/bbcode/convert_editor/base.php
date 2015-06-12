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

namespace phpbb\bbcode\convert_editor;

use \phpbb\request\request_interface;
use \phpbb\bbcode\js_regex_helper;

/**
* 
*/
abstract class base
{

	/**
	 *
	 * The constants for the get_available_modes() mask
	 *
	 */
	const HAS_BUTTON_MODE_ICON 		= 0x1;
	const HAS_BUTTON_MODE_TEXT 		= 0x2;
	const HAS_BUTTON_MODE_ICON_TEXT = 0x4;
	// Some buttons are icons, others are text. It depends on what it is. E.g. Default CKE toolbar.
	const HAS_BUTTON_MODE_MIXED 	= 0x8;

	/**
	 *
	 * The constants for the get_available_button_modes() mask
	 *
	 */
	const DEFAULT_WYSIWYG_MODE	= 0x1;
	const DEFAULT_SOURCE_MODE	= 0x2;
	const DEFAULT_MIXED_MODE	= 0x4;

	const EDITOR_CONFIG_BASENAME = 'editor_config_';
	const EDITOR_BBCODE_TOOLTIP_PREPEND = 'L_BBCODE_HELP_';

	const BBCODE_PROCESS_HANDLE = 'wysiwyg.text_formatter.s9e.factory';

	protected $toolbar_default_ordering;

	/**
	 * cache object
	 * @var \phpbb\cache\driver\driver_interface
	 */
	protected $cache;

	/**
	 * cache name prefix (includes the path)
	 * @var string
	 */
	protected $cache_prefix;

	/**
	 * Config object
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	* Event dispatcher object
	* @var \phpbb\bbcode\text_formatter_data_access
	*/
	protected $data_access;

	/**
	* Event dispatcher object
	* @var \phpbb\event\dispatcher_interface
	*/
	protected $phpbb_dispatcher;

	/**
	* Object loader object
	* @var \Symfony\Component\DependencyInjection\ContainerInterface
	*/
	protected $phpbb_container;

	/**
	* Request object
	* @var \phpbb\request\request
	*/
	protected $request;

	/**
	* Template object
	* @var \phpbb\template\template
	*/
	protected $template;

	/**
	 * Constructor
	 * 
	 *
	 * @param \phpbb\cache\driver\driver_interface $cache Cache object
	 * @param string $cache_prefix A string to prefix to the cache file name (includes the path)
	 * @param \phpbb\config\config $config Config object
	 * @param \phpbb\bbcode\text_formatter_data_access $data_access The one responsible for BBCode DB communication
	 * @param \phpbb\event\dispatcher_interface $phpbb_dispatcher Where to send events to
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $phpbb_container 
	 * @param \phpbb\request\request $request To handle the HTTP cache
	 * @param \phpbb\template\template $template 
	 */
	protected function __construct(\phpbb\cache\driver\driver_interface $cache, $cache_prefix,
		\phpbb\config\config $config, \phpbb\bbcode\text_formatter_data_access $data_access, \phpbb\event\dispatcher_interface $phpbb_dispatcher,
		\Symfony\Component\DependencyInjection\ContainerInterface $phpbb_container, 
		\phpbb\request\request $request, \phpbb\template\template $template)
	{
		$this->cache = $cache;
		$this->cache_prefix = $cache_prefix;
		$this->config = $config;
		$this->data_access = $data_access;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->phpbb_container = $phpbb_container;
		$this->request = $request;
		$this->template = $template;

		
		$this->toolbar_default_ordering = array(
			array('b', 'i', 'u'),
			array('quote', 'code'),
			array('list', '*'),
			array('img', 'url'),
			array('flash'),
			array('size', 'color'),
		);

	}

	/**
	 * This is the list of HTML void elements as defined by w3c
	 * @link http://www.w3.org/TR/html-markup/syntax.html#void-elements
	 * This list is meant to be used with the php isset() construct
	 *
	*/
	// const HTML_VOID_ELEMENTS = array(
									// 'area'		=> 1,
									// 'base'		=> 1,
									// 'br'		=> 1,
									// 'col'		=> 1,
									// 'command'	=> 1,
									// 'embed'		=> 1,
									// 'hr'		=> 1,
									// 'img'		=> 1,
									// 'input'		=> 1,
									// 'keygen'	=> 1,
									// 'link'		=> 1,
									// 'meta'		=> 1,
									// 'param'		=> 1,
									// 'source'	=> 1,
									// 'track'		=> 1,
									// 'wbr'	=> 1,
								// );

	
	abstract protected function generate_editor_setup_javascript($text_formatter_factory);

	abstract protected function get_static_javascript_variables();
	abstract protected function get_dynamic_javascript_variables();

	/**
	 * The name of the editor this class corresponds to.
	 * The result should only contain alphanumeric characters
	 * NOTE: The lower case of the value is used to identify file names
	 *
	 */
	abstract public function get_name();

	private function calculate_path_key()
	{
		$cache_name = $this->get_name();
		$this->template->set_filenames(array(
				'editor_config' => self::EDITOR_CONFIG_BASENAME . strtolower($cache_name) . '.js',
				'bbcode' => 'bbcode.html',
			));

		return md5($this->template->get_source_file_for_handle('editor_config') . 
						$this->template->get_source_file_for_handle('bbcode'));
	}

	public function purge_cache($force_all = false)
	{
		$this->cache->destroy('wysiwyg_data');

		if ($force_all)
		{
			$files = glob($this->cache_prefix . '*.js');
			foreach ($files as $file)
			{
				@unlink($file);
			}

			$files = glob($this->cache_prefix . '*.js.gz');
			foreach ($files as $file)
			{
				@unlink($file);
			}
		}
	}

	public function recalculate_editor_setup_javascript()
	{
		$cache_name = $this->get_name();

		$this->generate_editor_setup_javascript($this->phpbb_container->get(self::BBCODE_PROCESS_HANDLE));

		$editor_setup_vars = $this->get_static_javascript_variables();
		$editor_dynamic_vars = $this->get_dynamic_javascript_variables();

		$setup_javascript = $this->template->set_filenames(array(
			'editor_config' => self::EDITOR_CONFIG_BASENAME . strtolower($cache_name) . '.js',
			'bbcode' => 'bbcode.html',
		))
			->assign_vars($editor_setup_vars)
			->assign_vars(array(
				'EDITOR_JS_GLOBAL_OBJ' => \phpbb\bbcode\xsl_parse_helper::EDITOR_JS_GLOBAL_OBJ,
			))
			->assign_display('editor_config');

		// md4 would probably be a better option for performance reasons but this works fine
		$path_key = md5($this->template->get_source_file_for_handle('editor_config') . 
						$this->template->get_source_file_for_handle('bbcode'));

		$gzip_setup_javascript = gzencode($setup_javascript, 9);

		
		$etag = sha1($setup_javascript);
		$uncompressed_etag = $cache_name . '|'. $path_key .'|normal|' . $etag;
		$compressed_etag = $cache_name . '|'. $path_key .'|gzip|'. $etag;

		$file_name = $this->cache_prefix . '.' . $path_key . '.' . $cache_name . '.js';
		file_put_contents($file_name, $setup_javascript, LOCK_EX);
		file_put_contents($file_name . '.gz', $gzip_setup_javascript, LOCK_EX);

		// Only change or create the cached information after changing the files
		// This prevents corrupted data in the client

		$wysiwyg_data = array(
			$path_key => array(
				'wysiwyg_dynamic_js_vars' . $cache_name => $editor_dynamic_vars,
				'wysiwyg_etag' . $cache_name 			=> $uncompressed_etag,
				'wysiwyg_etag_gzip' . $cache_name 		=> $compressed_etag,
			),
		);

		$this->cache->put('wysiwyg_data', $wysiwyg_data);

		$this->config->increment('bbcode_version', 1);
	}

	/**
	 * This returns the javascript calculated
	 *
	 *
	 *
	 */
	public function get_setup_javascript($path_key = null, $use_gz_version = false)
	{
		if (empty($path_key))
		{
			$path_key = $this->calculate_path_key();
		}
		$file_name = $this->cache_prefix . '.' . $path_key . '.' . $this->get_name() . '.js';

		if($use_gz_version)
		{
			$file_name .= '.gz';
		}

		if (!file_exists($file_name))
		{
			return false;
		}

		$read_js = file_get_contents($file_name);

		
		return $read_js;
	}

	/**
	 * This handles all the required HTTP cache headers additionally to
	 * get_setup_javascript()
	 * If successful (returning true), the headers were set and the setup javascript was outputed to the user or
	 * the headers were set and status code 304 NOT MODIFIED was sent.
	 *
	 * @return boolean true on success, false on unknown failure
	 */
	public function handle_user_request_setup_javascript()
	{
		$cache_name = $this->get_name();
		$cached_data = $this->cache->get('wysiwyg_data');

		$accepted_encodings = $this->request->variable("HTTP_ACCEPT_ENCODING", '', request_interface::SERVER);

		header('Content-Type: application/javascript', true);
		// TODO: Find a better way to identify this
		// header('Vary: Accept-Encoding, cookie', true);
		// 1 week
		header('Cache-Control: public, max-age=604800', true);

		$etag_match = $this->request->variable("HTTP_IF_NONE_MATCH", '', request_interface::SERVER);

		$path_key = $this->calculate_path_key();
		if (empty($cached_data[$path_key]))
		{
			$this->recalculate_editor_setup_javascript($this->phpbb_container->get(self::BBCODE_PROCESS_HANDLE));
		}
		if (!empty($etag_match))
		{
			$etag_data = explode('|', $etag_match, 4);
			$current_etag = null;

			if ($etag_data[2] === $path_key)
			{
				if ($etag_data[3] === 'gzip')
				{
					$current_etag = $cached_data[$path_key]['wysiwyg_etag_gzip'. $cache_name];
					header('Content-Encoding: gzip', true);
				}
				else
				{
					$current_etag = $cached_data[$path_key]['wysiwyg_etag' . $cache_name];
				}

				if ($current_etag === $etag_match)
				{
					// not modified
					header('', false, 304);
					return true;
				}
			}
		}

		$accepts_gzip = strpos($accepted_encodings, 'gzip') !== false;

		$setup_javascript = $this->get_setup_javascript($accepts_gzip);

		if ($setup_javascript !== false)
		{
			if ($accepts_gzip)
			{
				header('Content-Encoding: gzip', true);
			}
			echo $setup_javascript;
			return true;
		}

		$setup_javascript = $this->get_setup_javascript($accepts_gzip);
		if ($setup_javascript !== false)
		{
			if ($accepts_gzip)
			{
				header('Content-Encoding: gzip', true);
			}
			echo $setup_javascript;
			return true;
		}
		return false;
	}

	public function get_request_variables()
	{
		$cache_name = $this->get_name();
		$cached_data = $this->cache->get('wysiwyg_data');

		$path_key = $this->calculate_path_key();

		$dynamic_javascript_vars = $cached_data[$path_key]['wysiwyg_dynamic_js_vars' . $cache_name];

		if ($dynamic_javascript_vars === false)
		{
			return false;
		}
		return $dynamic_javascript_vars;

	}


	protected function get_container_tags($child_nodes)
	{
		foreach ($child_nodes as $child_node)
		{
			if (isset($child_node['case']))
			{
				$containers = array();
				foreach($child_node['case'] as $case)
				{
					$tag = $this->get_container_tags($case['children']);
					$containers = array_merge($containers, $tag);
				}

				return $containers;
			}
			else if ($child_node['xsl'])
			{
				return $this->get_container_tags($child_node['children']);
			}
			else
			{
				return array($child_node['tagName']);
			}
		}

		return array();

	}

	protected function add_tooltip_text(&$bbcodes_data, $bbcode_settings)
	{
		foreach($bbcodes_data as $bbcode_name => &$bbcode_data)
		{
			if (empty($bbcode_settings[$bbcode_name]['bbcode_helpline']))
			{
				$bbcode_data['tooltip_lang'] = self::EDITOR_BBCODE_TOOLTIP_PREPEND . strtoupper($bbcode_name);
			}
			else
			{
				$bbcode_data['tooltip_text'] = $bbcode_settings[$bbcode_name]['bbcode_helpline'];
			}
		}
	}

	protected function get_bbcodes_for_tags($bbcodes)
	{
		$tag_to_BBCodes = array();

		foreach($bbcodes as $bbcode_name => $bbcode)
		{
			$tag_to_BBCodes[$bbcode->tagName][] = $bbcode_name;
		}

		return $tag_to_BBCodes;
	}

	protected function filter_out_tags_without_bbcode($bbcodes_for_tags, $tags)
	{
		foreach ($tags as $name => $data)
		{
			if (empty($bbcodes_for_tags[$name]))
			{
				$bbcodes_for_tags[$name] = array();
			}
		}

		return $bbcodes_for_tags;
	}

	protected function get_all_tag_names($tags)
	{
		$tagnames = array();
		foreach ($tags as $name => $data)
		{
			$tagnames[] = $name;
		}

		return $tagnames;
	}

	public function extract_and_normalize_bbcode_data($bbcodes, $tags, $low_case_names = true)
	{
		$bbcodes_data = array();

		$bbcodes_for_tags = $this->get_bbcodes_for_tags($bbcodes);
		$bbcodes_for_tags = $this->filter_out_tags_without_bbcode($bbcodes_for_tags, $tags);

		if ($low_case_names)
		{
			foreach($bbcodes_for_tags as &$bbcodes_for_tag)
			{
				$bbcodes_for_tag = array_map('strtolower', $bbcodes_for_tag);
			}
		}

		
		$tag_names = $this->get_all_tag_names($tags);

		foreach ($bbcodes as $bbcode_name => $bbcode){
			$this_data = $this->normalize_text_parser_data($bbcode, $tags[$bbcode->tagName], $tag_names);

			
			foreach(array('deniedChildren', 'allowedChildren', 
					'deniedDescendants', 'allowedDecendants') as $key)
			{
				$bbcode_list = array();
				foreach ($this_data[$key] as $current_tag)
				{
					foreach ($bbcodes_for_tags[$current_tag] as $bbcode)
					{
						$bbcode_list[] = $bbcode;
					}
				}

				$this_data[$key] = $bbcode_list;
			}
			if (isset($this_data['autoCloseOn'])){
				$bbcode_list = array();
				foreach ($this_data['autoCloseOn'] as $current_tag)
				{
					foreach ($bbcodes_for_tags[$current_tag] as $bbcode)
					{
						$bbcode_list[] = $bbcode;
					}
				}

				$this_data['autoCloseOn'] = $bbcode_list;
			}

			if ($low_case_names)
			{
				$bbcodes_data[strtolower($bbcode_name)] = $this_data;
			}
			else
			{
				$bbcodes_data[$bbcode_name] = $this_data;
			}
		}

		$bbcode_settings = $this->data_access->get_bbcodes_settings();
		$this->add_tooltip_text($bbcodes_data, $bbcode_settings);

		return $bbcodes_data;

	}

	public function normalize_text_parser_data($bbcode, $tag, $tag_names = array())
	{
		$config = array();
		$config['useContent'] = array();

		foreach ($bbcode->contentAttributes as $use_content_attribute)
		{
			$config['useContent'][] = $use_content_attribute;
		}

		$config['defaultAttribute'] = $bbcode->defaultAttribute;
		$config['onlyParseIfClosed'] = $bbcode->forceLookahead;

		$config['attrPresets'] = array();
		foreach ($bbcode->predefinedAttributes as $name => $preset)
		{
			$config['attrPresets'][$name] = $preset;
		}
		// Only parse BBCode if (the closing tag was written
		$config['onlyParseIfClosed'] = $bbcode->forceLookahead;
		// This may not be the same the the actual tag name. From the source code:
		// // Create [php] as an alias for [code=php]
		// $bbcode = $configurator->BBCodes->add('php');
		// $bbcode->tagName = 'CODE';
		// $bbcode->predefinedAttributes['lang'] = 'php';
		// That creates a "php" tag that is parsed the same way as a "code" tag with the language as "php"
		$config['bbcodeName'] = strtolower($bbcode->tagName);

		$attribute_list = &$config['attr'];
		$attribute_list = array();

		foreach ($tag->attributes as $name => $attribute)
		{
			$settings = array();
			$settings['required'] = $attribute->required;
			$settings['defaultValue'] = $attribute->defaultValue;
			$settings['filters'] = array();
			foreach ($attribute->filterChain as $filter)
			{
				$js_validation = $filter->getJS();
				if ($js_validation)
				{
					$js_validation = $js_validation->__toString();
					if (strpos($js_validation, 'BuiltInFilters.') === 0)
					{
						$new_filter = array(
							'name'		=> str_replace('BuiltInFilters.', '', $js_validation),
							'extraVars' => '',
						);

						foreach ($filter->getVars() as $var)
						{
							$new_filter['extraVars'] .= ', ' . json_encode($var);
						}

						$settings['filters'][] = $new_filter;
					}
					else if (strpos($js_validation, 'function') === 0)
					{
						$new_filter = array(
							'inlineFunc' => $js_validation,
							'extraVars' => '',
						);

						foreach ($filter->getVars() as $var)
						{
							$new_filter['extraVars'] .= ', ' . json_encode($var);
						}

						$settings['filters'][] = $new_filter;
					}
					else if ($js_validation === '')
					{
						// Skip
						// TODO: See if (it is feasable not to skip
						continue;
					}

				}
				// else
				// {
					// // TODO: Check better here what this is about
					// $settings['validations'][] = array(
						// 'name' 		=> explode('::', $validation->getCallback(), 2)[1],
						// 'extraVars'	=> array(),
					// );
				// }

			}
			$attribute_list[$name] = $settings;
		}

		// Some code to execute that, supposedly, eases handling to whomever is typing the BBCode
		$config['preProcessors'] = array();
		foreach ($tag->attributePreprocessors as $target_attribute => $pre_processor)
		{
			$regex = $pre_processor->getRegexp();
			$match_vs_attribute = array();
			$errors = null;
			$data = array(
				'sourceAttribute' => $target_attribute,
			);
			$regex = str_replace('&quot;', '"', $regex);
			$transformation_results = js_regex_helper::to_js($regex);

			$data['regexFixed'] = $transformation_results['jsRegex'];
			$data['modifiersFixed'] = $transformation_results['modifiers'];
			$data['matchNumVsAttr'] = $transformation_results['matchesVsNames'];
			$config['preProcessors'][] = $data;
		}

		$config['allowedChildren'] = array();
		$config['deniedChildren'] = array();
		$config['allowedDecendants'] = array();
		$config['deniedDescendants'] = array();

		foreach($tag->rules as $rule_name => $rule)
		{
			switch($rule_name)
			{
				case 'denyChild':
					$config['deniedChildren'] = array_merge($config['deniedChildren'], $rule);
					$config['allowedChildren'] = array_merge(array_diff($tag_names, $rule), $config['allowedChildren']);

				break;
				case 'allowChild':

					$config['allowedChildren'] = array_merge($config['allowedChildren'], $rule);

				break;
				case 'denyDescendant':

					$config['deniedDescendants'] = array_merge($config['deniedDescendants'], $rule);
					$config['allowedDecendants'] = array_merge(array_diff($tag_names, $rule), $config['allowedDecendants']);

				break;
				case 'allowDescendant':

					$config['allowedDecendants'] = array_merge(array_diff($tag_names, $rule), $config['allowedDecendants']);

				break;
				case 'closeParent':

					$config['autoCloseOn'] = $rule;

				break;
				case 'ignoreSurroundingWhitespace':

					$config['trimWhitespace'] = $rule;

				break;
				case 'suspendAutoLineBreaks':

					$config['doNotAutoLineBreak'] = $rule;

				break;
				case 'autoClose':

					$config['autoClose'] = $rule;

				break;
				case 'ignoreTags':

					$config['ignoreBBCodeInside'] = $rule;

				break;
				case 'ignoreText':

					$config['ignoreTextInside'] = $rule;

				break;

				// no default
			}
		}

		
		$config['deniedChildren'] = array_unique($config['deniedChildren']);
		$config['allowedChildren'] = array_unique($config['allowedChildren']);
		$config['deniedDescendants'] = array_unique($config['deniedDescendants']);
		$config['allowedDecendants'] = array_unique($config['allowedDecendants']);

		
		return $config;
	}

	/**
	 * Used to get which modes the editor has available.
	 * This is an bitewise OR ("|") of all the possible choices.
	 * 
	 * Typically, an editor has only the WYSIWYG and the source modes but it may
	 * contain a "MIXED" AKA "RTE" mode.
	 * 
	 * @return The joint OR mask of all the available modes the editor has.
	 */
	abstract public function get_available_modes();

	/**
	 * Used to get which ways to display buttons the editor has available.
	 * This is an bitewise OR ("|") of all the possible choices.
	 * 
	 * @return The joint OR mask of all the available modes the editor has.
	 */
	abstract public function get_available_button_modes();
}
