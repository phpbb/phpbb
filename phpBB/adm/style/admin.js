/**
* phpBB3 ACP functions
*/

/**
* Parse document block
*/
function parse_document(container) 
{
	var test = document.createElement('div'),
		oldBrowser = (typeof test.style.borderRadius == 'undefined');

	delete test;

	/**
	* Navigation
	*/
	container.find('#menu').each(function() {
		var menu = $(this),
			blocks = menu.children('.menu-block');

		if (!blocks.length) {
			return;
		}

		// Set onclick event
		blocks.children('a.header').click(function() {
			$(this).parent().toggleClass('active');
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
		var $this = $(this),
			th = $this.find('thead > tr > th'),
			columns = th.length,
			headers = [],
			totalHeaders = 0,
			i, headersLength;

		// Find columns
		$this.find('colgroup:first').children().each(function(i) {
			var column = $(this);
			if (column.hasClass('col1')) {
				$this.find('td:nth-child(' + (i + 1) + ')').addClass('col1');
			}
			if (column.hasClass('col2')) {
				$this.find('td:nth-child(' + (i + 1) + ')').addClass('col2');
			}
		});

		// Find each header
		if (!$this.data('no-responsive-header'))
		{
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
		}
		
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
}

/**
* Run onload functions
*/
(function($) {
	$(document).ready(function() {
		// Swap .nojs and .hasjs
		$('body.nojs').toggleClass('nojs hasjs');

		// Focus forms
		$('form[data-focus]:first').each(function() {
			$('#' + this.getAttribute('data-focus')).focus();
		});

		parse_document($('body'));
	});
})(jQuery);
