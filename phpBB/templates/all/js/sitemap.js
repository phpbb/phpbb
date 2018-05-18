/* Example initialisers

function initSitemap() {
	sitemap('h2', 'ul');
}
function initReadPrefs() {
	readPrefs('h2');
}
*/

if (window.addEventListener)
{
	window.addEventListener('load', initSitemap, false);
	window.addEventListener('load', initReadPrefs, false);
}
else
{
	window.attachEvent('onload', function() {
		initSitemap();
		initReadPrefs();
		})
}

// ::: START MAKE SITEMAP INTERACTIVE :::

function sitemap(headers, lists)
{
	// grab all header elements
	var h = document.getElementsByTagName(headers);
	// grab all list elements
	var u = document.getElementsByTagName(lists);

	for(i = 0; i < u.length; i++)
	{
		// hide all lists with 'sitemap' class
		if(u[i].className == 'sitemap')
		{
			u[i].style.display = 'none';
		}

		// get all links
		var a = u[i].getElementsByTagName('a');
		for(z = 0; z < a.length; z++)
		{
			// checks if link has a class of 'parent'
			if(a[z].className == 'parent')
			{
				var li = a[z].parentNode;
				// creates maximise.gif element
				var img = document.createElement('img');
				img.className = 'icon';
				img.src = 'templates/all/images/maximise.gif';
				img.style.verticalAlign = 'middle';

				li.insertBefore(img, a[z]);
				// set style
				li.className = 'parent';

				//hide child unordered list
				ul = a[z].nextSibling;
				while (ul.nodeType != 1)
				{
					ul = ul.nextSibling;
				}

				ul.style.display = 'none';

				// make clicking new image hide/show child list
				img.onclick = function()
				{
					li = this.parentNode;
					ul = li.getElementsByTagName(lists)[0];
					var ulStatus = (ul.style.display == 'none') ? 'block' : 'none';
					ul.style.display = ulStatus;

					// toggle between maximise.gif and minimise.gif
					imgStatus = (ulStatus == 'block') ? 'minimise' : 'maximise';
					this.src = 'templates/all/images/switch_' + imgStatus + '.gif';
				}
			}
		}
	}

	for(x = 0; x < h.length; x++)
	{
		if((h[x].className == 'sitemap') || (h[x].className == 'maximise') || (h[x].className == 'minimise'))
		{
			// assign unique IDS to each h2 element
			h[x].id = headers + x;
			h[x].className = 'maximise';

			// make h2 element show/hide unordered list when clicked
			h[x].onclick = function()
			{
				var ul = this.nextSibling;

				while (ul.nodeType != 1)
				{
					ul = ul.nextSibling;
				}

				var ulStatus = (ul.style.display == 'none') ? 'block' : 'none';

				ul.style.display = ulStatus;
				var hStatus = (ulStatus == 'block') ? 'minimise' : 'maximise';
				this.className = hStatus;

				// set cookie
				return writePrefs(this.id, ulStatus);
			}
		}
	}
}

// ::: END MAKE SITEMAP INTERACTIVE :::

// ::: START WRITE HIDE/SHOW COOKIE :::

function writePrefs(section, tf)
{
	var cookieName = section;
	var today = new Date();
	var expires = new Date(today.getTime() + 10 * 24 * 60 * 60 * 1000);
	var index = (document.cookie != document.cookie) ? document.cookie.indexOf(cookieName) : -1;

	if (document.cookie)
	{
		var index = document.cookie.indexOf(cookieName);
		if (index != -1)
		{
			var namestart = (document.cookie.indexOf("=", index) + 1);

			if (document.cookie.substring(namestart) == tf)
			{
				return false;
			}
		}
	}

	document.cookie= section + " = " + tf + "; expires=" + expires.toGMTString();
}

// ::: END WRITE HIDE/SHOW COOKIE :::

// ::: START READ HIDE/SHOW COOKIE :::

function readPrefs(headers)
{
	// grab all header elements
	var h = document.getElementsByTagName(headers);

	// check cookie for hide/show preferences
	for(i = 0; i < h.length; i++)
	{
		if((h[i].className == 'sitemap') || (h[i].className == 'maximise') || (h[i].className == 'minimise'))
		{
			// gets the element after the h2 heading
			var ul = h[i].nextSibling;
			h[i].id = headers + i;

			// makes sure ul is an element, not a blank space or carriage return
			while (ul.nodeType != 1)
			{
				ul = ul.nextSibling;
			}

			var cookieName = headers + i;

			if (document.cookie.length > 0)
			{
				var begin = document.cookie.indexOf(cookieName+"=");
				if (begin != -1)
				{
					begin += cookieName.length+1;
					var end = document.cookie.indexOf(";", begin);

					if (end == -1) end = document.cookie.length;

					// gets display status from cookie
					var secValue = unescape(document.cookie.substring(begin, end));

					// sets dispaly status to equal that which was in the cookie
					var secStatus = (secValue == 'none') ? 'none' : 'block';
					var headersImg = (secValue == 'none') ? 'maximise' : 'minimise';

					document.getElementById(cookieName).className = headersImg;
					ul.style.display = secStatus;
				}
			}
		}
	}
}

// ::: END READ HIDE/SHOW COOKIE :::
