
<h1><br />
  {L_GROUP_INFO} </h1>
 <br />
<form action="{S_GROUP_ACTION}" method="post">
  <table border="0" cellpadding="3" cellspacing="1" class="forumline" align="center">
	<tr> 
	  <th class="thHead" colspan="2">{L_GROUP_INFO}</th>
	</tr>
	<tr>
	  <td class="row1" colspan="2"><span class="gensmall">{L_ITEMS_REQUIRED}</span></td>
	</tr>
	<tr> 
	  <td class="row1" width="38%"><span class="gen">{L_GROUP_NAME}:</span></td>
	  <td class="row2" width="62%"> 
		<input type="text" name="group_name" size="35" maxlength="40" value="{S_GROUP_NAME}" />
	  </td>
	</tr>
	<tr> 
	  <td class="row1" width="38%"><span class="gen">{L_GROUP_DESCRIPTION}:</span></td>
	  <td class="row2" width="62%"> 
		<textarea name="group_description" rows=5 cols=51>{S_GROUP_DESCRIPTION}</textarea>
	  </td>
	</tr>
	<tr> 
	  <td class="row1" width="38%"><span class="gen">{L_GROUP_MODERATOR}:</span></td>
	  <td class="row2" width="62%">{S_GROUP_MODERATOR}</td>
	</tr>
	<tr> 
	  <td class="row1" width="38%"><span class="gen">{L_GROUP_STATUS}:</span></td>
	  <td class="row2" width="62%"> 
		<input type="radio" name="group_type" value="1" {S_GROUP_OPEN_CHECKED} />
		{L_GROUP_OPEN} &nbsp;&nbsp; 
		<input type="radio" name="group_type" value="0" {S_GROUP_CLOSED_CHECKED} />
		{L_GROUP_CLOSED}</td>
	</tr>
	<tr> 
	  <td class="row1" width="38%"><span class="gen">{L_GROUP_DELETE}:</span></td>
	  <td class="row2" width="62%"> 
		<input type="checkbox" name="deletegroup" value="1">
		{L_GROUP_DELETE_CHECK}</td>
	</tr>
	<tr> 
	  <td class="catBottom" colspan="2" align="center"><span class="cattitle"> 
		<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />
		&nbsp;&nbsp; 
		<input type="reset" value="{L_RESET}" name="reset" class="liteoption" />
		</span></td>
	</tr>
  </table>
  <input type="hidden" name="mode" value="{S_GROUP_MODE}" />
  <input type="hidden" name="updategroup" value="update" />
  <input type="hidden" name="group_id" value="{GROUP_ID}" />
</form>