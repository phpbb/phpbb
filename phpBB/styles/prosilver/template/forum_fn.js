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
		if (base_url.indexOf('%d') === -1) {
			if (base_url.indexOf('?') === -1) {
				document.location.href = base_url + '?start=' + ((page - 1) * per_page);
			} else {
				document.location.href = base_url.replace(/&amp;/g, '&') + '&start=' + ((page - 1) * per_page);
			}
		} else {
			document.location.href = base_url.replace('%d', page);
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
* Run MSN action
*/
function msn_action(action, address)
{
	// Does the browser support the MSNM object?
	var app = document.getElementById('objMessengerApp');

	if (!app || !app.MyStatus) {
		var lang = $('form[data-lang-im-msnm-browser]');
		if (lang.length) {
			alert(lang.attr('data-lang-im-msnm-browser'));
		}
		return false;
	}

	// Is MSNM connected?
	if (app.MyStatus == 1) {
		var lang = $('form[data-lang-im-msnm-connect]');
		if (lang.length) {
			alert(lang.attr('data-lang-im-msnm-connect'));
		}
		return false;
	}

	// Do stuff
	try {
		switch (action) {
			case 'add':
				app.AddContact(0, address);
				break;

			case 'im':
				app.InstantMessage(address);
				break;
		}
	}
	catch (e) {
		return;
	}
}

/**
* Add to your contact list
*/
function add_contact(address) 
{
	msn_action('add', address);
}

/**
* Write IM to contact
*/
function im_contact(address)
{
	msn_action('im', address);
}

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
		value = item.value + "\n" + value;
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
	insert_user(formId, user);
	self.close();
}

