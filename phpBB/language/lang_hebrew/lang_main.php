<?php
/***************************************************************************
 *                            lang_main.php [Hebrew]
 *                              -------------------
 *     begin                : Thu Jul 4 2002
 *     copyright            : (C) 2002 Gil Osher
 *     email                : dolfin@rpg.org.il
 *
 *     $Id: lang_main.php,v 1.85.2.3 2002/07/04 16:45:21 dolfin Exp $
 *
 ****************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

//
// Add your details here if wanted, e.g. Name, username, email address, website
//

//
// The format of this file is ---> $lang['message'] = 'text';
//
// You should also try to set a locale and a character encoding (plus direction). The encoding and direction
// will be sent to the template. The locale may or may not work, it's dependent on OS support and the syntax
// varies ... give it your best guess!
//

$lang['ENCODING'] = 'WINDOWS-1255';
$lang['DIRECTION'] = 'RTL';
$lang['LEFT'] = 'RIGHT';
$lang['RIGHT'] = 'LEFT';
$lang['DATE_FORMAT'] = 'd M Y'; // This should be changed to the default date format for your language, php date() format

// This is optional, if you would like a _SHORT_ message output
// along with our copyright message indicating you are the translator
// please add it here.
// $lang['TRANSLATION'] = 'תורגם על-ידי <A HREF="mailto:dolfin@rpg.org.il">גיל אשר</A> עבור <A HREF="http://www.rpg.org.il">העז הכאוטית</A>.';

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = 'פורום';
$lang['Category'] = 'קטגוריה';
$lang['Topic'] = 'נושא';
$lang['Topics'] = 'נושאים';
$lang['Replies'] = 'תגובות';
$lang['Views'] = 'צפיות';
$lang['Post'] = 'הודעה';
$lang['Posts'] = 'הודעות';
$lang['Posted'] = 'פורסם';
$lang['Username'] = 'שם משתמש';
$lang['Password'] = 'סיסמה';
$lang['Email'] = 'דואר אלקטרוני';
$lang['Poster'] = 'מפרסם';
$lang['Author'] = 'מחבר';
$lang['Time'] = 'זמן';
$lang['Hours'] = 'שעות';
$lang['Message'] = 'הודעה';

$lang['1_Day'] = 'יום אחד';
$lang['7_Days'] = '7 ימים';
$lang['2_Weeks'] = 'שבועיים';
$lang['1_Month'] = 'חודש אחד';
$lang['3_Months'] = '3 חודשים';
$lang['6_Months'] = '6 חודשים';
$lang['1_Year'] = 'שנה אחת';

$lang['Go'] = 'לך';
$lang['Jump_to'] = 'קפוץ אל';
$lang['Submit'] = 'שלח';
$lang['Reset'] = 'התחל מחדש';
$lang['Cancel'] = 'ביטול';
$lang['Preview'] = 'תצוגה מקדימה';
$lang['Confirm'] = 'אישור';
$lang['Spellcheck'] = 'בדיקת איות';
$lang['Yes'] = 'כן';
$lang['No'] = 'לא';
$lang['Enabled'] = 'פועל';
$lang['Disabled'] = 'כבוי';
$lang['Error'] = 'שגיאה';

$lang['Next'] = 'הבא';
$lang['Previous'] = 'הקודם';
$lang['Goto_page'] = 'לך לעמוד';
$lang['Joined'] = 'הצטרף';
$lang['IP_Address'] = 'כתובת IP';

$lang['Select_forum'] = 'בחר פורום';
$lang['View_latest_post'] = 'צפה בהודעה האחרונה';
$lang['View_newest_post'] = 'צפה בהודעה החדשה ביותר';
$lang['Page_of'] = 'עמוד <b>%d</b> מתוך <b>%d</b>'; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = 'מספר ICQ';
$lang['AIM'] = 'כתובת AIM';
$lang['MSNM'] = 'MSN Messenger';
$lang['YIM'] = 'Yahoo Messenger';

$lang['Forum_Index'] = 'אינדקס הפורומים של %s';  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = 'פרסם נושא חדש';
$lang['Reply_to_topic'] = 'הגב לנושא';
$lang['Reply_with_quote'] = 'הגב עם ציטוט';

$lang['Click_return_topic'] = 'לחץ %sכאן%s כדי לחזור לנושא'; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = 'לחץ %sכאן%s כדי לנסות שוב';
$lang['Click_return_forum'] = 'לחץ %sכאן%s כדי לחזור לפורום';
$lang['Click_view_message'] = 'לחץ %sכאן%s כדי לראות את הודעתך';
$lang['Click_return_modcp'] = 'לחץ %sכאן%s כדי לחזור ללוח הבקרה של האחראים';
$lang['Click_return_group'] = 'לחץ %sכאן%s כדי לחזור למידע הקבוצה';

$lang['Admin_panel'] = 'לך ללוח הניהול';

$lang['Board_disable'] = 'מצטערים, אך לוח זה אינו זמין כרגע, אנא נסה במועד מאוחר יותר';


//
// Global Header strings
//
$lang['Registered_users'] = 'משתמשים רשומים:';
$lang['Browsing_forum'] = 'משתמשים המדפדפים בפורום זה:';
$lang['Online_users_zero_total'] = 'בסך הכל ישנם <b>0</b> משתמשים מחוברים :: ';
$lang['Online_users_total'] = 'בסך הכל ישנם <b>%d</b> משתמשים מחוברים :: ';
$lang['Online_user_total'] = 'בסך הכל ישנו משתמש <b>אחד</b> מחובר :: ';
$lang['Reg_users_zero_total'] = '0 רשומים, ';
$lang['Reg_users_total'] = '%d רשומים, ';
$lang['Reg_user_total'] = 'אחד רשום, ';
$lang['Hidden_users_zero_total'] = '0 חבויים ו';
$lang['Hidden_user_total'] = '%d חבויים ו';
$lang['Hidden_users_total'] = 'אחד חבוי ו';
$lang['Guest_users_zero_total'] = '-0 אורחים';
$lang['Guest_users_total'] = '-%d אורחים';
$lang['Guest_user_total'] = 'אורח אחד';
$lang['Record_online_users'] = 'המספר הרב ביותר של משתמשים מחוברים אי פעם היה <b>%s</b> בתאריך %s'; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = '%sמנהל%s';
$lang['Mod_online_color'] = '%sאחראי%s';

$lang['You_last_visit'] = 'ביקרת לאחרונה ב %s'; // %s replaced by date/time
$lang['Current_time'] = 'השעה עכשיו היא %s'; // %s replaced by time

$lang['Search_new'] = 'הצג הודעות מאז הביקור האחרון';
$lang['Search_your_posts'] = 'צפה בהודעותיך';
$lang['Search_unanswered'] = 'צפה בהודעות שטרם נענו';

$lang['Register'] = 'הרשם';
$lang['Profile'] = 'פרופיל';
$lang['Edit_profile'] = 'ערוך את הפרופיל שלך';
$lang['Search'] = 'חיפוש';
$lang['Memberlist'] = 'רשימת חברים';
$lang['FAQ'] = 'FAQ';
$lang['BBCode_guide'] = 'מדריך BBCode';
$lang['Usergroups'] = 'קבוצות משתמשים';
$lang['Last_Post'] = 'הודעה אחרונה';
$lang['Moderator'] = 'אחראי';
$lang['Moderators'] = 'אחראים';


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = 'המשתמשים שלנו פרסמו סך-הכל <b>0</b> הודעות'; // Number of posts
$lang['Posted_articles_total'] = 'המשתמשים שלנו פרסמו סך-הכל <b>%d</b> הודעות'; // Number of posts
$lang['Posted_article_total'] = 'המשתמשים שלנו פרסמו סך-הכל הודעה <b>אחת</b>'; // Number of posts
$lang['Registered_users_zero_total'] = 'יש לנו <b>0</b> משתמשים רשומים'; // # registered users
$lang['Registered_users_total'] = 'יש לנו <b>%d</b> משתמשים רשומים'; // # registered users
$lang['Registered_user_total'] = 'יש לנו משתמש רשום <b>אחד</b>'; // # registered users
$lang['Newest_user'] = 'המשתמש הטרי ביותר הוא <b>%s%s%s</b>'; // a href, username, /a 

$lang['No_new_posts_last_visit'] = 'אין הודעות חדשות מאז ביקורך האחרון';
$lang['No_new_posts'] = 'אין הודעות חדשות';
$lang['New_posts'] = 'הודעות חדשות';
$lang['New_post'] = 'הודעה חדשה';
$lang['No_new_posts_hot'] = 'אין הודעות חדשות [ פופולארי ]';
$lang['New_posts_hot'] = 'יש הודעות חדשות [ פופולארי ]';
$lang['No_new_posts_locked'] = 'אין הודעות חדשות [ נעול ]';
$lang['New_posts_locked'] = 'יש הודעות חדשות [ נעול ]';
$lang['Forum_is_locked'] = 'הפורום נעול';


//
// Login
//
$lang['Enter_password'] = 'אנא הזן את שם המשתמש והסיסמה שלך כדי להתחבר';
$lang['Login'] = 'התחבר';
$lang['Logout'] = 'התנתק';

$lang['Forgotten_password'] = 'שכחתי את סיסמתי';

$lang['Log_me_in'] = 'חבר אותי אוטומטית בכל ביקור';

$lang['Error_login'] = 'סיפקת שם משתמש שגוי או לא פעיל או סיסמה שגויה';


//
// Index page
//
$lang['Index'] = 'אינדקס';
$lang['No_Posts'] = 'אין הודעות';
$lang['No_forums'] = 'בלוח זה אין פורומים';

$lang['Private_Message'] = 'הודעה פרטית';
$lang['Private_Messages'] = 'הודעות פרטיות';
$lang['Who_is_Online'] = 'מי מחובר';

$lang['Mark_all_forums'] = 'סמן את כל הפורומים כנקראו';
$lang['Forums_marked_read'] = 'כל הפורומים סומנו כנקראו';


//
// Viewforum
//
$lang['View_forum'] = 'ראה פורום';

$lang['Forum_not_exist'] = 'הפורום שבחרת אינו קיים';
$lang['Reached_on_error'] = 'הגעת לעמוד זה בטעות';

$lang['Display_topics'] = 'הצג נושאים מלפני';
$lang['All_Topics'] = 'כל הנושאים';

$lang['Topic_Announcement'] = '<b>הכרזה:</b>';
$lang['Topic_Sticky'] = '<b>דביק:</b>';
$lang['Topic_Moved'] = '<b>הוזז:</b>';
$lang['Topic_Poll'] = '<b>[ סקר ]</b>';

$lang['Mark_all_topics'] = 'סמן את כל הנושאים כנקראו';
$lang['Topics_marked_read'] = 'כל הנושאים בפורום זה סומנו עכשיו כנקראו';

$lang['Rules_post_can'] = 'אתה <b>יכול</b> לפרסם נושאים חדשים בפורום זה';
$lang['Rules_post_cannot'] = 'אתה <b>לא יכול</b> לפרסם נושאים חדשים בפורום זה';
$lang['Rules_reply_can'] = 'אתה <b>יכול</b> להגיב לנושאים בפורום זה';
$lang['Rules_reply_cannot'] = 'אתה <b>לא יכול</b> להגיב לנושאים בפורום זה';
$lang['Rules_edit_can'] = 'אתה <b>יכול</b> לערוך את הודעותיך בפורום זה';
$lang['Rules_edit_cannot'] = 'אתה <b>לא יכול</b> לערוך את הודעותיך בפורום זה';
$lang['Rules_delete_can'] = 'אתה <b>יכול</b> למחוק את הודעותיך בפורום זה';
$lang['Rules_delete_cannot'] = 'אתה <b>לא יכול</b> למחוק את הודעותיך בפורום זה';
$lang['Rules_vote_can'] = 'אתה <b>יכול</b> להצביע בסקרים בפורום זה';
$lang['Rules_vote_cannot'] = 'אתה <b>לא יכול</b> להצביע בסקרים בפורום זה';
$lang['Rules_moderate'] = 'אתה <b>יכול</b> %sלהשגיח על פורום זה%s'; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = 'אין הודעות בפורום זה<br />לחץ על הקישור<b>פרסם נושא חדש</b> בעמוד זה כדי לפרסם אחת';


//
// Viewtopic
//
$lang['View_topic'] = 'ראה נושא';

$lang['Guest'] = 'אורח';
$lang['Post_subject'] = 'נושא ההודעה';
$lang['View_next_topic'] = 'צפה בנושא הבא';
$lang['View_previous_topic'] = 'צפה בנושא הקודם';
$lang['Submit_vote'] = 'שלח קולך';
$lang['View_results'] = 'ראה תוצאות';

$lang['No_newer_topics'] = 'אין נושאים חדשים יותר בפורום זה';
$lang['No_older_topics'] = 'אין נושאים ישנים יותר בפורום זה';
$lang['Topic_post_not_exist'] = 'הנושא או ההודעה שביקשת אינם קיימים';
$lang['No_posts_topic'] = 'לא קיימות הודעות לנושא זה';

$lang['Display_posts'] = 'הצג הודעות מלפני';
$lang['All_Posts'] = 'כל ההודעות';
$lang['Newest_First'] = 'הכי חדשות קודם';
$lang['Oldest_First'] = 'הכי ישנות קודם';

$lang['Back_to_top'] = 'חזרה למעלה';

$lang['Read_profile'] = 'צפה בפרופיל המשתמש'; 
$lang['Send_email'] = 'שלח דוא\"ל';
$lang['Visit_website'] = 'בקר באתר המפרסם';
$lang['ICQ_status'] = 'מצב ICQ';
$lang['Edit_delete_post'] = 'ערוך/מחק הודעה זו';
$lang['View_IP'] = 'צפה ב IP של המשתמש';
$lang['Delete_post'] = 'מחק הודעה זו';

$lang['wrote'] = 'כתב'; // proceeds the username and is followed by the quoted text
$lang['Quote'] = 'ציטוט'; // comes before bbcode quote output.
$lang['Code'] = 'קוד'; // comes before bbcode code output.

$lang['Edited_time_total'] = 'נערך לאחרונה על-ידי %s בתאריך %s, סך-הכל נערך פעם אחת'; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = 'נערך לאחרונה על-ידי %s בתאריך %s, סך-הכל נערך %d פעמים'; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = 'נעל נושא זה';
$lang['Unlock_topic'] = 'פתח נושא זה';
$lang['Move_topic'] = 'הזז נושא זה';
$lang['Delete_topic'] = 'מחק נושא זה';
$lang['Split_topic'] = 'פצל נושא זה';

$lang['Stop_watching_topic'] = 'הפסק לעקוב אחר נושא זה';
$lang['Start_watching_topic'] = 'עקוב אחר נושא זה לתגובות';
$lang['No_longer_watching'] = 'אתה לא עוקב יותר אחר נושא זה';
$lang['You_are_watching'] = 'אתה עכשיו עוקב אחרי נושא זה';

$lang['Total_votes'] = 'סך-הכל הצבעות';

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = 'גוף ההודעה';
$lang['Topic_review'] = 'סיקור נושא';

$lang['No_post_mode'] = 'לא צויין מצב הודעה'; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = 'פרסם נושא חדש';
$lang['Post_a_reply'] = 'פרסם תגובה';
$lang['Post_topic_as'] = 'פרסם נושא כ';
$lang['Edit_Post'] = 'ערוך הודעה';
$lang['Options'] = 'אפשרויות';

$lang['Post_Announcement'] = 'הכרזה';
$lang['Post_Sticky'] = 'דביק';
$lang['Post_Normal'] = 'רגיל';

$lang['Confirm_delete'] = 'אתה בטוח שברצונך למחוק הודעה זו?';
$lang['Confirm_delete_poll'] = 'אתה בטוח שברצונך למחוק סקר זה?';

$lang['Flood_Error'] = 'לא ניתן לפרסם הודעה כל-כך מעט זמן אחרי האחרונה שלך, אנא נסה שוב תוך זמן קצר';
$lang['Empty_subject'] = 'אתה חייב לציין נושא כשמפרסמים נושא חדש';
$lang['Empty_message'] = 'אתה חייב להקליד תוכן להודעה שלך';
$lang['Forum_locked'] = 'פורום זה נעול, אתה לא יכול לפרסם, להגיב או לערוך נושאים';
$lang['Topic_locked'] = 'נושא זה נעול ואתה לא יכול לערוך הודעות או להגיב';
$lang['No_post_id'] = 'לא צויין ID ההודעה';
$lang['No_topic_id'] = 'אתה חייב לבחור נושא להגיב לו';
$lang['No_valid_mode'] = 'אתה יכול רק לפרסם, להגיב, לערוך או לצטט הודעות, אנא חזור ונסה שוב';
$lang['No_such_post'] = 'אין הודעה כזו, אנא חזור ונסה שוב';
$lang['Edit_own_posts'] = 'מצטער, אך אתה יכול לערוך רק את ההודעות שלך';
$lang['Delete_own_posts'] = 'מצטער, אך אתה יכול למחוק רק את ההודעות שלך';
$lang['Cannot_delete_replied'] = 'מצטער, אך אתה לא יכול למחוק הודעות שהגיבו להן';
$lang['Cannot_delete_poll'] = 'מצטער, אך אינך יכול למחוק סקר פעיל';
$lang['Empty_poll_title'] = 'אתה חייב למלא כותרת לסקר שלך';
$lang['To_few_poll_options'] = 'אתה חייב לציין לפחות 2 אפשרויות לסקר';
$lang['To_many_poll_options'] = 'ניסית להקליד יותר מידי אפשרויות לסקר';
$lang['Post_has_no_poll'] = 'בהודעה זו אין סקר';
$lang['Already_voted'] = 'כבר הצבעת לסקר זה';
$lang['No_vote_option'] = 'חובה לבחור אפשרות כשמצביעים';

$lang['Add_poll'] = 'הוסף סקר';
$lang['Add_poll_explain'] = 'אם אינך רוצה להוסיף סקר להודעתך השאר שדה זה ריק';
$lang['Poll_question'] = 'שאלת הסקר';
$lang['Poll_option'] = 'אפשרות הסקר';
$lang['Add_option'] = 'הוסף אפשרות';
$lang['Update'] = 'עדכן';
$lang['Delete'] = 'מחק';
$lang['Poll_for'] = 'הרץ סקר למשך';
$lang['Days'] = 'ימים'; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = '[ כתוב 0 או השאר ריק עבור סקר שאינו מסתיים ]';
$lang['Delete_poll'] = 'מחק סקר';

$lang['Disable_HTML_post'] = 'כבה HTML בהודעה זו';
$lang['Disable_BBCode_post'] = 'כבה BBCode בהודעה זו';
$lang['Disable_Smilies_post'] = 'כבה סמיילים בהודעה זו';

$lang['HTML_is_ON'] = 'HTML <u>דולק</u>';
$lang['HTML_is_OFF'] = 'HTML <u>כבוי</u>';
$lang['BBCode_is_ON'] = '%sBBCode%s <u>דולק</u>'; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = '%sBBCode%s <u>כבוי</u>';
$lang['Smilies_are_ON'] = 'סמיילים <u>דולקים</u>';
$lang['Smilies_are_OFF'] = 'סמיילים <u>כבויים</u>';

$lang['Attach_signature'] = 'צרף חתימה (ניתן לשנות את החתימה בפרופיל)';
$lang['Notify'] = 'יידע אותי כאשר תגובה מתפרסמת';
$lang['Delete_post'] = 'מחק הודעה זו';

$lang['Stored'] = 'ההודעה שלך נקלטה בהצלחה';
$lang['Deleted'] = 'ההודעה שלך נמחקה בהצלחה';
$lang['Poll_delete'] = 'הסקר שלך נמחק בהצלחה';
$lang['Vote_cast'] = 'הקול שלך נשלח';

$lang['Topic_reply_notification'] = 'הודעה על תגובה לנושא';

$lang['bbcode_b_help'] = 'טקטס מודגש: [b]טקסט[/b]  (alt+b)';
$lang['bbcode_i_help'] = 'טקסט נטוי: [i]טקסט[/i]  (alt+i)';
$lang['bbcode_u_help'] = 'טקסט עם קו תחתי: [u]טקסט[/u]  (alt+u)';
$lang['bbcode_q_help'] = 'ציטוט טקסט: [quote]טקסט[/quote]  (alt+q)';
$lang['bbcode_c_help'] = 'הצגת קוד: [code]קוד[/code]  (alt+c)';
$lang['bbcode_l_help'] = 'רשימה: [list]טקסט[/list] (alt+l)';
$lang['bbcode_o_help'] = 'רשימה מסודרת: [list=]טקסט[/list]  (alt+o)';
$lang['bbcode_p_help'] = 'צרף תמונה: [img]http://image_url[/img]  (alt+p)';
$lang['bbcode_w_help'] = 'צרף כתובת: [url]http://url[/url] או [url=http://url]טקסט הכתובת[/url]  (alt+w)';
$lang['bbcode_a_help'] = 'סגור את כל תגי ה bbCode הפתוחים';
$lang['bbcode_s_help'] = 'צבע גופן: [color=red]טקסט[/color]  עצה: ניתן גם להשתמש ב color=#FF0000';
$lang['bbcode_f_help'] = 'גודל גופן: [size=x-small]טקסט קטן[/size]';

$lang['Emoticons'] = 'סמיילים';
$lang['More_emoticons'] = 'ראה עוד סמיילים';

$lang['Font_color'] = 'צבע גופן';
$lang['color_default'] = 'ברירת מחדל';
$lang['color_dark_red'] = 'אדום כהה';
$lang['color_red'] = 'אדום';
$lang['color_orange'] = 'כתום';
$lang['color_brown'] = 'חום';
$lang['color_yellow'] = 'צהוב';
$lang['color_green'] = 'ירוק';
$lang['color_olive'] = 'זית';
$lang['color_cyan'] = 'תכלת';
$lang['color_blue'] = 'כחול';
$lang['color_dark_blue'] = 'כחול כהה';
$lang['color_indigo'] = 'סגול כהה';
$lang['color_violet'] = 'סגול';
$lang['color_white'] = 'לבן';
$lang['color_black'] = 'שחור';

$lang['Font_size'] = 'גודל הגופן';
$lang['font_tiny'] = 'קטנטן';
$lang['font_small'] = 'קטן';
$lang['font_normal'] = 'רגיל';
$lang['font_large'] = 'גדול';
$lang['font_huge'] = 'ענק';

$lang['Close_Tags'] = 'סגור תגים';
$lang['Styles_tip'] = 'עצה: אפשר לצרף סגנונות במהירות לטקסט מסומן';


//
// Private Messaging
//
$lang['Private_Messaging'] = 'הודעות פרטיות';

$lang['Login_check_pm'] = 'התחבר כדי לבדוק הודעות פרטיות';
$lang['New_pms'] = 'יש לך %d הודעות חדשות'; // You have 2 new messages
$lang['New_pm'] = 'יש לך הודעה חדשה אחת'; // You have 1 new message
$lang['No_new_pm'] = 'אין לך הודעות חדשות';
$lang['Unread_pms'] = 'יש לך %d הודעות שטרם נקראו';
$lang['Unread_pm'] = 'יש לך הודעה אחת שטרם נקראה';
$lang['No_unread_pm'] = 'אין לך הודעות שטרם נקראו';
$lang['You_new_pm'] = 'הודעה פרטית חדשה מחכה לך בתיבת הדואר הנכנס';
$lang['You_new_pms'] = 'הודעות פרטיות חדשות מחכות לך בתיבת הדואר הנכנס';
$lang['You_no_new_pm'] = 'אין הודעות פרטיות חדשות המחכות לך';

$lang['Unread_message'] = 'הודעה שטרם נקראה';
$lang['Read_message'] = 'הודעה שנקראה';

$lang['Read_pm'] = 'קרא הודעה';
$lang['Post_new_pm'] = 'פרסם הודעה';
$lang['Post_reply_pm'] = 'הגב להודעה';
$lang['Post_quote_pm'] = 'צטט הודעה';
$lang['Edit_pm'] = 'ערוך הודעה';

$lang['Inbox'] = 'תיבת דואר נכנס';
$lang['Outbox'] = 'תיבת דואר יוצא';
$lang['Savebox'] = 'תיבת דואר שמור';
$lang['Sentbox'] = 'תיבת דואר נשלח';
$lang['Flag'] = 'דגל';
$lang['Subject'] = 'נושא';
$lang['From'] = 'מאת';
$lang['To'] = 'למען';
$lang['Date'] = 'תאריך';
$lang['Mark'] = 'סמן';
$lang['Sent'] = 'נשלח';
$lang['Saved'] = 'נשלח';
$lang['Delete_marked'] = 'מחק מסומנים';
$lang['Delete_all'] = 'מחק הכל';
$lang['Save_marked'] = 'שמור מסומנים'; 
$lang['Save_message'] = 'שמור הודעה';
$lang['Delete_message'] = 'מחק הודעה';

$lang['Display_messages'] = 'הצג הודעות מלפני'; // Followed by number of days/weeks/months
$lang['All_Messages'] = 'כל ההודעות';

$lang['No_messages_folder'] = 'אין לך הודעות בתיקייה זו';

$lang['PM_disabled'] = 'הודעות פרטיות נחסמו בפורום זה';
$lang['Cannot_send_privmsg'] = 'מצטער, אך המנהל מנע ממך לשלוח הודעות פרטיות';
$lang['No_to_user'] = 'יש לציין שם משתמש אליו מיועדת ההודעה';
$lang['No_such_user'] = 'מצטער, אך לא קיים משתמש כזה';

$lang['Disable_HTML_pm'] = 'כבה HTML בהודעה זו';
$lang['Disable_BBCode_pm'] = 'כבה BBCode בהודעה זו';
$lang['Disable_Smilies_pm'] = 'כבה סמיילים בהודעה זו';

$lang['Message_sent'] = 'הודעתך נשלחה';

$lang['Click_return_inbox'] = 'לחץ %sכאן%s כדי לחזור לתיבת הדואר הנכנס שלך';
$lang['Click_return_index'] = 'לחץ %sכאן%s כדי לחזור לאינדקס';

$lang['Send_a_new_message'] = 'שלח הודעה פרטית חדשה';
$lang['Send_a_reply'] = 'הגב להודעה פרטית';
$lang['Edit_message'] = 'ערוך הודעה פרטית';

$lang['Notification_subject'] = 'הודעה פרטית חדשה הגיעה';

$lang['Find_username'] = 'מצא שם משתמש';
$lang['Find'] = 'מצא';
$lang['No_match'] = 'לא נמצאו תואמים';

$lang['No_post_id'] = 'לא צויין ID ההודעה';
$lang['No_such_folder'] = 'לא קיימת תיקייה כזו';
$lang['No_folder'] = 'לא צויינה תיקייה';

$lang['Mark_all'] = 'סמן הכל';
$lang['Unmark_all'] = 'בטל את כל הסימונים';

$lang['Confirm_delete_pm'] = 'אתה בטוח שברצונך למחוק הודעה זו?';
$lang['Confirm_delete_pms'] = 'אתה בטוח שברצונך למחוק הודעות אלו?';

$lang['Inbox_size'] = 'תיבת הדואר הנכנס שלך %d%% מלאה'; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = 'תיבת הדואר הנשלח שלך %d%% מלאה'; 
$lang['Savebox_size'] = 'תיבת הדואר השמור שלך %d%% מלאה'; 

$lang['Click_view_privmsg'] = 'לחץ %sכאן%s כדי לבקר בתיבת הדואר הנכנס שלך';


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = 'צפייה בפרופיל :: %s'; // %s is username 
$lang['About_user'] = 'הכל אודות %s'; // %s is username

$lang['Preferences'] = 'עדיפויות';
$lang['Items_required'] = 'פריטים המסומנים ב * דרושים אם לא צויין אחרת';
$lang['Registration_info'] = 'מידע רישום';
$lang['Profile_info'] = 'מידע פרופיל';
$lang['Profile_info_warn'] = 'מידע זה יהיה זמין לציבור';
$lang['Avatar_panel'] = 'לוח ניהול סימלונים';
$lang['Avatar_gallery'] = 'גלריית סימלונים';

$lang['Website'] = 'אתר אינטרנט';
$lang['Location'] = 'מיקום';
$lang['Contact'] = 'קשר';
$lang['Email_address'] = 'כתובת דואר אלקטרוני';
$lang['Email'] = 'דואר אלקטרוני';
$lang['Send_private_message'] = 'שלח הודעה פרטית';
$lang['Hidden_email'] = '[ חבוי ]';
$lang['Search_user_posts'] = 'מצא את כל ההודעות של %s';
$lang['Interests'] = 'תחביבים';
$lang['Occupation'] = 'עיסוק'; 
$lang['Poster_rank'] = 'דרגת מפרסם';

$lang['Total_posts'] = 'סך-כל ההודעות';
$lang['User_post_pct_stats'] = '%.2f%% מסך-הכל'; // 1.25% of total
$lang['User_post_day_stats'] = '%.2f הודעות ליום'; // 1.5 posts per day
$lang['Search_user_posts'] = 'מצא את כל ההודעות של %s'; // Find all posts by username

$lang['No_user_id_specified'] = 'מצטער, אך משתמש זה אינו קיים';
$lang['Wrong_Profile'] = 'אינך יכול לערוך פרופיל שאינו שלך.';

$lang['Only_one_avatar'] = 'רק סוג אחד של סימלון יכול להבחר';
$lang['File_no_data'] = 'הקובץ בכתובת שסיפקת לא מכיל מידע';
$lang['No_connection_URL'] = 'לא ניתן לבצע קשר עם הכתובת שסיפקת';
$lang['Incomplete_URL'] = 'הכתובת שסיפקת אינה גמורה';
$lang['Wrong_remote_avatar_format'] = 'הכתובת של הסמלון המרוחק אינה חוקית';
$lang['No_send_account_inactive'] = 'מצטער, אך לא ניתן לקבל את סיסמתך כי חשבונך לא פעיל. אנא צור קשר עם מנהל הפורום לעוד מידע';

$lang['Always_smile'] = 'תמיד אפשר סמיילים';
$lang['Always_html'] = 'תמיד אפשר HTML';
$lang['Always_bbcode'] = 'תמיד אפשר BBCode';
$lang['Always_add_sig'] = 'תמיד צרף את חתימתי';
$lang['Always_notify'] = 'תמיד הודע לי על תגובות';
$lang['Always_notify_explain'] = 'שולח לך דוא\"ל כשמישהו מגיב לנושא שפירסמת בו הודעה. ניתן לשנות זאת גם בזמן פרסום ההודעה';

$lang['Board_style'] = 'סגנון הלוח';
$lang['Board_lang'] = 'שפת הלוח';
$lang['No_themes'] = 'אין תמות במסד הנתונים';
$lang['Timezone'] = 'אזור זמן';
$lang['Date_format'] = 'סגנון תאריך';
$lang['Date_format_explain'] = 'התחביר זהה לזה של פונקציית PHP <a href=\"http://www.php.net/date\" target=\"_other\">date()</a>';
$lang['Signature'] = 'חתימה';
$lang['Signature_explain'] = 'זהו בלוק של טקסט שיכול להתווסף להודעות שאתה כותב. ישנה הגבלה ל %d תוים';
$lang['Public_view_email'] = 'תמיד הצג את כתובת הדוא\"ל שלי';

$lang['Current_password'] = 'סיסמה נוכחית';
$lang['New_password'] = 'סיסמה חדשה';
$lang['Confirm_password'] = 'אישור סיסמה';
$lang['Confirm_password_explain'] = 'אתה חייב לספק את סיסמתך הנוכחית אם ברצונך לערוך או לשנות את כתובת הדוא\"ל שלך';
$lang['password_if_changed'] = 'אתה צריך לספק סיסמה רק אם ברצונך לשנות אותה';
$lang['password_confirm_if_changed'] = 'אתה צריך לאשר את סיסמתך רק אם ברצונך לשנות אותה';

$lang['Avatar'] = 'סימלון';
$lang['Avatar_explain'] = 'מציג תמונה גרפית קטנה מתחת לפרטים בהודעות. רק תמונה אחת יכולה להיות מוצגת בזמן נתון, העובי שלה לא יכול לעלות על %d פיקסלים, והגובה לא יותר מ %d פיקסלים וגודל הקובץ לא יותר מ %dkB.';
$lang['Upload_Avatar_URL'] = 'טען סימלון מכתובת אינטרנט';
$lang['Upload_Avatar_URL_explain'] = 'הזן את כתובת האינטרנט המכילה את תמונת הסימלון, היא תועתק לאתר זה.';
$lang['Pick_local_Avatar'] = 'בחר סימלון מהגלרייה';
$lang['Link_remote_Avatar'] = 'קשר לסימלון מחוץ לאתר';
$lang['Link_remote_Avatar_explain'] = 'הזן את כתובת האינטרנט שמכילה את המיקום של הסימלון שאליו אתה רוצה לקשר.';
$lang['Avatar_URL'] = 'כתובת אינטרנט של תמונת סימלון';
$lang['Select_from_gallery'] = 'בחר סימלון מהגלריה';
$lang['View_avatar_gallery'] = 'הצג את הגלריה';

$lang['Select_avatar'] = 'בחר סימלון';
$lang['Return_profile'] = 'בטל סימלון';
$lang['Select_category'] = 'בחר קטגוריה';

$lang['Delete_Image'] = 'מחק תמונה';
$lang['Current_Image'] = 'תמונה נוכחית';

$lang['Notify_on_privmsg'] = 'הודע על הודעה פרטית חדשה';
$lang['Popup_on_privmsg'] = 'הקפץ חלון בהודעה פרטית חדשה'; 
$lang['Popup_on_privmsg_explain'] = 'מספר תבניות עשויות לפתוח חלון המודיע לך כאשר הודעה פרטית חדשה מגיעה'; 
$lang['Hide_user'] = 'הסתר את מצב החיבור שלך';

$lang['Profile_updated'] = 'הפרופיל שלך עודכן';
$lang['Profile_updated_inactive'] = 'הפרופיל שלך עודכן, אך שינית את הפרטים הפנימיים שלך למרות שהחשבון שלך עכשיו לא פעיל. בדוק את הדוא\"ל שלך כדי לבדוק איך להפעיל את החשבון שלך, או אם דרוש אישור מנהל חכה שמנהל יפעיל לך את החשבון';

$lang['Password_mismatch'] = 'הסיסמאות שהזנת אינן תואמות';
$lang['Current_password_mismatch'] = 'הסיסמה הנוכחית שציינת אינה תואמת את זו שבמסד הנתונים';
$lang['Password_long'] = 'על הסיסמה שלך להיות פחות מ 32 תוים';
$lang['Username_taken'] = 'מצטער, אך שם משתמש זה כבר נלקח';
$lang['Username_invalid'] = 'מצטער, אך שם משתמש זה מכיל תו לא חוקי כגון \"';
$lang['Username_disallowed'] = 'מצטער, אך שם משתמש זה אינו מורשה';
$lang['Email_taken'] = 'מצטער, אך כתובת הדוא\"ל שסיפקת כבר שייכת למשתמש אחר';
$lang['Email_banned'] = 'מצטער, אך כתובת דוא\"ל זו גורשה';
$lang['Email_invalid'] = 'מצטער, אך כתובת דוא\"ל זו אינה תיקנית';
$lang['Signature_too_long'] = 'החתימה שלך ארוכה מידי';
$lang['Fields_empty'] = 'יש למלא את כל השדות הדרושים';
$lang['Avatar_filetype'] = 'סוג הקובץ של הסמלון חיב להיות .jpg, .gif או .png';
$lang['Avatar_filesize'] = 'גודל הקובץ של תמונת הסימלון חייב להיות מתחת ל %d kB'; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = 'הסימלון חייב להיות ברוחב הקטן מ %d פיקסלים ובגובה הקטן מ %d פיקסלים'; 

$lang['Welcome_subject'] = 'ברוכים הבאים לפורומי %s'; // Welcome to my.com forums
$lang['New_account_subject'] = 'חשבון משתמש חדש';
$lang['Account_activated_subject'] = 'החשבון הופעל';

$lang['Account_added'] = 'תודה לך שנרשמת, החשבון שלך נוצר. אתה יכול להתחבר עכשיו עם שם המשתמש והסיסמה שלך';
$lang['Account_inactive'] = 'החשבון שלך נוצר. עם זאת, פורום זה דורש הפעלת חשבונות, מפתח הפעלה נשלח לכתובת הדוא\"ל שסיפקת. אנא בדוק דוא\"ל לעוד מידע';
$lang['Account_inactive_admin'] = 'החשבון שלך נוצר. עם זאת, פורום זה דורש הפעלת חשבונות על-ידי מנהל. An דוא\"ל נשלח להם ואתה תדע כאשר החשבון שלך יהיה פעיל';
$lang['Account_active'] = 'החשבון שלך הופעל עכשיו. תודה שנרשמת';
$lang['Account_active_admin'] = 'החשבון הופעל עכשיו';
$lang['Reactivate'] = 'הפעל מחדש את החשבון שלך!';
$lang['Already_activated'] = 'כבר הפעלת את החשבון שלך';
$lang['COPPA'] = 'החשבון שלך נוצר אך לא אושר, אנא בדוק דוא\"ל לעוד פרטים.';

$lang['Registration'] = 'תנאי הסכם ההרשמה';
$lang['Reg_agreement'] = 'While the administrators and moderators of this forum will attempt to remove or edit any generally objectionable material as quickly as possible, it is impossible to review every message. Therefore you acknowledge that all posts made to these forums express the views and opinions of the author and not the administrators, moderators or webmaster (except for posts by these people) and hence will not be held liable.<br /><br />You agree not to post any abusive, obscene, vulgar, slanderous, hateful, threatening, sexually-orientated or any other material that may violate any applicable laws. Doing so may lead to you being immediately and permanently banned (and your service provider being informed). The IP address of all posts is recorded to aid in enforcing these conditions. You agree that the webmaster, administrator and moderators of this forum have the right to remove, edit, move or close any topic at any time should they see fit. As a user you agree to any information you	have entered above being stored in a database. While this information will not be disclosed to any third party without your consent the webmaster, administrator and moderators cannot be held responsible for any hacking attempt that may lead to the data being compromised.<br /><br />This forum system uses cookies to store information on your local computer. These cookies do not contain any of the information you have entered above, they serve only to improve your viewing pleasure. The email address is used only for confirming your registration details and password (and for sending new passwords should you forget your current one).<br /><br />By clicking Register below you agree to be bound by these conditions.';

$lang['Agree_under_13'] = 'אני מסכים לתנאים אלו ואני <b>מתחת</b> לגיל 13';
$lang['Agree_over_13'] = 'אני מסכים לתנאים אלו ואני <b>מעל</b> גיל 13';
$lang['Agree_not'] = 'אינני מסכים לתנאים אלו';

$lang['Wrong_activation'] = 'מפתח ההפעלה שסיפקת אינו תואם לזה שבמסד הנתונים';
$lang['Send_password'] = 'שלח לי סיסמה חדשה'; 
$lang['Password_updated'] = 'סיסמה חדשה נוצרה, בדוק את הדוא\"ל שלך לפרטים על איך להפעיל אותה';
$lang['No_email_match'] = 'כתובת הדוא\"ל שסיפקת אינה תואמות את זה שסופקה עבור שם המשתמש הזה';
$lang['New_password_activation'] = 'הפעלת סיסמה חדשה';
$lang['Password_activated'] = 'החשבון שלך הופעל מחדש. כדי להתחבר השתמש בסיסמה שסופקה לך בדוא\"ל שקיבלת';

$lang['Send_email_msg'] = 'שלח הודעה בדואר אלקטרוני';
$lang['No_user_specified'] = 'לא צויין משתמש';
$lang['User_prevent_email'] = 'משתמש זה אינו מעוניין לקבל דוא\"ל. נסה לשלוח הודעה פרטית';
$lang['User_not_exist'] = 'משתמש זה אינו קיים';
$lang['CC_email'] = 'שלח עותק מדוא\"ל זה לעצמך';
$lang['Email_message_desc'] = 'הודעה זו תשלח כטקסט פשוט, אל תכליל HTML או BBCode. כתובת החזרה למכתב זה יהיה כתובת הדוא\"ל שלך.';
$lang['Flood_email_limit'] = 'אינך יכול לשלוח עוד דוא\"ל ברגע זה, נסה מאוחר יותר';
$lang['Recipient'] = 'נמען';
$lang['Email_sent'] = 'הדוא\"ל נשלח';
$lang['Send_email'] = 'שלח דוא\"ל';
$lang['Empty_subject_email'] = 'אתה חייב לציין נושא לדוא\"ל';
$lang['Empty_message_email'] = 'אתה חייב לכתוב תוכן לדוא\"ל';


//
// Memberslist
//
$lang['Select_sort_method'] = 'בחר שיטת מיון';
$lang['Sort'] = 'מיין';
$lang['Sort_Top_Ten'] = 'עשרת הכותבים הגדולים';
$lang['Sort_Joined'] = 'תאריך הצטרפות';
$lang['Sort_Username'] = 'שם משתמש';
$lang['Sort_Location'] = 'מיקום';
$lang['Sort_Posts'] = 'סך-הכל הודעות';
$lang['Sort_Email'] = 'דוא\"ל';
$lang['Sort_Website'] = 'אתר אינטרנט';
$lang['Sort_Ascending'] = 'סדר עולה';
$lang['Sort_Descending'] = 'סדר יורד';
$lang['Order'] = 'סדר';


//
// Group control panel
//
$lang['Group_Control_Panel'] = 'לוח ניהול קבוצה';
$lang['Group_member_details'] = 'פרטי חברות בקבוצה';
$lang['Group_member_join'] = 'הצטרף לקבוצה';

$lang['Group_Information'] = 'מידע על הקבוצה';
$lang['Group_name'] = 'שם הקבוצה';
$lang['Group_description'] = 'תאור הקבוצה';
$lang['Group_membership'] = 'חברות בקבוצה';
$lang['Group_Members'] = 'חברי הקבוצה';
$lang['Group_Moderator'] = 'אחראי הקבוצה';
$lang['Pending_members'] = 'חברים ממתינים';

$lang['Group_type'] = 'סוג הקבוצה';
$lang['Group_open'] = 'קבוצה פתוחה';
$lang['Group_closed'] = 'קבוצה סגורה';
$lang['Group_hidden'] = 'קבוצה נסתרת';

$lang['Current_memberships'] = 'חברויות נוכחיות';
$lang['Non_member_groups'] = 'קבוצות שאינך חבר בהן';
$lang['Memberships_pending'] = 'חברויות ממתינות';

$lang['No_groups_exist'] = 'לא קיימות קבוצות';
$lang['Group_not_exist'] = 'קבוצת משתמשים זו אינה קיימת';

$lang['Join_group'] = 'הצטרף לקבוצה';
$lang['No_group_members'] = 'בקבוצה זו אין חברים';
$lang['Group_hidden_members'] = 'קבוצה זו נסתרת, אינך יכול לראות את חבריה';
$lang['No_pending_group_members'] = 'לקבוצה זו אין חברים ממתינים';
$lang['Group_joined'] = 'נרשמת בהצלחה לקבוצה זו<br />ייודע לך כאשר הרישום יאושר על-ידי אחראי הקבוצה';
$lang['Group_request'] = 'בקשה להצטרפות לקבוצה שלך נעשתה';
$lang['Group_approved'] = 'בקשתך אושרה';
$lang['Group_added'] = 'נוספת לקבוצת המשתמשים הזו'; 
$lang['Already_member_group'] = 'אתה כבר חבר בקבוצה זו';
$lang['User_is_member_group'] = 'המשתמש כבר חבר בקבוצה זו';
$lang['Group_type_updated'] = 'סוג הקבוצה עודכן בהצלחה';

$lang['Could_not_add_user'] = 'המשתמש שבחרת אינו קיים';
$lang['Could_not_anon_user'] = 'אתה לא יכול לעשות את אנונימי חבר בקבוצה';

$lang['Confirm_unsub'] = 'אתה בטוח שברצונך לבטל את הרישום מקבוצה זו?';
$lang['Confirm_unsub_pending'] = 'רישומך לקבוצה זו טרם אושר, אתה בטוח שברצונך לבטל את הרישום?';

$lang['Unsub_success'] = 'ביטלת את הרישום מקבוצה זו.';

$lang['Approve_selected'] = 'הסכמה נבחרה';
$lang['Deny_selected'] = 'התנגדות נבחרה';
$lang['Not_logged_in'] = 'אתה חייב להיות מחובר כדי להצטרף לקבוצה.';
$lang['Remove_selected'] = 'הסר בחירה';
$lang['Add_member'] = 'הוסף חבר';
$lang['Not_group_moderator'] = 'אתה לא האחראי על קבוצה זו ולכן אינך יכול לבצע פעולה זו.';

$lang['Login_to_join'] = 'התחבר כדי להצטרף או לנהל קבוצת חברים';
$lang['This_open_group'] = 'זוהי קבוצה פתוחה, לחץ כדי לבקש חברות';
$lang['This_closed_group'] = 'זוהי קבוצה סגורה, לא יתקבלו עוד חברים';
$lang['This_hidden_group'] = 'זוהי קבוצה נסתרת, הוספת משתמשים אוטמטית אינה מורשית';
$lang['Member_this_group'] = 'אתה חבר בקבוצה זו';
$lang['Pending_this_group'] = 'חברותך בקבוצה זו בהמתנה';
$lang['Are_group_moderator'] = 'אתה אחראי הקבוצה';
$lang['None'] = 'אין';

$lang['Subscribe'] = 'הרשם';
$lang['Unsubscribe'] = 'בטל רישום';
$lang['View_Information'] = 'ראה מידע';


//
// Search
//
$lang['Search_query'] = 'שאילתת חיפוש';
$lang['Search_options'] = 'אפשרויות חיפוש';

$lang['Search_keywords'] = 'חפש מילות מפתח';
$lang['Search_keywords_explain'] = 'אתה יכול להשתמש ב <u>AND</u> כדי להגדיר מילים שחייבים להיות בתוצאות, <u>OR</u> כדי להגדיר מילים שיכולים להיות בתוצאות ו <u>NOT</u> כדי להגדיר מילים שלא צריכות להיות בתוצאות. השתמש ב * כתו מפתח לחלקי תוצאות';
$lang['Search_author'] = 'חפש מחבר';
$lang['Search_author_explain'] = 'השתמש ב * כתו מפתח לחלקי תוצאות';

$lang['Search_for_any'] = 'חפש עבור כל אחד מהמושגים או השתמש בשאילתה כפי שהוקלדה';
$lang['Search_for_all'] = 'חפש עבור כל המושגים';
$lang['Search_title_msg'] = 'חפש בכותרות הנושאים ובגוף ההודעה';
$lang['Search_msg_only'] = 'חפש בגוף ההודעה בלבד';

$lang['Return_first'] = 'החזר את'; // followed by xxx characters in a select box
$lang['characters_posts'] = 'התוים הראשונים של ההודעות';

$lang['Search_previous'] = 'חפש מלפני'; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = 'מיין לפי';
$lang['Sort_Time'] = 'זמן פרסום';
$lang['Sort_Post_Subject'] = 'נושא הודעה';
$lang['Sort_Topic_Title'] = 'כותרת נושא';
$lang['Sort_Author'] = 'מחבר';
$lang['Sort_Forum'] = 'פורום';

$lang['Display_results'] = 'הצג תוצאות כ';
$lang['All_available'] = 'כל האפשרויות';
$lang['No_searchable_forums'] = 'אין לך הרשאות לחפש בפורמים על אתר זה';

$lang['No_search_match'] = 'אף נושאים או ההודעות לא תאמו את קריטריון החיפוש שלך';
$lang['Found_search_match'] = 'החיפוש מצא תוצאה אחת'; // eg. Search found 1 match
$lang['Found_search_matches'] = 'החיפוש מצא %d תוצאות'; // eg. Search found 24 matches

$lang['Close_window'] = 'סגור חלון';


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = 'מצטער, אך רק %s יכולים לפרסם הכרזות בפורום זה';
$lang['Sorry_auth_sticky'] = 'מצטער, אך רק %s יכולים לפרסם הודעות דביקות בפורום זה'; 
$lang['Sorry_auth_read'] = 'מצטער, אך רק %s יכולים לקרוא נושאים בפורום זה'; 
$lang['Sorry_auth_post'] = 'מצטער, אך רק %s יכולים לפרסם נושאים בפורום זה'; 
$lang['Sorry_auth_reply'] = 'מצטער, אך רק %s יכולים להגיב להודעות בפורום זה'; 
$lang['Sorry_auth_edit'] = 'מצטער, אך רק %s יכולים לערוך הודעות בפורום זה'; 
$lang['Sorry_auth_delete'] = 'מצטער, אך רק %s יכולים למחוק הודעות בפורום זה'; 
$lang['Sorry_auth_vote'] = 'מצטער, אך רק %s יכולים להצביע לסקרים בפורום זה'; 

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = '<b>משתמשים אנונימיים</b>';
$lang['Auth_Registered_Users'] = '<b>משתמשים רשומים</b>';
$lang['Auth_Users_granted_access'] = '<b>משתמשים שניתנה להם גישה מיוחדת</b>';
$lang['Auth_Moderators'] = '<b>אחראים</b>';
$lang['Auth_Administrators'] = '<b>מנהלים</b>';

$lang['Not_Moderator'] = 'אתה לא אחראי על פורום זה';
$lang['Not_Authorised'] = 'לא מאושר';

$lang['You_been_banned'] = 'גורשת מפורום זה<br />אנא צור קשר עם מנהל האתר או מנהל הלוח לעוד מידע';


//
// Viewonline
//
$lang['Reg_users_zero_online'] = 'ישנם 0 משתמשים רשומים ו'; // There ae 5 Registered and
$lang['Reg_users_online'] = 'ישנם %d משתמשים רשומים ו'; // There ae 5 Registered and
$lang['Reg_user_online'] = 'ישנו משתמש רשום אחד ו'; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = '-0 משתמשים חבויים מחוברים'; // 6 Hidden users online
$lang['Hidden_users_online'] = '-%d משתמשים חבויים מחוברים'; // 6 Hidden users online
$lang['Hidden_user_online'] = 'משתמש חבוי אחד מחובר'; // 6 Hidden users online
$lang['Guest_users_online'] = 'ישנם %d משתמשים אורחים מחוברים'; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = 'ישנם 0 משתמשים אורחים מחוברים'; // There are 10 Guest users online
$lang['Guest_user_online'] = 'ישנו משתמש אורח אחד מחובר'; // There is 1 Guest user online
$lang['No_users_browsing'] = 'אין אנשים הגולשים בפורום ברגע זה';

$lang['Online_explain'] = 'מידע זה מבוסס על המשתמשים הפעילים במשך חמשת הדקות האחרונות';

$lang['Forum_Location'] = 'מיקום הפורום';
$lang['Last_updated'] = 'עודכן לאחרונה';

$lang['Forum_index'] = 'אינדקס הפורומים';
$lang['Logging_on'] = 'מתחבר';
$lang['Posting_message'] = 'מפרסם הודעה';
$lang['Searching_forums'] = 'מחפש בפורומים';
$lang['Viewing_profile'] = 'רואה פרופיל';
$lang['Viewing_online'] = 'רואה מי מחובר';
$lang['Viewing_member_list'] = 'רואה את רשימת החברים';
$lang['Viewing_priv_msgs'] = 'רואה הודעות פרטיות';
$lang['Viewing_FAQ'] = 'רואה FAQ';


//
// Moderator Control Panel
//
$lang['Mod_CP'] = 'לוח בקרה לאחראים';
$lang['Mod_CP_explain'] = 'בעזרת הטופס מטה אתה יכול לבצע עבודות השגחה מאסיביות על פורום זה. אתה יכול לנעול, לפתוח, להזיז או למחוק כל מספר של נושאים.';

$lang['Select'] = 'בחר';
$lang['Delete'] = 'מחק';
$lang['Move'] = 'הזז';
$lang['Lock'] = 'נעל';
$lang['Unlock'] = 'פתח';

$lang['Topics_Removed'] = 'הנושאים הנבחרים הוסרו בהצלחה ממסד הנתונים.';
$lang['Topics_Locked'] = 'הנושאים הנבחרים ננעלו';
$lang['Topics_Moved'] = 'הנושאים הנבחרים הוזזו';
$lang['Topics_Unlocked'] = 'הנושאים הנבחרים נפתחו';
$lang['No_Topics_Moved'] = 'אף נושא לא הוזז';

$lang['Confirm_delete_topic'] = 'אתה בטוח שברצונך להסיר את הנושא/ים שנבחר/ו?';
$lang['Confirm_lock_topic'] = 'אתה בטוח שברצונך לנעול את הנושא/ים שנבחר/ו?';
$lang['Confirm_unlock_topic'] = 'אתה בטוח שברצונך לפתוח את הנושא/ים שנבחר/ו?';
$lang['Confirm_move_topic'] = 'אתה בטוח שברצונך להזיז את הנושא/ים שנבחר/ו?';

$lang['Move_to_forum'] = 'הזז לפורום';
$lang['Leave_shadow_topic'] = 'השאר נושא צל בפורום הישן.';

$lang['Split_Topic'] = 'לוח בקרה לחילוק נושאים';
$lang['Split_Topic_explain'] = 'על-ידי שימוש בטופס מטה תוכל לחלק נושא לשניים, או על-ידי בחירה אינדיבידואלית של הודעות או על-ידי חלוקה מהודעה נבחרת';
$lang['Split_title'] = 'כותרת הנושא החדש';
$lang['Split_forum'] = 'פורום לנושא החדש';
$lang['Split_posts'] = 'חלק הודעות נבחרות';
$lang['Split_after'] = 'חלק מהודעה נבחרת';
$lang['Topic_split'] = 'הנושא הנבחר חולק בהצלחה';

$lang['Too_many_error'] = 'בחרת יותר מידי הודעות. אתה יכול להחור רק הודעה אחת שאחריה יחולק הנושא!';

$lang['None_selected'] = 'לא בחרת אף נושאים כדי לבצע עליהם את הפעולה. אנא חזור ובחר לפחות אחת.';
$lang['New_forum'] = 'פורום חדש';

$lang['This_posts_IP'] = 'IP להודעה זו';
$lang['Other_IP_this_user'] = 'כתובות IP אחרות שמשתמש זה פרסם מהם';
$lang['Users_this_IP'] = 'משתמשים המפרסמים מכתובת IP זו';
$lang['IP_info'] = 'מידע IP';
$lang['Lookup_IP'] = 'עקוב אחר IP';


//
// Timezones ... for display on each page
//
$lang['All_times'] = 'כל הזמנים הם %s'; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = 'GMT - 12 שעות';
$lang['-11'] = 'GMT - 11 שעות';
$lang['-10'] = 'GMT - 10 שעות';
$lang['-9'] = 'GMT - 9 שעות';
$lang['-8'] = 'GMT - 8 שעות';
$lang['-7'] = 'GMT - 7 שעות';
$lang['-6'] = 'GMT - 6 שעות';
$lang['-5'] = 'GMT - 5 שעות';
$lang['-4'] = 'GMT - 4 שעות';
$lang['-3.5'] = 'GMT - 3.5 Hours';
$lang['-3'] = 'GMT - 3 שעות';
$lang['-2'] = 'GMT - 2 שעות';
$lang['-1'] = 'GMT - שעה אחת';
$lang['0'] = 'GMT';
$lang['1'] = 'GMT + שעה אחת';
$lang['2'] = 'GMT + 2 שעות';
$lang['3'] = 'GMT + 3 שעות';
$lang['3.5'] = 'GMT + 3.5 Hours';
$lang['4'] = 'GMT + 4 שעות';
$lang['4.5'] = 'GMT + 4.5 Hours';
$lang['5'] = 'GMT + 5 שעות';
$lang['5.5'] = 'GMT + 5.5 Hours';
$lang['6'] = 'GMT + 6 שעות';
$lang['6.5'] = 'GMT + 6.5 Hours';
$lang['7'] = 'GMT + 7 שעות';
$lang['8'] = 'GMT + 8 שעות';
$lang['9'] = 'GMT + 9 שעות';
$lang['9.5'] = 'GMT + 9.5 Hours';
$lang['10'] = 'GMT + 10 שעות';
$lang['11'] = 'GMT + 11 שעות';
$lang['12'] = 'GMT + 12 שעות';

// These are displayed in the timezone select box
$lang['tz']['-12'] = 'GMT - 12 שעות';
$lang['tz']['-11'] = 'GMT - 11 שעות';
$lang['tz']['-10'] = 'GMT - 10 שעות';
$lang['tz']['-9'] = 'GMT - 9 שעות';
$lang['tz']['-8'] = 'GMT - 8 שעות';
$lang['tz']['-7'] = 'GMT - 7 שעות';
$lang['tz']['-6'] = 'GMT - 6 שעות';
$lang['tz']['-5'] = 'GMT - 5 שעות';
$lang['tz']['-4'] = 'GMT - 4 שעות';
$lang['tz']['-3.5'] = 'GMT - 3.5 שעות';
$lang['tz']['-3'] = 'GMT - 3 שעות';
$lang['tz']['-2'] = 'GMT - 2 שעות';
$lang['tz']['-1'] = 'GMT - שעה אחת';
$lang['tz']['0'] = 'GMT';
$lang['tz']['1'] = 'GMT + שעה אחת';
$lang['tz']['2'] = 'GMT + 2 שעות';
$lang['tz']['3'] = 'GMT + 3 שעות';
$lang['tz']['3.5'] = 'GMT + 3.5 שעות';
$lang['tz']['4'] = 'GMT + 4 שעות';
$lang['tz']['4.5'] = 'GMT + 4.5 שעות';
$lang['tz']['5'] = 'GMT + 5 שעות';
$lang['tz']['5.5'] = 'GMT + 5.5 שעות';
$lang['tz']['6'] = 'GMT + 6 שעות';
$lang['tz']['6.5'] = 'GMT + 6.5 שעות';
$lang['tz']['7'] = 'GMT + 7 שעות';
$lang['tz']['8'] = 'GMT + 8 שעות';
$lang['tz']['9'] = 'GMT + 9 שעות';
$lang['tz']['9.5'] = 'GMT + 9.5 שעות';
$lang['tz']['10'] = 'GMT + 10 שעות';
$lang['tz']['11'] = 'GMT + 11 שעות';
$lang['tz']['12'] = 'GMT + 12 שעות';

$lang['datetime']['Sunday'] = 'יום ראשון';
$lang['datetime']['Monday'] = 'יום שני';
$lang['datetime']['Tuesday'] = 'יום שלישי';
$lang['datetime']['Wednesday'] = 'יום רביעי';
$lang['datetime']['Thursday'] = 'יום חמישי';
$lang['datetime']['Friday'] = 'יום שישי';
$lang['datetime']['Saturday'] = 'יום שבת';
$lang['datetime']['Sun'] = 'ראשון';
$lang['datetime']['Mon'] = 'שני';
$lang['datetime']['Tue'] = 'שלישי';
$lang['datetime']['Wed'] = 'רביעי';
$lang['datetime']['Thu'] = 'חמישי';
$lang['datetime']['Fri'] = 'שישי';
$lang['datetime']['Sat'] = 'שבת';
$lang['datetime']['January'] = 'ינואר';
$lang['datetime']['February'] = 'פברואר';
$lang['datetime']['March'] = 'מרץ';
$lang['datetime']['April'] = 'אפריל';
$lang['datetime']['May'] = 'מאי';
$lang['datetime']['June'] = 'יוני';
$lang['datetime']['July'] = 'יולי';
$lang['datetime']['August'] = 'אוגוסט';
$lang['datetime']['September'] = 'ספטמבר';
$lang['datetime']['October'] = 'אוקטובר';
$lang['datetime']['November'] = 'נובמבר';
$lang['datetime']['December'] = 'דצמבר';
$lang['datetime']['Jan'] = 'ינו\'';
$lang['datetime']['Feb'] = 'פבר\'';
$lang['datetime']['Mar'] = 'מרץ';
$lang['datetime']['Apr'] = 'אפר\'';
$lang['datetime']['May'] = 'מאי';
$lang['datetime']['Jun'] = 'יונ\'';
$lang['datetime']['Jul'] = 'יול\'';
$lang['datetime']['Aug'] = 'אוג\'';
$lang['datetime']['Sep'] = 'ספט\'';
$lang['datetime']['Oct'] = 'אוק\'';
$lang['datetime']['Nov'] = 'נוב\'';
$lang['datetime']['Dec'] = 'דצמ\'';

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = 'מידע';
$lang['Critical_Information'] = 'מידע קריטי';

$lang['General_Error'] = 'שגיאה כללית';
$lang['Critical_Error'] = 'שגיאה קריטית';
$lang['An_error_occured'] = 'אראה שגיאה';
$lang['A_critical_error'] = 'אראה שגיאה קריטית';

//
// That's all Folks!
// -------------------------------------------------

?>