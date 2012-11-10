function phpbb_autosave () {
	try {
		window.localStorage['phpbb_post'] = $('#message')[0].value;
	} catch (e) {
		// Quota exceeded, should inform the user that their autosave isn't
		// working
	}
}

jQuery(function($) {
	// localStorage not supported or no post box on the page
	if (!window.localStorage || $('#message').length < 1) {
		return;
	}

	// If we have data in phpbb_post when the page loads we can assume it's ok
	// to load their autosave
	if (window.localStorage['phpbb_post']) {
		$('#message')[0].value = window.localStorage['phpbb_post'];
	}

	// Autosave every 30 seconds
	setInterval(phpbb_autosave, 30 * 1000);
});
