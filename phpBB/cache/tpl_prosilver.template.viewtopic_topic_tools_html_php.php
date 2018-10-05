<?php

// eXtreme Styles mod cache. Generated on Wed, 26 Sep 2018 03:12:08 +0000 (time=1537931528)

?><?php if (! $this->vars['S_IS_BOT'] && ( $this->vars['U_WATCH_TOPIC'] || $this->vars['U_BOOKMARK_TOPIC'] || $this->vars['U_BUMP_TOPIC'] || $this->vars['U_EMAIL_TOPIC'] || $this->vars['U_PRINT_TOPIC'] || $this->vars['S_DISPLAY_TOPIC_TOOLS'] )) {  ?>
	<div class="dropdown-container dropdown-button-control topic-tools">
		<span title="<?php echo isset($this->vars['L_TOPIC_TOOLS']) ? $this->vars['L_TOPIC_TOOLS'] : $this->lang('L_TOPIC_TOOLS'); ?>" class="button icon-button tools-icon dropdown-trigger dropdown-select"></span>
		<div class="dropdown hidden">
			<div class="pointer"><div class="pointer-inner"></div></div>
			<ul class="dropdown-contents">
				<!-- EVENT viewtopic_topic_tools_before -->
				<?php if ($this->vars['U_WATCH_TOPIC']) {  ?>
					<li class="small-icon icon-<?php if ($this->vars['S_WATCHING_TOPIC']) {  ?>unsubscribe<?php } else { ?>subscribe<?php } ?>">
						<a href="<?php echo isset($this->vars['U_WATCH_TOPIC']) ? $this->vars['U_WATCH_TOPIC'] : $this->lang('U_WATCH_TOPIC'); ?>" class="watch-topic-link" title="<?php echo isset($this->vars['S_WATCH_TOPIC_TITLE']) ? $this->vars['S_WATCH_TOPIC_TITLE'] : $this->lang('S_WATCH_TOPIC_TITLE'); ?>" data-ajax="toggle_link" data-toggle-class="small-icon icon-<?php if (! $this->vars['S_WATCHING_TOPIC']) {  ?>unsubscribe<?php } else { ?>subscribe<?php } ?>" data-toggle-text="<?php echo isset($this->vars['S_WATCH_TOPIC_TOGGLE']) ? $this->vars['S_WATCH_TOPIC_TOGGLE'] : $this->lang('S_WATCH_TOPIC_TOGGLE'); ?>" data-toggle-url="<?php echo isset($this->vars['U_WATCH_TOPIC_TOGGLE']) ? $this->vars['U_WATCH_TOPIC_TOGGLE'] : $this->lang('U_WATCH_TOPIC_TOGGLE'); ?>" data-update-all=".watch-topic-link"><?php echo isset($this->vars['S_WATCH_TOPIC_TITLE']) ? $this->vars['S_WATCH_TOPIC_TITLE'] : $this->lang('S_WATCH_TOPIC_TITLE'); ?></a>
					</li>
				<?php } ?>
				<?php if ($this->vars['U_BOOKMARK_TOPIC']) {  ?>
					<li class="small-icon icon-bookmark">
						<a href="<?php echo isset($this->vars['U_BOOKMARK_TOPIC']) ? $this->vars['U_BOOKMARK_TOPIC'] : $this->lang('U_BOOKMARK_TOPIC'); ?>" class="bookmark-link" title="<?php echo isset($this->vars['L_BOOKMARK_TOPIC']) ? $this->vars['L_BOOKMARK_TOPIC'] : $this->lang('L_BOOKMARK_TOPIC'); ?>" data-ajax="alt_text" data-alt-text="<?php echo isset($this->vars['S_BOOKMARK_TOGGLE']) ? $this->vars['S_BOOKMARK_TOGGLE'] : $this->lang('S_BOOKMARK_TOGGLE'); ?>" data-update-all=".bookmark-link"><?php echo isset($this->vars['S_BOOKMARK_TOPIC']) ? $this->vars['S_BOOKMARK_TOPIC'] : $this->lang('S_BOOKMARK_TOPIC'); ?></a>
					</li>
				<?php } ?>
				<?php if ($this->vars['U_BUMP_TOPIC']) {  ?><li class="small-icon icon-bump"><a href="<?php echo isset($this->vars['U_BUMP_TOPIC']) ? $this->vars['U_BUMP_TOPIC'] : $this->lang('U_BUMP_TOPIC'); ?>" title="<?php echo isset($this->vars['L_BUMP_TOPIC']) ? $this->vars['L_BUMP_TOPIC'] : $this->lang('L_BUMP_TOPIC'); ?>" data-ajax="true"><?php echo isset($this->vars['L_BUMP_TOPIC']) ? $this->vars['L_BUMP_TOPIC'] : $this->lang('L_BUMP_TOPIC'); ?></a></li><?php } ?>
				<?php if ($this->vars['U_EMAIL_TOPIC']) {  ?><li class="small-icon icon-sendemail"><a href="<?php echo isset($this->vars['U_EMAIL_TOPIC']) ? $this->vars['U_EMAIL_TOPIC'] : $this->lang('U_EMAIL_TOPIC'); ?>" title="<?php echo isset($this->vars['L_EMAIL_TOPIC']) ? $this->vars['L_EMAIL_TOPIC'] : $this->lang('L_EMAIL_TOPIC'); ?>"><?php echo isset($this->vars['L_EMAIL_TOPIC']) ? $this->vars['L_EMAIL_TOPIC'] : $this->lang('L_EMAIL_TOPIC'); ?></a></li><?php } ?>
				<?php if ($this->vars['U_PRINT_TOPIC']) {  ?><li class="small-icon icon-print"><a href="<?php echo isset($this->vars['U_PRINT_TOPIC']) ? $this->vars['U_PRINT_TOPIC'] : $this->lang('U_PRINT_TOPIC'); ?>" title="<?php echo isset($this->vars['L_PRINT_TOPIC']) ? $this->vars['L_PRINT_TOPIC'] : $this->lang('L_PRINT_TOPIC'); ?>" accesskey="p"><?php echo isset($this->vars['L_PRINT_TOPIC']) ? $this->vars['L_PRINT_TOPIC'] : $this->lang('L_PRINT_TOPIC'); ?></a></li><?php } ?>
				<!-- EVENT viewtopic_topic_tools_after -->
			</ul>
		</div>
	</div>
<?php } ?>
