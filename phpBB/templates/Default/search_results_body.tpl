<tr>
	<td><div align="center"><table border="0" cellpadding="1" cellspacing="0" width="100%">
	<tr>
		<td bgcolor="#000000"><table border="0" cellpadding="4" cellspacing="1" width="100%">
			<tr class="tableheader">
				<td colspan="2" align="center">&nbsp;{L_SEARCH} - {SEARCH_MATCHES} Matches&nbsp;</td>
			</tr>
		</table></td>
	</tr>
</table></div>

<br clear="all">

<div align="center"><table border="0" cellpadding="1" cellspacing="0" width="100%">
	<tr>
		<td bgcolor="#000000"><table border="0" cellpadding="0" cellspacing="1" width="100%">
			<!-- BEGIN searchresults -->
			<tr>
				<td bgcolor="#CCCCCC" class="tablebody" colspan="2" align="left"><table cellpadding="4" cellspacing="1" border="0">
					<tr>
						<td><img src="images/folder.gif">&nbsp;{L_FORUM}:&nbsp;<a href="{U_FORUM}">{searchresults.FORUM_NAME}</a></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td bgcolor="#CCCCCC" class="tablebody" rowspan="2" width="20%"><table height="100%" cellspacing="0" cellpadding="4" border="0">
					<tr>
						<td valign="top"><b><a href="{searchresults.U_USER_PROFILE}">{searchresults.POSTER_NAME}</a></b><br><br>{L_REPLIES}: <b>{searchresults.TOPIC_REPLIES}</b><br>{L_VIEWS}: <b>{searchresults.TOPIC_VIEWS}</b><br></td>
					</tr>
				</table></td>
				<td bgcolor="#DDDDDD" class="tablebody" width="80%"><table width="100%" cellspacing="0" cellpadding="4" border="0">
					<tr>
						<td>{L_TOPIC}:&nbsp;<a href="{searchresults.U_TOPIC}">{searchresults.TOPIC_TITLE}</a></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td bgcolor="#DDDDDD" class="tablebody"><table width="100%" height="100%" cellspacing="0" cellpadding="4" border="0">
					<tr>
						<td><a href="{searchresults.U_POST}"><img src="images/posticon.gif" alt="Post image icon" border="0"></a>{L_POSTED}: {searchresults.POST_DATE}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Post Subject: {searchresults.POST_SUBJECT}<hr></td>
					</tr>
					<tr>
						<td height="100%">{searchresults.MESSAGE}<br><br></td>
					</tr>
				</table></td>
			</tr>
			<!-- END searchresults -->
			<tr>
				<td colspan="2" bgcolor="#CCCCCC" class="tablebody"><table width="100%" cellspacing="0" cellpadding="4" border="0">
					<tr>
						<td align="left" valign="middle">&nbsp;{L_PAGE} <b>{ON_PAGE}</b> {L_OF} <b>{TOTAL_PAGES}</b>&nbsp;</td>
						<td align="right" valign="middle">{L_GOTO_PAGE}:&nbsp;{PAGINATION}</td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
</table></div>
	</td>
</tr>
<tr>
	<td align="center"><table border="0" cellpadding="1" cellspacing="0" width="100%">
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