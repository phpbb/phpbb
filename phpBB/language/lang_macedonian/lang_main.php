<?php
/***************************************************************************
 *                            lang_main.php [Macedonian]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id$
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
*                     Macedonian Translation
*                              -------------------
*     begin                : Friday Feb 07 2003
*     last update          : Friday Apr 18 2003
*     by                   : Boban Stoyanov
*     email                : bobanstojanov@msn.com
*     website:             : http://www.boban.tk/, http://www.room419.tk/
****************************************************************************/

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

$lang['ENCODING'] = 'windows-1251';
$lang['DIRECTION'] = 'ltr';
$lang['LEFT'] = 'left';
$lang['RIGHT'] = 'right';
$lang['DATE_FORMAT'] =  'd M Y'; // This should be changed to the default date format for your language, php date() format

// This is optional, if you would like a _SHORT_ message output
// along with our copyright message indicating you are the translator
// please add it here.
$lang['TRANSLATION_INFO'] = 'This forum is translated in Macedonian by: <a href="http://www.boban.tk/" target="_blank">Boban Stojanov</a>';

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = 'Форум';
$lang['Category'] = 'Категорија';
$lang['Topic'] = 'Тема';
$lang['Topics'] = 'Теми';
$lang['Replies'] = 'Одговори';
$lang['Views'] = 'Видено е';
$lang['Post'] = 'Мислење';
$lang['Posts'] = 'Мислења';
$lang['Posted'] = 'Пуштено на';
$lang['Username'] = 'Член';
$lang['Password'] = 'Лозинка';
$lang['Email'] = 'е-маил';
$lang['Poster'] = 'Постер';
$lang['Author'] = 'Автор';
$lang['Time'] = 'Време';
$lang['Hours'] = 'Час';
$lang['Message'] = 'Порака';

$lang['1_Day'] = '1 Ден';
$lang['7_Days'] = '7 Дена';
$lang['2_Weeks'] = '2 Недели';
$lang['1_Month'] = '1 Месец';
$lang['3_Months'] = '3 Месеци';
$lang['6_Months'] = '6 Месеци';
$lang['1_Year'] = '1 Година';

$lang['Go'] = 'Ајде!';
$lang['Jump_to'] = 'Отиди до';
$lang['Submit'] = 'Испрати';
$lang['Reset'] = 'Избриши';
$lang['Cancel'] = 'Прекрати';
$lang['Preview'] = 'Преглед';
$lang['Confirm'] = 'Потврди';
$lang['Spellcheck'] = 'Проверка за грешки';
$lang['Yes'] = 'Да';
$lang['No'] = 'Не';
$lang['Enabled'] = 'Вклучено';
$lang['Disabled'] = 'Исклучено';
$lang['Error'] = 'Грешка';

$lang['Next'] = 'Наредно';
$lang['Previous'] = 'Претходно';
$lang['Goto_page'] = 'Отиди на страна';
$lang['Joined'] = 'Регистриран на';
$lang['IP_Address'] = 'IP Адреса';

$lang['Select_forum'] = 'Одбери форум';
$lang['View_latest_post'] = 'Видете го последното мислење';
$lang['View_newest_post'] = 'Видете го најновото мислење';
$lang['Page_of'] = 'Страна <b>%d</b> of <b>%d</b>'; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = 'ICQ Број';
$lang['AIM'] = 'AIM Адреса';
$lang['MSNM'] = 'MSN Messenger';
$lang['YIM'] = 'Yahoo Messenger';

$lang['Forum_Index'] = '%s Форуми';  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = 'Создај нова тема';
$lang['Reply_to_topic'] = 'Одговори на тема';
$lang['Reply_with_quote'] = 'Одговори со цитат';

$lang['Click_return_topic'] = 'Кликнете %sовде%s за да се вратите на темата'; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = 'Кликнете %sовде%s да се обидете повторно';
$lang['Click_return_forum'] = 'Кликнете %sовде%s да се вратите на форумот';
$lang['Click_view_message'] = 'Кликнете %sовде%s за да ја видите вашата порака';
$lang['Click_return_modcp'] = 'Кликнете %sовде%s за да се вратите во Модераторската панела';
$lang['Click_return_group'] = 'Кликнете %sовде%s за да се вратите во Информација за групата';

$lang['Admin_panel'] = 'Отиди во Администрационата панела';

$lang['Board_disable'] = 'Форумот е временно затворен заради технички измени. Обиди се покасно!';


//
// Global Header strings
//
$lang['Registered_users'] = 'Регистрирани членови:';
$lang['Browsing_forum'] = 'Членови кои го разгледуваат овој форум:';
$lang['Online_users_zero_total'] = 'Севкупно има <b>0</b> членови он-лајн :: ';
$lang['Online_users_total'] = 'Севкупно има <b>%d</b> членови он-лајн :: ';
$lang['Online_user_total'] = 'In total there is <b>%d</b> user online :: ';
$lang['Reg_users_zero_total'] = '0 Регистрирани, ';
$lang['Reg_users_total'] = '%d Регистрирани, ';
$lang['Reg_user_total'] = '%d Регистрирани, ';
$lang['Hidden_users_zero_total'] = '0 Скриени и ';
$lang['Hidden_user_total'] = '%d Скриени и ';
$lang['Hidden_users_total'] = '%d Скриени и ';
$lang['Guest_users_zero_total'] = '0 Гости';
$lang['Guest_users_total'] = '%d Гости';
$lang['Guest_user_total'] = '%d Гост';
$lang['Record_online_users'] = 'Последен пат кога член беше он-лајн е <b>%s</b> на %s'; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = '%sАдминистратор%s';
$lang['Mod_online_color'] = '%sМодератор%s';

$lang['You_last_visit'] = 'Последната ви посета беше на %s'; // %s replaced by date/time
$lang['Current_time'] = 'Сега е точно %s'; // %s replaced by time

$lang['Search_new'] = 'Види ги сите актуелни мислења';
$lang['Search_your_posts'] = 'Видете ги своите мислења';
$lang['Search_unanswered'] = 'Видете ги мислењата без одговор';

$lang['Register'] = 'Регистрирајте се';
$lang['Profile'] = 'Профил';
$lang['Edit_profile'] = 'Промена на профил';
$lang['Search'] = 'Барај';
$lang['Memberlist'] = 'Членови';
$lang['FAQ'] = 'Прашања/Одговори';
$lang['BBCode_guide'] = 'BBCode водич';
$lang['Usergroups'] = 'Членски групи';
$lang['Last_Post'] = 'Последно мислење';
$lang['Moderator'] = 'Модератор';
$lang['Moderators'] = 'Модератори';


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = 'Нашите членови се испратиле точно <b>0</b> мислења'; // Number of posts
$lang['Posted_articles_total'] = 'Нашите членови се испратиле точно <b>%d</b> мислења'; // Number of posts
$lang['Posted_article_total'] = 'Нашите членови се испратиле точно <b>%d</b> мислење'; // Number of posts
$lang['Registered_users_zero_total'] = 'Постојат <b>0</b> регистрирани членови'; // # registered users
$lang['Registered_users_total'] = 'Постојат b>%d</b> регистрирани членови'; // # registered users
$lang['Registered_user_total'] = 'Постојат b>%d</b> регистриран член'; // # registered users
$lang['Newest_user'] = 'Најновиот регистриран член е <b>%s%s%s</b>'; // a href, username, /a

