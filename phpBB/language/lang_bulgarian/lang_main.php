<?php
/***************************************************************************
 *                            lang_main.php [Bulgarian]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id: lang_main.php,v 1.82 2002/02/03 18:17:08 thefinn Exp $
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

/***************************************************************************
*                     Bulgarian translation
*                              -------------------
*     begin                : Thu Dec 06 2001
*     last update          : Wed Feb 27 2002
*     by                   : Boby Dimitrov
*     email                : boby@azholding.com
****************************************************************************/

$lang['ENCODING'] = "windows-1251";
$lang['DIRECTION'] = "ltr";
$lang['LEFT'] = "left";
$lang['RIGHT'] = "right";
$lang['DATE_FORMAT'] =  "d M Y"; // This should be changed to the default date format for your language, php date() format

// Translator credit
$lang['TRANSLATION_INFO'] = 'Translation by: <a href="http://forums.rpgbg.net" target="_blank">Boby Dimitrov</a>';

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = "Форум";
$lang['Category'] = "Категория";
$lang['Topic'] = "Тема";
$lang['Topics'] = "Теми";
$lang['Reply'] = "Отговор";
$lang['Replies'] = "Отговори";
$lang['Views'] = "Видяна";
$lang['Post'] = "Мнение";
$lang['Posts'] = "Мнения";
$lang['Posted'] = "Пуснато на";
$lang['Username'] = "Потребител";
$lang['Password'] = "Парола";
$lang['Email'] = "Мейл";
$lang['Poster'] = "Poster";
$lang['Author'] = "Автор";
$lang['Time'] = "Време";
$lang['Hours'] = "Часове";
$lang['Message'] = "Съобщение";

$lang['1_Day'] = "1 Ден";
$lang['7_Days'] = "7 Дни";
$lang['2_Weeks'] = "2 Седмици";
$lang['1_Month'] = "1 Месеца";
$lang['3_Months'] = "3 Месеца";
$lang['6_Months'] = "6 Месеца";
$lang['1_Year'] = "1 Година";

$lang['Go'] = "Давай!";
$lang['Jump_to'] = "Идете на";
$lang['Submit'] = "Прати";
$lang['Reset'] = "Изчисти";
$lang['Cancel'] = "Прекрати";
$lang['Preview'] = "Преглед";
$lang['Confirm'] = "Потвърди";
$lang['Spellcheck'] = "Проверка за грешки";
$lang['Yes'] = "Да";
$lang['No'] = "Не";
$lang['Enabled'] = "Вкл.";
$lang['Disabled'] = "Изкл.";
$lang['Error'] = "Грешка";
$lang['Success'] = "Успех";

$lang['Next'] = "Следваща";
$lang['Previous'] = "Предишна";
$lang['Goto_page'] = "Иди на страница";
$lang['Joined'] = "Регистриран на";
$lang['IP_Address'] = "IP Адрес";

$lang['Select_forum'] = "Изберете форум";
$lang['View_latest_post'] = "Вижте последното мнение";
$lang['View_newest_post'] = "Вижте най-новото мнение";
$lang['Page_of'] = "Страница <b>%d</b> от <b>%d</b>"; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = "ICQ Номер";
$lang['AIM'] = "AIM Адрес";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Forum_Index'] = "%s Форуми";  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = "Създайте нова тема";
$lang['Reply_to_topic'] = "Напишете отговор";
$lang['Reply_with_quote'] = "Отговорете с цитат";

$lang['Click_return_topic'] = "Кликнете %sтук%s за да се върнете в темата"; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = "Кликнете %sтук%s за да опитате отново";
$lang['Click_return_forum'] = "Кликнете %sтук%s за да се върнете във форума";
$lang['Click_view_message'] = "Кликнете %sтук%s за да видите съобщението си";
$lang['Click_return_modcp'] = "Кликнете %sтук%s за да се върнете в Модераторския Панел";
$lang['Click_return_group'] = "Кликнете %sтук%s за да се върнете в Информацията за Групата";

$lang['Admin_panel'] = "Влете в Администраторския Панел";

$lang['Board_disable'] = "Затворено за профилактика! Моля опитайте по-късно!";


//
// Global Header strings
//
$lang['Registered_users'] = "Регистрирани потребители:";
$lang['Browsing_forum'] = "Потребители, разглеждащи този форум:";
$lang['Online_users_zero_total'] = "Общо онлайн са <b>0</b> потребители: ";
$lang['Online_users_total'] = "Общо онлайн са <b>%d</b> потребители: ";
$lang['Reg_users_zero_total'] = "0 Регистрирани, ";
$lang['Online_user_total'] = "Онлайн е <b>%d</b> потребител: ";
$lang['Reg_users_total'] = "%d Регистрирани, ";
$lang['Reg_user_total'] = "%d Регистриран, ";
$lang['Hidden_users_zero_total'] = "0 Скрити и ";
$lang['Hidden_users_total'] = "%d Скрити и ";
$lang['Hidden_user_total'] = "%d Скрит и ";
$lang['Guest_users_zero_total'] = "0 Гости";
$lang['Guest_users_total'] = "%d Гости";
$lang['Guest_user_total'] = "%d Гост";
$lang['Record_online_users'] = "Най-много потребители онлайн: <b>%s</b>, на %s"; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = "%sАдминистратор%s";
$lang['Mod_online_color'] = "%sМодератор%s";

$lang['You_last_visit'] = "Последното ви посещение: %s"; // %s replaced by date/time
$lang['Current_time'] = "В момента е: %s"; // %s replaced by time

$lang['Search_new'] = "Вижте всички актуални мнения";
$lang['Search_your_posts'] = "Вижте своите мнения";
$lang['Search_unanswered'] = "Вижте мненията без отговор";

$lang['Register'] = "Регистрирайте се";
$lang['Profile'] = "Профил";
$lang['Edit_profile'] = "Променете профила си";
$lang['Search'] = "Търсене";
$lang['Memberlist'] = "Потребители";
$lang['FAQ'] = "Въпроси/Отговори";
$lang['BBCode_guide'] = "Упътване за BBCode";
$lang['Usergroups'] = "Потребителски групи";
$lang['Last_Post'] = "Последно мнение";
$lang['Moderator'] = "Модератор";
$lang['Moderators'] = "Модератори";


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = "Нашите потребители са написали <b>0</b> мнения"; // Number of posts
$lang['Posted_articles_total'] = "Нашите потребители са написали <b>%d</b> мнения"; // Number of posts
$lang['Posted_article_total'] = "Нашите потребители са написали <b>%d</b> мнение"; // Number of posts
$lang['Registered_users_zero_total'] = "Имаме <b>0</b> регистрирани потребители"; // # registered users
$lang['Registered_users_total'] = "Имаме <b>%d</b> регистрирани потребители"; // # registered users
$lang['Registered_user_total'] = "Имаме <b>%d</b> регистриран потребител"; // # registered users
$lang['Newest_user'] = "Най-новият потребител е <b>%s%s%s</b>"; // a href, username, /a

