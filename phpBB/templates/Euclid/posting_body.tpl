
<!-- BEGIN privmsg_extensions -->
<table width="98%" cellspacing="0" cellpadding="0" border="0" align="center">
	<tr>
		<td><span class="cattitle">{INBOX_IMG} {INBOX_LINK} &nbsp; {SENTBOX_IMG} {SENTBOX_LINK} &nbsp; {OUTBOX_IMG} {OUTBOX_LINK} &nbsp; {SAVEBOX_IMG} {SAVEBOX_LINK}</span></td>
	</tr>
</table>

<br clear="all" />
<!-- END privmsg_extensions -->

<form action="{S_POST_ACTION}" method="post" name="post"><table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left"><span class="gensmall"><a href="{U_INDEX}">{L_INDEX}</a> -> <a href="{U_VIEW_FORUM}">{FORUM_NAME}</a></span></td>
	</tr>
</table>

<script language="JavaScript" type="text/javascript">
<!--
// bbCode control by
// subBlue design
// www.subBlue.com


// Startup variables
var imageTag = false;
var theSelection = false;


// Check for Browser & Platform for PC & IE specific bits
// More details from: http://www.mozilla.org/docs/web-developer/sniffer/browser_type.html
var clientPC = navigator.userAgent.toLowerCase(); // Get client info
var clientVer = parseInt(navigator.appVersion); // Get browser version

var is_ie = ((clientPC.indexOf("msie") != -1) && (clientPC.indexOf("opera") == -1));
var is_nav  = ((clientPC.indexOf('mozilla')!=-1) && (clientPC.indexOf('spoofer')==-1)
                && (clientPC.indexOf('compatible') == -1) && (clientPC.indexOf('opera')==-1)
                && (clientPC.indexOf('webtv')==-1) && (clientPC.indexOf('hotjava')==-1));

var is_win   = ((clientPC.indexOf("win")!=-1) || (agt.indexOf("16bit")!=-1));
var is_mac    = (clientPC.indexOf("mac")!=-1);


// Helpline messages
b_help = "Bold text: [b]text[/b]  (alt+b)";
i_help = "Italic text: [i]text[/i]  (alt+i)";
u_help = "Underline text: [u]text[/u]  (alt+u)";
q_help = "Quote text: [quote]text[/quote]  (alt+q)";
c_help = "Code display: [code]code[/code]  (alt+c)";
l_help = "List: [list]text[/list] (alt+l)";
o_help = "Ordered list: [list=]text[/list]  (alt+o)";
p_help = "Insert image: [img]http://image_url[/img]  (alt+p)";
w_help = "Insert URL: [url]http://url[/url] or [url=http://url]URL text[/url]  (alt+w)";
a_help = "Close all open bbCode tags";
s_help = "Font color: [color=red]text[/color]  Tip: you can also use color=#FF0000";
f_help = "Font size: [size=x-small]small text[/size]";

// Define the bbCode tags
bbcode = new Array();
bbtags = new Array('[b]','[/b]','[i]','[/i]','[u]','[/u]','[quote]','[/quote]','[code]','[/code]','[list]','[/list]','[list=]','[/list]','[img]','[/img]','[url]','[/url]');
imageTag = false;

// Shows the help messages in the helpline window
function helpline(help) {
	document.post.helpbox.value = eval(help + "_help");
}


// Replacement for arrayname.length property
function getarraysize(thearray) {
	for (i = 0; i < thearray.length; i++) {
		if ((thearray[i] == "undefined") || (thearray[i] == "") || (thearray[i] == null))
			return i;
		}
	return thearray.length;
}

// Replacement for arrayname.push(value) not implemented in IE until version 5.5
// Appends element to the array
function arraypush(thearray,value) {
	thearray[ getarraysize(thearray) ] = value;
}

// Replacement for arrayname.pop() not implemented in IE until version 5.5
// Removes and returns the last element of an array
function arraypop(thearray) {
	thearraysize = getarraysize(thearray);
	retval = thearray[thearraysize - 1];
	delete thearray[thearraysize - 1];
	return retval;
}


function checkForm() {

	formErrors = false;    

	if (document.post.message.value.length < 2) {
		formErrors = "You must enter a message!";
	}

	if (formErrors) {
		alert(formErrors);
		return false;
	} else {
		bbstyle(-1);
		//formObj.preview.disabled = true;
		//formObj.submit.disabled = true;
		return true;
	}
}

