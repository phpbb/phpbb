<?php

// eXtreme Styles mod cache. Generated on Sat, 26 May 2018 21:49:23 +0000 (time=1527371363)

?><ul>
<?php if ($this->vars['BASE_URL'] && $this->vars['TOTAL_PAGES'] > 6) {  ?>
	<li class="dropdown-container dropdown-button-control dropdown-page-jump page-jump">
		<a href="#" class="dropdown-trigger" title="<?php echo isset($this->vars['L_JUMP_TO_PAGE_CLICK']) ? $this->vars['L_JUMP_TO_PAGE_CLICK'] : $this->lang('L_JUMP_TO_PAGE_CLICK'); ?>" role="button"><?php echo isset($this->vars['PAGE_NUMBER']) ? $this->vars['PAGE_NUMBER'] : $this->lang('PAGE_NUMBER'); ?></a>
		<div class="dropdown hidden">
			<div class="pointer"><div class="pointer-inner"></div></div>
			<ul class="dropdown-contents">
				<li><?php echo isset($this->vars['L_JUMP_TO_PAGE']) ? $this->vars['L_JUMP_TO_PAGE'] : $this->lang('L_JUMP_TO_PAGE'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></li>
				<li class="page-jump-form">
					<input type="number" name="page-number" min="1" max="999999" title="<?php echo isset($this->vars['L_JUMP_PAGE']) ? $this->vars['L_JUMP_PAGE'] : $this->lang('L_JUMP_PAGE'); ?>" class="inputbox tiny" data-per-page="<?php echo isset($this->vars['PER_PAGE']) ? $this->vars['PER_PAGE'] : $this->lang('PER_PAGE'); ?>" data-base-url="{BASE_URL|e('html_attr')}" data-start-name="<?php echo isset($this->vars['START_NAME']) ? $this->vars['START_NAME'] : $this->lang('START_NAME'); ?>" />
					<input class="button2" value="<?php echo isset($this->vars['L_GO']) ? $this->vars['L_GO'] : $this->lang('L_GO'); ?>" type="button" />
				</li>
			</ul>
		</div>
	</li>
<?php } ?>
<?php

$pagination_count = ( isset($this->_tpldata['pagination.']) ) ?  sizeof($this->_tpldata['pagination.']) : 0;
for ($pagination_i = 0; $pagination_i < $pagination_count; $pagination_i++)
{
 $pagination_item = &$this->_tpldata['pagination.'][$pagination_i];
 $pagination_item['S_ROW_COUNT'] = $pagination_i;
 $pagination_item['S_NUM_ROWS'] = $pagination_count;

?>
	<?php if ($pagination_item['S_IS_PREV']) {  ?>
	<li class="previous"><a href="<?php echo isset($pagination_item['PAGE_URL']) ? $pagination_item['PAGE_URL'] : ''; ?>" rel="prev" role="button"><?php echo isset($this->vars['L_PREVIOUS']) ? $this->vars['L_PREVIOUS'] : $this->lang('L_PREVIOUS'); ?></a></li>
	<?php } elseif ($pagination_item['S_IS_CURRENT']) {  ?>
	<li class="active"><span><?php echo isset($pagination_item['PAGE_NUMBER']) ? $pagination_item['PAGE_NUMBER'] : ''; ?></span></li>
	<?php } elseif ($pagination_item['S_IS_ELLIPSIS']) {  ?>
	<li class="ellipsis" role="separator"><span><?php echo isset($this->vars['L_ELLIPSIS']) ? $this->vars['L_ELLIPSIS'] : $this->lang('L_ELLIPSIS'); ?></span></li>
	<?php } elseif ($pagination_item['S_IS_NEXT']) {  ?>
	<li class="next"><a href="<?php echo isset($pagination_item['PAGE_URL']) ? $pagination_item['PAGE_URL'] : ''; ?>" rel="next" role="button"><?php echo isset($this->vars['L_NEXT']) ? $this->vars['L_NEXT'] : $this->lang('L_NEXT'); ?></a></li>
	<?php } else { ?>
	<li><a href="<?php echo isset($pagination_item['PAGE_URL']) ? $pagination_item['PAGE_URL'] : ''; ?>" role="button"><?php echo isset($pagination_item['PAGE_NUMBER']) ? $pagination_item['PAGE_NUMBER'] : ''; ?></a></li>
	<?php } ?>
<?php

} // END pagination

if(isset($pagination_item)) { unset($pagination_item); } 

?>
</ul>
