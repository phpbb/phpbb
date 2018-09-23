{ERROR_BOX}

<!-- IF SOCIAL_CONNECT -->
<div class="genmed" style="text-align: left;">
{L_SOCIAL_CONNECT}&nbsp;
<!-- BEGIN social_connect_button -->
<a href="{social_connect_button.U_SOCIAL_CONNECT}" title="{social_connect_button.L_SOCIAL_CONNECT}">{social_connect_button.IMG_SOCIAL_CONNECT}</a>
<!-- END social_connect_button -->
</div>
<br class="clear" /><br />
<!-- ENDIF -->

<form method="post" action="{S_LANG_CHANGE_ACTION}"><div style="text-align: left;">{L_SELECT_LANG}:&nbsp;{LANGUAGE_SELECT}&nbsp;&nbsp;<input type="submit" name="lang_change" value="{L_GO}" class="mainoption" /></div></form>

<form method="post" action="{S_AGREE_ACTION}">
{IMG_THL}{IMG_THC}<span class="forumlink">{SITENAME} - {REGISTRATION}</span>{IMG_THR}<table class="forumlinenb">
<tr><td class="row1"><div class="post-text">{AGREEMENT}</div><br class="clear" /><br /><br /></td></tr>
<tr><td class="row1"><div class="post-text"><b>{L_PRIVACY_DISCLAIMER}</b></div><br class="clear" /><br /><label>&nbsp;<input type="checkbox" name="privacy" />&nbsp;<i>{AGREE_CHECKBOX}</i></label><br class="clear" /><br /></td></tr>
<tr><td class="cat">{S_HIDDEN_FIELDS}<input type="submit" name="not_agreed" value="{DO_NOT_AGREE}" class="liteoption" /><input type="submit" name="agreed" value="{AGREE_OVER_13}" class="mainoption" /></td></tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}
</form>
