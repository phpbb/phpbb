phpbb.autosave_prompt = function (data) {
	var dt,
		choice,
		str = phpbb_autosave_prompt_msg,
		keys = [];
	
	for (var key in data) {
		dt = new Date((key + phpbb_autosave_tz_offset) * 1000);
		str += '\n' + (keys.length + 1) + ') ' + phpbb.autosave_timestr(dt);

		keys.push(key);
	}

	if (keys.length < 2) {
		return data[keys[0]];
	} else {
		while (true) {
			choice = prompt(str);
			if (choice === null) {
				return '';
			}

			choice = Number(choice);
			
			if (isNaN(choice) || choice < 1 || choice > keys.length) {
				continue;
			}

			break;
		}

		return data[keys[choice - 1]];
	}
};

phpbb.autosave_save = function (key) {
	try {
		window.localStorage[key] = $('textarea[name=message]').val();
	} catch (e) {
		// Quota exceeded, should inform the user that their autosave isn't
		// working
	}
};

phpbb.autosave_timestr = function (dt) {
	var hrs = dt.getUTCHours(),
		mins = dt.getUTCMinutes();

	if (hrs < 10) {
		hrs = '0' + hrs;
	}

	if (mins < 10) {
		mins = '0' + mins;
	}

	return hrs + ':' + mins;
};

jQuery(function($) {
	var key,
		key_no_creation,
		data,
		explode,
		bind;

	// localStorage not supported or no post box on the page
	if (!window.localStorage || $('textarea[name=message]').length < 1) {
		return;
	}

	key = window.location.host + window.location.pathname;
	// Extract the f=, t= or p= from the URL and separate with underscores
	key += window.location.search.replace(/.*?\W(f|t|p)=(\d)+.*?/g, '_$2');

	// Just store a variable without the creation time for convenience
	key_no_creation = key;
	key += '_' + $('input[name=creation_time]').val();
	
	// If we have data in localStorage when the page loads we can assume it's
	// ok to load their autosave
	data = {};
	for (var storage_key in window.localStorage) {
		explode = storage_key.split('_');
		if (key_no_creation === explode.slice(0, -1).join('_')) {
			data[parseInt(explode.pop())] = window.localStorage[storage_key];
		}
	}

	$('textarea[name=message]').val(phpbb.autosave_prompt(data));

	// Create a closure to pass variables to the setInterval call
	bind = function () {
		return phpbb.autosave_save(key);
	};

	setInterval(bind, 30 * 1000);
});
