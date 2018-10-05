<?php

// eXtreme Styles mod cache. Generated on Mon, 24 Sep 2018 03:28:06 +0000 (time=1537759686)

?>		<!-- EVENT overall_footer_content_after -->
	</div>

<!-- EVENT overall_footer_page_body_after -->

<div id="page-footer" role="contentinfo">
	<?php  $this->set_filename('xs_include_73057b09c7ae47eacd77b7d5e8f5ce79', 'navbar_footer.html', true);  $this->pparse('xs_include_73057b09c7ae47eacd77b7d5e8f5ce79');  ?>

	<div class="copyright">
		<!-- EVENT overall_footer_copyright_prepend -->
		<?php echo isset($this->vars['CREDIT_LINE']) ? $this->vars['CREDIT_LINE'] : $this->lang('CREDIT_LINE'); ?>
		<?php if ($this->vars['TRANSLATION_INFO']) {  ?><br /><?php echo isset($this->vars['TRANSLATION_INFO']) ? $this->vars['TRANSLATION_INFO'] : $this->lang('TRANSLATION_INFO'); ?><?php } ?>
		<!-- EVENT overall_footer_copyright_append -->
		<?php if ($this->vars['DEBUG_OUTPUT']) {  ?><br /><?php echo isset($this->vars['DEBUG_OUTPUT']) ? $this->vars['DEBUG_OUTPUT'] : $this->lang('DEBUG_OUTPUT'); ?><?php } ?>
		<?php if ($this->vars['U_ACP']) {  ?><br /><strong><a href="<?php echo isset($this->vars['U_ACP']) ? $this->vars['U_ACP'] : $this->lang('U_ACP'); ?>"><?php echo isset($this->vars['L_ACP']) ? $this->vars['L_ACP'] : $this->lang('L_ACP'); ?></a></strong><?php } ?>
	</div>

	<div id="darkenwrapper" data-ajax-error-title="<?php echo isset($this->vars['L_AJAX_ERROR_TITLE']) ? $this->vars['L_AJAX_ERROR_TITLE'] : $this->lang('L_AJAX_ERROR_TITLE'); ?>" data-ajax-error-text="<?php echo isset($this->vars['L_AJAX_ERROR_TEXT']) ? $this->vars['L_AJAX_ERROR_TEXT'] : $this->lang('L_AJAX_ERROR_TEXT'); ?>" data-ajax-error-text-abort="<?php echo isset($this->vars['L_AJAX_ERROR_TEXT_ABORT']) ? $this->vars['L_AJAX_ERROR_TEXT_ABORT'] : $this->lang('L_AJAX_ERROR_TEXT_ABORT'); ?>" data-ajax-error-text-timeout="<?php echo isset($this->vars['L_AJAX_ERROR_TEXT_TIMEOUT']) ? $this->vars['L_AJAX_ERROR_TEXT_TIMEOUT'] : $this->lang('L_AJAX_ERROR_TEXT_TIMEOUT'); ?>" data-ajax-error-text-parsererror="<?php echo isset($this->vars['L_AJAX_ERROR_TEXT_PARSERERROR']) ? $this->vars['L_AJAX_ERROR_TEXT_PARSERERROR'] : $this->lang('L_AJAX_ERROR_TEXT_PARSERERROR'); ?>">
		<div id="darken">&nbsp;</div>
	</div>

	<div id="phpbb_alert" class="phpbb_alert" data-l-err="<?php echo isset($this->vars['L_ERROR']) ? $this->vars['L_ERROR'] : $this->lang('L_ERROR'); ?>" data-l-timeout-processing-req="<?php echo isset($this->vars['L_TIMEOUT_PROCESSING_REQ']) ? $this->vars['L_TIMEOUT_PROCESSING_REQ'] : $this->lang('L_TIMEOUT_PROCESSING_REQ'); ?>">
		<a href="#" class="alert_close"></a>
		<h3 class="alert_title">&nbsp;</h3><p class="alert_text"></p>
	</div>
	<div id="phpbb_confirm" class="phpbb_alert">
		<a href="#" class="alert_close"></a>
		<div class="alert_text"></div>
	</div>
</div>

</div>

<div>
	<a id="bottom" class="anchor" accesskey="z"></a>
	<?php if (! $this->vars['S_IS_BOT']) {  ?><?php echo isset($this->vars['RUN_CRON_TASK']) ? $this->vars['RUN_CRON_TASK'] : $this->lang('RUN_CRON_TASK'); ?><?php } ?>
</div>

<script type="text/javascript" src="<?php echo isset($this->vars['T_JQUERY_LINK']) ? $this->vars['T_JQUERY_LINK'] : $this->lang('T_JQUERY_LINK'); ?>"></script>
<?php if ($this->vars['S_ALLOW_CDN']) {  ?><script type="text/javascript">window.jQuery || document.write('\x3Cscript src="<?php echo isset($this->vars['T_ASSETS_PATH']) ? $this->vars['T_ASSETS_PATH'] : $this->lang('T_ASSETS_PATH'); ?>/javascript/jquery.min.js?assets_version=<?php echo isset($this->vars['T_ASSETS_VERSION']) ? $this->vars['T_ASSETS_VERSION'] : $this->lang('T_ASSETS_VERSION'); ?>">\x3C/script>');</script><?php } ?>
<script type="text/javascript" src="<?php echo isset($this->vars['T_ASSETS_PATH']) ? $this->vars['T_ASSETS_PATH'] : $this->lang('T_ASSETS_PATH'); ?>/javascript/core.js?assets_version=<?php echo isset($this->vars['T_ASSETS_VERSION']) ? $this->vars['T_ASSETS_VERSION'] : $this->lang('T_ASSETS_VERSION'); ?>"></script>
<!-- INCLUDEJS forum_fn.js -->
<!-- INCLUDEJS ajax.js -->

<!-- EVENT overall_footer_after -->

<?php if ($this->vars['S_PLUPLOAD']) {  ?><?php  $this->set_filename('xs_include_3ffde4fa77b35430e8e9b38a966f9948', 'plupload.html', true);  $this->pparse('xs_include_3ffde4fa77b35430e8e9b38a966f9948');  ?><?php } ?>
<?php echo isset($this->_tpldata['DEFINE']['.']['SCRIPTS']) ? $this->_tpldata['DEFINE']['.']['SCRIPTS'] : ''; ?>

<!-- EVENT overall_footer_body_after -->

</body>
</html>
