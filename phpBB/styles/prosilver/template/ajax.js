(function($) {  // Avoid conflicts with other libraries

"use strict";

/**
* Close popup alert after a specified delay
*
* @param int Delay in ms until darkenwrapper's click event is triggered
*/
phpbb.closeDarkenWrapper = function(delay) {
	setTimeout(function() {
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
phpbb.addAjaxCallback('post_approve', function(res) {
	var remove = (res.approved) ? $(this) : $(this).parents('.post');
	$(remove).css('pointer-events', 'none').fadeOut(function() {
		$(this).remove();
	});
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

/**
* Toggle the member search panel in memberlist.php.
*
* If user returns to search page after viewing results the search panel is automatically displayed.
* In any case the link will toggle the display status of the search panel and link text will be
* appropriately changed based on the status of the search panel.
*/
$('#member_search').click(function () {
	$('#memberlist_search').slideToggle('fast');
	phpbb.ajax_callbacks.alt_text.call(this);
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
