<br />
<b>{L_NOTICE}</b>
<br />

<h1>{L_EMAIL_TITLE}</h1>

<p>{L_EMAIL_EXPLAIN}</p>
<form method="post" action="{S_USER_ACTION}">
  <table cellspacing="1" cellpadding="4" border="0" align="center" class="forumline">
	<tr> 
	  <th class="thHead" colspan="2">{L_COMPOSE}</th>
	</tr>
	<tr> 
	  <td class="row1" align="right"><b>{L_GROUP_SELECT}</b></td>
	  <td class="row2" align="left">{S_GROUP_SELECT}</td>
	</tr>
	<tr> 
	  <td class="row1" align="right"><b>{L_EMAIL_SUBJECT}</b></td>
	  <td class="row2"><span class="gen"> 
		<input type="text" name="{S_EMAIL_SUBJECT}{S_EMAIL_SUBJECT}" size="45" maxlength="100" style="width:450px" tabindex="2" class="post" />
		</span></td>
	</tr>
	<tr> 
	  <td class="row1" align="right" valign="top"> <span class="gen"><b>{L_EMAIL_MSG}</b></span> 
	  <td class="row2"><span class="gen"> 
		<textarea name="{S_EMAIL_MSG}" rows="15" cols="35" wrap="virtual" style="width:450px" tabindex="3" class="post"></textarea>
		</span> 
	</tr>
	<tr> 
	  <td class="catBottom" align="center" colspan="2"> 
		<input type="submit" value="{L_EMAIL}" name="submit" class="mainoption" />
		&nbsp;</td>
	</tr>
  </table>
</form>
<br />
