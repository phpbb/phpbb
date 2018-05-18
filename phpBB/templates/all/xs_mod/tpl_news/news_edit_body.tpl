<script type="text/javascript">
function openAllSmiles()
{
	height = screen.height / 1.5;
	width = screen.width / 1.7;
	smiles = window.open('{U_MORE_SMILIES}','_phpbbsmilies','height=' + height + ',width=' + width + ',resizable=yes,scrollbars=yes');
	smiles.focus();
	return false;
}
</script>

<h1>{L_PAGE_TITLE}</h1>
<p>{L_PAGE_TITLE_EXPLAIN}</p>

<form name="post" action="{S_FORUM_ACTION}" method="post">
<table class="forumline">
<tr><th colspan="2">{L_NEWS_SETTINGS}</th></tr>
<tr>
	<td class="row1 tdalignr"><strong>{L_NEWS_DATE}:</strong>&nbsp;</td>
	<td class="row2"><input type="text" name="news_date" size="15" value="{NEWS_DATE}">&nbsp;<strong>{NEWS_DATE_EXPLAIN}</strong></td>
</tr>
<tr>
	<td class="row1" align="right" valign="top"><strong>{L_NEWS_ITEM}:</strong>&nbsp;</td>
	<td class="row2"><textarea rows="4" cols="25" style="width:450px" tabindex="3" name="message" class="post">{NEWS_ITEM}</textarea></td>
</tr>
<tr>
	<td class="row1 tdalignr"><strong>{L_NEWS_DISPLAY}:</strong>&nbsp;</td>
	<td class="row2"><input type="radio" name="news_display" value="1" {NEWS_DISPLAY_YES} /> {L_YES}&nbsp;&nbsp;&nbsp;<input type="radio" name="news_display" value="0" {NEWS_DISPLAY_NO} /> {L_NO}</td>
</tr>
<tr>
	<td class="row1 tdalignr"><strong>{L_NEWS_SMILIES}:</strong>&nbsp;</td>
	<td class="row2"><input type="radio" name="news_smilies" value="1" {NEWS_SMILIES_YES} /> {L_YES}&nbsp;&nbsp;&nbsp;<input type="radio" name="news_smilies" value="0" {NEWS_SMILIES_NO} /> {L_NO} &nbsp;&nbsp;<input type="button" class="liteoption" name="smiles_button" value="{L_ALL_SMILIES}" onclick="openAllSmiles();"></td>	</tr>
<tr><td class="cat tdalignc" colspan="2">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{S_SUBMIT_VALUE}" class="mainoption" /></td></tr>
</table>
</form>
<br clear="all" />