$lang['No_new_posts_last_visit'] = "Няма нови мнения след последното ви посещение.";
$lang['No_new_posts'] = "Няма нови мнения";
$lang['New_posts'] = "Има нови мнения";
$lang['New_post'] = "Ново мнение";
$lang['No_new_posts_hot'] = "Няма нови (Популярна тема)";
$lang['New_posts_hot'] = "Има нови (Популярна тема)";
$lang['No_new_posts_locked'] = "Няма нови (Заключена тема)";
$lang['New_posts_locked'] = "Има нови (Заключена тема)";
$lang['Forum_is_locked'] = "Форумът е заключен";


//
// Login
//
$lang['Enter_password'] = "Моля въведете името и паролата си!";
$lang['Login'] = "Вход";
$lang['Logout'] = "Изход";

$lang['Forgotten_password'] = "Забравих си паролата!";

$lang['Log_me_in'] = "Искам да влизам автоматично с всяко посещение";

$lang['Error_login'] = "Въвели сте невалидно (или неактивно) потребителско име или парола";

//
// Index page
//
$lang['Index'] = "Индекс";
$lang['No_Posts'] = "Няма мнения";
$lang['No_forums'] = "Няма създадени форуми";

$lang['Private_Message'] = "Лично съобщение";
$lang['Private_Messages'] = "Лични Съобщения";
$lang['Who_is_Online'] = "Кой е онлайн?";

$lang['Mark_all_forums'] = "Маркирай като прочетени всички форуми";
$lang['Forums_marked_read'] = "Всички форуми са маркирани като прочетени";

//
// Viewforum
//
$lang['View_forum'] = "Преглед на форум";

$lang['Forum_not_exist'] = "Форумът, който сте избрали, не съществува";
$lang['Reached_on_error'] = "Тази страница е резултат от грешка!";

$lang['Display_topics'] = "Покажи всички теми от преди";
$lang['All_Topics'] = "Всички теми";

$lang['Topic_Announcement'] = "<b>СЪОБЩЕНИЕ:</b>";
$lang['Topic_Sticky'] = "<b>Важна тема:</b>";
$lang['Topic_Moved'] = "<b>Преместена:</b>";
$lang['Topic_Poll'] = "<b>[Анкета]</b>";

$lang['Mark_all_topics'] = "Маркирай като прочетени всички теми";
$lang['Topics_marked_read'] = "Темите в този форум са маркирани като прочетени";

$lang['Rules_post_can'] = "<b>Можете</b> да пускате нови теми";
$lang['Rules_post_cannot'] = "<b>Не Можете</b> да пускате нови теми";
$lang['Rules_reply_can'] = "<b>Можете</b> да отговаряте на темите";
$lang['Rules_reply_cannot'] = "<b>Не Можете</b> да отговаряте на темите";
$lang['Rules_edit_can'] = "<b>Можете</b> да променяте съобщенията си";
$lang['Rules_edit_cannot'] = "<b>Не Можете</b> да променяте съобщенията си";
$lang['Rules_delete_can'] = "<b>Можете</b> да изтривате съобщенията си";
$lang['Rules_delete_cannot'] = "<b>Не Можете</b> да изтривате съобщенията си";
$lang['Rules_vote_can'] = "<b>Можете</b> да гласувате в анкети";
$lang['Rules_vote_cannot'] = "<b>Не Можете</b> да гласувате в анкети";
$lang['Rules_moderate'] = "<b>Можете</b> да %sмодерирате този форум%s"; // %s replaced by a href links, do not remove!

$lang['No_topics_post_one'] = "В този форум няма теми<br />Кликни на <b>Нова Тема</b>, за да напишеш първата!";


//
// Viewtopic
//
$lang['View_topic'] = "Преглед на тема";

$lang['Guest'] = 'Гост';
$lang['Post_subject'] = "Заглавие";
$lang['View_next_topic'] = "Следващата тема";
$lang['View_previous_topic'] = "Предишната тема";
$lang['Submit_vote'] = "Гласувай!";
$lang['View_results'] = "Вижте резултатите";

$lang['No_newer_topics'] = "Няма по-нови теми в този форум";
$lang['No_older_topics'] = "Няма по-стари теми в този форум";
$lang['Topic_post_not_exist'] = "Темата или мнението, което търсите, не съществува";
$lang['No_posts_topic'] = "Няма мнения в тази тема";

$lang['Display_posts'] = "Покажи мнения от преди";
$lang['All_Posts'] = "Всички мнения";
$lang['Newest_First'] = "Първо най-новите";
$lang['Oldest_First'] = "Първо най-старите";

$lang['Back_to_top'] = "Върнете се в началото";

$lang['Read_profile'] = "Вижте профила на потребителя";
$lang['Send_email'] = "Пратете мейл на потребителя";
$lang['Visit_website'] = "Посетете сайта на потребителя";
$lang['ICQ_status'] = "ICQ Статус";
$lang['Edit_delete_post'] = "Променете/Изтрийте това мнение";
$lang['View_IP'] = "Вижте IP адреса на потребителя";
$lang['Delete_post'] = "Изтрийте това мнение";

$lang['wrote'] = "написа"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "Цитат"; // comes before bbcode quote output.
$lang['Code'] = "Код"; // comes before bbcode code output.

$lang['Edited_time_total'] = "Последната промяна е направена от %s на %s; мнението е било променяно общо %d път"; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = "Последната промяна е направена от %s на %s; мнението е било променяно общо %d пъти"; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = "Заключете тази тема";
$lang['Unlock_topic'] = "Отключете тази тема";
$lang['Move_topic'] = "Преместете тази тема";
$lang['Delete_topic'] = "Изтрийте тази тема";
$lang['Split_topic'] = "Разделете тази тема";

$lang['Stop_watching_topic'] = "Спрете да наблюдавате тази тема за отговори";
$lang['Start_watching_topic'] = "Наблюдавайте тази тема за отговори";
$lang['No_longer_watching'] = "Вече не наблюдавате темата за отговори";
$lang['You_are_watching'] = "Вече наблюдавате темата за отговори. Ще получите мейл, когато някой отговори на темата.";

$lang['Total_votes'] = "Общо гласове";

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "Писане на съобщение";
$lang['Topic_review'] = "Преглед на темата";

$lang['No_post_mode'] = "Не сте избрали тип на съобщението";

$lang['Post_a_new_topic'] = "Пускане на нова тема";
$lang['Post_a_reply'] = "Писане на отговор";
$lang['Post_topic_as'] = "Пускане на тема като";
$lang['Edit_Post'] = "Промяна на мнение";
$lang['Options'] = "Опции";

$lang['Post_Announcement'] = "СЪОБЩЕНИЕ";
$lang['Post_Sticky'] = "Важна тема";
$lang['Post_Normal'] = "Нормална";

$lang['Confirm_delete'] = "Сигурни ли сте, че искате да изтриете това мнение?";
$lang['Confirm_delete_poll'] = "Сигурни ли сте, че искате да изтриете тази анкета?";

