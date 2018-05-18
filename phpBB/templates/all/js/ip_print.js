function item_width(id, width)
{
	var target_item = null;
	if (document.getElementById)
	{
		target_item = document.getElementById(id);
	}
	else if (document.all)
	{
		target_item = document.all[id];
	}
	else if (document.layers)
	{
		target_item = document.layers[id];
	}

	if (!target_item)
	{
		// do nothing
		return 0;
	}
	else
	{
		target_item.style.width = width;
		return 1;
	}
}

