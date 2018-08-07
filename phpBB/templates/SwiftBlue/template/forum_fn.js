/**
* phpBB3 forum functions
*/

function find_username(url)
{
	popup(url, 760, 570, '_usersearch');
	return false;
}

function popup(url, width, height, name)
{
	if (!name)
	{
		name = '_popup';
	}

	window.open(url.replace(/&amp;/g, '&'), name, 'height=' + height + ',resizable=yes,scrollbars=yes,width=' + width);
	return false;
}

function jumpto(page, per_page, base_url)
{
	if (page !== null && !isNaN(page) && page == Math.floor(page) && page > 0)
	{
		if (base_url.indexOf('?') == -1)
		{
			document.location.href = base_url + '?start=' + ((page - 1) * per_page);
		}
		else
		{
			document.location.href = base_url.replace(/&amp;/g, '&') + '&start=' + ((page - 1) * per_page);
		}
	}
}

function marklist(id, name, state)
{
	if (document.getElementById)	{ var parent = document.getElementById(id) || document[id]; }	else if (document.all) {var parent = document.all(id);}

	if (!parent)
	{
		return;
	}

	if (document.getElementsByTagName) {var rb = parent.getElementsByTagName('input');}	else if (document.all) {var rb = parent.document.all.tags('input');}

	for (var r = 0; r < rb.length; r++)
	{
		if (rb[r].name.substr(0, name.length) == name)
		{
			rb[r].checked = state;
		}
	}
}

function selectCode(a)
{
	'use strict';

	if (document.getElementsByTagName) {var e = a.parentNode.parentNode.getElementsByTagName('PRE')[0];}	else if (document.all) {var e = a.parentElement.parentElement.all.tags('PRE')[0];}
	var s, r;

	if (window.getSelection)
	{
		s = window.getSelection();
		if (window.opera && e.innerHTML.substring(e.innerHTML.length - 4) === '<BR>')
		{
			e.innerHTML = e.innerHTML + '&nbsp;';
		}
		r = document.createRange();
		r.selectNodeContents(e);
		s.removeAllRanges();
		s.addRange(r);
	}
	else if (document.getSelection)
	{
		s = document.getSelection();
		r = document.createRange();
		r.selectNodeContents(e);
		s.removeAllRanges();
		s.addRange(r);
	}
	else if (document.selection)
	{
		r = document.body.createTextRange();
		r.moveToElementText(e);
		r.select();
	}
}

/**
* Dropdown menus initialisation
*/

