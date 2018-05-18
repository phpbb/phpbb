<?xml version="1.0" encoding="UTF-8" ?>
<!-- MODX by the phpBB MOD Team XSL file v1.0 copyright 2005-2006 the phpBB MOD Team. 
	$Id: modx.subsilver.en.xsl,v 1.3 2006/05/08 22:29:29 wgeric Exp $ -->
<!DOCTYPE xsl:stylesheet[
	<!ENTITY nbsp "&#160;">
]>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:mod="http://www.phpbb.com/mods/xml/modx-1.0.xsd">
	<xsl:output method="html" omit-xml-declaration="no" indent="yes" />
<xsl:variable name="title" select="mod:mod/mod:header/mod:title" />
<xsl:variable name="version">
<xsl:for-each select="mod:mod/mod:header/mod:mod-version">
	<xsl:call-template name="give-version">
		</xsl:call-template>
	</xsl:for-each>
</xsl:variable>
	<xsl:template match="mod:mod">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<meta http-equiv="Content-Language" content="en-GB" />
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<style>

/* Style for a "Recommendation" */

/*
   Copyright 1997-2003 W3C (MIT, ERCIM, Keio). All Rights Reserved.
   The following software licensing rules apply:
   http://www.w3.org/Consortium/Legal/copyright-software */

/* $Id: modx.subsilver.en.xsl,v 1.3 2006/05/08 22:29:29 wgeric Exp $ */

/* Updated by Jon Stanley for use in phpBB XML MOD */

/* Updated by David Smith to look subSilvery for phpBB */

html, body {
  font-family: Verdana, Arial, Helvetica, sans-serif;
  color: black;
  background: #E5E5E5;
  background-position: top left;
  background-attachment: fixed;
  background-repeat: no-repeat;
  }
