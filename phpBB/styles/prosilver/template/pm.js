$(function() {
	// scroll to the very bottom of the conversation
	$('.c-pm-msg-list').scrollTop(99999);

	// make whole conversation clickable
	$('.c-pm-item').click(function() {
		window.location.href = $(this).find('.c-pm-item-url').attr('href');
	});

	// conversations pagination
	$(document).on('click', '#pm-conversation-pagination-newer', function(e) {
		handle_conversation_pagination(e, $(this).attr('href'));
	});
	$(document).on('click', '#pm-conversation-pagination-older', function(e) {
		handle_conversation_pagination(e, $(this).attr('href'));
	});

	function handle_conversation_pagination(e, url) {
		e.preventDefault();

		$.get(url, function(source) {
			$('#pm-conversations-list').replaceWith($('#pm-conversations-list', source));
			replace_ajax_links(source);
			history.pushState({}, '', url);
		});
	}

	// messages pagination
	$(document).on('click', '#pm-message-pagination-older', function(e) {
		handle_message_pagination(e, $(this).attr('href'));
	});
	$(document).on('click', '#pm-message-pagination-newer', function(e) {
		handle_message_pagination(e, $(this).attr('href'));
	});

	function handle_message_pagination(e, url) {
		e.preventDefault();

		$.get(url, function(source) {
			$('#pm-messages-list').replaceWith($('#pm-messages-list', source));
			replace_ajax_links(source);
			history.pushState({}, '', url);
		});
	}

	function replace_ajax_links(source) {
		$('#pm-conversation-pagination-newer').replaceWith($('#pm-conversation-pagination-newer', source));
		$('#pm-conversation-pagination-older').replaceWith($('#pm-conversation-pagination-older', source));
		$('#pm-message-pagination-older').replaceWith($('#pm-message-pagination-older', source));
		$('#pm-message-pagination-newer').replaceWith($('#pm-message-pagination-newer', source));
	}


	// edit title
	$('.c-pm-menu-action-edit-title').on('click', function(e) {
		e.preventDefault();

		$form = $('<form method="POST" action="' + $(this).attr('href') + '"><input type="text" name="new_title" value="' + $('.c-pm-title').html() + '" autocomplete="off" /></form>');
		$('.c-pm-title').replaceWith($form);
		$form.children('input').select();
	});
});
