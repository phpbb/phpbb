/**
* phpBB3 forum functions
*/

/**
* Find a member
*/
function find_username(url) {
	popup(url, 760, 570, '_usersearch');
	return false;
}

/**
* Window popup
*/
function popup(url, width, height, name) {
	if (!name) {
		name = '_popup';
	}

	window.open(url.replace(/&amp;/g, '&'), name, 'height=' + height + ',resizable=yes,scrollbars=yes, width=' + width);
	return false;
}

/**
* Jump to page
*/
function jumpto(item) {
	if (!item || !item.length) {
		item = $('a.pagination-trigger[data-lang-jump-page]');
		if (!item.length) {
			return;
		}
	}

	var jump_page = item.attr('data-lang-jump-page'),
		on_page = item.attr('data-on-page'),
		per_page = item.attr('data-per-page'),
		base_url = item.attr('data-base-url'),
		page = prompt(jump_page, on_page);

	if (page !== null && !isNaN(page) && page == Math.floor(page) && page > 0) {
		if (base_url.indexOf('?') === -1) {
			document.location.href = base_url + '?start=' + ((page - 1) * per_page);
		} else {
			document.location.href = base_url.replace(/&amp;/g, '&') + '&start=' + ((page - 1) * per_page);
		}
	}
}

/**
* Mark/unmark checklist
* id = ID of parent container, name = name prefix, state = state [true/false]
*/
function marklist(id, name, state) {
	jQuery('#' + id + ' input[type=checkbox][name]').each(function() {
		var $this = jQuery(this);
		if ($this.attr('name').substr(0, name.length) == name) {
			$this.prop('checked', state);
		}
	});
}

/**
* Resize viewable area for attached image or topic review panel (possibly others to come)
* e = element
*/
function viewableArea(e, itself) {
	if (!e) {
		return;
	}

	if (!itself) {
		e = e.parentNode;
	}

	if (!e.vaHeight) {
		// Store viewable area height before changing style to auto
		e.vaHeight = e.offsetHeight;
		e.vaMaxHeight = e.style.maxHeight;
		e.style.height = 'auto';
		e.style.maxHeight = 'none';
		e.style.overflow = 'visible';
	} else {
		// Restore viewable area height to the default
		e.style.height = e.vaHeight + 'px';
		e.style.overflow = 'auto';
		e.style.maxHeight = e.vaMaxHeight;
		e.vaHeight = false;
	}
}

/**
* Set display of page element
* s[-1,0,1] = hide,toggle display,show
* type = string: inline, block, inline-block or other CSS "display" type
*/
function dE(n, s, type) {
	if (!type) {
		type = 'block';
	}

	var e = document.getElementById(n);
	if (!s) {
		s = (e.style.display === '' || e.style.display === type) ? -1 : 1;
	}
	e.style.display = (s === 1) ? type : 'none';
}

/**
* Alternate display of subPanels
*/
jQuery(document).ready(function() {
	jQuery('.sub-panels').each(function() {

		var panels = [],
			childNodes = jQuery('a[data-subpanel]', this).each(function() {
				panels.push(this.getAttribute('data-subpanel'));
			}),
			show_panel = this.getAttribute('data-show-panel');

		if (panels.length) {
			subPanels(show_panel);
			childNodes.click(function () {
				subPanels(this.getAttribute('data-subpanel'));
				return false;
			});
		}

		function subPanels(p) {
			var i;

			if (typeof(p) === 'string') {
				show_panel = p;
			}

			for (i = 0; i < panels.length; i++) {
				jQuery('#' + panels[i]).css('display', panels[i] === show_panel ? 'block' : 'none');
				jQuery('#' + panels[i] + '-tab').toggleClass('activetab', panels[i] === show_panel);
			}
		}
	});
});

/**
* Call print preview
*/
function printPage() {
	if (is_ie) {
		printPreview();
	} else {
		window.print();
	}
}

/**
* Show/hide groups of blocks
* c = CSS style name
* e = checkbox element
* t = toggle dispay state (used to show 'grip-show' image in the profile block when hiding the profiles)
*/
function displayBlocks(c, e, t) {
	var s = (e.checked === true) ?  1 : -1;

	if (t) {
		s *= -1;
	}

	var divs = document.getElementsByTagName("DIV");

	for (var d = 0; d < divs.length; d++) {
		if (divs[d].className.indexOf(c) === 0) {
			divs[d].style.display = (s === 1) ? 'none' : 'block';
		}
	}
}

