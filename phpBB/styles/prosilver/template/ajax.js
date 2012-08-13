(function($) {  // Avoid conflicts with other libraries

"use strict";

// This callback finds the post from the delete link, and removes it.
phpbb.add_ajax_callback('post_delete', function() {
	var el = $(this),
		post_id;

	if (el.attr('data-refresh') === undefined)
	{
		post_id = el[0].href.split('&p=')[1];
		var post = el.parents('#p' + post_id).css('pointer-events', 'none');
		if (post.hasClass('bg1') || post.hasClass('bg2'))
		{
			var posts1 = post.nextAll('.bg1');
			post.nextAll('.bg2').removeClass('bg2').addClass('bg1');
			posts1.removeClass('bg1').addClass('bg2');
		}
		post.fadeOut(function() {
			$(this).remove();
		});
	}
});

// This callback removes the approve / disapprove div or link.
phpbb.add_ajax_callback('post_approve', function(res) {
	var remove = (res.approved) ? $(this) : $(this).parents('.post');
	$(remove).css('pointer-events', 'none').fadeOut(function() {
		$(this).remove();
	});
});

// This removes the parent row of the link or form that fired the callback.
phpbb.add_ajax_callback('row_delete', function() {
	$(this).parents('tr').remove();
});

// This handles friend / foe additions removals.
phpbb.add_ajax_callback('zebra', function(res) {
	var zebra;

	if (res.success) {
		zebra = $('.zebra');
		zebra.first().html(res.MESSAGE_TEXT);
		zebra.not(':first').html('&nbsp;').prev().html('&nbsp;');
	}
});

phpbb.add_ajax_callback('revisions.compare', function(res) {
	var i;

	for (i in res.revisions_block)
	{
		$('#r' + res.revisions_block[i].ID).css('opacity', res.revisions_block[i].IN_RANGE ? 1.0 : 0.7);
	}

	$('.first').html(res.subject_diff_rendered);
	$('.content').html(res.text_diff_rendered);
	$('.right-box').html(res.comparing_to);
});

phpbb.add_ajax_callback('revisions.protect', function(res) {
    if (res.success)
    {
    	$('#link_protect').hide();
    	$('#link_unprotect').show();
    	$('.revision_action_success').html(res.message).fadeIn(500).delay(5000).fadeOut(500);
    }
});

phpbb.add_ajax_callback('revisions.unprotect', function(res) {
	if (res.success)
	{
    	$('#link_unprotect').hide();
    	$('#link_protect').show();
    	$('.revision_action_success').html(res.message).fadeIn(500).delay(5000).fadeOut(500);
    }
});

phpbb.add_ajax_callback('revisions.delete', function(res) {
    if (res.success)
    {
    	var revision_count;

    	$(this).parents('ul').remove();
    	revision_count = parseInt($('#compare_summary').html());
    	$('#compare_summary').html(revision_count - 1);
    }
});

phpbb.add_ajax_callback('revisions.viewtopic_view', function() {
	var id;
	id = $(this).parents('.post').attr('id');
	$('#' + id + '_revisions').slideToggle();
});



$('[data-ajax]').each(function() {
	var $this = $(this),
		ajax = $this.attr('data-ajax'),
		fn;

	if (ajax !== 'false')
	{
		fn = (ajax !== 'true') ? ajax : null;
		phpbb.ajaxify({
			selector: this,
			refresh: $this.attr('data-refresh') !== undefined,
			callback: fn
		});
	}
});


/**
 * This simply appends #preview to the action of the
 * QR action when you click the Full Editor & Preview button
 */
$('#qr_full_editor').click(function() {
	$('#qr_postform').attr('action', function(i, val) {
		return val + '#preview';
	});
});



/**
 * This AJAXifies the quick-mod tools. The reason it cannot be a standard
 * callback / data attribute is that it requires filtering - some of the options
 * can be ajaxified, while others cannot.
 */
phpbb.ajaxify({
	selector: '#quickmodform',
	refresh: true,
	filter: function (data) {
		var action = $('#quick-mod-select').val();

		if (action === 'make_normal')
		{
			return $(this).find('select option[value="make_global"]').length > 0;
		}
		else if (action === 'lock' || action === 'unlock')
		{
			return true;
		}

		if (action === 'delete_topic' || action === 'make_sticky' || action === 'make_announce' || action === 'make_global') {
			return true;
		}

		return false;
	}
});



})(jQuery); // Avoid conflicts with other libraries
