<!-- INCLUDE overall_header.tpl -->

{CPL_MENU_OUTPUT}
<form method="post" name="privmsg_list" action="{S_PRIVMSGS_ACTION}">

<div class="block">
	<div class="popup">
		<a href="javascript:void(0);" class="gradient">{L_INBOX}</a>
		<ul class="menu">
			<li>{INBOX}</li>
			<li>{SENTBOX}</li>
			<li>{OUTBOX}</li>
			<li>{SAVEBOX}</li>
		</ul>
	</div>
</div>

<div style="text-align: left;"><span class="img-btn">{POST_PM_IMG}</span></div>

{BOX_NAME}
<!-- BEGIN listrow -->
<div class="forum" onclick="document.location.href='{listrow.U_READ}'; return false;">
	<p><a href="{listrow.U_READ}">{listrow.SUBJECT}</a></p>
	<p>
		{listrow.FROM} | {listrow.DATE}
		<label><input type="checkbox" name="mark[]" value="{listrow.S_MARK_ID}" /></label>
	</p>
</div>
<!-- END listrow -->
<!-- BEGIN switch_no_messages -->
<div class="block">{L_NO_MESSAGES}</div>
<!-- END switch_no_messages -->
<div class="block">
		{S_HIDDEN_FIELDS}
		<input type="submit" name="save" value="{L_SAVE_MARKED}" class="mainoption" style="padding-left: 2px; padding-right: 2px;" />
		<input type="submit" name="download" value="{L_DOWNLOAD_MARKED}" class="altoption" />
		<input type="submit" name="delete" value="{L_DELETE_MARKED}" class="liteoption" style="padding-left: 2px; padding-right: 2px;" />
		<input type="submit" name="deleteall" value="{L_DELETE_ALL}" class="liteoption" style="padding-left: 2px; padding-right: 2px;" />
</div>

<table>
<tr>
	<td><span class="img-btn">{POST_PM_IMG}</span></td>
	<td class="tdalignr tdnw"><span class="gensmall"><!-- IF PAGE_NUMBER -->{PAGE_NUMBER}<!-- ELSE -->&nbsp;<!-- ENDIF --></span><br /><div class="pagination"><!-- IF PAGINATION -->{PAGINATION}<!-- ELSE -->&nbsp;<!-- ENDIF --></div></td>
</tr>
</table>
</form>

<div align="right">{JUMPBOX}</div>

<!-- INCLUDE overall_footer.tpl -->