<table>
<?php
global $style_row;
if ( $style_row == 'articles_scroll')
{
?>
<tr><td>
<marquee id="recent_articles" behavior="scroll" direction="up" scrolldelay="100" height="80" scrollamount="2" loop="true" onmouseover="this.stop()" onmouseout="this.start()">
	<div>
	<br />
	<!-- BEGIN articles_scroll -->
		<!-- BEGIN recent_articles -->
		<b><a href="{recent_articles.U_ARTICLE}" title="{recent_articles.TITLE}" class="gensmall">{recent_articles.TITLE}</a></b><br />
		<span class="gensmall">{L_BY}&nbsp;{recent_articles.AUTHOR}&nbsp;{L_ON} {recent_articles.DATE}</span><br /><br />
		<!-- END recent_articles -->
	<!-- END articles_scroll -->
	<br />
	</div>
</marquee>
</td></tr>
<?php
}
?>
<!-- BEGIN articles_static -->
<!-- BEGIN recent_articles -->
<tr>
	<td>
	<b><a href="{recent_articles.U_ARTICLE}" class="gensmall">{recent_articles.TITLE}</a></b><br />
	<span class="gensmall">{L_BY}&nbsp;{recent_articles.AUTHOR}&nbsp;{L_ON} {recent_articles.DATE}</span>
	</td>
	</tr>
<!-- END recent_articles -->
<!-- END articles_static -->
</table>