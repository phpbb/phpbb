<?php

// eXtreme Styles mod cache. Generated on Wed, 10 Oct 2018 01:15:36 +0000 (time=1539134136)

?><script language="javascript" type="text/javascript">
// <![CDATA[

message = new Array();
<?php

$mail_sessions_count = ( isset($this->_tpldata['mail_sessions.']) ) ?  sizeof($this->_tpldata['mail_sessions.']) : 0;
for ($mail_sessions_i = 0; $mail_sessions_i < $mail_sessions_count; $mail_sessions_i++)
{
 $mail_sessions_item = &$this->_tpldata['mail_sessions.'][$mail_sessions_i];
 $mail_sessions_item['S_ROW_COUNT'] = $mail_sessions_i;
 $mail_sessions_item['S_NUM_ROWS'] = $mail_sessions_count;

?>
message[<?php echo isset($mail_sessions_item['ID']) ? $mail_sessions_item['ID'] : ''; ?>] = "<?php echo isset($mail_sessions_item['MESSAGE_BODY']) ? $mail_sessions_item['MESSAGE_BODY'] : ''; ?>";
<?php

} // END mail_sessions

if(isset($mail_sessions_item)) { unset($mail_sessions_item); } 

?>

function disableForm(theform)
{
	if (document.all || document.getElementById)
	{
		for (i = 0; i < theform.length; i++)
		{
			var tempobj = theform.elements[i];
			if (tempobj.type.toLowerCase() == "submit" || tempobj.type.toLowerCase() == "reset")
			{
				tempobj.disabled = true;
			}
		}
		return true;
	}
	else
	{
		alert("The form has been submitted. Please do NOT resubmit.");
		return false;
	}
}

function compileForm(m_id)
{
	str_find = new Array("&q_mg;", "&lt_mg;", "&gt_mg;");
	str_replace = new Array("\\\"", "<", ">");
	for(var i = 0; i < message[m_id].length; i++)
	{
		for (var j = 0; j < str_find.length; j++)
		{
			if (message[m_id].search(str_find[j]) != -1)
			{
				message[m_id] = message[m_id].replace(str_find[j],str_replace[j]);
			}
		}
	}
	document.post.message.value = message[m_id];
	document.post.message.focus();
	return;
}

// ]]>
</script>

<h1><?php echo isset($this->vars['L_MAIL_SESSION_HEADER']) ? $this->vars['L_MAIL_SESSION_HEADER'] : $this->lang('L_MAIL_SESSION_HEADER'); ?></h1>
<form method="post" name="post" action="<?php echo isset($this->vars['S_USER_ACTION']) ? $this->vars['S_USER_ACTION'] : $this->lang('S_USER_ACTION'); ?>" onsubmit="return disableForm(this);">
<table class="forumline">
<tr>
	<th><?php echo isset($this->vars['L_ID']) ? $this->vars['L_ID'] : $this->lang('L_ID'); ?></th>
	<th><?php echo isset($this->vars['L_GROUP']) ? $this->vars['L_GROUP'] : $this->lang('L_GROUP'); ?></th>
	<th><?php echo isset($this->vars['L_EMAIL_SUBJECT']) ? $this->vars['L_EMAIL_SUBJECT'] : $this->lang('L_EMAIL_SUBJECT'); ?></th>
	<th><?php echo isset($this->vars['L_MASS_PM']) ? $this->vars['L_MASS_PM'] : $this->lang('L_MASS_PM'); ?></th>
	<th><?php echo isset($this->vars['L_TEXT_FORMAT']) ? $this->vars['L_TEXT_FORMAT'] : $this->lang('L_TEXT_FORMAT'); ?></th>
	<th><?php echo isset($this->vars['L_BATCH_START']) ? $this->vars['L_BATCH_START'] : $this->lang('L_BATCH_START'); ?></th>
	<th><?php echo isset($this->vars['L_BATCH_SIZE']) ? $this->vars['L_BATCH_SIZE'] : $this->lang('L_BATCH_SIZE'); ?></th>
	<th><?php echo isset($this->vars['L_BATCH_WAIT']) ? $this->vars['L_BATCH_WAIT'] : $this->lang('L_BATCH_WAIT'); ?></th>
	<th><?php echo isset($this->vars['L_SENDER']) ? $this->vars['L_SENDER'] : $this->lang('L_SENDER'); ?></th>
	<th><?php echo isset($this->vars['L_STATUS']) ? $this->vars['L_STATUS'] : $this->lang('L_STATUS'); ?></th>
	<th><?php echo isset($this->vars['L_ACTIONS']) ? $this->vars['L_ACTIONS'] : $this->lang('L_ACTIONS'); ?></th>
