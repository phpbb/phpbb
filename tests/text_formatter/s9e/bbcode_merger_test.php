<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

class phpbb_textformatter_s9e_bbcode_merger_test extends phpbb_test_case
{
	/**
	* @dataProvider get_merge_bbcodes_tests
	*/
	public function test_merge_bbcodes($usage_without, $template_without, $usage_with, $template_with, $expected_usage, $expected_template)
	{
		$container     = $this->get_test_case_helpers()->set_s9e_services();
		$factory       = $container->get('text_formatter.s9e.factory');
		$bbcode_merger = new \phpbb\textformatter\s9e\bbcode_merger($factory);

		$without = ['usage' => $usage_without, 'template' => $template_without];
		$with    = ['usage' => $usage_with,    'template' => $template_with];
		$merged  = $bbcode_merger->merge_bbcodes($without, $with);

		// Normalize the expected template's whitespace to match the default indentation
		$expected_template = str_replace("\n\t\t\t\t", "\n", $expected_template);
		$expected_template = str_replace("\t",         '  ', $expected_template);

		$this->assertSame($expected_usage,    $merged['usage']);
		$this->assertSame($expected_template, $merged['template']);
	}

	public function get_merge_bbcodes_tests()
	{
		return [
			[
				'[x]{TEXT}[/x]',
				'<b>{TEXT}</b>',

				'[x={TEXT1}]{TEXT}[/x]',
				'<b title="{TEXT1}">{TEXT}</b>',

				'[x={TEXT1?}]{TEXT}[/x]',
				'<b>
					<xsl:if test="@x">
						<xsl:attribute name="title">
							<xsl:value-of select="@x"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:apply-templates/>
				</b>'
			],
			[
				// The tokens' numbering differs between versions
				'[x]{TEXT}[/x]',
				'<b>{TEXT}</b>',

				'[x={TEXT1}]{TEXT2}[/x]',
				'<b title="{TEXT1}">{TEXT2}</b>',

				'[x={TEXT1?}]{TEXT2}[/x]',
				'<b>
					<xsl:if test="@x">
						<xsl:attribute name="title">
							<xsl:value-of select="@x"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:apply-templates/>
				</b>'
			],
			[
				'[x]{URL}[/x]',
				'<a href="{URL}">{URL}</a>',

				'[x={URL}]{TEXT}[/x]',
				'<a href="{URL}">{TEXT}</a>',

				'[x={URL;useContent}]{TEXT}[/x]',
				'<a href="{@x}">
					<xsl:apply-templates/>
				</a>'
			],
			[
				'[x]{URL}[/x]',
				'<a href="{URL}">{L_GO_TO}: {URL}</a>',

				'[x={URL}]{TEXT}[/x]',
				'<a href="{URL}">{L_GO_TO}: {TEXT}</a>',

				'[x={URL;useContent}]{TEXT}[/x]',
				'<a href="{@x}">{L_GO_TO}: <xsl:apply-templates/></a>'
			],
			[
				// Test that unsafe BBCodes can still be merged
				'[script]{TEXT}[/script]',
				'<script>{TEXT}</script>',

				'[script={TEXT1}]{TEXT2}[/script]',
				'<script type="{TEXT1}">{TEXT2}</script>',

				'[script={TEXT1?}]{TEXT2}[/script]',
				'<script>
					<xsl:if test="@script">
						<xsl:attribute name="type">
							<xsl:value-of select="@script"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:apply-templates/>
				</script>'
			],
			[
				// https://www.phpbb.com/community/viewtopic.php?p=14848281#p14848281
				'[note]{TEXT}[/note]',
				'<span class="prime_bbcode_note_spur" onmouseover="show_note(this);" onmouseout="hide_note(this);" onclick="lock_note(this);"></span><span class="prime_bbcode_note">{TEXT}</span>',

				'[note={TEXT1}]{TEXT2}[/note]',
				'<span class="prime_bbcode_note_text" onmouseover="show_note(this);" onmouseout="hide_note(this);" onclick="lock_note(this);">{TEXT1}</span><span class="prime_bbcode_note_spur" onmouseover="show_note(this);" onmouseout="hide_note(this);" onclick="lock_note(this);"></span><span class="prime_bbcode_note">{TEXT2}</span>',

				'[note={TEXT1?}]{TEXT2}[/note]',
				'<xsl:if test="@note">
					<span class="prime_bbcode_note_text" onmouseover="show_note(this);" onmouseout="hide_note(this);" onclick="lock_note(this);">
						<xsl:value-of select="@note"/>
					</span>
				</xsl:if>
				<span class="prime_bbcode_note_spur" onmouseover="show_note(this);" onmouseout="hide_note(this);" onclick="lock_note(this);"/>
				<span class="prime_bbcode_note">
					<xsl:apply-templates/>
				</span>'
			],
			[
				// https://www.phpbb.com/community/viewtopic.php?p=14768441#p14768441
				'[MI]{TEXT}[/MI]',
				'<span style="color:red">MI:</span> <span style="color:#f6efe2">{TEXT}</span>',

				'[MI={TEXT2}]{TEXT1}[/MI]',
				'<span style="color:red">MI for: "{TEXT2}":</span> <span style="color:#f6efe2">{TEXT1}</span>',

				'[MI={TEXT2?}]{TEXT1}[/MI]',
				'<span style="color:red">MI<xsl:if test="@mi"> for: "<xsl:value-of select="@mi"/>"</xsl:if>:</span>
				<xsl:text> </xsl:text>
				<span style="color:#f6efe2">
					<xsl:apply-templates/>
				</span>'
			],
			[
				// https://www.phpbb.com/community/viewtopic.php?p=14700506#p14700506
				'[spoiler]{TEXT}[/spoiler]',
				'<span class="spoiler"> {TEXT}</span>',

				'[spoiler={TEXT1}]{TEXT2}[/spoiler]',
				'<div class="spoiler"><small> {TEXT1}</small>{TEXT2}</div>',

				'[spoiler={TEXT1?}]{TEXT2}[/spoiler]',
				'<xsl:choose>
					<xsl:when test="@spoiler">
						<div class="spoiler">
							<small>
								<xsl:text> </xsl:text>
								<xsl:value-of select="@spoiler"/>
							</small>
							<xsl:apply-templates/>
						</div>
					</xsl:when>
					<xsl:otherwise>
						<span class="spoiler">
							<xsl:text> </xsl:text>
							<xsl:apply-templates/>
						</span>
					</xsl:otherwise>
				</xsl:choose>'
			],
			[
				// https://www.phpbb.com/community/viewtopic.php?p=14859676#p14859676
				'[AE]{TEXT}[/AE]',
				'<table width="100%" border="1">
					<tr><td width="100%" align="center">
					<table width="100%" border="0">
					  <tr>
						<td width="100%" bgcolor="#E1E4F2">
						  <table width="100%" border="0" bgcolor="#F5F5FF">
							<tr>
							  <td width="1%" bgcolor="#000000" nowrap align="left">
								<font color="#FFFFFF" face="Arial"><font size="1"><b>&nbsp;ACTIVE EFFECTS & CONDITIONS&nbsp;</b></font></font></td>
							  <td width="99%">&nbsp;</td>
							</tr>
							<tr>
							  <td width="100%" bgcolor="#FFE5BA" colspan="2">
								<table width="100%" cellpadding="2">
								  <tr>
									<td width="100%" align="left" valign="top">
									  {TEXT}
									</td>
								  </tr>
								</table>
							  </td>
							</tr>
						  </table>
						</td>
					  </tr>
					</table>
				</td></tr>
				</table>
				<p>&nbsp;</p>',

				'[AE={TEXT1}]{TEXT2}[/AE]',
				'<table width="100%" border="1">
					<tr><td width="100%" align="center">
					<table width="100%" border="0">
					  <tr>
						<td width="100%" bgcolor="#E1E4F2">
						  <table width="100%" border="0" bgcolor="#F5F5FF">
							<tr>
							  <td width="1%" bgcolor="#000000" nowrap align="left">
								<font color="#FFFFFF" face="Arial"><font size="1"><b>&nbsp; {TEXT1}&nbsp;</b></font></font></td>
							  <td width="99%">&nbsp;</td>
							</tr>
							<tr>
							  <td width="100%" bgcolor="#FFE5BA" colspan="2">
								<table width="100%" cellpadding="2">
								  <tr>
									<td width="100%" align="left" valign="top">
									  {TEXT2}
									</td>
								  </tr>
								</table>
							  </td>
							</tr>
						  </table>
						</td>
					  </tr>
					</table>
					</td></tr>
				</table>
				<p>&nbsp;</p>',

				'[AE={TEXT1?}]{TEXT2}[/AE]',
				'<table width="100%" border="1">
					<tr>
						<td width="100%" align="center">
							<table width="100%" border="0">
								<tr>
									<td width="100%" bgcolor="#E1E4F2">
										<table width="100%" border="0" bgcolor="#F5F5FF">
											<tr>
												<td width="1%" bgcolor="#000000" nowrap="nowrap" align="left">
													<font color="#FFFFFF" face="Arial">
														<font size="1">
															<b> <xsl:choose><xsl:when test="@ae"><xsl:text> </xsl:text><xsl:value-of select="@ae"/></xsl:when><xsl:otherwise>ACTIVE EFFECTS &amp; CONDITIONS</xsl:otherwise></xsl:choose> </b>
														</font>
													</font>
												</td>
												<td width="99%"> </td>
											</tr>
											<tr>
												<td width="100%" bgcolor="#FFE5BA" colspan="2">
													<table width="100%" cellpadding="2">
														<tr>
															<td width="100%" align="left" valign="top">
																<xsl:apply-templates/>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<p> </p>'
			],
			[
				// https://www.phpbb.com/community/viewtopic.php?f=438&t=2530451
				'[issue]{NUMBER}[/issue]',
				'<a href="/default/issues/{NUMBER}"> Issue #{NUMBER}</a>',

				'[issue={SIMPLETEXT}]{NUMBER}[/issue]',
				'<a href="/{SIMPLETEXT}/issues/{NUMBER}"> Issue #{NUMBER} ({SIMPLETEXT})</a>',

				'[issue={SIMPLETEXT?}]{NUMBER}[/issue]',
				'<a>
					<xsl:choose>
						<xsl:when test="@issue"><xsl:attribute name="href">/<xsl:value-of select="@issue"/>/issues/<xsl:value-of select="@content"/></xsl:attribute> Issue #<xsl:value-of select="@content"/> (<xsl:value-of select="@issue"/>)</xsl:when>
						<xsl:otherwise><xsl:attribute name="href">/default/issues/<xsl:value-of select="@content"/></xsl:attribute> Issue #<xsl:value-of select="@content"/></xsl:otherwise>
					</xsl:choose>
				</a>'
			],
		];
	}
}
