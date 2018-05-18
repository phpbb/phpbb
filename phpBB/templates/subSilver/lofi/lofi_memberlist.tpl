<!-- INCLUDE ../common/lofi/lofi_header.tpl -->

<form method="post" action="{S_MODE_ACTION}">
	<table align="center" width="100%" cellspacing="2" cellpadding="2" border="0">
	<tr>
		<td><span class="nav"><a href="{U_INDEX}" class="nav">{L_INDEX}</a></span></td>
		<td class="tdalignr tdnw">
			<span class="genmed">
				{L_SELECT_SORT_METHOD}:&nbsp;{S_MODE_SELECT}&nbsp;&nbsp;{L_ORDER}&nbsp;{S_ORDER_SELECT}&nbsp;&nbsp;
				<input type="submit" name="submit" value="{L_SUBMIT}" class="liteoption" />
			</span>
		</td>
	</tr>
	</table>
	<div class="index">
		<table class="forumline">
		<tr>
			<th class="tdnw">#</th>
			<th>{L_PM}</th>
			<th>{L_USERNAME}</th>
			<th>{L_EMAIL}</th>
			<th>{L_FROM}</th>
			<th>{L_JOINED}</th>
			<th>{L_POSTS}</th>
			<th class="thCornerR" nowrap="nowrap">{L_WEBSITE}</th>
		</tr>
		<!-- BEGIN memberrow -->
		<tr>
			<td class="tdalignc">&nbsp;{memberrow.ROW_NUMBER}&nbsp;</span></td>
			<td class="tdalignc">&nbsp;{memberrow.PM}&nbsp;</td>
			<td class="tdalignc"><a href="{memberrow.U_VIEWPROFILE}" class="gen">{memberrow.USERNAME}</a></td>
			<td class="tdalignc tvalignm">&nbsp;{memberrow.EMAIL}&nbsp;</td>
			<td class="tdalignc tvalignm">{memberrow.FROM}</td>
			<td class="tdalignc tvalignm">{memberrow.JOINED}</td>
			<td class="tdalignc tvalignm">{memberrow.POSTS}</td>
			<td class="tdalignc">&nbsp;{memberrow.WWW}&nbsp;</td>
		</tr>
		<!-- END memberrow -->
		</table>
		<table class="s2px p2px"><tr><td class="tdalignr">&nbsp;</td></tr></table>

		<table>
		<tr>
			<td><span class="gensmall">{PAGE_NUMBER}</span></td>
			<td class="tdalignr"><span class="desc">{S_TIMEZONE}</span><br /><span class="pagination">{PAGINATION}</span></td>
		</tr>
		</table>
	</div>
</form>
<table class="s2px p2px"><tr><td class="tdalignr">{JUMPBOX}</td></tr></table>
<br />

<!-- INCLUDE ../common/lofi/lofi_footer.tpl -->