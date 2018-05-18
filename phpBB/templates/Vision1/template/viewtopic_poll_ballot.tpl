<form method="POST" action="{S_POLL_ACTION}">                                    
<table cellpadding="0" cellspacing="0" border="0" align="center">
                                        <tr>
                                            <td width="5" height="5" background="templates/Vision1/images/1.gif"></td>
                                            <td width="390" background="templates/Vision1/images/0.gif"></td>
                                            <td width="5" background="templates/Vision1/images/2.gif"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" background="templates/Vision1/images/0.gif" align="center">


                                      <table cellspacing="0" cellpadding="0" border="0" align="center" width="100%">
					<tr>
						<td align="center" colspan="2"><span class="gen"><b>{POLL_QUESTION}</b></span></td>
					</tr>
					<tr>
						<td align="center" colspan="2"><table cellspacing="0" cellpadding="2" border="0">
							<!-- BEGIN poll_option -->
							<tr>
								<td><input type="radio" name="vote_id" value="{poll_option.POLL_OPTION_ID}" />&nbsp;</td>
								<td><span class="gen">{poll_option.POLL_OPTION_CAPTION}</span></td>
							</tr>
							<!-- END poll_option -->
						</table>
                                            </td>
					</tr>
					<tr>
						<td align="center" colspan="2">
			<input type="submit" name="submit" value="{L_SUBMIT_VOTE}" class="liteoption" />
		  </td>
					</tr>
					<tr>
						
		  <td align="center" colspan="2"><span class="gensmall"><b><a href="{U_VIEW_RESULTS}" class="gensmall">{L_VIEW_RESULTS}</a></b></span></td>
					</tr>
				</table>{S_HIDDEN_FIELDS}

                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="5" background="templates/Vision1/images/3.gif"></td>
                                            <td background="templates/Vision1/images/0.gif"></td>
                                            <td background="templates/Vision1/images/4.gif"></td>
                                        </tr>
                                    </table>
</form>