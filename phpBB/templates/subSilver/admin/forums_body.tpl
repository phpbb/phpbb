
<h1>{L_FORUM_TITLE}</h1>

<p>{L_FORUM_EXPLAIN}</p>

<form method="post" action="{S_FORUM_ACTION}"><table width="100%" cellpadding="4" cellspacing="1" border="0" class="forumline" align="center">
	<!-- BEGIN catrow -->
	<tr> 
	  <td class="cat" colspan="3"><span class="cattitle"><b><a href="{catrow.U_VIEWCAT}">{catrow.CAT_DESC}</a></b></span>{catrow.S_ADDCAT}</td>
	  <td class="cat" align="center" valign="middle"><span class="gen">{catrow.CAT_EDIT}</span></td>
	  <td class="cat" align="center" valign="middle"><span class="gen">{catrow.CAT_DELETE}</span></td>
	  <td class="cat" align="center" valign="middle" nowrap="nowrap"><span class="gen">{catrow.CAT_UP} 
		{catrow.CAT_DOWN}</span></td>
	  <td class="cat" align="center" valign="middle"><span class="gen">&nbsp</span></td>
	</tr>
	{catrow.S_ADDCAT_END_FORM} 
	<!-- BEGIN forumrow -->
	<tr> 
	  <td class="row2"><span class="gen">{catrow.forumrow.S_ADD_FORUM}{catrow.forumrow.S_NEW_FORUM}<a href="{catrow.forumrow.U_VIEWFORUM}" target="_new">{catrow.forumrow.FORUM_NAME}</a></span><br />
		<span class="gensmall">{catrow.forumrow.FORUM_DESC}</span></td>
	  {catrow.forumrow.S_ADD_FORUM_END_FORM} 
	  <td class="row1" align="center" valign="middle"><span class="gen">{catrow.forumrow.NUM_TOPICS}</span></td>
	  <td class="row2" align="center" valign="middle"><span class="gen">{catrow.forumrow.NUM_POSTS}</span></td>
	  <td class="row1" align="center" valign="middle"><span class="gen">{catrow.forumrow.FORUM_EDIT}</span></td>
	  <td class="row2" align="center" valign="middle"><span class="gen">{catrow.forumrow.FORUM_DELETE}</span></td>
	  <td class="row1" align="center" valign="middle"><span class="gen">{catrow.forumrow.FORUM_UP} <br />
		{catrow.forumrow.FORUM_DOWN}</span></td>
	  <td class="row2" align="center" valign="middle"><span class="gen">{catrow.forumrow.FORUM_SYNC}</span></td>
	</tr>
	<!-- END forumrow -->
	<!-- END catrow -->
</table></form>
