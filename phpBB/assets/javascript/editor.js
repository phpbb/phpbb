
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








		}
		}
		}
		}
		}
	},
	}
}