function emoticon(text) {
	text = ' ' + text + ' ';
	if (document.post.message.createTextRange && document.post.message.caretPos) {
		var caretPos = document.post.message.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
		document.post.message.focus();
	} else {
	document.post.message.value  += text;
	document.post.message.focus();
	}
}


function bbfontstyle(bbopen, bbclose) {
	if ((clientVer >= 4) && is_ie && is_win) {
		theSelection = document.selection.createRange().text;
		if (!theSelection) {
			document.post.message.value += bbopen + bbclose;
			document.post.message.focus();
			return;
		}
		document.selection.createRange().text = bbopen + theSelection + bbclose;
		document.post.message.focus();
		return;
	} else {
		document.post.message.value += bbopen + bbclose;
		document.post.message.focus();
		return;
	}
}


function bbstyle(bbnumber) {

	donotinsert = false;
	theSelection = false;
	bblast = 0;

	if (bbnumber == -1) { // Close all open tags & default button names
		while (bbcode[0]) {
			butnumber = arraypop(bbcode) - 1;
			document.post.message.value += bbtags[butnumber + 1];
			buttext = eval('document.post.addbbcode' + butnumber + '.value');
			eval('document.post.addbbcode' + butnumber + '.value ="' + buttext.substr(0,(buttext.length - 1)) + '"');
		}
		document.post.message.focus();
		return;
	}

	if ((clientVer >= 4) && is_ie && is_win)
		theSelection = document.selection.createRange().text; // Get text selection
		
	if (theSelection) {
		// Add tags around selection
		document.selection.createRange().text = bbtags[bbnumber] + theSelection + bbtags[bbnumber+1];
		document.post.message.focus();
		theSelection = '';
		return;
	}
	
	// Find last occurance of an open tag the same as the one just clicked
	for (i = 0; i < bbcode.length; i++) {
		if (bbcode[i] == bbnumber+1) {
			bblast = i;
			donotinsert = true;
		}
	}

	if (donotinsert) {		// Close all open tags up to the one just clicked & default button names
		while (bbcode[bblast]) {
				butnumber = arraypop(bbcode) - 1;
				document.post.message.value += bbtags[butnumber + 1];
				buttext = eval('document.post.addbbcode' + butnumber + '.value');
				eval('document.post.addbbcode' + butnumber + '.value ="' + buttext.substr(0,(buttext.length - 1)) + '"');
				imageTag = false;
			}
			document.post.message.focus();
			return;
	} else { // Open tags
	
		if (imageTag && (bbnumber != 14)) {		// Close image tag before adding another
			document.post.message.value += bbtags[15];
			lastValue = arraypop(bbcode) - 1;	// Remove the close image tag from the list
			document.post.addbbcode14.value = "Img";	// Return button back to normal state
			imageTag = false;
		}
		
		// Open tag
		document.post.message.value += bbtags[bbnumber];
		if ((bbnumber == 14) && (imageTag == false)) imageTag = 1; // Check to stop additional tags after an unclosed image tag
		arraypush(bbcode,bbnumber+1);
		eval('document.post.addbbcode'+bbnumber+'.value += "*"');
		document.post.message.focus();
		return;
	}

}

// Insert at Claret position. Code from
// http://www.faqts.com/knowledge_base/view.phtml/aid/1052/fid/130
function storeCaret(textEl) {
	if (textEl.createTextRange) textEl.caretPos = document.selection.createRange().duplicate();
}

//-->
</script>

{POST_PREVIEW_BOX}

{ERROR_BOX}

