			<tr>
				<td class="row2" colspan="2"><br clear="all" /><table cellspacing="0" cellpadding="4" border="0" align="center">
					<tr>
						<td colspan="4" align="center"><span class="gen"><b>{POLL_QUESTION}</b></span></td>
					</tr>
					<tr>
						<td align="center"><table cellspacing="0" cellpadding="2" border="0">
							<!-- BEGIN poll_option -->
							<tr>
								<td><span class="gensmall">{poll_option.POLL_OPTION_CAPTION}</span></td>
								<td><table width="{poll_option.POLL_OPTION_IMG_WIDTH}" cellspacing="0" cellpadding="0" border="0">
									<tr>
										<td width="100%" bgcolor="{poll_option.POLL_OPTION_IMG_COLOR}"><img src="{poll_option.POLL_OPTION_IMG}" width="{poll_option.POLL_OPTION_IMG_WIDTH}" height="8" alt="{poll_option.POLL_OPTION_PERCENT}" /></td>
									</tr>
								</table></td>
								<td align="center">&nbsp;<span class="gensmall"><b>{poll_option.POLL_OPTION_PERCENT}</b></span>&nbsp;</td>
								<td align="center">&nbsp;<span class="gensmall">[ {poll_option.POLL_OPTION_RESULT} ]</span>&nbsp;</td>
							</tr>
							<!-- END poll_option -->
						</table></td>
					</tr>
					<tr>
						<td colspan="4" align="center"><span class="gensmall"><b>Total Votes : {TOTAL_VOTES}</b></span></td>
					</tr>
				</table><br clear="all" /></td>
			</tr>