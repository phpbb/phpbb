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
				// data = [{name:"Jacob", id : 1},
				// 		{name:"Isabella",id : 2},
				// 		{name:"Ethan", id : 3},
				// 		{name:"Emma", id : 4},
				// 		{name:"Michael", id : 5},
				// 		{name:"Olivia", id : 6},
				// 		{name:"Alexander", id : 7},
				// 		{name:"Sophia", id : 8},
				// 		{name:"William", id : 9},
				// 		{name:"Ava", id : 10},
				// 		{name:"Jashua", id : 11},
				// 		{name:"Emily", id : 12},
				// 		{name:"Daniel", id : 13},
				// 		{name:"Madison", id : 14},
				// 		{name:"Jayden", id : 15},
				// 		{name:"Abigail", id : 16},
				// 		{name:"Noah", id : 17},
				// 		{name:"Chloe", id : 16}]
				// callback(data);
				$.getJSON(U_AJAX_MENTION_URL, {q: query}, function (data) {
					// console.log(typeof data);
                    callback(data)
                });
			}

		}
	});

});