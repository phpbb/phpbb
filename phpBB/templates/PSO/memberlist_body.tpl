<div align="center"><table width="98%" cellspacing="0" cellpadding="4" border="0">
	<tr>
		<td align="left"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a></font></td>
	</tr>
</table></div>

<div align="center"><table border="0" cellpadding="1" cellspacing="0" width="98%">
	<tr>
		<td bgcolor="{T_TH_COLOR1}"><table border="0" cellpadding="4" cellspacing="1" width="100%">
			<tr>
				<td colspan="6" bgcolor="{T_TH_COLOR2}" align="right"><a href="{U_VIEW_TOP10}"><img src="templates/PSO/images/topten-posters.gif" border="1"></a>&nbsp;&nbsp;<a href="{U_SORTALPHA}"><img src="templates/PSO/images/alphabetical.gif" border="1"></a></td>
			</tr>
			<tr>
				<td bgcolor="{T_TH_COLOR3}"align="center"><font face="verdana" size="{T_FONTSIZE2}"><b>{L_USERNAME}</td>
				<td bgcolor="{T_TH_COLOR3}"align="center"><font face="verdana" size="{T_FONTSIZE2}"><b>{L_FROM}</b></font></td>
				<td bgcolor="{T_TH_COLOR3}" align="center"><font face="verdana" size="{T_FONTSIZE2}"><b>{L_JOINED}</b></font></td>
				<td bgcolor="{T_TH_COLOR3}" align="center"><font face="verdana" size="{T_FONTSIZE2}"><b>{L_POSTS}</b></font></td>
				<td bgcolor="{T_TH_COLOR3}" align="center"><font face="verdana" size="{T_FONTSIZE2}"><b>{L_EMAIL}</b></font></td>
				<td bgcolor="{T_TH_COLOR3}" align="center"><font face="verdana" size="{T_FONTSIZE2}"><b>{L_WEBSITE}</b></font></td>
			</tr>
			<!-- BEGIN memberrow -->
			<tr>
				<td bgcolor="{memberrow.ROW_COLOR}" align="center"><a href="{memberrow.U_VIEWPROFILE}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{memberrow.USERNAME}</font></a></td>
				<td bgcolor="{memberrow.ROW_COLOR}" align="center" valign="middle"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{memberrow.FROM}</font></td>
				<td bgcolor="{memberrow.ROW_COLOR}" align="center" valign="middle"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{memberrow.JOINED}</font></td>
				<td bgcolor="{memberrow.ROW_COLOR}" align="center" valign="middle"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{memberrow.POSTS}</font></td>
				<td bgcolor="{memberrow.ROW_COLOR}" align="center" valign="middle"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{memberrow.EMAIL}</font></td>
				<td bgcolor="{memberrow.ROW_COLOR}" align="center"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{memberrow.WEBSITE}</font></a></td>
			</tr>
			<!-- END memberrow -->
			<tr>
				<td colspan="6" bgcolor="{T_TH_COLOR2}"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">&nbsp;{L_PAGE} <b>{ON_PAGE}</b> {L_OF} <b>{TOTAL_PAGES}</b></font></td>
						<td align="right"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_GOTO_PAGE}:&nbsp;{PAGINATION}&nbsp;</font></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
</table></div>

<div align="center"><table cellspacing="2" border="0" width="98%">
	<tr>
		<td width="40%" valign="top"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}"><b>{S_TIMEZONE}</b></font></td>
		<td align="right" valign="top" nowrap>{JUMPBOX}</td>
	</tr>
</table></div>