/* global phpbb */
/* eslint no-var: 0 */

/**
* phpBB ACP functions
*/

/**
* Parse document block
*/
function parseDocument(container) {
	var test = document.createElement('div');
	test.remove();

	/**
	* Navigation
	*/
	container.find('#menu').each(function() {
		var menu = $(this);
		var blocks = menu.children('.menu-block');

		if (!blocks.length) {
			return;
		}

		// Set onclick event
		blocks.children('a.header').click(function() {
			var parent = $(this).parent();
			if (!parent.hasClass('active')) {
				parent.siblings().removeClass('active');
			}

			parent.toggleClass('active');
		});

		// Set active menu
		menu.find('#activemenu').parents('.menu-block').addClass('active');

		// Check if there is active menu
		if (!blocks.filter('.active').length) {
			blocks.filter(':first').addClass('active');
		}
	});

	/**
	* Responsive tables
	*/
	container.find('table').not('.not-responsive').each(function() {
		var $this = $(this);
		var th = $this.find('thead > tr > th');
		var headers = [];
		var totalHeaders = 0;
		var i;

		// Find columns
		$this.find('colgroup:first').children().each(function(i) {
			var column = $(this);
			$this.find('td:nth-child(' + (i + 1) + ')').addClass(column.prop('className'));
		});

		// Styles table
		if ($this.hasClass('styles')) {
			$this.find('td:first-child[style]').each(function() {
				var style = $(this).attr('style');
				if (style.length) {
					$(this).parent('tr').attr('style', style.toLowerCase().replace('padding', 'margin')).addClass('responsive-style-row');
				}
			});
		}

		// Find each header
		if (!$this.data('no-responsive-header')) {
			th.each(function(column) {
				var cell = $(this);
				var colspan = parseInt(cell.attr('colspan'), 10);
				var dfn = cell.attr('data-dfn');
				var text = dfn ? dfn : $.trim(cell.text());

				if (text === '&nbsp;') {
					text = '';
				}

				colspan = isNaN(colspan) || colspan < 1 ? 1 : colspan;

				for (i = 0; i < colspan; i++) {
					headers.push(text);
				}

				totalHeaders++;

				if (dfn && !column) {
					$this.addClass('show-header');
				}
			});
		}

		headersLength = headers.length;

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

		// Remove <dfn> in disabled extensions list
		$this.find('tr.ext_disabled > .empty:nth-child(2) + .empty').siblings(':first-child').children('dfn').remove();
	});

	/**
	* Hide empty responsive tables
	*/
	container.find('table.responsive > tbody').each(function() {
		var items = $(this).children('tr');
		if (!items.length) {
			$(this).parent('table:first').addClass('responsive-hide');
		}
	});

	/**
	* Fieldsets with empty <span>
	*/
	container.find('fieldset dt > span:last-child').each(function() {
		var $this = $(this);
		if ($this.html() === '&nbsp;') {
			$this.addClass('responsive-hide');
		}
	});

	/**
	 * Dynamically control a text field's maxlength (allows emoji to be counted as 1 character)
	 */
	container.find('#sitename_short').each(function() {
		const $this = this;
		const maxLength = $this.maxLength;
		$this.maxLength = maxLength * 2;
		$this.addEventListener('input', () => {
			const inputChars = Array.from($this.value);
			if (inputChars.length > maxLength) {
				$this.value = inputChars.slice(0, maxLength).join('');
			}
		});
	});

	/**
	* Responsive tabs
	*/
	container.find('#tabs').not('[data-skip-responsive]').each(function() {
		var $this = $(this);
		var $body = $('body');
		var ul = $this.children();
		var tabs = ul.children().not('[data-skip-responsive]');
		var links = tabs.children('a');
		var item = ul.append('<li class="tab responsive-tab" style="display:none;"><a href="javascript:void(0);" class="responsive-tab-link">&nbsp;</a><div class="dropdown tab-dropdown" style="display: none;"><div class="pointer"><div class="pointer-inner"></div></div><ul class="dropdown-contents" /></div></li>').find('li.responsive-tab');
		var menu = item.find('.dropdown-contents');
		var maxHeight = 0;
		var lastWidth = false;
		var responsive = false;

		links.each(function() {
			var link = $(this);
			maxHeight = Math.max(maxHeight, Math.max(link.outerHeight(true), link.parent().outerHeight(true)));
		});

		function check() {
			var width = $body.width();
			var height = $this.height();

			if (!arguments.length && (!responsive || width <= lastWidth) && height <= maxHeight) {
				return;
			}

			tabs.show();
			item.hide();

			lastWidth = width;
			height = $this.height();
			if (height <= maxHeight) {
				responsive = false;
				if (item.hasClass('dropdown-visible')) {
					phpbb.toggleDropdown.call(item.find('a.responsive-tab-link').get(0));
				}

				return;
			}

			responsive = true;
			item.show();
			menu.html('');

			var availableTabs = tabs.filter(':not(.activetab, .responsive-tab)');
			var total = availableTabs.length;
			var i;
			var tab;

			for (i = total - 1; i >= 0; i--) {
				tab = availableTabs.eq(i);
				menu.prepend(tab.clone(true).removeClass('tab'));
				tab.hide();
				if ($this.height() <= maxHeight) {
					menu.find('a').click(() => {
						check(true);
					});
					return;
				}
			}

			menu.find('a').click(() => {
				check(true);
			});
		}

		phpbb.registerDropdown(item.find('a.responsive-tab-link'), item.find('.dropdown'), { visibleClass: 'activetab', verticalDirection: 'down' });

		check(true);
		$(window).resize(check);
	});
}

/**
* Run onload functions
*/
(function($) {
	$(document).ready(() => {
		// Swap .nojs and .hasjs
		$('body.nojs').toggleClass('nojs hasjs');

		// Focus forms
		$('form[data-focus]:first').each(function() {
			$('#' + this.getAttribute('data-focus')).focus();
		});

		parseDocument($('body'));

		$('#questionnaire-form').css('display', 'none');
		var $triggerConfiglist = $('#trigger-configlist');

		$triggerConfiglist.on('click', function() {
			var $configlist = $('#configlist');
			$configlist.closest('.send-stats-data-row').toggleClass('send-stats-data-hidden');
			$configlist.closest('.send-stats-row').find('.send-stats-data-row:first-child').toggleClass('send-stats-data-only-row');
			$(this).find('i').toggleClass('fa-angle-down fa-angle-up');
		});

		$('#configlist').closest('.send-stats-data-row').addClass('send-stats-data-hidden');

		// Do not underline actions icons on hover (could not be done via CSS)
		$('.actions a:has(i.acp-icon)').mouseover(function() {
			$(this).css('text-decoration', 'none');
		});

		// Live update BBCode font icon preview
		const updateIconClass = (element, newClass) => {
			// Ignore invalid class names
			const faIconRegex = /^(?!-)(?!.*--)[a-z0-9-]+(?<!-)$/;
			if (!faIconRegex.test(newClass)) {
				return;
			}

			element.classList.forEach(className => {
				if (className.startsWith('fa-') && className !== 'fa-fw') {
					element.classList.remove(className);
				}
			});

			element.classList.add(`fa-${newClass}`);
		};

		const pageIconFont = document.getElementById('bbcode_font_icon');

		if (pageIconFont) {
			pageIconFont.addEventListener('keyup', function() {
				updateIconClass(this.nextElementSibling, this.value);
			});

			pageIconFont.addEventListener('blur', function() {
				updateIconClass(this.nextElementSibling, this.value);
			});
		}
	});
})(jQuery);