$lang['Flood_Error'] = "Не можете да пуснете съобщение, толкова скоро след предишното. Моля изчакайте и опитайте отново след малко";
$lang['Empty_subject'] = "Трябва да въведете заглавие, когато пускате нова тема.";
$lang['Empty_message'] = "Трябва да въведете съобщение";
$lang['Announce_and_sticky'] = "Не можете да пуснете тема, която да е едновременно маркирана като СЪОБЩЕНИЕ и Важна тема";
$lang['Forum_locked'] = "Този форум е заключен - не можете да пускате теми, да отговаряте или да променяте мнения";
$lang['Topic_locked'] = "Тази тема е заключена - не можеш да отговаряте или да променяте мнения";
$lang['No_post_id'] = "Трябва да изберете мнение, което да променяте";
$lang['No_topic_id'] = "Трябва да изберете тема, на която да отговорите";
$lang['No_valid_mode'] = "Можете само да пишете, да отговаряте, да променяте или да отговаряте с цитат на съобщения, моля върнете се обратно и опитайте пак";
$lang['No_such_post'] = "Няма такова мнение, моля върнете се обрато и опитайте пак";
$lang['Edit_own_posts'] = "Можеш само да променяш собствените си мнения";
$lang['Delete_own_posts'] = "Не можете да изтривате чуждите мнения";
$lang['Cannot_delete_replied'] = "Не можете да изтриете мнение, на което е било отговорено";
$lang['Cannot_delete_poll'] = "Не можете да изтриете активна анкета";
$lang['Empty_poll_title'] = "Трябва да въведете заглавие за акнетата";
$lang['To_few_poll_options'] = "Трябва да въведете поне два възможни избора за анкетата";
$lang['To_many_poll_options'] = "Не можете да въведете толкова много изботи за анкетата";
$lang['Post_has_no_poll'] = "Мнението няма прикачена анкета";
$lang['Already_voted'] = 'Вече сте гласували на тази анкета';
$lang['No_vote_option'] = 'Трябва да изберете отговор, когато гласувате';

$lang['Add_poll'] = "Добавете анкета";
$lang['Add_poll_explain'] = "Ако не искате да добавите анкета, оставете всички полета празни";
$lang['Poll_question'] = "Въпрос на анкетата";
$lang['Poll_option'] = "Възможен отговор";
$lang['Add_option'] = "Добавете възможен отговор";
$lang['Update'] = "Обнови";
$lang['Delete'] = "Изтрий";
$lang['Poll_for'] = "Анкетата ще бъде активна за";
$lang['Days'] = "Дни"; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = "(Въведете 0 или оставете празно за вечна анкета)";
$lang['Delete_poll'] = "Изтрий анкетата";

$lang['Disable_HTML_post'] = "Изключи HTML в това мнение";
$lang['Disable_BBCode_post'] = "Изключи BBCode в това мнение";
$lang['Disable_Smilies_post'] = "Изключи Smilies в това мнение";

$lang['HTML_is_ON'] = "HTML е <u>Включен</u>";
$lang['HTML_is_OFF'] = "HTML е <u>Изключен</u>";
$lang['BBCode_is_ON'] = "%sBBCode%s е <u>Включен</u>"; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = "%sBBCode%s е <u>Изключен</u>";
$lang['Smilies_are_ON'] = "Smilies са <u>Включени</u>";
$lang['Smilies_are_OFF'] = "Smilies са <u>Изключени</u>";

$lang['Attach_signature'] = "Добави подпис (подписите се променят от Профила)";
$lang['Notify'] = "Пращай ми мейл, когато някой отговори на темата";
$lang['Delete_post'] = "Изтрий това мнение";

$lang['Stored'] = "Съобщението е изпратено успешно";
$lang['Deleted'] = "Съобщението е изтрито успешно";
$lang['Poll_delete'] = "Анкетата е изтрита успешно";
$lang['Vote_cast'] = "Вашия глас е записан успешно";

$lang['Topic_reply_notification'] = "Уведемояване за отговори по темата";

$lang['bbcode_b_help'] = "Чер текст: [b]текст[/b] (alt+b)";
$lang['bbcode_i_help'] = "Курсив текс: [i]текст[/i] (alt+i)";
$lang['bbcode_u_help'] = "Подчертан текст: [u]текст[/u] (alt+u)";
$lang['bbcode_q_help'] = "Цитат: [quote]текст[/quote] (alt+q)";
$lang['bbcode_c_help'] = "Програмен код: [code]код[/code] (alt+c)";
$lang['bbcode_l_help'] = "Списък: [list]текст[/list] (alt+l)";
$lang['bbcode_o_help'] = "Подреден списък: [list=]текст[/list] (alt+o)";
$lang['bbcode_p_help'] = "Изображение: [img]http://адреса.на.изображението[/img] (alt+p)";
$lang['bbcode_w_help'] = "Връзка: [url]http://url[/url] or [url=http://url]Описание[/url] (alt+w)";
$lang['bbcode_a_help'] = "Затвори всички BBCode тагове";
$lang['bbcode_s_help'] = "Цвят на текста: [color=red]text[/color] Mожете да ползвате и color=#FF0000";
$lang['bbcode_f_help'] = "Размер на текста: [size=x-small]малък текст[/size]";

$lang['Emoticons'] = "Emoticons";
$lang['More_emoticons'] = "Виж всички Emoticons";

$lang['Font_color'] = "Цвят";
$lang['color_default'] = "Основен";
$lang['color_dark_red'] = "Тъмно червен";
$lang['color_red'] = "Червен";
$lang['color_orange'] = "Оранжев";
$lang['color_brown'] = "Кафяв";
$lang['color_yellow'] = "Жълт";
$lang['color_green'] = "Зелен";
$lang['color_olive'] = "Маслинов";
$lang['color_cyan'] = "Цианов";
$lang['color_blue'] = "Син";
$lang['color_dark_blue'] = "Тъмно син";
$lang['color_indigo'] = "Индигов";
$lang['color_violet'] = "Виолетов";
$lang['color_white'] = "Бял";
$lang['color_black'] = "Черен";

$lang['Font_size'] = "Размер";
$lang['font_tiny'] = "Много малък";
$lang['font_small'] = "Малък";
$lang['font_normal'] = "Нормален";
$lang['font_large'] = "Голям";
$lang['font_huge'] = "Огромен";

$lang['Close_Tags'] = "Затвори таговете";
$lang['Styles_tip'] = "Идея: Стиловете могат да бъдат прилагани на селектиран текст";

//
// Private Messaging
//
$lang['Private_Messaging'] = "Лични съобщения";

