<br />

<h1>Edit Forum</h1>

<p>The form below will allow you to customize all the general board options. For User and Forum configurations use the related links on the left hand side.</p>

<form action="{S_FORUM_ACTION}" method="POST">
  <table width="100%" cellpadding="4" cellspacing="1" border="0" class="forumline">
	<tr> 
	  <th class="thHead" colspan="2">General Forum Settings</th>
	</tr>
	<tr> 
	  <td class="row1">Forum Name:</td>
	  <td class="row2">
		<input type="text" size="25" name="forumname" value="{FORUMNAME}" class="post" />
	  </td>
	</tr>
	<tr> 
	  <td class="row1">Description:</td>
	  <td class="row2">
		<textarea rows="5" cols="45" wrap="VIRTUAL" name="forumdesc" class="post">{DESCRIPTION}</textarea>
	  </td>
	</tr>
	<tr> 
	  <td class="row1">Category:</td>
	  <td class="row2"> 
		<select name="cat_id">{S_CATLIST}</select>
	  </td>
	</tr>
	<tr> 
	  <td class="row1">Forum Status:</td>
	  <td class="row2"> 
		<select name="forumstatus">{S_STATUSLIST}</select>
	  </td>
	</tr>
	<tr> 
	  <td class="row1">Auto Pruning:</td>
	  <td class="row2"> 
		<table>
		  <tr> 
			<td align="right" valign="middle">{L_ENABLED}</td>
			<td align="left" valign="middle">
			  <input type="checkbox" name="prune_enable" value="1" {S_PRUNE_EN} />
			</td>
		  </tr>
		  <tr> 
			<td align="right" valign="middle">{L_PRUNE_DAYS}</td>
			<td align="left" valign="middle">&nbsp;
			  <input type="text" name="prune_days" value="{S_PRUNE_DAYS}" size="5" class="post" />
			  &nbsp;{L_DAYS}</td>
		  </tr>
		  <tr> 
			<td align="right" valign="middle">{L_PRUNE_FREQ}</td>
			<td align="left" valign="middle">&nbsp;
			  <input type="text" name="prune_freq" value="{S_PRUNE_FREQ}" size="5" class="post" />
			  &nbsp;{L_DAYS}</td>
		  </tr>
		</table>
	  </td>
	</tr>
	<tr> 
	  <td class="catBottom" colspan="2" align="center"> 
		<input type="hidden" name="mode" value="{S_NEWMODE}" />
		<input type="hidden" name="forum_id" value="{S_FORUMID}" />
		<input type="submit" name="submit" value="{BUTTONVALUE}" class="mainoption" />
	  </td>
	</tr>
  </table>
</form>
		
<br clear="all">
