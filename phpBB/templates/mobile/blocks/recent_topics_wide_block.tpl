<table>
<tr>
	<td align="left">
		<span class="gensmall">
			<!-- BEGIN scroll -->
			<marquee id="recent_topics" behavior="scroll" direction="up" height="200" scrolldelay="100" scrollamount="2" loop="true" onmouseover="this.stop()" onmouseout="this.start()">
				<!-- BEGIN recent_topic_row -->
				<b>&nbsp;&#8226;&nbsp;</b>{scroll.recent_topic_row.S_POSTTIME}<b>{NAV_SEP}</b>
				<b><a href="{scroll.recent_topic_row.U_FORUM}" title="{scroll.recent_topic_row.L_FORUM}">{scroll.recent_topic_row.L_FORUM}</a>{NAV_SEP}
				<a href="{scroll.recent_topic_row.U_TITLE}" title="{scroll.recent_topic_row.L_TITLE}">{scroll.recent_topic_row.L_TITLE}</a>{NAV_SEP}</b>
				{scroll.recent_topic_row.S_POSTER}<br /><br />
				<!-- END recent_topic_row -->
			</marquee>
			<!-- END scroll -->
			<!-- BEGIN static -->
				<!-- BEGIN recent_topic_row -->
				<b>&nbsp;&#8226;&nbsp;</b>{scroll.recent_topic_row.S_POSTTIME}<b>{NAV_SEP}</b>
				<b><a href="{static.recent_topic_row.U_FORUM}" title="{static.recent_topic_row.L_FORUM}">{static.recent_topic_row.L_FORUM}</a>{NAV_SEP}
				<a href="{static.recent_topic_row.U_TITLE}" title="{static.recent_topic_row.L_TITLE}">{static.recent_topic_row.L_TITLE}</a>{NAV_SEP}</b>
				{static.recent_topic_row.S_POSTER}<br /><br />
				<!-- END recent_topic_row -->
			<!-- END static -->
		</span>
	</td>
</tr>
</table>