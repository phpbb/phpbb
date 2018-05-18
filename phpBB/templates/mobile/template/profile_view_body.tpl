<!-- INCLUDE overall_header.tpl -->

<!-- IF S_ADMIN -->
{IMG_THL}{IMG_THC}<span class="forumlink">{L_QUICK_ADMIN_OPTIONS}</span>{IMG_THR}<table class="forumlinenb">
<tr>
	<td class="row1 row-center" colspan="2" ><span class="genmed"><a href="{U_ADMIN_EDIT_PROFILE}" title="{L_ADMIN_EDIT_PROFILE}">{L_ADMIN_EDIT_PROFILE}</a>&nbsp;&bull;&nbsp;<a href="{U_ADMIN_EDIT_PERMISSIONS}" title="{L_ADMIN_EDIT_PERMISSIONS}">{L_ADMIN_EDIT_PERMISSIONS}</a>&nbsp;&bull;&nbsp;{L_USER_ACTIVE_INACTIVE}&nbsp;&bull;&nbsp;{L_BANNED_USERNAME}&nbsp;[&nbsp;<a href="{U_USER_BAN_UNBAN}" title="{L_USER_BAN_UNBAN}">{L_USER_BAN_UNBAN}</a>&nbsp;]&nbsp;&bull;&nbsp;{L_BANNED_EMAIL}</span></td>
</tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}
<!-- ENDIF -->

