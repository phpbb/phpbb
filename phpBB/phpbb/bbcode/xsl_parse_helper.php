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

namespace phpbb\bbcode;

use DOMDocument;
use Exception;

/**
* 
*/
class xsl_parse_helper
{

	private $choose_num;
	private $current_bbcode;
	private $conditions_document;

	protected $attr_to_param;

	protected $stylesheet_params;

	// If edited, also edit posting_editor.html (in the templates)
	const EDITOR_JS_GLOBAL_OBJ = 'editorData';

	const XSLNS = "http://www.w3.org/1999/XSL/Transform";

	
	public function __construct(){
		$this->attr_to_param = false;
		$this->stylesheet_params = array();
	}

	public function translate_attribute_vars_to_params($option){
		$this->attr_to_param = $option;
	}

	public function get_built_xsl_sheet(){
		return $this->conditions_document->saveXML();
	}

	/**
	 * @note: This method applies data set using translate_attribute_vars_to_params()
	 * @seealso: translate_attribute_vars_to_params()
	 * @param DOMElement $node The node to get the params from analysis (maybe also edit)
	 * @param string $match_find $match_find must contain 5 RegEx matching groups. Each one means as follows:
	 *				1. The full name of the variable and its definition
	 *				2. Where "@" or "$" are.
	 *				3. The variable name with the kind ("L_" or "S_")
	 *				4. The identification of the kind of variable (single capital letter)
	 *				5. The name of the variable without prefixes
	 * @return array List of all the variables found with their data and metadata in an associative array
	*/
	public function parse_attributes($node, $match_find)
	{
		$vars = array();

		foreach ($node->attributes as $attr)
		{
			$attr->value = preg_replace_callback($match_find, 
					function ($match) use ($attr, &$vars)
					{
						$var_data = $vars[] = array(
							'attr' 			=> $attr->nodeName,
							'fullname' 		=> $match[1],
							'prefixedName'	=> $match[3],
							'name' 			=> $match[5],
							 // Is it an xsl parameter ($) or as a BBCode attribute (@)?
							'isParameter' 	=> $match[2] === '$',
							'isAttribute' 	=> $match[2] === '@',
							'isSetting' 	=> $match[4] === 'S',
							'isLanguage' 	=> $match[4] === 'L',
						);

						$this->stylesheet_params[$match[3]] = true;

						if ($this->attr_to_param && $var_data['isAttribute'])
						{
							return str_replace($var_data['fullname'], '$' . $var_data['prefixedName'], $match[0]);
						}

						return $match[0];

					}, $attr->value);
		}
		return $vars;
	}

	public function parse_tag_templates($bbcodes, $tags){

		$this->conditions_document = new DOMDocument();
		$this->conditions_document->preserveWhiteSpace = false;
		$this->conditions_document->formatOutput = true;
		$this->conditions_document->loadXML(
			'<?xml version="1.0"?>
			<xsl:stylesheet version="1.0" xmlns:xsl="' . self::XSLNS . '">
			<xsl:output method="text" encoding="iso-8859-1" indent="yes"/>
			</xsl:stylesheet>',
			LIBXML_DTDLOAD
		);

		$parseTrees = array();

		// [11] => xsl:copy-of
		// [20] => xsl:attribute -> https://msdn.microsoft.com/en-us/library/ms256232%28v=vs.110%29.aspx
		// [22] => xsl:value-of -> https://msdn.microsoft.com/en-us/library/ms256232%28v=vs.110%29.aspx

		// [17] => xsl:if -> https://developer.mozilla.org/en-US/docs/Web/XSLT/if
		// [25] => xsl:choose
		// [26] => xsl:when
		// [30] => xsl:otherwise
		// xsl:variable
		// xsl:for-each -> Postponed until examples arrive

		
		foreach($bbcodes as $bbcode_name => $bbcode){
			$bbcode_name = strtolower($bbcode_name);
			try{
				$this->current_bbcode = $bbcode_name;
				$this->choose_num = 0;
				$parseTrees[$bbcode_name] = $this->parse_tag_template($tags[$bbcode->tagName]->template);
			}catch(xsl_parse_helper_exception $e){
				var_dump($e->getMessage());
			}
		}

		$stylesheet_root = $this->conditions_document->documentElement;
		foreach ($this->stylesheet_params as $param_name => $truth)
		{
			$param = $this->conditions_document->createElementNS(self::XSLNS, 'xsl:param');
			$param->setAttribute('name', $param_name);
			$stylesheet_root->insertBefore($param, $stylesheet_root->firstChild);
		}

		return $parseTrees;
	}

	public function get_generated_xml(){
		return $this->conditions_document->saveXML();
	}

	public function parse_tag_template($template){

		$doc = $template->asDOM();

		$top = array();
		// Child nodes of the template Element
		foreach($doc->firstChild->childNodes AS $child_node){
			$result = $this->parse_tag_template_childNode($child_node);
			if ($result != null)
			{
				$top[] = $result;
			}
		}

		return $top;
	}

