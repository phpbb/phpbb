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
