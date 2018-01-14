/* global phpbb */

/* eslint-disable camelcase, no-unused-vars */

/**
* PhpBB3 ACP functions
*/

/**
* Parse document block
*/
function parse_document(container) {
	/**
	* Navigation
	*/
	container.find('#menu').each(() => {
		const menu = $(this);
		const blocks = menu.children('.menu-block');

		if (blocks.length === 0) {
			return;
		}

		// Set onclick event
		blocks.children('a.header').click(() => {
			const parent = $(this).parent();
			if (!parent.hasClass('active')) {
				parent.siblings().removeClass('active');
			}
			parent.toggleClass('active');
		});

		// Set active menu
		menu.find('#activemenu').parents('.menu-block').addClass('active');

		// Check if there is active menu
		if (blocks.filter('.active').length === 0) {
			blocks.filter(':first').addClass('active');
		}
	});

	/**
	* Responsive tables
	*/
	container.find('table').not('.not-responsive').each(() => {
		const $this = $(this);
		const th = $this.find('thead > tr > th');
		const headers = [];
		let totalHeaders = 0;
		let i;
		let headersLength = 0;

		// Find columns
		$this.find('colgroup:first').children().each(i => {
			const column = $(this);
			$this.find('td:nth-child(' + (i + 1) + ')').addClass(column.prop('className'));
		});

		// Styles table
		if ($this.hasClass('styles')) {
			$this.find('td:first-child[style]').each(() => {
				const style = $(this).attr('style');
				if (style.length !== 0) {
					$(this).parent('tr').attr('style', style.toLowerCase().replace('padding', 'margin')).addClass('responsive-style-row');
				}
			});
		}

		// Find each header
		if (!$this.data('no-responsive-header'))		{
			th.each(column => {
				const cell = $(this);
				let colspan = parseInt(cell.attr('colspan'), 0);
				const dfn = cell.attr('data-dfn');
				let text = dfn ? dfn : $.trim(cell.text());

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

		$this.find('tbody > tr').each(() => {
			const row = $(this);
			const cells = row.children('td');
			let column = 0;

			if (cells.length === 1) {
				row.addClass('big-column');
				return;
			}

			cells.each(() => {
				const cell = $(this);
				let colspan = parseInt(cell.attr('colspan'), 0);
				const text = $.trim(cell.text());

				if (headersLength <= column) {
					return;
				}

				if ((text.length !== 0 && text !== '-') || cell.children().length !== 0) {
					if (headers[column] !== '') {
						cell.prepend('<dfn style="display: none;">' + headers[column] + '</dfn>');
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
	container.find('table.responsive > tbody').each(() => {
		const items = $(this).children('tr');
		if (items.length === 0)		{
			$(this).parent('table:first').addClass('responsive-hide');
		}
	});

	/**
	* Fieldsets with empty <span>
	*/
	container.find('fieldset dt > span:last-child').each(() => {
		const $this = $(this);
		if ($this.html() === '&nbsp;') {
			$this.addClass('responsive-hide');
		}
	});

	/**
	* Responsive tabs
	*/
	container.find('#tabs').not('[data-skip-responsive]').each(() => {
		const $this = $(this);
		const $body = $('body');
		const ul = $this.children();
		const tabs = ul.children().not('[data-skip-responsive]');
		const links = tabs.children('a');
		const item = ul.append('<li class="tab responsive-tab" style="display:none;"><a href="javascript:void(0);" class="responsive-tab-link">&nbsp;</a><div class="dropdown tab-dropdown" style="display: none;"><div class="pointer"><div class="pointer-inner" /></div><ul class="dropdown-contents" /></div></li>').find('li.responsive-tab');
		const menu = item.find('.dropdown-contents');
		let maxHeight = 0;
		let lastWidth = false;
		let responsive = false;

		links.each(() => {
			const link = $(this);
			maxHeight = Math.max(maxHeight, Math.max(link.outerHeight(true), link.parent().outerHeight(true)));
		});

		function check() {
			const width = $body.width();
			let height = $this.height();

			if (arguments.length === 0 && (!responsive || width <= lastWidth) && height <= maxHeight) {
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

			const availableTabs = tabs.filter(':not(.activetab, .responsive-tab)');
			const total = availableTabs.length;
			let i;
			let tab;

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

		phpbb.registerDropdown(item.find('a.responsive-tab-link'), item.find('.dropdown'), {visibleClass: 'activetab', verticalDirection: 'down'});

		check(true);
		$(window).resize(check);
	});
}

/**
* Run onload functions
*/
(function ($) {
	$(document).ready(() => {
		// Swap .nojs and .hasjs
		$('body.nojs').toggleClass('nojs hasjs');

		// Focus forms
		$('form[data-focus]:first').each(() => {
			$('#' + this.getAttribute('data-focus')).focus();
		});

		parse_document($('body'));

		$('#questionnaire-form').css('display', 'none');
		const $triggerConfiglist = $('#trigger-configlist');

		$triggerConfiglist.on('click', () => {
			const $configlist = $('#configlist');
			$configlist.closest('.send-stats-data-row').toggleClass('send-stats-data-hidden');
			$configlist.closest('.send-stats-row').find('.send-stats-data-row:first-child').toggleClass('send-stats-data-only-row');
			$(this).find('i').toggleClass('fa-angle-down fa-angle-up');
		});

		$('#configlist').closest('.send-stats-data-row').addClass('send-stats-data-hidden');
	});
})(jQuery);

/* eslint-disable camelcase, no-unused-vars */