$lang['Login_check_pm'] = "Влезте, за да видите съобщенията си";
$lang['New_pms'] = "Имате %d нови съобщения"; // You have 2 new messages
$lang['New_pm'] = "Имате %d ново съобщение"; // You have 1 new message
$lang['No_new_pm'] = "Нямате нови съобщения";
$lang['Unread_pms'] = "Имате %d непрочетени съобщения";
$lang['Unread_pm'] = "Имате %d непрочетено съобщяние";
$lang['No_unread_pm'] = "Нямате непрочетени съобщения";
$lang['You_new_pm'] = "Пристигна ново входящо лично съобщение";
$lang['You_new_pms'] = "Пристигнаха нови входящи лични съобщения";
$lang['You_no_new_pm'] = "Нямате нови лични съобщения";

$lang['Unread_message'] = 'Непрочетено съобщение';
$lang['Read_message'] = 'Прочетено съобщение';

$lang['Read_pm'] = 'Прочетете съобщението';
$lang['Post_new_pm'] = 'Пратете ново съобщение';
$lang['Post_reply_pm'] = 'Отговорете на съобщението';
$lang['Post_quote_pm'] = 'Отговорете с цитат на съобщението';
$lang['Edit_pm'] = 'Променете съобщението';

$lang['Inbox'] = "Входящи";
$lang['Outbox'] = "Изпратени";
$lang['Savebox'] = "Съхранени";
$lang['Sentbox'] = "Получени";
$lang['Flag'] = "Флаг";
$lang['Subject'] = "Заглавие";
$lang['From'] = "От";
$lang['To'] = "До";
$lang['Date'] = "Дата";
$lang['Mark'] = "Маркирай";
$lang['Sent'] = "Изпратено";
$lang['Saved'] = "Съхранено";
$lang['Delete_marked'] = "Изтрийте маркираните";
$lang['Delete_all'] = "Изтрийте всички";
$lang['Save_marked'] = "Съхранете маркираните";
$lang['Save_message'] = "Съхранете съобщението";
$lang['Delete_message'] = "Изтрийте съобщението";

$lang['Display_messages'] = "Покажи всички съобщения от преди"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "Всички съобщения";

$lang['No_messages_folder'] = "В тази папка няма съобщения";

$lang['PM_disabled'] = "Личните съобщения са забранени";
$lang['Cannot_send_privmsg'] = "Администраторите са забранили изпращането на лични съобщения. Извиняваме се за неудобството";
$lang['No_to_user'] = "Трябва да въведете потребителско име, за да изпратите това съобщение";
$lang['No_such_user'] = "Няма такъв потребител";

$lang['Disable_HTML_pm'] = "Изключете HTML в това съобщение";
$lang['Disable_BBCode_pm'] = "Изключете BBCode в това съобщение";
$lang['Disable_Smilies_pm'] = "Изключете Smilies в това съобщение";

$lang['Message_sent'] = "Съобщението беше изпратено";

$lang['Click_return_inbox'] = "Кликнете %sтук%s, за да се върнете в Входящи";
$lang['Click_return_index'] = "Кликнете %sтук%s за да се върнете на форумите";

$lang['Send_a_new_message'] = "Изпратете ново лично съобщение";
$lang['Send_a_reply'] = "Отговорете на личното съобщение";
$lang['Edit_message'] = "Променете личното съобщение";

$lang['Notification_subject'] = "Получихте ново лично съобщения";

$lang['Find_username'] = "Намерете потребител";
$lang['Find'] = "Търси";
$lang['No_match'] = "Няма открити потребители";

$lang['No_post_id'] = "Не е посочено ID на съобщението";
$lang['No_such_folder'] = "Няма такава папка";
$lang['No_folder'] = "Не е посочена папка";

$lang['Mark_all'] = "Маркирай всички";
$lang['Unmark_all'] = "Размаркирай всички";

$lang['Confirm_delete_pm'] = "Сигурни ли се, че искате да изтриете това съобщение?";
$lang['Confirm_delete_pms'] = "Сигурни ли се, че искате да изтриете тези съобщения?";

$lang['Inbox_size'] = "Папката ви Входящи е %d%% пълна"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "Папката ви Получени е %d%% пълна";
$lang['Savebox_size'] = "Папката ви Съхранени е %d%% пълна";

$lang['Click_view_privmsg'] = "Кликнете %sтук%s, за да видите папката Входящи";


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "Профил на %s"; // %s is username
$lang['About_user'] = "Информация за %s"; // %s is username

$lang['Preferences'] = "Преференции";
$lang['Items_required'] = "Полетата, маркирани с * са задължителни";
$lang['Registration_info'] = "Регистрационна информация";
$lang['Profile_info'] = "Профил информация";
$lang['Profile_info_warn'] = "Тази информация ще бъде публично достъпна";
$lang['Avatar_panel'] = "Контролен панел за Аватари";
$lang['Avatar_gallery'] = "Галерия с Аватари";

$lang['Website'] = "Сайт";
$lang['Location'] = "Местожителство";
$lang['Contact'] = "Връзки";
$lang['Email_address'] = "Мейл адрес";
$lang['Email'] = "Мейл";
$lang['Send_private_message'] = "Изпратете лично съобщение";
$lang['Hidden_email'] = "[ Скрит мейл ]";
$lang['Search_user_posts'] = "Вижте всички мнения на потребителя";
$lang['Interests'] = "Интереси";
$lang['Occupation'] = "Професия";
$lang['Poster_rank'] = "Ранк във Форума";

$lang['Total_posts'] = "Общо мнения";
$lang['User_post_pct_stats'] = "%.2f%% от всички"; // 1.25% of total
$lang['User_post_day_stats'] = "%.2f мнения на ден"; // 1.5 posts per day
$lang['Search_user_posts'] = "Вижте всички мнения на %s"; // Find all posts by username

$lang['No_user_id_specified'] = "Няма такъв потребител";
$lang['Wrong_Profile'] = "Не можете да променяте чужд профил!";
$lang['Only_one_avatar'] = "Можете да изберете само един вид аватар";
$lang['File_no_data'] = "Файлът , чийто адрес сте въвели, не същестува";
$lang['No_connection_URL'] = "Адреса, който сте въвели, не може да бъде открит";
$lang['Incomplete_URL'] = "Адреса, който сте въвели е непълен";
$lang['Wrong_remote_avatar_format'] = "Адреса, който сте въведи не е валиден";
$lang['No_send_account_inactive'] = "Паролата ви не може да бъде доставена, защото в момента акаунта ви е неактивен. Моля свържете се с администраторите на форума за повече информация";

$lang['Always_smile'] = "Винаги разрешавай Smilies";
$lang['Always_html'] = "Винаги разрешавай HTML";
$lang['Always_bbcode'] = "Винаги разрешавай BBCode";
$lang['Always_add_sig'] = "Винаги прилагай подписа ми";
$lang['Always_notify'] = "Винаги ме уведомявай за отговори";
$lang['Always_notify_explain'] = "Изпраща мейл, когато някой отговори на тема, която сте пуснали. Тази опция може да бъде променена при всяко ваше мнение";

