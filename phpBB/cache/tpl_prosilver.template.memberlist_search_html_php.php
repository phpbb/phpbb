<?php

// eXtreme Styles mod cache. Generated on Sat, 26 May 2018 21:49:23 +0000 (time=1527371363)

?><h2 class="solo"><?php echo isset($this->vars['L_FIND_USERNAME']) ? $this->vars['L_FIND_USERNAME'] : $this->lang('L_FIND_USERNAME'); ?></h2>

<form method="post" action="<?php echo isset($this->vars['S_MODE_ACTION']) ? $this->vars['S_MODE_ACTION'] : $this->lang('S_MODE_ACTION'); ?>" id="search_memberlist">
<div class="panel">
	<div class="inner">

	<p><?php echo isset($this->vars['L_FIND_USERNAME_EXPLAIN']) ? $this->vars['L_FIND_USERNAME_EXPLAIN'] : $this->lang('L_FIND_USERNAME_EXPLAIN'); ?></p>

	<!-- EVENT memberlist_search_fields_before -->
	<fieldset class="fields1 column1">
	<dl style="overflow: visible;">
		<dt><label for="username"><?php echo isset($this->vars['L_USERNAME']) ? $this->vars['L_USERNAME'] : $this->lang('L_USERNAME'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label></dt>
		<dd>
			<?php if ($this->vars['U_LIVE_SEARCH']) {  ?><div class="dropdown-container dropdown-<?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?>"><?php } ?>
			<input type="text" name="username" id="username" value="<?php echo isset($this->vars['USERNAME']) ? $this->vars['USERNAME'] : $this->lang('USERNAME'); ?>" class="inputbox"<?php if ($this->vars['U_LIVE_SEARCH']) {  ?> autocomplete="off" data-filter="phpbb.search.filter" data-ajax="member_search" data-min-length="3" data-url="<?php echo isset($this->vars['U_LIVE_SEARCH']) ? $this->vars['U_LIVE_SEARCH'] : $this->lang('U_LIVE_SEARCH'); ?>" data-results="#user-search" data-overlay="false"<?php } ?> />
			<?php if ($this->vars['U_LIVE_SEARCH']) {  ?>
				<div class="dropdown live-search hidden" id="user-search">
					<div class="pointer"><div class="pointer-inner"></div></div>
					<ul class="dropdown-contents search-results">
						<li class="search-result-tpl"><span class="search-result"></span></li>
					</ul>
				</div>
			</div>
			<?php } ?>
		</dd>
	</dl>
<?php if ($this->vars['S_EMAIL_SEARCH_ALLOWED']) {  ?>
	<dl>
		<dt><label for="email"><?php echo isset($this->vars['L_EMAIL']) ? $this->vars['L_EMAIL'] : $this->lang('L_EMAIL'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label></dt>
		<dd><input type="text" name="email" id="email" value="<?php echo isset($this->vars['EMAIL']) ? $this->vars['EMAIL'] : $this->lang('EMAIL'); ?>" class="inputbox" /></dd>
	</dl>
<?php } ?>
<?php if ($this->vars['S_JABBER_ENABLED']) {  ?>
	<dl>
		<dt><label for="jabber"><?php echo isset($this->vars['L_JABBER']) ? $this->vars['L_JABBER'] : $this->lang('L_JABBER'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label></dt>
		<dd><input type="text" name="jabber" id="jabber" value="<?php echo isset($this->vars['JABBER']) ? $this->vars['JABBER'] : $this->lang('JABBER'); ?>" class="inputbox" /></dd>
	</dl>
<?php } ?>
	<dl>
		<dt><label for="search_group_id"><?php echo isset($this->vars['L_GROUP']) ? $this->vars['L_GROUP'] : $this->lang('L_GROUP'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label></dt>
		<dd><select name="search_group_id" id="search_group_id"><?php echo isset($this->vars['S_GROUP_SELECT']) ? $this->vars['S_GROUP_SELECT'] : $this->lang('S_GROUP_SELECT'); ?></select></dd>
	</dl>
	<!-- EVENT memberlist_search_sorting_options_before -->
	<dl>
		<dt><label for="sk" class="label3"><?php echo isset($this->vars['L_SORT_BY']) ? $this->vars['L_SORT_BY'] : $this->lang('L_SORT_BY'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label></dt>
		<dd><select name="sk" id="sk"><?php echo isset($this->vars['S_SORT_OPTIONS']) ? $this->vars['S_SORT_OPTIONS'] : $this->lang('S_SORT_OPTIONS'); ?></select> <select name="sd"><?php echo isset($this->vars['S_ORDER_SELECT']) ? $this->vars['S_ORDER_SELECT'] : $this->lang('S_ORDER_SELECT'); ?></select></dd>
	</dl>
	</fieldset>

	<fieldset class="fields1 column2">
	<dl>
		<dt><label for="joined"><?php echo isset($this->vars['L_JOINED']) ? $this->vars['L_JOINED'] : $this->lang('L_JOINED'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label></dt>
		<dd><select name="joined_select"><?php echo isset($this->vars['S_JOINED_TIME_OPTIONS']) ? $this->vars['S_JOINED_TIME_OPTIONS'] : $this->lang('S_JOINED_TIME_OPTIONS'); ?></select> <input class="inputbox medium" type="text" name="joined" id="joined" value="<?php echo isset($this->vars['JOINED']) ? $this->vars['JOINED'] : $this->lang('JOINED'); ?>" /></dd>
	</dl>
<?php if ($this->vars['S_VIEWONLINE']) {  ?>
	<dl>
		<dt><label for="active"><?php echo isset($this->vars['L_LAST_ACTIVE']) ? $this->vars['L_LAST_ACTIVE'] : $this->lang('L_LAST_ACTIVE'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label></dt>
		<dd><select name="active_select"><?php echo isset($this->vars['S_ACTIVE_TIME_OPTIONS']) ? $this->vars['S_ACTIVE_TIME_OPTIONS'] : $this->lang('S_ACTIVE_TIME_OPTIONS'); ?></select> <input class="inputbox medium" type="text" name="active" id="active" value="<?php echo isset($this->vars['ACTIVE']) ? $this->vars['ACTIVE'] : $this->lang('ACTIVE'); ?>" /></dd>
	</dl>
<?php } ?>
	<dl>
		<dt><label for="count"><?php echo isset($this->vars['L_POSTS']) ? $this->vars['L_POSTS'] : $this->lang('L_POSTS'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label></dt>
		<dd><select name="count_select"><?php echo isset($this->vars['S_COUNT_OPTIONS']) ? $this->vars['S_COUNT_OPTIONS'] : $this->lang('S_COUNT_OPTIONS'); ?></select> <input class="inputbox medium" type="number" min="0" name="count" id="count" value="<?php echo isset($this->vars['COUNT']) ? $this->vars['COUNT'] : $this->lang('COUNT'); ?>" /></dd>
	</dl>
<?php if ($this->vars['S_IP_SEARCH_ALLOWED']) {  ?>
	<dl>
		<dt><label for="ip"><?php echo isset($this->vars['L_POST_IP']) ? $this->vars['L_POST_IP'] : $this->lang('L_POST_IP'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label></dt>
		<dd><input class="inputbox medium" type="text" name="ip" id="ip" value="<?php echo isset($this->vars['IP']) ? $this->vars['IP'] : $this->lang('IP'); ?>" /></dd>
	</dl>
<?php } ?>
	<!-- EVENT memberlist_search_fields_after -->
	</fieldset>

	<div class="clear"></div>

	<hr />

	<fieldset class="submit-buttons">
		<input type="reset" value="<?php echo isset($this->vars['L_RESET']) ? $this->vars['L_RESET'] : $this->lang('L_RESET'); ?>" name="reset" class="button2" />&nbsp;
		<input type="submit" name="submit" value="<?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?>" class="button1" />
		<?php echo isset($this->vars['S_FORM_TOKEN']) ? $this->vars['S_FORM_TOKEN'] : $this->lang('S_FORM_TOKEN'); ?>
	</fieldset>

	</div>
</div>

</form>
