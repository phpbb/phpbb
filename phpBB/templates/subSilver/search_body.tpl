<form action="{S_SEARCH_ACTION}" method="POST">
  <table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
	<tr> 
	  <td align="left"><span class="nav"><a href="{U_INDEX}" class="nav">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
	</tr>
  </table>
  <table border="0" cellpadding="4" cellspacing="1" width="100%" class="forumline">
	<tr> 
	  <th class="thHead" colspan="4" height="25">{L_SEARCH_QUERY}</th>
	</tr>
	<tr> 
	  <td class="row1" colspan="2" width="50%"><span class="gen">{L_SEARCH_KEYWORDS}:</span><br />
		<span class="gensmall">{L_SEARCH_KEYWORDS_EXPLAIN}</span></td>
	  <td class="row2" colspan="2" valign="top"><span class="genmed"> 
		<input type="text" style="width: 300px" class="post" name="search_keywords" size="30" />
		<br />
		<input type="radio" name="addterms" value="any" checked />
		{L_SEARCH_ANY_TERMS}<br />
		<input type="radio" name="addterms" value="all" />
		{L_SEARCH_ALL_TERMS}</span></td>
	</tr>
	<tr> 
	  <td class="row1" colspan="2"><span class="gen">{L_SEARCH_AUTHOR}:</span><br />
		<span class="gensmall">{L_SEARCH_AUTHOR_EXPLAIN}</span></td>
	  <td class="row2" colspan="2" valign="middle"><span class="genmed"> 
		<input type="text" style="width: 300px" class="post" name="search_author" size="30" />
		</span> </td>
	</tr>
	<tr> 
	  <th class="thHead" colspan="4" height="25">{L_SEARCH_OPTIONS}</th>
	</tr>
	<tr> 
	  <td class="row1" align="right"><span class="gen">{L_FORUM}:&nbsp;</span></td>
	  <td class="row2"><span class="genmed">
		<select class="post" name="searchforum">{S_FORUM_OPTIONS}
		</select>
		</span></td>
	  <td class="row1" align="right"><span class="gen">{L_RETURN_FIRST}</span></td>
	  <td class="row2"><span class="genmed"> 
		<select class="post" name="charsreqd">{S_CHARACTER_OPTIONS}
		</select>
		{L_CHARACTERS}</span></td>
	</tr>
	<tr> 
	  <td class="row1" align="right"><span class="gen">{L_CATEGORY}:&nbsp;</span></td>
	  <td class="row2"><span class="genmed">
		<select class="post" name="searchcat">{S_CATEGORY_OPTIONS}
		</select>
		</span></td>
	  <td class="row1" align="right"><span class="gen">{L_SORT_BY}:&nbsp;</span></td>
	  <td class="row2" valign="middle" nowrap="nowrap"><span class="genmed"> 
		<select class="post" name="sortby">{S_SORT_OPTIONS}
			  
		</select>
		<br />
		<input type="radio" name="sortdir" value="ASC" />
		{L_SORT_ASCENDING}<br />
		<input type="radio" name="sortdir" value="DESC" checked />
		{L_SORT_DESCENDING}</span>&nbsp;</td>
	</tr>
	<tr> 
	  <td class="row1" align="right" nowrap="nowrap"><span class="gen">{L_DISPLAY_RESULTS}:&nbsp;</span></td>
	  <td class="row2" nowrap="nowrap"> 
		<input type="radio" name="showresults" value="posts" />
		<span class="genmed">{L_POSTS}
		<input type="radio" name="showresults" value="topics" checked="checked" />
		{L_TOPICS}</span></td>
	  <td class="row1" align="right" nowrap="nowrap"><span class="gen">{L_SEARCH_PREVIOUS}:&nbsp;</span></td>
	  <td class="row2" valign="middle"><span class="genmed">
		<select class="post" name="resultdays">{S_TIME_OPTIONS}
		</select>
		</span></td>
	</tr>
	<tr> 
	  <td class="catBottom" colspan="4" align="center" height="28">{S_HIDDEN_FIELDS} 
		<input class="liteoption" type="submit" value="{L_SEARCH}" />
	  </td>
	</tr>
  </table>
  <table width="100%" cellspacing="2" border="0" align="center" cellpadding="2">
	<tr> 
	  <td align="right" valign="middle"><span class="gensmall">{S_TIMEZONE}</span></td>
	</tr>
  </table>
</form>
<table width="100%" border="0">
  <tr> 
	<td align="right" valign="top">{JUMPBOX}</td>
  </tr>
</table>
