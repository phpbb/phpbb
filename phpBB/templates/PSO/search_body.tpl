<div align="center"><table width="70%" cellspacing="0" cellpadding="4" border="0">
	<tr>
		<td align="left"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a></font></td>
	</tr>
</table></div>

<div align="center"><table border="0" cellpadding="1" cellspacing="0" width="70%">
	<tr><form action="{S_SEARCH_ACTION}" method="post">
		<td bgcolor="{T_TH_COLOR1}"><table border="0" cellpadding="4" cellspacing="1" width="100%">
			<tr>
				<td colspan="2" bgcolor="{T_TH_COLOR3}" align="center"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}" color="{T_FONTCOLOR1}">&nbsp;<b>{L_SEARCH}</b>&nbsp;</font></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}" align="right"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}" color="{T_FONTCOLOR1}">Search query:&nbsp;</td>
				<td bgcolor="{T_TD_COLOR1}"><input type="text" name="querystring" size="40"><br><input type="radio" name="addterms" value="any" checked><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}">{L_SEARCH_ANY_TERMS}<br><input type="radio" name="addterms" value="all">{L_SEARCH_ALL_TERMS}</td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}" align="right"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}" color="{T_FONTCOLOR1}">{L_SEARCH_AUTHOR}:&nbsp;</td>
				<td bgcolor="{T_TD_COLOR1}"><input type="text" name="authorstring"></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}" align="right"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}" color="{T_FONTCOLOR1}">{L_FORUM}:&nbsp;</td>
				<td bgcolor="{T_TD_COLOR1}"><select name="searchforum">{S_FORUM_OPTIONS}</select></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}" align="right"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}" color="{T_FONTCOLOR1}">{L_LIMIT_CHARACTERS}:&nbsp;</td>
				<td bgcolor="{T_TD_COLOR1}"><select name="charsreqd">{S_CHARACTER_OPTIONS}</select></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}" align="right"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}" color="{T_FONTCOLOR1}">{L_SORT_BY}:&nbsp;</td>
				<td bgcolor="{T_TD_COLOR1}" valign="middle"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}"><select name="sortby">{S_SORT_OPTIONS}</select>&nbsp;&nbsp;<input type="radio" name="sortdir" value="ASC">&nbsp;{L_SORT_ASCENDING}&nbsp;&nbsp;&nbsp;<input type="radio" name="sortdir" value="DESC" checked>&nbsp;{L_SORT_DECENDING}&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2" bgcolor="{T_TH_COLOR2}" align="center">{S_HIDDEN_FIELDS}<input type="submit" value="{L_SEARCH}"></td>
			</tr>
		</table></td>
	</form></tr>
</table></div>

<div align="center"><table width="70%" border="0">
	<tr>
		<td align="left" valign="top"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}"><b>{S_TIMEZONE}</b></font></td>
		<td align="right" valign="top" nowrap>{JUMPBOX}</td>
	</tr>
</table>