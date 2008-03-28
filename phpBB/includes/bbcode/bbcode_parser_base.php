<?php
/**
*
* @package phpBB3
* @version
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
 * A stack based BBCode parser.
 *
 */
abstract class phpbb_bbcode_parser_base
{
	/**
	 * Array holding the BBCode definitions.
	 *
	 * This is all the documentation you'll find!
	 *
	 * 'tagName' => array( // The tag name must start with a letter and can consist only of letters and numbers.
	 * 		'replace' => 'The open tag is replaced with this. "{attribute}" - Will be replaced with an existing attribute.',
	 * 		// Optional
	 * 		'replace_func' => 'function_name', // Open tag is replaced with the the value that this function returns. replace will not be used. The function will get the arguments given to the tag and the tag definition. It is your responsibility to validate the arguments.
	 * 		'close' => 'The close tag is replaced by this. If set to bool(false) the tag won't need a closing tag.',
	 * 		// Optional
	 * 		'close_shadow' => true, // If set, no closing tag will be needed, but the value close will be added as soon as the parent tag is closed or a tag which is not allowed in the tag is encountered.
	 * 		// Optional
	 * 		'close_func' => 'function_name', // Close tag is replaced with the the value that this function returns. close will not be used. If close is set to bool this might not function as expected.
	 * 		'attributes' => array(
	 * 			'attributeName' => array(
	 * 				'replace' => 'Attribute replacement. Use string defined in self::$attr_value_replace as a replacement for the attributes value',
	 * 				'type_check' => 'function_name', // Optional. Function name to check if the value of the attribute is allowed. It must return bool or a corrected string. It must accept the attribute value string.
	 * 				'required' => true, // Optional. The attribute must be set and not empty for the tag to be parsed.
	 * 			),
	 * 			// ...
	 * 		),
	 * 		'children' => array(
	 * 			true, // true allows all tags to be a child of this tag except for the other tags in the array. false allows only the tags in the array.
	 * 			'tag2' => true,
	 * 			// ...
	 * 		),
	 * 		'parents' => array(true), // Same as 'children'.
	 * 		// Optional
	 * 		'content_func' => 'function_name', // Applies function to the contents of the tag and replaces it with the output. Used only when the tag does not allow children. It must return the replacement string and accept the input string. This is not like HTML...
	 * 	),
	 * 	'tag2' => array(
	 * // ...
	 *
	 * NOTE: Use "_" as the name of the attribute assigned to the tag itself. (eg. form the tag [tag="value"] "_" will hold "value")
	 * NOTE: Use "__" for the content of a tag without children. (eg. for [u]something[/u] "__" will hold "something") This is not like HTML...
	 * NOTE: The following special tags exist: "__url" (child), "__smiley" (child) and "__global" (parent). They are to be used in the child/parent allowed/disallowed lists.
	 * @var array
	 */
	protected  $tags = array();
	
	/**
	 * The smilies which are to be "parsed".
	 * 
	 * Smilies are treated the same way as BBCodes (though BBcodes have precedance).
	 * Use "__smiely" to allow/disallow them in tags. Smileys can only be children.
	 * 
	 * 'smiley' => 'replacement'
	 *
	 * @var array
	 */
	protected $smilies = array();
	
	/**
	 * Callback to be applied to all text nodes (in second_pass).
	 *
	 * @var mixed
	 */
	protected $text_callback = null;

	/**
	 * Used by first_pass and second_pass
	 *
	 * @var array
	 */
	private $stack = array();

	/**
	 * Regex to match BBCode tags.
	 *
	 * @var string
	 */
	private $tag_regex = '\[(/?)([a-z][a-z0-9]*)(?:=(\'[^\']*\'|"[^"]*"))?((?: [a-z]+(?:\s?=\s?(?:\'[^\']*\'|"[^"]*"))?)*)\]';
	
	/**
	 * Regex for URL's
	 * 
	 * @var string
	 */
	private $url_regex = '(?>([a-z+]{2,}://|www\.))(?:[a-z0-9]+(?:\.[a-z0-9]+)?@)?(?:(?:[a-z](?:[a-z0-9]|(?<!-)-)*[a-z0-9])(?:\.[a-z](?:[a-z0-9]|(?<!-)-)*[a-z0-9])+|(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))(?:/[^\\/:?*"<>|\n]*[a-z0-9])*/?(?:\?[a-z0-9_.%]+(?:=[a-z0-9_.%:/+-]*)?(?:&[a-z0-9_.%]+(?:=[a-z0-9_.%:/+-]*)?)*)?(?:#[a-z0-9_%.]+)?';

