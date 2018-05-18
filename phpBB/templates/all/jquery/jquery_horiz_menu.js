/**
* Website functions
*/

$(document).ready(function()
{

	function isIE()
	{
		if(navigator.userAgent.match(/MSIE \d\.\d+/))
			return true;
		return false;
	}

	// setup the drop downs
	$('#navbar ul > li').hoverIntent(
		// mouseover so show the drop down
		function()
		{
			// make the nav item have the over state
			$('> .sub-menu', this).toggle();
			$('a span', this).addClass('hovering');
		},
		// mouseout so hide the drop down
		function()
		{
			// remove the over state on the nav item
			$('> .sub-menu', this).toggle();
			$('a span', this).removeClass('hovering');
		}
	);

	// IE fun
	if(isIE())
	{
		$('#navbar li li').hover(
			function()
			{
				$(this).addClass('hover_ie');
			},
			function()
			{
				$(this).removeClass('hover_ie');
			}
		);
	}

	//from http://richa.avasthi.name/blogs/tepumpkin/2008/01/11/ie7-lessons-learned/
	function zIndexWorkaround()
	{
		// If the browser is IE,
		if(isIE())
		{
			/*
			** For each div with class menu (i.e.,
			** the thing we want to be on top),
			*/
			$(".sub-menu").parents().each(function() {
				var p = $(this);
				var pos = p.css("position");

				// If it's positioned,
				if(pos == "relative" ||
				 pos == "absolute" ||
				 pos == "fixed")
				{
					/*
					** Add the "on-top" class name when the
					** mouse is hovering over it, and remove
					** it when the mouse leaves.
					*/
					p.hover(function() {
						$(this).addClass("on-top");
					},
					function() {
						$(this).removeClass("on-top");
					});
				}
			});
		}
	}

	zIndexWorkaround();
})