{IMG_THL}{IMG_THC}<span class="forumlink">{USERNAME}</span>{IMG_THR}<table class="forumlinenb">
<tr>
	<td class="th2px">&nbsp;</td>
	<td>
		<table>
		<tr><td class="th2px">&nbsp;</td></tr>
		<tr>
			<td class="forumline tw50pct">
				<table>
					<tr><th colspan="2"><span class="genmed"><b>{L_INVISION_MEMBER_TITLE} &amp; {L_AVATAR}</b></span></th></tr>
					<tr>
						<td class="tw33pct row2"><b><span class="genmed">{L_INVISION_MEMBER_TITLE} &amp; {L_AVATAR}</span></b></td>
						<td class="row1 row-center tw64pct"><span class="genmed">{INVISION_AVATAR_IMG}<br class="clear" />{USER_RANK_01}{USER_RANK_01_IMG}{USER_RANK_02}{USER_RANK_02_IMG}{USER_RANK_03}{USER_RANK_03_IMG}{USER_RANK_04}{USER_RANK_04_IMG}{USER_RANK_05}{USER_RANK_05_IMG}</span></td>
					</tr>
					<tr><th colspan="2"><span class="genmed"><b>{L_INVISION_COMMUNICATE}</b></span></th></tr>
					<tr>
						<td class="row2"><b><span class="genmed">{L_PM}</span></b></td>
						<td class="row1 post-buttons"><span class="genmed">{PM_IMG}</span></td>
					</tr>
					<tr>
						<td class="row2"><b><span class="genmed">{L_EMAIL_ADDRESS}</span></b></td>
						<td class="row1 post-buttons"><span class="genmed">{EMAIL_IMG}</span></td>
					</tr>
					<tr>
						<td class="row2"><b><span class="genmed">{L_PHONE}</span></b></td>
						<td class="row1 post-buttons"><span class="genmed">{PHONE}</span></td>
					</tr>
					<!-- IF S_LOGGED_IN -->
					<!-- IF SHOW_FRIEND_LINK -->
					<tr>
						<td class="row2"><b><span class="genmed">{L_FRIENDSHIP_STATUS}</span></b></td>
						<td class="row1 post-buttons"><span class="gensmall"><a href="{U_FRIEND_ADD_REMOVE}" class="gensmall"><!-- IF IS_FRIEND -->{L_FRIEND_REMOVE}<!-- ELSE -->{L_FRIEND_ADD}<!-- ENDIF --></a></span></td>
					</tr>
					<!-- ENDIF -->
					<tr>
						<td class="row2"><b><span class="genmed">{L_AJAX_SHOUTBOX_PVT}</span></b></td>
						<td class="row1 post-buttons"><span class="gensmall"><a href="#" class="gensmall" onclick="window.open('{U_AJAX_SHOUTBOX_PVT_LINK}', '_chat', 'width=720,height=600,resizable=yes'); return false;">{L_AJAX_SHOUTBOX_PVT_LINK}</a></span></td>
					</tr>
					<!-- ENDIF -->
					<tr>
						<td class="row2"><b><span class="genmed">{L_INVISION_COMMUNICATE}</span></b></td>
						<td class="row1 post-buttons"><span class="genmed"><!-- IF S_LOGGED_IN and ICON_CHAT -->&nbsp;{ICON_CHAT}<!-- ENDIF --><!-- IF ICON_AIM -->&nbsp;{ICON_AIM}<!-- ENDIF --><!-- IF ICON_FACEBOOK -->&nbsp;{ICON_FACEBOOK}<!-- ENDIF --><!-- IF ICON_ICQ -->&nbsp;{ICON_ICQ}<!-- ENDIF --><!-- IF ICON_JABBER -->&nbsp;{ICON_JABBER}<!-- ENDIF --><!-- IF ICON_MSN -->&nbsp;{ICON_MSN}<!-- ENDIF --><!-- IF ICON_SKYPE -->&nbsp;{ICON_SKYPE}<!-- ENDIF --><!-- IF ICON_TWITTER -->&nbsp;{ICON_TWITTER}<!-- ENDIF --><!-- IF ICON_YAHOO -->&nbsp;{ICON_YAHOO}<!-- ENDIF -->&nbsp;</span></td>
					</tr>
					<!-- BEGIN custom_contact -->
					<tr>
						<td class="row2"><b><span class="genmed">{custom_contact.NAME}</span></b></td>
						<td class="row1 post-buttons"><span class="genmed">{custom_contact.VALUE}</span></td>
					</tr>
					<!-- END custom_contact -->
				</table>
			</td>
			<td class="tw2px"><img src="{SPACER}" width="2" alt="" /></td>
			<td class="forumline tw50pct">
				<table>
					<tr><th colspan="2"><span class="genmed"><b>{L_INVISION_INFO}</b></span></th></tr>
					<tr>
						<td class="tw33pct row2"><b><span class="genmed">{L_ONLINE_STATUS}</span></b></td>
						<td class="row1 tw64pct"><span class="genmed">{ONLINE_STATUS_IMG}&nbsp;{USER_OS_IMG}&nbsp;{USER_BROWSER_IMG}</span></td>
					</tr>
					<tr>
						<td class="row2"><b><span class="genmed">{L_USER_FIRST_NAME}</span></b></td>
						<td class="row1"><span class="genmed">{USER_FIRST_NAME}</span></td>
					</tr>
					<tr>
						<td class="row2"><b><span class="genmed">{L_USER_LAST_NAME}</span></b></td>
						<td class="row1"><span class="genmed">{USER_LAST_NAME}</span></td>
					</tr>
					<tr>
						<td class="row2"><b><span class="genmed">{L_INVISION_WEBSITE}</span></b></td>
						<td class="row1"><span class="genmed">{WWW}</span></td>
					</tr>
					<tr>
						<td class="row2"><b><span class="genmed">{L_BIRTHDAY}</span></b></td>
						<td class="row1"><span class="genmed">{BIRTHDAY}</span></td>
					</tr>
					<tr>
						<td class="row2"><b><span class="genmed">{L_GENDER}</span></b></td>
						<td class="row1"><span class="genmed">{GENDER}</span></td>
					</tr>
					<tr>
						<td class="row2"><b><span class="genmed">{L_LOCATION}</span></b></td>
						<td class="row1"><span class="genmed">{LOCATION}</span></td>
					</tr>
					<tr>
						<td class="row2"><b><span class="genmed">{L_INTERESTS}</span></b></td>
						<td class="row1"><span class="genmed">{INTERESTS}</span></td>
					</tr>
					<tr>
						<td class="row2"><b><span class="genmed">{L_OCCUPATION}</span></b></td>
						<td class="row1"><span class="genmed">{OCCUPATION}</span></td>
					</tr>
					<!-- BEGIN custom_about -->
					<tr>
						<td class="row2"><b><span class="genmed">{custom_about.NAME}</span></b></td>
						<td class="row1 post-buttons"><span class="genmed">{custom_about.VALUE}</span></td>
					</tr>
					<!-- END custom_about -->
					<tr>
						<td class="row2 tw30pct"><b><span class="genmed">{L_INVISION_SIGNATURE}</span></b></td>
						<td class="row1"><span class="genmed">{INVISION_USER_SIG}</span></td>
					</tr>
					<!-- BEGIN switch_groups_on -->
					<tr><th colspan="2"><span class="genmed"><b>{L_INVISION_MEMBER_GROUP}</b></span></th></tr>
					<tr>
						<td class="row2"><b><span class="genmed">{L_INVISION_MEMBER_GROUP}</span></b></td>
						<td class="row1">
							<span class="genmed">
					<!-- END switch_groups_on -->
							<!-- BEGIN groups -->
								<a href="{groups.U_GROUP_NAME}"{groups.GROUP_COLOR}><b{groups.GROUP_COLOR}>{groups.L_GROUP_NAME}</b></a>:&nbsp;{groups.L_GROUP_DESC}<br />
							<!-- END groups -->
					<!-- BEGIN switch_groups_on -->
							</span>
						</td>
					</tr>
					<!-- END switch_groups_on -->
				</table>
			</td>
		</tr>
		<tr><td class="tw2px"><img src="{SPACER}" width="2" alt="" /></td></tr>
		<tr>
			<td class="forumline tw50pct">
				<table>
					<tr><th colspan="2"><span class="genmed"><b>{L_INVISION_P_DETAILS}</b></span></th></tr>
					<tr>
						<td class="tw33pct row2"><b><span class="genmed">{L_INVISION_POSTS}</span></b></td>
						<td class="row1 tw64pct"><span class="genmed"><b>{POSTS}</b>&nbsp;{INVISION_POST_PERCENT_STATS}</span></td>
					</tr>
					<!-- IF S_POSTS_SECTION -->
					<tr>
						<td class="row2"><b><span class="genmed">{L_INVISION_PPD_STATS}</span></b></td>
						<td class="row1"><span class="genmed">{INVISION_POST_DAY_STATS}</span></td>
					</tr>
					<tr>
						<td class="row2"><b><span class="genmed">{L_INVISION_MOST_ACTIVE}</span></b></td>
						<td class="row1"><div class="genmed"><!-- IF INVISION_MOST_ACTIVE_FORUM_ID > 0 --><a href="{INVISION_MOST_ACTIVE_FORUM_URL}">{INVISION_MOST_ACTIVE_FORUM_NAME}</a><br />{L_INVISION_MOST_ACTIVE_POSTS}<!-- ELSE -->{L_NO_POSTS}<!-- ENDIF --></div></td>
					</tr>
					<!-- ENDIF -->
					<tr>
						<td class="row2"><b><span class="genmed">{L_RECENT_USER_ACTIVITY}</span></b></td>
						<td class="row1">
							<!-- IF S_EXTRA_STATS_AUTH -->
							<span class="genmed">[ <a href="{U_EXTRA_STATS}">{L_EXTRA_STATS}</a> ]</span><br />
							<!-- ENDIF -->
							<!-- IF S_POSTS_SECTION -->
							<span class="genmed">[ <a href="{U_USER_RECENT_TOPICS}">{L_USER_TOPICS_STARTED}</a> ]</span><br />
							<span class="genmed">[ <a href="{U_USER_RECENT_POSTS}">{L_USER_POSTS}</a> ]</span><br />
							<!-- ENDIF -->
							<!-- IF S_ADMIN -->
							<span class="genmed">[ <a href="{U_USER_RECENT_TOPICS_VIEW}">{L_USER_TOPICS_VIEWS}</a> ]</span>
							<!-- ENDIF -->
						</td>
					</tr>
					<!-- IF FEEDBACK -->
					<tr>
						<td class="row2 tw30pct"><b><span class="genmed">{L_FEEDBACK_RECEIVED}</span></b></td>
						<td class="row1">{FEEDBACK}</td>
					</tr>
					<!-- ENDIF -->
					<!-- IF S_ADMIN_MOD -->
					<tr><th colspan="2">{L_MODERATOR_IP_INFORMATION}:</th></tr>
					<tr>
						<td class="row2"><b><span class="genmed">{L_EMAIL_ADDRESS}</span></b></td>
						<td class="row1 post-buttons"><span class="genmed"><a href="mailto:{USER_EMAIL_ADDRESS}">{USER_EMAIL_ADDRESS}</a></span></td>
					</tr>
					<tr>
						<td class="row2"><b><span class="genmed">{L_REGISTERED_IP_ADDRESS}</span></b></td>
						<td class="row1 post-buttons"><span class="genmed">{U_USER_IP_ADDRESS}</span></td>
					</tr>
					<tr>
						<td class="row2"><b><span class="genmed">{L_REGISTERED_HOSTNAME}</span></b></td>
						<td class="row1 post-buttons"><span class="genmed">{USER_REGISTERED_HOSTNAME}</span></td>
					</tr>
					<!-- ENDIF -->
				</table>
			</td>
			<td class="tw2px"><img src="{SPACER}" width="7" height="1" alt="" /></td>
			<td class="forumline tw50pct">
				<table>
					<tr><th colspan="2"><span class="genmed"><b>{L_INVISION_A_STATS}</b></span></th></tr>
					<tr>
						<td class="row2"><b><span class="genmed">{L_JOINED}</span></b></td>
						<td class="row1"><span class="genmed">{JOINED}</span></td>
					</tr>
					<tr>
						<td class="row2"><b><span class="genmed">{L_LOGON}</span></b></td>
						<td class="row1"><span class="genmed">{LAST_LOGON}</span></td>
					</tr>
					<!-- IF S_ADMIN_MOD -->
					<tr>
						<td class="row2"><b><span class="genmed">{L_TOTAL_ONLINE_TIME}</span></b></td>
						<td class="row1"><span class="genmed">{TOTAL_ONLINE_TIME}</span></td>
					</tr>
					<!-- ENDIF -->
					<tr>
						<td class="row2"><b><span class="genmed">{L_Profile_viewed}</span></b></td>
						<td class="row1 post-buttons"><span class="genmed">{U_VISITS}</span></td>
					</tr>
					<!-- BEGIN switch_upload_limits -->
					<tr>
						<td class="row2"><b><span class="genmed">{L_UPLOAD_QUOTA}</span></b></td>
						<td class="row2">
							<table class="forumline tw200px">
								<tr>
									<td colspan="3" class="row2 tw190px tdnw"><img src="{BAR_GRAPHIC_LEFT}" width="4" height="12" alt="" /><img src="{BAR_GRAPHIC_BODY}" width="{UPLOAD_LIMIT_IMG_WIDTH}" height="12" alt="{INBOX_LIMIT_PERCENT}" /><img src="{BAR_GRAPHIC_RIGHT}" width="4" height="12" alt="" /></td>
								</tr>
								<tr>
									<td class="tw33pct row3"><span class="gensmall"><span class="text_green">0%</span></span></td>
									<td class="tw34pct row3 row-center"><span class="gensmall"><span class="text_blue">50%</span></span></td>
									<td class="tw33pct row3 row-right"><span class="gensmall"><span class="text_red">100%</span></span></td>
								</tr>
							</table>
							<span class="genmed">[{UPLOADED} / {QUOTA} / {PERCENT_FULL}]</span><br />
							<span class="gen"><a href="{U_UACP}" class="genmed">{L_UACP}</a></span>
						</td>
					</tr>
					<!-- END switch_upload_limits -->
					{CASH}
					<!-- BEGIN trophy -->
					<tr>
						<td class="row2"><b><span class="genmed">{trophy.TROPHY_TITLE}:</span></b></td>
						<td class="row1"><span class="genmed">{trophy.PROFILE_TROPHY}</span></td>
					</tr>
					<tr>
						<td class="row2"><b><span class="genmed">{PROFILE_TITLE}</span></b></td>
						<td class="row1"><span class="genmed">{PROFILE_TIME}</span></td>
					</tr>
					<!-- END trophy -->
				</table>
			</td>
		</tr>
		</table>
	</td>
	<td class="th2px">&nbsp;</td>
