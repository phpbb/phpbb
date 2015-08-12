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


/**
 *
 *
 *
 * @source Heavily based on s9e\TextFormatter\Configurator\Helpers\RegexpParser and s9e\TextFormatter\Configurator\JavaScript\RegexpConvertor
 */
class js_regex_helper
{

	const POSIX_BRACKET_REGEX = '(?:alnum|alpha|ascii|blank|cntrl|digit|graph|lower|print|punct|space|upper|word|xdigit)';

	// Regex options are not supported, use modifiers, instead
	const ERROR_OPTIONS_NOT_AVAILABLE = 1;
	// Subpattern options are not supported
	const ERROR_CAPTURE_OPTIONS_NOT_AVAILABLE = 2;
	// Lookbehind assertions are not supported. Use capturing groups, instead
	const ERROR_NEGATIVE_BEHIND_NOT_AVAILABLE = 3;
	// Negative lookbehind assertions are not supported. Use capturing groups, instead
	const ERROR_POSITIVE_BEHIND_NOT_AVAILABLE = 4;

	/**
	* @param  string $regexp
	* @return array
	*/
	public static function parse($regexp)
	{
		if (!preg_match('#^(.)(.*)\\1([ADSXJusmi]*)$#Ds', $regexp, $m))
		{
			switch ($regexp[0])
			{
				case '(':
				case '[':
				case '{':
				case '<':
					$translate_close = array(
						'(' => ')',
						'[' => ']',
						'{' => '}',
						'<' => '>',
					);
					if (!preg_match('#^('. preg_quote($regexp[0]) . ')(.*)' . 
						preg_quote($translate_close[$regexp[0]]) .'([ADSXJusmi]*)$#Ds', $regexp, $m))
					{
						throw new RuntimeException('Could not parse regexp delimiters');
					}

				break;

				default:
					throw new RuntimeException('Could not parse regexp delimiters');	
			}
		}


		$ret = array(
			'delimiter' => $m[1],
			'regexp'    => $m[2],
			'modifiers' => array_flip(str_split($m[3])),
			'tokens'    => array(),
		);

		// Fix modifiers
		// E.g. If "m" is used, D is ignored.
		// Source: http://php.net/manual/en/reference.pcre.pattern.modifiers.php
		if(isset($ret['modifiers']['m']))
		{
			unset($ret['modifiers']['D']);
		}

		if(isset($ret['modifiers']['D']))
		{
			// D is useless if the end-of-string anchor is not used
			// This check is incomplete on purpose to keep it short
			if (strpos($regexp, '$') === false)
			{
				unset($ret['modifiers']['D']);
			}
		}

		
		$regexp = $m[2];

		$openSubpatterns = array();

		$capture_num = 1;

		$pos = 0;
		$regexpLen = strlen($regexp);

		while ($pos < $regexpLen)
		{
			switch ($regexp[$pos])
			{
				case '\\':
					// skip next character
					$pos += 2;
				break;

				case '[':
					if (!preg_match('#\\[(.*?(?<!\\\\)(?:\\\\\\\\)*+)\\]((?:[+*][+?]?|\\{\d+(?:,\d*)?\\}|\\?)?)#', $regexp, $m, 0, $pos))
					{
						throw new RuntimeException('Could not find matching bracket from pos ' . $pos);
					}

					$ret['tokens'][] = array(
						'pos'         => $pos,
						'len'         => strlen($m[0]),
						'type'        => 'characterClass',
						'content'     => $m[1],
						'quantifiers' => $m[2],
					);

					$pos += strlen($m[0]);
				break;

				case '(':
					if (preg_match('#\\(\\?([a-z]*)\\)#i', $regexp, $m, 0, $pos))
					{
						// This is an option (?i) so we skip past the right parenthesis
						$ret['tokens'][] = array(
							'pos'     => $pos,
							'len'     => strlen($m[0]),
							'type'    => 'option',
							'options' => $m[1]
						);

						$pos += strlen($m[0]);
					break;
					}

					// This should be a subpattern, we just have to sniff which kind
					if (preg_match("#(?J)[(][?](?:P?<(?<name>[a-zA-Z_0-9]+)>|'(?<name>[a-zA-Z_0-9]+)')#A", $regexp, $m, 0, $pos))
					{
						// This is a named capture
						$token = array(
							'pos'  => $pos,
							'len'  => strlen($m[0]),
							'type' => 'capturingSubpatternStart',
							'name' => $m['name'],
							'num' => $capture_num++,
						);

						$pos += strlen($m[0][0]);
					}
					elseif (preg_match('#\\(\\?([a-z]*):#iA', $regexp, $m, 0, $pos))
					{
						// This is a non-capturing subpattern (?:xxx)
						$token = array(
							'pos'     => $pos,
							'len'     => strlen($m[0]),
							'type'    => 'nonCapturingSubpatternStart',
							'options' => $m[1]
						);

						$pos += strlen($m[0]);
					}
					elseif (preg_match('#\\(\\?>#iA', $regexp, $m, 0, $pos))
					{
						/* This is a non-capturing subpattern with atomic grouping "(?>x+)" */
						$token = array(
							'pos'     => $pos,
							'len'     => strlen($m[0]),
							'type'    => 'nonCapturingSubpatternStart',
							'subtype' => 'atomic'
						);

						$pos += strlen($m[0]);
					}
					elseif (preg_match('#\\(\\?(<?[!=])#A', $regexp, $m, 0, $pos))
					{
						// This is an assertion
						$assertions = array(
							'='  => 'lookahead',
							'<=' => 'lookbehind',
							'!'  => 'negativeLookahead',
							'<!' => 'negativeLookbehind'
						);

						$token = array(
							'pos'     => $pos,
							'len'     => strlen($m[0]),
							'type'    => $assertions[$m[1]] . 'AssertionStart'
						);

						$pos += strlen($m[0]);
					}
					elseif (preg_match('%[(][?]#%A', $regexp, $m, 0, $pos))
					{
						// This is a comment
						$token = array(
							'pos'     => $pos,
							'len'     => strlen($m[0]),
							'type'    => 'commentStart'
						);

						$pos += strlen($m[0]);
					}
					elseif (preg_match('#\\(\\?#A', $regexp, $m, 0, $pos))
					{
						throw new RuntimeException('Unsupported subpattern type at pos ' . $pos);
					}
					else
					{
						// This should be a normal capture
						$token = array(
							'pos'  => $pos,
							'len'  => 1,
							'type' => 'capturingSubpatternStart',
							'num' => $capture_num++,
						);

						$pos++;
					}

					$openSubpatterns[] = count($ret['tokens']);
					$ret['tokens'][] = $token;
				break;

				case ')':
					if (empty($openSubpatterns))
					{
						throw new RuntimeException('Could not find matching pattern start for right parenthesis at pos ' . $pos);
					}

					// Add the key to this token to its matching token and capture this subpattern's
					// content
					$k = array_pop($openSubpatterns);
					$startToken =& $ret['tokens'][$k];
					$startToken['endToken'] = count($ret['tokens']);
					$startToken['content']  = substr(
						$regexp,
						$startToken['pos'] + $startToken['len'],
						$pos - ($startToken['pos'] + $startToken['len'])
					);

					// Look for quantifiers after the subpattern, e.g. (?:ab)++ or (?:ab){1,}
					$spn = strspn($regexp, '+*?', 1 + $pos);
					if ($spn === 0 && isset($regexp[1 + $pos]) && $regexp[1 + $pos] === '{' &&
						preg_match('%\\{\d+(?:,\d*)?\\}%S', substr($regexp, 1 + $pos), $pos_match))
					{
						$spn = strlen($pos_match[0]);
					}
					$quantifiers = substr($regexp, 1 + $pos, $spn);

					$ret['tokens'][] = array(
						'pos'  => $pos,
						'len'  => 1 + $spn,
						'type' => substr($startToken['type'], 0, -5) . 'End',
						'quantifiers' => $quantifiers
					);

					unset($startToken);

					$pos += 1 + $spn;
				break;

				default:
					$pos++;
			}
		}

		if (!empty($openSubpatterns))
		{
			throw new RuntimeException('Could not find matching pattern end for left parenthesis at pos ' . $ret['tokens'][$openSubpatterns[0]]['pos']);
		}

		return $ret;
	}

	
	/**
	* Convert a PCRE regexp to a JavaScript regexp
	*
	* Among other tasks, this does:
	* - Find regex functionalities that do not exist in javascript regex parser. Those are:
	* atomic grouping, lookbehind (both positive and negative), conditionals, comments and \A and \z anchors.
	* Change the regex string so that basic differences are fixed. E.g. if modifier "s" is used, the dot "." is translated to "[\s\S]" so that it can matchany character.
	* Apply the "A" modifier by anchoring the start of the match.
	*
	*
	* TODO:
	* - Make possible replacements for lookbehind that apply for different kinds of matches (usually, they are very simple or they can just be replaced with a (?:) with its content inside and a small condition and they work well)
	* - Make the proper corrections for the X PCRE modifier.
	* - 
	*
	* @param  string $regexp PCRE regexp
	* @return RegExp         RegExp object
	*/
	public static function to_js($regexp)
	{
		$regexp_info = self::parse($regexp);
		$dotAll     = isset($regexp_info['modifiers']['s']);

		if (isset($regexp_info['modifiers']['x']))
		{
			throw new RuntimeException('PCRE_EXTENDED is not supported yet');
		}

		$issues = array();

		$match_vs_attribute = array();

		$regexp = '';
		$pos = 0;

		// Add an empty entry for match #0
		$captures = array('');

		foreach ($regexp_info['tokens'] as $token)
		{
			$regexp .= self::translate_unicode_properties(
				substr($regexp_info['regexp'], $pos, $token['pos'] - $pos),
				false,
				$dotAll
			);

			switch ($token['type'])
			{
				case 'option':
					$issues[self::ERROR_OPTIONS_NOT_AVAILABLE][] = $token['pos'];
				case 'capturingSubpatternStart':
					$regexp .= '(';

					// Each capturing subpattern adds an entry to the map. Non-named subpatterns
					// leave a null entry
					$match_vs_attribute[$token['num']] = (isset($token['name'])) ? $token['name'] : null;
				break;

				case 'nonCapturingSubpatternStart':
					if (!empty($token['options']))
					{
						$issues[self::ERROR_OPTIONS_NOT_AVAILABLE] = $token['pos'];
					}

					$regexp .= '(?:';
				break;

				case 'capturingSubpatternEnd':
				case 'nonCapturingSubpatternEnd':
					$regexp .= ')' . substr($token['quantifiers'], 0, 1);
				break;

				case 'characterClass':
					$regexp .= '[';

					// Javascript does not support the POSIX notation. This translates that.
					preg_replace_callback('%^:(' . self::POSIX_BRACKET_REGEX . ')+:$|\[:(' . self::POSIX_BRACKET_REGEX . ')+:\]%',
					function ($match)
					{
						$match_capture = empty($match[1]) ? $match[2] : $match[1];

						switch($match_capture)
						{
							case 'alnum':
								return 'a-zA-Z0-9';
							case 'alpha':
								return 'a-zA-Z';
							case 'ascii':
								return '\u0000-\u007F';
							case 'blank':
								return ' \t';
							case 'cntrl':
								return '\u0000-\u001F\u007F';
							case 'digit':
								return '0-9';
							case 'graph':
								return '\u0021-\u007E';
							case 'lower':
								return 'a-z';
							case 'print':
								return '\u0020-\u007E';
							case 'punct':
								return preg_quote('!"#$%&\'()*+,\\-./:;<=>?@[\\\]^_`{|}~');
							case 'space':
								return ' \t\r\n\v\f';
							case 'upper':
								return 'A-Z';
							case 'word':
								return 'A-Za-z0-9_';
							case 'xdigit':
								return 'A-Fa-f0-9';
						}
					},
					$token['content']);

					$regexp .= self::translate_unicode_properties(
						$token['content'],
						true,
						false
					);

					$regexp .= ']' . substr($token['quantifiers'], 0, 1);
				break;

				case 'lookaheadAssertionStart':
					$regexp .= '(?=';
				break;

				case 'negativeLookaheadAssertionStart':
					$regexp .= '(?!';
				break;

				case 'lookbehindAssertionStart':
					$issues[self::ERROR_POSITIVE_BEHIND_NOT_AVAILABLE][] = $token['pos'];

				break;

				case 'negativeLookbehindAssertionStart':
					// In javascript, it can be made more or less like this:
					// (?:^[\S\s]{0,SIZE-1}?|(?!MY_BEHIND_STRING)[\S\s]{SIZE})uii
					// Where SIZE is the size of the capture string in lookbehind
					// The problem now lies on how to count the size of the capturing lookbehind and how to deal with the "m" mode

					$issues[self::ERROR_NEGATIVE_BEHIND_NOT_AVAILABLE][] = $token['pos'];
				break;

				case 'lookaheadAssertionEnd':
				case 'negativeLookaheadAssertionEnd':
					$regexp .= ')';
				break;

				case 'lookbehindAssertionEnd':
				case 'negativeLookbehindAssertionEnd':
					/* NO OP */
				break;

				default:
					throw new RuntimeException("Unknown token type '" . $token['type'] . "' encountered while parsing regexp");
			}

			$pos = $token['pos'] + $token['len'];
		}

		$regexp .= self::translate_unicode_properties(
			substr($regexp_info['regexp'], $pos),
			false,
			$dotAll
		);


		if (isset($regexp_info['modifiers']['A']))
		{
			// This is a simple best-effort
			if ($regexp[0] !== '^')
			{
				$regexp = '^' . $regexp;
			}
		}

		if (!isset($regexp_info['modifiers']['D']) && !isset($regexp_info['modifiers']['D']))
		{
			$regexp = preg_replace('%(?<!\\\\)\\$(?=[)]|$)%', '\\$\\n?', $regexp);
		}

		
		if ($regexp_info['delimiter'] !== '/')
		{
			switch ($regexp_info['delimiter'])
			{
				case '(':
				case '{':
				case '[':
				case '<':

					$translate_close = array(
						'(' => ')',
						'[' => ']',
						'{' => '}',
						'<' => '>',
					);
					// In this mode, escaping the delimiter is not required. They have to be removed, though.
					$regexp = preg_replace('%' . preg_quote($translate_close[$regexp_info['delimiter']]) . '(?=[ADSXJusmi]*\z)%', '', substr($regexp, 1));

				break;

				default:
					// Remove escapes of the previous delimiter
					$regexp = preg_replace('/((?:\\\\\\\\)*+)\\' . preg_quote($regexp_info['delimiter'], '/') . '/', '$1\\/', $regexp);
				break;
			}

			// Escape for js's "//" syntax
			$regexp = preg_replace('#(?<!\\\\)((?:\\\\\\\\)*+)/#', '$1\\/', $regexp);
		}

		// Escape line terminators
		$regexp = preg_replace_callback(
			"/(\\\\*)([\\r\\n]|\xE2\x80\xA8|\xE2\x80\xA9)/",
			function ($m)
			{
				$table = array(
					"\r" => '\\r',
					"\n" => '\\n',
					"\xE2\x80\xA8" => '\\u2028',
					"\xE2\x80\xA9" => '\\u2029'
				);

				// Ensure an even number of backslashes
				if (strlen($m[1]) & 1)
				{
					$m[1] .= '\\';
				}

				return $m[1] . $table[$m[2]];
			},
			$regexp
		);

		// Do a whitelist pass, not a blacklist one

		$modifiers = '';

		foreach ($regexp_info['modifiers'] as $modifier => $one)
		{
			switch($modifier)
			{
				case 'i':
				case 'm':
					$modifiers .= $modifier;

				/* no-default */
			}
		}

		
		// Last line of defence. Remove everything that javascript does not understand
		// that is not properly checked earlier

		// Remove the special escape anchors.
		$regexp = preg_replace('#\\\\[AZzG]#', '', $regexp);

		
		return array(
			'jsRegex' => $regexp,
			'matchesVsNames' => $match_vs_attribute,
			'modifiers' => $modifiers,
		);
	}

