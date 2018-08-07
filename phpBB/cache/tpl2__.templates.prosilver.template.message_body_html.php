<?php

// eXtreme Styles mod cache. Generated on Sat, 26 May 2018 20:22:33 +0000 (time=1527366153)

?><?php if ($this->vars['S_SIMPLE_MESSAGE']) {  ?>
	<?php  $this->set_filename('xs_include_e6e1a0c90af49152b5c80a1104de8196', 'simple_header.html', true);  $this->pparse('xs_include_e6e1a0c90af49152b5c80a1104de8196');  ?>
<?php } else { ?>
	<!-- INCLUDEX overall_header.html -->
<?php } ?>

<div class="panel" id="message">
	<div class="inner">
	<h2 class="message-title"><?php echo isset($this->vars['MESSAGE_TITLE']) ? $this->vars['MESSAGE_TITLE'] : $this->lang('MESSAGE_TITLE'); ?></h2>
	<p><?php echo isset($this->vars['MESSAGE_TEXT']) ? $this->vars['MESSAGE_TEXT'] : $this->lang('MESSAGE_TEXT'); ?></p>
	<?php if ($this->vars['SCRIPT_NAME'] == "search" && ! $this->vars['S_BOARD_DISABLED'] && ! $this->vars['S_NO_SEARCH'] && $this->vars['L_RETURN_TO_SEARCH_ADV']) {  ?><p><a href="<?php echo isset($this->vars['U_SEARCH']) ? $this->vars['U_SEARCH'] : $this->lang('U_SEARCH'); ?>" class="arrow-<?php echo isset($this->vars['S_CONTENT_FLOW_BEGIN']) ? $this->vars['S_CONTENT_FLOW_BEGIN'] : $this->lang('S_CONTENT_FLOW_BEGIN'); ?>"><?php echo isset($this->vars['L_GO_TO_SEARCH_ADV']) ? $this->vars['L_GO_TO_SEARCH_ADV'] : $this->lang('L_GO_TO_SEARCH_ADV'); ?></a></p><?php } ?>
	</div>
</div>

<?php if ($this->vars['S_SIMPLE_MESSAGE']) {  ?>
	<?php  $this->set_filename('xs_include_a74c392848adadc00acb0f3667a15b9d', 'simple_footer.html', true);  $this->pparse('xs_include_a74c392848adadc00acb0f3667a15b9d');  ?>
<?php } else { ?>
	<!-- INCLUDEX overall_footer.html -->
<?php } ?>
