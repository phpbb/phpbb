<?php
/***************************************************************************
 *                         lang_bbcode.php [english]
 *                            -------------------
 *   begin                : Monday Apr 22, 2002
 *   copyright            : (C) 2002 Gil Osher and the Chaotic Goat
 *   email                : dolfin@rpg.org.il
 *
 *   $Id$
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/
 
// 
// To add an entry to your BBCode guide simply add a line to this file in this format:
// $faq[] = array("question", "answer");
// If you want to separate a section enter $faq[] = array("--","Block heading goes here if wanted");
// Links will be created automatically
//
// DO NOT forget the ; at the end of the line.
// Do NOT put double quotes (") in your BBCode guide entries, if you absolutely must then escape them ie. \"something\"
//
// The BBCode guide items will appear on the BBCode guide page in the same order they are listed in this file
//
// If just translating this file please do not alter the actual HTML unless absolutely necessary, thanks :)
//
// In addition please do not translate the colours referenced in relation to BBCode any section, if you do
// users browsing in your language may be confused to find they're BBCode doesn't work :D You can change
// references which are 'in-line' within the text though.
//
  
$faq[] = array("--","היכרות");
$faq[] = array("מה זה BBCode?", "BBCode הינו הזנה מיוחדת של קוד HTML. אם אכן ניתן להשתמש ב BBCode בהודעות בפורום מוחלט על-ידי המנהל. בנוסף ניתן לכבות את ה BBCode בהודעה באמצעות טופס כתיבת ההודעה. BBCode לכשעצמו דומה לסגנון של HTML, התגים נמצאים בתוך סוגריים מרובעות [ ו ] בניגוד ל &lt; ו &gt; וזה מציע שליטה גדולה יותר על איך משהו יראה. לפי התבנית בה אתה משתמש תגלה שהוספת BBCode להודעותיך נהיית קלה יותר על-ידי ממשק לחיץ מעל ההודעה בטופס ההודעה. אפילו עם זה תוכל למצוא את המדריך הזה יעיל.");

$faq[] = array("--","עיצוב טקסט");
$faq[] = array("איך ליצור טקסטים מודגשים, נטויים או עם קו תחתי", "BBCode כולל תגים המאפשרים לשנות את עיצוב הטקסט שלך במהרה. זה מתבצע בדרכים הבאות: <ul><li>כדי להדגיש קטע טקסט שים אותו בתוך <b>[b][/b]</b>, לדגומה <br /><br /><b>[b]</b>שלום<b>[/b]</b><br /><br />יהפך ל <b>שלום</b></li><li>כדי לשים קו תחתי <b>[u][/u]</b>, לדוגמה:<br /><br /><b>[u]</b>בוקר טוב<b>[/u]</b><br /><br />יהפך ל <u>בוקר טוב</u></li><li>כדי להטות טקסט השתמש ב <b>[i][/i]</b>, לדגומה<br /><br />זה <b>[i]</b>נהדר!<b>[/i]</b><br /><br />ייתן לנו זה <i>נהדר!</i></li></ul>");
$faq[] = array("איך לשנות את צבע הטקסט או את גודלו", "כדי לשנות את צבע או גודל הטקסט שלך ניתן להשתמש בתגים הבאים. שים לב שהצורה בה הפלט יופיע תלוי בדפדפן ובמערכת של הצופים: <ul><li>שינוי צבע הטקסט נעשה על-ידי כליאתו בתוך <b>[color=][/color]</b>. ניתן לציין או שם צבע מזוהה (באנגלית) (לדוגמה: red, blue, yellow, וכו') או לחילופין במשולש הקסדצימלי, לדוגמה: #FFFFFF, #000000. לדוגמה, כדי ליצור טקסט אדום ניתן להשתמש ב <br /><br /><b>[color=red]</b>שלום!<b>[/color]</b><br /><br />או ב<br /><br /><b>[color=#FF0000]</b>שלום!<b>[/color]</b><br /><br />שניהם ייתנו <span style=\"color:red\">שלום!</span></li><li>שינוי גודל הטקסט מושג בדרך דומה על-ידי שימוש ב <b>[size=][/size]</b>. תג זה תלוי בתבנית בה אתה משתמש אך הפורמט המומלץ הינו מספר המייצג את גודל הטקסט בפיקסלים, המתחיל ב 1 (כל-כך קטנה שלא תראה את זה) עד 29 (גדול מאוד). לדוגמה:<br /><br /><b>[size=9]</b>קטן<b>[/size]</b><br /><br />יהפך ל <span style=\"font-size:9px\">קטן</span><br /><br />בניגוד ל:<br /><br /><b>[size=24]</b>ענק!<b>[/size]</b><br /><br />שיהיה <span style=\"font-size:24px\">ענק!</span></li></ul>");
$faq[] = array("האם אני יכול לשלב תגי עיצוב?", "כן, כמובן שאתה יכול, לדוגמה, כדי לקבל את תשומת הלב של מישהו כתוב:<br /><br /><b>[size=18][color=red][b]</b>תסתכל עלי!<b>[/b][/color][/size]</b><br /><br />זה יחזיר <span style=\"color:red;font-size:18px\"><b>תסתכל עלי!</b></span><br /><br />למרות שאנחנו לא ממליצים לך לכתוב הרבה טקסט שנראה כך! זכור כי זה תלוי בך, המפרסם לוודא שכל התגים סגורים בצורה נכונה. הדוגמה הבאה אינה נכונה:<br /><br /><b>[b][u]</b>זה לא נכון<b>[/b][/u]</b>");

$faq[] = array("--","ציטוט והצגת טקסט מיושר");
$faq[] = array("ציטוט טקסט בתגובות", "ישנן שתי דרכים בהן ניתן לצטט טקסט, עם הפניה או בלעדיה.<ul><li>כשאתה משתמש בפונקציית הציטוט כדי להגיב להודעה בלוח אתה תשים לב שתוכן ההודעה מתווסף לחלון ההודעה בתוך בלוק <b>[quote=\"\"][/quote]</b>. שיטה זו מאפשרת לך לצטט עם קישור אדם או כל דבר אחר שברצונך להכניס! לדוגמה, כדי לצטט פיסת טקסט אשר אדון בלובי כתב, נכניס:<br /><br /><b>[quote=\"אדון בלובי\"]</b>הטקסט שאדון בלובי כתב יכנס כאן<b>[/quote]</b><br /><br />תוצאת הפלט תוסיף אוטומטית, אדון בלובי כתב: לפני הטקסט הממשי. זכור כי הינך <b>חייב</b> לכלול גרשיים \"\" סביב השם של מי שאתה מצטט, הם לא אופציה.</li><li>השיטה שהניה היא ציטוט עיוור של משהו. כדי להשתמש בזה סגור את הטקסט בתגי <b>[quote][/quote]</b>. כשתצפה בהודעה זה פשוט יראה, ציטוט: לפני הטקסט עצמו.</li></ul>");
$faq[] = array("הצגת קוד או מידע מיושר", "אם ברצונך להציג פיסת קוד או למעשה כל דבר המצריך רוחב מיושר, לדוגמה בסוג גופן Courier יש לכלוא את הטקסט בתגים <b>[code][/code]</b> לדוגמה<br /><br /><b>[code]</b>echo \"This is some code\";<b>[/code]</b><br /><br />כל היישורים בתוך התגים <b>[code][/code]</b> ישמרו כשתצפה בהם אחר-כך.");

$faq[] = array("--","ייצור רשימות");
$faq[] = array("יצירת רשימה לא מסודרת", "BBCode תומך בשני סוגי רשימות, לא מסודרת ומסודרת. הן זהות למקבילי ה HTML שלהן. רשימה לא מסודרת תחזיר כל פריט ברשימה שלך ברצף אחד אחרי השני ןתייצג כל אחד בתו מדגיש. כדי ליצור רשימה לא מסודרת השתמש ב <b>[list][/list]</b> והגדר כל פריט על-ידי שימוש ב <b>[*]</b>. לדוגמה כדי לפרט את הצבעים האהובים עליך תוכל להשתמש ב:<br /><br /><b>[list]</b><br /><b>[*]</b>אדום<br /><b>[*]</b>כחול<br /><b>[*]</b>צהוב<br /><b>[/list]</b><br /><br />זה ייצר את הרשימה הבאה:<ul><li>אדום</li><li>כחול</li><li>צהוב</li></ul>");
$faq[] = array("יצירת רשימה מסודרת", "הסוג השני של רשימה, רשימה מסודרת נונת לך שליטה על מה שיוצג לפני כל פריט. כדי ליצור רשימה מסודרת משתמשים ב <b>[list=1][/list]</b> כדי ליצור רשימה מסודרת לפי מספרים או לחילופין <b>[list=a][/list]</b> לרשימה אלפבתית (באנגלית). כמו ברשימה הלא מסודרת פריטים מייוצגים על-ידי שימוש ב <b>[*]</b>. לדוגמה:<br /><br /><b>[list=1]</b><br /><b>[*]</b>ללכת לחנות<br /><b>[*]</b>לקנות שרת חדש<br /><b>[*]</b>לקלל את השרת כשהוא נופל<br /><b>[/list]</b><br /><br />ייצר את הרשימה הבאה:<ol type=\"1\"><li>ללכת לחנות</li><li>לקנות שרת חדש</li><li>לקלל את השרת כשהוא נופל</li></ol>כאשר ברשימה אלפבתית תשתמש ב:<br /><br /><b>[list=a]</b><br /><b>[*]</b>התשובה האפשרית הראשונה<br /><b>[*]</b>התשובה האפשרית השניה<br /><b>[*]</b>התשובה האפשרית השלישית<br /><b>[/list]</b><br /><br />ייתן<ol type=\"a\"><li>התשובה האפשרית הראשונה</li><li>התשובה האפשרית השניה</li><li>התשובה האפשרית השלישית</li></ol>");

$faq[] = array("--", "ייצירת קישורים");
$faq[] = array("קישור לאתר אחר", "ה BBCode של phpBB תומך במספר רב של דרכים ליצור קישורים.<ul><li>הדרך הראשונה היא להשתמש בתגים <b>[url=][/url]</b>, מה שנכתב אחרי סימן הr the = יגרום לתוכן של התג להתנהג כקישור. לדוגמה, קישור ל phpBB.com יתבצע כך:<br /><br /><b>[url=http://www.phpbb.com/]</b>בקרו את phpBB!<b>[/url]</b><br /><br />זה יצור את הקישור הבא, <a href=\"http://www.phpbb.com/\" target=\"_blank\">בקרו את phpBB!</a> אתה תשים לב שהקישור נפתח בחלון חדש כדי שהמשתמש יוכל להמשיך לגלוש בפורומים כרצונו.</li><li>אם ברצונך שהכתובת עצמה תוצג כקישור, ניתן לעשות זאת בקלות כך:<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />זה יצור את הקישור הבא, <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a></li><li>בנוסף phpBB דבר הנקרא <i>קישורים קסומים</i>, זה יהפוך כל כתובת לקישור ללא צורך לציין תגים כלשהם או אפילו http://. לדוגמה, כתיבת www.phpbb.com בתוך ההודעה שלך, תקשר אוטומטית ל <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a> בזמן שצופים בהודעה.</li><li>אותו הדבר בדיוק לגבי כתובות דוא\"ל, ניתן לציין כתובת באופן מיוחד, לדוגמה:<br /><br /><b>[email]</b>no.one@domain.adr<b>[/email]</b><br /><br />אשר יצור <a href=\"emailto:no.one@domain.adr\">no.one@domain.adr</a> או שניתן לכתוב רק no.one@domain.adr בהודעה שלך וזה אוטומטית יומר בצפייה.</li></ul>כמו כל תגי ה BBCode ניתן לכלוא קישורים מסביב לכל תג אחר, כדוגמת <b>[img][/img]</b> (עיין הערך הבא), <b>[b][/b]</b>, וכו'. בנוגע לעיצוב התגים זה תלוי בך לוודא שסדר הפתיחה והסגירה עוקב, לדוגמה:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br />זה <u>לא</u> נכון ויכול להוביל למחיקת הודעתך, אז שים לב.");

$faq[] = array("--", "הצגת תמונות בהודעות");
$faq[] = array("הוספת תמונה להודעה", "ה BBCode של phpBB כולל תג כדי להוסיף תמונות להודעות שלך. שני דברים חשובים מאוד לזכור כאשר משתמשים בתג הזה; הרבה משתמשים לא אוהבים הרבה תמונות שמופיעות בהודעות ושנית התמונה שברצונך להציג חייבת להיות זמינה על האינטרנט (היא איננה יכולה להיות קיימת רק על המחשב שלך לדוגמה, אלא אם אתה מריץ שרת אינטרנט!). אין כרגע שום דרך לשמור תמונות מקומיות ב phpBB (כל הנושאים הללו צפויים להסתדר בהוצאה הבאה של phpBB). כדי להציג תמונה יש להקיף את הכתובת המכילה את התמונה בתגי <b>[img][/img]</b>. לדוגמה:<br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />כפי שצויין בחלק על קישור לאתר ניתן להקיף את התמונה בתגים <b>[url][/url]</b>, לדוגמה: <br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />יצור:<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"http://www.phpbb.com/images/phplogo.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "עיניינים אחרים");
$faq[] = array("אני יכול להוסיף תגים משלי?", "לא, חוששני שלא ישירות מ phpBB 2.0. אנו רוצים להציע תגי BBCode מותאמים אישית לגירסה הבאה.");

//
// This ends the BBCode guide entries
//

?>