$lang['Board_style'] = "Стил на Борда";
$lang['Board_lang'] = "Език на Борда";
$lang['No_themes'] = "Няма въведени форум-теми в базата";
$lang['Timezone'] = "Часова зона";
$lang['Date_format'] = "Формат на датата";
$lang['Date_format_explain'] = "Синтаксисът е индентичен с функцията <a href=\"http://www.php.net/date\" target=\"_other\">date()</a> на PHP.";
$lang['Signature'] = "Подпис";
$lang['Signature_explain'] = "Това е текстови блок, който може да бъде прикачан в края на съобщенията, които пускате. Има ограничение от %d символа";
$lang['Public_view_email'] = "Винаги показвай мейл адреса ми";

$lang['Current_password'] = "Текуща парола";
$lang['New_password'] = "Нова парола";
$lang['Confirm_password'] = "Потвърдете паролата";
$lang['Confirm_password_explain'] = "Трябва да потвърдите вашата текуща парола, ако желаете да я смените или да промените мейл адреса си!";
$lang['password_if_changed'] = "Въведете парола само ако искате да я смените!";
$lang['password_confirm_if_changed'] = "Потвърдете паролата само ако я сменяте!";

$lang['Avatar'] = "Аватар";
$lang['Avatar_explain'] = "Показва малка картинка под вашите детайли в съобщенията. Само една картинка може да бъде показвана, с основа не по-голяма от %d пиксела, височина не по-голяма от %d пиксела и размер на файла не по-голям от %dkB.";
$lang['Upload_Avatar_file'] = "Качете Аватар от вашия компютър на форума";
$lang['Upload_Avatar_URL'] = "Качете Аватар от URL на форума";
$lang['Upload_Avatar_URL_explain'] = "Въведете адреса на Аватара, от където той ще бъде копиран тук.";
$lang['Pick_local_Avatar'] = "Изберете Аватар от галерията";
$lang['Link_remote_Avatar'] = "Динамичен Аватар";
$lang['Link_remote_Avatar_explain'] = "Въведете URL, от където Аватара ще се зарежда директно.";
$lang['Avatar_URL'] = "Адрес на Аватара";
$lang['Select_from_gallery'] = "Изберете Аватар от галерията";
$lang['View_avatar_gallery'] = "Вижте галерията";

$lang['Select_avatar'] = "Изберете Аватар";
$lang['Return_profile'] = "Върнете се обратно";
$lang['Select_category'] = "Изберете категория";

$lang['Delete_Image'] = "Изтрите Аватара";
$lang['Current_Image'] = "Текущ Аватар";

$lang['Notify_on_privmsg'] = "Уведомяване при ново лично съобщение";
$lang['Popup_on_privmsg'] = "Отвори прозорец при ново лично съобщение";
$lang['Popup_on_privmsg_explain'] = "Някой форум-теми могат да отварят нов прозорец, за да ви информират когато пристигне ново лично съобщение.";
$lang['Hide_user'] = "Скриване на вашия онлайн статус";

$lang['Profile_updated'] = "Профилът ви е обновен";
$lang['Profile_updated_inactive'] = "Профилът ви е обновен, но тъй като сте променили някой много важни полета, акаунтът ви е деактивиран. Проверете мейла си за информация как да го активирате отново. Ако се налага активиране от администратор, моля изчакайте администраторите да активират акаунта ви.";

$lang['Password_mismatch'] = "Паролите, които сте въвели не съвпадат";
$lang['Current_password_mismatch'] = "Паролата, която сте въвели не съвпада с тази в базата";
$lang['Password_long'] = "Паролата ви неможе да е по-дълга от 32 символа!";
$lang['Username_taken'] = "Потребителското име, което сте въвели е заето!";
$lang['Username_invalid'] = "Потребителското име, което сте въвели е заето, забранено, или съдържа невалидни символи, като например \" !";
$lang['Username_disallowed'] = "Потребителското име, което сте въвели е забранено!";
$lang['Email_taken'] = "Мейл адреса, който сте въвели, е вече използван от друг потребител!";
$lang['Email_banned'] = "Мейл адреса, който сте въвели, е баннат!";
$lang['Email_invalid'] = "Мейл адреса, който сте въвели, е невалиден!";
$lang['Signature_too_long'] = "Подписът ви е прекалено дълъг";
$lang['Fields_empty'] = "Трябва да попълните задължителните полета";
$lang['Avatar_filetype'] = "Аватара трябва да е .jpg, .gif или .png";
$lang['Avatar_filesize'] = "Размера на файла на Аватара трябва да е по-малко от %dkB"; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = "Аватара трябва да има основа по-малка от %d пиксела и височина по-малка от %d пиксела";

$lang['Welcome_subject'] = "Добре дошли на %s Форумите"; // Welcome to my.com forums
$lang['New_account_subject'] = "Нов потребител";
$lang['Account_activated_subject'] = "Потребителя е активиран";

$lang['Account_added'] = "Благодарим ви, че се регистрирахте. Вашия потребител е създаден. Можете да влезете с вашите име и парола";
$lang['Account_inactive'] = "Вашият потребител е създаден. Тези форуми изискват лично активиране на потребителя и в тази връзка на мейла ви е изпратен ключ за активиране. Моля проверете мейла си за повече информация";
$lang['Account_inactive_admin'] = "Вашият потребител е създаден. Тези форуми изискват активиране на потребителя от администратор и в тази връзка на администраторите е изпратен мейл. Ще бъдете информиран, когато активират потребителя ви.";
$lang['Account_active'] = "Потребителят ви е активиран. Благодарим ви, че се регистрирахте.";
$lang['Account_active_admin'] = "Потребителят ви е активиран.";
$lang['Reactivate'] = "Ре-активирайте потребителя си!";
$lang['Already_activated'] = 'Вече сте активирали потребителя си';
$lang['COPPA'] = "Потребителя ви създаден, но трябва да бъде одобрен. Моля проверете мейла си за повече информация.";

$lang['Registration'] = "Условия за регистрация";
$lang['Reg_agreement'] = "Въпреки, че администраторите и модераторите на този форум ще се опитат да премахнат или да променят възмжно най-бързо всеки материал, носещ вреда, невъзможно е да бъдат прегледани всички съобщения. Вие разбирате, че всички съобщения на тези форуми изразяват личното мнение на съответните им автори, а не на администраторите, модераторите или уебмастъра (като изключим съобщенията, пуснати от тези хора), и следователно те не носят никаква отговорност.<br /><br />Приемате се да не пишете никакъв груб, неприличен, вулгарен, заплашителен, сексуално-ориентиран или всякакъв друг материал, който нарушава законите. Такова поведение може да доведе до моменталното и постоянното ви изгонване от форумите (както и уведомяването на вашия доставчик). IP адресите, от които са направени всички съобщения се записват и могат да бъдат използвани в такива случаи. Приемате, че уебмастъра, администратора и модераторите на този форум  имат правото да премахват, променят или заключват всяка тема по всяко време, ако намерят за уместно. Като потребител одобрявате записването на всяка информация, която въведете, във база данни. Въпреки, че тази информация няма да бъде предоставяна на трети страни без вашето одобрение, уебмастъра, администратора и модераторите на този форум не могат да бъдат отговорни за всякакви хакерски атаки, които могат да доведат до разкриване на данните.<br /><br />Тази форум система използва cookies, за да записва информация на вашия компютър. Тези cookies не съдържат никаква информация за вас; използват се само за да подобрят функционалността на форумите. Мейл адреса ви се използва само за потвърждение на детайлите по регистрацията и паролата ви (и за изпращане на нови пароли, ако случайно забравите текущата си).<br /><br />Избирайки <b>Съгласен съм...</b> вие приемате горепосочените условия";

