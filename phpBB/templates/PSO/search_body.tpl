<form action="{S_SEARCH_ACTION}" method="post"><table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left"><span class="gensmall"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
	</tr>
</table>

<table width="98%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table border="0" cellpadding="4" cellspacing="1" width="100%">
			<tr>
				<td class="cat" colspan="4" align="center"><span class="cattitle"><b>{L_SEARCH_QUERY}</b></span></td>
			</tr>
			<tr>
				<td class="row1" colspan="2" width="50%"><span class="gen">{L_SEARCH_KEYWORDS}:</span><br /><span class="gensmall">{L_SEARCH_KEYWORDS_EXPLAIN}</span></td>
				<td class="row2" colspan="2" valign="top"><input type="text" name="search_keywords" size="60" /><br /><input type="radio" name="addterms" value="any" checked="checked" /><span class="gensmall">{L_SEARCH_ANY_TERMS}<br /><input type="radio" name="addterms" value="all" />{L_SEARCH_ALL_TERMS}</span></td>
			</tr>
			<tr>
				<td class="row1" colspan="2"><span class="gen">{L_SEARCH_AUTHOR}:</span><br /><span class="gensmall">{L_SEARCH_AUTHOR_EXPLAIN}</span></td>
				<td class="row2" colspan="2" valign="top"><input type="text" name="search_author" size="40" /></td>
			</tr>
			<tr>
				<td class="cat" colspan="4" align="center"><span class="cattitle"><b>{L_SEARCH_OPTIONS}</b></span></td>
			</tr>
			<tr>
				<td class="row1" align="right"><span class="gen">{L_FORUM}:&nbsp;</span></td>
				<td class="row2"><select name="searchforum">{S_FORUM_OPTIONS}</select></td>
				<td class="row1" align="right"><span class="gen">{L_RETURN_FIRST}</span></td>
				<td class="row2"><select name="charsreqd">{S_CHARACTER_OPTIONS}</select> <span class="gen">{L_CHARACTERS}</span></td>
			</tr>
			<tr>
				<td class="row1" align="right"><span class="gen">{L_CATEGORY}:&nbsp;</span></td>
				<td class="row2"><select name="searchcat">{S_CATEGORY_OPTIONS}</select></td>
				<td class="row1" align="right"><span class="gen">{L_SORT_BY}:&nbsp;</span></td>
				<td class="row2" valign="middle"><select name="sortby">{S_SORT_OPTIONS}</select><br /><input type="radio" name="sortdir" value="ASC" />&nbsp;<span class="gensmall">{L_SORT_ASCENDING}</span>&nbsp;&nbsp;&nbsp;<input type="radio" name="sortdir" value="DESC" checked="checked" />&nbsp;<span class="gensmall">{L_SORT_DESCENDING}</span>&nbsp;</td>
			</tr>
			<tr>
				<td class="row1" align="right"><span class="gen">{L_DISPLAY_RESULTS}:&nbsp;</span></td>
				<td class="row2"><input type="radio" name="showresults" value="posts" checked="checked" />&nbsp;<span class="gensmall">{L_POSTS}</span>&nbsp;&nbsp;&nbsp;<input type="radio" name="showresults" value="topics" />&nbsp;<span class="gensmall">{L_TOPICS}</span>&nbsp;</td>
				<td class="row1" align="right"><span class="gen">{L_SEARCH_PREVIOUS}:&nbsp;</span></td>
				<td class="row2" valign="middle">{S_TIME_OPTIONS}</td>
			</tr>
			<tr>
				<td class="cat" colspan="4" align="center">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{L_SEARCH}" /></td>
			</tr>
		</table></td>
	</tr>
</table></form>

<table width="98%" border="0" align="center">
	<tr>
		<td align="left" valign="top"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
		<td align="right" valign="top" nowrap>{JUMPBOX}</td>
	</tr>
</table>
