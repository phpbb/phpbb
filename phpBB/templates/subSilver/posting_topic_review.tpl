
<!-- BEGIN switch_inline_mode -->
<table border="0" cellpadding="3" cellspacing="1" width="100%" class="forumline">
	<tr> 
	  <th class="thTop" height="25"><b>{L_TOPIC_REVIEW}</b></th>
	</tr>
	<tr>
		<td><iframe width="100%" height="300" src="{U_REVIEW_TOPIC}">
<!-- END switch_inline_mode -->

<table border="0" cellpadding="3" cellspacing="1" width="100%" class="forumline">
	<tr>
		<th class="thLeft" width="22%" height="26">{L_AUTHOR}</th>
		<th class="thRight">{L_MESSAGE}</th>
	</tr>
	<!-- BEGIN postrow -->
	<tr>
		<td width="22%" align="left" valign="top" class="{postrow.ROW_CLASS}"><span class="name"><a name="{postrow.U_POST_ID}"></a><b>{postrow.POSTER_NAME}</b></span></td>
		<td class="{postrow.ROW_CLASS}" height="28" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr> 
				<td width="100%">{postrow.MINI_POST_IMG}<span class="postdetails">{L_POSTED}: {postrow.POST_DATE}<span class="gen">&nbsp;</span>&nbsp;&nbsp;&nbsp;{L_POST_SUBJECT}: {postrow.POST_SUBJECT}</span></td>
			</tr>
			<tr> 
				<td colspan="2"><hr /></td>
			</tr>
			<tr> 
				<td colspan="2"><span class="postbody">{postrow.MESSAGE}</span></td>
			</tr>
		</table></td>
	</tr>
	<tr> 
		<td colspan="2" height="1" class="spaceRow"><img src="templates/subSilver/images/spacer.gif" alt="" width="1" height="1" /></td>
	</tr>
	 <!-- END postrow -->
</table>

<!-- BEGIN switch_inline_mode -->
		</iframe></td>
	</tr>
</table>
<!-- END switch_inline_mode -->
