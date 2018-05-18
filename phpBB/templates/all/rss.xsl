<!-- <?xml version="1.0" encoding="utf-8" ?> -->
<?xml version="1.0" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<!--
	Style sheet ver. 1.0.3 english  for RSS Feed. Copyright 2005, Egor Naklonyaeff.
	More info http://naklon.info/rss/about.htm
	Javascript for mozilla borrowed from http://feedster.com/
-->
<xsl:output method="html"/>
<xsl:template match="/">
<html>
<head>
<title><xsl:value-of select="/rss/channel/title"/></title>
<style>
.head, .head a {background:#006699;color:#FFA34F;font:bold large/normal tahoma,arial,sans-serif;margin:0;padding:1ex;text-align:left;text-decoration:none}
dd {font:0.8em arial,sans-serif;margin:0.5ex 10px 0.5ex 50px;text-align:left}
dt {background:#D1D7DC;font:0.8em arial,sans-serif;margin:0 10px 0 10px;text-align:left;}
.gen { font-size : 12px; }
.genmed { font-size : 0.8em;; }
.gensmall { font-size : 10px; }
.gen, .genmed, .gensmall { color : #000000; }
a.gen, a.genmed, a.gensmall { color: #006699; text-decoration: none; }
a.gen:hover, a.genmed:hover, a.gensmall:hover { color: #DD6900; text-decoration: underline; }
.code {
	font-family: Courier, 'Courier New', sans-serif; font-size: 11px; color: #006600;
	background-color: #FAFAFA; border: #D1D7DC; border-style: solid;
	border-left-width: 1px; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px
}
.quote {
	font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 11px; color: #444444; line-height: 125%;
	background-color: #FAFAFA; border: #D1D7DC; border-style: solid;
	border-left-width: 1px; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px
}
.datetime {font-size:0.8em;color:#FF6600}
.off, .off a, .note, .note a { font-size: 10px; color: #999999; }
.off a:hover, .note a:hover{color:#FFA34F}
</style>
</head>
<body>
<xsl:apply-templates/>
</body>
<script type="text/javascript"><![CDATA[
function sr(s,f,r)
{
	var ret = s;
	var start = ret.indexOf(f);
	while (start>=0)
	{
		ret = ret.substring(0,start) + r + ret.substring(start+f.length,ret.length);
		start = ret.indexOf(f,start+r.length);
	}
	return ret;
}
function moz()
{
	var i, o, d, t;
	for( i = 1; i; i++)
	{
		d = "d_" + i;
		o = document.getElementById(d);
		if( o == null ) break;
		if( null != o.innerText ) break; // IE ok
		t = unescape( o.innerHTML );
		t = sr( t, "&gt;", ">" );
		t = sr( t, "&lt;", "<" );
		t = sr( t, "&amp;", "&" );
		o.innerHTML = t;
	}
}
moz();
]]></script>
</html>
</xsl:template>
<xsl:template match="/rss/channel">
<h1><xsl:attribute name="class">head</xsl:attribute>
<xsl:if test="image">
<a><xsl:attribute name="href"><xsl:value-of select="image/link"/></xsl:attribute>
<img>
<xsl:attribute name="border">0</xsl:attribute>
<xsl:attribute name="src"><xsl:value-of select="image/url"/></xsl:attribute>
<xsl:attribute name="alt"><xsl:value-of select="image/title"/></xsl:attribute>
<xsl:attribute name="align">right</xsl:attribute>
</img></a>
</xsl:if>
<a><xsl:attribute name="href"><xsl:value-of select="link"/></xsl:attribute>
<xsl:value-of select="title"/></a></h1>
<table cellpadding="3" cellspacing="3"><xsl:attribute name="style">clear:both</xsl:attribute><xsl:attribute name="width">100%</xsl:attribute>
	<tbody>
		<tr>
			<td width="66%">
			<p><xsl:value-of select="description" disable-output-escaping = "yes"/></p>
			<xsl:if test="lastBuildDate">
			<p class="datetime">
			Last updated on:
			<xsl:value-of select="lastBuildDate"/></p></xsl:if>
			</td>
			<td width="34%" style="border:1px outset;">
			<p>
			<xsl:attribute name="class">note</xsl:attribute>
			You are looking at an RSS feed
			<xsl:choose>
			<xsl:when test="contains(generator, 'RSS Feed')">
			provided by <a href="http://naklon.info/rss/about.htm" title="RSS 2.0 and Atom 0.3 Feed for phpBB"><xsl:value-of select="generator"/></a>.
			</xsl:when>
			<xsl:when test="generator">
			provided by <xsl:value-of select="generator"/>.
			</xsl:when>
			</xsl:choose>
			It has been rendered as HTML using an XSL stylesheet. <br/>
			To see the underlying XML tags please select the "View Source" command in your browser.<br />
			<xsl:choose>
			<xsl:when test="count(item) = 1">
			There is only 1 post shown in this feed.
			</xsl:when>
			<xsl:otherwise>
			<xsl:value-of select="count(item)"/> posts are shown in this feed.
			</xsl:otherwise>
			</xsl:choose>
			</p>
			</td>
		</tr>
	</tbody>
</table>
<hr />
<dl>
<xsl:for-each select="item">
	<dt>
		<a><xsl:attribute name="href"><xsl:value-of select="link"/></xsl:attribute>
		<xsl:value-of select="title" disable-output-escaping = "yes"/></a>
	</dt>
	<dd>
		<xsl:attribute name="id">d_<xsl:value-of select="position()"/></xsl:attribute>
		<xsl:attribute name="class">genmed</xsl:attribute>
		<xsl:value-of select="description" disable-output-escaping = "yes"/><br /><span>
		<xsl:attribute name="class">datetime</xsl:attribute>
		<xsl:value-of select="pubDate"/></span>
	</dd>
</xsl:for-each></dl>
<hr /><p class="off"><xsl:value-of select="copyright"/></p>
<xsl:if test="contains(generator, 'RSS Feed')">
<p class="off">For URL's the following arguments may be optionally passed (as  applicable):</p>
<ul class="off">
<xsl:choose>
<xsl:when test="contains(generator, 'RSS Feed Album')">
<li>cat_id=x - Album cat id. Use data only from this category to output RSS.</li>
<li>comments - see album comments in RSS Feed too</li>
</xsl:when>
<xsl:otherwise>
<li>f=x - forum id. Use data only from this forum to output RSS.</li>
</xsl:otherwise>
</xsl:choose>
<li>t=1 - only new topics (first messages in topic). Default - 0.</li>
<li>atom - generate atom 0.3 feed instead of rss 2.0</li>
<li>c=x - feed items count.</li>
<li> login or uid=x - try to turn WWW-Authenticate on. May not work on some hosts.</li>
</ul>
</xsl:if>
</xsl:template>
</xsl:stylesheet>