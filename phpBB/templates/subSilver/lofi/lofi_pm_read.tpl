<!-- INCLUDE ../common/lofi/lofi_header.tpl -->

<table>
<tr>
<td class="tvalignm"><span class="cattitle">{INBOX} &nbsp;</span></td>
<td class="tvalignm"><span class="cattitle">{SENTBOX} &nbsp;</span></td>
<td class="tvalignm"><span class="cattitle">{OUTBOX} &nbsp;</span></td>
<td class="tvalignm"><span class="cattitle">{SAVEBOX}</span></td>
</tr>
</table>
<b>{BOX_NAME} :: {L_MESSAGE}</b>

<!-- BEGIN postrow -->
<div class="postwrapper">
<div class="posttopbar">
	<div class="postname">{postrow.POSTER_NAME}<br /></div>
	<div class="postedit">{postrow.QUOTE}</span> {postrow.EDIT} {postrow.DELETE} {postrow.IP}</div>
	<div class="postinfo">{postrow.POSTER_POSTS} {postrow.POSTER_FROM}<br /></div>
	<div class="postdate">{postrow.POST_DATE}</div>
</div>
<span class="desc">{L_SUBJECT}: {postrow.POST_SUBJECT}</span>
</div>
<!-- END postrow -->

<div class="index">
<a href="{U_POST_REPLY_TOPIC}" class="nav">{L_POST_REPLY_TOPIC}</a><br /><br />
	<div class="postwrapper">
	<div class="posttopbar">
		<div class="postname">{L_FROM}: {MESSAGE_FROM}<br /></div>
		<div class="postedit"> {QUOTE_PM} {EDIT_PM}</span></div>
		<div class="postinfo">{L_SUBJECT}: {POST_SUBJECT}<br /></div>
		<div class="postdate">{POST_DATE}</div>
	</div>
	<div class="postcontent">{MESSAGE}</div>
</div>
</ul>
{REPLY_PM} <div align="right"><span class="desc">{S_TIMEZONE}</span></div>
<br />
	<div>
	{S_HIDDEN_FIELDS}
		<input type="submit" name="save" value="{L_SAVE_MSG}" class="liteoption" />
		&nbsp;
		<input type="submit" name="delete" value="{L_DELETE_MSG}" class="liteoption" />
		<div align="right">{JUMPBOX}</div>
	</div>


</div><br />

<!-- INCLUDE ../common/lofi/lofi_footer.tpl -->