</tr>
<?php

$mail_sessions_count = ( isset($this->_tpldata['mail_sessions.']) ) ?  sizeof($this->_tpldata['mail_sessions.']) : 0;
for ($mail_sessions_i = 0; $mail_sessions_i < $mail_sessions_count; $mail_sessions_i++)
{
 $mail_sessions_item = &$this->_tpldata['mail_sessions.'][$mail_sessions_i];
 $mail_sessions_item['S_ROW_COUNT'] = $mail_sessions_i;
 $mail_sessions_item['S_NUM_ROWS'] = $mail_sessions_count;

?>
<tr>
	<td class="<?php echo isset($mail_sessions_item['ROW']) ? $mail_sessions_item['ROW'] : ''; ?> row-center"><?php echo isset($mail_sessions_item['ID']) ? $mail_sessions_item['ID'] : ''; ?></td>
	<td class="<?php echo isset($mail_sessions_item['ROW']) ? $mail_sessions_item['ROW'] : ''; ?> row-center"><?php echo isset($mail_sessions_item['GROUP']) ? $mail_sessions_item['GROUP'] : ''; ?></td>
	<td class="<?php echo isset($mail_sessions_item['ROW']) ? $mail_sessions_item['ROW'] : ''; ?>"><a href="javascript:compileForm(<?php echo isset($mail_sessions_item['ID']) ? $mail_sessions_item['ID'] : ''; ?>);"><?php echo isset($mail_sessions_item['SUBJECT']) ? $mail_sessions_item['SUBJECT'] : ''; ?></a></td>
	<td class="<?php echo isset($mail_sessions_item['ROW']) ? $mail_sessions_item['ROW'] : ''; ?> row-center"><?php echo isset($mail_sessions_item['MASS_PM']) ? $mail_sessions_item['MASS_PM'] : ''; ?></td>
	<td class="<?php echo isset($mail_sessions_item['ROW']) ? $mail_sessions_item['ROW'] : ''; ?> row-center"><?php echo isset($mail_sessions_item['EMAIL_FORMAT']) ? $mail_sessions_item['EMAIL_FORMAT'] : ''; ?></td>
	<td class="<?php echo isset($mail_sessions_item['ROW']) ? $mail_sessions_item['ROW'] : ''; ?> row-center"><?php echo isset($mail_sessions_item['BATCHSTART']) ? $mail_sessions_item['BATCHSTART'] : ''; ?></td>
	<td class="<?php echo isset($mail_sessions_item['ROW']) ? $mail_sessions_item['ROW'] : ''; ?> row-center"><?php echo isset($mail_sessions_item['BATCHSIZE']) ? $mail_sessions_item['BATCHSIZE'] : ''; ?></td>
	<td class="<?php echo isset($mail_sessions_item['ROW']) ? $mail_sessions_item['ROW'] : ''; ?> row-center"><?php echo isset($mail_sessions_item['BATCHWAIT']) ? $mail_sessions_item['BATCHWAIT'] : ''; ?></td>
	<td class="<?php echo isset($mail_sessions_item['ROW']) ? $mail_sessions_item['ROW'] : ''; ?> row-center"><?php echo isset($mail_sessions_item['SENDER']) ? $mail_sessions_item['SENDER'] : ''; ?></td>
	<td class="<?php echo isset($mail_sessions_item['ROW']) ? $mail_sessions_item['ROW'] : ''; ?> row-center"><?php echo isset($mail_sessions_item['STATUS']) ? $mail_sessions_item['STATUS'] : ''; ?></td>
	<td class="<?php echo isset($mail_sessions_item['ROW']) ? $mail_sessions_item['ROW'] : ''; ?> row-center"><a href="<?php echo isset($mail_sessions_item['U_DELETE']) ? $mail_sessions_item['U_DELETE'] : ''; ?>"><img src="<?php echo isset($this->vars['IMG_CMS_ICON_DELETE']) ? $this->vars['IMG_CMS_ICON_DELETE'] : $this->lang('IMG_CMS_ICON_DELETE'); ?>" alt="<?php echo isset($this->vars['L_DELETE']) ? $this->vars['L_DELETE'] : $this->lang('L_DELETE'); ?>" title="<?php echo isset($this->vars['L_DELETE']) ? $this->vars['L_DELETE'] : $this->lang('L_DELETE'); ?>" /></a></td>
</tr>
<?php

} // END mail_sessions

