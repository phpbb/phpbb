$(function() {
	// scroll to the very bottom of the thread
	$('.c-pm-msg-list').scrollTop(99999);

	// make whole item clickable
	$('.c-pm-item').click(function() {
		window.location.href = $(this).find('.c-pm-item-url').attr('href');
	});
});
