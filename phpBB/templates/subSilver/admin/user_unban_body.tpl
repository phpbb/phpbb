 <br />
<h1>{L_BAN_TITLE}</h1>
<p>{L_BAN_EXPLAIN}</p>
<form method="post" action="{S_BAN_ACTION}">
  <table width="80%" cellspacing="1" cellpadding="4" border="0" align="center" class="forumline">
	<tr> 
	  <th class="thHead" colspan="2">{L_BAN_USER}</th>
	</tr>
	<tr> 
	  <td class="row1">{L_USERNAME}:&nbsp;<br />
		<span class="gensmall">{L_BAN_USER_EXPLAIN}</span></td>
	  <td class="row2">{S_USERLIST_SELECT}</td>
	</tr>
	<tr> 
	  <td class="catSides" colspan="2" align="center"><span class="cattitle">{L_BAN_IP}</span></td>
	</tr>
	<tr> 
	  <td class="row1">{L_IP_OR_HOSTNAME}:&nbsp;<br />
		<span class="gensmall">{L_BAN_IP_EXPLAIN}</span></td>
	  <td class="row2">{S_IPLIST_SELECT}</td>
	</tr>
	<tr> 
	  <td class="catSides" colspan="2" align="center"><span class="cattitle">{L_BAN_EMAIL}</span></td>
	</tr>
	<tr> 
	  <td class="row1">{L_EMAIL_ADDRESS}:&nbsp;<br />
		<span class="gensmall">{L_BAN_EMAIL_EXPLAIN}</span></td>
	  <td class="row2">{S_EMAILLIST_SELECT}</td>
	</tr>
	<tr> 
	  <td class="catBottom" colspan="2" align="center">{S_HIDDEN_FIELDS}
		<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />
		&nbsp;&nbsp;
		<input type="reset" value="{L_RESET}" class="liteoption" />
	  </td>
	</tr>
  </table>
</form>
<br	clear="all" />
