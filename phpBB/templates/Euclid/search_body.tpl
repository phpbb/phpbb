
<form action="{S_SEARCH_ACTION}" method="post">

<table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr> 
		<td align="left"><span class="gensmall"><a href="{U_INDEX}">{L_INDEX}</a></span></td>
	</tr>
</table>

<table width="98%" cellpadding="0" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table width="100%" cellpadding="4" cellspacing="1" border="0">
			<tr>
				<td class="cat" colspan="4" height="30" align="center"><span class="cattitle">{L_SEARCH_QUERY}</span></td>
			</tr>
			<tr> 
				<td class="row1" colspan="2" width="50%"><span class="gen">{L_SEARCH_KEYWORDS}:</span><br /><span class="gensmall">{L_SEARCH_KEYWORDS_EXPLAIN}</span></td>
				<td class="row2" colspan="2" valign="top"><input type="text" style="width: 300px" name="search_keywords" size="30" /><br /><span class="gensmall"><input type="radio" name="addterms" value="any" checked />{L_SEARCH_ANY_TERMS}<br /><input type="radio" name="addterms" value="all" />{L_SEARCH_ALL_TERMS}</span></td>
			</tr>
			<tr> 
				<td class="row1" colspan="2"><span class="gen">{L_SEARCH_AUTHOR}:</span><br /><span class="gensmall">{L_SEARCH_AUTHOR_EXPLAIN}</span></td>
				<td class="row2" colspan="2" valign="middle"><span class="genmed"> <input type="text" style="width: 300px" name="search_author" size="30" /></span></td>
			</tr>
			<tr> 
				<td class="cat" colspan="4" height="30" align="center"><span class="cattitle">{L_SEARCH_OPTIONS}</span></td>
			</tr>
			<tr>
				<td class="row1" align="right"><span class="gen">{L_FORUM}:&nbsp;</span></td>
				<td class="row2"><span class="gensmall"><select class="post" name="searchforum">{S_FORUM_OPTIONS}</select></span></td>
				<td class="row1" align="right"><span class="gen">{L_RETURN_FIRST}</span></td>
				<td class="row2"><span class="gensmall"><select class="post" name="charsreqd">{S_CHARACTER_OPTIONS}</select></span> <span class="gen">{L_CHARACTERS}</span></td>
			</tr>
			<tr> 
				<td class="row1" align="right"><span class="gen">{L_CATEGORY}:&nbsp;</span></td>
				<td class="row2"><span class="gensmall"><select class="post" name="searchcat">{S_CATEGORY_OPTIONS}</select></span></td>
				<td class="row1" align="right"><span class="gen">{L_SORT_BY}:&nbsp;</span></td>
				<td class="row2" valign="middle" nowrap="nowrap"><span class="gensmall"><select class="post" name="sortby">{S_SORT_OPTIONS}</select><br /><input type="radio" name="sortdir" value="ASC" />{L_SORT_ASCENDING}<br /><input type="radio" name="sortdir" value="DESC" checked />{L_SORT_DESCENDING}</span>&nbsp;</td>
			</tr>
			<tr> 
				<td class="row1" align="right" nowrap="nowrap"><span class="gen">{L_DISPLAY_RESULTS}:&nbsp;</span></td>
				<td class="row2" nowrap="nowrap"><input type="radio" name="showresults" value="posts" /><span class="gen">{L_POSTS}<input type="radio" name="showresults" value="topics" checked="checked" />{L_TOPICS}</span></td>
				<td class="row1" align="right" nowrap="nowrap"><span class="gen">{L_SEARCH_PREVIOUS}:&nbsp;</span></td>
				<td class="row2" valign="middle"><span class="gensmall"><select class="post" name="resultdays">{S_TIME_OPTIONS}</select></span></td>
			</tr>
			<tr> 
				<td class="cat" colspan="4" height="30" align="center">{S_HIDDEN_FIELDS}<input class="mainoptiontable" type="submit" value="{L_SEARCH}" /></td>
			</tr>
		</table></td>
	</tr>
</table></form>

<table width="98%" cellspacing="2" border="0" align="center">
	<tr>
		<td valign="top"><span class="gensmall">{S_TIMEZONE}</span></td>
		<td align="right" valign="top">{JUMPBOX}</td>
	</tr>
</table>