<table width="98%" cellspacing="0" cellpadding="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table width="100%" cellspacing="1" cellpadding="4" border="0">
			<tr>
				<td class="cat" colspan="2" height="30" align="center"><span class="cattitle"><b>{L_POST_A}</b></span></td>
	        </tr>
			<!-- BEGIN username_select -->
			<tr>
				<td class="row1"><span class="gen"><b>{L_USERNAME}</b></span></td>
				<td class="row2"><span class="courier"><input type="text" name="username" size="25" maxlength="30" value="{USERNAME}" /></span></td>
			</tr>
			<!-- END username_select -->
			<!-- This is for private messaging -->
			<!-- BEGIN privmsg_extensions -->
			<tr>
				<td class="row1"><span class="gen"><b>{L_USERNAME}</b></span></td>
				<td class="row2"><input type="text" name="username" maxlength="30" size="25" /> &nbsp; <input class="liteoptiontable" type="submit"  name="usersubmit" value="{L_FIND_USERNAME}" onClick="window.open('search.php?mode=searchuser', '_phpbbsearch', 'HEIGHT=155,resizable=yes,WIDTH=400');return false;" /></td>
			</tr>
			<!-- END privmsg_extensions -->
            <tr>
				<td class="row1"><span class="gen"><b>{L_SUBJECT}</b></span></td>
				<td class="row2"><span class="courier"><input type="text" name="subject" size="50" maxlength="100" value="{SUBJECT}" /></span></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen"><b>{L_MESSAGE_BODY}</b></span><br /><br /><span class="gensmall">{HTML_STATUS}<br />{BBCODE_STATUS}<br />{SMILIES_STATUS}</span></td>
				<td class="row2" valign="middle"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td width="50%"><table cellspacing="0" cellpadding="2" border="0">
							<tr align="center" valign="middle"> 
								<td><span class="gensmall"><input type="button" class="liteoptiontable" accesskey="b" name="addbbcode0" value=" B " style="font-weight:bold; width: 30px" onClick="bbstyle(0)" onMouseOver="helpline('b')" /></span></td>
								<td><span class="gensmall"><input type="button" class="liteoptiontable" accesskey="i" name="addbbcode2" value=" i " style="font-style:italic; width: 30px" onClick="bbstyle(2)" onMouseOver="helpline('i')" /></span></td>
								<td><span class="gensmall"><input type="button" class="liteoptiontable" accesskey="u" name="addbbcode4" value=" u " style="text-decoration: underline; width: 30px" onClick="bbstyle(4)" onMouseOver="helpline('u')" /></span></td>
								<td><span class="gensmall"><input type="button" class="liteoptiontable" accesskey="q" name="addbbcode6" value="Quote" style="width: 50px" onClick="bbstyle(6)" onMouseOver="helpline('q')" /></span></td>
								<td><span class="gensmall"><input type="button" class="liteoptiontable" accesskey="c" name="addbbcode8" value="Code" style="width: 40px" onClick="bbstyle(8)" onMouseOver="helpline('c')" /></span></td>
								<td><span class="gensmall"><input type="button" class="liteoptiontable" accesskey="l" name="addbbcode10" value="List" style="width: 40px" onClick="bbstyle(10)" onMouseOver="helpline('l')" /></span></td>
								<td><span class="gensmall"><input type="button" class="liteoptiontable" accesskey="o" name="addbbcode12" value="List=" style="width: 40px" onClick="bbstyle(12)" onMouseOver="helpline('o')" /></span></td>
								<td><span class="gensmall"><input type="button" class="liteoptiontable" accesskey="p" name="addbbcode14" value="Img" style="width: 40px"  onClick="bbstyle(14)" onMouseOver="helpline('p')" /></span></td>
								<td><span class="gensmall"><input type="button" class="liteoptiontable" accesskey="w" name="addbbcode16" value="URL" style="text-decoration: underline; width: 40px" onClick="bbstyle(16)" onMouseOver="helpline('w')" /></span></td>
							</tr>
							<tr>
								<td colspan="9"><table width="100%" cellspacing="0" cellpadding="0" border="0">
									<tr> 
										<td><span class="gensmall"> &nbsp;Font color:<select name="addbbcode18" onChange="bbfontstyle('[color=' + this.form.addbbcode18.options[this.form.addbbcode18.selectedIndex].value + ']', '[/color]')" onMouseOver="helpline('s')"><option style="color:black; background-color: #FFFFFF " value="{T_FONTCOLOR1}" class="gensmall">Default</option><option style="color:darkred; background-color: white" value="darkred" class="gensmall">Dark Red</option><option style="color:red; background-color: white" value="red" class="gensmall">Red</option><option style="color:orange; background-color: white" value="orange" class="gensmall">Orange</option><option style="color:brown; background-color: white" value="brown" class="gensmall">Brown</option><option style="color:yellow; background-color: white" value="yellow" class="gensmall">Yellow</option><option style="color:green; background-color: white" value="green" class="gensmall">Green</option><option style="color:olive; background-color: white" value="olive" class="gensmall">Olive</option><option style="color:cyan; background-color: white" value="cyan" class="gensmall">Cyan</option><option style="color:blue; background-color: white" value="blue" class="gensmall">Blue</option><option style="color:darkblue; background-color: white" value="darkblue" class="gensmall">Dark Blue</option><option style="color:indigo; background-color: white" value="indigo" class="gensmall">Indigo</option><option style="color:violet; background-color: white" value="violet" class="gensmall">Violet</option><option style="color:white; background-color: white" value="white" class="gensmall">White</option><option style="color:black; background-color: white" value="black" class="gensmall">Black</option></select> &nbsp;Font size: <select name="addbbcode20" onChange="bbfontstyle('[size=' + this.form.addbbcode20.options[this.form.addbbcode20.selectedIndex].value + ']', '[/size]')" onMouseOver="helpline('f')"><option value="9" class="gensmall">Tiny</option><option value="10" class="gensmall">Small</option><option value="12" selected class="gensmall">Normal</option><option value="18" class="gensmall">Large</option><option  value="24" class="gensmall">Huge</option></select></span></td>
										<td nowrap="nowrap" align="right"><span class="gensmall"><a href="javascript:bbstyle(-1)" class="gensmall" onMouseOver="helpline('a')">Close Tags</a></span></td>
									</tr>
								</table></td>
							</tr>
							<tr> 
								<td colspan="9"><span class="gensmall"><input style="background-color: {T_TD_COLOR2}; border-style: none;" type="text" name="helpbox" size="45" maxlength="100" style="width:450px; font-size:10px" value="Tip: Styles can be applied quickly to selected text" /></span></td>
							</tr>
							<tr> 
								<td colspan="9"><span class="gen"><textarea name="message" rows="15" cols="35" wrap="virtual" style="width:450px" tabindex="3" class="post" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);">{MESSAGE}</textarea></span></td>
							</tr>
						</table></td>
						<td width="50%" valign="middle"><table border="0" cellspacing="0" cellpadding="5" align="center">
							<tr	align="center">
								<td colspan="4"><span class="gensmall"><b>Emoticons</b></span></td>
							</tr>
							<tr align="center" valign="middle">
								<td><a href="javascript:emoticon(':)')"><img src="images/smiles/icon_smile.gif" width="15" height="15"	border="0" alt="Smile"></a></td>
								<td><a href="javascript:emoticon(':D')"><img src="images/smiles/icon_biggrin.gif" width="15" height="15"	border="0" alt="Big grin"></a></td>
								<td><a href="javascript:emoticon(':lol:')"> <img src="images/smiles/icon_lol.gif" width="15" height="15"	border="0" alt="Laugh"></a></td>
								<td><a href="javascript:emoticon(';)')"><img src="images/smiles/icon_wink.gif" width="15" height="15"	border="0" alt="Wink"></a></td>
							</tr>
							<tr align="center" valign="middle">
								<td><a href="javascript:emoticon(':|')"><img src="images/smiles/icon_neutral.gif" width="15" height="15"	border="0"></a></td>
								<td><a href="javascript:emoticon(':(')"><img src="images/smiles/icon_sad.gif" width="15" height="15"	border="0"></a></td>
								<td><a href="javascript:emoticon(':?')"><img src="images/smiles/icon_confused.gif" width="15" height="15"	border="0"></a></td>
								<td><a href="javascript:emoticon(':shock:')"><img src="images/smiles/icon_eek.gif" width="15" height="15"	border="0"></a></td>
							</tr>
							<tr align="center" valign="middle">
								<td><a href="javascript:emoticon(':roll:')"><img src="images/smiles/icon_rolleyes.gif" width="15" height="15"	border="0"></a></td>
								<td><a href="javascript:emoticon('8)')"><img src="images/smiles/icon_cool.gif" width="15" height="15"	border="0"></a></td>
								<td><a href="javascript:emoticon(':p')"><img src="images/smiles/icon_razz.gif" width="15" height="15"	border="0"></a></td>
								<td><a href="javascript:emoticon(':oops:')"><img src="images/smiles/icon_redface.gif" width="15" height="15"	border="0"></a></td>
							</tr>
							<tr align="center" valign="middle">
								<td><a href="javascript:emoticon(':evil:')"><img src="images/smiles/icon_evil.gif" width="15" height="15"	border="0"></a></td>
								<td><a href="javascript:emoticon(':x')"><img src="images/smiles/icon_mad.gif" width="15" height="15"	border="0"></a></td>
								<td><a href="javascript:emoticon(':cry:')"><img src="images/smiles/icon_cry.gif" width="15" height="15"	border="0"></a></td>
								<td><a href="javascript:emoticon(':o')"><img src="images/smiles/icon_surprised.gif" width="15" height="15"	border="0"></a></td>
							</tr>
							<tr align="center" valign="middle">
								<td><a href="javascript:emoticon(':idea:')"><img src="images/smiles/icon_idea.gif" width="15" height="15"	border="0"></a></td>
								<td><a href="javascript:emoticon(':?:')"><img src="images/smiles/icon_question.gif" width="15" height="15"	border="0"></a></td>
								<td><a href="javascript:emoticon(':!:')"><img src="images/smiles/icon_exclaim.gif" width="15" height="15"	border="0"></a></td>
								<td><a href="javascript:emoticon(':arrow:')"><img src="images/smiles/icon_arrow.gif" width="15" height="15"	border="0"></a></td>
							</tr>
						</table></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen"><b>{L_OPTIONS}</b></span></td>
				<td class="row2"><table cellspacing="0" cellpadding="1" border="0">
					<!-- BEGIN html_checkbox -->
					<tr>
						<td><input type="checkbox" name="disable_html" {S_HTML_CHECKED} /></td>
						<td><span class="gen">{L_DISABLE_HTML}</span></td>
					</tr>
					<!-- END html_checkbox -->
					<!-- BEGIN bbcode_checkbox -->
					<tr>
						<td><input type="checkbox" name="disable_bbcode" {S_BBCODE_CHECKED} /></td>
						<td><span class="gen">{L_DISABLE_BBCODE}</span></td>
					</tr>
					<!-- END bbcode_checkbox -->
					<!-- BEGIN smilies_checkbox -->
					<tr>
						<td><input type="checkbox" name="disable_smilies" {S_SMILIES_CHECKED} /></td>
						<td><span class="gen">{L_DISABLE_SMILIES}</span></td>
					</tr>
					<!-- END smilies_checkbox -->
					<!-- BEGIN signature_checkbox -->
					<tr>
						<td><input type="checkbox" name="attach_sig" {S_SIGNATURE_CHECKED} /></td>
						<td><span class="gen">{L_ATTACH_SIGNATURE}</span></td>
					</tr>
					<!-- END signature_checkbox -->
					<!-- BEGIN notify_checkbox -->
					<tr>
						<td><input type="checkbox" name="notify" {S_NOTIFY_CHECKED} /></td>
						<td><span class="gen">{L_NOTIFY_ON_REPLY}</span></td>
					</tr>
					<!-- END notify_checkbox -->
					<!-- BEGIN delete_checkbox -->
					<tr>
						<td><input type="checkbox" name="delete" /></td>
						<td><span class="gen">{L_DELETE_POST}</span></td>
					</tr>
					<!-- END delete_checkbox -->
					<!-- BEGIN type_toggle -->
					<tr>
						<td></td>
						<td><span class="gen">{S_TYPE_TOGGLE}</span></td>
					</tr>
					<!-- END type_toggle -->
				</table></td>
			</tr>
{POLLBOX}
			<tr>
				<td class="cat" colspan="2" height="30" align="center">{S_HIDDEN_FORM_FIELDS}<!-- input class="liteoptiontable" type="button" tabindex="4" name="spellcheck" value="Spell Check" onclick= "doSpell ('uk', document.post.message, document.location.protocol + '//' + document.location.host + '/phpBB2/spellcheck/sproxy.php', true);" /-->&nbsp;<input class="liteoptiontable" type="submit" name="preview" value="{L_PREVIEW}" />&nbsp;<input class="mainoptiontable" type="submit" name="submit" value="{L_SUBMIT}" />&nbsp;<input class="liteoptiontable" type="submit" name="cancel" value="{L_CANCEL}" /></td>
			</tr>
		</table></td>
	</tr>
</table></form>

<table width="98%" cellspacing="2" border="0" align="center">
	<tr>
		<td valign="top"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
		<td align="right" valign="top" nowrap>{JUMPBOX}</td>
	</tr>
</table>

{TOPIC_REVIEW_BOX}
