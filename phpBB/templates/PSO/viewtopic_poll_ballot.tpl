			<tr>
				<td class="row2" colspan="2"><br clear="all" /><form method="POST" action="{S_VOTE_ACTION}"><table cellspacing="0" cellpadding="4" border="0" align="center">
					<tr>
						<td align="center"><span class="gen"><b>{POLL_QUESTION}</b></span></td>
					</tr>
					<tr>
						<td align="center"><table cellspacing="0" cellpadding="2" border="0">
							<!-- BEGIN poll_option -->
							<tr>
								<td><input type="radio" name="vote_id" value="{poll_option.POLL_OPTION_ID}">&nbsp;</td>
								<td><span class="gensmall">{poll_option.POLL_OPTION_CAPTION}</span></td>
							</tr>
							<!-- END poll_option -->
						</table></td>
					</tr>
					<tr>
						<td align="center"><input type="submit" name="submit" value="{L_SUBMIT_VOTE}" /></td>
					</tr>
					<tr>
						<td align="center"><span class="gensmall"><a href="{U_VIEW_RESULTS}">{L_VIEW_RESULTS}</a></span></td>
					</tr>
				</table>{S_HIDDEN_FIELDS}</form></td>
			</tr>