	/**
	 * Regex to match attribute&value pairs.
	 *
	 * @var string
	 */
	private $attribute_regex = '~([a-z]+)(?:\s?=\s?((?:\'[^\']*?\'|"[^"]*?")))?~i';

	/**
	 * Delimiter's ASCII code.
	 *
	 * @var int
	 */
	private $delimiter = 0;

	/**
	 * This string will be replaced by the attribute value.
	 *
	 * @var string
	 */
	private $attr_value_replace = '%s';

	/**
	 * First pass result.
	 *
	 * @var array
	 */
	private $parsed = array();
	private $parse_pos = 1;
	
	/**
	 * Types
	 */
	const TYPE_TAG				= 1;
	const TYPE_TAG_SIMPLE		= 2;
	const TYPE_CTAG				= 3;
	const TYPE_ABSTRACT_SMILEY	= 4;
	const TYPE_ABSTRACT_URL		= 5;
	
	/**
	 * Feature flags
	 */
	const PARSE_BBCODE	= 1;
	const PARSE_URLS	= 2;
	const PARSE_SMILIES	= 4;
	
	protected $flags;

	// Old ones without the "fast" tag=attribute.
//	private $tag_regex = '~\[(/?)([a-z][a-z0-9]*)((?: [a-z]+(?:\s?=\s?(?:\'(?:\\\'|[^\'])*\'|"(?:[^"]|\\\")*"))?)*)\]~i';
//	private $attribute_regex = '~([a-z]+)(?:\s?=\s?((?:\'(?:\\\'|[^\'])*?\'|"(?:\\"|[^"])*?")))?~i';

	/**
	 * Constructor.
	 *
	 */
	public function __construct()
	{
		$this->delimiter = chr($this->delimiter);
		$this->flags = self::PARSE_BBCODE | self::PARSE_URLS | self::PARSE_SMILIES;
	}

	/**
	 * Returns a string ready for storage and/or second_pass
	 *
	 * @param string $string
	 * @return string
	 */
	public function first_pass($string)
	{
		$this->stack = array();
		$this->parsed = array();
		$this->parse_pos = 1;

		// Remove the delimiter from the string.
		$string = str_replace($this->delimiter, '', $string);
		
		$smilies = implode('|',array_map(array($this, 'regex_quote'), array_keys($this->smilies)));
		
		// Make a regex out of the following items:
		$regex_parts = array(
			$this->tag_regex,
			$this->url_regex,
			$smilies,
		);

		$regex = '~' . implode('|', $regex_parts) . '~i';

		// Do most of the job here...
		$string = preg_replace_callback($regex, array($this, 'first_pass_tag_check'), $string);

		// Close all remaining open tags.
		if (sizeof($this->stack) > 0)
		{
			$string .= $this->close_tags($this->stack);
			$this->stack = array();
		}
		
		// Make a serialized array out of it.
		$string = explode($this->delimiter, $string);
		
		if (sizeof($string) > 1)
		{
			$parsed = array();
	
			$this->parse_pos = 0;
	
			end($this->parsed);
			reset($string);
			foreach ($this->parsed as $key => $val)
			{	
				$parsed[key($string) * 2] = current($string);
				$parsed[$key] = $val;
				next($string);
			}
	
			$this->parsed = array();
			$this->parse_pos = 1;
		}
		else
		{
			$parsed = $string;
		}

		return serialize($parsed);
	}

	/**
	 * Opposite function to first_pass.
	 * Changes the output of first_pass back to BBCode.
	 *
	 * @param string $string
	 * @return string
	 * @todo make sure this works after the change of first_pass data storage.
	 */
	public function first_pass_decompile($string)
	{
		$string = explode($this->delimiter, $string);
		for ($i = 1, $n = sizeof($string); $i < $n; $i += 2)
		{
			// This will proably throw an unwanted notice...
			$tag = $string[$i];
			if ($tag === false)
			{
				$tag = $string[$i];
			}
			$string[$i] = $this->decompile_tag($tag);
		}
		return implode('', $string);
	}

