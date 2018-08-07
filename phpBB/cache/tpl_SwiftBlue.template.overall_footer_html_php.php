<?php

// eXtreme Styles mod cache. Generated on Fri, 27 Jul 2018 16:44:47 +0000 (time=1532709887)

?>	<?php if (! $this->vars['S_IS_BOT']) {  ?><?php echo isset($this->vars['RUN_CRON_TASK']) ? $this->vars['RUN_CRON_TASK'] : $this->lang('RUN_CRON_TASK'); ?><?php } ?>
</div>

<!--
	We request you retain the full copyright notice below including the link to www.phpbb.com.
	This not only gives respect to the large amount of time given freely by the developers
	but also helps build interest, traffic and use of phpBB3. If you (honestly) cannot retain
	the full copyright we ask you at least leave in place the "Powered by phpBB" line, with
	"phpBB" linked to www.phpbb.com. If you refuse to include even this then support on our
	forums may be affected.

	The phpBB Group : 2006
//-->

<div id="wrapfooter">
	<?php if ($this->vars['U_ACP']) {  ?><span class="gensmall">[ <a href="<?php echo isset($this->vars['U_ACP']) ? $this->vars['U_ACP'] : $this->lang('U_ACP'); ?>"><?php echo isset($this->vars['L_ACP']) ? $this->vars['L_ACP'] : $this->lang('L_ACP'); ?></a> ]</span><br /><br /><?php } ?>
	<span class="copyright">Powered by <a href="http://www.phpbb.com/">phpBB</a> &copy; 2000, 2002, 2005, 2007 phpBB Group
	<?php if ($this->vars['TRANSLATION_INFO']) {  ?><br /><?php echo isset($this->vars['TRANSLATION_INFO']) ? $this->vars['TRANSLATION_INFO'] : $this->lang('TRANSLATION_INFO'); ?><?php } ?>
	<?php if ($this->vars['DEBUG_OUTPUT']) {  ?><br /><bdo dir="ltr">[ <?php echo isset($this->vars['DEBUG_OUTPUT']) ? $this->vars['DEBUG_OUTPUT'] : $this->lang('DEBUG_OUTPUT'); ?> ]</bdo><?php } ?></span>
</div>

</body>
</html>