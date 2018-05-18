<!-- INCLUDE breadcrumbs_vt.tpl -->
<br clear="all" />

<!-- IF S_FORUM_RULES -->
<div class="block">
	<!-- IF S_FORUM_RULES_TITLE --><h2>{L_FORUM_RULES}</h2><!-- ENDIF -->
	{FORUM_RULES}
</div>
<!-- ENDIF -->

<div class="block-empty">
	<h2><a href="{U_VIEW_TOPIC_BASE}" style="text-decoration: none;">{TOPIC_TITLE}</a></h2>
	<!-- IF not S_BOT -->
		<a href="{U_POST_NEW_TOPIC}" class="gradient link">{L_POST_NEW_TOPIC}</a>
		<a href="{U_POST_REPLY_TOPIC}" class="gradient link">{L_POST_REPLY_TOPIC}</a>
	<!-- ENDIF -->
	{PAGE_NUMBER} {PAGINATION}<br />
</div>

{POLL_DISPLAY}
{REG_DISPLAY}

<!-- BEGIN postrow -->
<div class="block post" style="position: relative;">
	<div class="popup right" style="position: static;">
		{postrow.POSTER_NAME}
		<div class="block">
			<p class="post-details">
				<!-- IF not postrow.S_THIS_POSTER_MASK -->
				{postrow.POSTER_JOINED}<br />
				{postrow.POSTER_POSTS}<br />
				<!-- BEGIN author_profile -->
				{postrow.author_profile.AUTHOR_VAL}<br />
				<!-- END author_profile -->
				<!-- ENDIF -->
			</p>
			<div class="extra-top-padding">
				<!-- IF not postrow.S_THIS_POSTER_MASK -->{postrow.PROFILE_IMG} {postrow.PM_IMG} {postrow.EMAIL_IMG} {postrow.WWW_IMG}<!-- ENDIF -->&nbsp;
			</div>
		</div>
	</div>
	<!-- IF not S_BOT and S_LOGGED_IN -->
	<div class="right popup" style="position: static;">
		<a href="javascript:void(0);" class="gradient button-main"><span></span></a>
		<ul class="menu">
			<li>{postrow.QUOTE}</li>
			<li>{postrow.EDIT}</li>
			<li>{postrow.DELETE}</li>
		</ul>
	</div>
	<!-- ENDIF -->
	<h2>{postrow.POST_SUBJECT}</h2>
	<p class="post-time">{postrow.POST_DATE}</p>
			{postrow.MESSAGE}<br />
			{postrow.ATTACHMENTS}
	<div class="clear"></div>
</div>
<!-- END postrow -->


