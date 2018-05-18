<h3>{L_PAGE_TITLE}</h3>
<p>{L_PAGE_TITLE_EXPLAIN}</p>

<form name="news" action="{S_FORUM_ACTION}" method="post">
<table class="forumline">
<tr><th colspan="2">{L_XS_NEWS_SETTINGS}</th></tr>
<tr>
	<td class="row1"><strong>{L_XS_SHOW_NEWS}</strong></td>
	<td class="row2"><input type="radio" name="xs_show_news" value="1" {XS_SHOWNEWS_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="xs_show_news" value="0" {XS_SHOWNEWS_NO} /> {L_NO}</td>
</tr>
<tr>
	<td class="row1"><strong>{L_XS_SHOW_NEWS_SUBTITLE}</strong><br /><span class="gensmall">{L_XS_SHOW_NEWS_SUBTITLE_EXPLAIN}</span></td>
	<td class="row2"><input type="radio" name="xs_show_news_subtitle" value="1" {XS_SHOWNEWS_SUBT_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="xs_show_news_subtitle" value="0" {XS_SHOWNEWS_SUBT_NO} /> {L_NO}</td>
</tr>
<tr>
	<td class="row1"><strong>{L_XS_NEWS_DATEFORMAT}</strong></td>
	<td class="row2">{XS_NEWS_DATEFORMAT}</td>
</tr>
<tr>
	<td class="row1"><strong>{L_XS_SHOW_TICKER}</strong><br /><span class="gensmall">{L_XS_SHOW_TICKER_EXPLAIN}</span></td>
	<td class="row2"><input type="radio" name="xs_show_ticker" value="1" {XS_SHOWTICKER_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="xs_show_ticker" value="0" {XS_SHOWTICKER_NO} /> {L_NO}</td>
</tr>
<tr>
	<td class="row1"><strong>{L_XS_SHOW_TICKER_SUBTITLE}</strong><br /><span class="gensmall">{L_XS_SHOW_TICKER_SUBTITLE_EXPLAIN}</span></td>
	<td class="row2"><input type="radio" name="xs_show_ticker_subtitle" value="1" {XS_SHOWTICKER_SUBT_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="xs_show_ticker_subtitle" value="0" {XS_SHOWTICKER_SUBT_NO} /> {L_NO}</td>
</tr>
<tr><td class="cat tdalignc" colspan="2">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{S_SUBMIT_VALUE}" class="mainoption" /></td></tr>
</table>
</form>
<br clear="all" />