	/**
	 * Removes first_pass data. This removes all BBCode tags. To reverse the effect of first_pass use first_pass_decompile
	 *
	 * @param string $string
	 * @return string
	 */
	public function remove_first_pass_data($string)
	{
		$decompiled = array();
		$compiled = unserialize($string);
		for ($i = 0, $n = sizeof($compiled); $i < $n; $n += 2)
		{
			$decompiled[] = $compiled[$i];
		}
		return implode('', $decompiled);
	}

	/**
	 * The function takes the result of first_pass and returnes the string fully parsed.
	 *
	 * @param string $string
	 * @return string
	 */
	public function second_pass($string)
	{
		$this->stack = array();

		$string = unserialize($string);
		
		if (!is_null($this->text_callback))
		{
			for ($i = 0, $n = sizeof($string); $i < $n; $i += 2)
			{
				$string[$i] = call_user_func($this->text_callback, $string[$i]);
			}
		}
		
		for ($i = 1, $n = sizeof($string); $i < $n; $i += 2)
		{

			$tag_data		= $string[$i];
			$type			= &$tag_data[0];
			$tag			= $tag_data[1];
			$tag_definition	= &$this->tags[$tag];

			if ($this->flags & self::PARSE_BBCODE && $type != self::TYPE_ABSTRACT_URL && $type != self::TYPE_ABSTRACT_SMILEY && $type != self::TYPE_CTAG)
			{
				// These apply to opening tags and tags without closing tags.

				// Is the tag still allowed as a child?
				// This is still needed!
				if (sizeof($this->stack) && isset($this->tags[$this->stack[0]['name']]['close_shadow']) && !is_bool($this->tags[$this->stack[0]['name']]['close']) && !$this->child_allowed($tag))
				{
					// The previous string won't be edited anymore.
					$string[$i - 1] .= $this->tags[$this->stack[0]['name']]['close'];
					array_shift($this->stack);
				}
				
				// Add tag to stack only if it needs a closing tag.
				if ($tag_definition['close'] !== false || !isset($tag_definition['close_shadow']))
				{
					array_unshift($this->stack, array('name' => $tag, 'attributes' => array()));
				}
			}
			
			switch ($type)
			{
				case self::TYPE_ABSTRACT_URL:

					if ($this->flags & self::PARSE_URLS && $this->child_allowed('__url'))
					{
						$string[$i] = '<a href="' . $tag_data[1] . '">' . $tag_data[1] . '</a>';
					}
					else
					{
						$string[$i] = $tag_data[1];
					}
				
				break;
					
				case self::TYPE_ABSTRACT_SMILEY:

					if ($this->flags & self::PARSE_SMILIES && $this->child_allowed('__smiley'))
					{
						$string[$i] = $this->smilies[$tag_data[1]];
					}
					else
					{
						$string[$i] = $tag_data[1];
					}
					
				break;
				
				case self::TYPE_CTAG:
					
					if (($this->flags & self::PARSE_BBCODE) == 0)
					{
						$string[$i] = $this->decompile_tag($string[$i]);
						break;
					}
	
					// It must be the last one as tag nesting was checked in the first pass.
					// An exception to this rule was created with adding the new type of tag without closing tag.
					if (isset($this->tags[$this->stack[0]['name']]['close_shadow']))
					{
						if (!is_bool($this->tags[$this->stack[0]['name']]['close']))
						{
							// the previous string won't be edited anymore.
							$string[$i - 1] .= $this->tags[$this->stack[0]['name']]['close'];
						}
						else if (isset($tag_definition['close_func']))
						{
							$string[$i - 1] .= call_user_func($tag_definition['close_func'], $this->stack[0]['attributes']);
						}
						array_shift($this->stack);
					}

					if ($tag != $this->stack[0]['name'])
					{
						$string[$i] = $this->decompile_tag('/' . $tag);
					}
					else if (isset($tag_definition['close_shadow']))
					{
						$string[$i] = '';
					}
					else if ($tag_definition['close'] !== false || !isset($tag_definition['close_shadow']))
					{
						if (isset($tag_definition['close_func']))
						{
							$string[$i] = call_user_func($tag_definition['close_func'], $this->stack[0]['attributes']);
						}
						else
						{
							$string[$i] = $tag_definition['close'];
						}
						array_shift($this->stack);
					}
					else
					{
						$string[$i] = '';
					}
					
				break;
					
				case self::TYPE_TAG_SIMPLE:
					
					if (($this->flags & self::PARSE_BBCODE) == 0)
					{
						$string[$i] = $this->decompile_tag($string[$i]);
						break;
					}
					
					if ($tag_definition['children'][0] == false && sizeof($tag_definition['children']) == 1)
					{
						if (isset($tag_definition['attributes']['__']))
						{
							$this->stack[0]['attributes'] = array('__' => $string[$i + 1]);
							if (isset($tag_definition['replace_func']))
							{
								$string[$i] = call_user_func($tag_definition['replace_func'], array('__' => $string[$i + 1]), $tag_definition);
							}
							else
							{
								$string[$i] = str_replace('{__}', $string[$i + 1], $tag_definition['replace']);
							}
						}
						else if (isset($tag_definition['replace_func']))
						{
							$string[$i] = call_user_func($tag_definition['replace_func'], array(), $tag_definition);
						}
						else
						{
							$string[$i] = $tag_definition['replace'];
						}

						if (isset($this->tags[$tag]['content_func']))
						{
							$string[$i + 1] = call_user_func($tag_definition['content_func'], $string[$i + 1]);
						}
					}
					else
					{
						if (isset($tag_definition['replace_func']))
						{
							$string[$i] = call_user_func($tag_definition['replace_func'], array(), $tag_definition);
						}
						else
						{
							$string[$i] = $tag_definition['replace'];
						}
					}
	
					if (sizeof($tag_definition['attributes']) > 0)
					{
						// The tag has defined attributes but doesn't use any. The attribute replacements must be removed. I don't want this regex here.
						$string[$i] = preg_replace('/{[^}]*}/', '', $string[$i]);
					}
						
				break;
				
				case self::TYPE_TAG:
			
					if (($this->flags & self::PARSE_BBCODE) == 0)
					{
						$string[$i] = $this->decompile_tag($string[$i]);
						break;
					}

					// These apply to tags with attributes.
					if (!isset($tag_data[2]))
					{
						$tag_data[2] = array('__' => $string[$i + 1]);
					}
					$this->stack[0]['attributes'] = $tag_data[2];
		
					// Handle the (opening) tag with a custom function
					if (isset($tag_definition['replace_func']))
					{
						
						$string[$i] = call_user_func($tag_definition['replace_func'], $tag_data[2], $tag_definition);
		
						if (isset($tag_definition['content_func']) && $tag_definition['children'][0] === false && sizeof($tag_definition['children']) == 1)
						{
							$string[$i + 1] = call_user_func($tag_definition['content_func'], $string[$i + 1]);
						}
						break;
					}
		
					// New code for the feature I've always wanted to implement :)
					if (isset($tag_definition['attributes']['__']) && $tag_definition['children'][0] == false && sizeof($tag_definition['children']) == 1)
					{
						$attributes = array('{__}');
						$replacements = array($string[$i + 1]);
						// End new code.
					}
					else
					{
						$attributes = array();
						$replacements = array();
					}
		
					foreach ($tag_definition['attributes'] as $attribute => $value)
					{
						$attributes[] = '{' . $attribute . '}';
						if (!isset($tag_data[2][$attribute]))
						{
							if (isset($value['required']))
							{
								$string[$i] = $this->decompile_tag($tag_data);
								break 2;
							}
							$replacements[] = '';
							continue;
						}
		
						$replacements[] = str_replace($this->attr_value_replace, $tag_data[2][$attribute], $tag_definition['attributes'][$attribute]['replace']);
					}
	
		
					$string[$i] = str_replace($attributes, $replacements, $this->tags[$tag]['replace']);
		
					// It has to be twice... this should not be used if required attributes are missing.
					if (isset($tag_definition['content_func']) && $tag_definition['children'][0] === false && sizeof($tag_definition['children']) == 1)
					{
						$string[$i + 1] = call_user_func($tag_definition['content_func'], $string[$i + 1]);
					}
					
				break;
			}
		}

		return implode($string);
	}