$lang['No_new_posts_last_visit'] = 'Нема нови мислења од твоето последно посетување';
$lang['No_new_posts'] = 'Нема нови мислења';
$lang['New_posts'] = 'Нови мислења';
$lang['New_post'] = 'Ново мислење';
$lang['No_new_posts_hot'] = 'Нема нови мислења [ Популарни теми ]';
$lang['New_posts_hot'] = 'Нови мислења [ Популарни теми ]';
$lang['No_new_posts_locked'] = 'Нема нови мислења[ Заклучена тема ]';
$lang['New_posts_locked'] = 'Нови мислења [ Заклучена тема ]';
$lang['Forum_is_locked'] = 'Форумот е заклучен';


//
// Login
//
$lang['Enter_password'] = 'Напишете го вашето име и лозинка за да се логирате';
$lang['Login'] = 'Влез';
$lang['Logout'] = 'Излез';

$lang['Forgotten_password'] = 'Си ја заворавив лозинката!';

$lang['Log_me_in'] = 'Логирај ме автоматски на секоја моја посета';

$lang['Error_login'] = 'Внесено е неточно (неактивно име) или лозинка!';


//
// Index page
//
$lang['Index'] = 'Индекс';
$lang['No_Posts'] = 'Нема мислења';
$lang['No_forums'] = 'Не постојат форуми';

$lang['Private_Message'] = 'Лична порака';
$lang['Private_Messages'] = 'Лични пораки';
$lang['Who_is_Online'] = 'Кој е он-лајн?';

$lang['Mark_all_forums'] = 'Маркирај ги сите форуми како прочитани.';
$lang['Forums_marked_read'] = 'Сите форуми се маркирани како прочитани';


//
// Viewforum
//
$lang['View_forum'] = 'Преглед на форумот';

$lang['Forum_not_exist'] = 'Форумот кој што го одбравте не постои';
$lang['Reached_on_error'] = 'Оваа страница се појави затоа што постои некоја грешка!';

$lang['Display_topics'] = 'Покажи ги сите теми од порано';
$lang['All_Topics'] = 'Сите теми';

$lang['Topic_Announcement'] = '<b>Објава:</b>';
$lang['Topic_Sticky'] = '<b>Важна тема:</b>';
$lang['Topic_Moved'] = '<b>Преместена:</b>';
$lang['Topic_Poll'] = '<b>[ Анкета ]</b>';

$lang['Mark_all_topics'] = 'Маркирај ги сите теми како прочитани';
$lang['Topics_marked_read'] = 'Темите од форумот се маркирани као прочитани!';

$lang['Rules_post_can'] = '<b>Можете</b> да праќате нови теми во форумот';
$lang['Rules_post_cannot'] = '<b>Не можете</b> да испраќате нови теми во форумот';
$lang['Rules_reply_can'] = '<b>Можете</b> да одговарате на темите во форумот';
$lang['Rules_reply_cannot'] = '<b>Не можете</b> да одговарате на темите во форумот';
$lang['Rules_edit_can'] = '<b>Можете</b> да ги менувате своите мислења во форумот';
$lang['Rules_edit_cannot'] = '<b>Не можете</b> да ги менувате своите мислења во форумот';
$lang['Rules_delete_can'] = '<b>Можете</b> да ги бришете своите мислења во форумот';
$lang['Rules_delete_cannot'] = '<b>Не можете</b> да ги бришете своите мислења во форумот';
$lang['Rules_vote_can'] = '<b>Можете</b> да гласате на анкетите во форумот';
$lang['Rules_vote_cannot'] = '<b>Не можете</b> да гласате на анкетите во форумот';
$lang['Rules_moderate'] = '<b>Можете</b> да го %sмодерирате овој форум%s'; // %s replaced by a href links, do not remove!

$lang['No_topics_post_one'] = 'Во овој форум нема теми<br />Кликни на <b>Нова Тема</b> за да ја напишеш првата!';


//
// Viewtopic
//
$lang['View_topic'] = 'Преглед на тема';

$lang['Guest'] = 'Гостин';
$lang['Post_subject'] = 'Предмет';
$lang['View_next_topic'] = 'Преглед на наредна тема';
$lang['View_previous_topic'] = 'Преглед на претходна тема';
$lang['Submit_vote'] = 'Испрати глас';
$lang['View_results'] = 'Види ги резултатите';

$lang['No_newer_topics'] = 'Нема понови теми во овој форум';
$lang['No_older_topics'] = 'Нема постари теми во овој форум';
$lang['Topic_post_not_exist'] = 'Темата или мислењето на кое кликна не постои!';
$lang['No_posts_topic'] = 'Не постојат мислења за таа тема';

$lang['Display_posts'] = 'Покажи ги мислењата од претходно';
$lang['All_Posts'] = 'Сите мислења';
$lang['Newest_First'] = 'Најновите први';
$lang['Oldest_First'] = 'Најстарите први';

$lang['Back_to_top'] = 'Вратете се на почетокот';

$lang['Read_profile'] = 'Преглед на профилот на членот';
$lang['Send_email'] = 'Испратете е-маил на членот';
$lang['Visit_website'] = 'Посетете го веб-сајтот на членот';
$lang['ICQ_status'] = 'ICQ Статус';
$lang['Edit_delete_post'] = 'Промени/Избриши го ова мислење';
$lang['View_IP'] = 'Видете ја IP адресата на членот';
$lang['Delete_post'] = 'Избриши го ова мислење';

$lang['wrote'] = 'напиша'; // proceeds the username and is followed by the quoted text
$lang['Quote'] = 'Цитат'; // comes before bbcode quote output.
$lang['Code'] = 'Код'; // comes before bbcode code output.

$lang['Edited_time_total'] = 'Последната промена е направена од %s на %s. Мислењето е променето %d пати'; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = 'Последната промена е направена од %s на %s. Мислењето е променето %d пати.'; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = 'Заклучај ја оваа тема';
$lang['Unlock_topic'] = 'Одклучај ја оваа тема';
$lang['Move_topic'] = 'Премести ја оваа тема';
$lang['Delete_topic'] = 'Избриши ја оваа тема';
$lang['Split_topic'] = 'Раздели ја оваа тема';

