
<h1>{L_THEMES_TITLE}</h1>

<p>{L_THEMES_EXPLAIN}</p>

<form action="{S_THEME_ACTION}" method="POST"><table width="99%" cellpadding="4" cellspacing="1" border="0" align="center" class="forumline">
	<tr>
	  <th class="thHead" colspan="3">{L_THEME_SETTINGS}</th>
	</tr>
	<tr>
		<td class="row1">{L_THEME_NAME}:</td>
		<td class="row2" colspan="2"><input type="text" size="25" maxlength="100" name="style_name" value="{THEME_NAME}"></td>
	</tr>
	<tr>
		<td class="row1">{L_TEMPLATE}:</td>
		<td class="row2" colspan="2">{S_TEMPLATE_SELECT}</td>
	</tr>
	<tr>
		<td class="cattitle">{L_THEME_ELEMENT}</td>
		<td class="cattitle">{L_VALUE}</td>
		<td class="cattitle">{L_SIMPLE_NAME}</td>
	</tr>
	<tr>
		<td class="row1">{L_STYLESHEET}:<br /><span class="gensmall">Filename for CSS stylesheet to use for this theme.</span></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="head_stylesheet" value="{HEAD_STYLESHEET}"></td>
		<td class="row2">&nbsp;</td>		
	</tr>
	<tr>
		<td class="row1">{L_BACKGROUND_IMAGE}:</td>
		<td class="row2" ><input type="text" size="25" maxlength="100" name="body_background" value="{BODY_BACKGROUND}"></td>
		<td class="row2">&nbsp;</td>		
	</tr>
	<tr>
		<td class="row1">{L_BACKGROUND_COLOR}:</td>
		<td class="row2" ><input type="text" size="25" maxlength="100" name="body_bgcolor" value="{BODY_BGCOLOR}"></td>
		<td class="row2">&nbsp;</td>
	</tr>
	<tr>
		<td class="row1">{L_BODY_LINK}:</td>
		<td class="row2" ><input type="text" size="25" maxlength="100" name="body_link" value="{BODY_LINK}"></td>
		<td class="row2">&nbsp;</td>
	</tr>
	<tr>
		<td class="row1">{L_BODY_VLINK}:</td>
		<td class="row2" ><input type="text" size="25" maxlength="100" name="body_vlink" value="{BODY_VLINK}"></td>
		<td class="row2">&nbsp;</td>		
	</tr>
	<tr>
		<td class="row1">{L_BODY_ALINK}:</td>
		<td class="row2" ><input type="text" size="25" maxlength="100" name="body_alink" value="{BODY_ALINK}"></td>
		<td class="row2">&nbsp;</td>		
	</tr>
	<tr>
		<td class="row1">{L_BODY_HLINK}:</td>
		<td class="row2" ><input type="text" size="25" maxlength="100" name="body_hlink" value="{BODY_HLINK}"></td>
		<td class="row2">&nbsp;</td>		
	</tr>

	<tr>
		<td class="row1">{L_TR_COLOR1}:</td>
		<td class="row2"><input type="text" size="6" maxlength="6" name="tr_color1" value="{TR_COLOR1}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="tr_color1_name" value="{TR_COLOR1_NAME}">
	</tr>

	<tr>
		<td class="row1">{L_TR_COLOR2}:</td>
		<td class="row2"><input type="text" size="6" maxlength="6" name="tr_color2" value="{TR_COLOR2}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="tr_color2_name" value="{TR_COLOR2_NAME}">
	</tr>

	<tr>
		<td class="row1">{L_TR_COLOR3}:</td>
		<td class="row2"><input type="text" size="6" maxlength="6" name="tr_color3" value="{TR_COLOR3}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="tr_color3_name" value="{TR_COLOR3_NAME}">
	</tr>
	<tr>
		<td class="row1">{L_TR_CLASS1}:</td>
		<td class="row2"><input type="text" size="25" maxlength="25" name="tr_class1" value="{TR_CLASS1}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="tr_class1_name" value="{TR_CLASS1_NAME}">
	</tr>

	<tr>
		<td class="row1">{L_TR_CLASS2}:</td>
		<td class="row2"><input type="text" size="25" maxlength="25" name="tr_class2" value="{TR_CLASS2}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="tr_class2_name" value="{TR_CLASS2_NAME}">
	</tr>

	<tr>
		<td class="row1">{L_TR_CLASS3}:</td>
		<td class="row2"><input type="text" size="25" maxlength="25" name="tr_class3" value="{TR_CLASS3}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="tr_class3_name" value="{TR_CLASS3_NAME}">
	</tr>
	<tr>
		<td class="row1">{L_TH_COLOR1}:</td>
		<td class="row2"><input type="text" size="6" maxlength="6" name="th_color1" value="{TH_COLOR1}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="th_color1_name" value="{TH_COLOR1_NAME}">
	</tr>

	<tr>
		<td class="row1">{L_TH_COLOR2}:</td>
		<td class="row2"><input type="text" size="6" maxlength="6" name="th_color2" value="{TH_COLOR2}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="th_color2_name" value="{TH_COLOR2_NAME}">
	</tr>

	<tr>
		<td class="row1">{L_TH_COLOR3}:</td>
		<td class="row2"><input type="text" size="6" maxlength="6" name="th_color3" value="{TH_COLOR3}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="th_color3_name" value="{TH_COLOR3_NAME}">
	</tr>
	<tr>
		<td class="row1">{L_TH_CLASS1}:</td>
		<td class="row2"><input type="text" size="25" maxlength="25" name="th_class1" value="{TH_CLASS1}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="th_class1_name" value="{TH_CLASS1_NAME}">
	</tr>

	<tr>
		<td class="row1">{L_TH_CLASS2}:</td>
		<td class="row2"><input type="text" size="25" maxlength="25" name="th_class2" value="{TH_CLASS2}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="th_class2_name" value="{TH_CLASS2_NAME}">
	</tr>

	<tr>
		<td class="row1">{L_TD_CLASS3}:</td>
		<td class="row2"><input type="text" size="25" maxlength="25" name="th_class3" value="{TD_CLASS3}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="th_class3_name" value="{TD_CLASS3_NAME}">
	</tr>
	<tr>
		<td class="row1">{L_TD_COLOR1}:</td>
		<td class="row2"><input type="text" size="6" maxlength="6" name="td_color1" value="{TD_COLOR1}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="td_color1_name" value="{TD_COLOR1_NAME}">
	</tr>

	<tr>
		<td class="row1">{L_TD_COLOR2}:</td>
		<td class="row2"><input type="text" size="6" maxlength="6" name="td_color2" value="{TD_COLOR2}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="td_color2_name" value="{TD_COLOR2_NAME}">
	</tr>

	<tr>
		<td class="row1">{L_TD_COLOR3}:</td>
		<td class="row2"><input type="text" size="6" maxlength="6" name="td_color3" value="{TD_COLOR3}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="td_color3_name" value="{TD_COLOR3_NAME}">
	</tr>
	<tr>
		<td class="row1">{L_TD_CLASS1}:</td>
		<td class="row2"><input type="text" size="25" maxlength="25" name="td_class1" value="{TD_CLASS1}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="td_class1_name" value="{TD_CLASS1_NAME}">
	</tr>

	<tr>
		<td class="row1">{L_TD_CLASS2}:</td>
		<td class="row2"><input type="text" size="25" maxlength="25" name="td_class2" value="{TD_CLASS2}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="td_class2_name" value="{TD_CLASS2_NAME}">
	</tr>

	<tr>
		<td class="row1">{L_TD_CLASS3}:</td>
		<td class="row2"><input type="text" size="25" maxlength="25" name="td_class3" value="{TD_CLASS3}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="td_class3_name" value="{TD_CLASS3_NAME}">
	</tr>

	<tr>
		<td class="row1">{L_FONTFACE_1}:</td>
		<td class="row2"><input type="text" size="25" maxlength="50" name="fontface1" value="{FONTFACE1}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="fontface1_name" value="{FONTFACE1_NAME}">
	</tr>

	<tr>
		<td class="row1">{L_FONTFACE_2}:</td>
		<td class="row2"><input type="text" size="25" maxlength="50" name="fontface2" value="{FONTFACE2}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="fontface2_name" value="{FONTFACE2_NAME}">
	</tr>

	<tr>
		<td class="row1">{L_FONTFACE_3}:</td>
		<td class="row2"><input type="text" size="25" maxlength="50" name="fontface3" value="{FONTFACE3}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="fontface3_name" value="{FONTFACE3_NAME}">
	</tr>

	<tr>
		<td class="row1">{L_FONTSIZE_1}:</td>
		<td class="row2"><input type="text" size="4" maxlength="4" name="fontsize1" value="{FONTSIZE1}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="fontsize1_name" value="{FONTSIZE1_NAME}">	
	</tr>

	<tr>
		<td class="row1">{L_FONTSIZE_2}:</td>
		<td class="row2"><input type="text" size="4" maxlength="4" name="fontsize2" value="{FONTSIZE2}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="fontsize2_name" value="{FONTSIZE2_NAME}">
	</tr>

	<tr>
		<td class="row1">{L_FONTSIZE_3}:</td>
		<td class="row2"><input type="text" size="4" maxlength="4" name="fontsize3" value="{FONTSIZE3}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="fontsize3_name" value="{FONTSIZE3_NAME}">
	</tr>

	<tr>
		<td class="row1">{L_FONTCOLOR_1}:</td>
		<td class="row2"><input type="text" size="6" maxlength="6" name="fontcolor1" value="{FONTCOLOR1}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="fontcolor1_name" value="{FONTCOLOR1_NAME}">	
	</tr>

	<tr>
		<td class="row1">{L_FONTCOLOR_2}:</td>
		<td class="row2"><input type="text" size="6" maxlength="6" name="fontcolor2" value="{FONTCOLOR2}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="fontcolor2_name" value="{FONTCOLOR2_NAME}">
	</tr>

	<tr>
		<td class="row1">{L_FONTCOLOR_3}:</td>
		<td class="row2"><input type="text" size="6" maxlength="6" name="fontcolor3" value="{FONTCOLOR3}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="fontcolor3_name" value="{FONTCOLOR3_NAME}">
	</tr>

	<tr>
		<td class="row1">{L_SPAN_CLASS_1}:</td>
		<td class="row2"><input type="text" size="25" maxlength="25" name="span_class1" value="{SPAN_CLASS1}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="span_class1_name" value="{SPAN_CLASS1_NAME}">	
	</tr>

	<tr>
		<td class="row1">{L_SPAN_CLASS_2}:</td>
		<td class="row2"><input type="text" size="25" maxlength="25" name="span_class2" value="{SPAN_CLASS2}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="span_class2_name" value="{SPAN_CLASS2_NAME}">
	</tr>

	<tr>
		<td class="row1">{L_SPAN_CLASS_3}:</td>
		<td class="row2"><input type="text" size="25" maxlength="25" name="span_class3" value="{SPAN_CLASS3}"></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="span_class3_name" value="{SPAN_CLASS3_NAME}">
	</tr>


	<tr>
		<td class="catBottom" colspan="3" align="center">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="Save Settings" class="mainoption" />
		</td>
	</tr>
</table></form>

<br clear="all">
