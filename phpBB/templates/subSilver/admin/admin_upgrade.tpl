
<br />

<h1>Upgrade</h1>

<p align="center"><a href="http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/phpbb/phpBB2">Go take a look at Sourceforge CVS</a></p>

<script language="Javascript">
function checkall(state)
{
	for (var i = 0; i < document.form.elements.length; i++)
	{
		var e = document.form.elements[i];
		if ( isNaN(e.name) == false )
		{
			e.checked = state;
		}
	}
}
</script>
<form method="post" action="{S_UPGRADE_ACTION}" name="form">
  <table cellspacing="1" cellpadding="4" border="0" align="center" class="forumline">
	<tr> 
	  <th class="thCornerL" colspan="2">Filename</th>
	  <th class="thTop">Local</th>
	  <th class="thTop">Remote</th>
	  <th class="thCornerR">Status</th>
	</tr>
	<tr> 
	  <td class="row2"><a href="javascript:checkall(true)">all</a>/<a href="javascript:checkall(false)">none</a></td>
	  <td colspan="2" class="row2"> 
		<input type="checkbox" name="backup" CHECKED>
		Backup files before upgrading</td>
	  <td colspan="2" class="row2" align="right"><a href="javascript:document.form.submit()">Upgrade 
		»</a></td>
	</tr>
	<!-- BEGIN dir -->
	<tr> 
	  <td colspan="5" class="catSides">DIR: <b>{dir.NAME}</b></td>
	</tr>
	<!-- BEGIN file -->
	<tr align="center"> 
	  <td class="row2"> 
		<input type="checkbox" name="{dir.file.INPUTNAME}" value="{dir.file.INPUTVALUE}">
		<input type="hidden" name="version{dir.file.INPUTNAME}" value="{dir.file.REMOTE}">
	  </td>
	  <td class="row1" align="left"><a href="{dir.file.LINK}">{dir.file.NAME}</a></td>
	  <td class="row2">{dir.file.LOCAL}</td>
	  <td class="row1">{dir.file.REMOTE}</td>
	  <td class="row2">{dir.file.STATUS}</td>
	</tr>
	<!-- END file -->
	<!-- END dir -->
	<tr> 
	  <td align="right" colspan="5" class="row2"><a href="javascript:document.form.submit()">Upgrade 
		»</a></td>
	</tr>
  </table>
<input type="hidden" name="mode" value="upgrade">
<input type="hidden" name="maxfile" value="{S_UPGRADE_MAXFILE}">
</form>