$lang['Stop_watching_topic'] = 'Престанете со разгледување на темата';
$lang['Start_watching_topic'] = 'Набљудувајте ја темата за одговори';
$lang['No_longer_watching'] = 'Веќе не ја набљудувате темата за одговори';
$lang['You_are_watching'] = 'Оваа тема се набљудува од вас за одговори';

$lang['Total_votes'] = 'Општо гласови';

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = 'Пишување на порака';
$lang['Topic_review'] = 'Преглед на темата';

$lang['No_post_mode'] = 'Не е избрано тип на пораката'; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = 'Испраќање на нова тема';
$lang['Post_a_reply'] = 'Испраќање на одговор';
$lang['Post_topic_as'] = 'Испрати го одговорот како';
$lang['Edit_Post'] = 'Промена го мислењето';
$lang['Options'] = 'Опции';

$lang['Post_Announcement'] = 'Објава';
$lang['Post_Sticky'] = 'Важна тема';
$lang['Post_Normal'] = 'Нормална';

$lang['Confirm_delete'] = 'Сигурни ли сте дека сакате да го избришете ова мислење?';
$lang['Confirm_delete_poll'] = 'Сигурни ли сте дека сакате да ја избришете оваа анкета?';

$lang['Flood_Error'] = 'Не можете да испратите мислење толку скоро.Обидете се по касно!';
$lang['Empty_subject'] = 'Морате да напишете Заглавие кога испраќате тема';
$lang['Empty_message'] = 'Морате да напишете порака пред да испратите';
$lang['Forum_locked'] = 'Овој форум е заклучен. Не можете да испраќате, одговарате на или менувате мислења';
$lang['Topic_locked'] = 'Оваа тема е заклучена. Не можете да менувате мислења и да одговарате!';
$lang['No_post_id'] = 'Морате да избрете мислење за менување';
$lang['No_topic_id'] = 'Морате да изберете тема на која да одговорите';
$lang['No_valid_mode'] = 'Можете само да изпраќате, да менувате, одговарате или да одговарате со цитат на пораките. Вратете се назад и обидете се повторно.';
$lang['No_such_post'] = 'Не постои такво мислење. Вратете се и обидете се повторно.';
$lang['Edit_own_posts'] = 'Можете да ги менувате само своите мислења.';
$lang['Delete_own_posts'] = 'Можете да ги бришете само своите мислења.';
$lang['Cannot_delete_replied'] = 'Не можете да избришете мислења на кои е одговорено.';
$lang['Cannot_delete_poll'] = 'Не можете да избришете активна анкета.';
$lang['Empty_poll_title'] = 'Мора да изберете наслов за својата анкета.';
$lang['To_few_poll_options'] = 'Мора да изберете најмалку две опции за гласање.';
$lang['To_many_poll_options'] = 'Се обидовте да напишете многу анкетни опции.';
$lang['Post_has_no_poll'] = 'Ова мислење нема анкета.';
$lang['Already_voted'] = 'Веќе сте гласали на оваа анкета.';
$lang['No_vote_option'] = 'Мора да изберете опција на која да гласате.';

$lang['Add_poll'] = 'Додај Анкета';
$lang['Add_poll_explain'] = 'Ако не сакате да додате анкета, оставете ги полињата празни.';
$lang['Poll_question'] = 'Прашање на Анкетата';
$lang['Poll_option'] = 'Опција на Анкетата';
$lang['Add_option'] = 'Додај опција за гласање';
$lang['Update'] = 'Обнови';
$lang['Delete'] = 'Избриши';
$lang['Poll_for'] = 'Нека анкетата биде активна';
$lang['Days'] = 'Дена'; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = '[ Напишете 0 или оставете го полето празно за вечна анкета]';
$lang['Delete_poll'] = 'Избриши ја Анкетата';

$lang['Disable_HTML_post'] = 'Изклучи HTML-от во ова мислење';
$lang['Disable_BBCode_post'] = 'Изкучи BBCode-от во ова мислење';
$lang['Disable_Smilies_post'] = 'Изклучи ги Смешковците во ова мислење';

$lang['HTML_is_ON'] = 'HTML-от е <u>Вклучен</u>';
$lang['HTML_is_OFF'] = 'HTML-от е <u>Исклучен</u>';
$lang['BBCode_is_ON'] = '%sBBCode-от%s е <u>Вклучен</u>'; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = '%sBBCode-от%s is <u>Исклучен</u>';
$lang['Smilies_are_ON'] = 'Смешковците се <u>Вклучени</u>';
$lang['Smilies_are_OFF'] = 'Смешковците се <u>Изклучени</u>';

$lang['Attach_signature'] = 'Додај потпис (потписите се менуваат во профилот)';
$lang['Notify'] = 'Предупреди ме кога е одговорено на мислењето';
$lang['Delete_post'] = 'Избриши го ова мислење';

$lang['Stored'] = 'Пораката е испратена успешно.';
$lang['Deleted'] = 'Пораката беше избришана успешно.';
$lang['Poll_delete'] = 'Анкетата е избришана успешно.';
$lang['Vote_cast'] = 'Вашиот глас е запишан.';

$lang['Topic_reply_notification'] = 'Соопштување при одговор';

$lang['bbcode_b_help'] = 'Здебелен текст: [b]текст[/b]  (alt+b)';
$lang['bbcode_i_help'] = 'Кос текст: [i]текст[/i]  (alt+i)';
$lang['bbcode_u_help'] = 'Подцртан текст: [u]текст[/u]  (alt+u)';
$lang['bbcode_q_help'] = 'Цитат: [quote]текст[/quote]  (alt+q)';
$lang['bbcode_c_help'] = 'Код: [code]код[/code]  (alt+c)';
$lang['bbcode_l_help'] = 'Список: [list]текст[/list] (alt+l)';
$lang['bbcode_o_help'] = 'Подреден список: [list=]текст[/list]  (alt+o)';
$lang['bbcode_p_help'] = 'Внесување на слика: [img]http://адреса_на_сликата[/img]  (alt+p)';
$lang['bbcode_w_help'] = 'Внеси врска: [url]http://url[/url] или [url=http://url]Опис[/url]  (alt+w)';
$lang['bbcode_a_help'] = 'Затвори ги сите bbCode тагови';
$lang['bbcode_s_help'] = 'Боја на буквите: [color=red]text[/color]  Совет: Можете да користите и color=#FF0000';
$lang['bbcode_f_help'] = 'Големина на буквите [size=x-small]мал текст[/size]';

$lang['Emoticons'] = 'Емоции';
$lang['More_emoticons'] = 'Види ги сите Емоции';

