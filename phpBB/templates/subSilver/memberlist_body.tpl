
<form method="post" action="{S_MODE_ACTION}">
  <table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
	<tr> 
	  <td align="left"><span class="nav"><a href="{U_INDEX}" class="nav">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
	  <td align="right" nowrap><span class="genmed">{L_SELECT_SORT_METHOD}:&nbsp;{S_MODE_SELECT}&nbsp;&nbsp;{L_ORDER}&nbsp;{S_ORDER_SELECT}&nbsp;&nbsp; 
		<input type="submit" name="submit" value="{L_SUBMIT}" class="liteoption" />
		</span></td>
	</tr>
  </table>
  <table width="100%" cellspacing="0" cellpadding="2" border="0" align="center">
	<tr> 
	  <td align="left" colspan="2" class="forumline"> 
		<table width="100%" border="0" cellspacing="0" cellpadding="1">
		  <tr> 
			<td class="innerline"> 
			  <table width="100%" cellpadding="3" cellspacing="1" border="0">
				<tr> 
				  <th height="25">&nbsp;</th>
				  <th>{L_USERNAME}</th>
				  <th>{L_EMAIL}</th>
				  <th>{L_FROM}</th>
				  <th>{L_JOINED}</th>
				  <th>{L_POSTS}</th>
				  <th>{L_WEBSITE}</th>
				</tr>
				<!-- BEGIN memberrow -->
				<tr> 
				  <td class="{memberrow.ROW_CLASS}" align="center">&nbsp;{memberrow.PM_IMG}&nbsp;</td>
				  <td class="{memberrow.ROW_CLASS}" align="center"><span class="gen"><a href="{memberrow.U_VIEWPROFILE}" class="gen">{memberrow.USERNAME}</a></span></td>
				  <td class="{memberrow.ROW_CLASS}" align="center" valign="middle">&nbsp;{memberrow.EMAIL_IMG}&nbsp;</td>
				  <td class="{memberrow.ROW_CLASS}" align="center" valign="middle"><span class="gen">{memberrow.FROM}</span></td>
				  <td class="{memberrow.ROW_CLASS}" align="center" valign="middle"><span class="gensmall">{memberrow.JOINED}</span></td>
				  <td class="{memberrow.ROW_CLASS}" align="center" valign="middle"><span class="gen">{memberrow.POSTS}</span></td>
				  <td class="{memberrow.ROW_CLASS}" align="center">&nbsp;{memberrow.WWW_IMG}&nbsp;</td>
				</tr>
				<!-- END memberrow -->
				<tr> 
				  <td class="cat" colspan="7" height="28">
					<table width="100%" cellspacing="0" cellpadding="0" border="0">
					  <tr> 
						<td><span class="nav">&nbsp;{L_PAGE} <b>{ON_PAGE}</b> 
						  {L_OF} <b>{TOTAL_PAGES}</b></span></td>
						<td align="right"><span class="nav">{PAGINATION}&nbsp;</span></td>
					  </tr>
					</table>
				  </td>
				</tr>
			  </table>
			</td>
		  </tr>
		</table>
	  </td>
	</tr>
  </table>
  <table width="100%" cellspacing="2" border="0" align="center" cellpadding="2">
	<tr> 
	  <td align="right" valign="top"><span class="gensmall">{S_TIMEZONE}</span></td>
	</tr>
  </table>
</form>

<table width="100%" cellspacing="2" border="0" align="center">
  <tr> 
	<td valign="top" align="right">{JUMPBOX}</td>
  </tr>
</table>
