/* global phpbb */
/* eslint camelcase: 0 */
/* eslint no-unused-vars: 0 */
/* eslint no-var:0 */

/**
* phpBB forum functions
*/

/**
* Find a member
*/
function find_username(url) {
	'use strict';

	popup(url, 760, 570, '_usersearch');
	return false;
}

/**
* Window popup
*/
function popup(url, width, height, name) {
	'use strict';

	if (!name) {
		name = '_popup';
	}

	window.open(url.replace(/&amp;/g, '&'), name, 'height=' + height + ',resizable=yes,scrollbars=yes, width=' + width);
	return false;
}

/**
* Jump to page
*/
function pageJump(item) {
	'use strict';

	var page = parseInt(item.val(), 10);
	var perPage = item.attr('data-per-page');
	var baseUrl = item.attr('data-base-url');
	var startName = item.attr('data-start-name');

	if (page !== null && !isNaN(page) && page === Math.floor(page) && page > 0) {
		if (baseUrl.indexOf('?') === -1) {
			document.location.href = baseUrl + '?' + startName + '=' + ((page - 1) * perPage);
		} else {
			document.location.href = baseUrl.replace(/&amp;/g, '&') + '&' + startName + '=' + ((page - 1) * perPage);
		}
	}
}

/**
* Mark/unmark checklist
* id = ID of parent container, name = name prefix, state = state [true/false]
*/
function marklist(id, name, state) {
	'use strict';

	jQuery('#' + id + ' input[type=checkbox][name]').each(function() {
		var $this = jQuery(this);
		if ($this.attr('name').substr(0, name.length) === name && !$this.prop('disabled')) {
			$this.prop('checked', state);
		}
	});
}

/**
* Resize viewable area for attached image or topic review panel (possibly others to come)
* e = element
*/
function viewableArea(e, itself) {
	'use strict';

	if (!e) {
		return;
	}

	if (!itself) {
		e = e.parentNode;
	}

	if (e.vaHeight) {
		// Restore viewable area height to the default
		e.style.height = e.vaHeight + 'px';
		e.style.overflow = 'auto';
		e.style.maxHeight = e.vaMaxHeight;
		e.vaHeight = false;
	} else {
		// Store viewable area height before changing style to auto
		e.vaHeight = e.offsetHeight;
		e.vaMaxHeight = e.style.maxHeight;
		e.style.height = 'auto';
		e.style.maxHeight = 'none';
		e.style.overflow = 'visible';
	}
}

/**
* Alternate display of subPanels
*/
jQuery($ => {
	'use strict';

	$('.sub-panels').each(function() {
		var $childNodes = $('a[data-subpanel]', this);
		var panels = $childNodes.map(function() {
			return this.getAttribute('data-subpanel');
		});
		var showPanel = this.getAttribute('data-show-panel');

		if (panels.length) {
			activateSubPanel(showPanel, panels);
			$childNodes.click(function() {
				activateSubPanel(this.getAttribute('data-subpanel'), panels);
				return false;
			});
		}
	});
});

/**
* Activate specific subPanel
*/
function activateSubPanel(p, panels) {
	'use strict';

	var i;
	var showPanel;

	if (typeof p === 'string') {
		showPanel = p;
	}

	$('input[name="show_panel"]').val(showPanel);

	if (typeof panels === 'undefined') {
		panels = jQuery('.sub-panels a[data-subpanel]').map(function() {
			return this.getAttribute('data-subpanel');
		});
	}

	for (i = 0; i < panels.length; i++) {
		jQuery('#' + panels[i]).css('display', panels[i] === showPanel ? 'block' : 'none');
		jQuery('#' + panels[i] + '-tab').toggleClass('activetab', panels[i] === showPanel);
	}
}

function selectCode(a) {
	'use strict';

	// Get ID of code block
	var e = a.parentNode.parentNode.getElementsByTagName('CODE')[0];
	var s;
	var r;

	// Not IE and IE9+
	if (window.getSelection) {
		s = window.getSelection();
		// Safari and Chrome
		if (s.setBaseAndExtent) {
			var l = (e.innerText.length > 1) ? e.innerText.length - 1 : 1;
			try {
				s.setBaseAndExtent(e, 0, e, l);
			} catch (error) {
				r = document.createRange();
				r.selectNodeContents(e);
				s.removeAllRanges();
				s.addRange(r);
			}
		} else {
			// Firefox and Opera
			// workaround for bug # 42885
			if (window.opera && e.innerHTML.substring(e.innerHTML.length - 4) === '<BR>') {
				e.innerHTML += '&nbsp;';
			}

			r = document.createRange();
			r.selectNodeContents(e);
			s.removeAllRanges();
			s.addRange(r);
		}
	} else if (document.getSelection) {
		// Some older browsers
		s = document.getSelection();
		r = document.createRange();
		r.selectNodeContents(e);
		s.removeAllRanges();
		s.addRange(r);
	} else if (document.selection) {
		// IE
		r = document.body.createTextRange();
		r.moveToElementText(e);
		r.select();
	}
}