$lang['Agree_under_13'] = "Съгласен съм със тези условия и възрастта ми е <b>под</b> 13 години";
$lang['Agree_over_13'] = "Съгласен съм със тези условия и възрастта ми е <b>над</b> 13 години";
$lang['Agree_not'] = "Не съм съгласен с тези условия";

$lang['Wrong_activation'] = "Ключът за активация, който сте въвели не съвпада с базата данни!";
$lang['Send_password'] = "Изпратете ми нова парола!";
$lang['Password_updated'] = "Зададена ви е нова парола. Моля проверете мейла си за иформация как да я активирате!";
$lang['No_email_match'] = "Мейл адреса, който сте въвели, не съвпада със записания мейл за това потребителско име";
$lang['New_password_activation'] = "Активиране на нова парола";
$lang['Password_activated'] = "Потребителят ви е ре-активиран. Влезте с новата парола, която получихте по мейла.";

$lang['Send_email_msg'] = "Изпратете мейл";
$lang['No_user_specified'] = "Трябва да изберете потребител";
$lang['User_prevent_email'] = "Този потребител предпочита да не получава поща. Можете да му изпратите лично съобщение";
$lang['User_not_exist'] = "Този потребител не съществува";
$lang['CC_email'] = "Изпратете копие на мейла до себе си";
$lang['Email_message_desc'] = "Това съобщение ще бъде изпратено като plain text, не включвайте HTML или BBCode. Адреса на подателя ще бъде вашия мейл адрес.";
$lang['Flood_email_limit'] = "Не можете да изпратите нов мейл толкова скоро. Моля изчакайте няколко минути";
$lang['Recipient'] = "Получател";
$lang['Email_sent'] = "Мейлът е изпратен";
$lang['Send_email'] = "Изпрати мейла";
$lang['Empty_subject_email'] = "Трябва да въведете заглавие на мейла";
$lang['Empty_message_email'] = "Трябва да въведете съобщение, което да бъде изпратено";


//
// Memberslist
//
$lang['Select_sort_method'] = "Изберете метод на сортиране";
$lang['Sort'] = "Сортирай";
$lang['Sort_Top_Ten'] = "10-те най-активни";
$lang['Sort_Joined'] = "Дата на записване";
$lang['Sort_Username'] = "Потребителско име";
$lang['Sort_Location'] = "Местожителство";
$lang['Sort_Posts'] = "Общо мнения";
$lang['Sort_Email'] = "Мейл адрес";
$lang['Sort_Website'] = "Сайт";
$lang['Sort_Ascending'] = "Възходящ ред";
$lang['Sort_Descending'] = "Низходящ ред";
$lang['Order'] = "Ред";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "Контролен панел на групата";
$lang['Group_member_details'] = "Детайли за групата";
$lang['Group_member_join'] = "Присъединяване към група";

$lang['Group_Information'] = "Информация за групата";
$lang['Group_name'] = "Име на групата";
$lang['Group_description'] = "Описание на групата";
$lang['Group_membership'] = "Членство в групата";
$lang['Group_Members'] = "Членове на групата";
$lang['Group_Moderator'] = "Модератор на групата";
$lang['Pending_members'] = "Чакащи кандидатури";

$lang['Group_type'] = "Тип на групата";
$lang['Group_open'] = "Отворена група";
$lang['Group_closed'] = "Затворена група";
$lang['Group_hidden'] = "Скрита група";

$lang['Current_memberships'] = "Групи, в които сте член";
$lang['Non_member_groups'] = "Групи, в които не сте член";
$lang['Memberships_pending'] = "Чакащи кандидатури";

$lang['No_groups_exist'] = "Няма създадени групи";
$lang['Group_not_exist'] = "Тази група не съществува";

$lang['Join_group'] = "Присъединете се към групата";
$lang['No_group_members'] = "Тази група няма членове";
$lang['Group_hidden_members'] = "Тази група е скрита - не можете да видите членовете й";
$lang['No_pending_group_members'] = "Тази група няма чакащи кандидатури";
$lang["Group_joined"] = "Заявката ви за приемане в групата е изпратена успешно<br />Ще бъдете уведомени, когато кандидатурата ви бъде одобрена от модератора на групата";
$lang['Group_request'] = "Получена е заявка за приемане в групата ви";
$lang['Group_approved'] = "Вашата кандидатура е одобрена";
$lang['Group_added'] = "Приет сте за член на тази група";
$lang['Already_member_group'] = "Вече сте член на тази група";
$lang['User_is_member_group'] = "Потребителя вече е член на тази група";
$lang['Group_type_updated'] = "Типа на групата е променен успешно";

$lang['Could_not_add_user'] = "Избрания потребител не съществува";
$lang['Could_not_anon_user'] = "Не можете да добавяте анонимни потребители към групата";

$lang['Confirm_unsub'] = "Сигурни ли сте, че искате да напуснете групата?";
$lang['Confirm_unsub_pending'] = "Вашата кандидатура все още не е одобрена, сигурни ли сте, че искате да напуснете групата?";

$lang['Unsub_success'] = "Успешно напуснахте тази група";

$lang['Approve_selected'] = "Одобрете селектираните";
$lang['Deny_selected'] = "Отхвърлете селетираните";
$lang['Not_logged_in'] = "Трябва да сте влезли, за да се присъедините към дадена група";
$lang['Remove_selected'] = "Премахнете селектираните";
$lang['Add_member'] = "Добавете член";
$lang['Not_group_moderator'] = "Не можете да изпълните това действие, защото не сте модератор на тази група";

$lang['Login_to_join'] = "Влезте, за да можете да се присъедините към групата или да я модерирате";
$lang['This_open_group'] = "Това е отворена група, кликнете, за да кандидатствате за членство";
$lang['This_closed_group'] = "Това е затворена група, нови членове не се приемат";
$lang['This_hidden_group'] = "Това е скрита група, автоматичното добавяне на потребители не е разрешено";
$lang['Member_this_group'] = "Вие сте член на тази група";
$lang['Pending_this_group'] = "Кандидатурата ви за тази група чака одобрение";
$lang['Are_group_moderator'] = "Вие сте модератора на групата";
$lang['None'] = "Нула";

$lang['Subscribe'] = "Запишете се";
$lang['Unsubscribe'] = "Отпишете се";
$lang['View_Information'] = "Информация";


//
// Search
//
$lang['Search_query'] = "Критерии за търсене";
$lang['Search_options'] = "Опции";

