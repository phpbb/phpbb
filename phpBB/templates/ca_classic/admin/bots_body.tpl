
<h1>{L_BOTS_TITLE}</h1>

<p>{L_BOTS_EXPLAIN}</p>

<form action="{S_BOTS_ACTION}" method="post"><table width="90%" cellpadding="4" cellspacing="1" border="0" align="center" class="forumline">

	<tr>
		<th nowrap="nowrap">{L_BOT_NAME}</th>
		<th nowrap="nowrap">{L_BOT_PAGES}</th>
		<th nowrap="nowrap">{L_BOT_VISITS}</th>
		<th nowrap="nowrap">{L_BOT_LAST_VISIT}</th>
		<th colspan="2" nowrap="nowrap">{L_BOT_OPTIONS}</th>
		<th nowrap="nowrap">{L_BOT_MARK}</th>
	</tr>

	<!-- BEGIN botrow -->

	<tr>
		<td class="{botrow.ROW_CLASS}" width="50%">{botrow.BOT_NAME}</td>
		<td class="{botrow.ROW_CLASS}" width="10%" align="center" nowrap="nowrap">{botrow.PAGES}</td>
		<td class="{botrow.ROW_CLASS}" width="10%" align="center" nowrap="nowrap">{botrow.VISITS}</td>
		<td class="{botrow.ROW_CLASS}" width="20%" align="center" nowrap="nowrap">{botrow.LAST_VISIT}</td>
		<td class="{botrow.ROW_CLASS}" width="3%" align="center">&nbsp;<a href="{S_BOTS_ACTION}&id={botrow.ROW_NUMBER}&action=edit">{L_BOT_EDIT}</a>&nbsp;</td>
		<td class="{botrow.ROW_CLASS}" width="3%" align="center">&nbsp;<a href="{S_BOTS_ACTION}&id={botrow.ROW_NUMBER}&action=delete">{L_BOT_DELETE}</a>&nbsp;</td>
		<td class="{botrow.ROW_CLASS}" width="3%" align="center"><input type="checkbox" name="mark[]" value="{botrow.ROW_NUMBER}" /></td>	
	</tr>

	<!-- END botrow -->

	<!-- BEGIN nobotrow -->

	<tr>
		<td class="row2" align="center" colspan="8"><br />{nobotrow.NO_BOTS}<br /><br /></td>
	</tr>

	<!-- END nobotrow -->

	<tr>
		<td class="cat" colspan="8"><table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td><input type="submit" class="liteoption" name="add" value="{L_BOT_ADD}" /></td>
				<td align="right"><select name="action"><option value="edit">{L_BOT_EDIT}</option><option value="delete">{L_BOT_DELETE}</option></select> <input type="submit" class="liteoption" name="submit" value="{L_BOT_SUBMIT}" /></td>
			</tr>
		</table></td>
	</tr>

</table></form>

<br />

<h1>{L_BOTS_TITLE_PENDING}</h1>

<p>{L_BOTS_EXPLAIN_PENDING}</p>

<table width="90%" cellpadding="4" cellspacing="1" border="0" align="center" class="forumline">

	<tr>
		<th nowrap="nowrap">{L_BOT_NAME}</th>
		<th nowrap="nowrap">{L_BOT_IP}</th>
		<th nowrap="nowrap">{L_BOT_AGENT}</th>
		<th colspan="2" nowrap="nowrap">{L_BOT_OPTIONS}</th>
	</tr>

	<!-- BEGIN pendingrow -->

	<tr>
		<td class="{pendingrow.ROW_CLASS}" width="30%">{pendingrow.BOT_NAME}</td>
		<td class="{pendingrow.ROW_CLASS}" width="20%" align="center" nowrap="nowrap">{pendingrow.IP}</td>
		<td class="{pendingrow.ROW_CLASS}" width="20%" align="center" nowrap="nowrap">{pendingrow.AGENT}</td>
		<td class="{pendingrow.ROW_CLASS}" width="3%" align="center">&nbsp;<a href="{S_BOTS_ACTION}&id={pendingrow.ROW_NUMBER}&pending={pendingrow.PENDING_NUMBER}&data={pendingrow.PENDING_DATA}&action=ignore_pending">{L_BOT_IGNORE}</a>&nbsp;</td>
		<td class="{pendingrow.ROW_CLASS}" width="3%" align="center">&nbsp;<a href="{S_BOTS_ACTION}&id={pendingrow.ROW_NUMBER}&pending={pendingrow.PENDING_NUMBER}&data={pendingrow.PENDING_DATA}&action=add_pending">{L_BOT_ADD}</a>&nbsp;</td>
	</tr>

	<!-- END pendingrow -->

	<!-- BEGIN nopendingrow -->

	<tr>
		<td class="row2" align="center" colspan="5"><br />{nopendingrow.NO_BOTS}<br /><br /></td>
	</tr>

	<!-- END nopendingrow -->

</table>

<br clear="all" />
</table>

<br clear="all" />