var inAutocomplete = false;
var lastKeyEntered = '';

/**
* Check event key
*/
function phpbbCheckKey(event) {
	'use strict';

	// Keycode is array down or up?
	if (event.keyCode && (event.keyCode === 40 || event.keyCode === 38)) {
		inAutocomplete = true;
	}

	// Make sure we are not within an "autocompletion" field
	if (inAutocomplete) {
		// If return pressed and key changed we reset the autocompletion
		if (!lastKeyEntered || lastKeyEntered === event.which) {
			inAutocomplete = false;
			return true;
		}
	}

	// Keycode is not return, then return. ;)
	if (event.which !== 13) {
		lastKeyEntered = event.which;
		return true;
	}

	return false;
}

/**
* Apply onkeypress event for forcing default submit button on ENTER key press
*/
jQuery($ => {
	'use strict';

	$('form input[type=text], form input[type=password]').on('keypress', function(e) {
		var defaultButton = $(this).parents('form').find('input[type=submit].default-submit-action');

		if (!defaultButton || defaultButton.length <= 0) {
			return true;
		}

		if (phpbbCheckKey(e)) {
			return true;
		}

		if ((e.which && e.which === 13) || (e.keyCode && e.keyCode === 13)) {
			defaultButton.click();
			return false;
		}

		return true;
	});
});

/**
* Functions for user search popup
*/
function insertUser(formId, value) {
	'use strict';

	var $form = jQuery(formId);
	var formName = $form.attr('data-form-name');
	var fieldName = $form.attr('data-field-name');
	var item = opener.document.forms[formName][fieldName];

	if (item.value.length && item.type === 'textarea') {
		value = item.value + '\n' + value;
	}

	item.value = value;
}

function insert_marked_users(formId, users) {
	'use strict';

	$(users).filter(':checked').each(function() {
		insertUser(formId, this.value);
	});

	window.close();
}

function insert_single_user(formId, user) {
	'use strict';

	insertUser(formId, user);
	window.close();
}

