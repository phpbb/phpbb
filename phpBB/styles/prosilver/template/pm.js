/**
 * Open Pm smiley popup
 */
$(document).ready(function(){
	$('.pm-smile-button').click(function(){
		var $alert = phpbb.alert("Smiley Box", $('.pm-smile').html());

		$alert.on('click', '.pm-smiley-code', function() {
			phpbb.alert.close($alert, true);
		});
	});
});

/**
 * Open PM:To popup
 */
$(document).ready(function(){
	$('#pm-to-button').click(function(){
		var $alert = phpbb.alert("PM To:", $('#pm-to-model').html());

	});
});

/**
 * Open BBCode popup
 */
$(document).ready(function(){
	$('#pm-bbcode-button').click(function(){
		phpbb.alert("BBCode Box", $('#pm-bbcode-model').html());
	});
});
