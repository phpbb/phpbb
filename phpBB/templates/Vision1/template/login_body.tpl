 
<form action="{S_LOGIN_ACTION}" method="post" target="_top">

<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
  <tr> 
	<td align="left" class="nav"><a href="{U_INDEX}" class="nav">{L_INDEX}</a></td>
  </tr>
</table>

<table width="100%" cellpadding="4" cellspacing="0" border="0" class="forumline" align="center">
  <tr> 
	<th height="25" class="thHead" nowrap="nowrap">{L_ENTER_PASSWORD}</th>
  </tr>
  <tr> 
	<td class="row1"><table border="0" cellpadding="3" cellspacing="1" width="100%">
		  <tr> 
			<td colspan="2" align="center">&nbsp;</td>
		  </tr>
		  <tr> 
			<td width="45%" align="right"><span class="gen">{L_USERNAME}:</span></td>
			<td> 
			  <input type="text" class="post" name="username" size="25" maxlength="40" value="{USERNAME}" />
			</td>
		  </tr>
		  <tr> 
			<td align="right"><span class="gen">{L_PASSWORD}:</span></td>
			<td> 
			  <input type="password" class="post" name="password" size="25" maxlength="32" />
			</td>
		  </tr>
		  <!-- BEGIN switch_allow_autologin -->
		  <tr align="center"> 
			<td colspan="2"><span class="gen">{L_AUTO_LOGIN}: <input type="checkbox" name="autologin" /></span></td>
		  </tr>
		  <!-- END switch_allow_autologin -->
		  <tr align="center"> 
			<td colspan="2">{S_HIDDEN_FIELDS}<input type="submit" name="login" class="mainoption" value="{L_LOGIN}" /></td>
		  </tr>
		  <tr align="center"> 
			<td colspan="2"><span class="gensmall"><a href="{U_SEND_PASSWORD}" class="gensmall">{L_SEND_PASSWORD}</a></span></td>
		  </tr>
		</table></td>
  </tr>
</table>
<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center">
  <tr>
       <td class="shadow_bottom" align="left"><img src="templates/Vision1/images/shadow_bottom_left.gif" border="0" width="36" height="5"></td>
       <td height="5" class="shadow_bottom" align="right"><img src="templates/Vision1/images/shadow_bottom_right.gif" border="0" width="5" height="5"></td>
  </tr>
</table>
</form>
