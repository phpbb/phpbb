
<h1>Delete</h1>

<p>The form below will allow you to delete a forum/category and decide where you want to put all topics/forums inside that forum/category.</p>

<form action="{S_FORUM_ACTION}" method="POST">
  <table cellpadding="4" cellspacing="1" border="0" class="forumline" align="center">
	<tr> 
	  <th colspan="2" class="thHead">Delete</th>
	  </tr>
	<tr> 
	  <td class="row1">Name:</td>
	  <td class="row1"><span class="row1">{NAME}</span></td>
	</tr>
	<tr> 
	  <td class="row1">Move everything to:</td>
	  <td class="row1"> {S_SELECT_TO} </td>
	</tr>
	<tr> 
	  <td class="catBottom" colspan="2" align="center"> 
		<input type="hidden" name="mode" value="{S_NEWMODE}" />
		<input type="hidden" name="from_id" value="{S_FROM_ID}" />
		<input type="submit" name="submit" value="{BUTTONVALUE}" class="mainoption" />
	  </td>
	</tr>
  </table>
</form>
