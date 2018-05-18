<!-- BEGIN switch_header_table -->
<div class="block">
	<h2>{L_STAFF_MESSAGE}</h2>
	{switch_header_table.HEADER_TEXT}
</div>
<!-- END switch_header_table -->

<!-- BEGIN ctracker_message -->
<div class="block">
	{ctracker_message.L_MESSAGE_TEXT}
	<!-- IF ctracker_message.U_MARK_MESSAGE --><br /><br /><a href="{ctracker_message.U_MARK_MESSAGE}">{ctracker_message.L_MARK_MESSAGE}</a><!-- ENDIF --></span><br /></td></tr>
</div>
<!-- END ctracker_message -->

<!-- IF S_LOGGED_IN -->
	<!-- IF NEW_PM_SWITCH --><div class="block"><a href="{FULL_SITE_PATH}{U_PRIVATEMSGS}">{PRIVATE_MESSAGE_INFO}</a></div><!-- ENDIF -->
<!-- ENDIF -->

<!-- BEGIN switch_admin_disable_board -->
<div class="block">{L_BOARD_DISABLE}</div>
<!-- END switch_admin_disable_board -->

<!-- IF SWITCH_CMS_GLOBAL_BLOCKS -->
	<!-- BEGIN header_blocks_row -->{header_blocks_row.CMS_BLOCK}<!-- END header_blocks_row -->
	<!-- BEGIN headerleft_blocks_row -->{headerleft_blocks_row.CMS_BLOCK}<!-- END headerleft_blocks_row -->
	<!-- IF HC_BLOCK --><!-- BEGIN headercenter_blocks_row -->{headercenter_blocks_row.CMS_BLOCK}<!-- END headercenter_blocks_row --><!-- ENDIF -->
<!-- ENDIF -->