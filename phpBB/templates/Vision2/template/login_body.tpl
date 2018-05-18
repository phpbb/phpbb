 
<form action="{S_LOGIN_ACTION}" method="post" target="_top">

<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
	<tr> 
		<td align="left" class="nav"><a href="{U_INDEX}" class="nav">{L_INDEX}</a></td>
	</tr>
</table>

<table width="100%" cellpadding="4" cellspacing="1" border="0" align="center">
  	<tr> 
		<th height="25" class="thHead" nowrap="nowrap">{L_ENTER_PASSWORD}</th>
  	</tr>
  	<tr> 
		<td>
			<table border="0" cellpadding="0" cellspacing="0" width="100%" class="bg">
    			<tr>
        			<td width="11" height="8"><img src="templates/Vision2/images/1.gif" width="11" height="8" border="0"></td>
        			<td></td>
        			<td width="11" height="8"><img src="templates/Vision2/images/2.gif" width="11" height="8" border="0"></td>
        		</tr>
        		<tr>
        		    <td></td>
        		    <td>
<table border="0" cellpadding="3" cellspacing="1" width="100%">
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
		</table>

        		    </td>
        		    <td width="11" height="8" background="templates/Vision2/images/6.gif" width="11" height="8" border="0"></td>
        		</tr>
    			<tr>
        			<td width="11" height="8"><img src="templates/Vision2/images/3.gif" width="11" height="8" border="0"></td>
        			<td background="templates/Vision2/images/5.gif"></td>
        			<td width="11" height="8"><img src="templates/Vision2/images/4.gif" width="11" height="8" border="0"></td>
        		</tr>
</table>
</td></tr></table>

</form>