$lang['Search_keywords'] = "Търсете по думи";
$lang['Search_keywords_explain'] = "Ползвайте <u>AND</u>, за да определите думи, които трябва да присъстват в резултатите, <u>OR</u> за такива, които могат да са в резултатите и <u>NOT</u> за думи, които не трябва да са в резултатите. Можете да ползвайте * като маска. Пример: *ива* връща Иванов, отбивам и коприва.";
$lang['Search_author'] = "Тръсете по автор";
$lang['Search_author_explain'] = "Можете да ползвайте * като маска.";

$lang['Search_for_any'] = "Търси за коя да е от въведените думи";
$lang['Search_for_all'] = "Търси за всички въведени думи";

$lang['Search_title_msg'] = "Търси в заглавието и съдържанието на мненията";
$lang['Search_msg_only'] = "Търси само в съдържанието на мненията";

$lang['Return_first'] = "Покажи първите"; // followed by xxx characters in a select box
$lang['characters_posts'] = "символа от мнението";

$lang['Search_previous'] = "От преди"; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = "Сортирай по";
$lang['Sort_Time'] = "Време";
$lang['Sort_Post_Subject'] = "Заглавие на мнение";
$lang['Sort_Topic_Title'] = "Заглавие на тема";
$lang['Sort_Author'] = "Автор";
$lang['Sort_Forum'] = "Форум";

$lang['Display_results'] = "Покажи резултатите като";
$lang['All_available'] = "Всички налични";
$lang['No_searchable_forums'] = "Не ви е разрешено да търсите из тези форуми!";

$lang['No_search_match'] = "Няма теми или мнения, които да отговарят на вашите критерии";
$lang['Found_search_match'] = "Търсенето даде %d резултат"; // eg. Search found 1 match
$lang['Found_search_matches'] = "Търсенето даде %d резултата"; // eg. Search found 24 matches

$lang['Close_window'] = "Затворете прозореца";


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = "Само %s могат да пускат СЪОБЩЕНИЯ в този форум";
$lang['Sorry_auth_sticky'] = "Само %s могат да пускат Важни теми в този форум";
$lang['Sorry_auth_read'] = "Само %s могат да четат теми в този форум";
$lang['Sorry_auth_post'] = "Само %s могат да пускат теми в този форум";
$lang['Sorry_auth_reply'] = "Само %s могат да отговарят в този форум";
$lang['Sorry_auth_edit'] = "Само %s могат да променят мнения в този форум";
$lang['Sorry_auth_delete'] = "Само %s могат да изтриват мнения в този форум";
$lang['Sorry_auth_vote'] = "Само %s canмогат да гласуват на анкети в този форум";

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = "<b>Анонимни потребители</b>";
$lang['Auth_Registered_Users'] = "<b>Регистрирани потребители</b>";
$lang['Auth_Users_granted_access'] = "<b>Потребители със специални права</b>";
$lang['Auth_Moderators'] = "<b>Модератори</b>";
$lang['Auth_Administrators'] = "<b>Администратори</b>";

$lang['Not_Moderator'] = "Вие не сте модератор на този форум";
$lang['Not_Authorised'] = "Нямате разрешение";

$lang['You_been_banned'] = "Вие сте изгонен от този форум!<br />Моля свържете се с администраторите за повече информация.";


//
// Viewonline
//
$lang['Reg_users_zero_online'] = "Има 0 Регистрирани и "; // There ae 5 Registered and
$lang['Reg_users_online'] = "Има %d Регистрирани и "; // There ae 5 Registered and
$lang['Reg_user_online'] = "Има %d Регистриран и "; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = "0 Скрити потребители онлайн"; // 6 Hidden users online
$lang['Hidden_users_online'] = "%d Скрити потребители онлайн"; // 6 Hidden users online
$lang['Hidden_user_online'] = "%d Скрит потребител онлайн"; // 6 Hidden users online
$lang['Guest_users_online'] = "Има %d Гости онлайн"; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = "Има 0 Гости онлайн"; // There are 10 Guest users online
$lang['Guest_user_online'] = "Има %d Гост онлайн"; // There is 1 Guest user online
$lang['No_users_browsing'] = "Няма потребители, разглеждащи този форум";

$lang['Online_explain'] = "Тези данни са базирани на активността на потребителите през последните 5 минути";

$lang['Forum_Location'] = "Къде се намира";
$lang['Last_updated'] = "Послено обновяване";

$lang['Forum_index'] = "Главната страница";
$lang['Logging_on'] = "Влиза";
$lang['Posting_message'] = "Пише мнение";
$lang['Searching_forums'] = "Търси из форумите";
$lang['Viewing_profile'] = "Разглежда профил";
$lang['Viewing_online'] = "Проверява кой е онлайн";
$lang['Viewing_member_list'] = "Гледа списъка с членовете";
$lang['Viewing_priv_msgs'] = "Чете личните си съобщения";
$lang['Viewing_FAQ'] = "Чете FAQ";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "Модераторски панел";
$lang['Mod_CP_explain'] = "С помощта на формата по-долу можете да извършвате масова модерация на този форум. Можете да заключвате, отключвате, премествате или изтривате избрани теми.";

$lang['Select'] = "Маркирай";
$lang['Delete'] = "Изтрий";
$lang['Move'] = "Премести";
$lang['Lock'] = "Заключи";
$lang['Unlock'] = "Отключи";

$lang['Topics_Removed'] = "Избраните теми са премахнати от базата.";
$lang['Topics_Locked'] = "Избраните теми са заключени";
$lang['Topics_Moved'] = "Избраните теми са преместени";
$lang['Topics_Unlocked'] = "Избраните теми са отключени";

$lang['Confirm_delete_topic'] = "Сигурни ли сте, че искате да изтриете избраните теми?";
$lang['Confirm_lock_topic'] = "Сигурни ли сте, че искате да заключите избраните теми?";
$lang['Confirm_unlock_topic'] = "Сигурни ли сте, че искате да отключите избраните теми?";
$lang['Confirm_move_topic'] = " Сигурни ли сте, че искате да преместите избраните?";

$lang['Move_to_forum'] = "Преместете във форум";
$lang['Leave_shadow_topic'] = "Остави линк към преместения топик в стария форум";

$lang['Split_Topic'] = "Панел за разделяне на теми";
$lang['Split_Topic_explain'] = "С помощта на формата по-долу можете да разделяте теми на две, като поставите избраните мнения в нови теми или просто разделите темата на две след избрано мнение.";
$lang['Split_title'] = "Заглавие на новата тема";
$lang['Split_forum'] = "Форум за новата тема";
$lang['Split_posts'] = "Разделете избраните мнения";
$lang['Split_after'] = "Разделете темата след избраното мнение";
$lang['Topic_split'] = "Темата е успешно разделена";

$lang['Too_many_error'] = "Селектирали сте прекалено много мнения! Трябва да изберете само едно мнение, след което искате да разделите темата!";

