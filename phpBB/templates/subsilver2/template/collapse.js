// <![CDATA[


	var sh_i = -1;
	var showhidden_triger = [];


	$(".topiclist.forums").each(function () {
	    $(this).before(function () {
 		if ($(this).hasClass('forums')) {

			sh_i = sh_i + 1;
			var cat_id = 'cat_' + sh_i;

			showhidden_triger[sh_i] = localStorage.getItem(cat_id);
			if (showhidden_triger[sh_i] == null) { showhidden_triger[sh_i] = 'true' };
                	if (showhidden_triger[sh_i] == 'false') {
				return '<div class="trigger inactive" cat="' + cat_id +'"></div>';
				} else {
				return '<div class="trigger active" cat="' + cat_id +'"></div>';
			}
		}
	    }).wrap('<div class="collapsethis" aria-hidden="false" />');
	});



	sh_i = 0;


	$(".collapsethis").each(function () {
		if (showhidden_triger[sh_i] == 'false') {
			$(this).attr('aria-hidden', 'true').hide();
			$(this).parents('div.forabg').css('opacity', '0.3');
		}
		sh_i = sh_i + 1;
	});


	$('.trigger').click(function () {
		var showhidden_triger_this = $(this).next().attr('aria-hidden');
		if (showhidden_triger_this == "false") {
			$(this).next().attr('aria-hidden', 'true').slideUp(500, 'easeInQuart', function() {
				$(this).parents('div.forabg').animate({
					opacity: '0.3'
					}, 1000)
				});
			$(this).removeClass('active').addClass('inactive');
		} else {
            		$(this).parents('div.forabg').animate({
               			 	opacity: '1.0'
            			}, 50, function() {
                			$('.trigger', this).next().attr('aria-hidden', 'false').slideDown(250, 'easeOutQuad')
            		});
 
			$(this).removeClass('inactive').addClass('active').removeClass('hover');
        	}
		localStorage.setItem($(this).attr('cat'), showhidden_triger_this);
	});


	$('div.forabg').on('mouseenter mouseleave', '.trigger.inactive', function(event) {
		var time = 200, opacity = event.type == 'mouseenter' ?  '1.0' : (time = 0,'0.3');
		$(event.delegateTarget).stop(true).delay(time).animate({ opacity: opacity }, 300)
	})


// ]]>