
<script language="Javascript" type="text/javascript">
<!--
	//
	// Should really check the browser to stop this whining ...
	//
	function select_switch(status)
	{
		for (i = 0; i < document.split_list.length; i++)
		{
			document.split_list.elements[i].checked = status;
		}
	}
//-->
</script>

<form action="{S_SPLIT_ACTION}" method="post" name="split_list">

<table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left" valign="bottom" nowrap><span class="gensmall"><a href="{U_INDEX}">{L_INDEX}</a> -> <a href="{U_VIEW_FORUM}">{FORUM_NAME}</a></span></td>
	</tr> 
</table>

<table width="98%" cellspacing="0" cellpadding="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table width="100%" cellspacing="1" cellpadding="4" border="0">
			<tr>
				<td class="cat" colspan="3" align="center"><span class="cattitle">{L_SPLIT_TOPIC}</span><br /><span class="gensmall">{L_SPLIT_TOPIC_EXPLAIN}</span></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_SPLIT_SUBJECT}</span></td>
				<td class="row2" colspan="2"><span class="courier"><input type="text" size="50" maxlength="100" name="subject"></span></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_SPLIT_FORUM}</span></td>
				<td class="row2" colspan="2"><span class="courier">{S_FORUM_SELECT}</span></td>
			</tr>
			<tr>
				<td class="cat" colspan="3" height="30" align="center"><input class="liteoptiontable" type="submit" name="split_type_all" value="{L_SPLIT_POSTS}">&nbsp;<input class="liteoptiontable" type="submit" name="split_type_beyond" value="{L_SPLIT_AFTER}"></td>
			</tr>
			<tr>
				<th width="160" height="25"><table width="160" cellspacing="0" cellpadding="0" border="0"> 
	                <tr>
               			<th>{L_AUTHOR}</th>
					</tr>
				</table></th>
				<th width="100%" height="25"><table width="100%" cellspacing="0" cellpadding="0" border="0"> 
	                <tr>
               			<th width="95%">{L_MESSAGE}</th>
					</tr>
				</table></th>
			</tr>
		</table></td>
	</tr>
	<tr>
		<td class="cat" height="2"><img src="images/spacer.gif" height="2"></td>
	</tr>
</table>

<!-- BEGIN postrow -->
<table width="98%" cellpadding="0" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table border="0" cellpadding="4" cellspacing="1" width="100%">
			<tr bgcolor="{postrow.ROW_COLOR}">
				<td width="160" align="left" valign="top"><a name="{postrow.U_POST_ID}"></a><table width="160" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td valign="top"><span class="gen"><b>{postrow.POSTER_NAME}</b></span></td>
					</tr>
				</table></td>
				<td width="100%" valign="top"><table width="100%" cellspacing="1" cellpadding="0" border="0">
					<tr>
						<td valign="top"><table width="100%" cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td valign="middle"><span class="gensmall">{L_POST_SUBJECT}: {postrow.POST_SUBJECT}</span></td>
							</tr>
						</table></td>
					</tr>
					<tr>
						<td width="100%" height="100%" valign="top"><hr /><span class="gen">{postrow.MESSAGE}</span></td>
					</tr>
				</table></td>
			</tr>
			<tr bgcolor="{postrow.ROW_COLOR}">
				<td align="left" valign="middle"><table cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td valign="middle"><a href="#top"><img src="templates/Euclid/images/topic.gif" border="0" alt="" /></a></td>
						<td>&nbsp;&nbsp;</td>
						<td valign="middle"><span class="gensmall">{postrow.POST_DATE}</span></td>
					</tr>
				</table></td>
				<td valign="middle"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td align="right" valign="middle"><span class="gen">{L_SELECT}: <input type="checkbox" name="post_id_list[]" value="{postrow.POST_ID}" /></span></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
	<tr>
		<td class="cat" height="2"><img src="images/spacer.gif" height="2"></td>
	</tr>
</table>
<!-- END postrow -->

<table width="98%" cellspacing="0" cellpadding="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table width="100%" cellspacing="1" cellpadding="4" border="0">
			<tr>
				<td class="cat" colspan="3" height="30" align="center"><input class="liteoptiontable" type="submit" name="split_type_all" value="{L_SPLIT_POSTS}">&nbsp;<input class="liteoptiontable" type="submit" name="split_type_beyond" value="{L_SPLIT_AFTER}"></td>
			</tr>
		</table></td>
	</tr>
</table>

<table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="right"><span class="gensmall"><a href="javascript:select_switch(true);">{L_MARK_ALL}</a> :: <a href="javascript:select_switch(false);">{L_UNMARK_ALL}</a></span></td>
	</tr>
</table>

<table cellspacing="2" border="0" width="98%" align="center">
	<tr>
		<td width="40%"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
	</tr>
</table>

{S_HIDDEN_FIELDS}

</form>
