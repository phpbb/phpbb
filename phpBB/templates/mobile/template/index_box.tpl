<?php
$banner_processed = false;
$catrow_count = (isset($this->_tpldata['catrow.'])) ? count($this->_tpldata['catrow.']) : 0;
for($catrow_i = 0; $catrow_i < $catrow_count; $catrow_i++)
{
	$catrow_item = &$this->_tpldata['catrow.'][$catrow_i];
	// check for new messages
	$new_msg = false;
	$forumrow_count = isset($catrow_item['forumrow.']) ? count($catrow_item['forumrow.']) : 0;
	for ($forumrow_i = 0; $forumrow_i < $forumrow_count; $forumrow_i++)
	{
		$forumrow_item = &$catrow_item['forumrow.'][$forumrow_i];
		$new_item = strpos($forumrow_item['FORUM_FOLDER_IMG'], '_unread') > 0 ? true : false;
		if($new_item)
		{
			$new_msg = true;
			$forumrow_item['LINK_CLASS'] = ' row-new';
		}
	}
	// add xs switch
	$catrow_item['LINK_CLASS'] = $new_msg ? ' row-new' : '';
}
?>
<!-- BEGIN catrow -->
<!-- BEGIN cathead -->
<div class="forum-cat">
	{catrow.cathead.CAT_TITLE}
</div>
<!-- END cathead -->
<!-- BEGIN forumrow -->
<div class="forum{forumrow.LINK_CLASS}" onclick="document.location.href='{catrow.forumrow.U_VIEWFORUM}'; return false;">
	<p><a href="{catrow.forumrow.U_VIEWFORUM}">{catrow.forumrow.FORUM_NAME}</a></p>
	<!-- BEGIN forum_link_no -->
	<p>
		<span class="extra" title="{L_TOPICS}, {L_POSTS}">{catrow.forumrow.TOPICS}, {catrow.forumrow.POSTS}</span>
	</p>
	<!-- END forum_link_no -->
</div>	
<!-- END forumrow -->
<!-- END catrow -->