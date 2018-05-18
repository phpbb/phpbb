<?php $this->vars['_TITLE'] = $this->vars['FORUM_NAME']; ?>
<!-- INCLUDE overall_header.tpl -->

{XS_NEWS}

{CALENDAR_BOX}

{BOARD_INDEX}

<!-- IF S_FORUM_RULES -->
<div class="block">
	<!-- IF S_FORUM_RULES_TITLE --><h2>{L_FORUM_RULES}</h2><!-- ENDIF -->
	{FORUM_RULES}
</div>
<!-- ENDIF -->

<div class="block-empty">
	<!-- IF PAGINATION -->{PAGE_NUMBER} {PAGINATION}<br /><!-- ENDIF -->
	<!-- IF not S_BOT --><a href="{U_POST_NEW_TOPIC}" class="gradient link">{L_POST_NEW_TOPIC}</a><!-- ENDIF -->
</div>

<form method="post" action="{S_POST_DAYS_ACTION}" style="display: inline;">
<!-- BEGIN topicrow -->
<div class="forum<!-- IF topicrow.CLASS_NEW --> forum{topicrow.CLASS_NEW}<!-- ENDIF -->" onclick="document.location.href='{topicrow.U_VIEW_TOPIC}'; return false;">
	<p><a href="{topicrow.U_VIEW_TOPIC}" class="{topicrow.TOPIC_CLASS}">{topicrow.TOPIC_TITLE}</a></p>
	<!-- IF topicrow.GOTO_PAGE_FULL --><p>{topicrow.GOTO_PAGE_FULL}</p><!-- ENDIF -->
	<p><span class="extra">{topicrow.REPLIES}</span> {topicrow.LAST_POST_TIME}</p>
</div>
<!-- END topicrow -->
<!-- BEGIN switch_no_topics -->
<div class="block">{L_NO_TOPICS}</div>
<!-- END switch_no_topics -->

<div class="block">
	<span class="genmed" style="float: right; text-align: right; vertical-align: middle; padding-right: 5px; padding-top: 5px;"><!-- IF S_TIMEZONE -->{S_TIMEZONE}<!-- ELSE -->&nbsp;<!-- ENDIF --></span>
	<div class="gensmall" style="text-align: left; padding-left: 5px; padding-top: 5px;">{L_DISPLAY_TOPICS}:&nbsp;{S_SELECT_TOPIC_DAYS}&nbsp;<input type="submit" class="liteoption jumpbox" value="{L_GO}" name="submit" /></div>
</div>
</form>

<!-- IF not S_BOT -->
<div class="block-empty">
	<!-- IF PAGINATION -->{PAGE_NUMBER} {PAGINATION}<br /><!-- ENDIF -->
	<a href="{U_POST_NEW_TOPIC}" class="gradient link">{L_POST_NEW_TOPIC}</a>
</div>
<!-- ENDIF -->

<!-- INCLUDE overall_footer.tpl -->