$lang['Font_color'] = 'Боја на буквите';
$lang['color_default'] = 'Основена';
$lang['color_dark_red'] = 'Темно Црвена';
$lang['color_red'] = 'Црвена';
$lang['color_orange'] = 'Портокалова';
$lang['color_brown'] = 'Кафеава';
$lang['color_yellow'] = 'Жолта';
$lang['color_green'] = 'Зелена';
$lang['color_olive'] = 'Маслинова';
$lang['color_cyan'] = 'Синозелена';
$lang['color_blue'] = 'Сина';
$lang['color_dark_blue'] = 'Темно Сина';
$lang['color_indigo'] = 'Маслинесто сина';
$lang['color_violet'] = 'Лилјакова';
$lang['color_white'] = 'Бела';
$lang['color_black'] = 'Црна';

$lang['Font_size'] = 'Големина на буквите';
$lang['font_tiny'] = 'Многу малечки';
$lang['font_small'] = 'Мали';
$lang['font_normal'] = 'Нормални';
$lang['font_large'] = 'Големи';
$lang['font_huge'] = 'Огромни';

$lang['Close_Tags'] = 'Затвори ги таговите';
$lang['Styles_tip'] = 'Совет: Стиловите можат се приложуваат на текстот.';


//
// Private Messaging
//
$lang['Private_Messaging'] = 'Лични пораки';

$lang['Login_check_pm'] = 'Логирајте се за да ги видите личните пораки.';
$lang['New_pms'] = 'Имате %d нови пораки'; // You have 2 new messages
$lang['New_pm'] = 'Имате %d нови пораки'; // You have 1 new message
$lang['No_new_pm'] = 'Немате нови пораки';
$lang['Unread_pms'] = 'Имате %d непрочитани пораки';
$lang['Unread_pm'] = 'Имате %d непрочитана порака';
$lang['No_unread_pm'] = 'Немате непрочитани пораки.';
$lang['You_new_pm'] = 'Нова порака пристигна';
$lang['You_new_pms'] = 'Нови пораки пристигнаа.';
$lang['You_no_new_pm'] = 'Немате нови пораки.';

$lang['Unread_message'] = 'Не прочитана порака.';
$lang['Read_message'] = 'Прочитај ја пораката.';

$lang['Read_pm'] = 'Прочитај ја пораката';
$lang['Post_new_pm'] = 'Испрати порака';
$lang['Post_reply_pm'] = 'Одговори на пораката';
$lang['Post_quote_pm'] = 'Цитирај ја пораката';
$lang['Edit_pm'] = 'Снемни ја пораката';

$lang['Inbox'] = 'Влезни';
$lang['Outbox'] = 'Излезни';
$lang['Savebox'] = 'Снимани';
$lang['Sentbox'] = 'Испратени';
$lang['Flag'] = 'Знаменце';
$lang['Subject'] = 'Предмет';
$lang['From'] = 'Од';
$lang['To'] = 'До';
$lang['Date'] = 'Дата';
$lang['Mark'] = 'Маркирај';
$lang['Sent'] = 'Испратено';
$lang['Saved'] = 'Снимано';
$lang['Delete_marked'] = 'Избриши маркирана';
$lang['Delete_all'] = 'Избриши ги сите';
$lang['Save_marked'] = 'Снимај маркирана';
$lang['Save_message'] = 'Снимај ја пораката';
$lang['Delete_message'] = 'Избриши ја пораката';

$lang['Display_messages'] = 'Покажи ја пораката од претходната'; // Followed by number of days/weeks/months
$lang['All_Messages'] = 'Сите пораки';

$lang['No_messages_folder'] = 'Немате нови пораки во овој фолдер.';

$lang['PM_disabled'] = 'Личните пораки се забранети овде.';
$lang['Cannot_send_privmsg'] = 'Администраторот е забранил испраќање на лични пораки. Се извинуваме за случајот.';
$lang['No_to_user'] = 'Мора да напишете членско име за да ја испратите оваа порака.';
$lang['No_such_user'] = 'Таков член не постои.';

$lang['Disable_HTML_pm'] = 'Исклучи го HTML-от во оваа порака.';
$lang['Disable_BBCode_pm'] = 'Исклучи го BBCode-от во оваа порака.';
$lang['Disable_Smilies_pm'] = 'Исклучи ги смешковците во оваа порака.';

$lang['Message_sent'] = 'Вашата порака не беше испратена.';

$lang['Click_return_inbox'] = 'Кликнете %sОвде%s за да се вратите во Влезните пораки';
$lang['Click_return_index'] = 'Кликнете %sОвде%s за да се вратите кај форумите';

$lang['Send_a_new_message'] = 'Испратете нова лична порака';
$lang['Send_a_reply'] = 'Одговорете на лична порака';
$lang['Edit_message'] = 'Променете лична порака';

$lang['Notification_subject'] = 'Добивте нова лична порака.';

$lang['Find_username'] = 'Пронајдете член';
$lang['Find'] = 'Пронајди';
$lang['No_match'] = 'Нема пронајдено членови.';

$lang['No_post_id'] = 'Нов ID беше одбран';
$lang['No_such_folder'] = 'Таков фолдер не постои';
$lang['No_folder'] = 'Не беше одбран фолдер.';

$lang['Mark_all'] = 'Маркирај ги сите';
$lang['Unmark_all'] = 'Демаркирај ги сите';

$lang['Confirm_delete_pm'] = 'Сигурни ли сте дека сакате да ја избришете оваа порака?';
$lang['Confirm_delete_pms'] = 'Сигурни ли сте дека сакате да ги избришете овие пораки?';

$lang['Inbox_size'] = 'Фолдерот со влезни пораки е %d%% полн'; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = 'Фолдерот со испратени пораки е %d%% полн';
$lang['Savebox_size'] = 'Фолдерот со снимани пораки е %d%% полн';

$lang['Click_view_privmsg'] = 'Кликни %sОвде%s за да го видиш фолдерот со влезни пораки';


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = 'Профил на :: %s'; // %s is username
$lang['About_user'] = 'Се за %s'; // %s is username

$lang['Preferences'] = 'Преференции';
$lang['Items_required'] = 'Полињата маркирани со * се задолжителни. Оние другите не се.';
$lang['Registration_info'] = 'Информација';
$lang['Profile_info'] = 'Информација на Профил';
$lang['Profile_info_warn'] = 'Оваа информација ќе може да се види јавно';
$lang['Avatar_panel'] = 'Контролен панел за аватари';
$lang['Avatar_gallery'] = 'Галерија на аватари';

$lang['Website'] = 'Веб-сајт';
$lang['Location'] = 'Местолокација';
$lang['Contact'] = 'Контакт';
$lang['Email_address'] = 'Е-маил адреса';
$lang['Email'] = 'Е-маил';
$lang['Send_private_message'] = 'Испрати лична порака';
$lang['Hidden_email'] = '[ Скриен меил ]';
$lang['Search_user_posts'] = 'Барај мислења на член';
$lang['Interests'] = 'Интереси';
$lang['Occupation'] = 'Професија';
$lang['Poster_rank'] = 'Ранк фо форумот';