	protected function parse_tag_template_childNode($current_node){

		$name = isset($current_node->localName) ? $current_node->localName : $current_node->nodeName;

		$current = array(
			'xsl' => $current_node->prefix === 'xsl',
			'tagName' => $name,
			'node' => $current_node,
			'js' => array(),
			'children' => array(),
		);

		$xsl_var_match = null;

		if ($current['xsl']){

			switch($current['tagName']){
				// case 'copy-of':
					// // TODO: How to handle a deep copy
					// break;
				case 'if':
					// "if" is somewhat like choose... Still different, though!
					return $this->translate_if($current_node);
					break;
				case 'choose':
					return $this->translate_case($current_node);
					break;
				// case 'for-each':
					// // TODO: Postponed until examples arrive.
				// break;
				case 'apply-templates':
				case 'text':
				case 'value-of':
					/* NOOP */
				break;
				case 'comment':
					// Comments are not instructions. Do not read this or anything inside.
					return null;
				break;
				default:
					throw new xsl_parse_helper_exception (
						'Tag ' . $current_node->tagName . ' is not recognized to translate for WYSIWYG'
					);
			}

			$xsl_var_match = "%(([@$])((?:([SL])_)?([a-zA-Z_0-9]+)))%";

		}else{
			$xsl_var_match = "%{(([@$])((?:([SL])_)?([a-zA-Z_0-9]+)))}%";
		}

		// Text nodes cannot have attributes. Not using hasAttributes so that it can warn unpredictability by outputting warnings
		if ($name !== '#text' || ($current['xsl'] && $name !== 'text'))
		{
			$current['vars'] = $this->parse_attributes($current_node, $xsl_var_match);
		}

		if ($current_node->hasChildNodes())
		{
			foreach ($current_node->childNodes AS $child_node)
			{
				$nextChild = $this->parse_tag_template_childNode($child_node);
				if ($nextChild !== null)
				{
					$current['children'][] = $nextChild;
				}
			}
		}

		return $current;
	}



	public function translate_if($current_node){
		$data = array(
			'num' => $this->choose_num,
			'case' => array(),
			'js' => array(),
		);
		$this->choose_num++;

		$template = $this->conditions_document->createElementNS(self::XSLNS, 'xsl:template');
		$template->setAttribute('match', $this->current_bbcode . "[@d='" . $data['num'] . "']");

		$choose = $this->conditions_document->createElementNS(self::XSLNS, 'xsl:choose');

		$chr = 'a';

		$case = &$data['case'];
			
		$when = $this->conditions_document->createElementNS(self::XSLNS, 'xsl:when');
		$when->setAttribute('test', $current_node->getAttribute('test'));
		// <xsl:when test>$chr</xsl:when>
		$when->appendChild($this->conditions_document->createTextNode($chr));

		$case[$chr] = array(
			'vars' => array(),
			'js' => array(),
			'children' => array(),
		);

		$case[$chr]['vars'] = $this->parse_attributes($when, "%(([@$])((?:([SL])_)?([a-zA-Z_0-9]+)))%");

		foreach ($current_node->childNodes as $child_node){
			$case[$chr]['children'][] = $this->parse_tag_template_childNode($child_node);
		}

		$choose->appendChild($when);
		$template->appendChild($choose);

		$this->conditions_document->firstChild->appendChild($template);

		return $data;
	}

	public function translate_case($current_node){
		$data = array(
			'num' => $this->choose_num,
			'case' => array(),
			'js' => array(),
		);
		$this->choose_num++;

		$template = $this->conditions_document->createElementNS(self::XSLNS, 'xsl:template');
		$template->setAttribute('match', $this->current_bbcode . "[@d='" . $data['num'] . "']");

		$choose = $this->conditions_document->createElementNS(self::XSLNS, 'xsl:choose');

		$chr = 'a';

		$case = &$data['case'];

		foreach ($current_node->childNodes AS $whenNode){
			// This can either be a "xsl:when" or "xsl:otherwise".
			// additionally, for the "xsl:when", I want the exact same @test attr
			$when = $this->conditions_document->importNode($whenNode->cloneNode(false));
			// <xsl:when test>$chr</xsl:when>
			$when->appendChild($this->conditions_document->createTextNode($chr));

			$case[$chr] = array(
				'vars' => array(),
				'js' => array(),
				'children' => array(),
			);

			$case[$chr]['vars'] = $this->parse_attributes($when, "%(([@$])((?:([SL])_)?([a-zA-Z_0-9]+)))%");

			foreach ($whenNode->childNodes as $child_node){
				$case[$chr]['children'][] = $this->parse_tag_template_childNode($child_node);
			}

			$choose->appendChild($when);
			$chr++;
		}

		$template->appendChild($choose);

		$this->conditions_document->firstChild->appendChild($template);

		return $data;
	}
}

class xsl_parse_helper_exception extends Exception
{

}
