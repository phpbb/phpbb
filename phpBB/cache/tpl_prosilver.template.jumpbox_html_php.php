<?php

// eXtreme Styles mod cache. Generated on Mon, 24 Sep 2018 03:28:06 +0000 (time=1537759686)

?>
<div class="action-bar actions-jump">
	<?php if ($this->vars['S_VIEWTOPIC']) {  ?>
		<p class="jumpbox-return"><a href="<?php echo isset($this->vars['U_VIEW_FORUM']) ? $this->vars['U_VIEW_FORUM'] : $this->lang('U_VIEW_FORUM'); ?>" class="left-box arrow-<?php echo isset($this->vars['S_CONTENT_FLOW_BEGIN']) ? $this->vars['S_CONTENT_FLOW_BEGIN'] : $this->lang('S_CONTENT_FLOW_BEGIN'); ?>" accesskey="r"><?php echo isset($this->vars['L_RETURN_TO_FORUM']) ? $this->vars['L_RETURN_TO_FORUM'] : $this->lang('L_RETURN_TO_FORUM'); ?></a></p>
	<?php } elseif ($this->vars['S_VIEWFORUM']) {  ?>
		<p class="jumpbox-return"><a href="<?php echo isset($this->vars['U_INDEX']) ? $this->vars['U_INDEX'] : $this->lang('U_INDEX'); ?>" class="left-box arrow-<?php echo isset($this->vars['S_CONTENT_FLOW_BEGIN']) ? $this->vars['S_CONTENT_FLOW_BEGIN'] : $this->lang('S_CONTENT_FLOW_BEGIN'); ?>" accesskey="r"><?php echo isset($this->vars['L_RETURN_TO_INDEX']) ? $this->vars['L_RETURN_TO_INDEX'] : $this->lang('L_RETURN_TO_INDEX'); ?></a></p>
	<?php } elseif ($this->vars['SEARCH_TOPIC']) {  ?>
		<p class="jumpbox-return"><a class="left-box arrow-<?php echo isset($this->vars['S_CONTENT_FLOW_BEGIN']) ? $this->vars['S_CONTENT_FLOW_BEGIN'] : $this->lang('S_CONTENT_FLOW_BEGIN'); ?>" href="<?php echo isset($this->vars['U_SEARCH_TOPIC']) ? $this->vars['U_SEARCH_TOPIC'] : $this->lang('U_SEARCH_TOPIC'); ?>" accesskey="r"><?php echo isset($this->vars['L_RETURN_TO_TOPIC']) ? $this->vars['L_RETURN_TO_TOPIC'] : $this->lang('L_RETURN_TO_TOPIC'); ?></a></p>
	<?php } elseif ($this->vars['S_SEARCH_ACTION']) {  ?>
		<p class="jumpbox-return"><a class="left-box arrow-<?php echo isset($this->vars['S_CONTENT_FLOW_BEGIN']) ? $this->vars['S_CONTENT_FLOW_BEGIN'] : $this->lang('S_CONTENT_FLOW_BEGIN'); ?>" href="<?php echo isset($this->vars['U_SEARCH']) ? $this->vars['U_SEARCH'] : $this->lang('U_SEARCH'); ?>" title="<?php echo isset($this->vars['L_SEARCH_ADV']) ? $this->vars['L_SEARCH_ADV'] : $this->lang('L_SEARCH_ADV'); ?>" accesskey="r"><?php echo isset($this->vars['L_GO_TO_SEARCH_ADV']) ? $this->vars['L_GO_TO_SEARCH_ADV'] : $this->lang('L_GO_TO_SEARCH_ADV'); ?></a></p>
	<?php } ?>

	<?php if ($this->vars['S_DISPLAY_JUMPBOX']) {  ?>

		<div class="dropdown-container dropdown-container-<?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?><?php if (! $this->vars['S_IN_MCP']) {  ?> dropdown-up<?php } ?> dropdown-<?php echo isset($this->vars['S_CONTENT_FLOW_BEGIN']) ? $this->vars['S_CONTENT_FLOW_BEGIN'] : $this->lang('S_CONTENT_FLOW_BEGIN'); ?> dropdown-button-control" id="jumpbox">
			<span title="<?php if ($this->vars['S_IN_MCP'] && $this->vars['S_MERGE_SELECT']) {  ?><?php echo isset($this->vars['L_SELECT_TOPICS_FROM']) ? $this->vars['L_SELECT_TOPICS_FROM'] : $this->lang('L_SELECT_TOPICS_FROM'); ?><?php } elseif ($this->vars['S_IN_MCP']) {  ?><?php echo isset($this->vars['L_MODERATE_FORUM']) ? $this->vars['L_MODERATE_FORUM'] : $this->lang('L_MODERATE_FORUM'); ?><?php } else { ?><?php echo isset($this->vars['L_JUMP_TO']) ? $this->vars['L_JUMP_TO'] : $this->lang('L_JUMP_TO'); ?><?php } ?>" class="dropdown-trigger button dropdown-select">
				<?php if ($this->vars['S_IN_MCP'] && $this->vars['S_MERGE_SELECT']) {  ?><?php echo isset($this->vars['L_SELECT_TOPICS_FROM']) ? $this->vars['L_SELECT_TOPICS_FROM'] : $this->lang('L_SELECT_TOPICS_FROM'); ?><?php } elseif ($this->vars['S_IN_MCP']) {  ?><?php echo isset($this->vars['L_MODERATE_FORUM']) ? $this->vars['L_MODERATE_FORUM'] : $this->lang('L_MODERATE_FORUM'); ?><?php } else { ?><?php echo isset($this->vars['L_JUMP_TO']) ? $this->vars['L_JUMP_TO'] : $this->lang('L_JUMP_TO'); ?><?php } ?>
			</span>
			<div class="dropdown hidden">
				<div class="pointer"><div class="pointer-inner"></div></div>
				<ul class="dropdown-contents">
				<?php

$jumpbox_forums_count = ( isset($this->_tpldata['jumpbox_forums.']) ) ?  sizeof($this->_tpldata['jumpbox_forums.']) : 0;
for ($jumpbox_forums_i = 0; $jumpbox_forums_i < $jumpbox_forums_count; $jumpbox_forums_i++)
{
 $jumpbox_forums_item = &$this->_tpldata['jumpbox_forums.'][$jumpbox_forums_i];
 $jumpbox_forums_item['S_ROW_COUNT'] = $jumpbox_forums_i;
 $jumpbox_forums_item['S_NUM_ROWS'] = $jumpbox_forums_count;

?>
					<?php if ($jumpbox_forums_item['FORUM_ID'] != -1) {  ?>
						<li><?php

$level_count = ( isset($jumpbox_forums_item['level.']) ) ? sizeof($jumpbox_forums_item['level.']) : 0;
for ($level_i = 0; $level_i < $level_count; $level_i++)
{
 $level_item = &$jumpbox_forums_item['level.'][$level_i];
 $level_item['S_ROW_COUNT'] = $level_i;
 $level_item['S_NUM_ROWS'] = $level_count;

?>&nbsp; &nbsp;<?php

} // END level

if(isset($level_item)) { unset($level_item); } 

?><a href="<?php echo isset($jumpbox_forums_item['LINK']) ? $jumpbox_forums_item['LINK'] : ''; ?>"><?php echo isset($jumpbox_forums_item['FORUM_NAME']) ? $jumpbox_forums_item['FORUM_NAME'] : ''; ?></a></li>
					<?php } ?>
				<?php

} // END jumpbox_forums

if(isset($jumpbox_forums_item)) { unset($jumpbox_forums_item); } 

?>
				</ul>
			</div>
		</div>

	<?php } else { ?>
	</br></br>
	<?php } ?>
</div>
