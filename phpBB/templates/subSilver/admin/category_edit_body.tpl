<br />

<h1>Edit Category</h1>

<p>Use this form to modify a category.</p>

<form action="{S_FORUM_ACTION}" method="POST">
  <table cellpadding="4" cellspacing="1" border="0" class="forumline" align="center">
	<tr> 
	  <th class="thHead" colspan="2">Modify Category</th>
	</tr>
	<tr> 
	  <td class="row1">Category Name:</td>
	  <td class="row2">
		<input type="text" size="25" name="cat_title" value="{CAT_TITLE}" />
	  </td>
	</tr>
	<tr> 
	  <td class="catBottom" colspan="2" align="center"> 
		<input type="hidden" name="mode" value="{S_NEWMODE}" />
		<input type="hidden" name="cat_id" value="{S_CATID}" />
		<input type="submit" name="submit" value="{BUTTONVALUE}" class="mainoption" />
	  </td>
	</tr>
  </table>
</form>
		
<br clear="all">
