<?php
/***************************************************************************
 *                            lang_main.php [Russian]
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

//
// Translation performed by Alexey V. Borzov (borz_off)
// borz_off@rdw.ru
//
// Transformed into "tu" form by Svyatozar on 2002-09-23
// svyatozar@pochtamt.ru
//

//setlocale(LC_ALL, "ru_RU.CP1251");
$lang['ENCODING'] = "windows-1251";
$lang['DIRECTION'] = "ltr";
$lang['LEFT'] = "налево";
$lang['RIGHT'] = "направо";
$lang['DATE_FORMAT'] =  "d.m.Y"; // This should be changed to the default date format for your language, php date() format

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = "Форум";
$lang['Category'] = "Категория";
$lang['Topic'] = "Тема";
$lang['Topics'] = "Темы";
$lang['Replies'] = "Ответ[а/ов]";
$lang['Views'] = "Просмотр[а/ов]";
$lang['Post'] = "Сообщение";        // ???
$lang['Posts'] = "Сообщения";       // ???
$lang['Posted'] = "Добавлено";
$lang['Username'] = "Имя";
$lang['Password'] = "Пароль";
$lang['Email'] = "Email";
$lang['Poster'] = "Автор";          // ???
$lang['Author'] = "Автор";
$lang['Time'] = "Время";
$lang['Hours'] = "Часы";
$lang['Message'] = "Сообщение";

$lang['1_Day'] = "за последний день";
$lang['7_Days'] = "за последние 7 дней";
$lang['2_Weeks'] = "за последние 2 недели";
$lang['1_Month'] = "за последний месяц";
$lang['3_Months'] = "за последние 3 месяца";
$lang['6_Months'] = "за последние 6 месяцев";
$lang['1_Year'] = "за последний год";

$lang['Go'] = "Перейти";
$lang['Jump_to'] = "Перейти";
$lang['Submit'] = "Отправить";
$lang['Reset'] = "Вернуть";
$lang['Cancel'] = "Отмена";
$lang['Preview'] = "Предв. просмотр";
$lang['Confirm'] = "Подтвердите";
$lang['Spellcheck'] = "Орфография";
$lang['Yes'] = "Да";
$lang['No'] = "Нет";
$lang['Enabled'] = "Включено";
$lang['Disabled'] = "Выключено";
$lang['Error'] = "Ошибка";

$lang['Next'] = "След.";
$lang['Previous'] = "Пред.";
$lang['Goto_page'] = "На страницу";
$lang['Joined'] = "Зарегистрирован";
$lang['IP_Address'] = "Адрес IP";

$lang['Select_forum'] = "Выбери форум";
$lang['View_latest_post'] = "Посмотреть последнее сообщение";
$lang['View_newest_post'] = "Самое новое сообщение";
$lang['Page_of'] = "Страница <b>%d</b> из <b>%d</b>"; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = "Номер ICQ";
$lang['AIM'] = "Адрес AIM";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Forum_Index'] = "Список форумов %s";

$lang['Post_new_topic'] = "Начать новую тему";
$lang['Reply_to_topic'] = "Ответить на тему";
$lang['Reply_with_quote'] = "Ответить с цитатой";

$lang['Click_return_topic'] = "%sВернуться в тему%s";
$lang['Click_return_login'] = "%sПопробовать еще раз%s";
$lang['Click_return_forum'] = "%sВернуться в форум%s";
$lang['Click_view_message'] = "%sПросмотреть свое сообщение%s";
$lang['Click_return_modcp'] = "%sВернуться к панели модерации%s";
$lang['Click_return_group'] = "%sВернуться к информации о группах%s";

$lang['Admin_panel'] = "Перейти в администраторский раздел";

$lang['Board_disable'] = "К сожалению, эти форумы отключены. Попробуй зайти попозже";


//
// Global Header strings
//
$lang['Registered_users'] = "Зарегистрированные пользователи:";
$lang['Browsing_forum'] = "Сейчас этот форум просматривают:";
$lang['Online_users_zero_total'] = "Сейчас посетителей на форуме: <b>0</b>, из них ";
$lang['Online_users_total'] = "Сейчас посетителей на форуме: <b>%d</b>, из них ";
$lang['Online_user_total'] = "Сейчас посетителей на форуме: <b>%d</b>, из них ";
$lang['Reg_users_zero_total'] = "зарегистрированных: 0, ";
$lang['Reg_users_total'] = "зарегистрированных: %d, ";
$lang['Reg_user_total'] = "зарегистрированных: %d, ";
$lang['Hidden_users_zero_total'] = "скрытых: 0 и ";
$lang['Hidden_users_total'] = "скрытых: %d и ";
$lang['Hidden_user_total'] = "скрытых: %d и ";
$lang['Guest_users_zero_total'] = "гостей: 0";
$lang['Guest_users_total'] = "гостей: %d";
$lang['Guest_user_total'] = "гостей: %d";
$lang['Record_online_users'] = "Больше всего посетителей (<b>%s</b>) здесь было %s"; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = "%sАдминистратор%s";
$lang['Mod_online_color'] = "%sМодератор%s";

$lang['You_last_visit'] = "Дата твоего последнего посещения: %s";
$lang['Current_time'] = "Текущее время %s"; // %s replaced by time

$lang['Search_new'] = "Найти сообщения с твоего последнего посещения";
$lang['Search_your_posts'] = "Найти свои сообщения";
$lang['Search_unanswered'] = "Найти сообщения без ответов";

$lang['Register'] = "Регистрация";
$lang['Profile'] = "Профиль";
$lang['Edit_profile'] = "Редактировать свой профиль";
$lang['Search'] = "Поиск";
$lang['Memberlist'] = "Пользователи";
$lang['FAQ'] = "FAQ";
$lang['BBCode_guide'] = "Руководство по BBCode";
$lang['Usergroups'] = "Группы";
$lang['Last_Post'] = "Последнее сообщение";
$lang['Moderator'] = "Модератор";
$lang['Moderators'] = "Модераторы";


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = "Наши пользователи не оставили ни одного сообщения"; // Number of posts
$lang['Posted_article_total'] = "Наши пользователи оставили сообщений: <b>%d</b>"; // Number of posts
$lang['Posted_articles_total'] = "Наши пользователи оставили сообщений: <b>%d</b>"; // Number of posts
$lang['Registered_users_zero_total'] = "У нас нет зарегистрированных пользователей"; // # registered users
$lang['Registered_user_total'] = "Всего зарегистрированных пользователей: <b>%d</b>"; // # registered users
$lang['Registered_users_total'] = "Всего зарегистрированных пользователей: <b>%d</b>"; // # registered users
$lang['Newest_user'] = "Последний зарегистрированный пользователь: <b>%s%s%s</b>"; // username

$lang['No_new_posts_last_visit'] = "Нет новых сообщений с последнего посещения";
$lang['No_new_posts'] = "Нет новых сообщений";
$lang['New_posts'] = "Новые сообщения";
$lang['New_post'] = "Новое сообщение";
$lang['No_new_posts_hot'] = "Нет новых сообщений [ Популярная тема ]";
$lang['New_posts_hot'] = "Новые сообщения [ Популярная тема ]";
$lang['No_new_posts_locked'] = "Нет новых сообщений [ Тема закрыта ]";
$lang['New_posts_locked'] = "Новые сообщения [ Тема закрыта ]";
$lang['Forum_is_locked'] = "Форум закрыт";


//
// Login
//
$lang['Enter_password'] = "Введи свои имя и пароль для входа в систему";
$lang['Login'] = "Вход";
$lang['Logout'] = "Выход";

$lang['Forgotten_password'] = "Забыл(а) пароль?";

$lang['Log_me_in'] = "Автоматически входить при каждом посещении";

$lang['Error_login'] = "Ты ввел(а) неверное/неактивное имя пользователя или неверный пароль.";


//
// Index page
//
$lang['Index'] = "Главная";
$lang['No_Posts'] = "Нет сообщений";
$lang['No_forums'] = "На этом сайте нет форумов";

$lang['Private_Message'] = "Личное сообщение";
$lang['Private_Messages'] = "Личные сообщения";
$lang['Who_is_Online'] = "Кто сейчас на форуме";

$lang['Mark_all_forums'] = "Отметить все форумы как прочитанные";
$lang['Forums_marked_read'] = "Все форумы были отмечены как прочитанные";


//
// Viewforum
//
$lang['View_forum'] = "Просмотр форума";

$lang['Forum_not_exist'] = "Такого форума не существует";
$lang['Reached_on_error'] = "Ты попал(а) на эту страницу из-за ошибки";

$lang['Display_topics'] = "Показать темы";
$lang['All_Topics'] = "все темы";

$lang['Topic_Announcement'] = "<b>Объявление:</b>";
$lang['Topic_Sticky'] = "<b>Важная:</b>";
$lang['Topic_Moved'] = "<b>Перемещена:</b>";
$lang['Topic_Poll'] = "<b>[ Опрос ]</b>";

$lang['Mark_all_topics'] = "Отметить все темы как прочитанные";
$lang['Topics_marked_read'] = "Все темы в этом форуме были отмечены как прочитанные";

$lang['Rules_post_can'] = "Ты <b>можешь</b> начинать темы";
$lang['Rules_post_cannot'] = "Ты <b>не можешь</b> начинать темы";
$lang['Rules_reply_can'] = "Ты <b>можешь</b> отвечать на сообщения";
$lang['Rules_reply_cannot'] = "Ты <b>не можешь</b> отвечать на сообщения";
$lang['Rules_edit_can'] = "Ты <b>можешь</b> редактировать свои сообщения";
$lang['Rules_edit_cannot'] = "Ты <b>не можешь</b> редактировать свои сообщения";
$lang['Rules_delete_can'] = "Ты <b>можешь</b> удалять свои сообщения";
$lang['Rules_delete_cannot'] = "Ты <b>не можешь</b> удалять свои сообщения";
$lang['Rules_vote_can'] = "Ты <b>можешь</b> голосовать в опросах";
$lang['Rules_vote_cannot'] = "Ты <b>не можешь</b> голосовать в опросах";
$lang['Rules_moderate'] = "Ты <b>можешь</b> %sмодерировать этот форум%s"; // %s replaced by a href

$lang['No_topics_post_one'] = "В этом форуме пока нет сообщений<br />Щелкни <b>Начать новую тему</b>, и твое сообщение станет первым.";


//
// Viewtopic
//
$lang['View_topic'] = "Просмотр темы";

$lang['Guest'] = 'Гость';
$lang['Post_subject'] = "Заголовок сообщения";
$lang['View_next_topic'] = "Следующая тема";
$lang['View_previous_topic'] = "Предыдущая тема";
$lang['Submit_vote'] = "Проголосовать";
$lang['View_results'] = "Результаты";

$lang['No_newer_topics'] = "В этом форуме нет более новых тем";
$lang['No_older_topics'] = "В этом форуме нет более старых тем";
$lang['Topic_post_not_exist'] = "Темы, которую ты запросил(а), не существует.";
$lang['No_posts_topic'] = "В этой теме нет сообщений";

$lang['Display_posts'] = "Показать сообщения";
$lang['All_Posts'] = "все сообщения";
$lang['Newest_First'] = "Начиная с новых";
$lang['Oldest_First'] = "Начиная со старых";

$lang['Back_to_top'] = "Вернуться к началу";

$lang['Read_profile'] = "Посмотреть профиль"; // Followed by username of poster
$lang['Send_email'] = "Отправить e-mail "; // Followed by username of poster
$lang['Visit_website'] = "Посетить сайт автора";
$lang['ICQ_status'] = "Статус ICQ";
$lang['Edit_delete_post'] = "Изменить/удалить это сообщение";
$lang['View_IP'] = "Показать IP адрес автора";
$lang['Delete_post'] = "Удалить это сообщение";

$lang['wrote'] = "писал(а)"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "Цитата"; // comes before bbcode quote output.
$lang['Code'] = "Код"; // comes before bbcode code output.

$lang['Edited_time_total'] = "Последний раз редактировалось: %s (%s), всего редактировалось %d раз"; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = "Последний раз редактировалось: %s (%s), всего редактировалось %d раз(а)"; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = "Закрыть тему";
$lang['Unlock_topic'] = "Вновь открыть тему";
$lang['Move_topic'] = "Перенести тему";
$lang['Delete_topic'] = "Удалить тему";
$lang['Split_topic'] = "Разделить тему";

$lang['Stop_watching_topic'] = "Перестать следить за ответами";
$lang['Start_watching_topic'] = "Следить за ответами в теме";
$lang['No_longer_watching'] = "Ты больше не следишь за ответами в этой теме";
$lang['You_are_watching'] = "Теперь ты следишь за ответами в этой теме";

$lang['Total_votes'] = "Всего проголосовало";

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "Сообщение";
$lang['Topic_review'] = "Обзор темы";

$lang['No_post_mode'] = "Не указан режим сообщения";

$lang['Post_a_new_topic'] = "Начать новую тему";
$lang['Post_a_reply'] = "Ответить";
$lang['Post_topic_as'] = "Статус создаваемой темы";
$lang['Edit_Post'] = "Редактировать сообщение";
$lang['Options'] = "Настройки";

$lang['Post_Announcement'] = "Объявление";
$lang['Post_Sticky'] = "Важная";
$lang['Post_Normal'] = "Обычная";

$lang['Confirm_delete'] = "Ты уверен(а), что хочешь удалить это сообщение?";
$lang['Confirm_delete_poll'] = "Ты уверен(а), что хочешь удалить этот опрос?";

$lang['Flood_Error'] = "Ты не можешь отправить следующее сообщение сразу после предыдущего. Пожалуйста, попробуй чуть попозже.";
$lang['Empty_subject'] = "Ты должен(а) указать заголовок сообщения, когда начинаешь новую тему";
$lang['Empty_message'] = "Ты должен(а) ввести текст сообщения";
$lang['Forum_locked'] = "Этот форум закрыт, ты не можешь писать новые сообщения и редактировать старые.";
$lang['Topic_locked'] = "Эта тема закрыта, ты не можешь писать ответы и редактировать сообщения.";
$lang['No_post_id'] = "Ты должен(а) выбрать сообщение для редактирования";
$lang['No_topic_id'] = "Ты должен(а) выбрать тему для ответа";
$lang['No_valid_mode'] = "Ты можешь только создавать темы, отвечать и редактировать сообщения. Вернись и попробуй еще раз.";
$lang['No_such_post'] = "Сообщение отсутствует. Вернись и попробуй еще раз.";
$lang['Edit_own_posts'] = "Ты можешь редактировать только свои собственные сообщения";
$lang['Delete_own_posts'] = "Ты можешь удалять только свои собственные сообщения";
$lang['Cannot_delete_replied'] = "Ты не сможешь удалить сообщение, на которое были получены ответы";
$lang['Cannot_delete_poll'] = "Ты не можешь удалить уже активный опрос";
$lang['Empty_poll_title'] = "Ты должен(а) ввести заголовок для опроса";
$lang['To_few_poll_options'] = "Ты должен(а) ввести не менее двух вариантов ответа";
$lang['To_many_poll_options'] = "Слишком много вариантов ответа";
$lang['Post_has_no_poll'] = "В этом сообщении нет опроса";

$lang['Add_poll'] = "Добавить опрос";
$lang['Add_poll_explain'] = "Если ты не хочешь добавлять опрос к своему сообщению, оставь поля пустыми";
$lang['Poll_question'] = "Вопрос";
$lang['Poll_option'] = "Вариант ответа";
$lang['Add_option'] = "Добавить еще вариант";
$lang['Update'] = "Обновить";
$lang['Delete'] = "Удалить";
$lang['Poll_for'] = "Опрос должен продолжаться";
$lang['Days'] = "Дней"; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = "[ Введи 0 или оставь поле пустым, чтобы опрос не кончался ]";
$lang['Delete_poll'] = "Удалить опрос";

$lang['Disable_HTML_post'] = "Отключить HTML в этом сообщении";
$lang['Disable_BBCode_post'] = "Отключить ББ-код в этом сообщении";
$lang['Disable_Smilies_post'] = "Отключить улыбочки в этом сообщении";

$lang['HTML_is_ON'] = "HTML <u>ВКЛЮЧЕН</u>";
$lang['HTML_is_OFF'] = "HTML <u>ВЫКЛЮЧЕН</u>";
$lang['BBCode_is_ON'] = "%sBBCode%s <u>ВКЛЮЧЕН</u>";
$lang['BBCode_is_OFF'] = "%sBBCode%s <u>ВЫКЛЮЧЕН</u>";
$lang['Smilies_are_ON'] = "Улыбочки <u>ВКЛЮЧЕНЫ</u>";
$lang['Smilies_are_OFF'] = "Улыбочки <u>ВЫКЛЮЧЕНЫ</u>";

$lang['Attach_signature'] = "Присоединить подпись (подпись можно изменять в профиле)";
$lang['Notify'] = "Сообщать мне о получении ответа";
$lang['Delete_post'] = "Удалить сообщение";

$lang['Stored'] = "Твое сообщение было успешно добавлено";
$lang['Deleted'] = "Твое сообщение было успешно удалено";
$lang['Poll_delete'] = "Твой опрос был успешно удален";
$lang['Vote_cast'] = "Твой голос был учтен";

$lang['Topic_reply_notification'] = "Уведомление об ответе в теме";

$lang['bbcode_b_help'] = "Жирный текст: [b]текст[/b]  (alt+b)";
$lang['bbcode_i_help'] = "Наклонный текст: [i]текст[/i]  (alt+i)";
$lang['bbcode_u_help'] = "Подчеркнутый текст: [u]текст[/u]  (alt+u)";
$lang['bbcode_q_help'] = "Цитата: [quote]текст[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "Код (программа): [code]код[/code]  (alt+c)";
$lang['bbcode_l_help'] = "Список: [list]текст[/list] (alt+l)";
$lang['bbcode_o_help'] = "Нумерованный список: [list=]текст[/list]  (alt+o)";
$lang['bbcode_p_help'] = "Вставить картинку: [img]http://image_url[/img]  (alt+p)";
$lang['bbcode_w_help'] = "Вставить ссылочку: [url]http://url[/url] или [url=http://url]текст ссылки[/url]  (alt+w)";
$lang['bbcode_a_help'] = "Закрыть все открытые таги ББ-кода";
$lang['bbcode_s_help'] = "Цвет шрифта: [color=red]текст[/color]  Подсказка: можно использовать color=#FF0000";
$lang['bbcode_f_help'] = "Размер шрифта: [size=x-small]маленький текст[/size]";
$lang['bbcode_k_help'] = "Прокручивающийся текст: [scroll]text[/scroll] (alt+k)";

$lang['Emoticons'] = "Улыбочки";
$lang['More_emoticons'] = "Дополнительные улыбочки";

$lang['Font_color'] = "Цвет шрифта";
$lang['color_default'] = "По умолчанию";
$lang['color_dark_red'] = "Темно-красный";
$lang['color_red'] = "Красный";
$lang['color_orange'] = "Оранжевый";
$lang['color_brown'] = "Коричневый";
$lang['color_yellow'] = "Желтый";
$lang['color_green'] = "Зеленый";
$lang['color_olive'] = "Оливковый";
$lang['color_cyan'] = "Голубой";
$lang['color_blue'] = "Синий";
$lang['color_dark_blue'] = "Темно-синий";
$lang['color_indigo'] = "Индиго";
$lang['color_violet'] = "Фиолетовый";
$lang['color_white'] = "Белый";
$lang['color_black'] = "Черный";

$lang['Font_size'] = "Размер шрифта";
$lang['font_tiny'] = "Очень маленький";
$lang['font_small'] = "Маленький";
$lang['font_normal'] = "Обычный";
$lang['font_large'] = "Большой";
$lang['font_huge'] = "Огромный";

$lang['Close_Tags'] = "Закрыть теги";
$lang['Styles_tip'] = "Подсказка: Можно быстро применить стили к выделенному тексту";


//
// Private Messaging
//
$lang['Private_Messaging'] = "Личные сообщения";

$lang['Login_check_pm'] = "Войти и проверить личные сообщения";
$lang['New_pms'] = "Новых сообщений: %d"; // You have 2 new messages
$lang['New_pm'] = "Новых сообщений: %d"; // You have 1 new message
$lang['No_new_pm'] = "Новых сообщений нет";
$lang['Unread_pms'] = "Непрочитанных сообщений: %d";
$lang['Unread_pm'] = "Непрочитанных сообщений: %d";
$lang['No_unread_pm'] = "Нет непрочитанных сообщений";
$lang['You_new_pm'] = "Пришло новое личное сообщение";
$lang['You_new_pms'] = "Пришли новые личные сообщения";
$lang['You_no_new_pm'] = "У тебя нет новых личных сообщений";

$lang['Inbox'] = "Входящие";
$lang['Outbox'] = "Исходящие";
$lang['Savebox'] = "Сохраненные";
$lang['Sentbox'] = "Отправленные";
$lang['Flag'] = "Флаг";
$lang['Subject'] = "Тема";
$lang['From'] = "От";
$lang['To'] = "Кому";
$lang['Date'] = "Дата";
$lang['Mark'] = "Отметка";
$lang['Sent'] = "Отправлено";
$lang['Saved'] = "Сохранено";
$lang['Delete_marked'] = "Удалить отмеченное";
$lang['Delete_all'] = "Удалить все";
$lang['Save_marked'] = "Сохранить отмеченное";
$lang['Save_message'] = "Сохранить сообщение";
$lang['Delete_message'] = "Удалить сообщение";

$lang['Display_messages'] = "Показать сообщения"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "Все сообщения";

$lang['No_messages_folder'] = "В этой папке нет сообщений";

$lang['PM_disabled'] = "Возможность отправки личных сообщений на этих форумах была отключена";
$lang['Cannot_send_privmsg'] = "Тебе не разрешено отправлять личные сообщения";
$lang['No_to_user'] = "Нужно указать имя получателя этого сообщения";
$lang['No_such_user'] = "Такого пользователя не существует";

$lang['Disable_HTML_pm'] = "Отключить HTML в этом сообщении";
$lang['Disable_BBCode_pm'] = "Отключить ББ-код в этом сообщении";
$lang['Disable_Smilies_pm'] = "Отключить улыбочки в этом сообщении";

$lang['Message_sent'] = "Сообщение было отправлено";

$lang['Click_return_inbox'] = "%sВернуться в папку «Входящие»%s";
$lang['Click_return_index'] = "%sВернуться к списку форумов%s";

$lang['Send_a_new_message'] = "Отправить личное сообщение";
$lang['Send_a_reply'] = "Ответить на личное сообщение";
$lang['Edit_message'] = "Редактировать личное сообщение";

$lang['Notification_subject'] = "Пришло новое личное сообщение";

$lang['Find_username'] = "Найти пользователя";
$lang['Find'] = "Найти";
$lang['No_match'] = "Не найдено";

$lang['No_post_id'] = "Не указан ID";
$lang['No_such_folder'] = "Такой папки нет";
$lang['No_folder'] = "Не указана папка";

$lang['Mark_all'] = "Выделить все";
$lang['Unmark_all'] = "Снять выделение";

$lang['Confirm_delete_pm'] = "Ты уверен(а), что хочешь удалить это сообщение?";
$lang['Confirm_delete_pms'] = "Ты уверен(а), что хочешь удалить эти сообщения?";

$lang['Inbox_size'] = "Твоя папка «Входящие» заполнена на %d%%"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "Твоя папка «Отправленные» заполнена на %d%%";
$lang['Savebox_size'] = "Твоя папка «Сохраненные» заполнена на %d%%";

$lang['Click_view_privmsg'] = "%sПерейти в папку «Входящие»%s";


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "Профиль пользователя %s"; // %s is username
$lang['About_user'] = "О пользователе %s"; // слово "пользователь" - чтобы не заморачиваться с мужским/женским родом

$lang['Preferences'] = "Личные настройки";
$lang['Items_required'] = "Поля отмеченные * обязательны к заполнению, если не указано обратное";
$lang['Registration_info'] = "Регистрационная информация";
$lang['Profile_info'] = "Профиль";
$lang['Profile_info_warn'] = "Эта информация будет в открытом доступе";
$lang['Avatar_panel'] = "Изменение маски (аватара)";
$lang['Avatar_gallery'] = "Выбор маски из галлереи";

$lang['Website'] = "Сайт";
$lang['Location'] = "Откуда";
$lang['Contact'] = "Как связаться с"; // Как связаться с Vasya_Poopkin
$lang['Email_address'] = "Адрес e-mail";
$lang['Email'] = "E-mail";
$lang['Send_private_message'] = "Отправить личное сообщение";
$lang['Hidden_email'] = "[ скрыт ]";
$lang['Search_user_posts'] = "Искать все собщения этого пользователя";
$lang['Interests'] = "Интересы";
$lang['Occupation'] = "Род занятий";
$lang['Poster_rank'] = "Звание";

$lang['Total_posts'] = "Всего сообщений";
$lang['User_post_pct_stats'] = "%.2f%% от общего числа"; // 15% of total
$lang['User_post_day_stats'] = "%.2f сообщений в день"; // 1.5 posts per day
$lang['Search_user_posts'] = "Найти все сообщения пользователя %s"; // Find all posts by username

$lang['No_user_id_specified'] = "Такого пользователя не существует";
$lang['Wrong_Profile'] = "Ты не можешь редактировать чужой профиль.";

$lang['Only_one_avatar'] = "Может быть указан только один тип маски (аватара)";
$lang['File_no_data'] = "Файл по указанному тобой адресу не содержит данных";
$lang['No_connection_URL'] = "Невозможно установить соединения с указанным тобой адресом";
$lang['Incomplete_URL'] = "Ты указал(а) неполный адрес";
$lang['Wrong_remote_avatar_format'] = "Неверный адрес удаленно-хранимой маски (аватары)";
$lang['No_send_account_inactive'] = "Пароль не может быть выслан, так как твоя учетная запись неактивна. Обратись к администратору форума за дополнительной информацией.";

$lang['Always_smile'] = "Улыбочки всегда включены";
$lang['Always_html'] = "HTML всегда включен";
$lang['Always_bbcode'] = "ББ-код всегда включен";
$lang['Always_add_sig'] = "Всегда присоединять мою подпись";
$lang['Always_notify'] = "Всегда сообщать мне об ответах";
$lang['Always_notify_explain'] = "Когда кто-нибудь ответит на тему, в которую ты писал(а), тебе высылается e-mail. Это можно также настроить при размещении сообщения.";

$lang['Board_style'] = "Внешний вид форумов";
$lang['Board_lang'] = "Язык";
$lang['No_themes'] = "В базе нет цветовых схем";
$lang['Timezone'] = "Часовой пояс";
$lang['Date_format'] = "Формат даты";
$lang['Date_format_explain'] = "Синтаксис идентичен функции <a href=\"http://www.php.net/date\" target=\"_other\">date()</a> языка PHP";
$lang['Signature'] = "Подпись";
$lang['Signature_explain'] = "Это текст, который можно добавлять к размещаемым вами сообщениям. Длина его ограничена %d символами.";
$lang['Public_view_email'] = "Всегда показывать мой адрес электронной почты";

$lang['Current_password'] = "Текущий пароль";
$lang['New_password'] = "Новый пароль";
$lang['Confirm_password'] = "Подтверди пароль";
$lang['Confirm_password_explain'] = "Ты должен(а) указать свой текущий пароль, если хочешь изменить его или поменять свой адрес электронной почты.";
$lang['password_if_changed'] = "Указывай пароль только если ты хочешь его поменять";
$lang['password_confirm_if_changed'] = "Подтверждать пароль нужно в том случае, если ты изменил(а) его выше.";

$lang['Avatar'] = "Маска (аватар)";
$lang['Avatar_explain'] = "Небольшое изображение под информацией о тебе в сообщениях. Может быть показано только одно изображение, шириной не более %d пикселов, высотой не более %d пикселов и объемом не более %d кб.";
$lang['Upload_Avatar_file'] = "Загрузить маску (аватар) с твоего компьютера";
$lang['Upload_Avatar_URL'] = "Загрузить маску (аватар) с адреса в Интернете";
$lang['Upload_Avatar_URL_explain'] = "Введи адрес по которому находится файл с изображением, и он будет скопирован на этот сайт.";
$lang['Pick_local_Avatar'] = "Выбрать маску (аватар) из галереи";
$lang['Link_remote_Avatar'] = "Показывать маску (аватар) с другого сервера";
$lang['Link_remote_Avatar_explain'] = "Введи адрес, на котором лежит изображение твоей маски (если есть).";
$lang['Avatar_URL'] = "Адрес изображения маски (аватар)";
$lang['Select_from_gallery'] = "Выбрать маску (аватар) из галереи";
$lang['View_avatar_gallery'] = "Показать галерею";

$lang['Select_avatar'] = "Выберите аватару";
$lang['Return_profile'] = "Вернуться к профилю";
$lang['Select_category'] = "Выбери категорию";

$lang['Delete_Image'] = "Удалить изображение";
$lang['Current_Image'] = "Текущее изображение";

$lang['Notify_on_privmsg'] = "Уведомлять о новых личных сообщениях";
$lang['Popup_on_privmsg'] = "Открывать новое окно при новом личном сообщении";
$lang['Popup_on_privmsg_explain'] = "В некоторых шаблонах может открываться новое окно браузера с уведомлением о приходе нового личного сообщения.";
$lang['Hide_user'] = "Скрывать твое пребывание на форуме";

$lang['Profile_updated'] = "Твой профиль был изменен";
$lang['Profile_updated_inactive'] = "Твой профиль был изменен, но ты изменил(а) важные данные, так что теперь твоя учетная запись неактивна. Проверь свой почтовый ящик, чтобы узнать как вновь активизировать учетную запись или, если требуется одобрение администратора, подожди пока это сделает администратор.";

$lang['Password_mismatch'] = "Введенные пароли не совпадают";
$lang['Current_password_mismatch'] = "Введенный тобой пароль не совпадает с паролем из базы";
$lang['Password_long'] = "Твой пароль должен быть не длиннее 32 символов";
$lang['Username_taken'] = "К сожалению, пользователь с таким именем уже существует";
$lang['Username_invalid'] = "Это имя содержит неподходящие символы, (например \")";
$lang['Username_disallowed'] = "Это имя было запрещено к использованию";
$lang['Email_taken'] = "Этот адрес электронной почты уже занят другим пользователем";
$lang['Email_banned'] = "Этот адрес электронной почты находится в черном списке";
$lang['Email_invalid'] = "Этот адрес электронной почты неправилен";
$lang['Invalid_username'] = "Запрошенное имя пользователя уже занято, запрещено, либо содержит неподходящие символы (например \")";
$lang['Signature_too_long'] = "Слишком длинная подпись";
$lang['Fields_empty'] = "Ты должен(а) заполнить обязательные поля";
$lang['Avatar_filetype'] = "Файл маски (аватар) должен быть .jpg, .gif или .png";
$lang['Avatar_filesize'] = "Объем файла маски (аватар) должен быть не более %d кб";
$lang['Avatar_imagesize'] = "Аватар должна быть не больше %d пикселов в ширину и %d пикселов в высоту";

$lang['Welcome_subject'] = "Добро пожаловать на форумы %s";
$lang['New_account_subject'] = "Новый пользователь";
$lang['Account_activated_subject'] = "Учетная запись активизирована";

$lang['Account_added'] = "Благодарим за регистрацию, учетная запись была создана. Ты можешь войти в систему, используя свои имя и пароль.";
$lang['Account_inactive'] = "Учетная запись была создана. На этом форуме требуется активизация учетной записи, ключ для активизации был выслан на введенный тобой адрес. Проверь свою почту для более подробной информации.";
$lang['Account_inactive_admin'] = "Учетная запись была создана. На этом форуме требуется активизация новой учетной записи администраторами. Им было отправлено сообщение по электронной почте, и, как только они активизируют твою учетную запись, ты получишь уведомление.";
$lang['Account_active'] = "Твоя учетная запись была активизирована. Благодарим за регистрацию.";
$lang['Account_active_admin'] = "Твоя учетная запись была активизирована.";
$lang['Reactivate'] = "Вновь активизировать учетную запись";
$lang['COPPA'] = "Твоя учетная запись была создана, но теперь она должна быть одобрена, более подробная информация была выслана тебе по электронной почте.";

$lang['Registration'] = "Условия регистрации";
$lang['Reg_agreement'] = "Хотя администраторы и модераторы этого форума стараются удалять или редактировать неприемлемые сообщения как можно быстрее, все сообщения просмотреть невозможно. Таким образом ты признаешь,  что сообщения на этих форумах отражают точки зрения их авторов, а не администрации форумов (кроме сообщений, размещенных ее представителями) и администрация не может быть ответственна за их содержание.<br /><br /> Ты соглашаешься не размещать оскорбительных, угрожающих, клеветнических сообщений, порнографических сообщений, призывов к национальной розни и прочих сообщений, могущих нарушить соответствующие законы. Попытки размещения таких сообщений могут привести к немедленному отключению от форумов (при этом твой провайдер будет поставлен в известность). IP адреса всех сообщений сохраняются для возможности проведения такой политики. Ты соглашаешься с тем, что администраторы форума имеют право удалить, отредактировать, перенести или закрыть любую тему в любое время по своему усмотрению. Как пользователь ты согласен(а) с тем, что введенная тобой информация будет храниться в базе данных. Хотя эта информация не будет открыта третьим лицам без твоего разрешения, администрация форумов не может быть ответственна за действия хакеров, которые могут привести к несанкционированному доступу к ней.<br /><br /> Эти форумы используют пряники (cookies) для хранения информации на твоем компьютере. Эти пряники не содержат никакой информации из введенной тобой и служат лишь для улучшения качества работы форумов. Твой адрес электронной почты используется лишь для подтверждения твоей регистрации и пароля (и для высылки нового пароля если ты забудешь текущий).<br /><br />Нажатием на кнопку регистрации ты подтверждаешь свое согласие с этими условиями.";

$lang['Agree_under_13'] = "Я согласен(а) с этими условиями и мне <b>меньше</b> 13 лет";
$lang['Agree_over_13'] = "Я согласен(а) с этими условиями и я <b>старше</b> 13 лет";
$lang['Agree_not'] = "Я не согласен с этими условиями";

$lang['Wrong_activation'] = "Введенный тобой ключ активизации не совпадает с хранящимся в базе";
$lang['Send_password'] = "Прислать новый пароль";
$lang['Password_updated'] = "Новый пароль был создан, проверь почтовый ящик, чтобы узнать как его активизировать";
$lang['No_email_match'] = "Введенный тобой адрес электронной почты не совпадает с записанным на этого пользователя";
$lang['New_password_activation'] = "Активизация нового пароля";
$lang['Password_activated'] = "Твоя учетная запись была вновь активизирована. Для входа в систему используй пароль из присланного тебе письма.";

$lang['Send_email_msg'] = "Отправить электронную почту";
$lang['No_user_specified'] = "Пользователь не был выбран";
$lang['User_prevent_email'] = "Пользователь не желает получать электронную почту. Попробуй отправить ему/ей личное";
$lang['User_not_exist'] = "Пользователя не существует";
$lang['CC_email'] = "Отправить копию сообщения самому себе";
$lang['Email_message_desc'] = "Сообщение будет отправлено в виде простого текста, не включай в него HTML или ББ-код. В качестве обратного адреса будет показываться твой адрес электронной почты.";
$lang['Flood_email_limit'] = "Ты не можешь отправить сообщение электронной почты сразу после предыдущего, попробуй сделать это попозже.";
$lang['Recipient'] = "Получатель";
$lang['Email_sent'] = "Сообщение было отправлено";
$lang['Send_email'] = "Отправить сообщения электронной почты";
$lang['Empty_subject_email'] = "Ты должен(а) указать тему сообщения";
$lang['Empty_message_email'] = "Ты должен(а) указать текст сообщения для отправки";


//
// Memberslist
//
$lang['Select_sort_method'] = "Упорядочить по";
$lang['Sort'] = "Упорядочить";
$lang['Sort_Top_Ten'] = "десять самых активных участников";
$lang['Sort_Joined'] = "дате регистрации";
$lang['Sort_Username'] = "имени пользователя";
$lang['Sort_Location'] = "местонахождению";
$lang['Sort_Posts'] = "количеству сообщений";
$lang['Sort_Email'] = "адресу e-mail";
$lang['Sort_Website'] = "адресу сайта";
$lang['Sort_Ascending'] = "по возрастанию";
$lang['Sort_Descending'] = "по убыванию";
$lang['Order'] = ""; // не нужно, в английском используется в контексте "Order ascending"


//
// Group control panel
//
$lang['Group_Control_Panel'] = "Пульт управления группами";
$lang['Group_member_details'] = "Информация о членстве в группах";
$lang['Group_member_join'] = "Вступить в группу";

$lang['Group_Information'] = "Информация о группе";
$lang['Group_name'] = "Название группы";
$lang['Group_description'] = "Описание группы";
$lang['Group_membership'] = "Членство в группе";
$lang['Group_Members'] = "Члены группы";
$lang['Group_Moderator'] = "Модератор группы";
$lang['Pending_members'] = "Кандидаты в члены группы";

$lang['Group_type'] = "Тип группы";
$lang['Group_open'] = "Группа с открытым членством";
$lang['Group_closed'] = "Группа с закрытым членством";
$lang['Group_hidden'] = "Скрытая группа";

$lang['Current_memberships'] = "Являешься членом групп";
$lang['Non_member_groups'] = "Не являешься членом групп";
$lang['Memberships_pending'] = "Кандидат в члены групп";

$lang['No_groups_exist'] = "Нет ни одной группы";
$lang['Group_not_exist'] = "Такой группы не существует";

$lang['Join_group'] = "Вступить в группу";
$lang['No_group_members'] = "В этой группе нет ни одного члена";
$lang['Group_hidden_members'] = "Эта группа скрыта, ты не можешь посмотреть ее состав";
$lang['No_pending_group_members'] = "В этой группе нет кандидатов в члены";
$lang["Group_joined"] = "Ты попросил о вступлении в группу. Когда твою просьбу одобрит модератор группы, тебе будет прислано уведомление.";
$lang['Group_request'] = "Была подана просьба о вступлении в группу.";
$lang['Group_approved'] = "Твоя просьба была удовлетворена.";
$lang['Group_added'] = "Ты был(а) включен(а) в группу";
$lang['Already_member_group'] = "Ты уже являешься членом этой группы";
$lang['User_is_member_group'] = "Пользователь уже является членом этой группы";
$lang['Group_type_updated'] = "Тип группы успешно изменен";

$lang['Could_not_add_user'] = "Выбранного пользователя не существует";
$lang['Could_not_anon_user'] = "Ты не можешь сделать анонимного пользователя членом группы";

$lang['Confirm_unsub'] = "Ты уверен(а), что хочешь выйти из этой группы?";
$lang['Confirm_unsub_pending'] = "Ты уверен(а), что хочешь отказаться от участия в этой группе? Твоя просьба о вступлении не была ни удовлетворена, ни отклонена!";

$lang['Unsub_success'] = "Ты успешно покинул(а) эту группу.";

$lang['Approve_selected'] = "Одобрить выделенное";
$lang['Deny_selected'] = "Отклонить выделенное";
$lang['Not_logged_in'] = "Ты должен(а) войти в систему, прежде чем вступать в группу.";
$lang['Remove_selected'] = "Удалить выделенное";
$lang['Add_member'] = "Добавить члена группы";
$lang['Not_group_moderator'] = "Ты не являешься модератором группы и не можешь выполнить данное действие";

$lang['Login_to_join'] = "Войди в систему, чтобы менять свое членство в группах";
$lang['This_open_group'] = "Это группа с открытым членством, ты можешь подать просьбу о вступлении";
$lang['This_closed_group'] = "Это группа с закрытым членством, новые пользователи не принимаются";
$lang['This_hidden_group'] = "Это скрытая группа, автоматическое добавление пользователей не разрешается";
$lang['Member_this_group'] = "Ты член этой группы";
$lang['Pending_this_group'] = "Ты кандидат в члены этой группы";
$lang['Are_group_moderator'] = "Ты модератор этой группы";
$lang['None'] = "Нет";

$lang['Subscribe'] = "Подписаться";
$lang['Unsubscribe'] = "Отписаться";
$lang['View_Information'] = "Просмотреть информацию";


//
// Search
//
$lang['Search_query'] = "Запрос";
$lang['Search_options'] = "Параметры запроса";

$lang['Search_keywords'] = "Ключевые слова";
$lang['Search_keywords_explain'] = "Ты можешь использовать <u>AND</u> чтобы определить слова, которые должны быть в результатах, <u>OR</u> для слов, которые могут быть в результатах, и <u>NOT</u> для слов, которых в результатах быть не должно. Используй * в качестве шаблона для частичного совпадения.";
$lang['Search_author'] = "Поиск по автору";
$lang['Search_author_explain'] = "Используй * в качестве шаблона";

$lang['Search_for_any'] = "Искать любое слово/поиск с языком запросов";
$lang['Search_for_all'] = "Искать все слова";
$lang['Search_title_msg'] = "Искать в названиях тем и текстах сообщений";
$lang['Search_msg_only'] = "Искать только в текстах сообщений";

$lang['Return_first'] = "Показывать первые"; // followed by xxx characters
$lang['characters_posts'] = "символов сообщений";

$lang['Search_previous'] = "Время размещения"; // followed by days, weeks, months, year, all

$lang['Sort_by'] = "Упорядочить по";
$lang['Sort_Time'] = "времени размещения";
$lang['Sort_Post_Subject'] = "заголовку сообщения";
$lang['Sort_Topic_Title'] = "теме";
$lang['Sort_Author'] = "автору";
$lang['Sort_Forum'] = "форуму";

$lang['Display_results'] = "Показывать результаты как";
$lang['All_available'] = "Все имеющиеся";
$lang['No_searchable_forums'] = "У тебя нет доступа к поиску ни в одном из форумов на сайте";

$lang['No_search_match'] = "Подходящих тем или сообщений не найдено";
$lang['Found_search_match'] = "Результатов поиска: %d"; // eg. Search found 1 match
$lang['Found_search_matches'] = "Результатов поиска: %d"; // eg. Search found 24 matches

$lang['Close_window'] = "Закрыть окно";


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = "Только %s могут размещать объявления в этом форуме";
$lang['Sorry_auth_sticky'] = "Только %s могут размещать важные темы в этом форуме";
$lang['Sorry_auth_read'] = "Только %s могут читать сообщения в этом форуме";
$lang['Sorry_auth_post'] = "Только %s могут начинать темы в этом форуме";
$lang['Sorry_auth_reply'] = "Только %s могут отвечать на сообщения в этом форуме";
$lang['Sorry_auth_edit'] = "Только %s могут редактировать сообщения в этом форуме";
$lang['Sorry_auth_delete'] = "Только %s могут удалять сообщения в этом форуме";
$lang['Sorry_auth_vote'] = "Только %s могут голосовать в опросах этого форума";

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = "<b>гости</b>";
$lang['Auth_Registered_Users'] = "<b>зарегистрированные пользователи</b>";
$lang['Auth_Users_granted_access'] = "<b>пользователи со специальными правами доступа</b>";
$lang['Auth_Moderators'] = "<b>модераторы</b>";
$lang['Auth_Administrators'] = "<b>администраторы</b>";

$lang['Not_Moderator'] = "Ты не являешься модератором этого форума";
$lang['Not_Authorised'] = "Нет доступа";

$lang['You_been_banned'] = "Тебе был закрыт доступ к форуму<br />Обратись к вебмастеру или администратору форумов за дополнительной информацией";


//
// Viewonline
//
$lang['Reg_users_zero_online'] = "Сейчас на форуме зарегистрированных пользователей: 0 и "; // There ae 5 Registered and
$lang['Reg_users_online'] = "Сейчас на форуме зарегистрированных пользователей: %d и "; // There ae 5 Registered and
$lang['Reg_user_online'] = "Сейчас на форуме зарегистрированных пользователей: %d и ";
$lang['Hidden_users_zero_online'] = "скрытых пользователей: 0"; // 6 Hidden users online
$lang['Hidden_users_online'] = "скрытых пользователей: %d";
$lang['Hidden_user_online'] = "скрытых пользователей: %d"; // 6 Hidden users online
$lang['Guest_users_online'] = "Сейчас на форуме гостей: %d";
$lang['Guest_users_zero_online'] = "Сейчас на форуме гостей: 0"; // There are 10 Guest users online
$lang['Guest_user_online'] = "Сейчас на форуме гостей: %d";
$lang['No_users_browsing'] = "Этот форум сейчас никто не просматривает";

$lang['Online_explain'] = "Эти данные основаны на активности пользователей за последние пять минут";

$lang['Forum_Location'] = "Место на форуме";
$lang['Last_updated'] = "Последнее изменение";

$lang['Forum_index'] = "Список форумов";
$lang['Logging_on'] = "Вход в систему";
$lang['Posting_message'] = "Размещение сообщения";
$lang['Searching_forums'] = "Поиск по форуму";
$lang['Viewing_profile'] = "Просмотр профиля";
$lang['Viewing_online'] = "Просмотр «Кто сейчас на форуме»";
$lang['Viewing_member_list'] = "Просмотр списка пользователей";
$lang['Viewing_priv_msgs'] = "Просмотр личных сообщений";
$lang['Viewing_FAQ'] = "Просмотр FAQ";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "Пульт модерации";
$lang['Mod_CP_explain'] = "Здесь ты можешь проводить массовую модерацию этого форума. Ты можешь закрывать, открывать, перемещать или удалять любое количество тем.";

$lang['Select'] = "Выбрать";
$lang['Delete'] = "Удалить";
$lang['Move'] = "Переместить";
$lang['Lock'] = "Закрыть";
$lang['Unlock'] = "Открыть";

$lang['Topics_Removed'] = "Выбранные темы были успешно удалены из базы данных";
$lang['Topics_Locked'] = "Выбранные темы были закрыты";
$lang['Topics_Moved'] = "Выбранные темы были перемещены";
$lang['Topics_Unlocked'] = "Выбранные темы были открыты";
$lang['No_Topics_Moved'] = "Не было перенесено ни одной темы";

$lang['Confirm_delete_topic'] = "Ты действительно хочешь удалить выбранные темы?";
$lang['Confirm_lock_topic'] = "Ты действительно хочешь закрыть выбранные темы?";
$lang['Confirm_unlock_topic'] = "Ты действительно хочешь открыть выбранные темы?";
$lang['Confirm_move_topic'] = "Ты действительно хочешь переместить выбранные темы?";

$lang['Move_to_forum'] = "Переместить в форум";
$lang['Leave_shadow_topic'] = "Оставить ссылку в старом форуме";

$lang['Split_Topic'] = "Разделение темы";
$lang['Split_Topic_explain'] = "С использованием этой анкеты ты можешь разделить тему на две либо выбирая сообщения по одному, либо разбив по выбранному сообщению";
$lang['Split_title'] = "Заголовок новой темы";
$lang['Split_forum'] = "Форум для новой темы";
$lang['Split_posts'] = "Отделить выбранные сообщения";
$lang['Split_after'] = "Отделить все сообщения после выбранного";
$lang['Topic_split'] = "Выбранная тема была успешно отделена";

$lang['Too_many_error'] = "Ты выбрал(а) слишком много сообщений. Ты можешь выбрать только одно сообщение, чтобы отделить все сообщения после него.";

$lang['None_selected'] = "Ты не выбрал(а) ни одной темы для совершения этой операции. Вернись назад и выбери.";
$lang['New_forum'] = "Новый форум";

$lang['This_posts_IP'] = "IP адрес для этого сообщения";
$lang['Other_IP_this_user'] = "Другие IP адреса с которых писал этот пользователь";
$lang['Users_this_IP'] = "Пользователи, писавшие с этого IP";
$lang['IP_info'] = "Информация об IP адресе";
$lang['Lookup_IP'] = "Посмотреть хозяина IP";


//
// Timezones ... for display on each page
//
$lang['All_times'] = "Часовой пояс: %s"; // This is followed by GMT and the timezone offset

$lang['-12'] = "GMT -12:00";
$lang['-11'] = "GMT -11:00";
$lang['-10'] = "HST (Hawaii)";
$lang['-9'] = "GMT -9:00";
$lang['-8'] = "PST (U.S./Canada)";
$lang['-7'] = "MST (U.S./Canada)";
$lang['-6'] = "CST (U.S./Canada)";
$lang['-5'] = "EST (U.S./Canada)";
$lang['-4'] = "GMT -4:00";
$lang['-3.5'] = "GMT -3:30";
$lang['-3'] = "GMT -3:00";
$lang['-2'] = "Mid-Atlantic";
$lang['-1'] = "GMT -1:00";
$lang['0'] = "GMT";
$lang['1'] = "CET (Europe)";
$lang['2'] = "EET (Europe)";
$lang['3'] = "GMT +3:00";
$lang['3.5'] = "GMT +3:30";
$lang['4'] = "GMT +4:00";
$lang['4.5'] = "GMT +4:30";
$lang['5'] = "GMT +5:00";
$lang['5.5'] = "GMT +5:30";
$lang['6'] = "GMT +6:00";
$lang['7'] = "GMT +7:00";
$lang['8'] = "WST (Australia)";
$lang['9'] = "GMT +9:00";
$lang['9.5'] = "CST (Australia)";
$lang['10'] = "EST (Australia)";
$lang['11'] = "GMT +11:00";
$lang['12'] = "GMT +12:00";

// Список русифицирован: где нужно - первыми идут Российские/бСССР города
$lang['tz']['-12'] = "(GMT -12:00) Эневеток, Кваджалейн";
$lang['tz']['-11'] = "(GMT -11:00) о-в Мидуэй, Самоа";
$lang['tz']['-10'] = "(GMT -10:00) Гавайи";
$lang['tz']['-9'] = "(GMT -9:00) Аляска";
$lang['tz']['-8'] = "(GMT -8:00) Pacific Time (США и Канада)";
$lang['tz']['-7'] = "(GMT -7:00) Mountain Time (США и Канада)";
$lang['tz']['-6'] = "(GMT -6:00) Central Time (США и Канада), Мехико";
$lang['tz']['-5'] = "(GMT -5:00) Eastern Time (США и Канада), Богота, Лима, Кито";
$lang['tz']['-4'] = "(GMT -4:00) Atlantic Time (Канада), Каракас, Ла Пас";
$lang['tz']['-3.5'] = "(GMT -3:30) Ньюфаундленд";
$lang['tz']['-3'] = "(GMT -3:00) Бразилиа, Буэнос-Айрес, Джорджтаун, Фолклендские о-ва";
$lang['tz']['-2'] = "(GMT -2:00) Среднеатлантическое время";
$lang['tz']['-1'] = "(GMT -1:00) Азорские о-ва, о-ва Зеленого Мыса";
$lang['tz']['0'] = "(GMT) Время по Гринвичу: Лондон, Дублин, Эдинбург, Лиссабон, Монровия";
$lang['tz']['1'] = "(GMT +1:00) Берлин, Брюссель, Копенгаген, Мадрид, Париж, Рим";
$lang['tz']['2'] = "(GMT +2:00) Калининград, Киев, Минск, Рига, Таллин";
$lang['tz']['3'] = "(GMT +3:00) Москва, Санкт-Петербург, Волгоград, Багдад, Эр-Рияд, Найроби";
$lang['tz']['3.5'] = "(GMT +3:30) Тегеран";
$lang['tz']['4'] = "(GMT +4:00) Баку, Ереван, Тбилиси, Абу Даби, Мускат";
$lang['tz']['4.5'] = "(GMT +4:30) Кабул";
$lang['tz']['5'] = "(GMT +5:00) Екатеринбург, Ташкент, Исламабад, Карачи";
$lang['tz']['5.5'] = "(GMT +5:30) Бомбей, Калькутта, Мадрас, Нью-Дели";
$lang['tz']['6'] = "(GMT +6:00) Новосибирск, Омск, Алма-Ата, Коломбо, Дакка";
$lang['tz']['7'] = "(GMT +7:00) Красноярск, Бангкок, Ханой, Джакарта";
$lang['tz']['8'] = "(GMT +8:00) Иркутск, Пекин, Гонконг, Перт, Сингапур, Тайбэй";
$lang['tz']['9'] = "(GMT +9:00) Якутск, Осака, Саппоро, Сеул, Токио";
$lang['tz']['9.5'] = "(GMT +9:30) Аделаида, Дарвин";
$lang['tz']['10'] = "(GMT +10:00) Владивосток, Мельбурн, Папуа Новая Гвинея, Сидней";
$lang['tz']['11'] = "(GMT +11:00) Магадан, Сахалин, Новая Каледония, Соломоновы о-ва";
$lang['tz']['12'] = "(GMT +12:00) Камчатка, Окленд, Веллингтон, Фиджи, Маршалловы о-ва";

$lang['datetime']['Sunday'] = "Воскресенье";
$lang['datetime']['Monday'] = "Понедельник";
$lang['datetime']['Tuesday'] = "Вторник";
$lang['datetime']['Wednesday'] = "Среда";
$lang['datetime']['Thursday'] = "Четверг";
$lang['datetime']['Friday'] = "Пятница";
$lang['datetime']['Saturday'] = "Суббота";
$lang['datetime']['Sun'] = "Вс";
$lang['datetime']['Mon'] = "Пн";
$lang['datetime']['Tue'] = "Вт";
$lang['datetime']['Wed'] = "Ср";
$lang['datetime']['Thu'] = "Чт";
$lang['datetime']['Fri'] = "Пт";
$lang['datetime']['Sat'] = "Сб";
$lang['datetime']['January'] = "Января";
$lang['datetime']['February'] = "Февраля";
$lang['datetime']['March'] = "Марта";
$lang['datetime']['April'] = "Апреля";
$lang['datetime']['May'] = "Мая";
$lang['datetime']['June'] = "Июня";
$lang['datetime']['July'] = "Июля";
$lang['datetime']['August'] = "Августа";
$lang['datetime']['September'] = "Сентября";
$lang['datetime']['October'] = "Октября";
$lang['datetime']['November'] = "Ноября";
$lang['datetime']['December'] = "Декабря";
$lang['datetime']['Jan'] = "Янв";
$lang['datetime']['Feb'] = "Фев";
$lang['datetime']['Mar'] = "Мар";
$lang['datetime']['Apr'] = "Апр";
$lang['datetime']['May'] = "Мая";
$lang['datetime']['Jun'] = "Июн";
$lang['datetime']['Jul'] = "Июл";
$lang['datetime']['Aug'] = "Авг";
$lang['datetime']['Sep'] = "Сен";
$lang['datetime']['Oct'] = "Окт";
$lang['datetime']['Nov'] = "Ноя";
$lang['datetime']['Desc'] = "Дек";

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = "Информация";
$lang['Critical_Information'] = "Критическая информация";

$lang['General_Error'] = "Общая ошибка";
$lang['Critical_Error'] = "Критическая ошибка";
$lang['An_error_occured'] = "Произошла ошибка";
$lang['A_critical_error'] = "Произошла критическая ошибка";

//
// That's all Folks!
// -------------------------------------------------

?>