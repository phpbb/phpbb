<tr>
	<td><div align="center"><table border="0" cellpadding="1" cellspacing="0" width="70%">
	<tr><form action="{S_SEARCH_ACTION}" method="post">
		<td bgcolor="#000000"><table border="0" cellpadding="4" cellspacing="1" width="100%">
			<tr class="tableheader">
				<td colspan="2" align="center">&nbsp;{L_SEARCH}&nbsp;</td>
			</tr>
			<tr bgcolor="#DDDDDD" class="tablebody">
				<td align="right">Search query:&nbsp;</td>
				<td><input type="text" name="querystring" size="40"><br><input type="radio" name="addterms" value="any" checked>{L_SEARCH_ANY_TERMS}<br><input type="radio" name="addterms" value="all">{L_SEARCH_ALL_TERMS}</td>
			</tr>
			<tr bgcolor="#DDDDDD" class="tablebody">
				<td align="right">{L_SEARCH_AUTHOR}:&nbsp;</td>
				<td><input type="text" name="authorstring"></td>
			</tr>
			<tr bgcolor="#DDDDDD" class="tablebody">
				<td align="right">{L_FORUM}:&nbsp;</td>
				<td><select name="searchforum">{S_FORUM_OPTIONS}</select></td>
			</tr>
			<tr bgcolor="#DDDDDD" class="tablebody">
				<td align="right"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}" color="{T_FONTCOLOR1}">{L_LIMIT_CHARACTERS}:&nbsp;</td>
				<td><select name="charsreqd">{S_CHARACTER_OPTIONS}</select></td>
			</tr>
			<tr bgcolor="#DDDDDD" class="tablebody">
				<td align="right">{L_SORT_BY}:&nbsp;</td>
				<td valign="middle"><select name="sortby">{S_SORT_OPTIONS}</select>&nbsp;&nbsp;<input type="radio" name="sortdir" value="ASC" checked>&nbsp;{L_SORT_ASCENDING}&nbsp;&nbsp;&nbsp;<input type="radio" name="sortdir" value="DESC">&nbsp;{L_SORT_DECENDING}&nbsp;</td>
			</tr>
			<tr bgcolor="#CCCCCC" class="tablebody">
				<td colspan="2" align="center">{S_HIDDEN_FIELDS}<input type="submit" value="{L_SEARCH}"></td>
			</tr>
		</table></td>
	</form></tr>
</table></div>


	</td>
</tr>
<tr>
	<td align="center"><table border="0" cellpadding="1" cellspacing="0" width="70%">
	<tr>
	  <td><table border="0" align="right" width="20%" bgcolor="#000000" cellpadding="0" cellspacing="1">
	    <tr>
	      <td>
	        <table border="0" width="100%" bgcolor="#CCCCCC" cellpadding="1" cellspacing="1">
	          <tr>
	            <td align="right" style="{font-size: 8pt; height: 55px;}">{JUMPBOX}</td>
	          </tr>
	        </table>
	      </td>
	    </tr>
	    </table></td>
	</tr>
	</table></td>
</tr>