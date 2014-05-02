(function($) {  // Avoid conflicts with other libraries

"use strict";

/**
* Close popup alert after a specified delay
*
* @param int Delay in ms until darkenwrapper's click event is triggered
*/
phpbb.closeDarkenWrapper = function(delay) {
	phpbbAlertTimer = setTimeout(function() {
		$('#darkenwrapper').trigger('click');
	}, delay);
};

// This callback will mark all forum icons read
phpbb.addAjaxCallback('mark_forums_read', function(res) {
	var readTitle = res.NO_UNREAD_POSTS;
	var unreadTitle = res.UNREAD_POSTS;
	var iconsArray = {
		'forum_unread': 'forum_read',
		'forum_unread_subforum': 'forum_read_subforum',
		'forum_unread_locked': 'forum_read_locked'
	};

	$('li.row').find('dl[class*="forum_unread"]').each(function() {
		var $this = $(this);

		$.each(iconsArray, function(unreadClass, readClass) {
			if ($this.hasClass(unreadClass)) {
				$this.removeClass(unreadClass).addClass(readClass);
			}
		});
		$this.children('dt[title="' + unreadTitle + '"]').attr('title', readTitle);
	});

	// Mark subforums read
	$('a.subforum[class*="unread"]').removeClass('unread').addClass('read');

	// Mark topics read if we are watching a category and showing active topics
	if ($('#active_topics').length) {
		phpbb.ajaxCallbacks.mark_topics_read.call(this, res, false);
	}

	// Update mark forums read links
	$('[data-ajax="mark_forums_read"]').attr('href', res.U_MARK_FORUMS);

	phpbb.closeDarkenWrapper(3000);
});

/** 
* This callback will mark all topic icons read
*
* @param update_topic_links bool Wether "Mark topics read" links should be
*     updated. Defaults to true.
*/
phpbb.addAjaxCallback('mark_topics_read', function(res, update_topic_links) {
	var readTitle = res.NO_UNREAD_POSTS;
	var unreadTitle = res.UNREAD_POSTS;
	var iconsArray = {
		'global_unread': 'global_read',
		'announce_unread': 'announce_read',
		'sticky_unread': 'sticky_read',
		'topic_unread': 'topic_read'
	};
	var iconsState = ['', '_hot', '_hot_mine', '_locked', '_locked_mine', '_mine'];
	var unreadClassSelectors = '';
	var classMap = {};
	var classNames = [];

	if (typeof update_topic_links === 'undefined') {
		update_topic_links = true;
	}

	$.each(iconsArray, function(unreadClass, readClass) {
		$.each(iconsState, function(key, value) {
			// Only topics can be hot
			if ((value === '_hot' || value === '_hot_mine') && unreadClass !== 'topic_unread') {
				return true;
			}
			classMap[unreadClass + value] = readClass + value;
			classNames.push(unreadClass + value);
		});
	});

	unreadClassSelectors = '.' + classNames.join(',.');

	$('li.row').find(unreadClassSelectors).each(function() {
		var $this = $(this);
		$.each(classMap, function(unreadClass, readClass) {
			if ($this.hasClass(unreadClass)) {
				$this.removeClass(unreadClass).addClass(readClass);
			}
		});
		$this.children('dt[title="' + unreadTitle + '"]').attr('title', readTitle);
	});

	// Remove link to first unread post
	$('a').has('span.icon_topic_newest').remove();

	// Update mark topics read links
	if (update_topic_links) {
		$('[data-ajax="mark_topics_read"]').attr('href', res.U_MARK_TOPICS);
	}

	phpbb.closeDarkenWrapper(3000);
});

// This callback will mark all notifications read
phpbb.addAjaxCallback('notification.mark_all_read', function(res) {
	if (typeof res.success !== 'undefined') {
		phpbb.markNotifications($('#notification_list li.bg2'), 0);
		phpbb.closeDarkenWrapper(3000);
	}
});

// This callback will mark a notification read
phpbb.addAjaxCallback('notification.mark_read', function(res) {
	if (typeof res.success !== 'undefined') {
		var unreadCount = Number($('#notification_list_button strong').html()) - 1;
		phpbb.markNotifications($(this).parent('li.bg2'), unreadCount);
	}
});

/**
 * Mark notification popup rows as read.
 *
 * @param {jQuery} el jQuery object(s) to mark read.
 * @param {int} unreadCount The new unread notifications count.
 */
phpbb.markNotifications = function(el, unreadCount) {
	// Remove the unread status.
	el.removeClass('bg2');
	el.find('a.mark_read').remove();

	// Update the notification link to the real URL.
	el.each(function() {
		var link = $(this).find('a');
		link.attr('href', link.attr('data-real-url'));
	});

	// Update the unread count.
	$('#notification_list_button strong').html(unreadCount);
	// Remove the Mark all read link if there are no unread notifications.
	if (!unreadCount) {
		$('#mark_all_notifications').remove();
	}
};

// This callback finds the post from the delete link, and removes it.
phpbb.addAjaxCallback('post_delete', function() {
	var el = $(this),
		postId;

	if (el.attr('data-refresh') === undefined) {
		postId = el[0].href.split('&p=')[1];
		var post = el.parents('#p' + postId).css('pointer-events', 'none');
		if (post.hasClass('bg1') || post.hasClass('bg2')) {
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
phpbb.addAjaxCallback('post_visibility', function(res) {
	var remove = (res.visible) ? $(this) : $(this).parents('.post');
	$(remove).css('pointer-events', 'none').fadeOut(function() {
		$(this).remove();
	});

	if (res.visible)
	{
		// Remove the "Deleted by" message from the post on restoring.
		remove.parents('.post').find('.post_deleted_msg').css('pointer-events', 'none').fadeOut(function() {
			$(this).remove();
		});
	}
});

// This removes the parent row of the link or form that fired the callback.
phpbb.addAjaxCallback('row_delete', function() {
	$(this).parents('tr').remove();
});

// This handles friend / foe additions removals.
phpbb.addAjaxCallback('zebra', function(res) {
	var zebra;

	if (res.success) {
		zebra = $('.zebra');
		zebra.first().html(res.MESSAGE_TEXT);
		zebra.not(':first').html('&nbsp;').prev().html('&nbsp;');
	}
});

/**
 * This callback updates the poll results after voting.
 */
phpbb.addAjaxCallback('vote_poll', function(res) {
	if (typeof res.success !== 'undefined') {
		var poll = $('.topic_poll');
		var panel = poll.find('.panel');
		var results_visible = poll.find('dl:first-child .resultbar').is(':visible');
		var most_votes = 0;

		// Set min-height to prevent the page from jumping when the content changes
		var update_panel_height = function (height) {
			var height = (typeof height === 'undefined') ? panel.find('.inner').outerHeight() : height;
			panel.css('min-height', height);
		};
		update_panel_height();

		// Remove the View results link
		if (!results_visible) {
			poll.find('.poll_view_results').hide(500);
		}

		if (!res.can_vote) {
			poll.find('.polls, .poll_max_votes, .poll_vote, .poll_option_select').fadeOut(500, function () {
				poll.find('.resultbar, .poll_option_percent, .poll_total_votes').show();
			});
		} else {
			// If the user can still vote, simply slide down the results
			poll.find('.resultbar, .poll_option_percent, .poll_total_votes').show(500);
		}
		
		// Get the votes count of the highest poll option
		poll.find('[data-poll-option-id]').each(function() {
			var option = $(this);
			var option_id = option.attr('data-poll-option-id');
			most_votes = (res.vote_counts[option_id] >= most_votes) ? res.vote_counts[option_id] : most_votes;
		});

		// Update the total votes count
		poll.find('.poll_total_vote_cnt').html(res.total_votes);

		// Update each option
		poll.find('[data-poll-option-id]').each(function() {
			var option = $(this);
			var option_id = option.attr('data-poll-option-id');
			var voted = (typeof res.user_votes[option_id] !== 'undefined') ? true : false;
			var most_voted = (res.vote_counts[option_id] == most_votes) ? true : false;
			var percent = (!res.total_votes) ? 0 : Math.round((res.vote_counts[option_id] / res.total_votes) * 100);
			var percent_rel = (most_votes == 0) ? 0 : Math.round((res.vote_counts[option_id] / most_votes) * 100);

			option.toggleClass('voted', voted);
			option.toggleClass('most-votes', most_voted);

			// Update the bars
			var bar = option.find('.resultbar div');
			var bar_time_lapse = (res.can_vote) ? 500 : 1500;
			var new_bar_class = (percent == 100) ? 'pollbar5' : 'pollbar' + (Math.floor(percent / 20) + 1);

			setTimeout(function () {
				bar.animate({width: percent_rel + '%'}, 500).removeClass('pollbar1 pollbar2 pollbar3 pollbar4 pollbar5').addClass(new_bar_class);
				bar.html(res.vote_counts[option_id]);

				var percent_txt = (!percent) ? res.NO_VOTES : percent + '%';
				option.find('.poll_option_percent').html(percent_txt);
			}, bar_time_lapse);
		});

		if (!res.can_vote) {
			poll.find('.polls').delay(400).fadeIn(500);
		}

		// Display "Your vote has been cast." message. Disappears after 5 seconds.
		var confirmation_delay = (res.can_vote) ? 300 : 900;
		poll.find('.vote-submitted').delay(confirmation_delay).slideDown(200, function() {
			if (results_visible) {
				update_panel_height();
			}

			$(this).delay(5000).fadeOut(500, function() {
				resize_panel(300);
			});
		});

		// Remove the gap resulting from removing options
		setTimeout(function() {
			resize_panel(500);
		}, 1500);

		var resize_panel = function (time) {
			var panel_height = panel.height();
			var inner_height = panel.find('.inner').outerHeight();

			if (panel_height != inner_height) {
				panel.css({'min-height': '', 'height': panel_height}).animate({height: inner_height}, time, function () {
					panel.css({'min-height': inner_height, 'height': ''});
				});
			}
		};
	}
});

/**
 * Show poll results when clicking View results link.
 */
$('.poll_view_results a').click(function(e) {
	// Do not follow the link
	e.preventDefault();

	var poll = $(this).parents('.topic_poll');

	poll.find('.resultbar, .poll_option_percent, .poll_total_votes').show(500);
	poll.find('.poll_view_results').hide(500);
});

$('[data-ajax]').each(function() {
	var $this = $(this),
		ajax = $this.attr('data-ajax'),
		fn;

	if (ajax !== 'false') {
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
 * Make the display post links to use JS
 */
$('.display_post').click(function(e) {
	// Do not follow the link
	e.preventDefault();

	var post_id = $(this).attr('data-post-id');
	$('#post_content' + post_id).show();
	$('#profile' + post_id).show();
	$('#post_hidden' + post_id).hide();
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

		if (action === 'make_normal') {
			return $(this).find('select option[value="make_global"]').length > 0;
		} else if (action === 'lock' || action === 'unlock') {
			return true;
		}

		if (action === 'delete_topic' || action === 'make_sticky' || action === 'make_announce' || action === 'make_global') {
			return true;
		}

		return false;
	}
});

$('#quick-mod-select').change(function () {
	$('#quickmodform').submit();
});

$('#delete_permanent').click(function () {
	if ($(this).prop('checked')) {
		$('#delete_reason').hide();
	} else {
		$('#delete_reason').show();
	}
});

/**
* Toggle the member search panel in memberlist.php.
*
* If user returns to search page after viewing results the search panel is automatically displayed.
* In any case the link will toggle the display status of the search panel and link text will be
* appropriately changed based on the status of the search panel.
*/
$('#member_search').click(function () {
	$('#memberlist_search').slideToggle('fast');
	phpbb.ajaxCallbacks.alt_text.call(this);
	// Focus on the username textbox if it's available and displayed
	if ($('#memberlist_search').is(':visible')) {
		$('#username').focus();
	}
	return false;
});

/**
* Automatically resize textarea
*/
$(document).ready(function() {
	phpbb.resizeTextArea($('textarea:not(#message-box textarea, .no-auto-resize)'), {minHeight: 75, maxHeight: 250});
	phpbb.resizeTextArea($('#message-box textarea'));
});


})(jQuery); // Avoid conflicts with other libraries
