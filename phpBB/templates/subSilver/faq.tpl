 
<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
  <tr> 
	<td align="left" valign="bottom" colspan="2"><span class="titlemedium">{TOPIC_TITLE}</span><span class="gensmall"><br>
	  &nbsp; </span></td>
  </tr>
</table>
<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
  <tr> 
	<td align="left"><span class="nav"><a href="{U_INDEX}" class="nav">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
  </tr>
</table>
<table width="100%" cellspacing="0" cellpadding="2" border="0" align="center">
  <tr> 
	<td align="left" colspan="2" class="forumline"> 
	  <table width="100%" border="0" cellspacing="0" cellpadding="1">
		<tr> 
		  <td class="innerline"> 
			<table border="0" width="100%" cellspacing="1" cellpadding="3">
			  <tr  class="cat"> 
				<td class="titlelarge">Frequently Asked Questions</td>
			  </tr>
			  <tr bgcolor="<?php echo $color2?>"> 
				<td class="general"> <a href="#register" class="general">Do I 
				  have to register?</a><br>
				  <a href="#smilies" class="general">Can I use smilies?</a><br>
				  <a href="#html" class="general">Using HTML</a><br>
				  <a href="#bbcode" class="general">Using BB Code</a><br>
				  <a href="#mods" class="general">What are moderators?</a><br>
				  <a href="#profile" class="general">Can I change my profile?</a><br>
				  <a href="#prefs" class="general">Can I customize the bulletin 
				  board in any way?</a><br>
				  <a href="#cookies" class="general">Do you use cookies?</a><br>
				  <a href="#edit" class="general">Can I edit my own posts?</a><br>
				  <a href="#attach" class="general">Can I attach files?</a><br>
				  <a href="#search" class="general">Can I search?</a><br>
				  <a href="#signature" class="general">Can I add a signature to 
				  the end of my posts?</a><br>
				  <a href="#announce" class="general">What are announcements?</a><br>
				  <a href="#pw" class="general">Is there a username/password retrieval 
				  system?</a><br>
				  <a href="#notify" class="general">Can I be notified by email 
				  if someone responds to my topic?</a><br>
				  <a href="#searchprivate" class="general">Can I search private 
				  forums?</a><br>
				  <a href="#ranks" class="general">What are the ranks in the 
				  <?php echo $sitename?>
				  </a><br>
				  <a href="#rednumbers" class="general">Why are icons flaming 
				  in the topic view?</a> <br>
				  <a name="register"></a>&nbsp; </td>
			  </tr>
			  <tr  class="cat"> 
				<td class="titlelarge">Registering</td>
			  </tr>
			  <tr bgcolor="<?php echo $color2?>"> 
				<td> <font class="postbody">Registration is only required on a 
				  per forum basis. Depending on the how the administrator has 
				  setup his/her forums some may require you to register in order 
				  to post, where some may allow you to post anonymously. If anonymous 
				  posting is allowed you can do so by simply not entering a username 
				  and password when prompted. Registration is free, and you are 
				  not required to post your real name. You are required to post 
				  your actual email address, however it will only be used to email 
				  you a new password if you have forgotten yours. You also have 
				  the option to hide you email address from everyone except the 
				  administrator, it option is selected by default but you can 
				  allow others to see your email address by selecting the 'Allow 
				  other users to view my email address' checkbox on the registration 
				  form. You can register by clicking <a href="<?php echo $url_phpbb?>/bb_register.<?php echo $phpEx?>?mode=agreement">here</a><br>
				  <a name="smilies"></a>&nbsp;</font> </td>
			  </tr>
			  <tr  class="cat"> 
				<td class="titlelarge">Smilies</td>
			  </tr>
			  <tr bgcolor="<?php echo $color2?>"> 
				<td> <font class="postbody"> You've probably seen others use smilies 
				  before in email messages or other bulletin board posts. Smilies 
				  are keyboard characters used to convey an emotion, such as a 
				  smile :) or a frown :(. This bulletin board automatically converts 
				  certain smilies to a graphical representation. The following 
				  smilies are currently supported:<br>
				  &nbsp; </font><br>
				  <table width="50%" align="CENTER" bgcolor="<?php echo $table_bgcolor?>" cellspaceing=1 border="0" cellpadding="1" cellspacing="1">
					<tr> 
					  <td> 
						<table width="100%" border="0" cellpadding="3" cellspacing="1">
						  <tr  class="cat"> 
							<td width="100" class="postbody"> 
							  <?php echo $l_smilesym?>
							</td>
							<td width="50%" class="postbody"> 
							  <?php echo $l_smileemotion?>
							</td>
							<td width="55" class="postbody"> 
							  <?php echo $l_smilepict?>
							</td>
						  </tr>
						  <?php

	  if ($getsmiles = mysql_query("SELECT * FROM smiles")) {
	     while ($smile = mysql_fetch_array($getsmiles)) {
?>
						  <tr bgcolor="<?php echo $color2?>"> 
							<td width="100" class="postbody">
							  <?php echo stripslashes($smile[code])?>
							</td>
							<td width="50%" class="postbody">
							  <?php echo stripslashes($smile[emotion])?>
							</td>
							<td width="55" class="postbody" align="center"><img src="<?php echo "$url_smiles/$smile[smile_url]";?>"> 
							</td>
						  </tr>
						  <?php
	     }
	  } else
	     echo "Could not retrieve from the smile database.";
?>
						</table>
				  </table>
				  <br>
				  <a name="html"></a>&nbsp; </td>
			  </tr>
			  <tr  class="cat"> 
				<td class="titlelarge"> Using HTML </td>
			  </tr>
			  <tr bgcolor="<?php echo $color2?>"> 
				<td class="postbody"> You may be able to use HTML in your posts, 
				  if your administrators and moderators have this option turned 
				  on. Every time you post a new note, you will be told whether 
				  BB Code and/or HTML is enabled. If HTML is on, you may use any 
				  HTML tags, but please be very careful that you proper HTML syntax. 
				  If you do not, your moderator or administrator may have to edit 
				  your post.<br>
				  <a name="bbcode"></a>&nbsp; </td>
			  <tr  class="cat"> 
				<td class="titlelarge"> Using BB Code</td>
			  </tr>
			  <tr bgcolor="<?php echo $color2?>"> 
				<td class="postbody"> BBCode is a variation on the HTML tags you 
				  may already be familiar with. Basically, it allows you to add 
				  functionality or style to your message that would normally require 
				  HTML. You can use BBCode even if HTML is not enabled for the 
				  forum you are using. You may want to use BBCode as opposed to 
				  HTML, even if HTML is enabled for your forum, because there 
				  is less coding required and it is safer to use (incorrect coding 
				  syntax will not lead to as many problems).<br>
				  <p> 
				  <table border=0 cellpadding=0 cellspacing=0 width="100%" align="CENTER">
					<tr> 
					  <td bgcolor="<?php echo $color2?>" align="center"> 
						<table width="90%" border="0" cellspacing="0" cellpadding="1" bgcolor="<?php echo $table_bgcolor?>">
						  <tr> 
							<td bgcolor="<?php echo $table_bgcolor?>"> 
							  <table border=0 cellpadding=4 cellspacing=1 width=100%>
								<tr  class="cat"> 
								  <td> <font class="titlemedium">URL Hyperlinking</font></td>
								</tr>
								<tr bgcolor="<?php echo $color2?>"> 
								  <td class="postbody"> If BBCode is enabled in 
									a forum, you no longer need to use the [URL] 
									code to create a hyperlink. Simply type the 
									complete URL in either of the following manners 
									and the hyperlink will be created automatically: 
									<ul>
									  <li> <font color="#0066FF">http://www.yourURL.com</font> 
									  <li> <font color="#0066FF">www.yourURL.com</font> 
										Notice that you can either use the complete 
										http:// address or shorten it to the www 
										domain. If the site does not begin with 
										"www", you must use the complete "http://" 
										address. Also, you may use https and ftp 
										URL prefixes in auto-link mode (when BBCode 
										is ON). 
										<p> The old [URL] code will still work, 
										  as detailed below. Just encase the link 
										  as shown in the following example (BBCode 
										  is in <font color="#FF0000">red</font>). 
										<p> 
										  <center>
											<font color="#FF0000">[url]</font>www.totalgeek.org<font color="#FF0000">[/url]</font> 
										  </center>
										<p> 
										  <center>
										  </center>
										  You can also have true hyperlinks using 
										  the [url] code. Just use the following 
										  format: <br>
										  <center>
											<font color="#FF0000">[url=http://www.totalgeek.org]</font>totalgeek.org<font color="#FF0000">[/url]</font> 
										  </center>
										<p> In the examples above, the BBCode 
										  automatically generates a hyperlink 
										  to the URL that is encased. It will 
										  also ensure that the link is opened 
										  in a new window when the user clicks 
										  on it. Note that the "http://" part 
										  of the URL is completely optional. In 
										  the second example above, the URL will 
										  hypelink the text to whatever URL you 
										  provide after the equal sign. Also note 
										  that you should NOT use quotation marks 
										  inside the URL tag. 
									</ul>
								  </td>
								<tr  class="cat"> 
								  <td> <font class="titlemedium"> Email Links</font></td>
								</tr>
								<tr bgcolor="<?php echo $color2?>"> 
								  <td> <font class="postbody"> To add a hyperlinked 
									email address within your message, just encase 
									the email address as shown in the following 
									example (BBCode is in <font color="#FF0000">red</font>). 
									</font> 
									<p> 
									  <center>
										<font class="postbody"><font color="#FF0000">[email]</font>james@totalgeek.org<font color="#FF0000">[/email]</font></font> 
									  </center>
									<p><font class="postbody"> In the example 
									  above, the BBCode automatically generates 
									  a hyperlink to the email address that is 
									  encased. </font> 
								  </td>
								</tr>
								<tr  class="cat"> 
								  <td> <font class="titlemedium"> Bold and Italics</font></td>
								</tr>
								<tr bgcolor="<?php echo $color2?>"> 
								  <td> <font class="postbody"> You can make italicized 
									text or make text bold by encasing the applicable 
									sections of your text with either the [b] 
									[/b] or [i] [/i] tags. </font> 
									<p> 
									  <center>
										<font class="postbody"> Hello, <font color="#FF0000">[b]</font><b>James</b><font color="#FF0000">[/b]</font><br>
										Hello, <font color="#FF0000">[i]</font><i>Mary</i><font color="#FF0000">[/i]</font></font> 
									  </center>
								  </td>
								</tr>
								<tr  class="cat"> 
								  <td> <font class="titlemedium"> Bullets/Lists</font></td>
								</tr>
								<tr bgcolor="<?php echo $color2?>"> 
								  <td class="postbody"> You can make bulleted 
									lists or ordered lists (by number or letter). 
									<p> Unordered, bulleted list: 
									<p> <font color="#FF0000">[list]</font> <br>
									  <font color="#FF0000">[*]</font> This is 
									  the first bulleted item.<br>
									  <font color="#FF0000">[*]</font> This is 
									  the second bulleted item.<br>
									  <font color="#FF0000">[/list]</font> 
									<p> This produces: 
									<ul>
									  <li> This is the first bulleted item. 
									  <li> This is the second bulleted item. 
									</ul>
									Note that you must include a closing [/list] 
									when you end each list. 
									<p> Making ordered lists is just as easy. 
									  Just add either [LIST=A] or [LIST=1]. Typing 
									  [List=A] will produce a list from A to Z. 
									  Using [List=1] will produce numbered lists. 
									<p> Here's an example: 
									<p> <font color="#FF0000">[list=A]</font> 
									  <br>
									  <font color="#FF0000">[*]</font> This is 
									  the first bulleted item.<br>
									  <font color="#FF0000">[*]</font> This is 
									  the second bulleted item.<br>
									  <font color="#FF0000">[/list]</font> 
									<p> This produces: 
									<ol type=A>
									  <li> This is the first bulleted item. 
									  <li> This is the second bulleted item. 
									</ol>
								  </td>
								</tr>
								<tr  class="cat"> 
								  <td> <font class="titlemedium"> Adding Images</font></td>
								</tr>
								<tr bgcolor="<?php echo $color2?>"> 
								  <td> <font class="postbody"> To add a graphic 
									within your message, just encase the URL of 
									the graphic image as shown in the following 
									example (BBCode is in <font color="#FF0000">red</font>). 
									</font> 
									<p> 
									  <center>
										<font class="postbody"><font color="#FF0000">[img]</font>http://www.totalgeek.org/images/tline.gif<font color="#FF0000">[/img]</font></font> 
									  </center>
									<p><font class="postbody"> In the example 
									  above, the BBCode automatically makes the 
									  graphic visible in your message. Note: the 
									  "http://" part of the URL is REQUIRED for 
									  the <font color="#FF0000">[img]</font> code. 
									  </font> 
								  </td>
								</tr>
								<tr  class="cat"> 
								  <td> <font class="titlemedium"> Quoting Other 
									Messages</font></td>
								</tr>
								<tr bgcolor="<?php echo $color2?>"> 
								  <td> <font class="postbody"> To reference something 
									specific that someone has posted, just cut 
									and paste the applicable verbiage and enclose 
									it as shown below (BBCode is in <font color="#FF0000">red</font>). 
									</font> 
									<p> 
									  <center>
										<font class="postbody"><font color="#FF0000">[QUOTE]</font>Ask 
										not what your country can do for you....<br>
										ask what you can do for your country.<font color="#FF0000">[/QUOTE]</font></font> 
									  </center>
									<p><font class="postbody"> In the example 
									  above, the BBCode automatically blockquotes 
									  the text you reference. </font> 
								  </td>
								</tr>
								<tr  class="cat"> 
								  <td> <font class="titlemedium"> Code Tag</font></td>
								</tr>
								<tr bgcolor="<?php echo $color2?>"> 
								  <td> <font class="postbody"> Similar to the 
									Quote tage, the Code tag adds some &lt;PRE&gt; 
									tags to preserve formatting. This useful for 
									displaying programming code, for instance. 
									</font> 
									<p> <font class="postbody"><font color="#FF0000">[CODE]</font>#!/usr/bin/perl 
									  </font> 
									<p><font class="postbody"> print "Content-type: 
									  text/html\n\n"; <br>
									  print "Hello World!"; <font color="#FF0000">[/CODE]</font></font> 
									<p><font class="postbody"> In the example 
									  above, the BBCode automatically blockquotes 
									  the text you reference and preserves the 
									  formatting of the coded text. </font> 
								  </td>
								</tr>
							  </table>
							</td>
						  </tr>
						</table>
					  </td>
					</tr>
				  </table>
				  <br>
				  <br>
				  You must not use both HTML and BBCode to do the same function. 
				  Also note that the BBCode is not case-sensitive (thus, you could 
				  use <font color="#FF0000">[URL]</font> or <font color="#FF0000">[url]</font>). 
				  <p> <font color="#0066FF">Incorrect BBCode Usage:</font> 
				  <p> <font color="#FF0000">[url]</font> www.totalgeek.org <font color="#FF0000">[/url]</font> 
					- don't put spaces between the bracketed code and the text 
					you are applying the code to. 
				  <p> <font color="#FF0000">[email]</font>james@totalgeek.org<font color="#FF0000">[email]</font> 
					- the end brackets must include a forward slash (<font color="#FF0000">[/email]</font>)<br>
					<a name="mods"></a> &nbsp; 
				</td>
			  <tr  class="cat"> 
				<td  class="titlelarge"> Moderators</td>
			  </tr>
			  <tr bgcolor="<?php echo $color2?>"> 
				<td class="postbody"> 
				  <p> Moderators control individual forums. They can edit, delete, 
					or prune any posts in their forums. If you have a question 
					about a particular forum, you should direct it to your forum 
					moderator.</p>
				  <p>Admins and forum moderators reserve the right to close or 
					delete any post that does not provide a clear and purposefull 
					topic. There are many members who still use 28.8 and 56k modems 
					that do not have the time to wade through useless and senseless 
					topics. </p>
				  <p>Anyone who posts just to increase their subBlue design Forums 
					stats or post topics out of boredom risk having there topics 
					closed, removed and/or membership revoked. </p>
				  <p>Try to make the topic wording mirror what is inside the thread. 
					Topics like "Check this out!" and "~~\\You have to see this!//~~" 
					only attract members to a topic they may not want to read.<br>
					<a name="profile"></a>&nbsp; </p>
				</td>
			  </tr>
			  <tr  class="cat"> 
				<td class="titlelarge"> Changing Your Profile</td>
			  </tr>
			  <tr bgcolor="<?php echo $color2?>"> 
				<td> <font class="postbody"> You may easily change any info stored 
				  in your registration profile, using the &quot;profile&quot; 
				  link located near the top of each page. Simply identify yourself 
				  by typing your username and password, or by logging in, and 
				  all of your profile information will appear on screen.<br>
				  <a name="prefs"></a>&nbsp; </font></td>
			  </tr>
			  <tr  class="cat"> 
				<td class="titlelarge"> Customizing Using Preferences </td>
			  </tr>
			  <tr bgcolor="<?php echo $color2?>"> 
				<td> <font class="postbody"> As a registered bulletin board user, 
				  you may store your username in memory for up to one year at 
				  a time. By doing this we create a way to keep track of who you 
				  are when you visit the forum, therefor you can customize the 
				  look of the forum by selecting from the themes that the administration 
				  has provided. Also, if the administrator allows it you may have 
				  the option of creating new themes for the fourms. In creating 
				  a new theme you will be able to set the colors, fonts and font 
				  sizes on the board, however at this time only the administrator 
				  may change the images for each theme. When a user creates a 
				  theme the images from the board's default theme will be selected. 
				  <br>
				  *NOTE: In order to use themes you MUST have cookies enabled.<br>
				  <a name="cookies"></a>&nbsp; </font></td>
			  </tr>
			  <tr  class="cat"> 
				<td class="titlelarge"> Cookies</td>
			  </tr>
			  <tr bgcolor="<?php echo $color2?>"> 
				<td> <font class="postbody"> This bulletin board uses cookies 
				  to store the following information: the last time you visited 
				  the forums, your username, and a unique session ID number when 
				  you login. These cookies are stored on your browser. If your 
				  browser does not support cookies, or you have not enabled cookies 
				  on your browser, none of these time-saving features will work 
				  properly.<br>
				  <a name="edit"></a>&nbsp; </font> </td>
			  </tr>
			  <tr  class="cat"> 
				<td class="titlelarge"> Editing Your Posts </td>
			  </tr>
			  <tr bgcolor="<?php echo $color2?>"> 
				<td> <font class="postbody"> You may edit your own posts at any 
				  time. Just go to the thread where the post to be edited is located 
				  and you will see an edit icon on the line under your message. 
				  Click on this icon and edit the post. No one else can edit your 
				  post, except for the forum moderator or the bulletin board administrator. 
				  Also, for up to 30 mins after you have posted you message the 
				  edit post screen will give you the option of deleteing that 
				  post. After 30 mins however only the moderator and/or administrator 
				  can remove the post.<br>
				  <a name="signature"></a>&nbsp; </font></td>
			  </tr>
			  <tr  class="cat"> 
				<td class="titlelarge">Adding Signatures </td>
			  </tr>
			  <tr bgcolor="<?php echo $color2?>"> 
				<td class="postbody"> You may use a signature on your posts. If 
				  you click on the profile link at the top of most pages, you 
				  will be able to edit your profile, including your standard signature. 
				  Once you have a signature stored, you can choose to include 
				  it any post you make by checking the &quot;include signature&quot; 
				  box when you create your post. This bulletin board's administrator 
				  may elect to turn the signature feature off at any time, however. 
				  If that is the case, the &quot;include signature&quot; option 
				  will not appear when you post a note, even if you have stored 
				  a signature. You may also change your signature at any time 
				  by changing your profile. 
				  <p>Note: You may use HTML or <a href="#bbcode">BB Code</a> if 
					the admin has enabled these options.<br>
					<a name="attach"></a>&nbsp; </p>
				</td>
			  </tr>
			  <tr  class="cat"> 
				<td class="titlelarge"> Attaching Files </td>
			  </tr>
			  <tr bgcolor="<?php echo $color2?>"> 
				<td> <font class="postbody"> For security reasons, you may not 
				  attach files to any posts. You may cut and paste text into your 
				  post, however, or use HTML and/or BB Code (if enabled) to provide 
				  hyperlinks to outside documents. File attachements will be included 
				  in a future version of phpBB.<br>
				  <a name="search"></a>&nbsp; </font></td>
			  </tr>
			  <tr  class="cat"> 
				<td class="titlelarge"> Searching For Specific Posts </td>
			  </tr>
			  <tr bgcolor="<?php echo $color2?>"> 
				<td> <font class="postbody"> You may search for specific posts 
				  based on a word or words found in the posts, a user name, a 
				  date, and/or a particular forum(s). Just click on the &quot;search&quot; 
				  link at the top of most pages.<br>
				  <a name="announce"></a>&nbsp; </font></td>
			  </tr>
			  <tr  class="cat"> 
				<td class="titlelarge"> Announcements </td>
			  </tr>
			  <tr bgcolor="<?php echo $color2?>"> 
				<td class="postbody"> Announcements have not been implemented, 
				  but are planned in a future release. However, the administrator 
				  can create a forum where only other administrators and moderators 
				  can post. This type of forum can easly be used as an announcement 
				  forum.<br>
				  <a name="pw"></a>&nbsp; </td>
			  </tr>
			  <tr  class="cat"> 
				<td class="titlelarge"> Lost User Name and/or Password </td>
			  </tr>
			  <tr bgcolor="<?php echo $color2?>"> 
				<td> <font  class="postbody"> In the even that you lose your password 
				  you can click on the &quot;Forgotten your password?&quot; link 
				  provided in the message posting screens next to the password 
				  field. This link will take you to a page where you can fill 
				  in your username and email address. The system will then email 
				  a new, randomly generated, password to the email address listed 
				  in your profile, assuming you supplied the correct email address.<br>
				  <a name="notify"></a>&nbsp; </font></td>
			  </tr>
			  <tr  class="cat"> 
				<td class="titlelarge"> Email Notification </td>
			  </tr>
			  <tr bgcolor="<?php echo $color2?>"> 
				<td> <font  class="postbody"> If you create a new topic, you have 
				  the option of receiving an email notification every time someone 
				  posts a reply to your topic. Just check the email notification 
				  box on the &quot;New Topic&quot; forum when you create your 
				  new topic if you want to use this feature.<br>
				  <a name="searchprivate"></a>&nbsp; </font> </td>
			  </tr>
			  <tr  class="cat"> 
				<td class="titlelarge"> <b>Can I search private forums?</b> </td>
			  </tr>
			  <tr bgcolor="<?php echo $color2?>"> 
				<td> <font class="postbody"> Yes, but you cannot read any of the 
				  posts unless you have the password to the private forum.<br>
				  <a name="ranks"></a>&nbsp; </font></td>
			  </tr>
			  <tr  class="cat"> 
				<td class="titlelarge"> What are the ranks for the subBlue design 
				  Forums? </td>
			  </tr>
			  <tr bgcolor="<?php echo $color2?>"> 
				<td> <font class="postbody"> The subBlue design Forums have established 
				  methods to classify their users by activity through the number 
				  of posts.<br>
				  The current ranks are as follows:<br>
				  <br>
				  <?php
	$sql = "SELECT * FROM ranks WHERE rank_special = 0";
	if(!$r = mysql_query($sql, $db)) {
	echo "Error connecting to the database";
	include('page_tail.'.$phpEx);
	exit();
	}
	?>
				  <table border="0" width="<?php echo $TableWidth?>" cellpadding="1" cellspacing="0" align="CENTER" valign="TOP">
					<tr> 
					  <td bgcolor="<?php echo $table_bgcolor?>"> 
						<table border="0" cellpadding="1" cellspacing="1" width="100%">
						  <tr  class="cat" align="CENTER"> 
							<td><font class="general">&nbsp;Rank Title&nbsp;</font></td>
							<td><font class="general">&nbsp;Minimum Posts&nbsp;</font></td>
							<td><font class="general">&nbsp;Maximum Posts&nbsp;</font></td>
							<td><font class="general">&nbsp;Rank Image</font></td>
						  </tr>
						  <?php
	if($m = mysql_fetch_array($r)) {
	do {
	echo "<TR BGCOLOR=\"$color2\" ALIGN=\"CENTER\">";
	echo "<TD><font class=\"postbody\">$m[rank_title]</font></TD>";
	echo "<TD><font class=\"postbody\">$m[rank_min]</font></TD>";
	echo "<TD><font class=\"postbody\">$m[rank_max]</font></TD>";
	if($m[rank_image] != '')
	   echo "<TD><img src=\"$url_images/$m[rank_image]\"></TD>";
	else
	   echo "<TD>&nbsp;</TD>";
	echo "</TR>";
	} while($m = mysql_fetch_array($r));
	}
	else {
	echo "<TR BGCOLOR=\"$color2\" ALIGN=\"CENTER\">";
	echo "<TD COLSPAN=\"4\">No Ranks in the database</TD>";
	echo "</TR>";
	}
	?>
						</table>
				  </table>
				  </font> <br>
				  <font class="postbody"> The adminstrator also has the option 
				  of assigning special ranks to any user they choose. The above 
				  table does not list these special ranks. <br>
				  &nbsp; </font> </td>
			  </tr>
			  <tr  class="cat"> 
				<td class="titlelarge"> <a name="rednumbers"></a>Why are some 
				  post icons <font color="#FF0033"> <b>flaming</b> </font> in 
				  the forum view? </td>
			  </tr>
			  <tr bgcolor="<?php echo $color2?>"> 
				<td> <font class="postbody"> Flaming icons signify that there 
				  are 
				  <?php echo $hot_threshold?>
				  or more posts in that thread. It is a warning to slower connections 
				  that the thread may take some time to load.<br>
				  </font></td>
			  </tr>
			</table>
		  </td>
		</tr>
	  </table>
	</td>
  </tr>
</table>