:link { color : #006699; background: transparent }
:visited { color : #006699; background: transparent }
a:active { color : #006699; background: transparent }
a:hover { text-decoration: underline; color : #DD6900; }

a:link img, a:visited img { border-style: none } /* no border on img links */

a img { color: white; }        /* trick to hide the border in Netscape 4 */
@media all {                   /* hide the next rule from Netscape 4 */
  a img { color: inherit; }    /* undo the color change above */
}

th, td { /* ns 4 */
  font-family: sans-serif;
}

h1, h2, h3, h4, h5, h6 { text-align: left }
/* background should be transparent, but WebTV has a bug */
h1, h2, h3 { color: #006699 }
h1 { font: 170% sans-serif }
h2 { font: 140% sans-serif }
h3 { font: 120% sans-serif }
h4 { font: bold 100% sans-serif }
h5 { font: italic 100% sans-serif }
h6 { font: small-caps 100% sans-serif }

.hide { display: none }

div.head { margin-bottom: 1em }
div.head h1 { margin-top: 2em; clear: both }
div.head table { margin-left: 2em; margin-top: 2em }

p.copyright { font-size: small }
p.copyright small { font-size: small }

@media screen {  /* hide from IE3 */
a[href]:hover { background: #ffa }
}

pre { margin-left: 2em }
/*
p {
  margin-top: 0.6em;
  margin-bottom: 0.6em;
}
*/
dt, dd { margin-top: 0; margin-bottom: 0 } /* opera 3.50 */
dt { font-weight: bold }

pre, code { font-family: monospace } /* navigator 4 requires this */

ul.toc {
  list-style: disc;		/* Mac NS has problem with 'none' */
  list-style: none;
}

@media aural {  
  h1, h2, h3 { stress: 20; richness: 90 }
  .hide { speak: none }
  p.copyright { volume: x-soft; speech-rate: x-fast }
  dt { pause-before: 20% }
  pre { speak-punctuation: code } 
}

/* Additional styles */

div.editFile {border: 2px solid #333333; margin: 0em 0em 2em; padding: 1em 1em; background: #D1D7DC;}
div.editFile h2 { font-size: 170%; margin: 0.4em 0em; }
div.action { border: 2px solid #DD6900; padding: 1em; background: #DEE3E7; margin: 1em 0em; }
div.action p { font-weight: normal; margin-top: 0px; margin-bottom: 0px; font-size: 0.8em; }
div.action h3 { margin-top: 0px; margin-bottom: 0px; }
div.action pre { padding: 0.2em; background: #EFEFEF; border: 2px solid #006699; overflow: scroll; width: 95%; }
div.editFile pre { padding: 0.2em; background: #EFEFEF; border: 2px solid #006699; overflow: scroll; width: 95%; }

#pageBody { background-color: #FFFFFF; border: 1px #98AAB1 solid; padding: 1em 1em;}

hr	{ height: 0px; border: solid #D1D7DC 0px; border-top-width: 1px;}

strong.red { color: red; }

</style>

<script type="text/javascript"><![CDATA[<!--]]>
var i = 0;
var box = new Array();
<xsl:for-each select="mod:action-group/mod:open/mod:edit">
	<xsl:for-each select="mod:find|mod:action">
		box[i] = '<xsl:value-of select="generate-id()"/>';
		i += 1;
	</xsl:for-each>
	<xsl:for-each select="mod:inline-edit">
		<xsl:for-each select="mod:inline-find|mod:inline-action">
			box[i] = '<xsl:value-of select="generate-id()"/>';
			i += 1;
		</xsl:for-each>
	</xsl:for-each>
</xsl:for-each>

<![CDATA[
var selectedElement = -1;
var boxes = box.length;
var pre_count = 0;

// The following line from http://www.ryancooper.com/resources/keycode.asp
document.onkeydown = mod_doKeyPress;

function SXBB_IsIEMac()
{
	// Any better way to detect IEMac?
	var ua = String(navigator.userAgent).toLowerCase();
	if( document.all && ua.indexOf("mac") >= 0 )
	{
		return true;
	}
	return false;
}

function select_text(id)
{
	var o = document.getElementById(id);
	if( !o )
	{
		return;
	}
	var r, s;
	if( document.selection && !SXBB_IsIEMac() )
	{
		// Works on: IE5+
		// To be confirmed: IE4? / IEMac fails?
		r = document.body.createTextRange();
		r.moveToElementText(o);
		r.select();
	}
	else if( document.createRange && (document.getSelection || window.getSelection) )
	{
		// Works on: Netscape/Mozilla/Konqueror/Safari
		// To be confirmed: Konqueror/Safari use window.getSelection ?
		r = document.createRange();
		r.selectNodeContents(o);
		s = window.getSelection ? window.getSelection() : document.getSelection();
		s.removeAllRanges();
		s.addRange(r);
	}

	find_selected(id);
	return o;
}

function find_selected(id)
{
	for( x = 0; x < box.length; x++ )
	{
		if ( box[x] == id )
		{
			selectedElement = x;
		}
	}
}

// function findPosY taken from http://www.quirksmode.org/js/findpos.html
function findPosY(obj)
{
	var curtop = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curtop += obj.offsetTop
			obj = obj.offsetParent;
		}
	}
	else if (obj.y)
	{
		curtop += obj.y;
	}
	return curtop;
}

function selectNextBox()
{
	selectedElement += 1;
	if (selectedElement >= boxes) selectedElement = 0;
	obj = select_text(box[selectedElement]);
	window.scrollTo(0, findPosY(obj) - 100);
}

function selectPrevBox()
{
	selectedElement -= 1;
	if (selectedElement < 0) selectedElement = boxes - 1;
	obj = select_text(box[selectedElement]);
	window.scrollTo(0, findPosY(obj) - 100);
}

function selectFirstBox()
{
	selectedElement = 0;
	obj = select_text(box[selectedElement]);
	window.scrollTo(0, findPosY(obj) - 100);
}

function mod_doKeyPress(e)
{
	/* section from w3 schools starts here http://www.w3schools.com/jsref/jsref_onkeypress.asp */
	var keynum;
	/* section from w3 schools ends here */

	// The following line from http://www.ryancooper.com/resources/keycode.asp
	if (window.event) keynum = window.event.keyCode;
	else if (e) keynum = e.which;

	if (keynum == 84) selectNextBox();
	//if (keynum == 9) selectNextBox(); //tab
	//if (keynum == 13) selectNextBox(); //enter/return
	//if (keynum == 32) selectNextBox(); //space
	if (keynum == 40) selectNextBox(); //down key
	if (keynum == 38) selectPrevBox(); //up key
	if (keynum == 83 || keynum == 37)
	{
		selectFirstBox();
	}
	return false;
}
//-->]]></script>
				<title>phpBB MOD &#187; <xsl:value-of select="$title" /></title>
			</head>
			<body>
				<div id="pageBody">
					<div id="modInfo">
						<xsl:for-each select="mod:header">
							<xsl:call-template name="give-header"></xsl:call-template>
						</xsl:for-each>
						<div id="modInstructions">
							<xsl:for-each select="mod:action-group">
								<xsl:call-template name="give-actions"></xsl:call-template>
							</xsl:for-each>
						</div>
						<hr />
						<div class="endMOD">
							<h1>Save all files. End of MOD.</h1>
							<p>You have finished the installation for this MOD. Upload all changed files to your website. If the installation went bad, simply restore your backed up files.</p>
						</div>
					</div>
				</div>
				<p class="copyright" style="text-align: center; font-size: 10px;">MOD UA XSLT File Copyright &#169; 2006 The phpBB Group, this MOD is copyright to the author<xsl:if test="count(author) > 1">s</xsl:if> listed above.</p>
			</body>
		</html>
	</xsl:template>
	<xsl:template name="give-header">
		<h1>Installation instructions for '<xsl:value-of select="$title" />' Version <xsl:value-of select="$version" /></h1>
		<h2>About this MOD</h2>
		<dl>
			<dt>Title:</dt>
			<dd>
				<xsl:if test="count(mod:title) > 1">
					<dl id="title">
						<xsl:for-each select="mod:title">
							<dl id="{generate-id()}">
								<dt>
									<xsl:value-of select="@lang" />
								</dt>
								<dd style='white-space:pre;'>
									<xsl:value-of select="current()" />
								</dd>
							</dl>
						</xsl:for-each>
					</dl>
				</xsl:if>
				<xsl:if test="count(mod:title) = 1">
					<xsl:value-of select="mod:title" />
				</xsl:if>
			</dd>
			<dt>Description:</dt>
			<dd>
				<xsl:if test="count(mod:description) > 1">
					<dl id="description">
						<xsl:for-each select="mod:description">
							<dl id="{generate-id()}">
								<dt>
									<xsl:value-of select="@lang" />
								</dt>
								<dd>
									<xsl:call-template name="add-line-breaks">
										<xsl:with-param name="string">
											<xsl:value-of select="current()" />
										</xsl:with-param>
									</xsl:call-template>
								</dd>
							</dl>
						</xsl:for-each>
					</dl>
				</xsl:if>
				<xsl:if test="count(mod:description) = 1">
					<xsl:call-template name="add-line-breaks">
						<xsl:with-param name="string">
							<xsl:value-of select="mod:description" />
						</xsl:with-param>
					</xsl:call-template>
				</xsl:if>
			</dd>
			<dt>Version:</dt>
			<dd>
				<xsl:for-each select="mod:mod-version">
					<xsl:call-template name="give-version"></xsl:call-template>
				</xsl:for-each>
			</dd>
			<xsl:for-each select="mod:installation">
				<xsl:call-template name="give-installation"></xsl:call-template>
			</xsl:for-each>
		</dl>
		<xsl:for-each select="mod:author-group">
			<h2>Author<xsl:if test="count(mod:author) > 1">s</xsl:if></h2>
			<xsl:call-template name="give-authors"></xsl:call-template>
		</xsl:for-each>
		<h2>Files To Edit</h2>
		<xsl:for-each select="../mod:action-group">
			<xsl:call-template name="give-files-to-edit"></xsl:call-template>
		</xsl:for-each>
		<h2>Included Files</h2>
		<xsl:if test="count(../mod:action-group/mod:copy/mod:file) = 0">
			<p>No files have been included with this MOD.</p>
		</xsl:if>
		<xsl:for-each select="../mod:action-group">
			<xsl:call-template name="give-files-included"></xsl:call-template>
		</xsl:for-each>
		<hr />
		<div id="modDisclaimer">
			<h1>Disclaimer</h1>
			<p>For Security Purposes, Please Check: <a href="http://www.phpbb.com/mods/">http://www.phpbb.com/mods/</a> for the latest version of this MOD. Downloading this MOD from other sites could cause malicious code to enter into your phpBB Forum. As such, phpBB will not offer support for MOD's not offered in our MOD-Database, located at: <a href="http://www.phpbb.com/mods/">http://www.phpbb.com/mods/</a></p>
			<h2>Author Notes</h2>
			<xsl:if test="count(mod:author-notes) > 1">
				<dl id="author-notes">
					<xsl:for-each select="mod:author-notes">
						<dl id="{generate-id()}">
							<dt>
								<xsl:value-of select="@lang" />
							</dt>
							<dd>
								<xsl:call-template name="add-line-breaks">
									<xsl:with-param name="string">
										<xsl:value-of select="current()" />
									</xsl:with-param>
								</xsl:call-template>
							</dd>
						</dl>
					</xsl:for-each>
				</dl>
			</xsl:if>
			<xsl:if test="count(mod:author-notes) = 1">
				<xsl:call-template name="add-line-breaks">
					<xsl:with-param name="string">
						<xsl:value-of select="mod:author-notes" />
					</xsl:with-param>
				</xsl:call-template>
			</xsl:if>
			<xsl:for-each select="mod:history">
				<xsl:call-template name="give-mod-history"></xsl:call-template>
			</xsl:for-each>
			<h3>License</h3>
			<p>This MOD has been licensed under the following license:</p>
			<p style='white-space:pre;'>
				<xsl:value-of select="mod:license" />
			</p>
			<h3>Other Notes</h3>
			<p>Before Adding This MOD To Your Forum, You Should Back Up All Files Related To This MOD</p>
			<p>This MOD was designed for phpBB<xsl:value-of select="mod:installation/mod:target-version/mod:target-primary" /> and may not function as stated on other phpBB versions. MODs for phpBB3.0 will <strong>not</strong> work on phpBB2.0 and vice versa.</p>
			<xsl:if test="./mod:mod-version/mod:minor mod 2 != 0 or ./mod:mod-version/mod:major = 0">
				<p>
					<strong class="red">This MOD is development quality. It is not recommended that you install it on a live forum.</strong>
				</p>
			</xsl:if>
		</div>
		<hr />
	</xsl:template>
	<xsl:template name="give-authors">
		<xsl:for-each select="mod:author">
			<xsl:call-template name="give-author"></xsl:call-template>
		</xsl:for-each>
	</xsl:template>
	<xsl:template name="give-author">
		<dl>
			<dt>Username:</dt>
			<dd>
				<a href="http://www.phpbb.com/phpBB/profile.php?mode=viewprofile&amp;u={mod:username}">
					<xsl:value-of select="mod:username" />
				</a>
			</dd>
			<xsl:if test="mod:email != 'N/A' and mod:email != 'n/a' and mod:email != ''">
				<dt>Email:</dt>
				<dd>
					<a href="mailto:{mod:email}">
						<xsl:value-of select="mod:email" />
					</a>
				</dd>
			</xsl:if>
			<dt>Realname:</dt>
			<dd>
				<xsl:value-of select="mod:realname" />
			</dd>
			<xsl:if test="mod:homepage != 'N/A' and mod:homepage != 'n/a' and mod:homepage!=''">
				<dt>WWW:</dt>
				<dd>
					<a href="{mod:homepage}">
						<xsl:value-of select="mod:homepage" />
					</a>
				</dd>
			</xsl:if>
		</dl>
		<br />
	</xsl:template>
	<xsl:template name="give-version"><xsl:value-of select="concat(mod:major, '.', mod:minor, '.', mod:revision, mod:release)" /></xsl:template>
	<xsl:template name="give-installation">
		<dt>Installation Level:</dt>
		<dd>
			<xsl:if test="mod:level='easy'">Easy</xsl:if>
			<xsl:if test="mod:level='intermediate'">Intermediate</xsl:if>
			<xsl:if test="mod:level='hard'">Hard</xsl:if>
		</dd>
		<dt>Installation Time:</dt>
		<dd>~<xsl:value-of select="floor(mod:time div 60)" /> minutes</dd>
	</xsl:template>
	<xsl:template name="give-mod-history">
		<xsl:if test="count(mod:entry)>1">
			<h2>MOD History</h2>
			<dl>
				<xsl:for-each select="mod:entry">
					<xsl:call-template name="give-history-entry"></xsl:call-template>
				</xsl:for-each>
			</dl>
		</xsl:if>
	</xsl:template>
	<xsl:template name="give-history-entry">
		<dt><xsl:value-of select="substring(mod:date,1,10)" /> - Version 
			<xsl:for-each select="mod:rev-version">
					<xsl:call-template name="give-version"></xsl:call-template>
				</xsl:for-each></dt>
		<dd>
			<xsl:if test="count(mod:changelog) > 1">
				<xsl:for-each select="mod:changelog">
					<xsl:call-template name="give-history-entry-changelog"></xsl:call-template>
				</xsl:for-each>
			</xsl:if>
			<xsl:if test="count(mod:changelog) = 1">
				<xsl:for-each select="mod:changelog">
					<xsl:call-template name="give-history-entry-changelog-single"></xsl:call-template>
				</xsl:for-each>
			</xsl:if>
		</dd>
	</xsl:template>
	<xsl:template name="give-history-entry-changelog">
		<dl>
			<dt>
				<xsl:value-of select="@lang" />
			</dt>
			<dd>
				<ul>
					<xsl:for-each select="mod:change">
						<li>
							<xsl:value-of select="current()" />
						</li>
					</xsl:for-each>
				</ul>
			</dd>
		</dl>
	</xsl:template>
	<xsl:template name="give-history-entry-changelog-single">
		<ul>
			<xsl:for-each select="mod:change">
				<li>
					<xsl:value-of select="current()" />
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>
	<xsl:template name="give-files-to-edit">
		<ul>
			<xsl:for-each select="mod:open">
				<xsl:call-template name="give-file"></xsl:call-template>
			</xsl:for-each>
		</ul>
	</xsl:template>
	<xsl:template name="give-files-included">
		<ul>
			<xsl:for-each select="mod:copy">
				<xsl:call-template name="give-file-copy"></xsl:call-template>
			</xsl:for-each>
		</ul>
	</xsl:template>
	<xsl:template name="give-file">
		<li>
			<xsl:value-of select="@src" />
			<xsl:if test="position()!=last()">,</xsl:if>
		</li>
	</xsl:template>
	<xsl:template name="give-file-copy">
		<xsl:for-each select="mod:file">
			<li>
				<xsl:value-of select="@from" />
				<xsl:if test="position()!=last()">,</xsl:if>
			</li>
		</xsl:for-each>
	</xsl:template>
	<xsl:template name="give-actions">
		<xsl:if test="count(mod:sql) > 0">
			<h1 onclick="select_text('sql');">SQL</h1>
		</xsl:if>
		<div id="sql">
		<xsl:for-each select="mod:sql">			
			<xsl:call-template name="give-sql"></xsl:call-template>			
		</xsl:for-each>
		</div>
		<xsl:if test="count(mod:copy) > 0">
			<h1>File Copy</h1>
		</xsl:if>
		<xsl:for-each select="mod:copy">
			<xsl:call-template name="give-filez"></xsl:call-template>
		</xsl:for-each>
		<h1>Edits</h1>
		<p>Click on the action name or in the code box to select the code.  You may also hit '<em>s</em>' on your keyboard to go to the first code box and the <em>up</em> and <em>down</em> arrows to scroll through the code boxes.</p>
		<xsl:for-each select="mod:open">
			<xsl:call-template name="give-fileo"></xsl:call-template>
		</xsl:for-each>
		<xsl:call-template name="give-manual"></xsl:call-template>
	</xsl:template>
	<xsl:template name="give-sql">
		<div class="action">
			<pre>
				<xsl:value-of select="current()" />
			</pre>
		</div>
	</xsl:template>
	<xsl:template name="give-manual">
		<xsl:for-each select="mod:diy-instructions">
			<div class="editFile">
				<h2 onClick="select_text('{generate-id()}')">DIY Instructions<xsl:if test="count(../mod:diy-instructions) > 1"> (<xsl:value-of select="@lang" />)</xsl:if></h2>
				<p>These are manual instructions that cannot be performed automatically. You should follow these instructions carefully.</p>
				<pre id="{generate-id()}">
					<xsl:value-of select="current()" />
				</pre>
			</div>
		</xsl:for-each>
	</xsl:template>
	<xsl:template name="give-fileo">
		<div class="editFile">
			<h2>Open: <xsl:value-of select="@src" /></h2>
			<xsl:for-each select="mod:edit">
				<div class="action">
					<xsl:for-each select="mod:find|mod:action|mod:inline-edit|mod:comment">
						<xsl:if test="name() = 'find'">
							<h3 onClick="select_text('{generate-id()}')">Find</h3>
							<p><strong>Tip:</strong> This may be a partial find and not the whole line.
<xsl:if test="@type = 'regex'">
									<br />
									<em>This find contains an advanced feature known as regular expressions, click here to learn more.</em>
								</xsl:if>
</p>
							<pre id="{generate-id()}">
								<xsl:value-of select="current()" />
							</pre>
						</xsl:if>
						<xsl:if test="name() = 'action'">
							<xsl:if test="@type = 'after-add'">
								<h3 onClick="select_text('{generate-id()}')">Add after</h3>
								<p><strong>Tip:</strong> Add these lines on a new blank line after the preceding line(s) to find.</p>
							</xsl:if>
							<xsl:if test="@type = 'before-add'">
								<h3 onClick="select_text('{generate-id()}')">Add before</h3>
								<p><strong>Tip:</strong> Add these lines on a new blank line before the preceding line(s) to find.</p>
							</xsl:if>
							<xsl:if test="@type = 'replace-with'">
								<h3 onClick="select_text('{generate-id()}')">Replace With</h3>
								<p><strong>Tip:</strong> Replace the preceding line(s) to find with the following lines.</p>
							</xsl:if>
							<xsl:if test="@type = 'operation'">
								<h3 onClick="select_text('{generate-id()}')">Increment</h3>
								<p><strong>Tip:</strong> This allows you to alter integers. For help on what each operator means, click here.</p>
							</xsl:if>
							<pre id="{generate-id()}">
								<xsl:value-of select="current()" />
							</pre>
						</xsl:if>
						<xsl:if test="name() = 'comment'">
							<dl>
								<dt>Comment:<xsl:if test="count(../mod:comment) > 1"> (<xsl:value-of select="@lang" />)</xsl:if></dt>
								<dd>
									<xsl:call-template name="add-line-breaks">
										<xsl:with-param name="string">
											<xsl:value-of select="current()" />
										</xsl:with-param>
									</xsl:call-template>
								</dd>
							</dl>
						</xsl:if>
						<xsl:if test="name() = 'inline-edit'">
							<div class="action">
								<xsl:for-each select="mod:inline-find|mod:inline-action|mod:inline-comment">
									<xsl:if test="name() = 'inline-find'">
										<h3 onClick="select_text('{generate-id()}')">In-line Find</h3>
										<p><strong>Tip:</strong> This is a partial match of a line for in-line operations.
<xsl:if test="@type = 'regex'">
												<br />
												<em>This find contains an advanced feature known as regular expressions, click here to learn more.</em>
											</xsl:if>
</p>
										<pre id="{generate-id()}">
											<xsl:value-of select="current()" />
										</pre>
									</xsl:if>
									<xsl:if test="name() = 'inline-action'">
										<xsl:if test="@type = 'after-add'">
											<h3 onClick="select_text('{generate-id()}')">In-line Add after</h3>
										</xsl:if>
										<xsl:if test="@type = 'before-add'">
											<h3 onClick="select_text('{generate-id()}')">In-line Add before</h3>
										</xsl:if>
										<xsl:if test="@type = 'replace-with'">
											<h3 onClick="select_text('{generate-id()}')">In-line Replace With</h3>
										</xsl:if>
										<xsl:if test="@type = 'operation'">
											<h3 onClick="select_text('{generate-id()}')">In-line Increment</h3>
											<p><strong>Tip:</strong> This allows you to alter integers. For help on what each operator means, click here.</p>
										</xsl:if>
										<pre id="{generate-id()}">
											<xsl:value-of select="current()" />
										</pre>
									</xsl:if>
									<xsl:if test="name() = 'inline-comment'">
										<p>
											<strong>Comment:</strong>
											<em>
												<xsl:value-of select="current()" />
											</em>
										</p>
									</xsl:if>
								</xsl:for-each>
							</div>
						</xsl:if>
					</xsl:for-each>
				</div>
			</xsl:for-each>
		</div>
	</xsl:template>
	<xsl:template name="give-filez">
		<dl>
			<xsl:for-each select="mod:file">
				<dt>Copy: <xsl:value-of select="@from" /></dt>
				<dd>To: <xsl:value-of select="@to" /></dd>
			</xsl:for-each>
		</dl>
	</xsl:template>
	<xsl:template name="give-sub-action-find">
		<p>Find</p>
		<pre>
			<xsl:value-of select="find-string" />
		</pre>
		<xsl:if test="count(in-line) > 0">
			<div class="action">
				<xsl:for-each select="in-line">
					<xsl:for-each select="find-in-line|edit-in-line">
						<xsl:if test="name() = 'find-in-line'">
							<xsl:call-template name="give-sub-action-in-line-find"></xsl:call-template>
						</xsl:if>
						<xsl:if test="name() = 'edit-in-line'">
							<xsl:call-template name="give-sub-action-in-line-edit"></xsl:call-template>
						</xsl:if>
					</xsl:for-each>
				</xsl:for-each>
			</div>
		</xsl:if>
	</xsl:template>
	<xsl:template name="give-sub-action-in-line-find">
		<p>In-line, Find</p>
		<pre>
			<xsl:value-of select="find-string-in-line" />
		</pre>
	</xsl:template>
	<xsl:template name="give-sub-action-edit">
		<xsl:if test="@action = 'replace'">
			<p>Replace, Add</p>
		</xsl:if>
		<xsl:if test="@action = 'add' and @where = 'after'">
			<p>After, Add</p>
		</xsl:if>
		<xsl:if test="@action = 'add' and @where = 'before'">
			<p>Before, Add</p>
		</xsl:if>
		<pre>
			<xsl:value-of select="current()" />
		</pre>
	</xsl:template>
	<xsl:template name="give-sub-action-in-line-edit">
		<xsl:if test="@action = 'replace'">
			<p>In-line, Replace With</p>
		</xsl:if>
		<xsl:if test="@action = 'add' and @where = 'after'">
			<p>In-line, After, Add</p>
		</xsl:if>
		<xsl:if test="@action = 'add' and @where = 'before'">
			<p>In-line, Before, Add</p>
		</xsl:if>
		<xsl:if test="@action = 'operation'">
			<p>In-line, perform the following mathematical operation</p>
			<xsl:variable name="oper_body" select="@operation" />
			<pre>
				<xsl:value-of select="$oper_body" />
			</pre>
		</xsl:if>
		<pre>
			<xsl:value-of select="current()" />
		</pre>
	</xsl:template>
	<!-- add-line-breaks borrowed from http://www.stylusstudio.com/xsllist/200103/post40180.html -->
	<xsl:template name="add-line-breaks">
		<xsl:param name="string" select="." />
		<xsl:choose>
			<xsl:when test="contains($string, '&#xA;')">
				<xsl:value-of select="substring-before($string, '&#xA;')" />
				<br />
				<xsl:call-template name="add-line-breaks">
					<xsl:with-param name="string" select="substring-after($string, '&#xA;')" />
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$string" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>