	/**
	 * Callback for preg_replace_callback in first_pass.
	 *
	 * @param array $matches
	 * @return string
	 */
	private function first_pass_tag_check($matches)
	{
		switch (sizeof($matches))
		{
			// Smilies
			case 1:
				
				$this->parsed[$this->parse_pos] = array(self::TYPE_ABSTRACT_SMILEY, $matches[0]);
				$this->parse_pos += 2;
				return $this->delimiter;
				
			break;
			
			// URL
			case 6:

				$this->parsed[$this->parse_pos] = array(self::TYPE_ABSTRACT_URL, $matches[0]);
				$this->parse_pos += 2;
				return $this->delimiter;
				
			break;
			
			default:

				if (!isset($this->tags[$matches[2]]))
				{
					// Tag with the given name not defined.
					return $matches[0];
				}

				// If tag is an opening tag.
				if (strlen($matches[1]) == 0)
				{
					if (sizeof($this->stack))
					{
						if ($this->tags[$this->stack[0]]['children'][0] == false && sizeof($this->tags[$this->stack[0]]['children']) == 1)
						{
							// Tag does not allow children.
							return $matches[0];
						}
						// Tag parent not allowed for this tag. Omit here.
						else if (!$this->parent_allowed($matches[2], $this->stack[0]))
						{
							if (isset($this->tags[$this->stack[0]]['close_shadow']))
							{
								array_shift($this->stack);
							}
							else
							{
								return $matches[0];
							}
						}
					}
					// Is tag allowed in global scope?
					else if (!$this->parent_allowed($matches[2], '__global'))
					{
						return $matches[0];
					}
		
					if ($this->tags[$matches[2]]['close'] !== false || !isset($this->tags[$matches[2]]['close_shadow']))
					{
						// Do not add tags to stack that do not need closing tags.
						array_unshift($this->stack, $matches[2]);
					}
		
					$tag_attributes = &$this->tags[$matches[2]]['attributes'];
		
					if (strlen($matches[3]) != 0 && isset($tag_attributes['_']))
					{
						// Add short attribute.
						$attributes = array('_' => substr($matches[3], 1, -1));
					}
					else if (strlen($matches[4]) == 0 || (sizeof($tag_attributes)) == 0)
					{
						// Check all attributes, which were not used, if they are required.
						if ($this->has_required($matches[2], array_keys($tag_attributes)))
						{
							// Not all required attributes were used.
							return $matches[0];
						}
						else
						{
							$this->parsed[$this->parse_pos] = array(self::TYPE_TAG_SIMPLE, $matches[2]);
							if (isset($attributes))
							{
								$this->parsed[$this->parse_pos][] = $attributes;
							}
							$this->parse_pos += 2;
							return $this->delimiter;
						}
					}
					else
					{
						$attributes = array();
					}
		
					// Analyzer...
					$matched_attrs = array();
		
					preg_match_all($this->attribute_regex, $matches[4], $matched_attrs, PREG_SET_ORDER);
		
					foreach($matched_attrs as $i => $value)
					{
						$tag_attribs_matched = &$tag_attributes[$value[1]];
						if (isset($attributes[$value[1]]))
						{
							// This prevents adding the same attribute more than once. Childish betatesters are needed.
							continue;
						}
						if (isset($tag_attribs_matched))
						{
							// The attribute exists within the defined tag. Undefined tags are removed.
		
							$attr_value = substr($value[2], 1, -1);
		
							if (isset($tag_attribs_matched['type_check']))
							{
								// A type check is needed for this attribute.

								$type_check = $tag_attribs_matched['type_check']($attr_value);
		
								if (!is_bool($type_check))
								{
									// The type check function decided to fix the input instead of returning false.
									$attr_value = $type_check;
								}
								else if ($type_check === false)
								{
									// Type check has failed.
									continue;
								}
							}
							if (isset($tag_attribs_matched['required']) && strlen($attr_value) == 0)
							{
								// A required attribute is empty. This is done after the type check as the type check may return an empty value.
								return $matches[0];
							}
							$attributes[$value[1]] = $attr_value;
						}
					}
		
					// Check all attributes, which were not used, if they are required.
					if ($this->has_required($matches[2], array_values(array_diff(array_keys($tag_attributes), array_keys($attributes)))))
					{
						// Not all required attributes were used.
						return $matches[0];
					}
		
					if (sizeof($attributes))
					{
						$this->parsed[$this->parse_pos] = array(self::TYPE_TAG, $matches[2], $attributes);
						$this->parse_pos += 2;
						return $this->delimiter;
					}

					$this->parsed[$this->parse_pos] = array(self::TYPE_TAG_SIMPLE, $matches[2]);
					$this->parse_pos += 2;
					return $this->delimiter;
				}
				// If tag is a closing tag.
				

				$valid = array_search($matches[2], $this->stack);
		
				if ($valid === false)
				{
					// Closing tag without open tag.
					return $matches[0];
				}
				else if ($valid != 0)
				{
					if ($this->tags[$this->stack[0]]['children'][0] == false && sizeof($this->tags[$this->stack[0]]['children']) == 1)
					{
						// Tag does not allow children.
						// Do not handle other closing tags here as they are invalid in tags which do not allow children.
						return $matches[0];
					}
					// Now we have to close all tags that were opened before this closing tag.
					// We know that this tag does not close the last opened tag.
					$to_close = array_splice($this->stack, 0, $valid + 1);
					return $this->close_tags($to_close);
				}
				else
				{
					// A unset() was elicting many notices here.
					array_shift($this->stack);
					$this->parsed[$this->parse_pos] = array(self::TYPE_CTAG, $matches[2]);
					$this->parse_pos += 2;
					return $this->delimiter;
				}
				
			break;
		}
	}