	/**
	* Replace Unicode properties in a string
	*
	* NOTE: does not support \X
	*
	* @link http://docs.php.net/manual/en/regexp.reference.unicode.php
	*
	* @param  string $str              Original string
	* @param  bool   $inCharacterClass Whether this string is in a character class
	* @param  bool   $dotAll           Whether PCRE_DOTALL is set
	* @return string                   Modified string
	*/
	protected static function translate_unicode_properties($str, $inCharacterClass, $dotAll)
	{
		$unicodeProps = self::$unicodeProps;

		$propNames = [];
		foreach (array_keys($unicodeProps) as $propName)
		{
			$propNames[] = $propName;
			$propNames[] = preg_replace('#(.)(.+)#', '$1\\{$2\\}', $propName);
			$propNames[] = preg_replace('#(.)(.+)#', '$1\\{\\^$2\\}', $propName);
		}

		$str = preg_replace_callback(
			'#(?<!\\\\)((?:\\\\\\\\)*+)\\\\(' . implode('|', $propNames) . ')#',
			function ($m) use ($inCharacterClass, $unicodeProps)
			{
				$propName = preg_replace('#[\\{\\}]#', '', $m[2]);

				if ($propName[1] === '^')
				{
					/**
					* Replace p^L with PL
					*/
					$propName = (($propName[0] === 'p') ? 'P' : 'p') . substr($propName, 2);
				}

				return (($inCharacterClass) ? '' : '[')
				     . $unicodeProps[$propName]
				     . (($inCharacterClass) ? '' : ']');
			},
			$str
		);

		if ($dotAll)
		{
			$str = preg_replace(
				'#(?<!\\\\)((?:\\\\\\\\)*+)\\.#',
				'$1[\\s\\S]',
				$str
			);
		}

		return $str;
	}