if(isset($mail_sessions_item)) { unset($mail_sessions_item); } 

?>
<?php

$switch_no_sessions_count = ( isset($this->_tpldata['switch_no_sessions.']) ) ?  sizeof($this->_tpldata['switch_no_sessions.']) : 0;
for ($switch_no_sessions_i = 0; $switch_no_sessions_i < $switch_no_sessions_count; $switch_no_sessions_i++)
{
 $switch_no_sessions_item = &$this->_tpldata['switch_no_sessions.'][$switch_no_sessions_i];
 $switch_no_sessions_item['S_ROW_COUNT'] = $switch_no_sessions_i;
 $switch_no_sessions_item['S_NUM_ROWS'] = $switch_no_sessions_count;

?>
<tr><td class="row2 row-center" colspan="11"><?php echo isset($switch_no_sessions_item['EMPTY']) ? $switch_no_sessions_item['EMPTY'] : ''; ?></td></tr>
<?php

} // END switch_no_sessions

if(isset($switch_no_sessions_item)) { unset($switch_no_sessions_item); } 

?>
</table>

<h1><?php echo isset($this->vars['L_EMAIL_TITLE']) ? $this->vars['L_EMAIL_TITLE'] : $this->lang('L_EMAIL_TITLE'); ?></h1>
<p><?php echo isset($this->vars['L_EMAIL_EXPLAIN']) ? $this->vars['L_EMAIL_EXPLAIN'] : $this->lang('L_EMAIL_EXPLAIN'); ?></p>
<?php echo isset($this->vars['ERROR_BOX']) ? $this->vars['ERROR_BOX'] : $this->lang('ERROR_BOX'); ?>
<table class="forumline">
<tr><th colspan="2"><?php echo isset($this->vars['L_COMPOSE']) ? $this->vars['L_COMPOSE'] : $this->lang('L_COMPOSE'); ?></th></tr>
<tr>
	<td class="row1 tdalignr"><b><?php echo isset($this->vars['L_RECIPIENTS']) ? $this->vars['L_RECIPIENTS'] : $this->lang('L_RECIPIENTS'); ?></b></td>
	<td class="row2"><?php echo isset($this->vars['S_GROUP_SELECT']) ? $this->vars['S_GROUP_SELECT'] : $this->lang('S_GROUP_SELECT'); ?></td>
</tr>
<tr>
	<td class="row1 tdalignr"><b><?php echo isset($this->vars['L_BATCH_SIZE']) ? $this->vars['L_BATCH_SIZE'] : $this->lang('L_BATCH_SIZE'); ?></b></td>
	<td class="row2"><span class="gen"><input type="text" name="batchsize" size="4" maxlength="4" tabindex="2" class="post" value="<?php echo isset($this->vars['DEFAULT_SIZE']) ? $this->vars['DEFAULT_SIZE'] : $this->lang('DEFAULT_SIZE'); ?>" /></span></td>