function selectCode(a) {
	// Get ID of code block
	var e = a.parentNode.parentNode.getElementsByTagName('CODE')[0];
	var s, r;

	// Not IE and IE9+
	if (window.getSelection) {
		s = window.getSelection();
		// Safari
		if (s.setBaseAndExtent) {
			s.setBaseAndExtent(e, 0, e, e.innerText.length - 1);
		}
		// Firefox and Opera
		else {
			// workaround for bug # 42885
			if (window.opera && e.innerHTML.substring(e.innerHTML.length - 4) === '<BR>') {
				e.innerHTML = e.innerHTML + '&nbsp;';
			}

			r = document.createRange();
			r.selectNodeContents(e);
			s.removeAllRanges();
			s.addRange(r);
		}
	}
	// Some older browsers
	else if (document.getSelection) {
		s = document.getSelection();
		r = document.createRange();
		r.selectNodeContents(e);
		s.removeAllRanges();
		s.addRange(r);
	}
	// IE
	else if (document.selection) {
		r = document.body.createTextRange();
		r.moveToElementText(e);
		r.select();
	}
}

/**
* Play quicktime file by determining it's width/height
* from the displayed rectangle area
*/
function play_qt_file(obj) {
	var rectangle = obj.GetRectangle();
	var width, height;

	if (rectangle) {
		rectangle = rectangle.split(',');
		var x1 = parseInt(rectangle[0], 10);
		var x2 = parseInt(rectangle[2], 10);
		var y1 = parseInt(rectangle[1], 10);
		var y2 = parseInt(rectangle[3], 10);

		width = (x1 < 0) ? (x1 * -1) + x2 : x2 - x1;
		height = (y1 < 0) ? (y1 * -1) + y2 : y2 - y1;
	} else {
		width = 200;
		height = 0;
	}

	obj.width = width;
	obj.height = height + 16;

	obj.SetControllerVisible(true);
	obj.Play();
}

var in_autocomplete = false;
var last_key_entered = '';

/**
* Check event key
*/
function phpbb_check_key(event) {
	// Keycode is array down or up?
	if (event.keyCode && (event.keyCode === 40 || event.keyCode === 38)) {
		in_autocomplete = true;
	}

	// Make sure we are not within an "autocompletion" field
	if (in_autocomplete) {
		// If return pressed and key changed we reset the autocompletion
		if (!last_key_entered || last_key_entered === event.which) {
			in_autocompletion = false;
			return true;
		}
	}

	// Keycode is not return, then return. ;)
	if (event.which !== 13) {
		last_key_entered = event.which;
		return true;
	}

	return false;
}

/**
* Apply onkeypress event for forcing default submit button on ENTER key press
* The jQuery snippet used is based on http://greatwebguy.com/programming/dom/default-html-button-submit-on-enter-with-jquery/
* The non-jQuery code is a mimick of the jQuery code ;)
*/
function apply_onkeypress_event() {
	jQuery('form input[type=text], form input[type=password]').on('keypress', function (e) {
		var default_button = jQuery(this).parents('form').find('input[type=submit].default-submit-action');

		if (!default_button || default_button.length <= 0) {
			return true;
		}

		if (phpbb_check_key(e)) {
			return true;
		}

		if ((e.which && e.which === 13) || (e.keyCode && e.keyCode === 13)) {
			default_button.click();
			return false;
		}

		return true;
	});
}

jQuery(document).ready(apply_onkeypress_event);

/**
* Run onload functions
*/
(function($) {
	$(document).ready(function() {
		// Focus forms
		$('form[data-focus]:first').each(function() {
			$('#' + this.getAttribute('data-focus')).focus();
		});

		// Reset avatar dimensions when changing URL or EMAIL
		$('input[data-reset-on-edit]').bind('keyup', function() {
			$(this.getAttribute('data-reset-on-edit')).val('');
		});

		// Pagination
		$('a.pagination-trigger').click(function() {
			jumpto($(this));
		});

		// Adjust HTML code for IE8 and older versions		
		var test = document.createElement('div'),
			oldBrowser = (typeof test.style.borderRadius == 'undefined');
		delete test;

		if (oldBrowser) {
			// Fix .linkslist.bulletin lists
			$('ul.linklist.bulletin li:first-child, ul.linklist.bulletin li.rightside:last-child').addClass('no-bulletin');
		}
	});
})(jQuery);