$lang['Total_posts'] = 'Општо мислења';
$lang['User_post_pct_stats'] = '%.2f%% од сите'; // 1.25% of total
$lang['User_post_day_stats'] = '%.2f мислења на ден'; // 1.5 posts per day
$lang['Search_user_posts'] = 'Видете ги сите мислења %s'; // Find all posts by username

$lang['No_user_id_specified'] = 'Таков член не постои';
$lang['Wrong_Profile'] = 'Не можеш да менуваш профил кој не е твој.';

$lang['Only_one_avatar'] = 'Само еден тип на аватар може да биде одбран.';
$lang['File_no_data'] = 'Фајлот на врската која ја додадовте не постои.';
$lang['No_connection_URL'] = 'Не можеше да се провери точноста на врската.';
$lang['Incomplete_URL'] = 'Врскате која ја внесовте е некомплетна.';
$lang['Wrong_remote_avatar_format'] = 'Врската на надворешниот аватар е невалидна.';
$lang['No_send_account_inactive'] = 'Вашата лозинка не можеше да биде откриена, затоа што вашиот членски профил е времено неактивен. Контактирајте го администраторот за повеќе информации.';

$lang['Always_smile'] = 'Секогаш нека бидат вклучени Смешковците';
$lang['Always_html'] = 'Секогаш нека биде вклучен HTML-от';
$lang['Always_bbcode'] = 'Секогаш нека биде вклучен BBCode-от';
$lang['Always_add_sig'] = 'Секогаш додавај го мојот потпис';
$lang['Always_notify'] = 'Секогаш известувај ме за одговори';
$lang['Always_notify_explain'] = 'Ви испраќа е-маил кога некој е одговарил на вашата порака.';

$lang['Board_style'] = 'Стил на форумот';
$lang['Board_lang'] = 'јазик на форумот';
$lang['No_themes'] = 'Нема теми во базата.';
$lang['Timezone'] = 'Временска зона';
$lang['Date_format'] = 'Формат на датата';
$lang['Date_format_explain'] = 'Синтаксот е идентичен на PHP <a href=\'http://www.php.net/date\' target=\'_other\'>date()</a> функцијата';
$lang['Signature'] = 'Потпис';
$lang['Signature_explain'] = 'Ова се неколку реда текст кои можат да се додаваат на мислењата кои ги пишуваш. Има ограничување %d во бројот на симболите.';
$lang['Public_view_email'] = 'Секогаш ја покажувај мојата е-маил адреса';

$lang['Current_password'] = 'Сегашна лозинка';
$lang['New_password'] = 'Нова лозинка';
$lang['Confirm_password'] = 'Потврди лозника';
$lang['Confirm_password_explain'] = 'Мореате да го потврдите вашата сегашна лозинка за да ја менувате или да ја смените вашата е-маил адреса.';
$lang['password_if_changed'] = 'Треба да напишете лозинка само ако сакате да ја смените.';
$lang['password_confirm_if_changed'] = 'Треба да ја потврдите лозинката само ако ја менувате.';

$lang['Avatar'] = 'Аватар';
$lang['Avatar_explain'] = 'Аватарот покажува мала сликичка под твоите мислења во форумот. Нејзината широчина не смее да биде поголема од %d пиксели, височината не поголема од %d пиксели и големината на фајлот не поголема од %dkB.';
$lang['Upload_Avatar_file'] = 'Ставете Аватар од вашиот компјутер';
$lang['Upload_Avatar_URL'] = 'Земи Аватар од Веб-адреса';
$lang['Upload_Avatar_URL_explain'] = 'Напишете ја URL адресата на локацијата која го содржи Аватарот и ќе биде копирана во овој сајт.';
$lang['Pick_local_Avatar'] = 'Одбери Аватар од галеријата';
$lang['Link_remote_Avatar'] = 'Аватар од друг сајт';
$lang['Link_remote_Avatar_explain'] = 'Напишете ја URL адресата од локацијата која го содржи Аватарот и ќе биде земан од таму.';
$lang['Avatar_URL'] = 'URL адреса на Аватарот';
$lang['Select_from_gallery'] = 'Одберете Аватар од галеријата';
$lang['View_avatar_gallery'] = 'Видете ја галеријата';

$lang['Select_avatar'] = 'Одбери Аватар';
$lang['Return_profile'] = 'Престани со одбирањето';
$lang['Select_category'] = 'Одбери категорија';

$lang['Delete_Image'] = 'Избришете го Аватарот';
$lang['Current_Image'] = 'Сегашен Аватар';

$lang['Notify_on_privmsg'] = 'Соопштување при нови Лични Пораки';
$lang['Popup_on_privmsg'] = 'Pop up - прозорец при нови Лични Пораки';
$lang['Popup_on_privmsg_explain'] = 'Некои теми можат да отворат нов прозорец кога ќе пристигне нова порака.';
$lang['Hide_user'] = 'Криење на вашиот он-лајн статус';

$lang['Profile_updated'] = 'Вашиот профил беше обновен.';
$lang['Profile_updated_inactive'] = 'Вашиот профил беше обновен, но поради тоа што направивте значајни промени вашето членство е неактивно. Проверете дали ви е стигнал нов е-маил за тоа како да си го реактивирате членството, ако не тогаш почекајте администраторот да ви го реактивира.';

$lang['Password_mismatch'] = 'Лозинките кои ги внесовте не се совпаднаа.';
$lang['Current_password_mismatch'] = 'Лозинката која ја напишавте не совпаѓа со онаа од нашата база.';
$lang['Password_long'] = 'Вашата лозинка не смее да биде подолга од 32 букви.';
$lang['Username_taken'] = 'Членското име кое го одбравте е веќе земено.';
$lang['Username_invalid'] = 'Членското име кое го одбравте содржи невалидни карактери. Како на пример \'';
$lang['Username_disallowed'] = 'Членското име кое го одбравте е забрането за користење.';
$lang['Email_taken'] = 'Е-маил адресата која ја внесовте веќе се користи од некој член.';
$lang['Email_banned'] = 'Е-маил адресата која ја внесовте е банирана.';
$lang['Email_invalid'] = 'Е-маил адресата која ја внесовте е невалидна.';
$lang['Signature_too_long'] = 'Потписот ви е премногу долг.';
$lang['Fields_empty'] = 'Мора да ги пополните сите потребни полиња.';
$lang['Avatar_filetype'] = 'Аватарот мора да биде со завршеток .jpg, .gif или .png';
$lang['Avatar_filesize'] = 'Големината на Аватарот не смее да биде поголема од %d kB'; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = 'Широчината на Аватарот мора да биде помала од %d пиксели и неговата височина мора да е помала од %d пиксели';