/**
* Parse document block
*/
function parse_document(container) 
{
	var test = document.createElement('div'),
		oldBrowser = (typeof test.style.borderRadius == 'undefined');

	delete test;

	/**
	* Reset avatar dimensions when changing URL or EMAIL
	*/
	container.find('input[data-reset-on-edit]').bind('keyup', function() {
		$(this.getAttribute('data-reset-on-edit')).val('');
	});

	/**
	* Pagination
	*/
	container.find('a.pagination-trigger').click(function() {
		jumpto($(this));
	});

	/**
	* Adjust HTML code for IE8 and older versions		
	*/
	if (oldBrowser) {
		// Fix .linklist.bulletin lists
		container.find('ul.linklist.bulletin li:first-child, ul.linklist.bulletin li.rightside:last-child').addClass('no-bulletin');

		// Do not run functions below for old browsers
		return;
	}

	/**
	* Resize navigation block to keep all links on same line
	*/
	container.find('.navlinks').each(function() {
		var $this = $(this),
			left = $this.children().not('.rightside'),
			right = $this.children('.rightside');

		if (left.length !== 1 || !right.length) return;

		function resize() {
			var width = 0,
				diff = left.outerWidth(true) - left.width();

			right.each(function() {
				width += $(this).outerWidth(true);
			});
			left.css('max-width', Math.floor($this.width() - width - diff) + 'px');
		}

		resize();
		$(window).resize(resize);
	});

	/**
	* Makes breadcrumbs responsive
	*/
	container.find('.breadcrumbs:not(.skip-responsive)').each(function() {
		var $this = $(this),
			$body = $('body'),
			links = $this.find('.crumb'),
			length = links.length,
			classes = ['wrapped-max', 'wrapped-wide', 'wrapped-medium', 'wrapped-small', 'wrapped-tiny'],
			classesLength = classes.length,
			maxHeight = 0,
			lastWidth = false,
			wrapped = false;

		// Test height by setting nowrap
		$this.css('white-space', 'nowrap');
		maxHeight = $this.height() + 1;
		$this.css('white-space', '');

		// Set tooltips
		$this.find('a').each(function() {
			var $link = $(this);
			$link.attr('title', $link.text());
		});

		// Funciton that checks breadcrumbs
		function check() {
			var height = $this.height(),
				width = $body.width(),
				link, i, j;

			if (height <= maxHeight) {
				if (!wrapped || lastWidth === false || lastWidth >= width) {
					lastWidth = width;
					return;
				}
			}
			lastWidth = width;

			if (wrapped) {
				$this.removeClass('wrapped').find('.crumb.wrapped').removeClass('wrapped ' + classes.join(' '));
				wrapped = false;
				if ($this.height() <= maxHeight) {
					return;
				}
			}

			wrapped = true;
			$this.addClass('wrapped');
			if ($this.height() <= maxHeight) {
				return;
			}

			for (i = 0; i < classesLength; i ++) {
				for (j = length - 1; j >= 0; j --) {
					links.eq(j).addClass('wrapped ' + classes[i]);
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
	* Adjust topiclist lists with check boxes
	*/
	container.find('ul.topiclist dd.mark').siblings('dt').children('.list-inner').addClass('with-mark');

	/**
	* Appends contents of all extra columns to first column in
	* .topiclist lists for mobile devices. Copies contents as is.
	*
	* To add that functionality to .topiclist list simply add
	* responsive-show-all to list of classes
	*/
	container.find('.topiclist.responsive-show-all > li > dl').each(function() {
		var $this = $(this),
			block = $this.find('dt .responsive-show:last-child'),
			first = true;

		// Create block that is visible only on mobile devices
		if (!block.length) {
			$this.find('dt > .list-inner').append('<div class="responsive-show" style="display:none;" />');
			block = $this.find('dt .responsive-show:last-child');
		}
		else {
			first = (block.text().trim().length == 0);
		}

		// Copy contents of each column
		$this.find('dd').not('.mark').each(function() {
			var column = $(this),
				children = column.children(),
				html = column.html();

			if (children.length == 1 && children.text() == column.text()) {
				html = children.html();
			}

			block.append((first ? '' : '<br />') + html);

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
	container.find('.topiclist.responsive-show-columns').each(function() {
		var list = $(this),
			headers = [],
			headersLength = 0;

		// Find all headers, get contents
		list.prev('.topiclist').find('li.header dd').not('.mark').each(function() {
			headers.push($(this).text());
			headersLength ++;
		});

		if (!headersLength) {
			return;
		}

		// Parse each row
		list.find('dl').each(function() {
			var $this = $(this),
				block = $this.find('dt .responsive-show:last-child'),
				first = true;

			// Create block that is visible only on mobile devices
			if (!block.length) {
				$this.find('dt > .list-inner').append('<div class="responsive-show" style="display:none;" />');
				block = $this.find('dt .responsive-show:last-child');
			}
			else {
				first = (block.text().trim().length == 0);
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

				block.append((first ? '' : '<br />') + html);

				first = false;
			});
		});
	});

	/**
	* Responsive tables
	*/
	container.find('table.table1').not('.not-responsive').each(function() {
		var $this = $(this),
			th = $this.find('thead > tr > th'),
			columns = th.length,
			headers = [],
			totalHeaders = 0,
			i, headersLength;

		// Find each header
		th.each(function(column) {
			var cell = $(this),
				colspan = parseInt(cell.attr('colspan')),
				dfn = cell.attr('data-dfn'),
				text = dfn ? dfn : cell.text();

			colspan = isNaN(colspan) || colspan < 1 ? 1 : colspan;

			for (i=0; i<colspan; i++) {
				headers.push(text);
			}
			totalHeaders ++;

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
					text = cell.text().trim();

				if (headersLength <= column) {
					return;
				}

				if (text.length && text !== '-') {
					cell.prepend('<dfn style="display: none;">' + headers[column] + '</dfn>');
				}
				else {
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
	container.find('table.responsive > tbody').each(function() {
		var items = $(this).children('tr');
		if (items.length == 0)
		{
			$(this).parent('table:first').addClass('responsive-hide');
		}
	});

	/**
	* Responsive link lists
	*/
	container.find('.linklist:not(.navlinks, .skip-responsive), .postbody ul.profile-icons:not(.skip-responsive)').each(function() {
		var $this = $(this),
			$body = $('body'),
			filterSkip = '.breadcrumbs, .skip-responsive',
			filterLast = '.pagination, .icon-notifications, .icon-pm, .icon-logout, .icon-login, .mark-read, .edit-icon, .quote-icon',
			links = $this.children().not(filterSkip),
			html = '<li class="responsive-menu" style="display:none;"><a href="javascript:void(0);" class="responsive-menu-link">&nbsp;</a><ul class="responsive-popup" style="display:none;" /></li>',
			filterLastList = links.filter(filterLast);

		if (links.is('.rightside'))
		{
			links.filter('.rightside:first').before(html);
		}
		else
		{
			$this.append(html);
		}

		var toggle = $this.children('.responsive-menu'),
			toggleLink = toggle.find('a.responsive-menu-link'),
			menu = toggle.find('ul.responsive-popup'),
			lastWidth = false,
			compact = false,
			responsive = false,
			copied = false;

		function check() {
			var width = $body.width();
			if (responsive && width <= lastWidth) {
				return;
			}

			// Reset responsive and compact layout
			if (responsive) {
				responsive = false;
				$this.removeClass('responsive');
				links.css('display', '');
				toggle.css('display', 'none');
			}

			if (compact) {
				compact = false;
				$this.removeClass('compact');
			}

			// Find tallest element
			var maxHeight = 0;
			links.each(function() {
				if (!$(this).height()) return;
				maxHeight = Math.max(maxHeight, $(this).outerHeight(true));
			});

			if (maxHeight < 1) {
				return;
			}

			// Nothing to resize if block's height is not bigger than tallest element's height
			if ($this.height() <= maxHeight) {
				toggle.removeClass('visible');
				menu.hide();
				return;
			}

			// Enable compact layout, find tallest element, compare to height of whole block
			compact = true;
			$this.addClass('compact');

			var compactMaxHeight = 0;
			links.each(function() {
				if (!$(this).height()) return;
				compactMaxHeight = Math.max(compactMaxHeight, $(this).outerHeight(true));
			});

			if ($this.height() <= maxHeight) {
				toggle.removeClass('visible');
				menu.hide();
				return;
			}

			// Compact layout did not resize block enough, switch to responsive layout
			compact = false;
			$this.removeClass('compact');
			responsive = true;

			if (!copied) {
				if (menu.parents().is('.rightside')) {
					menu.addClass('responsive-rightside');
				}
				menu.append(links.clone(true));
				menu.find('li.leftside, li.rightside').removeClass('leftside rightside');
				menu.find('.inputbox').parents('li:first').css('white-space', 'normal');
				copied = true;
			}
			else {
				menu.children().css('display', '');
			}

			toggle.css('display', '');
			$this.addClass('responsive');

			// Try to not hide filtered items
			if (filterLastList.length) {
				links.not(filterLast).css('display', 'none');

				maxHeight = 0;
				filterLastList.each(function() {
					if (!$(this).height()) return;
					maxHeight = Math.max(maxHeight, $(this).outerHeight(true));
				});

				if ($this.height() <= maxHeight) {
					menu.children().filter(filterLast).css('display', 'none');
					return;
				}
			}

			links.css('display', 'none');
		}

		toggleLink.click(function() {
			if (!responsive) return;
			if (!toggle.hasClass('visible')) {
				// Hide other popups
				$('.responsive-menu.visible').removeClass('visible').find('.responsive-popup').hide();
			}
			toggle.toggleClass('visible');
			menu.toggle();
		});

		check();
		$(window).resize(check);
	});

	/**
	* Responsive tabs
	*/
	container.find('#tabs, #minitabs').not('.skip-responsive').each(function() {
		var $this = $(this),
			$body = $('body'),
			ul = $this.children(),
			tabs = ul.children().not('.skip-responsive'),
			links = tabs.children('a'),
			toggle = ul.append('<li class="responsive-tab" style="display:none;"><a href="javascript:void(0);" class="responsive-tab-link"><span>&nbsp;</span></a><ul class="responsive-tabs" style="display:none;" /></li>').find('li.responsive-tab'),
			toggleLink = toggle.find('a.responsive-tab-link'),
			menu = toggle.find('ul.responsive-tabs'),
			maxHeight = 0,
			lastWidth = false,
			responsive = false;

		links.each(function() {
			maxHeight = Math.max(maxHeight, $(this).outerHeight(true));
		})

		function check() {
			var width = $body.width(),
				height = $this.height();

			if (arguments.length == 0 && (!responsive || width <= lastWidth) && height <= maxHeight) {
				return;
			}

			tabs.show();
			toggle.hide();

			lastWidth = width;
			height = $this.height();
			if (height <= maxHeight) {
				responsive = false;
				return;
			}

			responsive = true;
			toggle.show();
			menu.hide().html('');

			var availableTabs = tabs.filter(':not(.activetab, .responsive-tab)'),
				total = availableTabs.length,
				i, tab;

			for (i = total - 1; i >= 0; i --) {
				tab = availableTabs.eq(i);
				menu.prepend(tab.clone(true));
				tab.hide();
				if ($this.height() <= maxHeight) {
					menu.find('a').click(function() { check(true); });
					return;
				}
			}
			menu.find('a').click(function() { check(true); });
		}

		toggleLink.click(function() {
			if (!responsive) return;
			menu.toggle();
		});

		check(true);
		$(window).resize(check);
	});

	/**
	 * Hide UCP/MCP navigation if there is only 1 item
	 */
	container.find('#navigation').each(function() {
		var items = $(this).children('ol, ul').children('li');
		if (items.length == 1)
		{
			$(this).addClass('responsive-hide');
		}
	});
}

/**
* Run onload functions
*/
(function($) {
	$(document).ready(function() {
		// Swap .nojs and .hasjs
		$('#phpbb.nojs').toggleClass('nojs hasjs');

		// Focus forms
		$('form[data-focus]:first').each(function() {
			$('#' + this.getAttribute('data-focus')).focus();
		});

		// Hide responsive menu and tabs
		$('#phpbb').click(function(e) {
			var parents = $(e.target).parents();
			if (!parents.is('.responsive-menu.visible')) {
				$('.responsive-menu.visible').removeClass('visible').find('.responsive-popup').hide();
			}
			if (!parents.is('.responsive-tab')) {
				$('.responsive-tabs').hide();
			}
		});

		parse_document($('body'));
	});
})(jQuery);
