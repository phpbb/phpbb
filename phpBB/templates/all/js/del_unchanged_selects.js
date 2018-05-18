var select_id = new Array();
var select_value = new Array();

function remove_object(id)
{
	e = document.getElementById(id);
	e.parentNode.removeChild(e);
}

function save_initial_values()
{
	my_form = document.getElementById('acl_form');
	el = my_form.elements;
	for (i = 0; i < el.length; i++)
	{
		if ((el[i].type == 'select-one') && (el[i].id != ''))
		{
			select_id.push(el[i].id);
			select_value.push(el[i].value);
		}
	}
}

function remove_unchanged_selects()
{
	for (i = 0; i < select_id.length; i++)
	{
		s = document.getElementById(select_id[i]);
		if (s.value == select_value[i])
		{
			remove_object(select_id[i]);
		}
	}
}

if (window.attachEvent)
{
	window.attachEvent("onload", save_initial_values())
}
else
{
	window.onload = save_initial_values();
}
