/**
* phpBB3 acp functions
*/

//var jump_page = '{LA_JUMP_PAGE}:';
//var on_page = '{ON_PAGE}';
//var per_page = '{PER_PAGE}';
//var base_url = '{A_BASE_URL}';

//var s_content_direction = '{S_CONTENT_DIRECTION}';
var s_content_direction = 'ltr';
var menu_state = 'shown';

/**
* Window popup
*/
function popup(url, width, height, name)
{
	if (!name)
	{
		name = '_popup';
	}

	window.open(url.replace(/&amp;/g, '&'), name, 'height=' + height + ', resizable=yes, scrollbars=yes, width=' + width);
	return false;
}

/**
* Jump to page
*/
function jumpto()
{
	var page = prompt(jump_page, on_page);

	if ((page !== null) && !isNaN(page) && (page > 0))
	{
		document.location.href = base_url.replace(/&amp;/g, '&') + '&start=' + ((page - 1) * per_page);
	}
}

/**
* Set display of page element
* s[-1,0,1] = hide,toggle display,show
*/
function dE(n, s, type)
{
	if (!type)
	{
		type = 'block';
	}

	var e = document.getElementById(n);
	if (!s)
	{
		s = (e.style.display == '') ? -1 : 1;
	}
	e.style.display = (s == 1) ? type : 'none';
}

/**
* Hiding/Showing the side menu
*/
function switch_menu()
{
	var menu = document.getElementById('menu');
	var main = document.getElementById('main');
	var toggle = document.getElementById('toggle');
	var handle = document.getElementById('toggle-handle');

	switch (menu_state)
	{
		// hide
		case 'shown':
			main.style.width = '93%';
			menu_state = 'hidden';
			menu.style.display = 'none';
			toggle.style.width = '20px';
			handle.style.backgroundImage = 'url(images/toggle.gif)';
			handle.style.backgroundRepeat = 'no-repeat';

			if (s_content_direction == 'rtl')
			{
				handle.style.backgroundPosition = '0% 50%';
				toggle.style.left = '96%';
			}
			else
			{
				handle.style.backgroundPosition = '100% 50%';
				toggle.style.left = '0';
			}
		break;

		// show
		case 'hidden':
			main.style.width = '76%';
			menu_state = 'shown';
			menu.style.display = 'block';
			toggle.style.width = '5%';
			handle.style.backgroundImage = 'url(images/toggle.gif)';
			handle.style.backgroundRepeat = 'no-repeat';

			if (s_content_direction == 'rtl')
			{
				handle.style.backgroundPosition = '100% 50%';
				toggle.style.left = '75%';
			}
			else
			{
				handle.style.backgroundPosition = '0% 50%';
				toggle.style.left = '15%';
			}
		break;
	}
}

/**
* Mark/unmark checkboxes
* id = ID of parent container, name = name prefix, state = state [true/false]
*/
function marklist(id, name, state)
{
	var parent = document.getElementById(id);
	if (!parent)
	{
		eval('parent = document.' + id);
	}

	if (!parent)
	{
		return;
	}

	var rb = parent.getElementsByTagName('input');

	for (var r = 0; r < rb.length; r++)
	{
		if (rb[r].name.substr(0, name.length) == name)
		{
			rb[r].checked = state;
		}
	}
}

