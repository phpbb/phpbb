<?php

// eXtreme Styles mod cache. Generated on Fri, 27 Jul 2018 16:49:04 +0000 (time=1532710144)

?><h1><?php echo isset($this->vars['L_FORUM_PRUNE']) ? $this->vars['L_FORUM_PRUNE'] : $this->lang('L_FORUM_PRUNE'); ?></h1>
<form method="post" action="<?php echo isset($this->vars['S_FORUMPRUNE_ACTION']) ? $this->vars['S_FORUMPRUNE_ACTION'] : $this->lang('S_FORUMPRUNE_ACTION'); ?>">
<fieldset>
	<legend><?php echo isset($this->vars['L_SELECT_FORUM']) ? $this->vars['L_SELECT_FORUM'] : $this->lang('L_SELECT_FORUM'); ?></legend>
	<p><?php echo isset($this->vars['S_FORUMS_SELECT']) ? $this->vars['S_FORUMS_SELECT'] : $this->lang('S_FORUMS_SELECT'); ?>&nbsp; <input type="submit" name="pruneset" value="<?php echo isset($this->vars['L_LOOK_UP']) ? $this->vars['L_LOOK_UP'] : $this->lang('L_LOOK_UP'); ?>" class="button2" /></p>
</fieldset>
</form>