
<form action="{S_MODCP_ACTION}" method="post">
  <table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
	<tr> 
	  <td align="left" class="nav"><a href="{U_INDEX}" class="nav">{SITENAME}&nbsp;{L_INDEX}</a></td>
	</tr>
  </table>
  <table width="100%" cellpadding="4" cellspacing="1" border="0" class="forumline">
	<tr> 
	  <th height="25" class="thHead"><b>{MESSAGE_TITLE}</b></th>
	</tr>
	<tr> 
	  <td class="row1"> 
		<table width="100%" border="0" cellspacing="0" cellpadding="1">
		  <tr> 
			<td>&nbsp;</td>
		  </tr>
		  <tr> 
			<td align="center"><span class="gen">{L_MOVE_TO_FORUM} &nbsp; {S_FORUM_BOX}<br />
			  <br />
			  {MESSAGE_TEXT}</span><br />
			  <br />
			  {S_HIDDEN_FIELDS} 
			  <input type="submit" name="confirm" value="{L_YES}" />
			  &nbsp;&nbsp; 
			  <input type="submit" name="cancel" value="{L_NO}" />
			</td>
		  </tr>
		  <tr> 
			<td>&nbsp;</td>
		  </tr>
		</table>
	  </td>
	</tr>
  </table>
</form>
