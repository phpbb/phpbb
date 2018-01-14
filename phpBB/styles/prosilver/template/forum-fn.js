/* global phpbb, jQuery, $, window, document, opener */

/* eslint-disable complexity, camelcase, no-unused-vars */

/**
* phpBB3 forum functions
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

	const page = parseInt(item.val(), 10);
	const perPage = item.attr('data-per-page');
	const baseUrl = item.attr('data-base-url');
	const startName = item.attr('data-start-name');

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

	jQuery('#' + id + ' input[type=checkbox][name]').each(function () {
		const $this = jQuery(this);
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

	$('.sub-panels').each(function () {
		const $childNodes = $('a[data-subpanel]', this);
		const panels = $childNodes.map(function () {
			return this.getAttribute('data-subpanel');
		});
		const showPanel = this.getAttribute('data-show-panel');

		if (panels.length !== 0) {
			activateSubPanel(showPanel, panels);
			$childNodes.click(function () {
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

	let i;
	let showPanel;

	if (typeof p === 'string') {
		showPanel = p;
	}
	$('input[name="show_panel"]').val(showPanel);

	if (typeof panels === 'undefined') {
		panels = jQuery('.sub-panels a[data-subpanel]').map(function () {
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
	const e = a.parentNode.parentNode.getElementsByTagName('CODE')[0];
	let s;
	let r;

	// Not IE and IE9+
	if (window.getSelection) {
		s = window.getSelection();
		// Safari and Chrome
		if (s.setBaseAndExtent) {
			const l = (e.innerText.length > 1) ? e.innerText.length - 1 : 1;
			try {
				s.setBaseAndExtent(e, 0, e, l);
			} catch (err) {
				r = document.createRange();
				r.selectNodeContents(e);
				s.removeAllRanges();
				s.addRange(r);
			}
		} else { // Firefox and Opera
			// Workaround for bug # 42885
			if (window.opera && e.innerHTML.substring(e.innerHTML.length - 4) === '<BR>') {
				e.innerHTML += '&nbsp;';
			}

			r = document.createRange();
			r.selectNodeContents(e);
			s.removeAllRanges();
			s.addRange(r);
		}
	} else if (document.getSelection) { // Some older browsers
		s = document.getSelection();
		r = document.createRange();
		r.selectNodeContents(e);
		s.removeAllRanges();
		s.addRange(r);
	} else if (document.selection) { // IE
		r = document.body.createTextRange();
		r.moveToElementText(e);
		r.select();
	}
}

let inAutocomplete = false;
let lastKeyEntered = '';

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

	$('form input[type=text], form input[type=password]').on('keypress', function (e) {
		const defaultButton = $(this).parents('form').find('input[type=submit].default-submit-action');

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

	const $form = jQuery(formId);
	const formName = $form.attr('data-form-name');
	const fieldName = $form.attr('data-field-name');
	const item = opener.document.forms[formName][fieldName];

	if (item.value.length !== 0 && item.type === 'textarea') {
		value = item.value + '\n' + value;
	}

	item.value = value;
}

function insert_marked_users(formId, users) {
	'use strict';

	for (let i = 0; i < users.length; i++) {
		if (users[i].checked) {
			insertUser(formId, users[i].value);
		}
	}

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

	const test = document.createElement('div');
	const oldBrowser = (typeof test.style.borderRadius === 'undefined');
	const $body = $('body');

	/**
	* Reset avatar dimensions when changing URL or EMAIL
	*/
	$container.find('input[data-reset-on-edit]').on('keyup', function () {
		$(this.getAttribute('data-reset-on-edit')).val('');
	});

	/**
	* Pagination
	*/
	$container.find('.pagination .page-jump-form :button').click(function () {
		const $input = $(this).siblings('input.inputbox');
		pageJump($input);
	});

	$container.find('.pagination .page-jump-form input.inputbox').on('keypress', function (event) {
		if (event.which === 13 || event.keyCode === 13) {
			event.preventDefault();
			pageJump($(this));
		}
	});

	$container.find('.pagination .dropdown-trigger').click(function () {
		const $dropdownContainer = $(this).parent();
		// Wait a little bit to make sure the dropdown has activated
		setTimeout(() => {
			if ($dropdownContainer.hasClass('dropdown-visible')) {
				$dropdownContainer.find('input.inputbox').focus();
			}
		}, 100);
	});

	/**
	* Adjust HTML code for IE8 and older versions
	*/
	// if (oldBrowser) {
	// 	// Fix .linklist.bulletin lists
	// 	$container
	// 		.find('ul.linklist.bulletin > li')
	// 		.filter(':first-child, .rightside:last-child')
	// 		.addClass('no-bulletin');
	// }

	/**
	* Resize navigation (breadcrumbs) block to keep all links on same line
	*/
	$container.find('.navlinks').each(function () {
		const $this = $(this);
		const $left = $this.children().not('.rightside');
		const $right = $this.children('.rightside');

		if ($left.length !== 1 || $right.length === 0) {
			return;
		}

		function resize() {
			let width = 0;
			const diff = $left.outerWidth(true) - $left.width();
			const minWidth = Math.max($this.width() / 3, 240);

			$right.each(function () {
				const $this = $(this);
				if ($this.is(':visible')) {
					width += $this.outerWidth(true);
				}
			});

			const maxWidth = $this.width() - width - diff;
			$left.css('max-width', Math.floor(Math.max(maxWidth, minWidth)) + 'px');
		}

		resize();
		$(window).resize(resize);
	});

	/**
	* Makes breadcrumbs responsive
	*/
	$container.find('.breadcrumbs:not([data-skip-responsive])').each(function () {
		const $this = $(this);
		const $links = $this.find('.crumb');
		const length = $links.length;
		const classes = ['wrapped-max', 'wrapped-wide', 'wrapped-medium', 'wrapped-small', 'wrapped-tiny'];
		const classesLength = classes.length;
		let maxHeight = 0;
		let lastWidth = false;
		let wrapped = false;

		// Set tooltips
		$this.find('a').each(function () {
			const $link = $(this);
			$link.attr('title', $link.text());
		});

		// Function that checks breadcrumbs
		function check() {
			const height = $this.height();
			let width;

			// Test max-width set in code for .navlinks above
			width = parseInt($this.css('max-width'), 10);
			if (!width) {
				width = $body.width();
			}

			maxHeight = parseInt($this.css('line-height'), 10);
			$links.each(function () {
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

			for (let i = 0; i < classesLength; i++) {
				for (let j = length - 1; j >= 0; j--) {
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
	* Responsive link lists
	*/
	const selector = '.linklist:not(.navlinks, [data-skip-responsive]),' +
		'.postbody .post-buttons:not([data-skip-responsive])';
	$container.find(selector).each(function () {
		const $this = $(this);
		const filterSkip = '.breadcrumbs, [data-skip-responsive]';
		const filterLast = '.edit-icon, .quote-icon, [data-last-responsive]';
		const $linksAll = $this.children();
		const $linksNotSkip = $linksAll.not(filterSkip); // All items that can potentially be hidden
		const $linksFirst = $linksNotSkip.not(filterLast); // The items that will be hidden first
		const $linksLast = $linksNotSkip.filter(filterLast); // The items that will be hidden last
		const persistent = $this.attr('id') === 'nav-main'; // Does this list already have a menu (such as quick-links)?
		const html = '<li class="responsive-menu hidden"><a href="javascript:void(0);" class="js-responsive-menu-link responsive-menu-link"><i class="icon fa-bars fa-fw" aria-hidden="true"></i></a><div class="dropdown"><div class="pointer"><div class="pointer-inner" /></div><ul class="dropdown-contents" /></div></li>';
		const slack = 3; // Vertical slack space (in pixels). Determines how sensitive the script is in determining whether a line-break has occurred.

		// Add a hidden drop-down menu to each links list (except those that already have one)
		if (!persistent) {
			if ($linksNotSkip.is('.rightside')) {
				$linksNotSkip.filter('.rightside:first').before(html);
				$this.children('.responsive-menu').addClass('rightside');
			} else {
				$this.append(html);
			}
		}

		// Set some object references and initial states
		const $menu = $this.children('.responsive-menu');
		const $menuContents = $menu.find('.dropdown-contents');
		const persistentContent = $menuContents.find('li:not(.separator)').length;
		let lastWidth = false;
		let compact = false;
		let responsive1 = false;
		let responsive2 = false;
		let copied1 = false;
		let copied2 = false;
		let maxHeight = 0;

		// Find the tallest element in the list (we assume that all elements are roughly the same height)
		$linksAll.each(function () {
			if (!$(this).height()) {
				return;
			}
			maxHeight = Math.max(maxHeight, $(this).outerHeight(true));
		});
		if (maxHeight < 1) {
			return; // Shouldn't be possible, but just in case, abort
		}
		maxHeight += slack;

		function check() {
			const width = $body.width();
			// We can't make it any smaller than this, so just skip
			if (responsive2 && compact && (width <= lastWidth)) {
				return;
			}
			lastWidth = width;

			// Reset responsive and compact layout
			if (responsive1 || responsive2) {
				$linksNotSkip.removeClass('hidden');
				$menuContents.children('.clone').addClass('hidden');
				responsive1 = false;
				responsive2 = false;
			}
			if (compact) {
				$this.removeClass('compact');
				compact = false;
			}

			// Unhide the quick-links menu if it has "persistent" content
			if (persistent && persistentContent) {
				$menu.removeClass('hidden');
			} else {
				$menu.addClass('hidden');
			}

			// Nothing to resize if block's height is not bigger than tallest element's height
			if ($this.height() <= maxHeight) {
				return;
			}

			// STEP 1: Compact
			if (!compact) {
				$this.addClass('compact');
				compact = true;
			}
			if ($this.height() <= maxHeight) {
				return;
			}

			// STEP 2: First responsive set - compact
			if (compact) {
				$this.removeClass('compact');
				compact = false;
			}
			// Copy the list items to the dropdown
			if (!copied1) {
				const $clones1 = $linksFirst.clone();
				$menuContents.prepend($clones1.addClass('clone clone-first').removeClass('leftside rightside'));

				if ($this.hasClass('post-buttons')) {
					$('.button', $menuContents).removeClass('button');
					$('.sr-only', $menuContents).removeClass('sr-only');
					$('.js-responsive-menu-link').addClass('button').addClass('button-icon-only');
					$('.js-responsive-menu-link .icon').removeClass('fa-bars').addClass('fa-ellipsis-h');
				}
				copied1 = true;
			}
			if (!responsive1) {
				$linksFirst.addClass('hidden');
				responsive1 = true;
				$menuContents.children('.clone-first').removeClass('hidden');
				$menu.removeClass('hidden');
			}
			if ($this.height() <= maxHeight) {
				return;
			}

			// STEP 3: First responsive set + compact
			if (!compact) {
				$this.addClass('compact');
				compact = true;
			}
			if ($this.height() <= maxHeight) {
				return;
			}

			// STEP 4: Last responsive set - compact
			if ($linksLast.length === 0) {
				return; // No other links to hide, can't do more
			}
			if (compact) {
				$this.removeClass('compact');
				compact = false;
			}
			// Copy the list items to the dropdown
			if (!copied2) {
				const $clones2 = $linksLast.clone();
				$menuContents.prepend($clones2.addClass('clone clone-last').removeClass('leftside rightside'));
				copied2 = true;
			}
			if (!responsive2) {
				$linksLast.addClass('hidden');
				responsive2 = true;
				$menuContents.children('.clone-last').removeClass('hidden');
			}
			if ($this.height() <= maxHeight) {
				return;
			}

			// STEP 5: Last responsive set + compact
			if (!compact) {
				$this.addClass('compact');
				compact = true;
			}
		}

		if (!persistent) {
			phpbb.registerDropdown($menu.find('a.js-responsive-menu-link'), $menu.find('.dropdown'), false);
		}

		// If there are any images in the links list, run the check again after they have loaded
		$linksAll.find('img').each(function () {
			$(this).load(() => {
				check();
			});
		});

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
	$container.find('.topiclist.responsive-show-all > li > dl').each(function () {
		const $this = $(this);
		let $block = $this.find('dt .responsive-show:last-child');
		let first = true;

		// Create block that is visible only on mobile devices
		if ($block.length === 0) {
			$this.find('dt > .list-inner').append('<div class="responsive-show" style="display:none;" />');
			$block = $this.find('dt .responsive-show:last-child');
		} else {
			first = ($.trim($block.text()).length === 0);
		}

		// Copy contents of each column
		$this.find('dd').not('.mark').each(function () {
			const column = $(this);
			const $children = column.children();
			let html = column.html();

			if ($children.length === 1 && $children.text() === column.text()) {
				html = $children.html();
			}

			$block.append((first ? '' : '<br />') + html);

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
	$container.find('.topiclist.responsive-show-columns').each(function () {
		const $list = $(this);
		const headers = [];
		let headersLength = 0;

		// Find all headers, get contents
		$list.prev('.topiclist').find('li.header dd').not('.mark').each(function () {
			headers.push($(this).text());
			headersLength++;
		});

		if (!headersLength) {
			return;
		}

		// Parse each row
		$list.find('dl').each(function () {
			const $this = $(this);
			let $block = $this.find('dt .responsive-show:last-child');
			let first = true;

			// Create block that is visible only on mobile devices
			if ($block.length === 0) {
				$this.find('dt > .list-inner').append('<div class="responsive-show" style="display:none;" />');
				$block = $this.find('dt .responsive-show:last-child');
			} else {
				first = ($.trim($block.text()).length === 0);
			}

			// Copy contents of each column
			$this.find('dd').not('.mark').each(function (i) {
				const column = $(this);
				const children = column.children();
				let html = column.html();

				if (children.length === 1 && children.text() === column.text()) {
					html = children.html();
				}

				// Prepend contents of matching header before contents of column
				if (i < headersLength) {
					html = headers[i] + ': <strong>' + html + '</strong>';
				}

				$block.append((first ? '' : '<br />') + html);

				first = false;
			});
		});
	});

	/**
	* Responsive tables
	*/
	$container.find('table.table1').not('.not-responsive').each(function () {
		const $this = $(this);
		const $th = $this.find('thead > tr > th');
		const headers = [];
		let totalHeaders = 0;
		let i;

		// Find each header
		$th.each(function (column) {
			const cell = $(this);
			let colspan = parseInt(cell.attr('colspan'), 10);
			const dfn = cell.attr('data-dfn');
			const text = dfn ? dfn : cell.text();

			colspan = isNaN(colspan) || colspan < 1 ? 1 : colspan;

			for (i = 0; i < colspan; i++) {
				headers.push(text);
			}
			totalHeaders++;

			if (dfn && !column) {
				$this.addClass('show-header');
			}
		});

		const headersLength = headers.length;

		// Add header text to each cell as <dfn>
		$this.addClass('responsive');

		if (totalHeaders < 2) {
			$this.addClass('show-header');
			return;
		}

		$this.find('tbody > tr').each(function () {
			const row = $(this);
			const cells = row.children('td');
			let column = 0;

			if (cells.length === 1) {
				row.addClass('big-column');
				return;
			}

			cells.each(function () {
				const cell = $(this);
				let colspan = parseInt(cell.attr('colspan'), 10);
				const text = $.trim(cell.text());

				if (headersLength <= column) {
					return;
				}

				if ((text.length !== 0 && text !== '-') || cell.children().length !== 0) {
					cell.prepend('<dfn style="display: none;">' + headers[column] + '</dfn>');
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
	$container.find('table.responsive > tbody').not('.responsive-skip-empty').each(function () {
		const $items = $(this).children('tr');
		if ($items.length === 0) {
			$(this).parent('table:first').addClass('responsive-hide');
		}
	});

	/**
	* Responsive tabs
	*/
	$container.find('#tabs, #minitabs').not('[data-skip-responsive]').each(function () {
		const $this = $(this);
		const $ul = $this.children();
		const $tabs = $ul.children().not('[data-skip-responsive]');
		const $links = $tabs.children('a');
		const $item = $ul.append('<li class="tab responsive-tab" style="display:none;"><a href="javascript:void(0);" class="responsive-tab-link">&nbsp;</a><div class="dropdown tab-dropdown" style="display: none;"><div class="pointer"><div class="pointer-inner" /></div><ul class="dropdown-contents" /></div></li>').find('li.responsive-tab');
		const $menu = $item.find('.dropdown-contents');
		let maxHeight = 0;
		let lastWidth = false;
		let responsive = false;

		$links.each(function () {
			const $this = $(this);
			maxHeight = Math.max(maxHeight, Math.max($this.outerHeight(true), $this.parent().outerHeight(true)));
		});

		function check() {
			const width = $body.width();
			let height = $this.height();

			if (arguments.length === 0 && (!responsive || width <= lastWidth) && height <= maxHeight) {
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

			const $availableTabs = $tabs.filter(':not(.activetab, .responsive-tab)');
			const total = $availableTabs.length;
			let i;
			let $tab;

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

		const $tabLink = $item.find('a.responsive-tab-link');
		phpbb.registerDropdown($tabLink, $item.find('.dropdown'), {
			visibleClass: 'activetab'
		});

		check(true);
		$(window).resize(check);
	});

	/**
	 * Hide UCP/MCP navigation if there is only 1 item
	 */
	$container.find('#navigation').each(function () {
		const $items = $(this).children('ol, ul').children('li');
		if ($items.length === 1) {
			$(this).addClass('responsive-hide');
		}
	});

	/**
	* Replace responsive text
	*/
	$container.find('[data-responsive-text]').each(function () {
		const $this = $(this);
		const fullText = $this.text();
		const responsiveText = $this.attr('data-responsive-text');
		let responsive = false;

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
(function ($) {
	'use strict';

	// Swap .nojs and .hasjs
	$('#phpbb.nojs').toggleClass('nojs hasjs');
	$('#phpbb').toggleClass('hastouch', phpbb.isTouch);
	$('#phpbb.hastouch').removeClass('notouch');

	// Focus forms
	$('form[data-focus]:first').each(function () {
		$('#' + this.getAttribute('data-focus')).focus();
	});

	parseDocument($('body'));
})(jQuery);

/* eslint-enable complexity, camelcase, no-unused-vars */
