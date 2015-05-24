
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

		}






		}








		}
		}
		}
		}
		}
	},
	}
}

