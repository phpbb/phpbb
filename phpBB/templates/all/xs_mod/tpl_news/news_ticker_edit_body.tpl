<h3>{L_PAGE_TITLE}</h3>
<p>{L_PAGE_TITLE_EXPLAIN}</p>

<form name="news" action="{S_FORUM_ACTION}" method="post">
<table class="forumline">
<tr><th colspan="2">{L_XS_NEWS_TICKER_SETTINGS}</th></tr>
<tr>
	<td class="row1"><strong>{L_XS_NEWS_TICKER_TITLE}</strong><br /><span class="gensmall">{L_XS_NEWS_TICKER_TITLE_EXPLAIN}</span></td>
	<td class="row2"><input class="post" type="text" maxlength="255" size="50" name="xml_title" value="{XS_NEWS_TICKER_TITLE}" /></td>
</tr>
<tr>
	<td class="row1"><strong>{L_XS_NEWS_TICKER_SHOW}</strong></td>
	<td class="row2"><input type="radio" name="xml_show" value="1" {XS_SHOWTICKER_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="xml_show" value="0" {XS_SHOWTICKER_NO} /> {L_NO}</td>
</tr>
<tr> 
	<td class="row1" align="left" valign="top"><strong>{L_XS_NEWS_TICKER_FEED}</strong><br /><span class="gensmall">{L_XS_NEWS_TICKER_FEED_EXPLAIN}</span></td>
	<td class="row2"><textarea rows="4" cols="25" style="width:300px" tabindex="3" name="xml_feed" class="post">{XS_NEWS_TICKER_FEED}</textarea></td>
</tr>
<tr>
	<td class="row1" align="left" valign="top"><strong>{L_XS_NEWS_TICKER_IS_FEED}</strong><br /><span class="gensmall">{L_XS_NEWS_TICKER_IS_FEED_EXPLAIN}</span></td>
	<td class="row2"><input type="radio" name="xml_is_feed" value="1" {XS_NEWS_ISFEED_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="xml_is_feed" value="0" {XS_NEWS_ISFEED_NO} /> {L_NO}</td>
</tr>
<tr>
	<td class="row1"><strong>{L_XS_NEWS_TICKER_FONTSIZE}</strong><br /><span class="gensmall">{L_XS_NEWS_TICKER_FONTSIZE_EXPLAIN}</span></td>
	<td class="row2"><input class="post" type="text" maxlength="4" size="3" name="xml_font" value="{XS_NEWS_TICKER_FONTSIZE}" /></td>
</tr>
<tr>
	<td class="row1"><strong>{L_XS_NEWS_TICKER_SS}</strong><br /><span class="gensmall">{L_XS_NEWS_TICKER_SS_EXPLAIN}</span></td>
	<td class="row2"><input class="post" type="text" maxlength="4" size="3" name="xml_speed" value="{XS_NEWS_TICKER_SS}" /></td>
</tr>
<tr>
	<td class="row1"><strong>{L_XS_NEWS_TICKER_SD}</strong></td>
	<td class="row2"><input type="radio" name="xml_direction" value="0" {XS_NEWS_TICKER_SD_LEFT} /> {L_XS_LEFT}&nbsp;&nbsp;<input type="radio" name="xml_direction" value="1" {XS_NEWS_TICKER_SD_RIGHT} /> {L_XS_RIGHT}</td>
</tr>
<tr><td class="cat" colspan="2">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{S_SUBMIT_VALUE}" class="mainoption" /></td></tr>
</table>
</form>
<br clear="all" />