	/**
	 * Returns closing tags for all tags in the $tags array (in reverse order).
	 *
	 * @param array $tags
	 * @return string
	 */
	private function close_tags($tags)
	{
		$ret = '';
		foreach($tags as $tag)
		{
			// @todo: Is this needed?
			if (!isset($this->tags[$tag]['close_shadow']))
			{
				$this->parsed[$this->parse_pos] = array(self::TYPE_CTAG, $tag);
				$this->parse_pos += 2;
				$ret .= $this->delimiter;
			}
		}
		return $ret;
	}

	/**
	 * Returns the tag to the form it had before the first_pass
	 *
	 * @param mixed $tag
	 * @return string
	 */
	private function decompile_tag($tag)
	{

		if (!is_array($tag))
		{
			return '[' . $tag . ']';
		}

		$ret = '[' . (($tag[0]) ? '' : '/');
		$ret .= $tag[1];
		
		if(isset($tag[2]))
		{
			if (isset($tag[2]['_']))
			{
				$ret .= '="' . $tag[2]['_'] . '"';
				unset($tag[2]['_']);
			}
	
			foreach ($tag[2] as $attribute => $value)
			{
				$ret .= ' ' . $attribute . '=' . $value;
			}
		}
		$ret .= ']';

		return $ret;
	}