$lang['None_selected'] = "Не сте избрали тема, на която да приложите тази операция! Моля върнете се обратно и изберете поне една.";
$lang['New_forum'] = "Нов форум";

$lang['This_posts_IP'] = "IP адрес за това мнение";
$lang['Other_IP_this_user'] = "Други IP адреси, от които е писал този потребител";
$lang['Users_this_IP'] = "Потребители, писали от този IP адрес";
$lang['IP_info'] = "Информация за IP адреса";
$lang['Lookup_IP'] = "Виж IP адреса";


//
// Timezones ... for display on each page
//
//
// Timezones ... for display on each page
//
$lang['All_times'] = 'Часовете са според зоната %s'; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = 'GMT - 12 Часа';
$lang['-11'] = 'GMT - 11 Часа';
$lang['-10'] = 'GMT - 10 Часа';
$lang['-9'] = 'GMT - 9 Часа';
$lang['-8'] = 'GMT - 8 Часа';
$lang['-7'] = 'GMT - 7 Часа';
$lang['-6'] = 'GMT - 6 Часа';
$lang['-5'] = 'GMT - 5 Часа';
$lang['-4'] = 'GMT - 4 Часа';
$lang['-3.5'] = 'GMT - 3.5 Часа';
$lang['-3'] = 'GMT - 3 Часа';
$lang['-2'] = 'GMT - 2 Часа';
$lang['-1'] = 'GMT - 1 Час';
$lang['0'] = 'GMT';
$lang['1'] = 'GMT + 1 Hour';
$lang['2'] = 'GMT + 2 Часа';
$lang['3'] = 'GMT + 3 Часа';
$lang['3.5'] = 'GMT + 3.5 Часа';
$lang['4'] = 'GMT + 4 Часа';
$lang['4.5'] = 'GMT + 4.5 Часа';
$lang['5'] = 'GMT + 5 Часа';
$lang['5.5'] = 'GMT + 5.5 Часа';
$lang['6'] = 'GMT + 6 Часа';
$lang['6.5'] = 'GMT + 6.5 Часа';
$lang['7'] = 'GMT + 7 Часа';
$lang['8'] = 'GMT + 8 Часа';
$lang['9'] = 'GMT + 9 Часа';
$lang['9.5'] = 'GMT + 9.5 Часа';
$lang['10'] = 'GMT + 10 Часа';
$lang['11'] = 'GMT + 11 Часа';
$lang['12'] = 'GMT + 12 Часа';

// These are displayed in the timezone select box
$lang['tz']['-12'] = 'GMT - 12 Часа';
$lang['tz']['-11'] = 'GMT - 11 Часа';
$lang['tz']['-10'] = 'GMT - 10 Часа';
$lang['tz']['-9'] = 'GMT - 9 Часа';
$lang['tz']['-8'] = 'GMT - 8 Часа';
$lang['tz']['-7'] = 'GMT - 7 Часа';
$lang['tz']['-6'] = 'GMT - 6 Часа';
$lang['tz']['-5'] = 'GMT - 5 Часа';
$lang['tz']['-4'] = 'GMT - 4 Часа';
$lang['tz']['-3.5'] = 'GMT - 3.5 Часа';
$lang['tz']['-3'] = 'GMT - 3 Часа';
$lang['tz']['-2'] = 'GMT - 2 Часа';
$lang['tz']['-1'] = 'GMT - 1 Час';
$lang['tz']['0'] = 'GMT';
$lang['tz']['1'] = 'GMT + 1 Час';
$lang['tz']['2'] = 'GMT + 2 Часа';
$lang['tz']['3'] = 'GMT + 3 Часа';
$lang['tz']['3.5'] = 'GMT + 3.5 Часа';
$lang['tz']['4'] = 'GMT + 4 Часа';
$lang['tz']['4.5'] = 'GMT + 4.5 Часа';
$lang['tz']['5'] = 'GMT + 5 Часа';
$lang['tz']['5.5'] = 'GMT + 5.5 Часа';
$lang['tz']['6'] = 'GMT + 6 Часа';
$lang['tz']['6.5'] = 'GMT + 6.5 Часа';
$lang['tz']['7'] = 'GMT + 7 Часа';
$lang['tz']['8'] = 'GMT + 8 Часа';
$lang['tz']['9'] = 'GMT + 9 Часа';
$lang['tz']['9.5'] = 'GMT + 9.5 Часа';
$lang['tz']['10'] = 'GMT + 10 Часа';
$lang['tz']['11'] = 'GMT + 11 Часа';
$lang['tz']['12'] = 'GMT + 12 Часа';

$lang['datetime']['Sunday'] = "Неделя";
$lang['datetime']['Monday'] = "Понеделник";
$lang['datetime']['Tuesday'] = "Вторник";
$lang['datetime']['Wednesday'] = "Сряда";
$lang['datetime']['Thursday'] = "Четвъртък";
$lang['datetime']['Friday'] = "Петък";
$lang['datetime']['Saturday'] = "Събота";
$lang['datetime']['Sun'] = "Нед";
$lang['datetime']['Mon'] = "Пон";
$lang['datetime']['Tue'] = "Вто";
$lang['datetime']['Wed'] = "Сря";
$lang['datetime']['Thu'] = "Чет";
$lang['datetime']['Fri'] = "Пет";
$lang['datetime']['Sat'] = "Съб";
$lang['datetime']['January'] = "Януари";
$lang['datetime']['February'] = "Февруари";
$lang['datetime']['March'] = "Март";
$lang['datetime']['April'] = "Април";
$lang['datetime']['May'] = "Май";
$lang['datetime']['June'] = "Юни";
$lang['datetime']['July'] = "Юли";
$lang['datetime']['August'] = "Август";
$lang['datetime']['September'] = "Септември";
$lang['datetime']['October'] = "Октомври";
$lang['datetime']['November'] = "Ноември";
$lang['datetime']['December'] = "Декември";
$lang['datetime']['Jan'] = "Яну";
$lang['datetime']['Feb'] = "Фев";
$lang['datetime']['Mar'] = "Мар";
$lang['datetime']['Apr'] = "Апр";
$lang['datetime']['May'] = "Май";
$lang['datetime']['Jun'] = "Юни";
$lang['datetime']['Jul'] = "Юли";
$lang['datetime']['Aug'] = "Авг";
$lang['datetime']['Sep'] = "Сеп";
$lang['datetime']['Oct'] = "Окт";
$lang['datetime']['Nov'] = "Ное";
$lang['datetime']['Dec'] = "Дек";

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = "Информация";
$lang['Critical_Information'] = "Критична информация";

$lang['General_Error'] = "Обща Грешка";
$lang['Critical_Error'] = "Критична Грешка";
$lang['An_error_occured'] = "Натъкнахте се на Грешка! Моля уведомете администраторите!";
$lang['A_critical_error'] = "Натъкнахте се на Критична Грешка! Моля незабавно уведомете администраторите!";

//
// That's all Folks!
// -------------------------------------------------

?>