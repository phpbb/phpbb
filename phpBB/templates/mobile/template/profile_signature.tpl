<!-- INCLUDE overall_header.tpl -->

<!-- INCLUDE profile_cpl_menu_inc_start.tpl -->

<!-- BEGIN switch_current_sig -->
<form method="post" action="{SIG_LINK}" name="post">
{IMG_THL}{IMG_THC}<span class="forumlink">{SIG_CURRENT}</span>{IMG_THR}<table class="forumlinenb">
<tr><td class="row2" width="100%" valign="bottom"><span class="gen">{CURRENT_PREVIEW}</span><br /><br /></td></tr>
<tr><td class="cat">&nbsp;</td></tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}

<br />

{IMG_THL}{IMG_THC}<span class="forumlink">{SIG_EDIT}</span>{IMG_THR}<table class="forumlinenb">
<tr>
	<td class="row1 tw130px">
		<span class="gen">{L_SIGNATURE}:</span><br />
		<span class="gensmall">{L_SIGNATURE_EXPLAIN}</span><br /><br />
		<table><tr><td class="tdalignc tvalignm"><br />{BBCB_SMILEYS_MG}</td></tr></table>
	</td>
	<td class="row2 tw550px">
		<table class="p2px tw450px">
			<tr>
				<td colspan="9">
					{BBCB_MG}
					<div class="message-box"><textarea id="message" name="message" rows="15" cols="76" tabindex="3" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);">{SIGNATURE}</textarea></div>
				</td>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<td class="row1 tw130px">&nbsp;</td>
	<td class="row2 tw550px">
		<input type="button" class="liteoption" value="{L_PROFILE}" onclick="location='{U_PROFILE}'">
		<input type="button" class="liteoption" value="{SIG_CURRENT}" onclick="location='{SIG_LINK}'">
		<input type="submit" class="liteoption" value="{SIG_PREVIEW}" name="preview">
		<input type="submit" class="mainoption" value="{SIG_SAVE}" name="save">
		<input type="submit" class="liteoption" value="{SIG_CANCEL}" name="cancel">
	</td>
</tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}
</form>
<!-- END switch_current_sig -->

<!-- BEGIN switch_preview_sig -->
<form method="post" action="{SIG_LINK}" name="post">
{IMG_THL}{IMG_THC}<span class="forumlink">{SIG_PREVIEW}</span>{IMG_THR}<table class="forumlinenb">
<tr>
	<td class="row1" width="140" height="140"><span class="gen">{L_SIGNATURE}:</span></td>
	<td class="row2" width="520" valign="bottom"><span class="gen">{REAL_PREVIEW}</span><br /><br /></td>
</tr>
<tr><td class="cat" colspan="2">&nbsp;</td></tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}

<br />
{IMG_THL}{IMG_THC}<span class="forumlink">{SIG_EDIT}</span>{IMG_THR}<table class="forumlinenb">
<tr>
	<td class="row1 tw130px">
		<span class="gen">{L_SIGNATURE}:</span><br />
		<span class="gensmall">{L_SIGNATURE_EXPLAIN}</span><br /><br />
		<table><tr><td class="tdalignc tvalignm"><br />{BBCB_SMILEYS_MG}</td></tr></table>
	</td>
	<td class="row2 tw550px">
		<table class="p2px tw450px">
			<tr>
				<td colspan="9">
					{BBCB_MG}
					<div class="message-box"><textarea id="message" name="message" rows="15" cols="76" tabindex="3" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);">{SIGNATURE}</textarea></div>
				</td>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<td class="row1 tw130px">&nbsp;</td>
	<td class="row2 tw550px">
		<input type="button" class="liteoption" value="{L_PROFILE}" onclick="location='{U_PROFILE}'" />
		<input type="button" class="liteoption" value="{SIG_CURRENT}" onclick="location='{SIG_LINK}'" />
		<input type="submit" class="liteoption" value="{SIG_PREVIEW}" name="preview" />
		<input type="submit" class="mainoption" value="{SIG_SAVE}" name="save" />
		<input type="submit" class="liteoption" value="{SIG_CANCEL}" name="cancel" />
	</td>
</tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}
</form>
<!-- END switch_preview_sig -->

<!-- BEGIN switch_save_sig -->
<form method="post" action="{SIG_LINK}" name="preview">
{IMG_THL}{IMG_THC}<span class="forumlink">{SIG_SAVE}</span>{IMG_THR}<table class="forumlinenb">
<tr><td class="row1 row-center tvalignm" height="100"><span class="gen">{SAVE_MESSAGE}</span></td></tr>
<tr>
	<td class="row2 row-center">
		<input type="button" class="liteoption" value="{L_PROFILE}" onclick="location='{U_PROFILE}'" />
		<input type="button" class="liteoption" value="{SIG_CURRENT}" onclick="location='{SIG_LINK}'" />
		<input type="submit" class="liteoption" value="{SIG_CANCEL}" name="cancel" />
	</td>
</tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}
</form>
<!-- END switch_save_sig -->

<!-- INCLUDE profile_cpl_menu_inc_end.tpl -->

<!-- INCLUDE overall_footer.tpl -->