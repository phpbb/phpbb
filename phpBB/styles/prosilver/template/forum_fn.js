/* global phpbb */

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

	var page = item.val(),
		perPage = item.attr('data-per-page'),
		baseUrl = item.attr('data-base-url'),
		startName = item.attr('data-start-name');

	if (page !== null && !isNaN(page) && page == Math.floor(page) && page > 0) {
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
		if ($this.attr('name').substr(0, name.length) === name) {
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
* Alternate display of subPanels
*/
jQuery(function($) {
	'use strict';

	$('.sub-panels').each(function() {

		var $childNodes = $('a[data-subpanel]', this),
			panels = $childNodes.map(function () {
				return this.getAttribute('data-subpanel');
			}),
			showPanel = this.getAttribute('data-show-panel');

		if (panels.length) {
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

	var i, showPanel;

	if (typeof(p) === 'string') {
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
	var s, r;

	// Not IE and IE9+
	if (window.getSelection) {
		s = window.getSelection();
		// Safari and Chrome
		if (s.setBaseAndExtent) {
			var l = (e.innerText.length > 1) ? e.innerText.length - 1 : 1;
			s.setBaseAndExtent(e, 0, e, l);
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
	'use strict';

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
jQuery(function($) {
	'use strict';

	$('form input[type=text], form input[type=password]').on('keypress', function (e) {
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
function insert_user(formId, value)
{
	var form = jQuery(formId),
		formName = form.attr('data-form-name'),
		fieldName = form.attr('data-field-name'),
		item = opener.document.forms[formName][fieldName];

	if (item.value.length && item.type == 'textarea') {
		value = item.value + '\n' + value;
	}

	item.value = value;
}

function insert_marked_users(formId, users)
{
	if (typeof(users.length) == "undefined")
	{
		if (users.checked)
		{
			insert_user(formId, users.value);
		}
	}
	else if (users.length > 0)
	{
		for (i = 0; i < users.length; i++)
		{
			if (users[i].checked)
			{
				insert_user(formId, users[i].value);
			}
		}
	}

	self.close();
}

function insert_single_user(formId, user)
{
	insertUser(formId, user);
	self.close();
}

/**
* Parse document block
*/
function parseDocument($container) {
	'use strict';

	var test = document.createElement('div'),
		oldBrowser = (typeof test.style.borderRadius == 'undefined');

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
		setTimeout(function() { 
			if ($dropdownContainer.hasClass('dropdown-visible')) {
				$dropdownContainer.find('input.inputbox').focus();
			}
		}, 100);
	});

	/**
	* Adjust HTML code for IE8 and older versions		
	*/
	if (oldBrowser) {
		// Fix .linklist.bulletin lists
		$container.find('ul.linklist.bulletin > li:first-child, ul.linklist.bulletin > li.rightside:last-child').addClass('no-bulletin');
	}

	/**
	* Resize navigation block to keep all links on same line
	*/
	$container.find('.navlinks').each(function() {
		var $this = $(this),
			$left = $this.children().not('.rightside'),
			$right = $this.children('.rightside');

		if ($left.length !== 1 || !$right.length) {
			return;
		}

		function resize() {
			var width = 0,
				diff = $left.outerWidth(true) - $left.width();

			$right.each(function() {
				width += $(this).outerWidth(true);
			});
			$left.css('max-width', Math.floor($this.width() - width - diff) + 'px');
		}

		resize();
		$(window).resize(resize);
	});

	/**
	* Makes breadcrumbs responsive
	*/
	$container.find('.breadcrumbs:not([data-skip-responsive])').each(function() {
		var $this = $(this),
			$body = $('body'),
			$links = $this.find('.crumb'),
			length = $links.length,
			classes = ['wrapped-max', 'wrapped-wide', 'wrapped-medium', 'wrapped-small', 'wrapped-tiny'],
			classesLength = classes.length,
			maxHeight = 0,
			lastWidth = false,
			wrapped = false;

		// Set tooltips
		$this.find('a').each(function() {
			var $link = $(this);
			$link.attr('title', $link.text());
		});

		// Function that checks breadcrumbs
		function check() {
			var height = $this.height(),
				width = $body.width();

			maxHeight = parseInt($this.css('line-height'));
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

			for (var i = 0; i < classesLength; i ++) {
				for (var j = length - 1; j >= 0; j --) {
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
	$container.find('.linklist:not(.navlinks, [data-skip-responsive]), .postbody .post-buttons:not([data-skip-responsive])').each(function() {
		var $this = $(this),
			$body = $('body'),
			filterSkip = '.breadcrumbs, [data-skip-responsive]',
			filterLast = '.edit-icon, .quote-icon, [data-last-responsive]',
			persist = $this.attr('id') == 'nav-main',
			$allLinks = $this.children(),
			$links = $allLinks.not(filterSkip),
			html = '<li class="responsive-menu" style="display:none;"><a href="javascript:void(0);" class="responsive-menu-link">&nbsp;</a><div class="dropdown" style="display:none;"><div class="pointer"><div class="pointer-inner" /></div><ul class="dropdown-contents" /></div></li>',
			$filterLastList = $links.filter(filterLast),
			slack = 1; // Vertical slack space (in pixels). Determines how sensitive the script is in determining whether a line-break has occured. 

		if (!persist) {
			if ($links.is('.rightside')) {
				$links.filter('.rightside:first').before(html);
				$this.children('.responsive-menu').addClass('rightside');
			} else {
				$this.append(html);
			}
		}

		var $item = $this.children('.responsive-menu'),
			$menu = $item.find('.dropdown-contents'),
			lastWidth = false,
			compact = false,
			responsive = false,
			copied = false;

		function check() {
			var width = $body.width();
			if (responsive && width <= lastWidth) {
				return;
			}

			// Unhide the quick-links menu if it has content
			if (persist) {
				$item.addClass('hidden');
				if ($menu.find('li:not(.separator, .clone)').length || (responsive && $menu.find('li.clone').length)) {
					$item.removeClass('hidden');
				}
			}

			// Reset responsive and compact layout
			if (responsive) {
				$this.removeClass('responsive');
				$links.css('display', '');
				if (!persist) {
					$item.css('display', 'none');
				}
			}

			if (compact) {
				$this.removeClass('compact');
			}

			// Find tallest element
			var maxHeight = 0;
			$allLinks.each(function() {
				if (!$(this).height()) {
					return;
				}
				maxHeight = Math.max(maxHeight, $(this).outerHeight(true));
			});

			if (maxHeight < 1) {
				return;
			}

			// Nothing to resize if block's height is not bigger than tallest element's height
			if ($this.height() <= (maxHeight + slack)) {
				return;
			}

			// Enable compact layout, find tallest element, compare to height of whole block
			$this.addClass('compact');

			var compactMaxHeight = 0;
			$allLinks.each(function() {
				if (!$(this).height()) {
					return;
				}
				compactMaxHeight = Math.max(compactMaxHeight, $(this).outerHeight(true));
			});

			if ($this.height() <= (maxHeight + slack)) {
				return;
			}

			// Compact layout did not resize block enough, switch to responsive layout
			$this.removeClass('compact');
			responsive = true;

			if (!copied) {
				var clone = $links.clone(true);
				clone.filter('.rightside').each(function() {
					if (persist) {
						$(this).addClass('clone');
					}
					$menu.prepend(this);
				});
				
				if (persist) {
					$menu.prepend(clone.not('.rightside').addClass('clone'));
				} else {
					$menu.prepend(clone.not('.rightside'));
				}

				$menu.find('li.leftside, li.rightside').removeClass('leftside rightside');
				$menu.find('.inputbox').parents('li:first').css('white-space', 'normal');

				if ($this.hasClass('post-buttons')) {
					$('.button', $menu).removeClass('button icon-button');
					$('.responsive-menu-link', $item).addClass('button icon-button').prepend('<span></span>');
				}
				copied = true;
			} else {
				$menu.children().css('display', '');
			}

			$item.css('display', '');
			$this.addClass('responsive');

			// Try to not hide filtered items
			if ($filterLastList.length) {
				$links.not(filterLast).css('display', 'none');

				maxHeight = 0;
				$filterLastList.each(function() {
					if (!$(this).height()) return;
					maxHeight = Math.max(maxHeight, $(this).outerHeight(true));
				});

				if ($this.height() <= (maxHeight + slack)) {
					$menu.children().filter(filterLast).css('display', 'none');
					return;
				}
			}

			// If even responsive isn't enough, use both responsive and compact at same time
			compact = true;
			$this.addClass('compact');

			$links.css('display', 'none');
		}

		if (!persist) {
			phpbb.registerDropdown($item.find('a.responsive-menu-link'), $item.find('.dropdown'));
		}

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
		var $this = $(this),
			$block = $this.find('dt .responsive-show:last-child'),
			first = true;

		// Create block that is visible only on mobile devices
		if (!$block.length) {
			$this.find('dt > .list-inner').append('<div class="responsive-show" style="display:none;" />');
			$block = $this.find('dt .responsive-show:last-child');
		} else {
			first = ($.trim($block.text()).length === 0);
		}

		// Copy contents of each column
		$this.find('dd').not('.mark').each(function() {
			var column = $(this),
				$children = column.children(),
				html = column.html();

			if ($children.length == 1 && $children.text() == column.text()) {
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
	$container.find('.topiclist.responsive-show-columns').each(function() {
		var $list = $(this),
			headers = [],
			headersLength = 0;

		// Find all headers, get contents
		$list.prev('.topiclist').find('li.header dd').not('.mark').each(function() {
			headers.push($(this).text());
			headersLength++;
		});

		if (!headersLength) {
			return;
		}

		// Parse each row
		$list.find('dl').each(function() {
			var $this = $(this),
				$block = $this.find('dt .responsive-show:last-child'),
				first = true;

			// Create block that is visible only on mobile devices
			if (!$block.length) {
				$this.find('dt > .list-inner').append('<div class="responsive-show" style="display:none;" />');
				$block = $this.find('dt .responsive-show:last-child');
			}
			else {
				first = ($.trim($block.text()).length === 0);
			}

			// Copy contents of each column
			$this.find('dd').not('.mark').each(function(i) {
				var column = $(this),
					children = column.children(),
					html = column.html();

				if (children.length == 1 && children.text() == column.text()) {
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
	$container.find('table.table1').not('.not-responsive').each(function() {
		var $this = $(this),
			$th = $this.find('thead > tr > th'),
			headers = [],
			totalHeaders = 0,
			i, headersLength;

		// Find each header
		$th.each(function(column) {
			var cell = $(this),
				colspan = parseInt(cell.attr('colspan')),
				dfn = cell.attr('data-dfn'),
				text = dfn ? dfn : cell.text();

			colspan = isNaN(colspan) || colspan < 1 ? 1 : colspan;

			for (i = 0; i < colspan; i++) {
				headers.push(text);
			}
			totalHeaders++;

			if (dfn && !column) {
				$this.addClass('show-header');
			}
		});
		
		headersLength = headers.length;

		// Add header text to each cell as <dfn>
		$this.addClass('responsive');

		if (totalHeaders < 2) {
			$this.addClass('show-header');
			return;
		}

		$this.find('tbody > tr').each(function() {
			var row = $(this),
				cells = row.children('td'),
				column = 0;

			if (cells.length == 1) {
				row.addClass('big-column');
				return;
			}

			cells.each(function() {
				var cell = $(this),
					colspan = parseInt(cell.attr('colspan')),
					text = $.trim(cell.text());

				if (headersLength <= column) {
					return;
				}

				if ((text.length && text !== '-') || cell.children().length) {
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
		var $this = $(this),
			$body = $('body'),
			$ul = $this.children(),
			$tabs = $ul.children().not('[data-skip-responsive]'),
			$links = $tabs.children('a'),
			$item = $ul.append('<li class="tab responsive-tab" style="display:none;"><a href="javascript:void(0);" class="responsive-tab-link">&nbsp;</a><div class="dropdown tab-dropdown" style="display: none;"><div class="pointer"><div class="pointer-inner" /></div><ul class="dropdown-contents" /></div></li>').find('li.responsive-tab'),
			$menu = $item.find('.dropdown-contents'),
			maxHeight = 0,
			lastWidth = false,
			responsive = false;

		$links.each(function() {
			var $this = $(this);
			maxHeight = Math.max(maxHeight, Math.max($this.outerHeight(true), $this.parent().outerHeight(true)));
		});

		function check() {
			var width = $body.width(),
				height = $this.height();

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

			var $availableTabs = $tabs.filter(':not(.activetab, .responsive-tab)'),
				total = $availableTabs.length,
				i, $tab;

			for (i = total - 1; i >= 0; i --) {
				$tab = $availableTabs.eq(i);
				$menu.prepend($tab.clone(true).removeClass('tab'));
				$tab.hide();
				if ($this.height() <= maxHeight) {
					$menu.find('a').click(function() { check(true); });
					return;
				}
			}
			$menu.find('a').click(function() { check(true); });
		}

		phpbb.registerDropdown($item.find('a.responsive-tab-link'), $item.find('.dropdown'), {visibleClass: 'activetab'});

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
		var $this = $(this),
			fullText = $this.text(),
			responsiveText = $this.attr('data-responsive-text'),
			responsive = false;

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
jQuery(function($) {
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
