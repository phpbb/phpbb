<!-- INCLUDE overall_header.tpl -->

<?php
$catrow_count = (isset($this->_tpldata['catrow.'])) ? count($this->_tpldata['catrow.']) : 0;
for($catrow_i = 0; $catrow_i < $catrow_count; $catrow_i++)
{
	$catrow_item = &$this->_tpldata['catrow.'][$catrow_i];
	// check for new messages
	$new_msg = false;
	$forumrow_count = (isset($catrow_item['forumrow.'])) ? count($catrow_item['forumrow.']) : 0;
	for ($forumrow_i = 0; $forumrow_i < $forumrow_count; $forumrow_i++)
	{
		$forumrow_item = &$catrow_item['forumrow.'][$forumrow_i];
		$new_item = strpos($forumrow_item['FORUM_FOLDER_IMG'], '_unread') > 0 ? true : false;
		if($new_item)
		{
			$new_msg = true;
			$forumrow_item['LINK_CLASS'] = '-new';
		}
	}
	// add xs switch
	$catrow_item['LINK_CLASS'] = $new_msg ? '-new' : '';
}

?>
<!-- INCLUDE index_body.tpl -->

<!-- INCLUDE overall_footer.tpl -->