$lang['Welcome_subject'] = 'Добредојдовте на %s Форумите'; // Welcome to my.com forums
$lang['New_account_subject'] = 'Членство на нов член.';
$lang['Account_activated_subject'] = 'Членството е активирано.';

$lang['Account_added'] = 'Благодариме што се регистриравте. Сега можете да се логирате со вашето членско име и лозинка.';
$lang['Account_inactive'] = 'Вашето членство е активирано. Меѓутоа овој форум бара активрање на членство со клуч. Активациониот клуч е испратен на вашата е-маил адреса. Проверете ја вашата е-маил адреса за повеќе информација.';
$lang['Account_inactive_admin'] = 'Вашето членство е активирано. Но овој форум бара потврда од администраторот. Тој ќе биде информиран а и вие кога вашето членство ќе биде активирано.';
$lang['Account_active'] = 'Вашето членство е активирано. Благодариме што се регистриравте.';
$lang['Account_active_admin'] = 'Вашето членство е активирано.';
$lang['Reactivate'] = 'Реактивирајте го вашето членство.';
$lang['Already_activated'] = 'Веќе е активирано вашето членство. Нема потреба од повторно активирање.';
$lang['COPPA'] = 'Вашето членство е активирано но мора да биде одобрено. Проверете ја вашата е-маил адреса за детали.';

$lang['Registration'] = 'Услови за регистрирање';
$lang['Reg_agreement'] = 'И покрај тоа што администраторите ќе се обидат да го избришат или променат секој материјал што носи кршење на закони што е можно побрзо невозможно е да се видат сите пораки и мислења. Вие разбирате дека сите мислења на овој форум ги изразуваат мислењата на авторите им, а не на администраторите, модераторите или вебмастерот (освен мислењата напишани од нив) соодветно тие не носат никаква одговорност.<br /><br />Се согласувате дека нема да пишувате никаков груб, неприличен, вулгарен, плашлив, омразлив, сексуално ориентиран или било каков материјал кој може да наруши закони. Во спротивно може да се случи ваше банирање од форумот. (И вашиот Интернет провајдер да биде информиран) IP адресата од која ќе ги пишувате вашите мислења ќе биде запишана за да се запазат овие услови. Се согласувате дека вебмастерот, администраторите и модераторите на овој форум имаат право да ги избришат и променат вашите мислења секогаш кога ќе ги нарушите условите. Се согласувате дека секоја информација која ја внесувате може да биде запишана во нашата база. И покрај тоа што оваа информација нема да биде дадена на трети личности без ваше одобрение, вебмастерот, администраторите и модераторите не носат одговорност на хакерските упади кои можат да резултираат со очевидност на вашите податоци. <br /><br />Системот на овој форум користи cookies за да ја запази информацијата на вашиот компјутер. Тие не содржат никаква информација која ќе ја напишете овде, туку се користат да се подобри функционалноста на форумите. Вашата е-маил адреса се користи само за потврда на регистрацијата (и за испраќање на лозинката ако случајно сте ја заборавиле).<br /><br />Со кликнување на Согласен сум вие ги примате горенаведените услови.';

$lang['Agree_under_13'] = 'Согласен сум со условите и возраста ми е <b>под</b>13 години.';
$lang['Agree_over_13'] = 'Согласен сум со условите и возраста ми е <b>над</b> или <b>точно</b> 13 години.';
$lang['Agree_not'] = 'Не сум согласен со овие услови.';

$lang['Wrong_activation'] = 'Активациониот клуч кој го напишавте со оној во нашата база.';
$lang['Send_password'] = 'Испрати ми нова лозинка';
$lang['Password_updated'] = 'Нова лозинка беше креирана; проверете си ја е-маил адресата за детали како да ја активирате.';
$lang['No_email_match'] = 'Е-маил адресата која ја напишавте не совпадна со онаа во нашата база.';
$lang['New_password_activation'] = 'Активирање на нова лозинка';
$lang['Password_activated'] = 'Твоето членство беше реактивирано. За да се логирате напишете ја лозниката која ја добивте во вашата е-маил адреса.';

$lang['Send_email_msg'] = 'Испратете е-маил';
$lang['No_user_specified'] = 'Членско име не е одбрано';
$lang['User_prevent_email'] = 'Овој член претпочита да не прима пораки од форумот. Пратете му лична порака.';
$lang['User_not_exist'] = 'Таков член не постои';
$lang['CC_email'] = 'Испрати си себе копија од поракава';
$lang['Email_message_desc'] = 'Оваа порака ќе биде испратена како обичен текст, нема да содржи HTML или BBКод. Адресата на испраќачот ќе биде вашата е-маил адреса.';
$lang['Flood_email_limit'] = 'Не можете да испратите е-маил толку скоро. Обидете се покасно.';
$lang['Recipient'] = 'Примател';
$lang['Email_sent'] = 'Е-маилот беше испратен.';
$lang['Send_email'] = 'Испрати е-маил';
$lang['Empty_subject_email'] = 'Морате да напишете заглавие за пораката.';
$lang['Empty_message_email'] = 'Мора да напишете некоја порака';


//
// Memberslist
//
$lang['Select_sort_method'] = 'Одберете метод на сортирање';
$lang['Sort'] = 'Сортирај';
$lang['Sort_Top_Ten'] = 'Топ десет Испраќачи';
$lang['Sort_Joined'] = 'Дата на вклучување';
$lang['Sort_Username'] = 'Членско име';
$lang['Sort_Location'] = 'Местоположба';
$lang['Sort_Posts'] = 'Општо мислења';
$lang['Sort_Email'] = 'Е-маил';
$lang['Sort_Website'] = 'Веб-сајт';
$lang['Sort_Ascending'] = 'Вертикален ред';
$lang['Sort_Descending'] = 'Хоризонтален ред';
$lang['Order'] = 'Ред';


//
// Group control panel
//
$lang['Group_Control_Panel'] = 'Контролна панела на групата';
$lang['Group_member_details'] = 'Детали за членство во групата';
$lang['Group_member_join'] = 'Вклучи се во група';

$lang['Group_Information'] = 'Информација за групата';
$lang['Group_name'] = 'Име на групата';
$lang['Group_description'] = 'Опис на групата';
$lang['Group_membership'] = 'Членство на групата';
$lang['Group_Members'] = 'Членови на групата';
$lang['Group_Moderator'] = 'Модератори на групата';
$lang['Pending_members'] = 'Кандидати кои чекаат за членство';

$lang['Group_type'] = 'Тип на групата';
$lang['Group_open'] = 'Отворена група';
$lang['Group_closed'] = 'Затворена група';
$lang['Group_hidden'] = 'Скриена група';

$lang['Current_memberships'] = 'Групи во кои сте член';
$lang['Non_member_groups'] = 'Групи во кои не сте член';
$lang['Memberships_pending'] = 'Кандидат за групи';

