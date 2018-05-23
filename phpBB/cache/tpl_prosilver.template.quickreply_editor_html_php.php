<?php

// eXtreme Styles mod cache. Generated on Tue, 22 May 2018 19:50:05 +0000 (time=1527018605)

?><form method="post" action="<?php echo isset($this->vars['U_QR_ACTION']) ? $this->vars['U_QR_ACTION'] : $this->lang('U_QR_ACTION'); ?>" id="qr_postform">
<!-- EVENT quickreply_editor_panel_before -->
	<div class="panel">
		<div class="inner">
				<h2 class="quickreply-title"><?php echo isset($this->vars['L_QUICKREPLY']) ? $this->vars['L_QUICKREPLY'] : $this->lang('L_QUICKREPLY'); ?></h2>
				<fieldset class="fields1">
				<!-- EVENT quickreply_editor_subject_before -->
					<dl style="clear: left;">
						<dt><label for="subject"><?php echo isset($this->vars['L_SUBJECT']) ? $this->vars['L_SUBJECT'] : $this->lang('L_SUBJECT'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label></dt>
						<dd><input type="text" name="subject" id="subject" size="45" maxlength="124" tabindex="2" value="<?php echo isset($this->vars['SUBJECT']) ? $this->vars['SUBJECT'] : $this->lang('SUBJECT'); ?>" class="inputbox autowidth" /></dd>
					</dl>
				<!-- EVENT quickreply_editor_message_before -->
				<div id="message-box">
					<textarea style="height: 9em;" name="message" rows="7" cols="76" tabindex="3" class="inputbox"></textarea>
				</div>
				<!-- EVENT quickreply_editor_message_after -->
				</fieldset>
				<fieldset class="submit-buttons">
					<?php echo isset($this->vars['S_FORM_TOKEN']) ? $this->vars['S_FORM_TOKEN'] : $this->lang('S_FORM_TOKEN'); ?>
					<?php echo isset($this->vars['QR_HIDDEN_FIELDS']) ? $this->vars['QR_HIDDEN_FIELDS'] : $this->lang('QR_HIDDEN_FIELDS'); ?>
					<input type="submit" accesskey="f" tabindex="6" name="preview" value="<?php echo isset($this->vars['L_FULL_EDITOR']) ? $this->vars['L_FULL_EDITOR'] : $this->lang('L_FULL_EDITOR'); ?>" class="button2" id="qr_full_editor" />&nbsp;
					<input type="submit" accesskey="s" tabindex="7" name="post" value="<?php echo isset($this->vars['L_SUBMIT']) ? $this->vars['L_SUBMIT'] : $this->lang('L_SUBMIT'); ?>" class="button1" />&nbsp;
				</fieldset>
		</div>
	</div>
<!-- EVENT quickreply_editor_panel_after -->
</form>
