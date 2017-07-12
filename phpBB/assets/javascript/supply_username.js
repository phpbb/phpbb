$(document).ready(function(){
	console.log(U_AJAX_MENTION_URL);
	$('#message').atwho({
		at: "@",
		insertTpl: '[mention]${name}[/mention]',
		limit: 500,
		maxLen: 25,
		callbacks: {
		/*
		 It function is given, At.js will invoke it if local filter can not find any data
		 @param query [String] matched query
		 @param callback [Function] callback to render page.
		*/
			remoteFilter: function(query, callback) {
				$.getJSON(U_AJAX_MENTION_URL, {q: query}, function (data) {
                    callback(data)
                });
			}

		}
	});

});