$lang['No_groups_exist'] = 'Не постојат групи';
$lang['Group_not_exist'] = 'Таква членска група не постои';

$lang['Join_group'] = 'Присоедини се кон група';
$lang['No_group_members'] = 'Оваа група нема членови';
$lang['Group_hidden_members'] = 'Оваа група е скриена, не можете да го видите нејзиното членство.';
$lang['No_pending_group_members'] = 'Оваа група нема кандидати кои чекаат да се вклучат.';
$lang['Group_joined'] = 'Успешно се кандидиравте во оваа група. <br />Ќе бидете информирани кога вашето членство е одобрено од администраторот на групата.';
$lang['Group_request'] = 'Кандидатура за член на група е направена';
$lang['Group_approved'] = 'Вашата кандидатура е одобрена';
$lang['Group_added'] = 'Примани сте како член на оваа група';
$lang['Already_member_group'] = 'Веќе сте член на оваа група';
$lang['User_is_member_group'] = 'Корисникот е веќе член на оваа група';
$lang['Group_type_updated'] = 'Успешно се обнови типот на групата';

$lang['Could_not_add_user'] = 'Членот кој го одбравте не постои';
$lang['Could_not_anon_user'] = 'Не можете да додавате анонимни членови во групата.';

$lang['Confirm_unsub'] = 'Дали сте сигурни дека сакате да се исклучите од оваа група?';
$lang['Confirm_unsub_pending'] = 'Вашето кандидирање од оваа група уште не е одобрено. Дали сте сигурни дека сакате да се исклучите?';

$lang['Unsub_success'] = 'Успешно се исклучивте од оваа група.';

$lang['Approve_selected'] = 'Одобри ги селектираните';
$lang['Deny_selected'] = 'Одбиј ги селектираните';
$lang['Not_logged_in'] = 'Мора да сте логирани за да се вклучите во група.';
$lang['Remove_selected'] = 'Избриши ги селектираните';
$lang['Add_member'] = 'Додај член';
$lang['Not_group_moderator'] = 'Не можете да го исполните ова дејствие затоа што не сте модератор на оваа група.';

$lang['Login_to_join'] = 'Логирајте се за да се вклучите во група или да ја модерирате.';
$lang['This_open_group'] = 'Ова е отворена група. Кликнете за членство.';
$lang['This_closed_group'] = 'Ова е затворена група. Членови не се примаат.';
$lang['This_hidden_group'] = 'Ова е скриена група, автоматско членство не е дозволено.';
$lang['Member_this_group'] = 'Член сте на оваа група.';
$lang['Pending_this_group'] = 'Твојата кандидатура за член на оваа група е во процес на проверка.';
$lang['Are_group_moderator'] = 'Вие сте модератор на оваа група.';
$lang['None'] = 'Никој';

$lang['Subscribe'] = 'Запишете се';
$lang['Unsubscribe'] = 'Отпишете се';
$lang['View_Information'] = 'Информации';


//
// Search
//
$lang['Search_query'] = 'Критериум за барање';
$lang['Search_options'] = 'Опции за барање';

$lang['Search_keywords'] = 'Барање по клучни зборови.';
$lang['Search_keywords_explain'] = 'Користете <u>AND</u> за да ги определите зборовите кои можат да бидат во резултатите, <u>OR</u> за да ги определите зборовите кои би можело да се во резултатите и <u>NOT</u> за да ги определите зборовите кои не треба да се во резултатите. Користете (*) кога се налага. ';
$lang['Search_author'] = 'Барај автор.';
$lang['Search_author_explain'] = 'Користете * за одделни резултати.';

$lang['Search_for_any'] = 'Барај со сите воведени зборови';
$lang['Search_for_all'] = 'Барај со сите зборови.';
$lang['Search_title_msg'] = 'Барај наслов на тема и во содржината на мислењата.';
$lang['Search_msg_only'] = 'Барај само во содржината на мислењата';

$lang['Return_first'] = 'Покажи ги првите'; // followed by xxx characters in a select box
$lang['characters_posts'] = 'букви од мислењето';

$lang['Search_previous'] = 'Барај од претходното'; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = 'Сортирај по';
$lang['Sort_Time'] = 'Време на испраќање';
$lang['Sort_Post_Subject'] = 'Заглавие на мислењето';
$lang['Sort_Topic_Title'] = 'Наслов на темата';
$lang['Sort_Author'] = 'Автор';
$lang['Sort_Forum'] = 'Форум';

$lang['Display_results'] = 'Покажи ги резултатите како';
$lang['All_available'] = 'Сите постоечки';
$lang['No_searchable_forums'] = 'Немате дозвола да барате по форумот на овој сајт.';

$lang['No_search_match'] = 'Не постојат теми или мислења кои одговараат на вашиот критериум';
$lang['Found_search_match'] = 'Барањето даде %d резултат'; // eg. Search found 1 match
$lang['Found_search_matches'] = 'Барањето даде %d резултат'; // eg. Search found 24 matches

$lang['Close_window'] = 'Затвори го прозорецот';


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = 'Само %s можат да праќаат Соопштенија во овој форум';
$lang['Sorry_auth_sticky'] = 'Само %s можат да праќаат Важни теми во овој форум';
$lang['Sorry_auth_read'] = 'Само %s можат да ги читаат темите во овој форум';
$lang['Sorry_auth_post'] = 'Само %s можат да создаваат теми во овој форум';
$lang['Sorry_auth_reply'] = 'Само %s можат да одговараат на темите во овој форум';
$lang['Sorry_auth_edit'] = 'Само %s можат да ги менуваат мислењата во овој форум';
$lang['Sorry_auth_delete'] = 'Само %s можат да бришат мислења во овој форум';
$lang['Sorry_auth_vote'] = 'Само %s можат да гласаат на анкетите во овој форум';

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = '<b>анонимните членови</b>';
$lang['Auth_Registered_Users'] = '<b>регистрираните членови</b>';
$lang['Auth_Users_granted_access'] = '<b>членовите со специјален пристап</b>';
$lang['Auth_Moderators'] = '<b>модераторите</b>';
$lang['Auth_Administrators'] = '<b>администраторите</b>';

$lang['Not_Moderator'] = 'Вие не сте модератор на оваа група.';
$lang['Not_Authorised'] = 'Немате дозвола';

$lang['You_been_banned'] = 'Банирани сте од овој форум.<br />Контактирајте го вебмастерот или администраторот на форумот за повеќе информации.';


