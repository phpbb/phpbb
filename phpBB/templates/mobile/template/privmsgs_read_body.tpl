<!-- INCLUDE overall_header.tpl -->

{CPL_MENU_OUTPUT}
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

<div class="block-empty">
	{REPLY_PM_IMG} {QUOTE_PM_IMG} {EDIT_PM_IMG}
</div>

<form method="post" action="{S_PRIVMSGS_ACTION}">
{S_HIDDEN_FIELDS}

<div class="block post" style="position: relative;">
	<div class="popup right" style="position: static;">
		{MESSAGE_FROM}
		<div class="block">
			<p class="post-details">
				{POSTER_JOINED}<br />
				{POSTER_POSTS}<br />
			</p>
			<div class="extra-top-padding">
				{PROFILE_IMG} {PM_IMG} {EMAIL_IMG} {WWW_IMG}&nbsp;
			</div>
		</div>
	</div>
	<h2>{POST_SUBJECT}</h2>
	<p class="post-time">{POST_DATE}</p>
			{MESSAGE}<br />
			{ATTACHMENTS}
	<div class="clear"></div>
</div>

<div class="block-empty">
	{REPLY_PM_IMG} {QUOTE_PM_IMG} {EDIT_PM_IMG}<br />
	<input type="submit" name="save" value="{L_SAVE_MSG}" class="liteoption" />&nbsp;&nbsp;
	<input type="submit" name="delete" value="{L_DELETE_MSG}" class="liteoption" />&nbsp;
	<!-- BEGIN switch_attachments -->
	<input type="submit" name="pm_delete_attach" value="{L_DELETE_ATTACHMENTS}" class="liteoption" />
	<!-- END switch_attachments -->
</div>

</form>

<!-- INCLUDE overall_footer.tpl -->