/**
* Parse document block
*/
function parseDocument($container) {
	'use strict';

	var test = document.createElement('div');
	var oldBrowser = (typeof test.style.borderRadius === 'undefined');
	var $body = $('body');

	/**
	* Reset avatar dimensions when changing URL or EMAIL
	*/
	$container.find('input[data-reset-on-edit]').on('keyup', function() {
		$(this.getAttribute('data-reset-on-edit')).val('');
	});

	/**
	* Pagination
	*/
	$container.find('.pagination .page-jump-form :button').click(function() {
		var $input = $(this).siblings('input.inputbox');
		pageJump($input);
	});

	$container.find('.pagination .page-jump-form input.inputbox').on('keypress', function(event) {
		if (event.which === 13 || event.keyCode === 13) {
			event.preventDefault();
			pageJump($(this));
		}
	});

	$container.find('.pagination .dropdown-trigger').click(function() {
		var $dropdownContainer = $(this).parent();
		// Wait a little bit to make sure the dropdown has activated
		setTimeout(() => {
			if ($dropdownContainer.hasClass('dropdown-visible')) {
				$dropdownContainer.find('input.inputbox').focus();
			}
		}, 100);
	});

	/**
	* Resize navigation (breadcrumbs) block to keep all links on same line
	*/
	$container.find('.navlinks').each(function() {
		var $this = $(this);
		var $left = $this.children().not('.rightside');
		var $right = $this.children('.rightside');

		if ($left.length !== 1 || !$right.length) {
			return;
		}

		function resize() {
			var width = 0;
			var diff = $left.outerWidth(true) - $left.width();
			var minWidth = Math.max($this.width() / 3, 240);

			$right.each(function() {
				var $this = $(this);
				if ($this.is(':visible')) {
					width += $this.outerWidth(true);
				}
			});

			var maxWidth = $this.width() - width - diff;
			$left.css('max-width', Math.floor(Math.max(maxWidth, minWidth)) + 'px');
		}

		resize();
		$(window).resize(resize);
	});

	/**
	* Makes breadcrumbs responsive
	*/
	$container.find('.breadcrumbs:not([data-skip-responsive])').each(function() {
		var $this = $(this);
		var $links = $this.find('.crumb');
		var { length } = $links;
		var classes = [ 'wrapped-max', 'wrapped-wide', 'wrapped-medium', 'wrapped-small', 'wrapped-tiny' ];
		var classesLength = classes.length;
		var maxHeight = 0;
		var lastWidth = false;
		var wrapped = false;

		// Set tooltips
		$this.find('a').each(function() {
			var $link = $(this);
			$link.attr('title', $link.text());
		});

		// Function that checks breadcrumbs
		function check() {
			var height = $this.height();
			var width;

			// Test max-width set in code for .navlinks above
			width = parseInt($this.css('max-width'), 10);
			if (!width) {
				width = $body.width();
			}

			maxHeight = parseInt($this.css('line-height'), 10);
			$links.each(function() {
				if ($(this).height() > 0) {
					maxHeight = Math.max(maxHeight, $(this).outerHeight(true));
				}
			});

			if (height <= maxHeight) {
				if (!wrapped || lastWidth === false || lastWidth >= width) {
					return;
				}
			}

			lastWidth = width;

			if (wrapped) {
				$this.removeClass('wrapped').find('.crumb.wrapped').removeClass('wrapped ' + classes.join(' '));
				if ($this.height() <= maxHeight) {
					return;
				}
			}

			wrapped = true;
			$this.addClass('wrapped');
			if ($this.height() <= maxHeight) {
				return;
			}

			for (var i = 0; i < classesLength; i++) {
				for (var j = length - 1; j >= 0; j--) {
					$links.eq(j).addClass('wrapped ' + classes[i]);
					if ($this.height() <= maxHeight) {
						return;
					}
				}
			}
		}

		// Run function and set event
		check();
		$(window).resize(check);
	});

	/**
	* Do not run functions below for old browsers
	*/
	if (oldBrowser) {
		return;
	}

	/**
	* Adjust topiclist lists with check boxes
	*/
	$container.find('ul.topiclist dd.mark').siblings('dt').children('.list-inner').addClass('with-mark');

	/**
	* Appends contents of all extra columns to first column in
	* .topiclist lists for mobile devices. Copies contents as is.
	*
	* To add that functionality to .topiclist list simply add
	* responsive-show-all to list of classes
	*/
	$container.find('.topiclist.responsive-show-all > li > dl').each(function() {
		var $this = $(this);
		var $block = $this.find('dt .responsive-show:last-child');
		var first = true;

		// Create block that is visible only on mobile devices
		if ($block.length) {
			first = ($.trim($block.text()).length === 0);
		} else {
			$this.find('dt > .list-inner').append('<div class="responsive-show" style="display:none;" />');
			$block = $this.find('dt .responsive-show:last-child');
		}

		// Copy contents of each column
		$this.find('dd').not('.mark').each(function() {
			var column = $(this);
			var $children = column.children();
			var html = column.html();

			if ($children.length === 1 && $children.text() === column.text()) {
				html = $children.html();
			}

			$block.append((first ? '' : '<br>') + html);

			first = false;
		});
	});

	/**
	* Same as above, but prepends text from header to each
	* column before contents of that column.
	*
	* To add that functionality to .topiclist list simply add
	* responsive-show-columns to list of classes
	*/
	$container.find('.topiclist.responsive-show-columns').each(function() {
		var $list = $(this);
		var headers = [];
		var headersLength = 0;

		// Find all headers, get contents
		$list.prev('.topiclist').find('li.header dd').not('.mark').each(function() {
			headers.push($('<div>').text($(this).text()).html());
			headersLength++;
		});

		if (!headersLength) {
			return;
		}

		// Parse each row
		$list.find('dl').each(function() {
			var $this = $(this);
			var $block = $this.find('dt .responsive-show:last-child');
			var first = true;

			// Create block that is visible only on mobile devices
			if ($block.length) {
				first = ($.trim($block.text()).length === 0);
			} else {
				$this.find('dt > .list-inner').append('<div class="responsive-show" style="display:none;" />');
				$block = $this.find('dt .responsive-show:last-child');
			}

			// Copy contents of each column
			$this.find('dd').not('.mark').each(function(i) {
				var column = $(this);
				var children = column.children();
				var html = column.html();

				if (children.length === 1 && children.text() === column.text()) {
					html = children.html();
				}

				// Prepend contents of matching header before contents of column
				if (i < headersLength) {
					html = headers[i] + ': <strong>' + html + '</strong>';
				}

				$block.append((first ? '' : '<br>') + html);

				first = false;
			});
		});
	});

	/**
	* Responsive tables
	*/
	$container.find('table.table1').not('.not-responsive').each(function() {
		var $this = $(this);
		var $th = $this.find('thead > tr > th');
		var headers = [];
		var totalHeaders = 0;
		var i;

		// Find each header
		$th.each(function(column) {
			var cell = $(this);
			var colspan = parseInt(cell.attr('colspan'), 10);
			var dfn = cell.attr('data-dfn');
			var text = dfn ? dfn : cell.text();

			colspan = isNaN(colspan) || colspan < 1 ? 1 : colspan;

			for (i = 0; i < colspan; i++) {
				headers.push(text);
			}

			totalHeaders++;

			if (dfn && !column) {
				$this.addClass('show-header');
			}
		});

		var headersLength = headers.length;

		// Add header text to each cell as <dfn>
		$this.addClass('responsive');

		if (totalHeaders < 2) {
			$this.addClass('show-header');
			return;
		}

		$this.find('tbody > tr').each(function() {
			var row = $(this);
			var cells = row.children('td');
			var column = 0;

			if (cells.length === 1) {
				row.addClass('big-column');
				return;
			}

			cells.each(function() {
				var cell = $(this);
				var colspan = parseInt(cell.attr('colspan'), 10);
				var text = $.trim(cell.text());

				if (headersLength <= column) {
					return;
				}

				if ((text.length && text !== '-') || cell.children().length) {
					if (headers[column].length) {
						cell.prepend($('<dfn>').css('display', 'none').text(headers[column]));
					}
				} else {
					cell.addClass('empty');
				}

				colspan = isNaN(colspan) || colspan < 1 ? 1 : colspan;
				column += colspan;
			});
		});
	});

	/**
	* Hide empty responsive tables
	*/
	$container.find('table.responsive > tbody').not('.responsive-skip-empty').each(function() {
		var $items = $(this).children('tr');
		if (!$items.length) {
			$(this).parent('table:first').addClass('responsive-hide');
		}
	});

	/**
	* Responsive tabs
	*/
	$container.find('#tabs, #minitabs').not('[data-skip-responsive]').each(function() {
		var $this = $(this);
		var $ul = $this.children();
		var $tabs = $ul.children().not('[data-skip-responsive]');
		var $links = $tabs.children('a');
		var $item = $ul.append('<li class="tab responsive-tab" style="display:none;"><a href="javascript:void(0);" class="responsive-tab-link">&nbsp;</a><div class="dropdown tab-dropdown" style="display: none;"><div class="pointer"><div class="pointer-inner"></div></div><ul class="dropdown-contents" /></div></li>').find('li.responsive-tab');
		var $menu = $item.find('.dropdown-contents');
		var maxHeight = 0;
		var lastWidth = false;
		var responsive = false;

		$links.each(function() {
			var $this = $(this);
			maxHeight = Math.max(maxHeight, Math.max($this.outerHeight(true), $this.parent().outerHeight(true)));
		});

		function check() {
			var width = $body.width();
			var height = $this.height();

			if (!arguments.length && (!responsive || width <= lastWidth) && height <= maxHeight) {
				return;
			}

			$tabs.show();
			$item.hide();

			lastWidth = width;
			height = $this.height();
			if (height <= maxHeight) {
				if ($item.hasClass('dropdown-visible')) {
					phpbb.toggleDropdown.call($item.find('a.responsive-tab-link').get(0));
				}

				return;
			}

			responsive = true;
			$item.show();
			$menu.html('');

			var $availableTabs = $tabs.filter(':not(.activetab, .responsive-tab)');
			var total = $availableTabs.length;
			var i;
			var $tab;

			for (i = total - 1; i >= 0; i--) {
				$tab = $availableTabs.eq(i);
				$menu.prepend($tab.clone(true).removeClass('tab'));
				$tab.hide();
				if ($this.height() <= maxHeight) {
					$menu.find('a').click(() => {
						check(true);
					});
					return;
				}
			}

			$menu.find('a').click(() => {
				check(true);
			});
		}

		var $tabLink = $item.find('a.responsive-tab-link');
		phpbb.registerDropdown($tabLink, $item.find('.dropdown'), {
			visibleClass: 'activetab',
		});

		check(true);
		$(window).resize(check);
	});

	/**
	 * Hide UCP/MCP navigation if there is only 1 item
	 */
	$container.find('#navigation').each(function() {
		var $items = $(this).children('ol, ul').children('li');
		if ($items.length === 1) {
			$(this).addClass('responsive-hide');
		}
	});

	/**
	* Replace responsive text
	*/
	$container.find('[data-responsive-text]').each(function() {
		var $this = $(this);
		var fullText = $this.text();
		var responsiveText = $this.attr('data-responsive-text');
		var responsive = false;

		function check() {
			if ($(window).width() > 700) {
				if (!responsive) {
					return;
				}

				$this.text(fullText);
				responsive = false;
				return;
			}

			if (responsive) {
				return;
			}

			$this.text(responsiveText);
			responsive = true;
		}

		check();
		$(window).resize(check);
	});
}

/**
* Run onload functions
*/
jQuery($ => {
	'use strict';

	// Swap .nojs and .hasjs
	$('#phpbb.nojs').toggleClass('nojs hasjs');
	$('#phpbb').toggleClass('hastouch', phpbb.isTouch);
	$('#phpbb.hastouch').removeClass('notouch');

	// Focus forms
	$('form[data-focus]:first').each(function() {
		$('#' + this.getAttribute('data-focus')).focus();
	});

	parseDocument($('body'));
});
