
/**
* Shows the help messages in the helpline window
*/
function helpline(help) {
	document.forms[form_name].helpbox.value = help_line[help];
}



// If there's no console or any of the used console methods, just make them noop as there's nothing I can do
window.console || (window.console = {});
console.log || (console.log = function (){});
console.info || (console.info = console.log);
console.warn || (console.warn = console.log);
console.error || (console.error = console.log);

var editorConstants = {
	'NO_VALUE' 			: -1,
	'VALUE_IN_CONTENT' 	: -2,

};

var editor = {

	tokenRegex: {
		'ALPHANUM': /^[0-9A-Za-z]+$/,
		'SIMPLETEXT': /^[a-z0-9,.\-+_]+$/i,
		'IDENTIFIER': /^[a-z0-9-_]+$/i,
		'INTTEXT': /^[a-zA-Z\u00C0-\u017F]+,\s[a-zA-Z\u00C0-\u017F]+$/,
		'NUMBER': /^[0-9]+$/,
		'INTEGER': /^(?:0|-?[1-9]\d*)$/,

		'EMAIL': /[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[A-Z]{2}|com|org|net|edu|gov|mil|me|biz|info|mobi|name|aero|asia|jobs|museum)\b/,

		'URL': /^(?:(?:https?|ftps?):\/\/)?(?:(?:[a-z]+@)?(([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})|((?:[A-F0-9]{1,4}:){7}[A-F0-9]{1,4})|((?:[A-F0-9]{1,4}:){1,4}:(?:[A-F0-9]{1,4}:){0,4}[A-F0-9]{1,4})|(?:(?:(?:[a-z0-9-]|%\d\d)+\.)+[a-z]{2,7})|localhost))?(?:\/([a-z0-9-\/.]*))?(?:\?((?:[^=]+=[^&]+&)*(?:[^=]+=[^#]+)?))?(?:#.*)?$/im,
		'LOCAL_URL': /^(?:\/([a-z0-9-\/.]*))?(?:\?((?:[^=]+=[^&]+&)*(?:[^=]+=[^#$]+)?))?(?:#[^$]*)?$/,
		'RELATIVE_URL': /^(?:\/([a-z0-9-\/.]*))?(?:\?((?:[^=]+=[^&]+&)*(?:[^=]+=[^#$]+)?))?(?:#[^$]*)?$/,

		'COLOR': /^(?:#[0-9a-f]{3,6}|rgb\(\d{1,3}, *\d{1,3}, *\d{1,3}\)|aqua|black|blue|fuchsia|gray|green|lime|maroon|navy|olive|orange|purple|red|silver|teal|white|yellow)$/i
	},

	getElementDefaultDisplay: function () {
		if(window.getComputedStyle){
			return function (tagName){
				var tag = document.createElement(tagName);
				document.body.appendChild(tag);
				var cStyle = window.getComputedStyle(tag, "").display;
				document.body.removeChild(tag);
				return cStyle;
			}
		}else{
			return function (tagName){
				var tag = document.createElement(tagName);
				document.body.appendChild(tag);
				var cStyle = tag.currentStyle.display;
				document.body.removeChild(tag);
				return cStyle;
			}
		}
	}(),

	paramFilters: {
		filterUrl: function (url){
			return tokenRegexTranslator.URL.test(url);
		},

		filterHashmap: function(attrValue, map, strict){
			if (attrValue in map){
				return map[attrValue];
			}

			return (strict) ? false : attrValue;
		},

		filterIdentifier: function(attrValue){
			return editor.tokenRegex.IDENTIFIER.test(attrValue) ? attrValue : false;
		},

		filterInt: function(attrValue){
			return editor.tokenRegex.INTEGER.test(attrValue) ? attrValue : false;
		},

		filterUrl: function(attrValue){
			return editor.tokenRegex.URL.test(attrValue) ? attrValue : false;
		},

		filterIp: function(attrValue){
			return filterURL(attrValue);
		},
		filterIpv4: function(attrValue){
			return filterURL(attrValue);
		},
		filterIpv6: function(attrValue){
			return filterURL(attrValue);
		},

		filterNumber: function(attrValue){
			return editor.tokenRegex.NUMBER.test(attrValue) ? attrValue : false;
		},


		filterRange: function(attrValue, min, max){
			if (!editor.tokenRegex.INTEGER.test(attrValue)){
				return false;
			}

			attrValue = parseInt(attrValue, 10);

			if (attrValue < min){
				console.info('Value ' + attrValue + ' out of range. Value raised to ' + min + ' (min value).');
				return min;
			}

			if (attrValue > max){
				console.info('Value ' + attrValue + ' out of range. Value lowered to ' + max + ' (max value).');
				return max;
			}

			return attrValue;
		},

		filterRegexp: function(attrValue, regexp){
			return regexp.test(attrValue) ? attrValue : false;
		},

		filterSimpletext: function(attrValue){
			return /^[-\w+., ]+$/.test(attrValue) ? attrValue : false;
		},

		filterUint: function(attrValue){
			return /^(?:0|[1-9]\d*)$/.test(attrValue) ? attrValue : false;
		}
	},

	/*
	 * A port in javascript of the PHP functions textFormatter allows.
	 *
	 * @source s9e/TextFormatter/src/Configurator/JavaScript/functions
	 */
	phpFuncFilters: {
		addslashes: function(str){
			return str.replace(/["'\\]/g, '\\$&').replace(/\u0000/g, '\\0');
		},

		dechex: function(str){
			return parseInt(str).toString(16);
		},

		intval: function(str){
			return parseInt(str) || 0;
		},

		ltrim: function(str){
			return str.replace(/^[ \n\r\t\0\x0B]+/g, '');
		},

		mb_strtolower: function(str){
			return str.toLowerCase();
		},

		mb_strtoupper: function(str){
			return str.toUpperCase();
		},

		mt_rand: function(min, max){
			return (min + Math.floor(Math.random() * (max + 1 - min)));
		},

		rawurlencode: function(str){
			return encodeURIComponent(str).replace(
				/[!'()*]/g,
				/**
				* @param {!string} c
				*/
				function(c){
					return '%' + c.charCodeAt(0).toString(16).toUpperCase();
				}
			);
		},

		rtrim: function(str){
			return str.replace(/[ \n\r\t\0\x0B]+$/g, '');
		},

		str_rot13: function(str){
			return str.replace(
				/[a-z]/gi,
				function(c){
					return String.fromCharCode(c.charCodeAt(0) + ((c.toLowerCase() < 'n') ? 13 : -13));
				}
			);
		},

		stripslashes: function(str){
			// NOTE: this will not correctly transform \0 into a NULL byte. I consider this a feature
			//       rather than a bug. There's no reason to use NULL bytes in a text.
			return str.replace(/\\([\s\S]?)/g, '\\1');
		},

		strrev: function(str){
			return str.split('').reverse().join('');
		},

		strtolower: function(str){
			return str.toLowerCase();
		},

		strtotime: function(str){
			return Date.parse(str) / 1000;
		},

		strtoupper: function(str){
			return str.toUpperCase();
		},

		trim: function(str){
			return str.replace(/^[ \n\r\t\0\x0B]+/g, '').replace(/[ \n\r\t\0\x0B]+$/g, '');
		},

		ucfirst: function(str){
			return str.charAt(0).toUpperCase() + str.substr(1);
		},

		ucwords: function(str){
			return str.replace(
				/(?:^|\s)[a-z]/g,
				function(m){
					return m.toUpperCase()
				}
			);
		},

		urlencode: function(str){
			return encodeURIComponent(str);
		}

	},

	/**
	* Given a name, an object with the attributes and a content, 
	* this returns a string with the BBCode definition of the content.
	* NOTE: No escapes are made to the content. All data is used as-is.
	*
	* @param name string The name of the BBCode tag
	* @param attributes object An object with key-value data for the BBCode attributes
	* @param string content The content of the BBCode tag
	* @param boolean isSelfClosing Set to true for not return with a closing tag
	* @return string The BBCode tag given the above parameters
	*/
	revertBackToBBCode: function (name, attributes, content, isSelfClosing){
		var attributeStr = ' ';
		if(attributes.defaultattr){
			attributeStr = '="' + attributes.defaultattr + '" ';
			delete attributes.defaultattr;
		}

		for(attributeName in attributes){
			attributeStr += attributeName + '="' + attributes[attributeName] + '" ';
		}

		if(attributeStr === ' '){
			attributeStr = '';
		}

		return '[' + name + attributeStr + ']' + content + 
				(isSelfClosing ? '[/' + name + ']' : '');
	},


	/**
	 * @return A javascript object that allows adding parameters and
	 * @source Based on s9e\TextFormatter\render.js
	*/
	xslt: function (xsl){
		// older IE has its own way of doing it
		var standardsBrowser = (typeof DOMParser !== 'undefined' && typeof XSLTProcessor !== 'undefined');
		if (standardsBrowser) {
			var xslDoc = (new DOMParser).parseFromString(xsl, 'text/xml');

			var processor = new XSLTProcessor();
			processor.importStylesheet(xslDoc);

			return {
				'setParameter': function (name, value){
					if(!value){
						if(value === ''){
							value = ' ';
						}else{
							value = '';
						}
					}
					processor.setParameter(null, name, value);
				},

				'transformToFragment' : function (xml, onDocument){
					var xmlDoc = (new DOMParser).parseFromString(xml, 'text/xml');
					// NOTE: importNode() is used because of https://code.google.com/p/chromium/issues/detail?id=266305
					return onDocument.importNode(processor.transformToFragment(xmlDoc, onDocument), true);
				}
			};
		}else{
			var ieStylesheet = new ActiveXObject('MSXML2.FreeThreadedDOMDocument.6.0');
			ieStylesheet.async = false;
			ieStylesheet.validateOnParse = false;
			ieStylesheet.loadXML(xsl);

			var ieGenerator = new ActiveXObject("MSXML2.XSLTemplate.6.0");
			ieGenerator.stylesheet = ieStylesheet;
			var ieTransformer = ieGenerator.createProcessor();

			return {
				'setParameter' : function (name, value){
					if(!value){
						if(value === ''){
							value = ' ';
						}else{
							value = '';
						}
					}
					ieTransformer.addParameter(name, value, '');
				},

				'transformToFragment' : function (xml, onDocument){
					var div = onDocument.createElement('div'),
						fragment = onDocument.createDocumentFragment();

					var ieTargetStylesheet = new ActiveXObject('MSXML2.FreeThreadedDOMDocument.6.0');
					ieTargetStylesheet.async = false;
					ieTargetStylesheet.validateOnParse = false;
					ieTargetStylesheet.loadXML(xml);

					ieTransformer.input = ieTargetStylesheet
					ieTransformer.transform();

					div.innerHTML = ieTransformer.output;
					while (div.firstChild){
						fragment.appendChild(div.removeChild(div.firstChild));
					}

					return fragment;
				}
			};
		}
	},

	/**
	 * Insert HTML in the editor at the selection point
	 *
	 */
	insertHTML: function (editor, start, end){
		alert("Misconfigured editor. The editor setup code does not override editor.insertHTML");
	},
	/**
	 * Insert BBCode in the editor at the selection point
	 * all HTML is considered literal.
	 * This is the preferred way of adding text to the editor from an external source.
	 *
	 */
	insertBBCode: function (editor, start, end){
		alert("Misconfigured editor. The editor setup code does not override editor.insertBBCode");
	},
	/**
	 * Insert text in the editor at the selection point.
	 * A best-effort is made to make sure all text has no meaning but all BBCode may be translated to HTML in the server.
	 *
	 */
	insertUnformatted: function (editor, start, end){
		alert("Misconfigured editor. The editor setup code does not override editor.insertUnformatted");
	}
	/**
	 * Get current text from the editor
	 * A best-effort is made to make sure all HTML is translated to BBCode. The remaining content is HTML-escaped.
	 */
	getValue: function (editor, start, end){
		alert("Misconfigured editor. The editor setup code does not override editor.getValue");
	}
}