if (typeof LegacyDropDownMenus !== 'undefined')
{
	if (!window.XMLHttpRequest)
	{
		var iFrameFix=document.createElement("IFRAME"); iFrameFix.src="javascript:'<html></html>';";
	};

	if (document.getElementById('quick_links_list'))
	{
		var qkl=document.getElementById('quick_links_list'); qkl.onclick = function(e) { if (e) { e.stopPropagation(); } else { window.event.cancelBubble = true; } };

		document.getElementById('quick_link').onclick = function(e) { if (e) { e.stopPropagation(); } else { window.event.cancelBubble = true; } if (typeof nfl !== 'undefined') {nfl.style.display='none'}; if (typeof usl !== 'undefined') {usl.style.display='none'}; if (typeof ttl !== 'undefined') {ttl.style.display='none'}; var qks = qkl.style.display != 'none'; qkl.style.display = qks ? 'none' : 'inline';
			if (qkl.style.display != 'none')
			{
				for (i=0 ; i<qkl.getElementsByTagName('a').length ; i++) {	var LinkWidth = qkl.getElementsByTagName('a')[i].offsetWidth;	if (i==0) { var LinkWidest = LinkWidth } else if (LinkWidth>LinkWidest) { LinkWidest=LinkWidth }}	qkl.style.width = LinkWidest+8+"px";
				if (document.addEventListener) { var TotalHeight = window.innerHeight } else { var TotalHeight = document.documentElement.offsetHeight }	if (TotalHeight>400) { qkl.style.lineHeight="220%" } else { qkl.style.lineHeight="normal" }
				if (typeof iFrameFix !== 'undefined') {	qkl.appendChild(iFrameFix); iFrameFix.style.height=qkl.offsetHeight+"px";	iFrameFix.style.width=qkl.offsetWidth+"px";}
			};
			return false;
		};
	}

	if (document.getElementById('user_list'))
	{
		if (document.getElementById('notification_list'))
		{
			var nfl=document.getElementById('notification_list'); nfl.onclick = function(e) { if (e) { e.stopPropagation(); } else { window.event.cancelBubble = true; } };

			document.getElementById('notification_link').onclick = function(e) { if (e) { e.stopPropagation(); } else { window.event.cancelBubble = true; } if (typeof qkl !== 'undefined') {qkl.style.display='none'}; usl.style.display='none'; if (typeof ttl !== 'undefined') {ttl.style.display='none'}; var nfs = nfl.style.display != 'none'; nfl.style.display = nfs ? 'none' : 'inline'; 

				if (nfl.style.display != 'none')
				{
					var UserSideWidth = document.getElementById('user_side').offsetWidth; if (UserSideWidth < 180) { document.getElementById('notification_title').style.fontSize="80%"; document.getElementById('notification_title').style.padding="2px" } if (UserSideWidth > 270) { nfl.style.width = "270px" } else if (UserSideWidth < 90) { nfl.style.width = "90px" } else { nfl.style.width = UserSideWidth+"px" }
					if (document.getElementById('notification_list_overflow')) { var nfo = document.getElementById('notification_list_overflow'); nfo.style.height="auto"; if (document.addEventListener) { var TotalHeight = window.innerHeight } else { var TotalHeight = document.documentElement.offsetHeight } if (TotalHeight>520) { var MaxListHeight = 350 } else if (TotalHeight>400) { var MaxListHeight = 225 } else { var MaxListHeight = 100 } var ListHeight = nfo.offsetHeight; if (ListHeight < MaxListHeight) { nfo.style.height = ListHeight+"px" } else { nfo.style.height = MaxListHeight+"px" }}
					if (typeof iFrameFix !== 'undefined') { nfl.appendChild(iFrameFix); iFrameFix.style.height=nfl.offsetHeight+"px"; iFrameFix.style.width=nfl.offsetWidth+"px";}
				};
				return false;
			};
		}

		var usl=document.getElementById('user_list'); usl.onclick = function(e) { if (e) { e.stopPropagation(); } else { window.event.cancelBubble = true; } };

		document.getElementById('user_link').onclick = function(e) { if (e) { e.stopPropagation(); } else { window.event.cancelBubble = true; } if (typeof qkl !== 'undefined') {qkl.style.display='none'}; if (typeof nfl !== 'undefined') {nfl.style.display='none'}; if (typeof ttl !== 'undefined') {ttl.style.display='none'}; var uss = usl.style.display != 'none'; usl.style.display = uss ? 'none' : 'inline'; 
			if (usl.style.display != 'none')
			{
				for (i=0 ; i<usl.getElementsByTagName('a').length ; i++) {	var LinkWidth = usl.getElementsByTagName('a')[i].offsetWidth;	if (i==0) { var LinkWidest = LinkWidth } else if (LinkWidth>LinkWidest) { LinkWidest=LinkWidth }} usl.style.width = LinkWidest+8+"px"; var UserSideWidth = document.getElementById('user_side').offsetWidth; var UserListMargin = UserSideWidth - usl.offsetWidth; if (document.body.className == "ltr") { usl.style.marginLeft = UserListMargin+2+"px";} else { usl.style.marginRight = UserListMargin+2+"px";}
				if (typeof iFrameFix !== 'undefined') { usl.appendChild(iFrameFix); iFrameFix.style.height=usl.offsetHeight+"px"; iFrameFix.style.width=usl.offsetWidth+"px";}
			};
			return false;
		};

	}

	document.onclick = function() { if (typeof qkl !== 'undefined') {qkl.style.display='none'};  if (typeof nfl !== 'undefined') {nfl.style.display='none'};  if (typeof usl !== 'undefined') {usl.style.display='none'}; if (typeof ttl !== 'undefined') {ttl.style.display='none'};};
}

/**
* Run onload function
*/

window.onload = function () { if (document.getElementById) { document.documentElement.className = document.documentElement.className.replace(/nojs/gi,'hasjs');}	else if (document.all) { document.all.tags('html')[0].className = 'hasjs';}}
