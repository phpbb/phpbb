
<h1>{L_DISALLOW_TITLE}</h1>
<p>{L_EXPLAIN}</p>

<form method="post" action="{S_FORM_ACTION}"><table width="80%" cellspacing="1" cellpadding="4" border="0" align="center" class="forumline">
	<tr> 
	  <th class="thHead" colspan="2">{L_DEL_DISALLOW}</th>
	</tr>
	<tr> 
	  <td class="row1">{L_USERNAME}: <br />
		<span class="gensmall">{L_DEL_EXPLAIN}</span></td>
	  <td class="row2">{S_DISALLOW_SELECT}&nbsp;<input type="submit" name="mode" value="{L_DELETE}" class="liteoption" /></td>
	</tr>
	<tr> 
	  <th class="thHead" colspan="2">{L_ADD_DISALLOW}</th>
	</tr>
	<tr> 
	  <td class="row1">{L_USERNAME}: <br />
		<span class="gensmall">{L_ADD_EXPLAIN}></td>
	  <td class="row2">
		<input type="text" name="disallowed_user" size="35" />
	  </td>
	</tr>
	<tr> 
	  <td class="catBottom" colspan="2" align="center">
		<input type="submit" name="mode" value="{L_ADD}" class="mainoption" />
		&nbsp;&nbsp;
		<input type="reset" value="{L_RESET}" class="liteoption" />
	  </td>
	</tr>
</table></form>

<p>{L_INFO}</p>
