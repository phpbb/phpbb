<div align="center"><table width="98%" cellspacing="0" cellpadding="4" border="0">
	<tr>
		<td align="left"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a> -> <a href="{U_VIEW_FORUM}">{FORUM_NAME}</a> -> {TOPIC_TITLE}</font></td>
	</tr>
</table></div>

<div align="center"><table border="0" cellpadding="1" cellspacing="0" width="98%">
	<tr>
		<td bgcolor="{T_TH_COLOR1}"><table border="0" cellpadding="4" cellspacing="1" width="100%">
			<tr>
			        <td colspan="2" bgcolor="{T_TH_COLOR2}"><table width="100%" cellspacing="0" cellpadding="0" border="0"> 
			                <tr>
                        			<td><font face="{T_FONTFACE1}" size="{T_FONTSIZE3}"><b>{TOPIC_TITLE}</b></font></td> 
                        			<td align="right" valign="middle"><a href="{U_POST_REPLY_TOPIC}"><img src="templates/PSO/images/reply.gif" border="1" /></a>&nbsp;&nbsp;<a href="{U_POST_NEW_TOPIC}"><img src="templates/PSO/images/post.gif" border="1" /></a>&nbsp;</td>  
 			               </tr>
        			</table></td>
			</tr>
			<tr>
				<td width="20%" bgcolor="{T_TH_COLOR3}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><b>{L_AUTHOR}</b></font></td>
				<td bgcolor="{T_TH_COLOR3}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><b>{L_MESSAGE}</b></font></td>
			</tr>
	        <!-- BEGIN postrow -->
			<tr bgcolor="{postrow.ROW_COLOR}">
				<td width="15%" align="left" valign="top"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><b>{postrow.POSTER_NAME}</b></font><br /><font face="{T_FONTFACE2}" size="{T_FONTSIZE1}">{postrow.POSTER_RANK}<br /><br />{L_JOINED}: {postrow.POSTER_JOINED}<br />{L_POSTS}: {postrow.POSTER_POSTS}<br/ >{postrow.POSTER_FROM}</font><br /><br />{postrow.AVATAR}</td>
				
				<td><a name="{postrow.U_POST_ID}"></a><img src="images/posticon.gif" alt="Post image icon" /><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{L_POSTED}: {postrow.POST_DATE}</font><hr /><font face="{T_FONTFACE3}" size="{T_FONTSIZE2}" color="{T_FONTCOLOR1}">{postrow.MESSAGE}</font><hr />{postrow.PROFILE_IMG}&nbsp;{postrow.EMAIL_IMG}&nbsp;{postrow.WWW_IMG}&nbsp;{postrow.ICQ_STATUS_IMG}&nbsp;{postrow.ICQ_ADD_IMG}&nbsp;{postrow.AIM_IMG}&nbsp;{postrow.YIM_IMG}&nbsp;{postrow.MSN_IMG}&nbsp;<img src="images/div.gif">&nbsp;{postrow.EDIT_IMG}&nbsp;{postrow.QUOTE_IMG}&nbsp;{postrow.PMSG_IMG}&nbsp;<img src="images/div.gif">&nbsp;{postrow.IP_IMG}&nbsp;{postrow.DELPOST_IMG}</td>
			</tr>
			<!-- END postrow -->
			<tr bgcolor="<?php echo $color1?>">
				<td colspan="2" bgcolor="{T_TH_COLOR2}"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td align="left" valign="middle"><a href="{U_POST_REPLY_TOPIC}"><img src="templates/PSO/images/reply.gif" border="1" /></a>&nbsp;&nbsp;<a href="{U_POST_NEW_TOPIC}"><img src="templates/PSO/images/post.gif" border="1" /></a></td>
						<td align="right" valign="middle"><font face="{T_FONTFACE2}" size="{T_FONTSIZE2}">{PAGINATION}</font></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
</table></div>

<div align="center"><table cellspacing="2" border="0" width="98%">
	<tr>
		<td width="40%" valign="top"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}"><b>{S_TIMEZONE}</b></font></td>
		<td align="right" valign="top" nowrap>{JUMPBOX}</td>
	</tr>
</table></div>