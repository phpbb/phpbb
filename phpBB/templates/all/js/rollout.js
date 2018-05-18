function ref(object)
{
	if (document.getElementById)
	{
		return document.getElementById(object);
	}
	else if (document.all)
	{
		return eval('document.all.' + object);
	}
	else
	{
		return false;
	}
}

function expand(object)
{
	object = ref(object);
	
	if( !object.style )
	{
		return false;
	}
	else
	{
		object.style.display = '';
	}

	if (window.event)
	{
		window.event.cancelBubble = true;
	}
}

function contract(object)
{
	object = ref(object);
	
	if( !object.style )
	{
		return false;
	}
	else
	{
		object.style.display = 'none';
	}

	if (window.event)
	{
		window.event.cancelBubble = true;
	}
}

function toggle(object, path)
{
	path = 'images/'
	image = ref(object + '_img');
	object = ref(object);

	if( !object.style )
	{
		return false;
	}
	
	if( object.style.display == 'none' )
	{
		object.style.display = '';
		image.src = path + 'contract.gif';
	}
	else
	{
		object.style.display = 'none';
		image.src = path + 'expand.gif';
	}
}