</tr>
<tr><td class="th2px">&nbsp;</td></tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}

{IMG_THL}{IMG_THC}<span class="forumlink">{L_EXTRA_PROFILE_INFO}</span>{IMG_THR}<table class="forumlinenb">
<tr><td class="row1"><div class="post-text post-text-hide-flow">{SELFDES}</div></td></tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}

<!-- BEGIN recent_pics_block -->
{IMG_THL}{IMG_THC}<span class="forumlink">{L_RECENT_PUBLIC_PICS}</span>{IMG_THR}<table class="forumlinenb">
<!-- BEGIN no_pics -->
<tr><td class="row1 row-center" colspan="{S_COLS}" height="50"><span class="gen">{L_NO_PICS}</span></td></tr>
<!-- END no_pics -->
<!-- BEGIN recent_pics -->
<tr>
	<!-- BEGIN recent_col -->
	<td class="row1 row-center" width="{S_COL_WIDTH}">
		<a href="{recent_pics_block.recent_pics.recent_col.U_PIC_DL}"{recent_pics_block.recent_pics.recent_col.PIC_PREVIEW_HS}><img class="vs10px" src="{recent_pics_block.recent_pics.recent_col.THUMBNAIL}" alt="{recent_pics_block.recent_pics.recent_col.DESC}" title="{recent_pics_block.recent_pics.recent_col.DESC}" /></a>
	</td>
	<!-- END recent_col -->