	/**
	* Javascript does not allow the same notation as PHP for Unicode ranges. Therefore, a translation to all
	*/
	protected static $unicodeProps = [
		'PL' => 'A-Za-z\\u00C0-\\u02C1\\u02C6-\\u02D1\\u02E0-\\u02E4\\u02EC-\\u02EE\\u0370-\\u0377\\u037A-\\u037F\\u0386-\\u0481\\u048A-\\u0556\\u0561-\\u0587\\u05D0-\\u05EA\\u05F0-\\u05F2\\u0620-\\u064A\\u066E-\\u06D5\\u06E5\\u06E6\\u06EE\\u06EF\\u06FA-\\u06FC\\u0710-\\u072F\\u074D-\\u07A5\\u07CA-\\u07EA\\u07F4\\u07F5\\u0800-\\u0815\\u0840-\\u0858\\u08A0-\\u08B2\\u0904-\\u0939\\u0958-\\u0961\\u0971-\\u0980\\u0985-\\u098C\\u098F\\u0990\\u0993-\\u09B2\\u09B6-\\u09B9\\u09DC-\\u09E1\\u09F0\\u09F1\\u0A05-\\u0A0A\\u0A0F\\u0A10\\u0A13-\\u0A39\\u0A59-\\u0A5E\\u0A72-\\u0A74\\u0A85-\\u0AB9\\u0AE0\\u0AE1\\u0B05-\\u0B0C\\u0B0F\\u0B10\\u0B13-\\u0B39\\u0B5C-\\u0B61\\u0B83-\\u0B8A\\u0B8E-\\u0B95\\u0B99-\\u0B9F\\u0BA3\\u0BA4\\u0BA8-\\u0BAA\\u0BAE-\\u0BB9\\u0C05-\\u0C39\\u0C58\\u0C59\\u0C60\\u0C61\\u0C85-\\u0CB9\\u0CDE-\\u0CE1\\u0CF1\\u0CF2\\u0D05-\\u0D3A\\u0D60\\u0D61\\u0D7A-\\u0D7F\\u0D85-\\u0D96\\u0D9A-\\u0DBD\\u0DC0-\\u0DC6\\u0E01-\\u0E33\\u0E40-\\u0E46\\u0E81-\\u0E84\\u0E87-\\u0E8A\\u0E94-\\u0EA7\\u0EAA-\\u0EB3\\u0EC0-\\u0EC6\\u0EDC-\\u0EDF\\u0F40-\\u0F6C\\u0F88-\\u0F8C\\u1000-\\u102A\\u1050-\\u1055\\u105A-\\u105D\\u1065\\u1066\\u106E-\\u1070\\u1075-\\u1081\\u10A0-\\u10C7\\u10D0-\\u124D\\u1250-\\u125D\\u1260-\\u128D\\u1290-\\u12B5\\u12B8-\\u12C5\\u12C8-\\u1315\\u1318-\\u135A\\u1380-\\u138F\\u13A0-\\u13F4\\u1401-\\u166C\\u166F-\\u169A\\u16A0-\\u16EA\\u16F1-\\u16F8\\u1700-\\u1711\\u1720-\\u1731\\u1740-\\u1751\\u1760-\\u1770\\u1780-\\u17B3\\u1820-\\u1877\\u1880-\\u18AA\\u18B0-\\u18F5\\u1900-\\u191E\\u1950-\\u196D\\u1970-\\u1974\\u1980-\\u19AB\\u19C1-\\u19C7\\u1A00-\\u1A16\\u1A20-\\u1A54\\u1B05-\\u1B33\\u1B45-\\u1B4B\\u1B83-\\u1BA0\\u1BAE\\u1BAF\\u1BBA-\\u1BE5\\u1C00-\\u1C23\\u1C4D-\\u1C4F\\u1C5A-\\u1C7D\\u1CE9-\\u1CF1\\u1CF5\\u1CF6\\u1D00-\\u1DBF\\u1E00-\\u1F15\\u1F18-\\u1F1D\\u1F20-\\u1F45\\u1F48-\\u1F4D\\u1F50-\\u1F7D\\u1F80-\\u1FBE\\u1FC2-\\u1FCC\\u1FD0-\\u1FD3\\u1FD6-\\u1FDB\\u1FE0-\\u1FEC\\u1FF2-\\u1FFC\\u2090-\\u209C\\u210A-\\u2115\\u2119-\\u211D\\u2124-\\u2139\\u213C-\\u213F\\u2145-\\u2149\\u2183\\u2184\\u2C00-\\u2CE4\\u2CEB-\\u2CEE\\u2CF2\\u2CF3\\u2D00-\\u2D27\\u2D30-\\u2D67\\u2D80-\\u2D96\\u2DA0-\\u2DDE\\u3005\\u3006\\u3031-\\u3035\\u303B\\u303C\\u3041-\\u3096\\u309D-\\u30FF\\u3105-\\u312D\\u3131-\\u318E\\u31A0-\\u31BA\\u31F0-\\u31FF\\u3400-\\u4DB5\\u4E00-\\u9FCC\\uA000-\\uA48C\\uA4D0-\\uA4FD\\uA500-\\uA60C\\uA610-\\uA61F\\uA62A\\uA62B\\uA640-\\uA66E\\uA67F-\\uA69D\\uA6A0-\\uA6E5\\uA717-\\uA71F\\uA722-\\uA788\\uA78B-\\uA7AD\\uA7B0\\uA7B1\\uA7F7-\\uA822\\uA840-\\uA873\\uA882-\\uA8B3\\uA8F2-\\uA8F7\\uA90A-\\uA925\\uA930-\\uA946\\uA960-\\uA97C\\uA984-\\uA9B2\\uA9E0-\\uA9EF\\uA9FA-\\uAA28\\uAA40-\\uAA4B\\uAA60-\\uAA76\\uAA7E-\\uAAB1\\uAAB5\\uAAB6\\uAAB9-\\uAABD\\uAAC0-\\uAAC2\\uAADB-\\uAADD\\uAAE0-\\uAAEA\\uAAF2-\\uAAF4\\uAB01-\\uAB06\\uAB09-\\uAB0E\\uAB11-\\uAB16\\uAB20-\\uAB5F\\uAB64\\uAB65\\uABC0-\\uABE2\\uAC00-\\uD7A3\\uD7B0-\\uD7C6\\uD7CB-\\uD7FB\\uF900-\\uFA6D\\uFA70-\\uFAD9\\uFB00-\\uFB06\\uFB13-\\uFB17\\uFB1D-\\uFBB1\\uFBD3-\\uFD3D\\uFD50-\\uFD8F\\uFD92-\\uFDC7\\uFDF0-\\uFDFB\\uFE70-\\uFEFC\\uFF21-\\uFF3A\\uFF41-\\uFF5A\\uFF66-\\uFFBE\\uFFC2-\\uFFC7\\uFFCA-\\uFFCF\\uFFD2-\\uFFD7\\uFFDA-\\uFFDC',
		'PLm' => '\\u02B0-\\u02C1\\u02C6-\\u02D1\\u02E0-\\u02E4\\u02EC-\\u02EE\\u06E5\\u06E6\\u07F4\\u07F5\\u1C78-\\u1C7D\\u1D2C-\\u1D6A\\u1D9B-\\u1DBF\\u2090-\\u209C\\u2C7C\\u2C7D\\u3031-\\u3035\\u309D\\u309E\\u30FC-\\u30FE\\uA4F8-\\uA4FD\\uA69C\\uA69D\\uA717-\\uA71F\\uA7F8\\uA7F9\\uAAF3\\uAAF4\\uAB5C-\\uAB5F\\uFF9E\\uFF9F',
		'PLo' => '\\u01C0-\\u01C3\\u05D0-\\u05EA\\u05F0-\\u05F2\\u0620-\\u064A\\u066E-\\u06D5\\u06EE\\u06EF\\u06FA-\\u06FC\\u0710-\\u072F\\u074D-\\u07A5\\u07CA-\\u07EA\\u0800-\\u0815\\u0840-\\u0858\\u08A0-\\u08B2\\u0904-\\u0939\\u0958-\\u0961\\u0972-\\u0980\\u0985-\\u098C\\u098F\\u0990\\u0993-\\u09B2\\u09B6-\\u09B9\\u09DC-\\u09E1\\u09F0\\u09F1\\u0A05-\\u0A0A\\u0A0F\\u0A10\\u0A13-\\u0A39\\u0A59-\\u0A5E\\u0A72-\\u0A74\\u0A85-\\u0AB9\\u0AE0\\u0AE1\\u0B05-\\u0B0C\\u0B0F\\u0B10\\u0B13-\\u0B39\\u0B5C-\\u0B61\\u0B83-\\u0B8A\\u0B8E-\\u0B95\\u0B99-\\u0B9F\\u0BA3\\u0BA4\\u0BA8-\\u0BAA\\u0BAE-\\u0BB9\\u0C05-\\u0C39\\u0C58\\u0C59\\u0C60\\u0C61\\u0C85-\\u0CB9\\u0CDE-\\u0CE1\\u0CF1\\u0CF2\\u0D05-\\u0D3A\\u0D60\\u0D61\\u0D7A-\\u0D7F\\u0D85-\\u0D96\\u0D9A-\\u0DBD\\u0DC0-\\u0DC6\\u0E01-\\u0E33\\u0E40-\\u0E45\\u0E81-\\u0E84\\u0E87-\\u0E8A\\u0E94-\\u0EA7\\u0EAA-\\u0EB3\\u0EC0-\\u0EC4\\u0EDC-\\u0EDF\\u0F40-\\u0F6C\\u0F88-\\u0F8C\\u1000-\\u102A\\u1050-\\u1055\\u105A-\\u105D\\u1065\\u1066\\u106E-\\u1070\\u1075-\\u1081\\u10D0-\\u10FA\\u10FD-\\u124D\\u1250-\\u125D\\u1260-\\u128D\\u1290-\\u12B5\\u12B8-\\u12C5\\u12C8-\\u1315\\u1318-\\u135A\\u1380-\\u138F\\u13A0-\\u13F4\\u1401-\\u166C\\u166F-\\u169A\\u16A0-\\u16EA\\u16F1-\\u16F8\\u1700-\\u1711\\u1720-\\u1731\\u1740-\\u1751\\u1760-\\u1770\\u1780-\\u17B3\\u1820-\\u1877\\u1880-\\u18AA\\u18B0-\\u18F5\\u1900-\\u191E\\u1950-\\u196D\\u1970-\\u1974\\u1980-\\u19AB\\u19C1-\\u19C7\\u1A00-\\u1A16\\u1A20-\\u1A54\\u1B05-\\u1B33\\u1B45-\\u1B4B\\u1B83-\\u1BA0\\u1BAE\\u1BAF\\u1BBA-\\u1BE5\\u1C00-\\u1C23\\u1C4D-\\u1C4F\\u1C5A-\\u1C77\\u1CE9-\\u1CF1\\u1CF5\\u1CF6\\u2135-\\u2138\\u2D30-\\u2D67\\u2D80-\\u2D96\\u2DA0-\\u2DDE\\u3041-\\u3096\\u309F-\\u30FA\\u3105-\\u312D\\u3131-\\u318E\\u31A0-\\u31BA\\u31F0-\\u31FF\\u3400-\\u4DB5\\u4E00-\\u9FCC\\uA000-\\uA48C\\uA4D0-\\uA4F7\\uA500-\\uA60B\\uA610-\\uA61F\\uA62A\\uA62B\\uA6A0-\\uA6E5\\uA7FB-\\uA822\\uA840-\\uA873\\uA882-\\uA8B3\\uA8F2-\\uA8F7\\uA90A-\\uA925\\uA930-\\uA946\\uA960-\\uA97C\\uA984-\\uA9B2\\uA9E0-\\uA9E4\\uA9E7-\\uA9EF\\uA9FA-\\uAA28\\uAA40-\\uAA4B\\uAA60-\\uAA76\\uAA7E-\\uAAB1\\uAAB5\\uAAB6\\uAAB9-\\uAABD\\uAAC0-\\uAAC2\\uAADB\\uAADC\\uAAE0-\\uAAEA\\uAB01-\\uAB06\\uAB09-\\uAB0E\\uAB11-\\uAB16\\uAB20-\\uAB2E\\uABC0-\\uABE2\\uAC00-\\uD7A3\\uD7B0-\\uD7C6\\uD7CB-\\uD7FB\\uF900-\\uFA6D\\uFA70-\\uFAD9\\uFB1D-\\uFBB1\\uFBD3-\\uFD3D\\uFD50-\\uFD8F\\uFD92-\\uFDC7\\uFDF0-\\uFDFB\\uFE70-\\uFEFC\\uFF66-\\uFF9D\\uFFA0-\\uFFBE\\uFFC2-\\uFFC7\\uFFCA-\\uFFCF\\uFFD2-\\uFFD7\\uFFDA-\\uFFDC',
		'PN' => '0-9\\u00B2\\u00B3\\u00BC-\\u00BE\\u0660-\\u0669\\u06F0-\\u06F9\\u07C0-\\u07C9\\u0966-\\u096F\\u09E6-\\u09EF\\u09F4-\\u09F9\\u0A66-\\u0A6F\\u0AE6-\\u0AEF\\u0B66-\\u0B6F\\u0B72-\\u0B77\\u0BE6-\\u0BF2\\u0C66-\\u0C6F\\u0C78-\\u0C7E\\u0CE6-\\u0CEF\\u0D66-\\u0D75\\u0DE6-\\u0DEF\\u0E50-\\u0E59\\u0ED0-\\u0ED9\\u0F20-\\u0F33\\u1040-\\u1049\\u1090-\\u1099\\u1369-\\u137C\\u16EE-\\u16F0\\u17E0-\\u17E9\\u17F0-\\u17F9\\u1810-\\u1819\\u1946-\\u194F\\u19D0-\\u19DA\\u1A80-\\u1A89\\u1A90-\\u1A99\\u1B50-\\u1B59\\u1BB0-\\u1BB9\\u1C40-\\u1C49\\u1C50-\\u1C59\\u2074-\\u2079\\u2080-\\u2089\\u2150-\\u2182\\u2185-\\u2189\\u2460-\\u249B\\u24EA-\\u24FF\\u2776-\\u2793\\u3021-\\u3029\\u3038-\\u303A\\u3192-\\u3195\\u3220-\\u3229\\u3248-\\u325F\\u3280-\\u3289\\u32B1-\\u32BF\\uA620-\\uA629\\uA6E6-\\uA6EF\\uA830-\\uA835\\uA8D0-\\uA8D9\\uA900-\\uA909\\uA9D0-\\uA9D9\\uA9F0-\\uA9F9\\uAA50-\\uAA59\\uABF0-\\uABF9\\uFF10-\\uFF19',
		'PNd' => '0-9\\u0660-\\u0669\\u06F0-\\u06F9\\u07C0-\\u07C9\\u0966-\\u096F\\u09E6-\\u09EF\\u0A66-\\u0A6F\\u0AE6-\\u0AEF\\u0B66-\\u0B6F\\u0BE6-\\u0BEF\\u0C66-\\u0C6F\\u0CE6-\\u0CEF\\u0D66-\\u0D6F\\u0DE6-\\u0DEF\\u0E50-\\u0E59\\u0ED0-\\u0ED9\\u0F20-\\u0F29\\u1040-\\u1049\\u1090-\\u1099\\u17E0-\\u17E9\\u1810-\\u1819\\u1946-\\u194F\\u19D0-\\u19D9\\u1A80-\\u1A89\\u1A90-\\u1A99\\u1B50-\\u1B59\\u1BB0-\\u1BB9\\u1C40-\\u1C49\\u1C50-\\u1C59\\uA620-\\uA629\\uA8D0-\\uA8D9\\uA900-\\uA909\\uA9D0-\\uA9D9\\uA9F0-\\uA9F9\\uAA50-\\uAA59\\uABF0-\\uABF9\\uFF10-\\uFF19',
		'PNl' => '\\u16EE-\\u16F0\\u2160-\\u2182\\u2185-\\u2188\\u3021-\\u3029\\u3038-\\u303A\\uA6E6-\\uA6EF',
		'PNo' => '\\u00B2\\u00B3\\u00BC-\\u00BE\\u09F4-\\u09F9\\u0B72-\\u0B77\\u0BF0-\\u0BF2\\u0C78-\\u0C7E\\u0D70-\\u0D75\\u0F2A-\\u0F33\\u1369-\\u137C\\u17F0-\\u17F9\\u2074-\\u2079\\u2080-\\u2089\\u2150-\\u215F\\u2460-\\u249B\\u24EA-\\u24FF\\u2776-\\u2793\\u3192-\\u3195\\u3220-\\u3229\\u3248-\\u325F\\u3280-\\u3289\\u32B1-\\u32BF\\uA830-\\uA835',
		'PP' => '\\!-/\\:;\\?@\\[-_\\{-\\}\\u00B6\\u00B7\\u055A-\\u055F\\u0589\\u058A\\u05BE-\\u05C0\\u05F3\\u05F4\\u0609-\\u060D\\u061E\\u061F\\u066A-\\u066D\\u0700-\\u070D\\u07F7-\\u07F9\\u0830-\\u083E\\u0964\\u0965\\u0E5A\\u0E5B\\u0F04-\\u0F14\\u0F3A-\\u0F3D\\u0FD0-\\u0FD4\\u0FD9\\u0FDA\\u104A-\\u104F\\u1360-\\u1368\\u166D\\u166E\\u169B\\u169C\\u16EB-\\u16ED\\u1735\\u1736\\u17D4-\\u17DA\\u1800-\\u180A\\u1944\\u1945\\u1A1E\\u1A1F\\u1AA0-\\u1AAD\\u1B5A-\\u1B60\\u1BFC-\\u1BFF\\u1C3B-\\u1C3F\\u1C7E\\u1C7F\\u1CC0-\\u1CC7\\u2010-\\u2027\\u2030-\\u205E\\u207D\\u207E\\u208D\\u208E\\u2308-\\u230B\\u2329\\u232A\\u2768-\\u2775\\u27C5\\u27C6\\u27E6-\\u27EF\\u2983-\\u2998\\u29D8-\\u29DB\\u29FC\\u29FD\\u2CF9-\\u2CFF\\u2E00-\\u2E42\\u3001-\\u3003\\u3008-\\u3011\\u3014-\\u301F\\uA4FE\\uA4FF\\uA60D-\\uA60F\\uA6F2-\\uA6F7\\uA874-\\uA877\\uA8CE\\uA8CF\\uA8F8-\\uA8FA\\uA92E\\uA92F\\uA9C1-\\uA9CD\\uA9DE\\uA9DF\\uAA5C-\\uAA5F\\uAADE\\uAADF\\uAAF0\\uAAF1\\uFD3E\\uFD3F\\uFE10-\\uFE19\\uFE30-\\uFE63\\uFE68-\\uFE6B\\uFF01-\\uFF0F\\uFF1A\\uFF1B\\uFF1F\\uFF20\\uFF3B-\\uFF3F\\uFF5B-\\uFF65',
		'PPc' => '\\u203F\\u2040\\uFE33\\uFE34\\uFE4D-\\uFE4F',
		'PPd' => '\\u2010-\\u2015\\u2E3A\\u2E3B\\uFE31\\uFE32',
		'PPe' => '\\u0F3B-\\u0F3D\\u2309-\\u230B\\u2769-\\u2775\\u27E7-\\u27EF\\u2984-\\u2998\\u29D9-\\u29DB\\u2E23-\\u2E29\\u3009-\\u3011\\u3015-\\u301B\\u301E\\u301F\\uFE36-\\uFE44\\uFE5A-\\uFE5E',
		'PPf' => '\\u2E03-\\u2E05',
		'PPi' => '\\u201B\\u201C\\u2E02-\\u2E04',
		'PPo' => '\\!-\'\\*-/\\:;\\?@\\u00B6\\u00B7\\u055A-\\u055F\\u05F3\\u05F4\\u0609-\\u060D\\u061E\\u061F\\u066A-\\u066D\\u0700-\\u070D\\u07F7-\\u07F9\\u0830-\\u083E\\u0964\\u0965\\u0E5A\\u0E5B\\u0F04-\\u0F14\\u0FD0-\\u0FD4\\u0FD9\\u0FDA\\u104A-\\u104F\\u1360-\\u1368\\u166D\\u166E\\u16EB-\\u16ED\\u1735\\u1736\\u17D4-\\u17DA\\u1800-\\u180A\\u1944\\u1945\\u1A1E\\u1A1F\\u1AA0-\\u1AAD\\u1B5A-\\u1B60\\u1BFC-\\u1BFF\\u1C3B-\\u1C3F\\u1C7E\\u1C7F\\u1CC0-\\u1CC7\\u2016\\u2017\\u2020-\\u2027\\u2030-\\u2038\\u203B-\\u203E\\u2041-\\u2043\\u2047-\\u205E\\u2CF9-\\u2CFF\\u2E00\\u2E01\\u2E06-\\u2E08\\u2E0E-\\u2E1B\\u2E1E\\u2E1F\\u2E2A-\\u2E39\\u2E3C-\\u2E41\\u3001-\\u3003\\uA4FE\\uA4FF\\uA60D-\\uA60F\\uA6F2-\\uA6F7\\uA874-\\uA877\\uA8CE\\uA8CF\\uA8F8-\\uA8FA\\uA92E\\uA92F\\uA9C1-\\uA9CD\\uA9DE\\uA9DF\\uAA5C-\\uAA5F\\uAADE\\uAADF\\uAAF0\\uAAF1\\uFE10-\\uFE16\\uFE45\\uFE46\\uFE49-\\uFE4C\\uFE50-\\uFE57\\uFE5F-\\uFE61\\uFE68-\\uFE6B\\uFF01-\\uFF07\\uFF0A-\\uFF0F\\uFF1A\\uFF1B\\uFF1F\\uFF20\\uFF64\\uFF65',
		'PPs' => '\\u0F3A-\\u0F3C\\u2308-\\u230A\\u2768-\\u2774\\u27E6-\\u27EE\\u2983-\\u2997\\u29D8-\\u29DA\\u2E22-\\u2E28\\u3008-\\u3010\\u3014-\\u301A\\uFE35-\\uFE43\\uFE59-\\uFE5D',
		'PS' => '\\<-\\>\\^-`\\|-~\\u00A2-\\u00A9\\u00AC-\\u00B1\\u02C2-\\u02C5\\u02D2-\\u02DF\\u02E5-\\u02FF\\u0384\\u0385\\u058D-\\u058F\\u0606-\\u0608\\u060E\\u060F\\u06FD\\u06FE\\u09F2\\u09F3\\u09FA\\u09FB\\u0BF3-\\u0BFA\\u0F01-\\u0F03\\u0F13-\\u0F17\\u0F1A-\\u0F1F\\u0F34-\\u0F38\\u0FBE-\\u0FCF\\u0FD5-\\u0FD8\\u109E\\u109F\\u1390-\\u1399\\u19DE-\\u19FF\\u1B61-\\u1B6A\\u1B74-\\u1B7C\\u1FBD-\\u1FC1\\u1FCD-\\u1FCF\\u1FDD-\\u1FDF\\u1FED-\\u1FEF\\u1FFD\\u1FFE\\u207A-\\u207C\\u208A-\\u208C\\u20A0-\\u20BD\\u2100-\\u2109\\u2114-\\u2118\\u211E-\\u2129\\u213A\\u213B\\u2140-\\u2144\\u214A-\\u214F\\u2190-\\u2307\\u230C-\\u2328\\u232B-\\u23FA\\u2400-\\u2426\\u2440-\\u244A\\u249C-\\u24E9\\u2500-\\u2767\\u2794-\\u27C4\\u27C7-\\u27E5\\u27F0-\\u2982\\u2999-\\u29D7\\u29DC-\\u29FB\\u29FE-\\u2B73\\u2B76-\\u2B95\\u2B98-\\u2BB9\\u2BBD-\\u2BD1\\u2CE5-\\u2CEA\\u2E80-\\u2EF3\\u2F00-\\u2FD5\\u2FF0-\\u2FFB\\u3012\\u3013\\u3036\\u3037\\u303E\\u303F\\u309B\\u309C\\u3190\\u3191\\u3196-\\u319F\\u31C0-\\u31E3\\u3200-\\u321E\\u322A-\\u3247\\u3260-\\u327F\\u328A-\\u32B0\\u32C0-\\u33FF\\u4DC0-\\u4DFF\\uA490-\\uA4C6\\uA700-\\uA716\\uA720\\uA721\\uA789\\uA78A\\uA828-\\uA82B\\uA836-\\uA839\\uAA77-\\uAA79\\uFBB2-\\uFBC1\\uFDFC\\uFDFD\\uFE62-\\uFE66\\uFF1C-\\uFF1E\\uFF3E-\\uFF40\\uFF5C-\\uFF5E\\uFFE0-\\uFFEE\\uFFFC\\uFFFD',
		'PSc' => '\\u00A2-\\u00A5\\u09F2\\u09F3\\u20A0-\\u20BD\\uFFE0\\uFFE1\\uFFE5\\uFFE6',
		'PSk' => '\\^-`\\u02C2-\\u02C5\\u02D2-\\u02DF\\u02E5-\\u02FF\\u0384\\u0385\\u1FBD-\\u1FC1\\u1FCD-\\u1FCF\\u1FDD-\\u1FDF\\u1FED-\\u1FEF\\u1FFD\\u1FFE\\u309B\\u309C\\uA700-\\uA716\\uA720\\uA721\\uA789\\uA78A\\uFBB2-\\uFBC1\\uFF3E-\\uFF40',
		'PSm' => '\\<-\\>\\|-~\\u0606-\\u0608\\u207A-\\u207C\\u208A-\\u208C\\u2140-\\u2144\\u2190-\\u2194\\u219A\\u219B\\u21CE\\u21CF\\u21D2-\\u21D4\\u21F4-\\u22FF\\u2320\\u2321\\u239B-\\u23B3\\u23DC-\\u23E1\\u25F8-\\u25FF\\u27C0-\\u27C4\\u27C7-\\u27E5\\u27F0-\\u27FF\\u2900-\\u2982\\u2999-\\u29D7\\u29DC-\\u29FB\\u29FE-\\u2AFF\\u2B30-\\u2B44\\u2B47-\\u2B4C\\uFE62-\\uFE66\\uFF1C-\\uFF1E\\uFF5C-\\uFF5E\\uFFE9-\\uFFEC',
		'PSo' => '\\u00AE-\\u00B0\\u058D\\u058E\\u060E\\u060F\\u06FD\\u06FE\\u0BF3-\\u0BFA\\u0F01-\\u0F03\\u0F13-\\u0F17\\u0F1A-\\u0F1F\\u0F34-\\u0F38\\u0FBE-\\u0FCF\\u0FD5-\\u0FD8\\u109E\\u109F\\u1390-\\u1399\\u19DE-\\u19FF\\u1B61-\\u1B6A\\u1B74-\\u1B7C\\u2100-\\u2109\\u2114-\\u2117\\u211E-\\u2129\\u213A\\u213B\\u214A-\\u214F\\u2195-\\u2199\\u219C-\\u21CD\\u21D0-\\u21F3\\u2300-\\u2307\\u230C-\\u231F\\u2322-\\u2328\\u232B-\\u239A\\u23B4-\\u23DB\\u23E2-\\u23FA\\u2400-\\u2426\\u2440-\\u244A\\u249C-\\u24E9\\u2500-\\u25F7\\u2600-\\u2767\\u2794-\\u27BF\\u2800-\\u28FF\\u2B00-\\u2B2F\\u2B45\\u2B46\\u2B4D-\\u2B73\\u2B76-\\u2B95\\u2B98-\\u2BB9\\u2BBD-\\u2BD1\\u2CE5-\\u2CEA\\u2E80-\\u2EF3\\u2F00-\\u2FD5\\u2FF0-\\u2FFB\\u3012\\u3013\\u3036\\u3037\\u303E\\u303F\\u3190\\u3191\\u3196-\\u319F\\u31C0-\\u31E3\\u3200-\\u321E\\u322A-\\u3247\\u3260-\\u327F\\u328A-\\u32B0\\u32C0-\\u33FF\\u4DC0-\\u4DFF\\uA490-\\uA4C6\\uA828-\\uA82B\\uA836-\\uA839\\uAA77-\\uAA79\\uFFED\\uFFEE\\uFFFC\\uFFFD',
		'PZ' => '\\u2000-\\u200A\\u2028\\u2029',
		'PZl' => '',
		'PZp' => '',
		'PZs' => '\\u2000-\\u200A',
		'pL' => 'A-Za-z\\u00AA\\u00B5\\u00BA\\u00C0-\\u00D6\\u00D8-\\u00F6\\u00F8-\\u02C1\\u02C6-\\u02D1\\u02E0-\\u02E4\\u02EC\\u02EE\\u0370-\\u0374\\u0376\\u0377\\u037A-\\u037D\\u037F\\u0386\\u0388-\\u038A\\u038C\\u038E-\\u03A1\\u03A3-\\u03F5\\u03F7-\\u0481\\u048A-\\u052F\\u0531-\\u0556\\u0559\\u0561-\\u0587\\u05D0-\\u05EA\\u05F0-\\u05F2\\u0620-\\u064A\\u066E\\u066F\\u0671-\\u06D3\\u06D5\\u06E5\\u06E6\\u06EE\\u06EF\\u06FA-\\u06FC\\u06FF\\u0710\\u0712-\\u072F\\u074D-\\u07A5\\u07B1\\u07CA-\\u07EA\\u07F4\\u07F5\\u07FA\\u0800-\\u0815\\u081A\\u0824\\u0828\\u0840-\\u0858\\u08A0-\\u08B2\\u0904-\\u0939\\u093D\\u0950\\u0958-\\u0961\\u0971-\\u0980\\u0985-\\u098C\\u098F\\u0990\\u0993-\\u09A8\\u09AA-\\u09B0\\u09B2\\u09B6-\\u09B9\\u09BD\\u09CE\\u09DC\\u09DD\\u09DF-\\u09E1\\u09F0\\u09F1\\u0A05-\\u0A0A\\u0A0F\\u0A10\\u0A13-\\u0A28\\u0A2A-\\u0A30\\u0A32\\u0A33\\u0A35\\u0A36\\u0A38\\u0A39\\u0A59-\\u0A5C\\u0A5E\\u0A72-\\u0A74\\u0A85-\\u0A8D\\u0A8F-\\u0A91\\u0A93-\\u0AA8\\u0AAA-\\u0AB0\\u0AB2\\u0AB3\\u0AB5-\\u0AB9\\u0ABD\\u0AD0\\u0AE0\\u0AE1\\u0B05-\\u0B0C\\u0B0F\\u0B10\\u0B13-\\u0B28\\u0B2A-\\u0B30\\u0B32\\u0B33\\u0B35-\\u0B39\\u0B3D\\u0B5C\\u0B5D\\u0B5F-\\u0B61\\u0B71\\u0B83\\u0B85-\\u0B8A\\u0B8E-\\u0B90\\u0B92-\\u0B95\\u0B99\\u0B9A\\u0B9C\\u0B9E\\u0B9F\\u0BA3\\u0BA4\\u0BA8-\\u0BAA\\u0BAE-\\u0BB9\\u0BD0\\u0C05-\\u0C0C\\u0C0E-\\u0C10\\u0C12-\\u0C28\\u0C2A-\\u0C39\\u0C3D\\u0C58\\u0C59\\u0C60\\u0C61\\u0C85-\\u0C8C\\u0C8E-\\u0C90\\u0C92-\\u0CA8\\u0CAA-\\u0CB3\\u0CB5-\\u0CB9\\u0CBD\\u0CDE\\u0CE0\\u0CE1\\u0CF1\\u0CF2\\u0D05-\\u0D0C\\u0D0E-\\u0D10\\u0D12-\\u0D3A\\u0D3D\\u0D4E\\u0D60\\u0D61\\u0D7A-\\u0D7F\\u0D85-\\u0D96\\u0D9A-\\u0DB1\\u0DB3-\\u0DBB\\u0DBD\\u0DC0-\\u0DC6\\u0E01-\\u0E30\\u0E32\\u0E33\\u0E40-\\u0E46\\u0E81\\u0E82\\u0E84\\u0E87\\u0E88\\u0E8A\\u0E8D\\u0E94-\\u0E97\\u0E99-\\u0E9F\\u0EA1-\\u0EA3\\u0EA5\\u0EA7\\u0EAA\\u0EAB\\u0EAD-\\u0EB0\\u0EB2\\u0EB3\\u0EBD\\u0EC0-\\u0EC4\\u0EC6\\u0EDC-\\u0EDF\\u0F00\\u0F40-\\u0F47\\u0F49-\\u0F6C\\u0F88-\\u0F8C\\u1000-\\u102A\\u103F\\u1050-\\u1055\\u105A-\\u105D\\u1061\\u1065\\u1066\\u106E-\\u1070\\u1075-\\u1081\\u108E\\u10A0-\\u10C5\\u10C7\\u10CD\\u10D0-\\u10FA\\u10FC-\\u1248\\u124A-\\u124D\\u1250-\\u1256\\u1258\\u125A-\\u125D\\u1260-\\u1288\\u128A-\\u128D\\u1290-\\u12B0\\u12B2-\\u12B5\\u12B8-\\u12BE\\u12C0\\u12C2-\\u12C5\\u12C8-\\u12D6\\u12D8-\\u1310\\u1312-\\u1315\\u1318-\\u135A\\u1380-\\u138F\\u13A0-\\u13F4\\u1401-\\u166C\\u166F-\\u167F\\u1681-\\u169A\\u16A0-\\u16EA\\u16F1-\\u16F8\\u1700-\\u170C\\u170E-\\u1711\\u1720-\\u1731\\u1740-\\u1751\\u1760-\\u176C\\u176E-\\u1770\\u1780-\\u17B3\\u17D7\\u17DC\\u1820-\\u1877\\u1880-\\u18A8\\u18AA\\u18B0-\\u18F5\\u1900-\\u191E\\u1950-\\u196D\\u1970-\\u1974\\u1980-\\u19AB\\u19C1-\\u19C7\\u1A00-\\u1A16\\u1A20-\\u1A54\\u1AA7\\u1B05-\\u1B33\\u1B45-\\u1B4B\\u1B83-\\u1BA0\\u1BAE\\u1BAF\\u1BBA-\\u1BE5\\u1C00-\\u1C23\\u1C4D-\\u1C4F\\u1C5A-\\u1C7D\\u1CE9-\\u1CEC\\u1CEE-\\u1CF1\\u1CF5\\u1CF6\\u1D00-\\u1DBF\\u1E00-\\u1F15\\u1F18-\\u1F1D\\u1F20-\\u1F45\\u1F48-\\u1F4D\\u1F50-\\u1F57\\u1F59\\u1F5B\\u1F5D\\u1F5F-\\u1F7D\\u1F80-\\u1FB4\\u1FB6-\\u1FBC\\u1FBE\\u1FC2-\\u1FC4\\u1FC6-\\u1FCC\\u1FD0-\\u1FD3\\u1FD6-\\u1FDB\\u1FE0-\\u1FEC\\u1FF2-\\u1FF4\\u1FF6-\\u1FFC\\u2071\\u207F\\u2090-\\u209C\\u2102\\u2107\\u210A-\\u2113\\u2115\\u2119-\\u211D\\u2124\\u2126\\u2128\\u212A-\\u212D\\u212F-\\u2139\\u213C-\\u213F\\u2145-\\u2149\\u214E\\u2183\\u2184\\u2C00-\\u2C2E\\u2C30-\\u2C5E\\u2C60-\\u2CE4\\u2CEB-\\u2CEE\\u2CF2\\u2CF3\\u2D00-\\u2D25\\u2D27\\u2D2D\\u2D30-\\u2D67\\u2D6F\\u2D80-\\u2D96\\u2DA0-\\u2DA6\\u2DA8-\\u2DAE\\u2DB0-\\u2DB6\\u2DB8-\\u2DBE\\u2DC0-\\u2DC6\\u2DC8-\\u2DCE\\u2DD0-\\u2DD6\\u2DD8-\\u2DDE\\u2E2F\\u3005\\u3006\\u3031-\\u3035\\u303B\\u303C\\u3041-\\u3096\\u309D-\\u309F\\u30A1-\\u30FA\\u30FC-\\u30FF\\u3105-\\u312D\\u3131-\\u318E\\u31A0-\\u31BA\\u31F0-\\u31FF\\u3400-\\u4DB5\\u4E00-\\u9FCC\\uA000-\\uA48C\\uA4D0-\\uA4FD\\uA500-\\uA60C\\uA610-\\uA61F\\uA62A\\uA62B\\uA640-\\uA66E\\uA67F-\\uA69D\\uA6A0-\\uA6E5\\uA717-\\uA71F\\uA722-\\uA788\\uA78B-\\uA78E\\uA790-\\uA7AD\\uA7B0\\uA7B1\\uA7F7-\\uA801\\uA803-\\uA805\\uA807-\\uA80A\\uA80C-\\uA822\\uA840-\\uA873\\uA882-\\uA8B3\\uA8F2-\\uA8F7\\uA8FB\\uA90A-\\uA925\\uA930-\\uA946\\uA960-\\uA97C\\uA984-\\uA9B2\\uA9CF\\uA9E0-\\uA9E4\\uA9E6-\\uA9EF\\uA9FA-\\uA9FE\\uAA00-\\uAA28\\uAA40-\\uAA42\\uAA44-\\uAA4B\\uAA60-\\uAA76\\uAA7A\\uAA7E-\\uAAAF\\uAAB1\\uAAB5\\uAAB6\\uAAB9-\\uAABD\\uAAC0\\uAAC2\\uAADB-\\uAADD\\uAAE0-\\uAAEA\\uAAF2-\\uAAF4\\uAB01-\\uAB06\\uAB09-\\uAB0E\\uAB11-\\uAB16\\uAB20-\\uAB26\\uAB28-\\uAB2E\\uAB30-\\uAB5A\\uAB5C-\\uAB5F\\uAB64\\uAB65\\uABC0-\\uABE2\\uAC00-\\uD7A3\\uD7B0-\\uD7C6\\uD7CB-\\uD7FB\\uF900-\\uFA6D\\uFA70-\\uFAD9\\uFB00-\\uFB06\\uFB13-\\uFB17\\uFB1D\\uFB1F-\\uFB28\\uFB2A-\\uFB36\\uFB38-\\uFB3C\\uFB3E\\uFB40\\uFB41\\uFB43\\uFB44\\uFB46-\\uFBB1\\uFBD3-\\uFD3D\\uFD50-\\uFD8F\\uFD92-\\uFDC7\\uFDF0-\\uFDFB\\uFE70-\\uFE74\\uFE76-\\uFEFC\\uFF21-\\uFF3A\\uFF41-\\uFF5A\\uFF66-\\uFFBE\\uFFC2-\\uFFC7\\uFFCA-\\uFFCF\\uFFD2-\\uFFD7\\uFFDA-\\uFFDC',
		'pLm' => '\\u02B0-\\u02C1\\u02C6-\\u02D1\\u02E0-\\u02E4\\u02EC\\u02EE\\u0374\\u037A\\u0559\\u0640\\u06E5\\u06E6\\u07F4\\u07F5\\u07FA\\u081A\\u0824\\u0828\\u0971\\u0E46\\u0EC6\\u10FC\\u17D7\\u1843\\u1AA7\\u1C78-\\u1C7D\\u1D2C-\\u1D6A\\u1D78\\u1D9B-\\u1DBF\\u2071\\u207F\\u2090-\\u209C\\u2C7C\\u2C7D\\u2D6F\\u2E2F\\u3005\\u3031-\\u3035\\u303B\\u309D\\u309E\\u30FC-\\u30FE\\uA015\\uA4F8-\\uA4FD\\uA60C\\uA67F\\uA69C\\uA69D\\uA717-\\uA71F\\uA770\\uA788\\uA7F8\\uA7F9\\uA9CF\\uA9E6\\uAA70\\uAADD\\uAAF3\\uAAF4\\uAB5C-\\uAB5F\\uFF70\\uFF9E\\uFF9F',
		'pLo' => '\\u00AA\\u00BA\\u01BB\\u01C0-\\u01C3\\u0294\\u05D0-\\u05EA\\u05F0-\\u05F2\\u0620-\\u063F\\u0641-\\u064A\\u066E\\u066F\\u0671-\\u06D3\\u06D5\\u06EE\\u06EF\\u06FA-\\u06FC\\u06FF\\u0710\\u0712-\\u072F\\u074D-\\u07A5\\u07B1\\u07CA-\\u07EA\\u0800-\\u0815\\u0840-\\u0858\\u08A0-\\u08B2\\u0904-\\u0939\\u093D\\u0950\\u0958-\\u0961\\u0972-\\u0980\\u0985-\\u098C\\u098F\\u0990\\u0993-\\u09A8\\u09AA-\\u09B0\\u09B2\\u09B6-\\u09B9\\u09BD\\u09CE\\u09DC\\u09DD\\u09DF-\\u09E1\\u09F0\\u09F1\\u0A05-\\u0A0A\\u0A0F\\u0A10\\u0A13-\\u0A28\\u0A2A-\\u0A30\\u0A32\\u0A33\\u0A35\\u0A36\\u0A38\\u0A39\\u0A59-\\u0A5C\\u0A5E\\u0A72-\\u0A74\\u0A85-\\u0A8D\\u0A8F-\\u0A91\\u0A93-\\u0AA8\\u0AAA-\\u0AB0\\u0AB2\\u0AB3\\u0AB5-\\u0AB9\\u0ABD\\u0AD0\\u0AE0\\u0AE1\\u0B05-\\u0B0C\\u0B0F\\u0B10\\u0B13-\\u0B28\\u0B2A-\\u0B30\\u0B32\\u0B33\\u0B35-\\u0B39\\u0B3D\\u0B5C\\u0B5D\\u0B5F-\\u0B61\\u0B71\\u0B83\\u0B85-\\u0B8A\\u0B8E-\\u0B90\\u0B92-\\u0B95\\u0B99\\u0B9A\\u0B9C\\u0B9E\\u0B9F\\u0BA3\\u0BA4\\u0BA8-\\u0BAA\\u0BAE-\\u0BB9\\u0BD0\\u0C05-\\u0C0C\\u0C0E-\\u0C10\\u0C12-\\u0C28\\u0C2A-\\u0C39\\u0C3D\\u0C58\\u0C59\\u0C60\\u0C61\\u0C85-\\u0C8C\\u0C8E-\\u0C90\\u0C92-\\u0CA8\\u0CAA-\\u0CB3\\u0CB5-\\u0CB9\\u0CBD\\u0CDE\\u0CE0\\u0CE1\\u0CF1\\u0CF2\\u0D05-\\u0D0C\\u0D0E-\\u0D10\\u0D12-\\u0D3A\\u0D3D\\u0D4E\\u0D60\\u0D61\\u0D7A-\\u0D7F\\u0D85-\\u0D96\\u0D9A-\\u0DB1\\u0DB3-\\u0DBB\\u0DBD\\u0DC0-\\u0DC6\\u0E01-\\u0E30\\u0E32\\u0E33\\u0E40-\\u0E45\\u0E81\\u0E82\\u0E84\\u0E87\\u0E88\\u0E8A\\u0E8D\\u0E94-\\u0E97\\u0E99-\\u0E9F\\u0EA1-\\u0EA3\\u0EA5\\u0EA7\\u0EAA\\u0EAB\\u0EAD-\\u0EB0\\u0EB2\\u0EB3\\u0EBD\\u0EC0-\\u0EC4\\u0EDC-\\u0EDF\\u0F00\\u0F40-\\u0F47\\u0F49-\\u0F6C\\u0F88-\\u0F8C\\u1000-\\u102A\\u103F\\u1050-\\u1055\\u105A-\\u105D\\u1061\\u1065\\u1066\\u106E-\\u1070\\u1075-\\u1081\\u108E\\u10D0-\\u10FA\\u10FD-\\u1248\\u124A-\\u124D\\u1250-\\u1256\\u1258\\u125A-\\u125D\\u1260-\\u1288\\u128A-\\u128D\\u1290-\\u12B0\\u12B2-\\u12B5\\u12B8-\\u12BE\\u12C0\\u12C2-\\u12C5\\u12C8-\\u12D6\\u12D8-\\u1310\\u1312-\\u1315\\u1318-\\u135A\\u1380-\\u138F\\u13A0-\\u13F4\\u1401-\\u166C\\u166F-\\u167F\\u1681-\\u169A\\u16A0-\\u16EA\\u16F1-\\u16F8\\u1700-\\u170C\\u170E-\\u1711\\u1720-\\u1731\\u1740-\\u1751\\u1760-\\u176C\\u176E-\\u1770\\u1780-\\u17B3\\u17DC\\u1820-\\u1842\\u1844-\\u1877\\u1880-\\u18A8\\u18AA\\u18B0-\\u18F5\\u1900-\\u191E\\u1950-\\u196D\\u1970-\\u1974\\u1980-\\u19AB\\u19C1-\\u19C7\\u1A00-\\u1A16\\u1A20-\\u1A54\\u1B05-\\u1B33\\u1B45-\\u1B4B\\u1B83-\\u1BA0\\u1BAE\\u1BAF\\u1BBA-\\u1BE5\\u1C00-\\u1C23\\u1C4D-\\u1C4F\\u1C5A-\\u1C77\\u1CE9-\\u1CEC\\u1CEE-\\u1CF1\\u1CF5\\u1CF6\\u2135-\\u2138\\u2D30-\\u2D67\\u2D80-\\u2D96\\u2DA0-\\u2DA6\\u2DA8-\\u2DAE\\u2DB0-\\u2DB6\\u2DB8-\\u2DBE\\u2DC0-\\u2DC6\\u2DC8-\\u2DCE\\u2DD0-\\u2DD6\\u2DD8-\\u2DDE\\u3006\\u303C\\u3041-\\u3096\\u309F\\u30A1-\\u30FA\\u30FF\\u3105-\\u312D\\u3131-\\u318E\\u31A0-\\u31BA\\u31F0-\\u31FF\\u3400-\\u4DB5\\u4E00-\\u9FCC\\uA000-\\uA014\\uA016-\\uA48C\\uA4D0-\\uA4F7\\uA500-\\uA60B\\uA610-\\uA61F\\uA62A\\uA62B\\uA66E\\uA6A0-\\uA6E5\\uA7F7\\uA7FB-\\uA801\\uA803-\\uA805\\uA807-\\uA80A\\uA80C-\\uA822\\uA840-\\uA873\\uA882-\\uA8B3\\uA8F2-\\uA8F7\\uA8FB\\uA90A-\\uA925\\uA930-\\uA946\\uA960-\\uA97C\\uA984-\\uA9B2\\uA9E0-\\uA9E4\\uA9E7-\\uA9EF\\uA9FA-\\uA9FE\\uAA00-\\uAA28\\uAA40-\\uAA42\\uAA44-\\uAA4B\\uAA60-\\uAA6F\\uAA71-\\uAA76\\uAA7A\\uAA7E-\\uAAAF\\uAAB1\\uAAB5\\uAAB6\\uAAB9-\\uAABD\\uAAC0\\uAAC2\\uAADB\\uAADC\\uAAE0-\\uAAEA\\uAAF2\\uAB01-\\uAB06\\uAB09-\\uAB0E\\uAB11-\\uAB16\\uAB20-\\uAB26\\uAB28-\\uAB2E\\uABC0-\\uABE2\\uAC00-\\uD7A3\\uD7B0-\\uD7C6\\uD7CB-\\uD7FB\\uF900-\\uFA6D\\uFA70-\\uFAD9\\uFB1D\\uFB1F-\\uFB28\\uFB2A-\\uFB36\\uFB38-\\uFB3C\\uFB3E\\uFB40\\uFB41\\uFB43\\uFB44\\uFB46-\\uFBB1\\uFBD3-\\uFD3D\\uFD50-\\uFD8F\\uFD92-\\uFDC7\\uFDF0-\\uFDFB\\uFE70-\\uFE74\\uFE76-\\uFEFC\\uFF66-\\uFF6F\\uFF71-\\uFF9D\\uFFA0-\\uFFBE\\uFFC2-\\uFFC7\\uFFCA-\\uFFCF\\uFFD2-\\uFFD7\\uFFDA-\\uFFDC',
		'pN' => '0-9\\u00B2\\u00B3\\u00B9\\u00BC-\\u00BE\\u0660-\\u0669\\u06F0-\\u06F9\\u07C0-\\u07C9\\u0966-\\u096F\\u09E6-\\u09EF\\u09F4-\\u09F9\\u0A66-\\u0A6F\\u0AE6-\\u0AEF\\u0B66-\\u0B6F\\u0B72-\\u0B77\\u0BE6-\\u0BF2\\u0C66-\\u0C6F\\u0C78-\\u0C7E\\u0CE6-\\u0CEF\\u0D66-\\u0D75\\u0DE6-\\u0DEF\\u0E50-\\u0E59\\u0ED0-\\u0ED9\\u0F20-\\u0F33\\u1040-\\u1049\\u1090-\\u1099\\u1369-\\u137C\\u16EE-\\u16F0\\u17E0-\\u17E9\\u17F0-\\u17F9\\u1810-\\u1819\\u1946-\\u194F\\u19D0-\\u19DA\\u1A80-\\u1A89\\u1A90-\\u1A99\\u1B50-\\u1B59\\u1BB0-\\u1BB9\\u1C40-\\u1C49\\u1C50-\\u1C59\\u2070\\u2074-\\u2079\\u2080-\\u2089\\u2150-\\u2182\\u2185-\\u2189\\u2460-\\u249B\\u24EA-\\u24FF\\u2776-\\u2793\\u2CFD\\u3007\\u3021-\\u3029\\u3038-\\u303A\\u3192-\\u3195\\u3220-\\u3229\\u3248-\\u324F\\u3251-\\u325F\\u3280-\\u3289\\u32B1-\\u32BF\\uA620-\\uA629\\uA6E6-\\uA6EF\\uA830-\\uA835\\uA8D0-\\uA8D9\\uA900-\\uA909\\uA9D0-\\uA9D9\\uA9F0-\\uA9F9\\uAA50-\\uAA59\\uABF0-\\uABF9\\uFF10-\\uFF19',
		'pNd' => '0-9\\u0660-\\u0669\\u06F0-\\u06F9\\u07C0-\\u07C9\\u0966-\\u096F\\u09E6-\\u09EF\\u0A66-\\u0A6F\\u0AE6-\\u0AEF\\u0B66-\\u0B6F\\u0BE6-\\u0BEF\\u0C66-\\u0C6F\\u0CE6-\\u0CEF\\u0D66-\\u0D6F\\u0DE6-\\u0DEF\\u0E50-\\u0E59\\u0ED0-\\u0ED9\\u0F20-\\u0F29\\u1040-\\u1049\\u1090-\\u1099\\u17E0-\\u17E9\\u1810-\\u1819\\u1946-\\u194F\\u19D0-\\u19D9\\u1A80-\\u1A89\\u1A90-\\u1A99\\u1B50-\\u1B59\\u1BB0-\\u1BB9\\u1C40-\\u1C49\\u1C50-\\u1C59\\uA620-\\uA629\\uA8D0-\\uA8D9\\uA900-\\uA909\\uA9D0-\\uA9D9\\uA9F0-\\uA9F9\\uAA50-\\uAA59\\uABF0-\\uABF9\\uFF10-\\uFF19',
		'pNl' => '\\u16EE-\\u16F0\\u2160-\\u2182\\u2185-\\u2188\\u3007\\u3021-\\u3029\\u3038-\\u303A\\uA6E6-\\uA6EF',
		'pNo' => '\\u00B2\\u00B3\\u00B9\\u00BC-\\u00BE\\u09F4-\\u09F9\\u0B72-\\u0B77\\u0BF0-\\u0BF2\\u0C78-\\u0C7E\\u0D70-\\u0D75\\u0F2A-\\u0F33\\u1369-\\u137C\\u17F0-\\u17F9\\u19DA\\u2070\\u2074-\\u2079\\u2080-\\u2089\\u2150-\\u215F\\u2189\\u2460-\\u249B\\u24EA-\\u24FF\\u2776-\\u2793\\u2CFD\\u3192-\\u3195\\u3220-\\u3229\\u3248-\\u324F\\u3251-\\u325F\\u3280-\\u3289\\u32B1-\\u32BF\\uA830-\\uA835',
		'pP' => '\\!-#%-\\*,-/\\:;\\?@\\[-\\]_\\{\\}\\u00A1\\u00A7\\u00AB\\u00B6\\u00B7\\u00BB\\u00BF\\u037E\\u0387\\u055A-\\u055F\\u0589\\u058A\\u05BE\\u05C0\\u05C3\\u05C6\\u05F3\\u05F4\\u0609\\u060A\\u060C\\u060D\\u061B\\u061E\\u061F\\u066A-\\u066D\\u06D4\\u0700-\\u070D\\u07F7-\\u07F9\\u0830-\\u083E\\u085E\\u0964\\u0965\\u0970\\u0AF0\\u0DF4\\u0E4F\\u0E5A\\u0E5B\\u0F04-\\u0F12\\u0F14\\u0F3A-\\u0F3D\\u0F85\\u0FD0-\\u0FD4\\u0FD9\\u0FDA\\u104A-\\u104F\\u10FB\\u1360-\\u1368\\u1400\\u166D\\u166E\\u169B\\u169C\\u16EB-\\u16ED\\u1735\\u1736\\u17D4-\\u17D6\\u17D8-\\u17DA\\u1800-\\u180A\\u1944\\u1945\\u1A1E\\u1A1F\\u1AA0-\\u1AA6\\u1AA8-\\u1AAD\\u1B5A-\\u1B60\\u1BFC-\\u1BFF\\u1C3B-\\u1C3F\\u1C7E\\u1C7F\\u1CC0-\\u1CC7\\u1CD3\\u2010-\\u2027\\u2030-\\u2043\\u2045-\\u2051\\u2053-\\u205E\\u207D\\u207E\\u208D\\u208E\\u2308-\\u230B\\u2329\\u232A\\u2768-\\u2775\\u27C5\\u27C6\\u27E6-\\u27EF\\u2983-\\u2998\\u29D8-\\u29DB\\u29FC\\u29FD\\u2CF9-\\u2CFC\\u2CFE\\u2CFF\\u2D70\\u2E00-\\u2E2E\\u2E30-\\u2E42\\u3001-\\u3003\\u3008-\\u3011\\u3014-\\u301F\\u3030\\u303D\\u30A0\\u30FB\\uA4FE\\uA4FF\\uA60D-\\uA60F\\uA673\\uA67E\\uA6F2-\\uA6F7\\uA874-\\uA877\\uA8CE\\uA8CF\\uA8F8-\\uA8FA\\uA92E\\uA92F\\uA95F\\uA9C1-\\uA9CD\\uA9DE\\uA9DF\\uAA5C-\\uAA5F\\uAADE\\uAADF\\uAAF0\\uAAF1\\uABEB\\uFD3E\\uFD3F\\uFE10-\\uFE19\\uFE30-\\uFE52\\uFE54-\\uFE61\\uFE63\\uFE68\\uFE6A\\uFE6B\\uFF01-\\uFF03\\uFF05-\\uFF0A\\uFF0C-\\uFF0F\\uFF1A\\uFF1B\\uFF1F\\uFF20\\uFF3B-\\uFF3D\\uFF3F\\uFF5B\\uFF5D\\uFF5F-\\uFF65',
		'pPc' => '_\\u203F\\u2040\\u2054\\uFE33\\uFE34\\uFE4D-\\uFE4F\\uFF3F',
		'pPd' => '\\-\\u058A\\u05BE\\u1400\\u1806\\u2010-\\u2015\\u2E17\\u2E1A\\u2E3A\\u2E3B\\u2E40\\u301C\\u3030\\u30A0\\uFE31\\uFE32\\uFE58\\uFE63\\uFF0D',
		'pPe' => '\\)\\]\\}\\u0F3B\\u0F3D\\u169C\\u2046\\u207E\\u208E\\u2309\\u230B\\u232A\\u2769\\u276B\\u276D\\u276F\\u2771\\u2773\\u2775\\u27C6\\u27E7\\u27E9\\u27EB\\u27ED\\u27EF\\u2984\\u2986\\u2988\\u298A\\u298C\\u298E\\u2990\\u2992\\u2994\\u2996\\u2998\\u29D9\\u29DB\\u29FD\\u2E23\\u2E25\\u2E27\\u2E29\\u3009\\u300B\\u300D\\u300F\\u3011\\u3015\\u3017\\u3019\\u301B\\u301E\\u301F\\uFD3E\\uFE18\\uFE36\\uFE38\\uFE3A\\uFE3C\\uFE3E\\uFE40\\uFE42\\uFE44\\uFE48\\uFE5A\\uFE5C\\uFE5E\\uFF09\\uFF3D\\uFF5D\\uFF60\\uFF63',
		'pPf' => '\\u00BB\\u2019\\u201D\\u203A\\u2E03\\u2E05\\u2E0A\\u2E0D\\u2E1D\\u2E21',
		'pPi' => '\\u00AB\\u2018\\u201B\\u201C\\u201F\\u2039\\u2E02\\u2E04\\u2E09\\u2E0C\\u2E1C\\u2E20',
		'pPo' => '\\!-#%-\'\\*,\\./\\:;\\?@\\\\\\u00A1\\u00A7\\u00B6\\u00B7\\u00BF\\u037E\\u0387\\u055A-\\u055F\\u0589\\u05C0\\u05C3\\u05C6\\u05F3\\u05F4\\u0609\\u060A\\u060C\\u060D\\u061B\\u061E\\u061F\\u066A-\\u066D\\u06D4\\u0700-\\u070D\\u07F7-\\u07F9\\u0830-\\u083E\\u085E\\u0964\\u0965\\u0970\\u0AF0\\u0DF4\\u0E4F\\u0E5A\\u0E5B\\u0F04-\\u0F12\\u0F14\\u0F85\\u0FD0-\\u0FD4\\u0FD9\\u0FDA\\u104A-\\u104F\\u10FB\\u1360-\\u1368\\u166D\\u166E\\u16EB-\\u16ED\\u1735\\u1736\\u17D4-\\u17D6\\u17D8-\\u17DA\\u1800-\\u1805\\u1807-\\u180A\\u1944\\u1945\\u1A1E\\u1A1F\\u1AA0-\\u1AA6\\u1AA8-\\u1AAD\\u1B5A-\\u1B60\\u1BFC-\\u1BFF\\u1C3B-\\u1C3F\\u1C7E\\u1C7F\\u1CC0-\\u1CC7\\u1CD3\\u2016\\u2017\\u2020-\\u2027\\u2030-\\u2038\\u203B-\\u203E\\u2041-\\u2043\\u2047-\\u2051\\u2053\\u2055-\\u205E\\u2CF9-\\u2CFC\\u2CFE\\u2CFF\\u2D70\\u2E00\\u2E01\\u2E06-\\u2E08\\u2E0B\\u2E0E-\\u2E16\\u2E18\\u2E19\\u2E1B\\u2E1E\\u2E1F\\u2E2A-\\u2E2E\\u2E30-\\u2E39\\u2E3C-\\u2E3F\\u2E41\\u3001-\\u3003\\u303D\\u30FB\\uA4FE\\uA4FF\\uA60D-\\uA60F\\uA673\\uA67E\\uA6F2-\\uA6F7\\uA874-\\uA877\\uA8CE\\uA8CF\\uA8F8-\\uA8FA\\uA92E\\uA92F\\uA95F\\uA9C1-\\uA9CD\\uA9DE\\uA9DF\\uAA5C-\\uAA5F\\uAADE\\uAADF\\uAAF0\\uAAF1\\uABEB\\uFE10-\\uFE16\\uFE19\\uFE30\\uFE45\\uFE46\\uFE49-\\uFE4C\\uFE50-\\uFE52\\uFE54-\\uFE57\\uFE5F-\\uFE61\\uFE68\\uFE6A\\uFE6B\\uFF01-\\uFF03\\uFF05-\\uFF07\\uFF0A\\uFF0C\\uFF0E\\uFF0F\\uFF1A\\uFF1B\\uFF1F\\uFF20\\uFF3C\\uFF61\\uFF64\\uFF65',
		'pPs' => '\\(\\[\\{\\u0F3A\\u0F3C\\u169B\\u201A\\u201E\\u2045\\u207D\\u208D\\u2308\\u230A\\u2329\\u2768\\u276A\\u276C\\u276E\\u2770\\u2772\\u2774\\u27C5\\u27E6\\u27E8\\u27EA\\u27EC\\u27EE\\u2983\\u2985\\u2987\\u2989\\u298B\\u298D\\u298F\\u2991\\u2993\\u2995\\u2997\\u29D8\\u29DA\\u29FC\\u2E22\\u2E24\\u2E26\\u2E28\\u2E42\\u3008\\u300A\\u300C\\u300E\\u3010\\u3014\\u3016\\u3018\\u301A\\u301D\\uFD3F\\uFE17\\uFE35\\uFE37\\uFE39\\uFE3B\\uFE3D\\uFE3F\\uFE41\\uFE43\\uFE47\\uFE59\\uFE5B\\uFE5D\\uFF08\\uFF3B\\uFF5B\\uFF5F\\uFF62',
		'pS' => '\\$\\+\\<-\\>\\^`\\|~\\u00A2-\\u00A6\\u00A8\\u00A9\\u00AC\\u00AE-\\u00B1\\u00B4\\u00B8\\u00D7\\u00F7\\u02C2-\\u02C5\\u02D2-\\u02DF\\u02E5-\\u02EB\\u02ED\\u02EF-\\u02FF\\u0375\\u0384\\u0385\\u03F6\\u0482\\u058D-\\u058F\\u0606-\\u0608\\u060B\\u060E\\u060F\\u06DE\\u06E9\\u06FD\\u06FE\\u07F6\\u09F2\\u09F3\\u09FA\\u09FB\\u0AF1\\u0B70\\u0BF3-\\u0BFA\\u0C7F\\u0D79\\u0E3F\\u0F01-\\u0F03\\u0F13\\u0F15-\\u0F17\\u0F1A-\\u0F1F\\u0F34\\u0F36\\u0F38\\u0FBE-\\u0FC5\\u0FC7-\\u0FCC\\u0FCE\\u0FCF\\u0FD5-\\u0FD8\\u109E\\u109F\\u1390-\\u1399\\u17DB\\u1940\\u19DE-\\u19FF\\u1B61-\\u1B6A\\u1B74-\\u1B7C\\u1FBD\\u1FBF-\\u1FC1\\u1FCD-\\u1FCF\\u1FDD-\\u1FDF\\u1FED-\\u1FEF\\u1FFD\\u1FFE\\u2044\\u2052\\u207A-\\u207C\\u208A-\\u208C\\u20A0-\\u20BD\\u2100\\u2101\\u2103-\\u2106\\u2108\\u2109\\u2114\\u2116-\\u2118\\u211E-\\u2123\\u2125\\u2127\\u2129\\u212E\\u213A\\u213B\\u2140-\\u2144\\u214A-\\u214D\\u214F\\u2190-\\u2307\\u230C-\\u2328\\u232B-\\u23FA\\u2400-\\u2426\\u2440-\\u244A\\u249C-\\u24E9\\u2500-\\u2767\\u2794-\\u27C4\\u27C7-\\u27E5\\u27F0-\\u2982\\u2999-\\u29D7\\u29DC-\\u29FB\\u29FE-\\u2B73\\u2B76-\\u2B95\\u2B98-\\u2BB9\\u2BBD-\\u2BC8\\u2BCA-\\u2BD1\\u2CE5-\\u2CEA\\u2E80-\\u2E99\\u2E9B-\\u2EF3\\u2F00-\\u2FD5\\u2FF0-\\u2FFB\\u3004\\u3012\\u3013\\u3020\\u3036\\u3037\\u303E\\u303F\\u309B\\u309C\\u3190\\u3191\\u3196-\\u319F\\u31C0-\\u31E3\\u3200-\\u321E\\u322A-\\u3247\\u3250\\u3260-\\u327F\\u328A-\\u32B0\\u32C0-\\u32FE\\u3300-\\u33FF\\u4DC0-\\u4DFF\\uA490-\\uA4C6\\uA700-\\uA716\\uA720\\uA721\\uA789\\uA78A\\uA828-\\uA82B\\uA836-\\uA839\\uAA77-\\uAA79\\uAB5B\\uFB29\\uFBB2-\\uFBC1\\uFDFC\\uFDFD\\uFE62\\uFE64-\\uFE66\\uFE69\\uFF04\\uFF0B\\uFF1C-\\uFF1E\\uFF3E\\uFF40\\uFF5C\\uFF5E\\uFFE0-\\uFFE6\\uFFE8-\\uFFEE\\uFFFC\\uFFFD',
		'pSc' => '\\$\\u00A2-\\u00A5\\u058F\\u060B\\u09F2\\u09F3\\u09FB\\u0AF1\\u0BF9\\u0E3F\\u17DB\\u20A0-\\u20BD\\uA838\\uFDFC\\uFE69\\uFF04\\uFFE0\\uFFE1\\uFFE5\\uFFE6',
		'pSk' => '\\^`\\u00A8\\u00AF\\u00B4\\u00B8\\u02C2-\\u02C5\\u02D2-\\u02DF\\u02E5-\\u02EB\\u02ED\\u02EF-\\u02FF\\u0375\\u0384\\u0385\\u1FBD\\u1FBF-\\u1FC1\\u1FCD-\\u1FCF\\u1FDD-\\u1FDF\\u1FED-\\u1FEF\\u1FFD\\u1FFE\\u309B\\u309C\\uA700-\\uA716\\uA720\\uA721\\uA789\\uA78A\\uAB5B\\uFBB2-\\uFBC1\\uFF3E\\uFF40\\uFFE3',
		'pSm' => '\\+\\<-\\>\\|~\\u00AC\\u00B1\\u00D7\\u00F7\\u03F6\\u0606-\\u0608\\u2044\\u2052\\u207A-\\u207C\\u208A-\\u208C\\u2118\\u2140-\\u2144\\u214B\\u2190-\\u2194\\u219A\\u219B\\u21A0\\u21A3\\u21A6\\u21AE\\u21CE\\u21CF\\u21D2\\u21D4\\u21F4-\\u22FF\\u2320\\u2321\\u237C\\u239B-\\u23B3\\u23DC-\\u23E1\\u25B7\\u25C1\\u25F8-\\u25FF\\u266F\\u27C0-\\u27C4\\u27C7-\\u27E5\\u27F0-\\u27FF\\u2900-\\u2982\\u2999-\\u29D7\\u29DC-\\u29FB\\u29FE-\\u2AFF\\u2B30-\\u2B44\\u2B47-\\u2B4C\\uFB29\\uFE62\\uFE64-\\uFE66\\uFF0B\\uFF1C-\\uFF1E\\uFF5C\\uFF5E\\uFFE2\\uFFE9-\\uFFEC',
		'pSo' => '\\u00A6\\u00A9\\u00AE\\u00B0\\u0482\\u058D\\u058E\\u060E\\u060F\\u06DE\\u06E9\\u06FD\\u06FE\\u07F6\\u09FA\\u0B70\\u0BF3-\\u0BF8\\u0BFA\\u0C7F\\u0D79\\u0F01-\\u0F03\\u0F13\\u0F15-\\u0F17\\u0F1A-\\u0F1F\\u0F34\\u0F36\\u0F38\\u0FBE-\\u0FC5\\u0FC7-\\u0FCC\\u0FCE\\u0FCF\\u0FD5-\\u0FD8\\u109E\\u109F\\u1390-\\u1399\\u1940\\u19DE-\\u19FF\\u1B61-\\u1B6A\\u1B74-\\u1B7C\\u2100\\u2101\\u2103-\\u2106\\u2108\\u2109\\u2114\\u2116\\u2117\\u211E-\\u2123\\u2125\\u2127\\u2129\\u212E\\u213A\\u213B\\u214A\\u214C\\u214D\\u214F\\u2195-\\u2199\\u219C-\\u219F\\u21A1\\u21A2\\u21A4\\u21A5\\u21A7-\\u21AD\\u21AF-\\u21CD\\u21D0\\u21D1\\u21D3\\u21D5-\\u21F3\\u2300-\\u2307\\u230C-\\u231F\\u2322-\\u2328\\u232B-\\u237B\\u237D-\\u239A\\u23B4-\\u23DB\\u23E2-\\u23FA\\u2400-\\u2426\\u2440-\\u244A\\u249C-\\u24E9\\u2500-\\u25B6\\u25B8-\\u25C0\\u25C2-\\u25F7\\u2600-\\u266E\\u2670-\\u2767\\u2794-\\u27BF\\u2800-\\u28FF\\u2B00-\\u2B2F\\u2B45\\u2B46\\u2B4D-\\u2B73\\u2B76-\\u2B95\\u2B98-\\u2BB9\\u2BBD-\\u2BC8\\u2BCA-\\u2BD1\\u2CE5-\\u2CEA\\u2E80-\\u2E99\\u2E9B-\\u2EF3\\u2F00-\\u2FD5\\u2FF0-\\u2FFB\\u3004\\u3012\\u3013\\u3020\\u3036\\u3037\\u303E\\u303F\\u3190\\u3191\\u3196-\\u319F\\u31C0-\\u31E3\\u3200-\\u321E\\u322A-\\u3247\\u3250\\u3260-\\u327F\\u328A-\\u32B0\\u32C0-\\u32FE\\u3300-\\u33FF\\u4DC0-\\u4DFF\\uA490-\\uA4C6\\uA828-\\uA82B\\uA836\\uA837\\uA839\\uAA77-\\uAA79\\uFDFD\\uFFE4\\uFFE8\\uFFED\\uFFEE\\uFFFC\\uFFFD',
		'pZ' => ' \\u00A0\\u1680\\u2000-\\u200A\\u2028\\u2029\\u202F\\u205F\\u3000',
		'pZl' => '\\u2028',
		'pZp' => '\\u2029',
		'pZs' => ' \\u00A0\\u1680\\u2000-\\u200A\\u202F\\u205F\\u3000'
	];

}