{ERROR_BOX}
<form method="post" action="{S_AGREE_ACTION}">
<div class="block">
	<h2>{REGISTRATION}</h2>
	{AGREEMENT}<br clear="all" /><br /><br />
	{L_PRIVACY_DISCLAIMER}<br clear="all" /><br /><br /><br />
	<label><input type="checkbox" name="privacy" />&nbsp;{AGREE_CHECKBOX}</label><br clear="all" /><br />
</div>
<div class="block-empty center">
	{S_HIDDEN_FIELDS}
	<input type="submit" name="not_agreed" value="{DO_NOT_AGREE}" class="liteoption" />
	<input type="submit" name="agreed" value="{AGREE_OVER_13}" class="mainoption" />
</div>
</form>
