<?php

/***************************************************************************
 *                            lang_admin.php [Hebrew]
 *                              -------------------
 *     begin                : Thu Jul 4 2002
 *     copyright            : (C) 2002 Gil Osher
 *     email                : dolfin@rpg.org.il
 *
 *     $Id: lang_admin.php,v 1.35.2.2 2002/07/04 16:45:21 dolfin Exp $
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
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = 'ניהול כללי';
$lang['Users'] = 'ניהול משתמשים';
$lang['Groups'] = 'ניהול קבוצות';
$lang['Forums'] = 'ניהול פורום';
$lang['Styles'] = 'ניהול ססגונות';

$lang['Configuration'] = 'הגדרות';
$lang['Permissions'] = 'הרשאות';
$lang['Manage'] = 'ארגון';
$lang['Disallow'] = 'מנע שמות';
$lang['Prune'] = 'Pruning';
$lang['Mass_Email'] = 'Mass Email';
$lang['Ranks'] = 'דירוגים';
$lang['Smilies'] = 'סמיילים';
$lang['Ban_Management'] = 'בקרת גירושים';
$lang['Word_Censor'] = 'סינון מילים';
$lang['Export'] = 'יצא';
$lang['Create_new'] = 'צור';
$lang['Add_new'] = 'הוסף';
$lang['Backup_DB'] = 'גבה מסד נתונים';
$lang['Restore_DB'] = 'שחזר מסד נתונים';


//
// Index
//
$lang['Admin'] = 'ניהול';
$lang['Not_admin'] = 'אתה לא מורשה לנהל את הלוח הזה';
$lang['Welcome_phpBB'] = 'ברוך הבא ל phpBB';
$lang['Admin_intro'] = 'תודה שבחרת phpBB כפתרון הפורום שלך. מסך זה יציג 
בפניך סקירה מהירה על כל הסטטיסטיקות של הפורום שלך. תוכל לחזור לדף זה בכל עת 
על ידי לחיצה על
<u>Admin Tools</u>
בצד השמאלי. על מנת לחזור לאינדקס הראשי, לחץ על סמל ה-phpBB אשר מצוי גם הוא 
בצד השמאלי. שאר הקישוריות בצד השמאלי יאפשרו לך לשלוט בכל תחום של הפורום שלך, 
בכל מסך יהיו הוראות בנוגע לשימוש בכליו.';
$lang['Main_index'] = 'אינדקס הפורומים';
$lang['Forum_stats'] = 'סטטיסטיקות הפורומים';
$lang['Admin_Index'] = 'אינדקס ניהול';
$lang['Preview_forum'] = 'תצוגה מקדימה של פורום';

$lang['Click_return_admin_index'] = 'לחץ %sכאן%s כדי לחזור לאינדקס הניהול';

$lang['Statistic'] = 'סטטיסטי';
$lang['Value'] = 'ערך';
$lang['Number_posts'] = 'מספר הודעות';
$lang['Posts_per_day'] = 'הודעות ליום';
$lang['Number_topics'] = 'מספר נושאים';
$lang['Topics_per_day'] = 'נושאים ליום';
$lang['Number_users'] = 'מספר משתמשים';
$lang['Users_per_day'] = 'משתמשים ליום';
$lang['Board_started'] = 'הלוח התחיל';
$lang['Avatar_dir_size'] = 'גודל תיקיית הסימלונים';
$lang['Database_size'] = 'גודל מסד הנתונים';
$lang['Gzip_compression'] ='Gzip compression';
$lang['Not_available'] = 'לא קיים';

$lang['ON'] = 'פועל'; // This is for GZip compression
$lang['OFF'] = 'כבוי'; 


//
// DB Utils
//
$lang['Database_Utilities'] = 'עזרי מסד נתונים';

$lang['Restore'] = 'שחזר';
$lang['Backup'] = 'גבה';
$lang['Restore_explain'] = 'זה יבצע שחזור מלא של כל טבלאות ה-phpBB מקובץ 
שמור. אם השרת שלך תומך בכך, תוכל להעלות קבצי טקסט מקובצים בפורמט GZIP והוא 
יפתח בצורה אוטומטית.
<b>אזהרה</b> פעולה זו תכתוב על כל מידע קיים. פעולת השחזור עלולה לקחת זמן רב 
לצורך עיבוד נתונים - אנא אל תעבור מעמוד זה עד לסיום התהליך.';
$lang['Backup_explain'] = 'פעולה זו יוצרת גיבוי לכל סוגי המידע של phpBB. אם 
יש לך טבלאות שיצרת באותו מאגר נתונים של phpBB והיית מעוניין לגבות גם אותן, 
אנא הכנס את שמותיהן כאשר הן מופרדות בסימן הפסיק במקומות למטה. במידה והשרת 
שלך מאפשר זאת תוכל לקבץ בפורמט Gzip את הקובץ בכדי להקטין את גודלו לפני 
ההורדה.';

$lang['Backup_options'] = 'אפשרויות גיבוי';
$lang['Start_backup'] = 'התחל גיבוי';
$lang['Full_backup'] = 'גיבוי מלא';
$lang['Structure_backup'] = 'גבה מבנה בלבד';
$lang['Data_backup'] = 'גבה נתונים בלבד';
$lang['Additional_tables'] = 'טבלאות נוספות';
$lang['Gzip_compress'] = 'קובץ מכווץ ב Gzip';
$lang['Select_file'] = 'בחר קובץ';
$lang['Start_Restore'] = 'התחל שחזור';

$lang['Restore_success'] = 'מסד הנתונים שוחזר בהצלחה.<br /><br />הלוח שלך 
אמור
להיות במצב שהיה בו התבצע הגיבוי.';
$lang['Backup_download'] = 'ההורדה שלך תחל בקרוב, אנא המתן עד שהיא מתחילה';
$lang['Backups_not_supported'] = 'מצטער, אך גיבוי מסד הנתונים לא נתמך כרגע
במערכת מסד הנתונים שלך';

$lang['Restore_Error_uploading'] = 'שגיאה בטעינת קובץ הגיבוי';
$lang['Restore_Error_filename'] = 'בעיית שם קובץ, אנא נסה קובץ אחר';
$lang['Restore_Error_decompress'] = 'לא יכול לכווץ קובץ gzip, אנא טען קובץ 
טקסט
פשוט';
$lang['Restore_Error_no_file'] = 'אף קובץ לא נטען';


//
// Auth pages
//
$lang['Select_a_User'] = 'בחר משתמש';
$lang['Select_a_Group'] = 'בחר קבוצה';
$lang['Select_a_Forum'] = 'בחר פורום';
$lang['Auth_Control_User'] = 'בקרת הרשאות משתמש'; 
$lang['Auth_Control_Group'] = 'בקרת הרשאות קבוצה'; 
$lang['Auth_Control_Forum'] = 'בקרת הרשאות פורום'; 
$lang['Look_up_User'] = 'בדוק משתמש'; 
$lang['Look_up_Group'] = 'בדוק קבוצה'; 
$lang['Look_up_Forum'] = 'בדוק פורום'; 

$lang['Group_auth_explain'] = 'כאן תוכל לשנות את ההרשאות ומצב אחראי עבור כל 
קבוצת משתמשים. אל תשכח שגם עם שינוי הגדרות קבוצה - הגדרות משתמש פרטיות 
עלולות עדיין לאפשר למשתמש להכנס לפורומים, וכו\'. במידה וזה המצב, תקבל אזהרה 
על כך.';
$lang['User_auth_explain'] = 'כאן תוכל לשנות את ההרשאות וסטטוס האחראי עבור 
כל משתמש פרטי. זכור כי שינוי הרשאות משתמש לא ימנעו סופית ממשתמש להכנס לפורום 
אם הוא בצוי בקבוצה בעלת הרשאת כניסה. תקבל אזהרה במידה ודבר כזה יתרחש.';
$lang['Forum_auth_explain'] = 'כאן תוכל לשנות את דרגות ההרשאה של כל פורום. 
לרשותך תהליך פשוט ותהליך מתקדם לכך, כאשר התהליך המתקדם מציע שליטה נרחבת יותר 
בפעולת הפורום. זכור כי שינוי דרגת ההרשאה תשפיע על אילו משתמשים יכולים לבצע 
פעולות שונות במסגרתן';

$lang['Simple_mode'] = 'מצב פשוט';
$lang['Advanced_mode'] = 'מצב מתקדם';
$lang['Moderator_status'] = 'מצב אחראי';

$lang['Allowed_Access'] = 'גישה מורשית';
$lang['Disallowed_Access'] = 'גישה אסורה';
$lang['Is_Moderator'] = 'הוא אחראי';
$lang['Not_Moderator'] = 'לא אחראי';

$lang['Conflict_warning'] = 'אזהרת התנגשות הרשאות';
$lang['Conflict_access_userauth'] = 'למשתמש זה עדיין יש הרשאת כניסה לפורום 
זה באמצעות אישור קבוצת משתמשים. יתכן כי תרצה לשנות את הרשאות הקבוצה או להסיר 
את המשתמש  מהקבוצה בכדי למנוע באופן מלא את הרשאת הכניסה שלו. הקבוצות המתירות 
את הזכויות מצויינות בהמשך';
$lang['Conflict_mod_userauth'] = ' משתמש זה הינו עדיין אחראי לפורום מכיוון 
שהוא מצוי בקבוצת משתמשים מסויימת. יתכן כי תרצה לשנות את הרשאות הקבוצה או 
להסיר את המשתמש מקבוצה זו בדי למנוע ממנו לזכות במעמד של אחראי על הפורום. 
הקבוצות המתירות את הזכויות מצויינות בהמשך';

$lang['Conflict_access_groupauth'] = 'למשתמש (או משתמשים) זה יש עדיין הרשאת 
כניסה לפורום זה באמצעות הגדרות ההרשאה הפרטיות שלהם. יתכן ותרצה לשנות הגדרות 
אילו בכדי למנוע באופן מלא את הרשאת הכניסה שלהם. ההרשאות של משתמש זה מפורטות 
בהמשך.';
$lang['Conflict_mod_groupauth'] = 'למשתמש (או משתמשים) זה יש עדיין זכויות 
אחראי לפורום זה באמצעות הגדרות ההרשאה הפרטיות. יתכן ותרצה לשנות הגדרות אילו 
בכדי למחוק באופן מלא את סטטוס האחראי שלו. ההרשאות של משתמש זה מפורטות 
בהמשך.';

$lang['Public'] = 'ציבורי';
$lang['Private'] = 'פרטי';
$lang['Registered'] = 'רשום';
$lang['Administrators'] = 'מנהלים';
$lang['Hidden'] = 'חבוי';

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = 'הכל';
$lang['Forum_REG'] = 'רשומים';
$lang['Forum_PRIVATE'] = 'פרטי';
$lang['Forum_MOD'] = 'אחראי';
$lang['Forum_ADMIN'] = 'אדמין';

$lang['View'] = 'צפה';
$lang['Read'] = 'קרא';
$lang['Post'] = 'פרסם';
$lang['Reply'] = 'הגב';
$lang['Edit'] = 'ערוך';
$lang['Delete'] = 'מחק';
$lang['Sticky'] = 'דביק';
$lang['Announce'] = 'הודע'; 
$lang['Vote'] = 'הצבע';
$lang['Pollcreate'] = 'צור סקר';

$lang['Permissions'] = 'הרשאות';
$lang['Simple_Permission'] = 'הרשאה פשוטה';

$lang['User_Level'] = 'רמת משתמש'; 
$lang['Auth_User'] = 'משתמש';
$lang['Auth_Admin'] = 'מנהל';
$lang['Group_memberships'] = 'חברות בקבוצת משתמשים';
$lang['Usergroup_members'] = 'בקבוצה זו נמצאים החברים הבאים';

$lang['Forum_auth_updated'] = 'הרשאות פורום עודכנו';
$lang['User_auth_updated'] = 'הרשאות משתמש עודכנו';
$lang['Group_auth_updated'] = 'הרשאות קבוצה עודכנו';

$lang['Auth_updated'] = 'ההרשאות עודכנו';
$lang['Click_return_userauth'] = 'לחץ %sכאן%s כדי לחזור להרשאות משתמש';
$lang['Click_return_groupauth'] = 'לחץ %sכאן%s כדי לחזור להרשאות קבוצה';
$lang['Click_return_forumauth'] = 'לחץ %sכאן%s כדי לחזור להרשאות פורום';


//
// Banning
//
$lang['Ban_control'] = 'בקרת גירוש';
$lang['Ban_explain'] = 'כאן תוכל לשלוט בגירוש משתמשים. תוכל לגרש משתמש על 
ידי גירוש שם המשתמש שלו, או טווח של כתובת IP. שיטות אילו מונעות ממשתמש להגיע 
אפילו לדף האינדקס של הפורום שלך. בכדי למנוע ממשתמש להירשם מחדש תחת שם משתמש 
שונה, תוכל לחסום גם כתובת דוא\"ל. שים לב - חסימת דוא\"ל בלבד לא תמנע ממשמתש 
כלשהו להתחבר ולפרסם הודעות בפורום, עליך תחילה להשתמש באחת משתי השיטות 
שצויינו לעיל על מנת לגרש.';
$lang['Ban_explain_warn'] = 'אנא שים לב כי הכנסת טווח IP לגירוש לכך כי כל 
הכתובות מהכתובת הראשונה לאחרונה יגורשו מן הפורום. ממולץ לצמצם ככל הניתן את 
הטווח בכדי למנוע חסימה של משתמשים רצויים.';

$lang['Select_username'] = 'בחר שם משתמש';
$lang['Select_ip'] = 'בחר כתובת IP';
$lang['Select_email'] = 'בחר כתובת דוא\"ל';

$lang['Ban_username'] = 'גרש משתמש מסויים אחד או יותר';
$lang['Ban_username_explain'] = 'ביכולתך לגרש מספר רב של משתמשים בפעם אחת על 
ידי השילוב הנכון של העכבר והמקלדת עבור המחשב והדפדפן שלך.';

$lang['Ban_IP'] = 'גרש כתובת IP או hostname אחד או יותר';
$lang['IP_hostname'] = 'כתובות IP או hostnames';
$lang['Ban_IP_explain'] = 'לצורך גירוש כתובות IP ו-Hostnames רבים, הפרד אותם 
על ידי סימן הפסיק. לצורך סימון טווח של כתובות IP, הפרד בין הראשון לאחרון 
באמצעות הסימן מקף (-). לצורך השימוש ב-wildcard העזר ב-*';

$lang['Ban_email'] = 'גרש כתובת דוא\"ל אחת או יותר';
$lang['Ban_email_explain'] = 'על מנת לחסום יותר מכתובת דוא\"ל אחת, הפרד בין 
הכתובת עם סימן הפסיק. תוכל להעזר ב-wildcard כמקש *';

$lang['Unban_username'] = 'החזר משתמש מסויים אחד או יותר';
$lang['Unban_username_explain'] = 'ביכולתך להחזיר כמה משתמשים בו זמנית על 
ידי שימוש נכון בצירוף עכבר ומקלדת עבור המחשב והדפדפן שלך';

$lang['Unban_IP'] = 'החזר כתובת IP אחת או יותר';
$lang['Unban_IP_explain'] = 'ביכולתך להחזיר מספר כתובות IP בו זמנית על ידי 
שימוש נכון בצירוף עכבר ומקלדת עבור המחשב והדפדפן שלך';

$lang['Unban_email'] = 'החזר כתובת דוא\"ל אחת או יותר';
$lang['Unban_email_explain'] = 'ביכולתך להחזיר מספר כתובות דוא\"ל בו זמנית 
על ידי שימוש נכון בצירוף עכבר ומקלדת עבור המחשב והדפדפן שלך';

$lang['No_banned_users'] = 'אין שמות משתמשים מגורשים';
$lang['No_banned_ip'] = 'אין כתובות IP מגורשות';
$lang['No_banned_email'] = 'אין כתובות דוא\"ל מגורשות';

$lang['Ban_update_sucessful'] = 'רשימת המגורשים עודכנה בהצלחה';
$lang['Click_return_banadmin'] = 'לחץ %sכאן%s כדי לחזור לבקרת הגירוש';


//
// Configuration
//
$lang['General_Config'] = 'הגדרות כלליות';
$lang['Config_explain'] = 'הטופס הבא מאפשר לך להתאים אישית את הגדרות הלוח
הכלליות. להגדרות משתמש ופורום השתמש בקישורים בצד שמאל.';

$lang['Click_return_config'] = 'לחץ %sכאן%s כדי לחזור להגדרות הכלליות';

$lang['General_settings'] = 'הגדרות לוח בלליות';
$lang['Server_name'] = 'שם הדומיין';
$lang['Server_name_explain'] = 'הדומיין ממנו פועל הפורום הזה';
$lang['Script_path'] = 'כתובת הסקריפט';
$lang['Script_path_explain'] = 'הספריה אשר בא ממוקם הסקריפט יחסית לדומיין';
$lang['Server_port'] = 'פורט שרת';
$lang['Server_port_explain'] = 'מספר הפורט (port) שהשרת שלך רץ עליו. בדרך 
כלל הערך הוא 80';
$lang['Site_name'] = 'שם האתר';
$lang['Site_desc'] = 'תאור האתר';
$lang['Board_disable'] = 'כבה לוח';
$lang['Board_disable_explain'] = 'זה יהפוך את הלוח ללא זמין למשתמשים. אל 
תתנתק
כשהלוח כבוי,לא תוכל להתחבר חזרה!';
$lang['Acct_activation'] = 'אפשר אישור חשבון';
$lang['Acc_None'] = 'ללא'; // These three entries are the type of activation
$lang['Acc_User'] = 'משתמש';
$lang['Acc_Admin'] = 'מנהל';

$lang['Abilities_settings'] = 'הגדרות משתמש ופורום בסיסיים';
$lang['Max_poll_options'] = 'מספר אפשרויות בסקר';
$lang['Flood_Interval'] = 'מונע הצפות';
$lang['Flood_Interval_explain'] = 'מספר השניות שמשתמש חייב להמתין בין פרסום
הודעות'; 
$lang['Board_email_form'] = 'שליחת דוא\"ל דרך הלוח';
$lang['Board_email_form_explain'] = 'משתמשים שולחים דוא\"ל אחד לשני דרך 
הלוח';
$lang['Topics_per_page'] = 'נושאים לעמוד';
$lang['Posts_per_page'] = 'הודעות לעמוד';
$lang['Hot_threshold'] = 'הודעות עבור נושאים פופולארים';
$lang['Default_style'] = 'סגנון ברירת-מחדל';
$lang['Override_style'] = 'דרוס סגנון משתמש';
$lang['Override_style_explain'] = 'מחליף את סגנונות המשתמש עם ברירת-המחדל';
$lang['Default_language'] = 'שפת ברירת-מחדל';
$lang['Date_format'] = 'תבנית תאריך';
$lang['System_timezone'] = 'אזור זמן המערכת';
$lang['Enable_gzip'] = 'אפשר כיווץ GZip';
$lang['Enable_prune'] = 'אשר סריקת הודעות בפורום';
$lang['Allow_HTML'] = 'הרשה HTML';
$lang['Allow_BBCode'] = 'הרשה BBCode';
$lang['Allowed_tags'] = 'הרשה תגי HTML';
$lang['Allowed_tags_explain'] = 'הפרד בין התגים באמצעות סימן הפסיק';
$lang['Allow_smilies'] = 'הרשה סמיילים';
$lang['Smilies_path'] = 'נתיב אחסון סמיילים';
$lang['Smilies_path_explain'] = 'נתיב תחת ספריית השורש של phpBB, לדוגמה
images/smilies';
$lang['Allow_sig'] = 'הרשה חתימות';
$lang['Max_sig_length'] = 'אורך חתימה מקסימלי';
$lang['Max_sig_length_explain'] = 'מספר מקסימום של תוים בחתימה';
$lang['Allow_name_change'] = 'הרשה שינויי שמות משתמש';

$lang['Avatar_settings'] = 'הגדרות סימלונים';
$lang['Allow_local'] = 'אפשר גלריית סימלונים';
$lang['Allow_remote'] = 'אפשר סימלונים מרוחקים';
$lang['Allow_remote_explain'] = 'סימלונים מקושרים מאתרים אחרים';
$lang['Allow_upload'] = 'אפשר טעינת סימלונים';
$lang['Max_filesize'] = 'גודל מקסימלי לקובץ סימלון';
$lang['Max_filesize_explain'] = 'לטעינת קובצי סימלון';
$lang['Max_avatar_size'] = 'מימדי סימלון מקסימליים';
$lang['Max_avatar_size_explain'] = '(גובה x רוחב בפיקסלים)';
$lang['Avatar_storage_path'] = 'נתיב אחסון סימלונים';
$lang['Avatar_storage_path_explain'] = 'נתיב תחת ספריית השורש של phpBB, 
לדוגמה
images/avatars';
$lang['Avatar_gallery_path'] = 'נתיב גלריית סימלונים';
$lang['Avatar_gallery_path_explain'] = 'נתיב תחת ספריית השורש של phpBB 
לתמונות
קיימות, לדוגמה images/avatars/gallery';

$lang['COPPA_settings'] = 'הגדרות COPPA';
$lang['COPPA_fax'] = 'מספר פקס של COPPA';
$lang['COPPA_mail'] = 'כתובת דואר של COPPA';
$lang['COPPA_mail_explain'] = 'זוהי כתובת הדואר שאליה ישלחו ההורים את טפסי
הרישום של COPPA';

$lang['Email_settings'] = 'הגדרות דוא\"ל';
$lang['Admin_email'] = 'כתובת דוא\"ל של המנהל';
$lang['Email_sig'] = 'חתימת דוא\"ל';
$lang['Email_sig_explain'] = 'טקסט זה יצורף לכל המכתבים שישלחו מהלוח';
$lang['Use_SMTP'] = 'השתמש בשרת SMTP עבור דוא\"ל';
$lang['Use_SMTP_explain'] = 'אמור כן אם ברצונך לשלוח דוא\"ל דרך שרת רשום 
במקום
להשתמש בפונקציית הדוא\"ל המקומית';
$lang['SMTP_server'] = 'כתובת שרת SMTP';
$lang['SMTP_username'] = 'שם משתמש SMTP';
$lang['SMTP_username_explain'] = 'הכנס שם משתמש רק אם שרת ה-SMTP שלך דורש 
זאת';
$lang['SMTP_password'] = 'סיסמת SMTP';
$lang['SMTP_password_explain'] = 'הכנס סיסמא רק אם שרת ה-SMTP שלך דורש זאת';

$lang['Disable_privmsg'] = 'הודעות פרטיות';
$lang['Inbox_limits'] = 'מספר הודעות מקסימלי בתיבת הדואר הנכנס';
$lang['Sentbox_limits'] = 'מספר הודעות מקסימלי בתיבת הדואר היוצא';
$lang['Savebox_limits'] = 'מספר הודעות מקסימלי בתיבת הדואר השמור';

$lang['Cookie_settings'] = 'הגדרות \"עוגייה\"'; 
$lang['Cookie_settings_explain'] = 'הגדרות אילו שולטות בצורה שבה מוגדרת 
ה\"עוגייה\" על ידי הדפדפנים. ברוב המקרים  ברירת המחדל תהיה מספיקה. אם אתה 
מתכוון לשנות כאן דבר, עשה זאת בזהירות, הגדרות מוטעות עלולות למנוע ממשתמשים 
להתחבר לפורום';
$lang['Cookie_domain'] = 'דומיין העוגייה';
$lang['Cookie_name'] = 'שם העוגייה';
$lang['Cookie_path'] = 'נתיב העוגייה';
$lang['Cookie_secure'] = 'אבטחת עוגיה [ https ]';
$lang['Cookie_secure_explain'] = 'אם השרת שלך פועל דרך SSL סמן אופצייה זו 
אחרת אשר אותה מבוטלת';
$lang['Session_length'] = 'אורך הריצה [בשניות]';


//
// Forum Management
//
$lang['Forum_admin'] = 'ניהול פורום';
$lang['Forum_admin_explain'] = 'מחלון זה תוכל להוסיף, למחוק, לערוך לשנות סדר 
ולסנכרן מחדש קטגוריות ופורומים';
$lang['Edit_forum'] = 'ערוך פורום';
$lang['Create_forum'] = 'צור פורום חדש';
$lang['Create_category'] = 'צור קטגוריה חדשה';
$lang['Remove'] = 'הסר';
$lang['Action'] = 'Action';
$lang['Update_order'] = 'עדכן סדר';
$lang['Config_updated'] = 'הגדרות הפורום עודכנו בהצלחה!';
$lang['Edit'] = 'ערוך';
$lang['Delete'] = 'מחק';
$lang['Move_up'] = 'הזז מעלה';
$lang['Move_down'] = 'הזז מטה';
$lang['Resync'] = 'Resync';
$lang['No_mode'] = 'שום מצב לא נבחר';
$lang['Forum_edit_delete_explain'] = 'טופס זה יאפשר לך לשנות ולאפיין את כל 
אפשרויות הפורום הכללי. על מנת לערוך הגדרות משתמש ופורום ספציפיים, העזר 
בקישוריות המתאימות.';

$lang['Move_contents'] = 'העבר את כל התוכן';
$lang['Forum_delete'] = 'מחק פורום';
$lang['Forum_delete_explain'] = 'טופס זה יאפשר לך למחוק פורום (או קטגוריה) 
ולהחליט לאן להעביר את כל ההודעות (או הפורומים) שהוא הכיל.';

$lang['Forum_settings'] = 'הגדרות פורום כלליות';
$lang['Forum_name'] = 'שם הפורום';
$lang['Forum_desc'] = 'תיאור';
$lang['Forum_status'] = 'מצב הפורום';
$lang['Forum_pruning'] = 'סריקת הודעות אוטומטית';

$lang['prune_freq'] = 'בדוק את גיל ההודעה כל';
$lang['prune_days'] = 'הסר דיונים שלא נוספו להם הודעות תוך';
$lang['Set_prune_data'] = 'הפעלת את סריקת ההודעות האוטומטית ומחיקתן לפורום 
זה, אך לא הגדרת תדירות או מספר ימים לביצוע הסריקה. אנא חזור ובצע זאת';

$lang['Move_and_Delete'] = 'העבר ומחוק';

$lang['Delete_all_posts'] = 'מחק את כל ההודעות';
$lang['Nowhere_to_move'] = 'אין לאן להעביר';

$lang['Edit_Category'] = 'ערוך קטגוריה';
$lang['Edit_Category_explain'] = 'העזר בטופס זה בכדי לשנות את שם הקטגוריה';

$lang['Forums_updated'] = 'המידע של הפורום ו/או הקטגוריה עודכנו בהצלחה';

$lang['Must_delete_forums'] = 'עליך למחוק את כל הפורומים לפני שתסיר קטגוריה 
זו';

$lang['Click_return_forumadmin'] = 'לחץ %sכאן%s על מנת לחזור ללוח ניהול';


//
// Smiley Management
//
$lang['smiley_title'] = 'כלי עריכת הסמיילים';
$lang['smile_desc'] = 'מכאן תוכל להוסיף, למחוק ולערוך את הסמיילים אותם יוכלו 
משתמשי הפורום להוסיף להודעותיהם';

$lang['smiley_config'] = 'הגדרות סמיילים';
$lang['smiley_code'] = 'מקשי הסמיילי';
$lang['smiley_url'] = 'קובץ הסמיילי';
$lang['smiley_emot'] = 'פירוש הסמיילי';
$lang['smile_add'] = 'הוסף סמיילי חדש';
$lang['Smile'] = 'סמיילי';
$lang['Emotion'] = 'רגש (פירוש הסמיילי)';

$lang['Select_pak'] = 'בחר בקובץ מסוג .pak';
$lang['replace_existing'] = 'החלף סמיילי קיים';
$lang['keep_existing'] = 'שמור על סמיילי קיים';
$lang['smiley_import_inst'] = 'עליך לפתוח את קובץ הסמיילי המקווץ ולהעלות את 
כל הקבצים לספריית סמיילים המתאימה עבר ההתקנה שלך. לאחר מכן בחר את המידע 
הנכון לצורך יבוא חבילת הסמיילי';
$lang['smiley_import'] = 'ייבא סמיילי';
$lang['choose_smile_pak'] = 'בחר בקובץ סמיילי מסוג .pak';
$lang['import'] = 'יבא סמיילים';
$lang['smile_conflicts'] = 'מה צריך להעשות במקרה של קושי';
$lang['del_existing_smileys'] = 'מחק סמיילים קיימים לפני הייבוא';
$lang['import_smile_pack'] = 'ייבא חבילת סמיילים';
$lang['export_smile_pack'] = 'צור חבילת סמיילים';
$lang['export_smiles'] = 'לצורך יצירת חבילת סמיילים מבין מבחר הסמיילים אשר 
כבר מותקן וקיים ברשותך, לחץ %sכאן%s כדי להוריד רת קובץ ה smiles.pak. שנה את 
שמו של קובץ זה בהתאם, אך דאג להשאיר את סיומת הקובת .pak. לאחר מכן צור קובץ 
zip המכיל את כל תמונות הסמיילים בנוסף לקובץ ההגדרות .pak הזה.';

$lang['smiley_add_success'] = 'הסמיילי נוסף בהצלחה';
$lang['smiley_edit_success'] = 'הסמיילי עודכן בהצלחה';
$lang['smiley_import_success'] = 'הסמיילי יובא בהצלחה!';
$lang['smiley_del_success'] = 'הסמיילי הוסר בהצלחה';
$lang['Click_return_smileadmin'] = 'לחץ %sכאן%s בכדי לשוב לכלי ניהול 
הסמיילים';


//
// User Management
//
$lang['User_admin'] = 'ניהול משתמשים';
$lang['User_admin_explain'] = 'כאן תוכל לשנות את מידע המשתמש ומספר אפשרויות 
ספיציפיות. על מנת לשנות הרשאות משתמש אנא העזר במערכת הרשאות קבוצה ומשתמש';

$lang['Look_up_user'] = 'חפש משתמש';

$lang['Admin_user_fail'] = 'נכשל עדכון פרופיל משתמש';
$lang['Admin_user_updated'] = 'פרופיל משתמש עודכן בהצלחה.';
$lang['Click_return_useradmin'] = 'לחץ %sכאן%s בכדי לחזור לניהול משתמשים.';

$lang['User_delete'] = 'מחק משתמש זה';
$lang['User_delete_explain'] = 'לחץ כאן בכדי למחוק את המשתמש, תהליך זה הינו 
בלתי הפיך!';
$lang['User_deleted'] = 'המשתמש נמחק בהצלחה.';

$lang['User_status'] = 'המשתמש פעיל';
$lang['User_allowpm'] = 'יכול לשלוח הודעות פרטיות';
$lang['User_allowavatar'] = 'יכול להציג סמלון';

$lang['Admin_avatar_explain'] = 'כאן ביכולתך לראות ולמחוק את הסמלון של משתמש 
מסויים.';

$lang['User_special'] = 'אזורים מיוחדים למנהלים בלבד';
$lang['User_special_explain'] = 'משתמשים אינם יכולים לערוך שדות אילו. כאן 
תוכל לקבוע להם סטטוס ואפשרויות אחרות אשר לא ניתנות למשתמשים';


//
// Group Management
//
$lang['Group_administration'] = 'ניהול קבוצות';
$lang['Group_admin_explain'] = 'מכאן תוכל לנהל את כל הקבוצות, ביכולתך למחוק, 
ליצור ולערוך קבוצות קיימות. אתה יכול לבחור באחראים, לשנות את הרשאות הקבוצה 
ולשנות את שם הקבוצה ותיאורה';
$lang['Error_updating_groups'] = 'אירעה שגיאה בעת עדכון הגדרות הקבוצות';
$lang['Updated_group'] = 'הקבוצה עודכנה בהצלחה';
$lang['Added_new_group'] = 'קבוצה חדשה נוצרה בהצלחה';
$lang['Deleted_group'] = 'הקבוצה נמחקה בהצלחה';
$lang['New_group'] = 'צור קבוצה חדשה';
$lang['Edit_group'] = 'ערוך קבוצה';
$lang['group_name'] = 'שם הקבוצה';
$lang['group_description'] = 'תיאור הקבוצה';
$lang['group_moderator'] = 'אחראי קבוצה';
$lang['group_status'] = 'סטטוס קבוצה';
$lang['group_open'] = 'פתח קבוצה';
$lang['group_closed'] = 'סגור קבוצה';
$lang['group_hidden'] = 'קבוצה נסתרת';
$lang['group_delete'] = 'מחק קבוצה';
$lang['group_delete_check'] = 'מחק קבוצה זו';
$lang['submit_group_changes'] = 'עדכן שינויים';
$lang['reset_group_changes'] = 'מחק שינויים';
$lang['No_group_name'] = 'אתה חייב לציין שם עבור קבוצה זו';
$lang['No_group_moderator'] = 'אתה חייב לציין אחראי לקבוצה זו';
$lang['No_group_mode'] = 'אתה חייב לציין מצב לקבוצה זו, קבוצה פתוחה או קבוצה 
סגורה';
$lang['No_group_action'] = 'לא צויינה פעולה';
$lang['delete_group_moderator'] = 'האם למחוק את אחראי הקבוצה הישן?';
$lang['delete_moderator_explain'] = 'אם אתה משנה את אחראי הקבוצה, סמן את 
הריבוע הזה על מנת למחוק את האחראי הישן. אחרת, אל תסמן ריבוע זה, והמשתמש 
יהפוך לחבר רגיל בקבוצה.';
$lang['Click_return_groupsadmin'] = 'לחץ %sכאן%s כדי לחזור לניהול קבוצות';
$lang['Select_group'] = 'בחר קבוצה';
$lang['Look_up_group'] = 'חפש קבוצה';


//
// Prune Administration
//
$lang['Forum_Prune'] = 'אוטומחיקת פורום';
$lang['Forum_Prune_explain'] = 'אפשרות זאת תמחוק כל נושא אשר לא נכתבו בו 
הודעות במשך תקופת זמן מסויימת אותה תקבע מראש. באם לא תכניס ערך מספרי, כל 
הנושאים ימחקו. אוטומחיקה לא ימחק נושאים בהם מערכת הסקרים עדיין פעילה, וכמו 
כן לא תמחוק הודעות מערכת. תאלץ להסיר נושאים אילו בצורה ידנית.';
$lang['Do_Prune'] = 'בצע אוטומחיקה';
$lang['All_Forums'] = 'כל הפורומים';
$lang['Prune_topics_not_posted'] = 'אוטומחיקת נושאים אשר לא נכתבו בהם הודעות 
במשך ערך זה של ימים';
$lang['Topics_pruned'] = 'אוטומחיקת נושאים';
$lang['Posts_pruned'] = 'אוטומחיקת הודעות';
$lang['Prune_success'] = 'אוטומחיקת פורומים התבצעה בהצלחה.';


//
// Word censor
//
$lang['Words_title'] = 'צנזור מילים';
$lang['Words_explain'] = 'מכאן באפשרותך להוסיף, לערוך ולמחוק מילים אשר 
יצונזרו באופן אוטומטי בפורומים שלך. בנוסף, משתמשים לא יורשו להרשם עם שמות 
משתמש אשר מכילות בתוכם את המילים הללו. קיצורים מקובלים כאן, כגון * ו ?.';
$lang['Word'] = 'מילה';
$lang['Edit_word_censor'] = 'ערוך מסנן מילים';
$lang['Replacement'] = 'החלף';
$lang['Add_new_word'] = 'הוסף מילה חדשה';
$lang['Update_word'] = 'עדכן מסנן מילים';

$lang['Must_enter_word'] = 'אתה חייב להכניס מילה את המילה המחליפה אותה';
$lang['No_word_selected'] = 'לא נבחרה אף מילה לעריכה';

$lang['Word_updated'] = 'מסנן המילים הנבחר עודכן בהצלחה';
$lang['Word_added'] = 'מסנן מילים חדש התווסף בהצלחה';
$lang['Word_removed'] = 'מסנן המילים הנבחר הוסר בהצלחה';

$lang['Click_return_wordadmin'] = 'לחץ %sכאן%s בכדי לחזור לניהול צנזור 
המילים';


//
// Mass Email
//
$lang['Mass_email_explain'] = 'מכאן תוכל לשלוח דואל לכל חברי הפורום שלך או 
לכל חברי קבוצה מסויימת. על מנת לעשות זאת, דואל ישלח לכתובת הדואר הניהולית 
שסופקה, עם העתקים לכל הנמענים. אם אתה שולח הודעה למספר רב של אנשים, אנא היה 
סבלני ואל תעצור את הדף באמצע הפעולה. שליחת הודעה למספר רב של אנשים לוקחת זמן 
רב, ואתה תקבל הודעה ברגע שהתהליך יושם.';
$lang['Compose'] = 'שלח'; 

$lang['Recipients'] = 'הנמענים'; 
$lang['All_users'] = 'כל המשתמשים';

$lang['Email_successfull'] = 'הודעתך נשלחה.';
$lang['Click_return_massemail'] = 'לחץ %sכאן%s בכדי לחזור ללוח שליחת דואל';


//
// Ranks admin
//
$lang['Ranks_title'] = 'ניהול דירוגים';
$lang['Ranks_explain'] = 'באמצעות טופס זה תוכלו להוסיף, לערוך, לצפות ואף 
למחוק דירוגים. כמו כן תוכלו ליצור דירוגים מיוחדים אשר יתווספו למשתמשים 
ספציפים באמצעות לוח הניהול';

$lang['Add_new_rank'] = 'הוסף דירוג חדש';

$lang['Rank_title'] = 'שם הדירוג';
$lang['Rank_special'] = 'הגדר כדירוג מיוחד';
$lang['Rank_minimum'] = 'מינימום הודעות';
$lang['Rank_maximum'] = 'מקסימום הודעות';
$lang['Rank_image'] = 'תמונת הדירוג (לפי ספריית ה-PHP)';
$lang['Rank_image_explain'] = 'השתמש בכך בכדי לקבוע סמלים קטנים הקשורים 
לדירוג';

$lang['Must_select_rank'] = 'יש לבחור דירוג';
$lang['No_assigned_rank'] = 'שום דירוג מיוחד לא נבחר';

$lang['Rank_updated'] = 'הדירוג עודכן בהצלחה';
$lang['Rank_added'] = 'הדירוג נוסף בהצלחה';
$lang['Rank_removed'] = 'הדירוג נמחק בהצלחה';
$lang['No_update_ranks'] = 'הדירוג נמחק בהצלחה, אך משתמשים אשר משתמשים 
בדירוג זה לא עודכנו. תיאלץ לשנות באופן ידני את הדירוג של משתמשים אילו';

$lang['Click_return_rankadmin'] = 'לחץ %sכאן%s בכדי לחזור ללוח ניהול 
דירוגים';


//
// Disallow Username Admin
//
$lang['Disallow_control'] = 'מערכת חסימת שמות משתמש';
$lang['Disallow_explain'] = 'כלי זה מעניק לך את היכולת למנוע משם משתמש 
מסויים להרשם. ניתן להעזר בסימן * בכדי להגדיר טווח אותיות מסויים. אם שם משתמש 
שנחסם כבר קיים, עליך למחוק תחילה את שם המשתמש הקיים, ואז לבצע את החסימה.';

$lang['Delete_disallow'] = 'מחק';
$lang['Delete_disallow_title'] = 'הסר שם משתמש חסום';
$lang['Delete_disallow_explain'] = 'ביכולתך למחוק שם משתמש חסום על ידי בחירת 
שם המשתמש מרשימה זו ולחיצה על מחק';

$lang['Add_disallow'] = 'הוסף';
$lang['Add_disallow_title'] = 'הוסף שם משתמש חסום';
$lang['Add_disallow_explain'] = 'ביכולתך לחסום שם משתמש ולהעזר בסימן * בכדי 
להגדיר טווח';

$lang['No_disallowed'] = 'אין שמות משתמש חסומים!';

$lang['Disallowed_deleted'] = 'חסימת שם המשתמש הוסרה בהצלחה';
$lang['Disallow_successful'] = 'חסימת שם המשתמש החדשה נוספה בהצלחה';
$lang['Disallowed_already'] = 'לא ניתן לחסום את שם המשתמש שהגדרת. יתכן כי שם 
המשתמש כבר קיים ברשימת החסימה, או ברשימת צינזור המילים ואף יתכן כי יש בנמצא 
שם משתמש רשום שכזה';

$lang['Click_return_disallowadmin'] = 'לחץ %sכאן%s בכדי לשוב למערכת חסימת 
שמות משתמש';


//
// Styles Admin
//
$lang['Styles_admin'] = 'מערכת ניהול סגנונות';
$lang['Styles_explain'] = 'במערכת זו תוכל להוסיף, למחוק ולערוך סגנונות אשר 
יהיו בשימוש משתמשי הפורום.';
$lang['Styles_addnew_explain'] = 'הרשימה הבאה מכילה את כל הסקינים האפשריים 
עבור הסגנונות שיש לך כעת. כל המצוי ברשימה זו טרם הותקן במאגר המידע של PhpBB. 
על מנת להתקין נושא מסויים, פשוט לחץ על קישור ההתקנה בצד הרכיב';

$lang['Select_template'] = 'בחר תבנית';

$lang['Style'] = 'סגנון';
$lang['Template'] = 'תבנית';
$lang['Install'] = 'התקן';
$lang['Download'] = 'הורד';

$lang['Edit_theme'] = 'ערוך סקין';
$lang['Edit_theme_explain'] = 'בטופס הבא תוכל לערוך את ההגדרות עבור הסקין 
הנבחר';

$lang['Create_theme'] = 'צור סקין';
$lang['Create_theme_explain'] = 'בטופס הבא תוכל ליצור סקין חדש עבור תבנית 
נבחרת. כאשר תגדיר את הצבעים (עבורם עליך להשתמש בקוד HEX), אסור לך להכליל את 
הסימן # ההתחלתי.
למשל... CCCCCC הינו ערך מקובל, כאשר #CCCCCC אינו ערך מקובל.';

$lang['Export_themes'] = 'יצא סקינים';
$lang['Export_explain'] = 'בפאנל זה תוכל לייצא את מידע הסקין עבור תבנית אשר 
נבחרה. בחר את התבנית מרשימה מתחת והסקריפט יצור את קובץ הגדרות הסקין וינסה 
לשמור אותו בספריית התבנית הנבחרת. אם פעולת השמירה לא תתבצע בהצלחה, תנתן לך 
האפשרות להוריד אותו מהשרת. בכדי להבטיח את הצלחת תהליך השמירה של הסקריפט, 
עליך להעניק לשרת האתר הרשאת כתיבה עבור ספריית התבנית הנבחרת.';

$lang['Theme_installed'] = 'הסקין הנבחר הותקן בהצלחה';
$lang['Style_removed'] = 'הסגנון הנבחר הוסר בהצלחה ממאגר המידע. על מנת להסיר 
באופן מוחלט את סגנון זה מהמערכת עליך למחוק את הסגנון המתאים מספריות התבנית 
שלך.';
$lang['Theme_info_saved'] = 'המידע של הסקין עבור התבנית הנבחרת נשמר בהצלחה. 
עליך להחזיר כעת להרשאות של-theme_info.cfg (ואם אפשרי את ספריית התבנית 
הנבחרת) לקריאה בלבד';
$lang['Theme_updated'] = 'הסקין הנבחר עודכן בהצלחה. עליך לייצא כעת את הגדרות 
הסקין החדשות';
$lang['Theme_created'] = 'הסקין נוצר. עליך לייצא כעת את הסקין לקובץ הגדרות 
סקין כגיבוי, או עבור שימוש במקום אחר';

$lang['Confirm_delete_style'] = 'האם אתה בטח כי ברצונך למחוק את הסגנון 
הזה?';

$lang['Download_theme_cfg'] = 'תהליך הייצוא לא יכל לכתוב את קובץ מידע הסקין. 
לחץ על הכפתור שמתחת על מנת להוריד את הקובץ באמצעות הדפדפן שלך. מהרגע שהורדת 
אותו, תוכל להעביר אותו לספרייה המכילה את קבצי התבנית. לאחר מכן תוכל להפיץ את 
הסקין לכל מקום שתרצה';
$lang['No_themes'] = 'התבנית אותה בחרת הינה חסרת סקינים המשוייכים אליה. בכדי 
ליצור סקין חדש, לחץ על קישור \"צור סקין חדש\" בפאנל הצידי.';
$lang['No_template_dir'] = 'פתיחת תיקיית התבנית נכשלה. יתכן כי אין הרשאת 
קריאה עבור תיקייה זו על ידי השרת, או שאולי איננה קיימת.';
$lang['Cannot_remove_style'] = 'פעולת ההסרת הסגנון הנבחר נכשלה מכיוון שהוא 
מהווה כעת את ברירת המחדל של הפורום. אנא שנה את ברירת המחדל ונסה שנית.';
$lang['Style_exists'] = 'השם עבור הסגנון כבר קיים, אנא חזור ובחר בשם אחר.';

$lang['Click_return_styleadmin'] = 'לחץ %sכאן%s על מנת לחזור למערכת ניהול 
סגנונות';

$lang['Theme_settings'] = 'הגדרות סקינים';
$lang['Theme_element'] = 'אלמנט הסקין';
$lang['Simple_name'] = 'שם הסקין';
$lang['Value'] = 'ערך';
$lang['Save_Settings'] = 'שמור הגדרות';

$lang['Stylesheet'] = 'CSS Stylesheet';
$lang['Background_image'] = 'תמונת רקע';
$lang['Background_color'] = 'צבע רקע';
$lang['Theme_name'] = 'שם הסקין';
$lang['Link_color'] = 'צבע קישור';
$lang['Text_color'] = 'צבע הטקסט';
$lang['VLink_color'] = 'צבע קישור שנבדק';
$lang['ALink_color'] = 'צבע קישור פעיל';
$lang['HLink_color'] = 'צבע קישור אשר סמן העכבר מצביע עליו';
$lang['Tr_color1'] = 'צבע שורת טבלה 1';
$lang['Tr_color2'] = 'צבע שורת טבלה 2';
$lang['Tr_color3'] = 'צבע שורת טבלה 3';
$lang['Tr_class1'] = 'סוג שורת טבלה 1';
$lang['Tr_class2'] = 'סוג שורת טבלה 2';
$lang['Tr_class3'] = 'סוג שורת טבלה 3';
$lang['Th_color1'] = 'צבע כותרת טבלה 1';
$lang['Th_color2'] = 'צבע כותרת טבלה 2';
$lang['Th_color3'] = 'צבע כותרת טבלה 3';
$lang['Th_class1'] = 'סוג כותרת טבלה 1';
$lang['Th_class2'] = 'סוג כותרת טבלה 2';
$lang['Th_class3'] = 'סוג כותרת טבלה 3';
$lang['Td_color1'] = 'צבע תא טבלה 1';
$lang['Td_color2'] = 'צבע תא טבלה 2';
$lang['Td_color3'] = 'צבע תא טבלה 3';
$lang['Td_class1'] = 'סוג תא טבלה 1';
$lang['Td_class2'] = 'סוג תא טבלה 2';
$lang['Td_class3'] = 'סוג תא טבלה 3';
$lang['fontface1'] = 'גופן מספר 1';
$lang['fontface2'] = 'גופן מספר 2';
$lang['fontface3'] = 'גופן מספר 3';
$lang['fontsize1'] = 'גודל גופן 1';
$lang['fontsize2'] = 'גודל גופן 2';
$lang['fontsize3'] = 'גודל גופן 3';
$lang['fontcolor1'] = 'צבע גופן 1';
$lang['fontcolor2'] = 'צבע גופן 2';
$lang['fontcolor3'] = 'צבע גופן 3';
$lang['span_class1'] = 'Span Class 1';
$lang['span_class2'] = 'Span Class 2';
$lang['span_class3'] = 'Span Class 3';
$lang['img_poll_size'] = 'גודל תמונת הצבעה [פיקסלים]';
$lang['img_pm_size'] = 'גודל מצב הודעה פרטית [פיקסלים]';


//
// Install Process
//
$lang['Welcome_install'] = 'ברוך הבא להתקנת phpBB 2 - הגרסה העברית';
$lang['Initial_config'] = 'הגדרות בסיסיות';
$lang['DB_config'] = 'הגדרות מסד נתונים';
$lang['Admin_config'] = 'הגדרות מנהל';
$lang['continue_upgrade'] = 'ברגע שהורדת את קובץ ההגדרות למחשב אתה רשאי 
להמשיך את פעולת העדכון על ידי לחיצה על כפתור \"המשך עדכון\".  אנא המתן 
להעלאת קובץ ההגדרות עד שתהליך העדכון יסתיים.';
$lang['upgrade_submit'] = 'המשך עדכון';

$lang['Installer_Error'] = 'תקלה אירעה במהלך ההתקנה';
$lang['Previous_Install'] = 'התקנה קודמת זוהתה';
$lang['Install_db_error'] = 'תקלה התרחשה בתהליך עדכון מסד הנתונים';

$lang['Re_install'] = 'ההתקנה הקודמת עדיין תקפה.<br /><br
/>
אם תרצה להתקין מחדש את phpBB 2 עליך ללחוץ על כפתור כן מתחת. אנא שם לב שביצוע 
פעולה זו תשמיד את כל המידע הקיים, ללא עשיית כל גיבוי! שם המשתמש והסיסמא של 
האדמין בהם התשמשת תיווצר מחדש בסיום ההתקנה, אך שום מידע אחר לא ישמר.
<br /><br
/>
חשוב בזהירות לפני לחיצה על כן!';

$lang['Inst_Step_0'] = 'תודה לך שבחרת ב phpBB 2, הגירסה העיברית. בכדי להשלים 
את תהליך ההתקנה הזה אנא מלא את הפרטים המבוקשים. אנא שים לב שמאגר המידע שאתה 
מתקין לתוכו כבר אמור להיות קיים. אם אתה מתקין לתוך מאגר מידע הנעזר ב ODBC, 
לדוגמא MS Access, עליך תחילה ליצור DSN עבורו לפני שתמשיך.';

$lang['Start_Install'] = 'תחילת ההתקנה';
$lang['Finish_Install'] = 'סיום ההתקנה';

$lang['Default_lang'] = 'שפת ברירת המחדל';
$lang['DB_Host'] = 'שם שרת מסד הנתונים / DSN';
$lang['DB_Name'] = 'שמך במאגר המידע';
$lang['DB_Username'] = 'שם משתמש מאגר המידע';
$lang['DB_Password'] = 'סיסמא למאגר המידע';
$lang['Database'] = 'מאגר המידע שלך';
$lang['Install_lang'] = 'בחר בשפה עבור ההתקנה';
$lang['dbms'] = 'סוג מאגר מידע';
$lang['Table_Prefix'] = 'קיצור (סימן מיוחד) עבור טבלאות במאגר המידע';
$lang['Admin_Username'] = 'שם משתמש האדמין';
$lang['Admin_Password'] = 'סיסמת האדמין';
$lang['Admin_Password_confirm'] = 'הקש שנית את סיסמת האדמין [לצורך אישור]';

$lang['Inst_Step_2'] = 'שם משתמש האדמין שלך נוצר. בנקודה זו ההתקנה הבסיסית 
נסתיימה. כעת תלקח למסך אשר יאפשר לך לנהל את ההתקנה החדשה. אנא בדוק את פרטי 
ההגדרות הכלליות ובצע את כל השינויים הדרושים. תודה לך שבחרת ב phpBB2, הגירסה 
העיברית.';

$lang['Unwriteable_config'] = 'קובץ ההגדרות שלך אינו ניתן לכתיבה כרגע. עותק 
של קובץ ההגדרות יועבר למחשבך ברגע שתקיש על הכפתור מתחת. עליך להעלות קובץ זה 
לאותה ספרייה בה ממוקם phpBB2. ברגע שפעולה זו תתבצע תוכל להתחבר באמצעות סיסמת 
האדמין שלך ולבקר בלוח הניהול (קישור יופיע בתחתית הפורום) בשביל לשנות את 
ההגדרות הכלליות. תודה לך שבחרת ב-phpBB2, הגירסה העיברית.';
$lang['Download_config'] = 'החל הורדה';

$lang['ftp_choose'] = 'בחר צורת הורדה';
$lang['ftp_option'] = '<br />מכיוון שסיומות FTP הינם מורשות בגירסה זו של 
php, תהיה רשאי לנסות תחילה להעלות אוטומטית ב-ftp את קובץ ההגדרות למקומו.';
$lang['ftp_instructs'] = 'בחרת להעלות את הקובץ לחשבון המכיל את phpBB2 בצורת 
FTP באופן אוטומטי. אנא הכנס את המידע הדרוש בשביל לבצע את תהליך זה. אנא שם לב 
שכתובת ה-FTP צריכה להיות הכתובת המדוייקת באמצעות FTP אל התקנת ה phpBB2 כפי 
שהיית ניגש אליה באמצעי FTP רגילים.';
$lang['ftp_info'] = 'הכנס את נתוני ה-FTP כאן';
$lang['Attempt_ftp'] = 'נסה לשלוח את קובץ ההגדרות למקומו באמצעות FTP';
$lang['Send_file'] = 'פשוט שלח אלי את הקובץ ואני אעלה אותו ידנית';
$lang['ftp_path'] = 'כתובת FTP אל phpBB2';
$lang['ftp_username'] = 'שם משתמש FTP שלך';
$lang['ftp_password'] = 'סיסמת FTP שלך';
$lang['Transfer_config'] = 'התחל בהעברה';
$lang['NoFTP_config'] = 'נסיון השליחה של הקובץ באמצעות FTP נכשל. אנא הורד את 
הקובץ והכנס אותו בעצמך באופן ידני.';

$lang['Install'] = 'התקן';
$lang['Upgrade'] = 'שדרג';


$lang['Install_Method'] = 'בחר את שיטת ההתקנה הרצוייה';

$lang['Install_No_Ext'] = 'הגדרות ה-php בשרת שלך לא תומכות בסוג מאגר המידע 
בו בחרת';

$lang['Install_No_PCRE'] = 'phpBB2 דורש Perl-Compatible Regular
Expressions Module עבור php, כאשר הגדרות ה-Php שלך כנראה אינן תומכות!';

//
// That's all Folks!
// -------------------------------------------------

?>