//
// Viewonline
//
$lang['Reg_users_zero_online'] = 'Има 0 Регистрирани членови и '; // There ae 5 Registered and
$lang['Reg_users_online'] = 'Има %d Регистрирани членови и '; // There ae 5 Registered and
$lang['Reg_user_online'] = 'Има %d Регистриран член и '; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = '0 скриени членови он-лајн'; // 6 Hidden users online
$lang['Hidden_users_online'] = '%d скриени членови он-лајн'; // 6 Hidden users online
$lang['Hidden_user_online'] = '%d скриен член он-лајн.'; // 6 Hidden users online
$lang['Guest_users_online'] = 'Има %d гости он-лајн'; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = 'Има 0 гости он-лајн'; // There are 10 Guest users online
$lang['Guest_user_online'] = 'Има %d гост он-лајн'; // There is 1 Guest user online
$lang['No_users_browsing'] = 'Нема членови кои се на форумот.';

$lang['Online_explain'] = 'Податоците се базирани на активноста на членовите во последните пет минути.';

$lang['Forum_Location'] = 'Локација на форумот';
$lang['Last_updated'] = 'Последно обновување';

$lang['Forum_index'] = 'Главна страна';
$lang['Logging_on'] = 'Влегува';
$lang['Posting_message'] = 'Испраќа мислење';
$lang['Searching_forums'] = 'Бара по форуми';
$lang['Viewing_profile'] = 'Прегледува профил';
$lang['Viewing_online'] = 'Гледа кој е он-лајн';
$lang['Viewing_member_list'] = 'Прегледува кои се членови';
$lang['Viewing_priv_msgs'] = 'Ги прегледува личните пораки';
$lang['Viewing_FAQ'] = 'Ги чита Прашања/Одговори';


//
// Moderator Control Panel
//
$lang['Mod_CP'] = 'Модераторски Контрол Панел';
$lang['Mod_CP_explain'] = 'Со користење на формата подолу можете да го модерирате форумот. Модерација подразбира заклучување, отклучување, преместување или бришење на оделни теми.';

$lang['Select'] = 'Одбери';
$lang['Delete'] = 'Избриши';
$lang['Move'] = 'Премести';
$lang['Lock'] = 'Заклучи';
$lang['Unlock'] = 'Отклучи';

$lang['Topics_Removed'] = 'Одбраните теми се успешно избришани од базата';
$lang['Topics_Locked'] = 'Одбраните теми се заклучени';
$lang['Topics_Moved'] = 'Одбраните теми се преместен';
$lang['Topics_Unlocked'] = 'Одбраните теми се отклучени';
$lang['No_Topics_Moved'] = 'Никакви теми не се преместени';

$lang['Confirm_delete_topic'] = 'Сигурни ли сте дека сакате да ги избришете одбраните теми?';
$lang['Confirm_lock_topic'] = 'Сигруни ли сте дека сакате да ги заклучите одбраните теми?';
$lang['Confirm_unlock_topic'] = 'Сигруни ли сте дека сакате да ги отклучите одбраните теми?';
$lang['Confirm_move_topic'] = 'Сигурни ли сте дека сакате да ги преместите одбраните теми?';

$lang['Move_to_forum'] = 'Премести во форум';
$lang['Leave_shadow_topic'] = 'Остави линк во стариот форум';

$lang['Split_Topic'] = 'Разделување на теми';
$lang['Split_Topic_explain'] = 'На овој начин можете да разделите тема на два дела : поставување на мислењата во нова тема или разделување на темата на две по избраното мислење.';
$lang['Split_title'] = 'Нов наслов на тема';
$lang['Split_forum'] = 'Форум за новата тема';
$lang['Split_posts'] = 'Раздели ги одбраните мислења';
$lang['Split_after'] = 'Раздели ја темата по избраното мислење';
$lang['Topic_split'] = 'Одбраната тема беше разделена успешно';

$lang['Too_many_error'] = 'Одбравте премногу мислења. Треба да изберете само едно мислење по кое сакате да ја разделите темата.';

$lang['None_selected'] = 'Не одбравте теми за разделување. Вратете се и одберете една.';
$lang['New_forum'] = 'Нов форум';

$lang['This_posts_IP'] = 'IP адреса на ова мислење';
$lang['Other_IP_this_user'] = 'Други IP адреси од каде е испраќал овој член';
$lang['Users_this_IP'] = 'Членови кои испраќаат од оваа IP адреса';
$lang['IP_info'] = 'IP Информација';
$lang['Lookup_IP'] = 'Види ја IP адресата';


//
// Timezones ... for display on each page
//
$lang['All_times'] = 'Часовите се според зоната %s'; // eg. All times are GMT - 12 Hours (times from next block)

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
$lang['1'] = 'GMT + 1 Час';
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
$lang['tz']['-1'] = 'GMT - 1 Часа';
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

$lang['datetime']['Sunday'] = 'Недела';
$lang['datetime']['Monday'] = 'Понеделник';
$lang['datetime']['Tuesday'] = 'Вторник';
$lang['datetime']['Wednesday'] = 'Среда';
$lang['datetime']['Thursday'] = 'Четврток';
$lang['datetime']['Friday'] = 'Петок';
$lang['datetime']['Saturday'] = 'Сабота';
$lang['datetime']['Sun'] = 'Нед';
$lang['datetime']['Mon'] = 'Пон';
$lang['datetime']['Tue'] = 'Вто';
$lang['datetime']['Wed'] = 'Сре';
$lang['datetime']['Thu'] = 'Чет';
$lang['datetime']['Fri'] = 'Пет';
$lang['datetime']['Sat'] = 'Саб';
$lang['datetime']['January'] = 'Јануари';
$lang['datetime']['February'] = 'Февруари';
$lang['datetime']['March'] = 'Март';
$lang['datetime']['April'] = 'Април';
$lang['datetime']['May'] = 'Мај';
$lang['datetime']['June'] = 'Јуни';
$lang['datetime']['July'] = 'Јули';
$lang['datetime']['August'] = 'Август';
$lang['datetime']['September'] = 'Септември';
$lang['datetime']['October'] = 'Октомври';
$lang['datetime']['November'] = 'Ноември';
$lang['datetime']['December'] = 'Декември';
$lang['datetime']['Jan'] = 'Јан';
$lang['datetime']['Feb'] = 'Фед';
$lang['datetime']['Mar'] = 'Мар';
$lang['datetime']['Apr'] = 'Апр';
$lang['datetime']['May'] = 'Мај';
$lang['datetime']['Jun'] = 'Јун';
$lang['datetime']['Jul'] = 'Jул';
$lang['datetime']['Aug'] = 'Aвг';
$lang['datetime']['Sep'] = 'Сеп';
$lang['datetime']['Oct'] = 'Окт';
$lang['datetime']['Nov'] = 'Ное';
$lang['datetime']['Dec'] = 'Дек';

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = 'Информации';
$lang['Critical_Information'] = 'Критична информација';

$lang['General_Error'] = 'Општа грешка';
$lang['Critical_Error'] = 'Критична грешка';
$lang['An_error_occured'] = 'Се појави грешка';
$lang['A_critical_error'] = 'Се појави критична грешка';

//
// That's all Folks!
// -------------------------------------------------

?>