</tr>
<tr>
	<!-- BEGIN recent_detail -->
	<td class="row1 row-center">
		<div class="gensmall">
			{L_PIC_TITLE}: <a href="{recent_pics_block.recent_pics.recent_col.recent_detail.U_PIC_SP}">{recent_pics_block.recent_pics.recent_detail.PIC_TITLE}</a><br />
			{L_DOWNLOAD}: <a href="{recent_pics_block.recent_pics.recent_col.recent_detail.U_PIC_DL}">{recent_pics_block.recent_pics.recent_detail.PIC_TITLE}</a><br />
			{L_POSTER}: {recent_pics_block.recent_pics.recent_detail.POSTER}<br />
			{L_POSTED}: {recent_pics_block.recent_pics.recent_detail.TIME}<br />
			{L_VIEW}: {recent_pics_block.recent_pics.recent_detail.VIEW}<br />
		</div>
	</td>
	<!-- END recent_detail -->
</tr>
<!-- END recent_pics -->
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}
<!-- END recent_pics_block -->

<!-- IF S_EXTRA_STATS -->
<!-- INCLUDE profile_view_stats.tpl -->
<!-- ENDIF -->

<!-- BEGIN profile_char -->
{profile_char.CHAR_PROFILE}
<!-- END profile_char -->

<table><tr><td class="nav tdalignr"><br />{JUMPBOX}</td></tr></table>

<!-- INCLUDE overall_footer.tpl -->