</tr>
<tr>
	<td class="row1 tdalignr"><b><?php echo isset($this->vars['L_BATCH_WAIT']) ? $this->vars['L_BATCH_WAIT'] : $this->lang('L_BATCH_WAIT'); ?></b></td>
	<td class="row2"><span class="gen"><input type="text" name="batchwait" size="4" maxlength="4" tabindex="3" class="post" value="<?php echo isset($this->vars['DEFAULT_WAIT']) ? $this->vars['DEFAULT_WAIT'] : $this->lang('DEFAULT_WAIT'); ?>" /> s.</span></td>
</tr>
<tr>
	<td class="row1 tdalignr"><b><?php echo isset($this->vars['L_MASS_PM']) ? $this->vars['L_MASS_PM'] : $this->lang('L_MASS_PM'); ?></b></td>
	<td class="row2"><span class="gen"><input type="radio" name="mass_pm" class="post" value="0" checked="checked" />&nbsp;<?php echo isset($this->vars['L_NO']) ? $this->vars['L_NO'] : $this->lang('L_NO'); ?>&nbsp;&nbsp;<input type="radio" name="mass_pm" class="post" value="1" />&nbsp;<?php echo isset($this->vars['L_YES']) ? $this->vars['L_YES'] : $this->lang('L_YES'); ?></span></td>
</tr>
<tr>
	<td class="row1 tdalignr"><b><?php echo isset($this->vars['L_TEXT_FORMAT']) ? $this->vars['L_TEXT_FORMAT'] : $this->lang('L_TEXT_FORMAT'); ?></b></td>
	<td class="row2"><span class="gen"><input type="radio" name="email_format" class="post" value="1" />&nbsp;<?php echo isset($this->vars['L_BBCODE']) ? $this->vars['L_BBCODE'] : $this->lang('L_BBCODE'); ?>&nbsp;&nbsp;<input type="radio" name="email_format" class="post" value="0" checked="checked" />&nbsp;<?php echo isset($this->vars['L_HTML']) ? $this->vars['L_HTML'] : $this->lang('L_HTML'); ?>&nbsp;&nbsp;<input type="radio" name="email_format" class="post" value="2" />&nbsp;<?php echo isset($this->vars['L_FULL_HTML']) ? $this->vars['L_FULL_HTML'] : $this->lang('L_FULL_HTML'); ?></span></td>
</tr>
<tr>
	<td class="row1 tdalignr"><b><?php echo isset($this->vars['L_EMAIL_SUBJECT']) ? $this->vars['L_EMAIL_SUBJECT'] : $this->lang('L_EMAIL_SUBJECT'); ?></b></td>
	<td class="row2"><span class="gen"><input type="text" name="subject" size="45" maxlength="160" tabindex="4" class="post" value="<?php echo isset($this->vars['SUBJECT']) ? $this->vars['SUBJECT'] : $this->lang('SUBJECT'); ?>" /></span></td>
</tr>
<tr>
	<td class="row1" align="right" valign="top"><span class="gen"><b><?php echo isset($this->vars['L_EMAIL_MSG']) ? $this->vars['L_EMAIL_MSG'] : $this->lang('L_EMAIL_MSG'); ?></b></span></td>
	<td class="row2"><span class="gen"><textarea id="message" name="message" rows="15" cols="35" style="width:450px" tabindex="5" class="post"><?php echo isset($this->vars['MESSAGE']) ? $this->vars['MESSAGE'] : $this->lang('MESSAGE'); ?></textarea></span></td>
</tr>
<tr><td class="cat" colspan="2"><input type="submit" value="<?php echo isset($this->vars['L_SEND']) ? $this->vars['L_SEND'] : $this->lang('L_SEND'); ?>" name="submit" class="mainoption" /></td></tr>
</table>

</form>