	/**
	 * Checks if $tag can be a child of the tag in stack index $index
	 *
	 * @param string $tag
	 * @param int $index = 0
	 * @return bool
	 */
	private function child_allowed($tag, $index = 0)
	{
		if (!isset($this->stack[$index]))
		{
			return true;
		}
		// I assume this trick is usefull starting form two.
		$children = &$this->tags[$this->stack[$index]['name']]['children'];
		if (isset($children[$tag]) xor $children[0])
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Checks if the $tag can be a child of $parent
	 *
	 * @param string $tag
	 * @param string $parent
	 * @return bool
	 */
	private function parent_allowed($tag, $parent)
	{
		$parents = &$this->tags[$tag]['parents'];
		if (isset($parents[$parent]) xor $parents[0])
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Checks if any of $tag's attributes in $attributes are required.
	 *
	 * @param string $tag
	 * @param string $attributes
	 * @return bool
	 */
	private function has_required($tag, $attributes)
	{
		for ($i = 0, $n = sizeof($attributes); $i < $n; ++$i)
		{
			if (isset($this->tags[$tag]['attributes'][$attributes[$i]]['required']))
			{
				return true;
			}
		}

		return false;
	}
	
	private function regex_quote($var)
	{
		return preg_quote($var, '~');
	}
	
	public function set_flags($flags)
	{
		$this->flags = (int) $flags;
	}
}
