<?php
/***************************************************************************
 *                            lang_main.php [Turkish]
 *                              -------------------
 *     begin                : Wed Jan 9 2002
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
// Translation by:
//
// Onur Turgay (postexitus)  :: onurturgay@isnet.net.tr :: http://welcome.to/aaal2000
// Erdem Corapcioglu (erdem) :: erdem@smtg.org         :: http://www.smtg.org
//
// For questions and comments use: onurturgay@isnet.net.tr
//


//setlocale(LC_ALL, "tr");
$lang['ENCODING'] = "iso-8859-9";
$lang['DIRECTION'] = "ltr";
$lang['LEFT'] = "left";
$lang['RIGHT'] = "right";
$lang['DATE_FORMAT'] =  "d M Y"; // This should be changed to the default date format for your language, php date() format

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = "Forum";
$lang['Category'] = "Kategori";
$lang['Topic'] = "Baþlýk";
$lang['Topics'] = "Baþlýklar";
$lang['Replies'] = "Cevaplar";
$lang['Views'] = "Görüntüleme";
$lang['Post'] = "Mesaj";
$lang['Posts'] = "Mesajlar";
$lang['Posted'] = "Gönderilen";
$lang['Username'] = "Kullanýcý Adý";
$lang['Password'] = "Þifre";
$lang['Email'] = "E-mail";
$lang['Poster'] = "Gönderen";
$lang['Author'] = "Yazar";
$lang['Time'] = "Saat";
$lang['Hours'] = "Saat";
$lang['Message'] = "Mesaj";

$lang['1_Day'] = "1 Günlük";
$lang['7_Days'] = "7 Günlük";
$lang['2_Weeks'] = "2 Haftalýk";
$lang['1_Month'] = "1 Aylýk";
$lang['3_Months'] = "3 Aylýk";
$lang['6_Months'] = "6 Aylýk";
$lang['1_Year'] = "1 Yýllýk";

$lang['Go'] = "OK";
$lang['Jump_to'] = "Forum Seçin";
$lang['Submit'] = "Gönder";
$lang['Reset'] = "Sil";
$lang['Cancel'] = "Ýptal";
$lang['Preview'] = "Önizleme";
$lang['Confirm'] = "Onayla";
$lang['Spellcheck'] = "Spellcheck";
$lang['Yes'] = "Evet";
$lang['No'] = "Hayýr";
$lang['Enabled'] = "Açýk";
$lang['Disabled'] = "Kapalý";
$lang['Error'] = "Hata";

$lang['Next'] = "Sonraki";
$lang['Previous'] = "Önceki";
$lang['Goto_page'] = "Sayfa";
$lang['Joined'] = "Kayýt";
$lang['IP_Address'] = "IP Adresi";

$lang['Select_forum'] = "Bir Forum Seçin";
$lang['View_latest_post'] = "Son Mesajlarý Gör";
$lang['View_newest_post'] = "Yeni Mesajlarý Gör";
$lang['Page_of'] =  "<b>%d</b>. sayfa  (Toplam <b>%d</b> sayfa)"; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = "ICQ Numarasý";
$lang['AIM'] = "AIM Adresi";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Forum_Index'] = "%s Forum Ana Sayfasý";  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = "Yeni Baþlýk Gönder";
$lang['Reply_to_topic'] = "Cevap Gönder";
$lang['Reply_with_quote'] = "Alýntýyla Cevap Ver";

$lang['Click_return_topic'] = "Mesajlara dönmek için %sburaya%s týklayýn"; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = "Bir daha denemek için %sburaya%s týklayýn";
$lang['Click_return_forum'] = "Foruma dönmek için %sburaya%s týklayýn";
$lang['Click_view_message'] = "Mesajýnýzý görmek için %sburaya%s týklayýn";
$lang['Click_return_modcp'] = "Moderator Kontrol Paneline dönmek için %sburaya%s týklayýn";
$lang['Click_return_group'] = "Grup bilgilerine dönmek için %sburaya%s týklayýn";

$lang['Admin_panel'] = "Yönetim Paneli";

$lang['Board_disable'] = "Üzgünüz, forumumuz þimdilik kapalýdýr. Lütfen daha sonra tekrar deneyiniz.";


//
// Global Header strings
//
$lang['Registered_users'] = "Kayýtlý Kullanýcýlar:";
$lang['Browsing_forum'] = "Bu forumu gezen kullanýcýlar:";
$lang['Online_users_zero_total'] = "Toplam <b>0</b> kullanýcý online :: ";
$lang['Online_users_total'] = "Toplam <b>%d</b> kullanýcý online :: ";
$lang['Online_user_total'] = $lang['Online_users_total'];
$lang['Reg_users_zero_total'] = "0 Kayýtlý, ";
$lang['Reg_users_total'] = "%d Kayýtlý, ";
$lang['Reg_user_total'] = "%d Kayýtlý, ";
$lang['Hidden_users_zero_total'] = "0 Gizli ve ";
$lang['Hidden_user_total'] = "%d Gizli ve ";
$lang['Hidden_user_total'] = "%d Gizli ve ";
$lang['Guest_users_zero_total'] = "0 Misafir";
$lang['Guest_users_total'] = "%d Misafir";
$lang['Guest_user_total'] = "%d Misafir";
$lang['Record_online_users'] = "Sitede bugüne kadar en çok <b>%s</b> kiþi %s tarihinde online oldu."; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = "%sAdmin%s";
$lang['Mod_online_color'] = "%sModerator%s";

$lang['You_last_visit'] = "Son ziyaretiniz: %s"; // %s replaced by date/time
$lang['Current_time'] = "Forum Saati: %s"; // %s replaced by time

$lang['Search_new'] = "Son geliþinizden bu yana gönderilen mesajlar";
$lang['Search_your_posts'] = "Kendi mesajlarýnýz";
$lang['Search_unanswered'] = "Cevaplanmamýþ mesajlar";

$lang['Register'] = "Kayýt Ol";
$lang['Profile'] = "Profil";
$lang['Edit_profile'] = "Profilinizi Deðiþtirin";
$lang['Search'] = "Arama";
$lang['Memberlist'] = "Üye Listesi";
$lang['FAQ'] = "SSS";
$lang['BBCode_guide'] = "BBCode Kullanma Kýlavuzu";
$lang['Usergroups'] = "Kullanýcý Gruplarý";
$lang['Last_Post'] = "Son Gönderilen";
$lang['Moderator'] = "Moderator";
$lang['Moderators'] = "Moderator";


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = "Kullanýcýlarýmýz toplam <b>0</b> mesaj attýlar"; // Number of posts
$lang['Posted_articles_total'] = "Kullanýcýlarýmýz toplam <b>%d</b> mesaj attýlar"; // Number of posts
$lang['Posted_article_total'] = "Kullanýcýlarýmýz toplam <b>%d</b> mesaj attýlar"; // Number of posts
$lang['Registered_users_zero_total'] = "Toplam <b>0</b> kayýtlý kullanýcýmýz var"; // # registered users
$lang['Registered_users_total'] = "Toplam <b>%d</b> kayýtlý kullanýcýmýz var"; // # registered users
$lang['Registered_user_total'] = "Toplam <b>%d</b> kayýtlý kullanýcýmýz var"; // # registered users
$lang['Newest_user'] = "Son kaydolan kullanýcýmýz: <b>%s%s%s</b>"; // a href, username, /a

$lang['No_new_posts_last_visit'] = "Son ziyaretinizden bu yana hiç yeni mesaj yok";
$lang['No_new_posts'] = "Yeni mesaj yok";
$lang['New_posts'] = "Yeni mesaj var";
$lang['New_post'] = "Yeni mesaj var";
$lang['No_new_posts_hot'] = "Yeni mesaj yok [ Popüler ]";
$lang['New_posts_hot'] = "Yeni mesaj var [ Popüler ]";
$lang['No_new_posts_locked'] = "Yeni mesaj yok [ Kilitli ]";
$lang['New_posts_locked'] = "Yeni mesaj var [ Kilitli ]";
$lang['Forum_is_locked'] = "Forum kilitlenmiþtir";


//
// Login
//
$lang['Enter_password'] = "Lütfen Kullanýcý Ýsminizi ve Þifrenizi Giriniz";
$lang['Login'] = "Login";
$lang['Logout'] = "Logout";

$lang['Forgotten_password'] = "Þifremi Unuttum";

$lang['Log_me_in'] = "Otomatik login";

$lang['Error_login'] = "Yanlýþ ya da aktive edilmemiþ bir kullanýcý adý veya yanlýþ bir þifre girdiniz";


//
// Index page
//
$lang['Index'] = "Ýndeks";
$lang['No_Posts'] = "Mesaj Yok";
$lang['No_forums'] = "Bu sitenin henüz hiç forumu yok";

$lang['Private_Message'] = "Özel Mesaj";
$lang['Private_Messages'] = "Özel Mesajlar";
$lang['Who_is_Online'] = "Kimler Online";

$lang['Mark_all_forums'] = "Tüm forumlarý okunmuþ say";
$lang['Forums_marked_read'] = "Tüm forumlar okunmuþ sayýldý";


//
// Viewforum
//
$lang['View_forum'] = "Forumu görüntüle";

$lang['Forum_not_exist'] = "Seçtiðiniz forum bu sitede bulunmamaktadýr";
$lang['Reached_on_error'] = "Bu sayfaya bir hata sonucu geldiniz";

$lang['Display_topics'] = "Mesajlarý göster";
$lang['All_Topics'] = "Hepsi";

$lang['Topic_Announcement'] = "<b>Duyuru:</b>";
$lang['Topic_Sticky'] = "<b>Sabit:</b>";
$lang['Topic_Moved'] = "<b>Taþýndý:</b>";
$lang['Topic_Poll'] = "<b>[ Anket ]</b>";

$lang['Mark_all_topics'] = "Tüm mesajlarý okunmuþ say";
$lang['Topics_marked_read'] = "Bu forumdaki tüm mesajlar okunmuþ sayýldý";

$lang['Rules_post_can'] = "Bu forumda yeni konular <b>açabilirsiniz</b>";
$lang['Rules_post_cannot'] = "Bu forumda yeni konular <b>açamazsýnýz</b>";
$lang['Rules_reply_can'] = "Bu forumdaki mesajlara cevap <b>verebilirsiniz</b>";
$lang['Rules_reply_cannot'] = "Bu forumdaki mesajlara cevap <b>veremezsiniz</b>";
$lang['Rules_edit_can'] = "Bu forumdaki mesajlarýnýzý edit <b>edebilirsiniz</b>";
$lang['Rules_edit_cannot'] = "Bu forumdaki mesajlarýnýzý edit <b>edemezsiniz</b>";
$lang['Rules_delete_can'] = "Bu forumdaki mesajlarýnýzý <b>silebilirsiniz</b>";
$lang['Rules_delete_cannot'] = "Bu forumdaki mesajlarýnýzý <b>silemezsiniz</b>";
$lang['Rules_vote_can'] = "Bu forumdaki anketlerde oy <b>kullanabilirsiniz</b>";
$lang['Rules_vote_cannot'] = "Bu forumdaki anketlerde oy <b>kullanamazsýnýz</b>";
$lang['Rules_moderate'] = "Bu forumu %smodere%s <b>edebilirsiniz</b>"; // %s replaced by a href links, do not remove!

$lang['No_topics_post_one'] = "Bu forumda hiç mesaj yok<br />Yeni bir tane göndermek için<b>Yeni Baþlýk Yolla</b> linkine týklayýn";


//
// Viewtopic
//
$lang['View_topic'] = "Baþlýðý Görüntüle";

$lang['Guest'] = 'Misafir';
$lang['Post_subject'] = "Mesaj konusu";
$lang['View_next_topic'] = "Sonraki baþlýk";
$lang['View_previous_topic'] = "Önceki baþlýk";
$lang['Submit_vote'] = "Oy Ver";
$lang['View_results'] = "Sonuçlarý Gör";

$lang['No_newer_topics'] = "Bu forumda daha yeni baþlýk yok";
$lang['No_older_topics'] = "Bu forumda daha eski baþlýk yok";
$lang['Topic_post_not_exist'] = "Seçtiðiniz baþlýk bu forumda yok";
$lang['No_posts_topic'] = "Bu baþlýða hiç cevap gelmemiþ";

$lang['Display_posts'] = "Mesajlarý göster";
$lang['All_Posts'] = "Hepsi";
$lang['Newest_First'] = "yeniden-eskiye";
$lang['Oldest_First'] = "eskiden-yeniye";

$lang['Back_to_top'] = "Baþa dön";

$lang['Read_profile'] = "Kullanýcý profilini gör";
$lang['Send_email'] = "Kullanýcýya e-mail gönder";
$lang['Visit_website'] = "Kullanýcýnýn web sitesini ziyaret et";
$lang['ICQ_status'] = "ICQ Status";
$lang['Edit_delete_post'] = "Mesajý editle/sil";
$lang['View_IP'] = "Bu mesajý gönderenin IP adresine bak";
$lang['Delete_post'] = "Bu mesajý sil";

$lang['wrote'] = "demiþ ki"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "Alýntý"; // comes before bbcode quote output.
$lang['Code'] = "Kod"; // comes before bbcode code output.

$lang['Edited_time_total'] = "En son %s tarafýndan %s tarihinde editlendi, toplamda %d kere editlendi"; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = "En son %s tarafýndan %s tarihinde editlendi, toplamda %d kere editlendi"; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = "Bu baþlýðý kilitle";
$lang['Unlock_topic'] = "Bu baþlýðýn kilidini aç";
$lang['Move_topic'] = "Bu baþlýðý taþý";
$lang['Delete_topic'] = "Bu baþlýðý sil";
$lang['Split_topic'] = "Bu baþlýðý böl";

$lang['Stop_watching_topic'] = "Bu baþlýðý takip etmeyi býrak";
$lang['Start_watching_topic'] = "Bu baþlýðý cevaplar için takip et";
$lang['No_longer_watching'] = "Artýk bu baþlýðý takip etmiyorsunuz";
$lang['You_are_watching'] = "Þu anda bu baþlýðý cevaplar için takip ediyorsunuz";

$lang['Total_votes'] = "Toplam Oylar";

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "Mesaj Gövdesi";
$lang['Topic_review'] = "Orjinal Mesaj";

$lang['No_post_mode'] = "Hiçbir post metodu seçilmedi"; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = "Yeni bir baþlýk gönder";
$lang['Post_a_reply'] = "Cevap Gönder";
$lang['Post_topic_as'] = "Mesaj Türü";
$lang['Edit_Post'] = "Mesajý editle";
$lang['Options'] = "Seçenekler";

$lang['Post_Announcement'] = "Duyuru";
$lang['Post_Sticky'] = "Sabit";
$lang['Post_Normal'] = "Normal";

$lang['Confirm_delete'] = "Bu mesajý silmek istediðinize emin misiniz?";
$lang['Confirm_delete_poll'] = "Bu anketi silmek istediðinize emin misiniz?";

$lang['Flood_Error'] = "Son mesajýnýzdan bu kadar kýsa süre sonra bir yenisini gönderemezsiniz, lütfen kýsa bir süre sonra tekrar deneyiniz";
$lang['Empty_subject'] = "Yeni bir baþlýk açarken konu belirtmek zorundasýnýz";
$lang['Empty_message'] = "Boþ bir mesaj gönderemezsiniz";
$lang['Forum_locked'] = "Bu forum kilitlenmiþtir, yeni bir baþlýk açamazsýnýz, cevap gönderebilir ya da mesajlarý deðiþtirebilirsiniz";
$lang['Topic_locked'] = "Bu baþlýk kilitlenmiþtir, cevap yazamaz ya da mesajlarý deðiþtiremezsiniz";
$lang['No_post_id'] = "Deðiþtirmek için bir mesaj seçmelisiniz";
$lang['No_topic_id'] = "Cevap vermek için bir mesaj seçmelisiniz";
$lang['No_valid_mode'] = "Sadece mesaj gonderebilir, deðiþtirebilir, cevap verebilir, alýntý yapabilirsiniz; lütfen geri dönüp tekrar deneyin";
$lang['No_such_post'] = "Böyle bir mesaj yok, lütfen geri dönüp tekrar deneyin";
$lang['Edit_own_posts'] = "Üzgünüz, sadece kendi mesajlarýnýzý deðiþtirebilirsiniz";
$lang['Delete_own_posts'] = "Üzgünüz, sadece kendi mesajlarýnýzý silebilirsiniz";
$lang['Cannot_delete_replied'] = "Üzgünüz, cevap verilmiþ olan mesajlarýnýzý silemezsiniz";
$lang['Cannot_delete_poll'] = "Üzgünüz, aktif bir anketi silemezsiniz";
$lang['Empty_poll_title'] = "Anketiniz için bir baþlýk girmelisiniz";
$lang['To_few_poll_options'] = "Anket için en az iki þýk girmelisiniz";
$lang['To_many_poll_options'] = "Anket için çok fazla þýk girdiniz";
$lang['Post_has_no_poll'] = "Bu mesajda anket yoktur";

$lang['Add_poll'] = "Anket Ekle";
$lang['Add_poll_explain'] = "Eðer mesajýnýza bir anket eklemek istemiyorsanýz, aþaðýdaki bölümleri boþ býrakýn";
$lang['Poll_question'] = "Anket sorusu";
$lang['Poll_option'] = "Anket þýkký";
$lang['Add_option'] = "Bu þýkký ekle";
$lang['Update'] = "Güncelle";
$lang['Delete'] = "Sil";
$lang['Poll_for'] = "Gösterim süresi";
$lang['Days'] = "Gün"; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = "[ Sýnýrlama koymamak için 0 yazýn ya da boþ býrakýn ]";
$lang['Delete_poll'] = "Anketi sil";

$lang['Disable_HTML_post'] = "Bu mesajda HTML kullanma";
$lang['Disable_BBCode_post'] = "Bu mesajda BBCode kullanma";
$lang['Disable_Smilies_post'] = "Bu mesajda Smilileri kullanme";

$lang['HTML_is_ON'] = "HTML <u>Açýk</u>";
$lang['HTML_is_OFF'] = "HTML <u>Kapalý</u>";
$lang['BBCode_is_ON'] = "%sBBCode%s <u>Açýk</u>"; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = "%sBBCode%s <u>Kapalý</u>";
$lang['Smilies_are_ON'] = "Smililer <u>Açýk</u>";
$lang['Smilies_are_OFF'] = "Smililer <u>Kapalý</u>";

$lang['Attach_signature'] = "Ýmzamý ekle (imzanýz profil bölümünden deðiþtirilebilir)";
$lang['Notify'] = "Cevap geldiðinde bana haber ver";
$lang['Delete_post'] = "Bu mesajý sil";

$lang['Stored'] = "Mesajýnýz baþarýyla gönderilmiþtir";
$lang['Deleted'] = "Mesajýnýz baþarýyla silinmiþtir";
$lang['Poll_delete'] = "Anketiniz baþarýyla silinmiþtir";
$lang['Vote_cast'] = "Oyunuz ankete eklendi";

$lang['Topic_reply_notification'] = "Cevap gözleme";

$lang['bbcode_b_help'] = "Kalýn yazý: [b]metin[/b]  (alt+b)";
$lang['bbcode_i_help'] = "Italic yazý: [i]metin[/i]  (alt+i)";
$lang['bbcode_u_help'] = "Altçizgili yazý: [u]metin[/u]  (alt+u)";
$lang['bbcode_q_help'] = "Alýntý: [quote]metin[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "Kod görüntüleme: [code]kod[/code]  (alt+c)";
$lang['bbcode_l_help'] = "Liste: [list]liste[/list] (alt+l)";
$lang['bbcode_o_help'] = "Sýralý liste: [list=]metin[/list]  (alt+o)";
$lang['bbcode_p_help'] = "Resim koy: [img]http://adres[/img]  (alt+p)";
$lang['bbcode_w_help'] = "URL koy: [url]http://url[/url] ya da [url=http://url]metin[/url]  (alt+w)";
$lang['bbcode_a_help'] = "Açýk tüm BBCode komutlarýný sonlandýrýr.";
$lang['bbcode_s_help'] = "Font rengi: [color=red]metin[/color]  Tiyo: color=#FF0000 diye de kullanýlailbir";
$lang['bbcode_f_help'] = "Font boyutu: [size=x-small]küçük font[/size]";

$lang['Emoticons'] = "Smiley Paneli";
$lang['More_emoticons'] = "Tüm smilileri göster";

$lang['Font_color'] = "Font rengi";
$lang['color_default'] = "Standart";
$lang['color_dark_red'] = "Koyu kýrmýzý";
$lang['color_red'] = "Kýrmýzý";
$lang['color_orange'] = "Turuncu";
$lang['color_brown'] = "Kahverengi";
$lang['color_yellow'] = "Sarý";
$lang['color_green'] = "Yeþil";
$lang['color_olive'] = "Haki";
$lang['color_cyan'] = "Turkuaz";
$lang['color_blue'] = "Mavi";
$lang['color_dark_blue'] = "Koyu mavi";
$lang['color_indigo'] = "Mor";
$lang['color_violet'] = "Eflatun";
$lang['color_white'] = "Beyaz";
$lang['color_black'] = "Siyah";

$lang['Font_size'] = "Font boyutu";
$lang['font_tiny'] = "Ufacýk";
$lang['font_small'] = "Küçük";
$lang['font_normal'] = "Normal";
$lang['font_large'] = "Büyük";
$lang['font_huge'] = "Kocaman";

$lang['Close_Tags'] = "Komutlarý Sonlandýr";
$lang['Styles_tip'] = "Tavsiye: Yazýyý seçerek burdaki stilleri daha rahat uygulayabilirsiniz";


//
// Private Messaging
//
$lang['Private_Messaging'] = "Özel Mesajlar";

$lang['Login_check_pm'] = "Özel mesajlarýnýzý kontrol etmek için login olun";
$lang['New_pms'] = "%d adet yeni mesajýnýz var"; // You have 2 new messages
$lang['New_pm'] = "%d adet yeni mesajýnýz var"; // You have 1 new message
$lang['No_new_pm'] = "Yeni mesajýnýz yok";
$lang['Unread_pms'] = "%d adet okunmamýþ mesajýnýz var";
$lang['Unread_pm'] = "%d adet okunmamýþ mesajýnýz var";
$lang['No_unread_pm'] = "Okunmamýþ mesajýnýz yok";
$lang['You_new_pm'] = "Yeni bir özel mesaj sizi bekliyor";
$lang['You_new_pms'] = "Yeni özel mesajlar sizi bekliyor";
$lang['You_no_new_pm'] = "Bekleyen yeni mesajýnýz yok";

$lang['Inbox'] = "Gelenler";
$lang['Outbox'] = "Gönderilenler";
$lang['Savebox'] = "Saklananlar";
$lang['Sentbox'] = "Ulaþanlar";
$lang['Flag'] = "Durum";
$lang['Subject'] = "Konu";
$lang['From'] = "Kimden";
$lang['To'] = "Kime";
$lang['Date'] = "Tarih";
$lang['Mark'] = "Ýþaret";
$lang['Sent'] = "Gönderildi";
$lang['Saved'] = "Kaydedildi";
$lang['Delete_marked'] = "Seçilenleri Sil";
$lang['Delete_all'] = "Hepsini Sil";
$lang['Save_marked'] = "Seçilenleri Sakla";
$lang['Save_message'] = "Mesajý Sakla";
$lang['Delete_message'] = "Mesajý Sil";

$lang['Display_messages'] = "mesajlarý göster"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "Tüm";

$lang['No_messages_folder'] = "Bu klasörde hiç mesajýnýz yok";

$lang['PM_disabled'] = "Bu sitede Özel Mesajlaþma engellenmiþtir";
$lang['Cannot_send_privmsg'] = "Üzgünüz, administrator sizin özel mesaj atma hakkýnýzý iptal etmiþtir";
$lang['No_to_user'] = "Bu mesajý göndermek için bir kullanýcý adý belirtmelisiniz";
$lang['No_such_user'] = "Üzgünüz, böyle bir kullanýcý bulunmamaktadýr";

$lang['Disable_HTML_pm'] = "Bu mesajda HTML'i kapa";
$lang['Disable_BBCode_pm'] = "Bu mesajda BBCode'u kapa";
$lang['Disable_Smilies_pm'] = "Bu mesajda Smilileri kullanma";

$lang['Message_sent'] = "Mesajýnýz gönderilmiþtir";

$lang['Click_return_inbox'] = "Gelenler Kutusuna dönmek için %sburaya%s týklayýn";
$lang['Click_return_index'] = "Ana Sayfaya gitmek için %sburaya%s týklayýn";

$lang['Send_a_new_message'] = "Yeni bir özel mesaj gönder";
$lang['Send_a_reply'] = "Özel bir mesaja cevap ver";
$lang['Edit_message'] = "Özel mesajý deðiþtir";

$lang['Notification_subject'] = "Yeni bir özel mesaj geldi";

$lang['Find_username'] = "Kullanýcý adý bul";
$lang['Find'] = "Bul";
$lang['No_match'] = "Kullanýcý adý bulunamadý";

$lang['No_post_id'] = "Mesaj ID'si belirtilmemiþ";
$lang['No_such_folder'] = "Böyle bir klasör yok";
$lang['No_folder'] = "Klasör belirtilmemiþ";

$lang['Mark_all'] = "Hepsini iþaretle";
$lang['Unmark_all'] = "Ýþaretleri kaldýr";

$lang['Confirm_delete_pm'] = "Bu mesajý silmek istediðinize emin misiniz?";
$lang['Confirm_delete_pms'] = "Bu mesajlarý silmek istediðinize emin misiniz?";

$lang['Inbox_size'] = "Gelenler Kutunuz %d%% dolu"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "Ulaþanlar Kutunuz %d%% dolu";
$lang['Savebox_size'] = "Saklananlar Kutunuz %d%% dolu";

$lang['Click_view_privmsg'] = "Gelenler Kutunuza gitmek için %sburaya%s týklayýnýz";


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "Profili görüntülenen :: %s"; // %s is username
$lang['About_user'] = "%s hakkýnda"; // %s is username

$lang['Preferences'] = "Seçenekler";
$lang['Items_required'] = "* iþaretli bölümler aksi belirtilmedikçe doldurulmak zorundadýr";
$lang['Registration_info'] = "Kayýt bilgileri";
$lang['Profile_info'] = "Profil";
$lang['Profile_info_warn'] = "Bu bilgiler herkes tarafýndan görülebilecektir";
$lang['Avatar_panel'] = "Avatar kontrol paneli";
$lang['Avatar_gallery'] = "Avatar galerisi";

$lang['Website'] = "Web sitesi";
$lang['Location'] = "Nerden";
$lang['Contact'] = "ile haberleþme";
$lang['Email_address'] = "E-mail adresi";
$lang['Email'] = "E-mail";
$lang['Send_private_message'] = "Özel mesaj gönder";
$lang['Hidden_email'] = "[ Gizli ]";
$lang['Search_user_posts'] = "Bu kullanýcýnýn gönderdiði mesajlarý bul";
$lang['Interests'] = "Ýlgi alanlarý";
$lang['Occupation'] = "Meslek";
$lang['Poster_rank'] = "Kullanýcý derecesi";

$lang['Total_posts'] = "Mesaj Sayýsý";
$lang['User_post_pct_stats'] = "Tüm mesajlarýn %.2f%%"; // 1.25% of total
$lang['User_post_day_stats'] = "Ortalama hergün %.2f mesaj"; // 1.5 posts per day
$lang['Search_user_posts'] = "%s tarafýndan gönderilen tüm mesajlarý bul"; // Find all posts by username

$lang['No_user_id_specified'] = "Üzgünüz, böyle bir kullanýcý yok";
$lang['Wrong_Profile'] = "Kendinizin olmayan bir profili deðiþtiremezsiniz";

$lang['Only_one_avatar'] = "Sadece bir tip avatar seçilebilir";
$lang['File_no_data'] = "Verdiðiniz URL'deki dosya bilgi içermiyor";
$lang['No_connection_URL'] = "Verdiðiniz URL ile baðlantý kurulamadý";
$lang['Incomplete_URL'] = "Verdiðiniz URL tamamlanmamýþ";
$lang['Wrong_remote_avatar_format'] = "URL'sini verdiðiniz avatar, doðru bir formatta deðil";
$lang['No_send_account_inactive'] = "Þu anda þifreniz belirlenemiyor çünkü hesabýnýz aktif deðil. Lütfen forum admini ile görüþünüz";

$lang['Always_smile'] = "Smilileri her zaman kullan";
$lang['Always_html'] = "Her zaman HTML kullan";
$lang['Always_bbcode'] = "Her zaman BBCode kullan";
$lang['Always_add_sig'] = "Her zaman imzamý ekle";
$lang['Always_notify'] = "Her zaman beni cevaplardan haberdar et";
$lang['Always_notify_explain'] = "Sizin gönderdiðiniz biz baþlýða her cevap geldiðinde sizi e-mail ile haberdar eder. Bu her mesaj gönderiþinizde de deðiþtirilebilir.";

$lang['Board_style'] = "Ana tema";
$lang['Board_lang'] = "Dil";
$lang['No_themes'] = "Kayýtlý tema yok";
$lang['Timezone'] = "Zaman dilimi";
$lang['Date_format'] = "Saat formatý";
$lang['Date_format_explain'] = "Kullanýlan yazým tarzý PHP'deki <a href=\"http://www.php.net/date\" target=\"_other\">date()</a> fonksiyonuna eþtir";
$lang['Signature'] = "Ýmza";
$lang['Signature_explain'] = "Bu gönderdiðiniz mesajlara eklenebilecek bir yazý bloðudur. %d karakterlik bir limit vardýr";
$lang['Public_view_email'] = "Herzaman e-mail adresimi göster";

$lang['Current_password'] = "Þimdiki þifreniz";
$lang['New_password'] = "Yeni þifreniz";
$lang['Confirm_password'] = "Yeni þifrenizi tekrar girin";
$lang['Confirm_password_explain'] = "Þifrenizi ya da e-mail adresinizi deðiþtirmek için þifrenizi tekrar girerek onaylamanýz gerekmektedir";
$lang['password_if_changed'] = "Sadece deðiþtirmek istiyorsanýz þifrenizi yazmalýsýnýz";
$lang['password_confirm_if_changed'] = "Sadece þifrenizi deðiþtirdiyseniz yeni þifrenizi onaylamalýsýnýz";

$lang['Avatar'] = "Avatar";
$lang['Avatar_explain'] = "Mesajlarýnýzýn yanýndaki küçük resim. Bir seferde sadece bir resim gösterilebilir, geniþliði %d pixelden, yüksekliði %d pixelden ve boyutu %dkB'tan büyük olamaz.";
$lang['Upload_Avatar_URL'] = "Bir URL'den Avatar gönderin";
$lang['Upload_Avatar_URL_explain'] = "Avatar'ýn olduðu sitenin URL'sini girin, buraya kopyalanacaktýr";
$lang['Pick_local_Avatar'] = "Avatar'ý galeriden seçin";
$lang['Link_remote_Avatar'] = "Baþka bir siteden Avatar seçin";
$lang['Link_remote_Avatar_explain'] = "Ýstediðiniz baþka bir Avatarýn URLsini girin. Bu siteye kopyalanmayacaktýr";
$lang['Avatar_URL'] = "Avatar URL'si";
$lang['Select_from_gallery'] = "Galeriden Avatar seçin";
$lang['View_avatar_gallery'] = "Galeriyi göster";

$lang['Select_avatar'] = "Avatarý Seç ";
$lang['Return_profile'] = "Ýptal";
$lang['Select_category'] = "Kategori seç";

$lang['Delete_Image'] = "Avatarý sil";
$lang['Current_Image'] = "Þu andaki Avatar";

$lang['Notify_on_privmsg'] = "Özel Mesaj gelince beni haberdar et";
$lang['Popup_on_privmsg'] = "Özel Mesaj gelince yeni bir pencere aç";
$lang['Popup_on_privmsg_explain'] = "Bu seçeneði seçerek, yeni bir Özel Mesaj geldiðinde yeni bir pencere ile haberdar edilirsiniz";
$lang['Hide_user'] = "Online olduðumu gizle";

$lang['Profile_updated'] = "Profiliniz güncellendi";
$lang['Profile_updated_inactive'] = "Profiliniz güncellendi, ama bazý önemli bilgileri deðiþtirdiðiniz için hesabýnýz aktif deðil. Yeniden aktif hale getirmek için yapmanýz gerekenleri bulmak için e-mail'inizi kontrol edin, eðer admin onayý gerekiyorsa, adminin onaylamasýný bekleyin";

$lang['Password_mismatch'] = "Girdiðiniz þifreler birbirini tutmuyor";
$lang['Current_password_mismatch'] = "Girdiðiniz þifre þu andaki þifrenizi tutmuyor";
$lang['Password_long'] = "Þifreniz 32 karakterden uzun olamaz";
$lang['Username_taken'] = "Üzgünüz bu kullanýcý ismi daha önce alýnmýþ";
$lang['Username_invalid'] = "Üzgünüz bu kullanýcý ismi \" gibi izin verilmeyen bir karakter içeriyor";
$lang['Username_disallowed'] = "Üzgünüz bu kullanýcý ismine izin verilmiyor";
$lang['Email_taken'] = "Üzgünüz bu e-mail adresi baþka bir kullanýcý tarafýnaan kullanýlýyor";
$lang['Email_banned'] = "Üzgünüz bu e-mail adresi yasaklanmýþ (banlanmýþ)";
$lang['Email_invalid'] = "Üzgünüz bu e-mail adresi doðru deðil";
$lang['Signature_too_long'] = "Ýmzanýz çok uzun";
$lang['Fields_empty'] = "Zorunlu bölümleri doldurmalýsýnýz";
$lang['Avatar_filetype'] = "Avatarýn formatý .jpg, .gif ya da .png olmalýdýr";
$lang['Avatar_filesize'] = "Avatar dosyasý %d kB'tan az olmalýdýr"; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = "Avatarýn geniþliði %d pixelden, yüksekliði %d pixelden küçük olmalýdýr";

$lang['Welcome_subject'] = "%s Forumlarýna Hoþgeldiniz"; // Welcome to my.com forums
$lang['New_account_subject'] = "Yeni kullanýcý hesabý";
$lang['Account_activated_subject'] = "Hesap aktif hale getirilmiþtir";

$lang['Account_added'] = "Kaydolduðunuz için teþekkürler, hesabýnýz yaratýlmýþtýr. Kullanýcý isminiz ve Þifrenizle login olabilirsiniz";
$lang['Account_inactive'] = "Hesabýnýz yaratýlmýþtýr. Aktivasyon kodu e-mail'inize gönderilmiþtir. Daha fazla bilgi için e-mail'inizi kontrol ediniz";
$lang['Account_inactive_admin'] = "Hesabýnýz yaratýlmýþtýr. Ama hesabýnýzýn aktif hale gelmesi için admin onayý gerekmektedir. Onlara bir e-mail gönderilmiþtir ve hesabýnýz aktif hale geldiðinde size haber verilecektir";
$lang['Account_active'] = "Hesabýnýz aktif hale getirilmiþtir. Kaydolduðunuz için teþekkür ederiz";
$lang['Account_active_admin'] = "Hesap aktif hale getirilmiþtir";
$lang['Reactivate'] = "Hesabýnýzý yeniden aktif hale getirmelisiniz!";
$lang['COPPA'] = "Hesabýnýz yaratýlmýþtýr ama onaylanmasý gerekmektedir, detaylar için e-mail'inizi kontrol ediniz.";

$lang['Registration'] = "Kayýt Anlaþmasý";
$lang['Reg_agreement'] = "Bu forumun yöneticileri ve moderatörleri her ne kadar itiraz edilebilecek her türlü materyali mümkün olduðu kadar kýsa sürede siteden kaldýracak da olsa, bütün mesajlarýn incelenmesi mümkün olmamaktadýr. Bu durumda siz buraya gönderilen her mesajýn, onu gönderen kullanýcýnýn görüþlerini yansýttýðýný, moderatörlerin, adminlerin ya da webmasterlarýn (kendilerine ait mesajlar dýþýnda) sorumlu tutulamýyacaðýný peþinen kabul etmiþ bulunuyorsunuz.<br /><br />Aþaðýlayýcý, müstehcen, kaba, iftira niteliðinde, nefret dolu, tehdit edici, sekse yönelik ya da kanunlarla çeliþecek içerikler göndermeyeceðinizi kabul ediyorsunuz. Bunlarý dikkate almamanýz durumunda hemen ve süresizce siteden uzaklaþtýrýlýrsýnýz (ve servis saðlayýcýnýz da haberdar edilir). Her mesajýn IP adresi bunlarý engellemek için kaydedilmektedir. Bu forumun moderatörleri, adminleri ya da webmasterýnýn, kendi iradeleri doðrultusuna herhangi bir baþlýðý silme, taþýma, kilitleme yetkisi olduðunu kabul ediyorsunuz. Bir kullanýcý olarak her girdiðiniz bilginin veritabanýnda saklanacaðýný kabul ediyorsunuz. Her ne kadar bu bilgiler sizin bilginiz dýþýnda 3. þahýslara verilmeyecek olsa da, herhangi bir 'hack' olayý sonucunda bu bilgiler 3. þahýslara daðýlýrsa bundan webmaster, moderatör ya da adminleri sorumlu tutamazsýnýz.<br /><br />Bu forum sistemi, bazý bilgileri bilgisayarýnýzda saklamak için cookie'leri kullanmaktadýr. Girdiðiniz özel bilgilerin hiçbiri bu cookie'lerde bulunmamaktadýr, bunlarýn tek amacý forumda daha rahat bir gezinti yapabilmenizdir. E-mail adresiniz sadece kaydýnýzý onaylamak ve þifrenizi yollamak içindir (Ve unuttuðunuz zaman þifrenizi yeniden yollamak için).<br /><br />Aþaðýdaki kabul ediyorum linkine basmak sureti ile yukarýdaki bütün koþullarýn baðlayýcýlýðýný kabul edersiniz.";

$lang['Agree_under_13'] = "Yukarýdaki koþullarý kabul ediyorum ve 13 yaþýn <b>altýndayým</b>";
$lang['Agree_over_13'] = "Yukarýdaki koþullarý kabul ediyorum ve 13 yaþýn <b>üstündeyim</b>";
$lang['Agree_not'] = "Bu koþullarý kabul etmiyorum";

$lang['Wrong_activation'] = "Girdiðiniz aktivasyon kodu veritabanýndaki ile uyuþmuyor.";
$lang['Send_password'] = "Bana yeni bir þifre gönder";
$lang['Password_updated'] = "Yeni þifreniz yaratýldý, nasýl aktif hale getireceðinizi öðrenmek için e-mail'inizi kontrol ediniz";
$lang['No_email_match'] = "Bu kullanýcý için verdiðiniz e-mail adresi veritabanýndaki ile uyuþmuyor";
$lang['New_password_activation'] = "Yeni þifre aktivasyonu";
$lang['Password_activated'] = "Hesabýnýz yeniden aktif hale getirilmiþtir. Login olmak için e-mail'inize gönderilen þifreyi kullanýn";

$lang['Send_email_msg'] = "E-mail gönder";
$lang['No_user_specified'] = "Kullanýcý ismi seçilmedi";
$lang['User_prevent_email'] = "Bu kullanýcý e-mail almak istemiyor. Özel Mesaj göndermeyi deneyin";
$lang['User_not_exist'] = "Böyle bir kullanýcý yok";
$lang['CC_email'] = "Bu e-mail'in bir kopyasýný kendinize gönderin";
$lang['Email_message_desc'] = "Bu mesaj düz metin içercektir, BBCode ya da HTML kullanýlmayacaktýr. Cevap adresi olarak sizin e-mail adresiniz girilmiþtir";
$lang['Flood_email_limit'] = "Þu anda baþka bir e-mail gönderemezsiniz, lütfen daha sonra tekrar deneyiniz";
$lang['Recipient'] = "Alýcý";
$lang['Email_sent'] = "E-mail gönderilmiþtir";
$lang['Send_email'] = "E-mail'i gönder";
$lang['Empty_subject_email'] = "E-mail için bir konu belirtmelisiniz";
$lang['Empty_message_email'] = "E-mail'le gönderilecek bir mesaj yazmalýsýnýz";


//
// Memberslist
//
$lang['Select_sort_method'] = "Sýralama stilini seçiniz";
$lang['Sort'] = "Sýrala";
$lang['Sort_Top_Ten'] = "TOP 10";
$lang['Sort_Joined'] = "Giriþ tarihi";
$lang['Sort_Username'] = "Kullanýcý ismi";
$lang['Sort_Location'] = "Yer";
$lang['Sort_Posts'] = "Toplam mesaj";
$lang['Sort_Email'] = "Email";
$lang['Sort_Website'] = "Web sitesi";
$lang['Sort_Ascending'] = "Artan";
$lang['Sort_Descending'] = "Azalan";
$lang['Order'] = "Düzen";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "Grup Kontrol Paneli";
$lang['Group_member_details'] = "Grup Üyeliði Detaylarý";
$lang['Group_member_join'] = "Bir Gruba Katýl";

$lang['Group_Information'] = "Grup Bilgileri";
$lang['Group_name'] = "Grup adý";
$lang['Group_description'] = "Grup açýklamasý";
$lang['Group_membership'] = "Grup üyeliði";
$lang['Group_Members'] = "Grup Üyeleri";
$lang['Group_Moderator'] = "Grup Moderatorü";
$lang['Pending_members'] = "Onay bekleyen üyeler";

$lang['Group_type'] = "Grup türü";
$lang['Group_open'] = "Açýk grup";
$lang['Group_closed'] = "Kapalý group";
$lang['Group_hidden'] = "Gizli group";

$lang['Current_memberships'] = "Þu andaki üyelikler";
$lang['Non_member_groups'] = "Üyelik istemeyen gruplar";
$lang['Memberships_pending'] = "Onay bekleyen üyelikler";

$lang['No_groups_exist'] = "Hiç kullanýcý grubu yok";
$lang['Group_not_exist'] = "Böyle bir kullanýcý grubu yok";

$lang['Join_group'] = "Gruba katýl";
$lang['No_group_members'] = "Bu grubun hiç üyesi yok";
$lang['Group_hidden_members'] = "Bu grup gizlenmiþ, üyelikleri göremezsiniz";
$lang['No_pending_group_members'] = "Bu grupta hiç onay bekleyen üyelik yok";
$lang["Group_joined"] = "Bu gruba baþarýyla kaydoldunuz<br />Üyeliðiniz moderator tarafýndan onaylandýðýna haberdar edileceksiniz";
$lang['Group_request'] = "Grubunuza katýlmak için bir baþvuru var";
$lang['Group_approved'] = "Baþvurunuz onaylanmýþtýr";
$lang['Group_added'] = "Bu gruba eklendiniz";
$lang['Already_member_group'] = "Zaten bu grubun üyesisiniz";
$lang['User_is_member_group'] = "Kullanýcý zaten bu grubun üyesi";
$lang['Group_type_updated'] = "Grup türü güncellendi";

$lang['Could_not_add_user'] = "Seçtiðiniz kullanýcý yok";
$lang['Could_not_anon_user'] = "Misafir bir kullanýcýyý üye yapamazsýnýz";

$lang['Confirm_unsub'] = "Bu gruptan üyeliðinizi sildirmek istediðinize emin misiniz?";
$lang['Confirm_unsub_pending'] = "Bu gruba üyeliðiniz henüz onaylanmadý, gene de sildirmek istediðinize emin misiniz?";

$lang['Unsub_success'] = "Bu gruptan kaydýnýz silinmiþtir";

$lang['Approve_selected'] = "Seçilenleri onayla";
$lang['Deny_selected'] = "Seçilenleri reddet";
$lang['Not_logged_in'] = "Bir gruba katýlmak için login olmanýz lazým.";
$lang['Remove_selected'] = "Seçilenleri çýkar";
$lang['Add_member'] = "Üye Ekle";
$lang['Not_group_moderator'] = "Bu grubun moderatoru deðilsiniz, bunu yapamazsýnýz.";

$lang['Login_to_join'] = "Gruba katýlmak ya da grubu yönetmek için login olmalýsýnýz";
$lang['This_open_group'] = "Bu açýk bir grup, üyelik istemek için týklayýn";
$lang['This_closed_group'] = "Bu kapalý bir grup, yeni üyelik alýnmýyor";
$lang['This_hidden_group'] = "Bu gizli bir grup, otomatik üye alýmýna izin verilmiyor";
$lang['Member_this_group'] = "Bu grubun üyesisiniz";
$lang['Pending_this_group'] = "Bu gruba üyeliðiniz onay için beklemede";
$lang['Are_group_moderator'] = "Bu grubun moderatorüsünüz";
$lang['None'] = "Yok";

$lang['Subscribe'] = "Üye ol";
$lang['Unsubscribe'] = "Üyelik sildir";
$lang['View_Information'] = "Grup hakkýnda";


//
// Search
//
$lang['Search_query'] = "Arama";
$lang['Search_options'] = "Arama Seçenekleri";

$lang['Search_keywords'] = "Anahtar Kelimeleri Ara";
$lang['Search_keywords_explain'] = "<u>AND</u> ile sonuçlarda bulunmasý zorunlu kelimeleri, <u>OR</u> ile sonuçlarda olabilecek kelimeleri ve <u>NOT</u> ile sonuçta olmamasý gereken kelimeleri tanýmlayabilirsiniz. * iþareti ile kelimenin bir bölümünü girip gerisinin bulunmasýný saðlayabilirsiniz";
$lang['Search_author'] = "Yazarý Ara";
$lang['Search_author_explain'] = "* ile kelimenin bir kýsmýný girip tutan sonuclarý bulabilirsiniz";

$lang['Search_for_any'] = "Herhangi bir terim için ara ya da girilen önermeyi kullan";
$lang['Search_for_all'] = "Bütün terimler için ara";
$lang['Search_title_msg'] = "Baþlýk ve mesaj metninde ara";
$lang['Search_msg_only'] = "Sadece mesaj metninde ara";

$lang['Return_first'] = "Mesajýn ilk"; // followed by xxx characters in a select box
$lang['characters_posts'] = "karakterini göster";

$lang['Search_previous'] = "Süre"; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = "Sýralama";
$lang['Sort_Time'] = "Zaman";
$lang['Sort_Post_Subject'] = "Konu";
$lang['Sort_Topic_Title'] = "Baþlýk";
$lang['Sort_Author'] = "Yazar";
$lang['Sort_Forum'] = "Forum";

$lang['Display_results'] = "Sonuçlar";
$lang['All_available'] = "Tümü";
$lang['No_searchable_forums'] = "Bu sitedeki hiçbir forumda arama yapma yetkiniz yok";

$lang['No_search_match'] = "Arama kriterlerinize uygun mesaj ya da baþlýk bulunamadý";
$lang['Found_search_match'] = "Arama sonucunda %d adet mesaj bulundu"; // eg. Search found 1 match
$lang['Found_search_matches'] = "Arama sonucunda %d adet mesaj bulundu"; // eg. Search found 24 matches

$lang['Close_window'] = "Pencereyi kapat";


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = "Üzgünüz, sadece %s bu foruma duyu gönderebilir";
$lang['Sorry_auth_sticky'] = "Üzgünüz, sadece %s bu foruma sabit mesaj gönderebilir";
$lang['Sorry_auth_read'] = "Üzgünüz, sadece %s bu forumdaki mesajlarý okuyabilir";
$lang['Sorry_auth_post'] = "Üzgünüz, sadece %s bu foruma baþlýk gönderebilir";
$lang['Sorry_auth_reply'] = "Üzgünüz, sadece %s bu forumdaki mesajlara cevap verebilir";
$lang['Sorry_auth_edit'] = "Üzgünüz, sadece %s bu forumdaki mesajlarý deðiþtirebilir";
$lang['Sorry_auth_delete'] = "Üzgünüz, sadece %s bu forumdaki mesajlarý silebilir";
$lang['Sorry_auth_vote'] = "Üzgünüz, sadece %s bu forumdaki anketlere oy verebilir";

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = "<b>misafirler</b>";
$lang['Auth_Registered_Users'] = "<b>kayýtlý kullanýcýlar</b>";
$lang['Auth_Users_granted_access'] = "<b>özel haklara sahip kullanýcýlar</b>";
$lang['Auth_Moderators'] = "<b>moderatorler</b>";
$lang['Auth_Administrators'] = "<b>administratorler</b>";

$lang['Not_Moderator'] = "Bu forumun moderatoru deðilsiniz";
$lang['Not_Authorised'] = "Ýzniniz yok";

$lang['You_been_banned'] = "Bu forumdan atýldýnýz<br />Ayrýntýlý bilgi için webmaster ya da forum admini ile baðlantýya geçin";


//
// Viewonline
//
$lang['Reg_users_zero_online'] = "0 kayýtlý kullanýcý ve "; // There ae 5 Registered and
$lang['Reg_users_online'] = "%d kayýtlý kullanýcý ve "; // There ae 5 Registered and
$lang['Reg_user_online'] = "%d kayýtlý kullanýcý ve "; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = "0 gizli kullanýcý online"; // 6 Hidden users online
$lang['Hidden_users_online'] = "%d gizli kullanýcý online"; // 6 Hidden users online
$lang['Hidden_user_online'] = "%d gizli kullanýcý online"; // 6 Hidden users online
$lang['Guest_users_online'] = "%d misafir online"; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = "0 misafir online"; // There are 10 Guest users online
$lang['Guest_user_online'] = "%d misafir online"; // There is 1 Guest user online
$lang['No_users_browsing'] = "Þu anda bu forumu dolaþan kullanýcý yok";

$lang['Online_explain'] = "Bu bilgi son 5 dakika içinde aktif olan kullanýcýlara dayanmaktadýr";

$lang['Forum_Location'] = "Forumdaki Yeri";
$lang['Last_updated'] = "Son güncelleme";

$lang['Forum_index'] = "Forum Ana Sayfa";
$lang['Logging_on'] = "Giriþ yapýyor";
$lang['Posting_message'] = "Mesaj gönderiyor";
$lang['Searching_forums'] = "Arama yapýyor";
$lang['Viewing_profile'] = "Profil görüntülüyor";
$lang['Viewing_online'] = "Kimin online olduðuna bakýyor";
$lang['Viewing_member_list'] = "Üye listesine bakýyor";
$lang['Viewing_priv_msgs'] = "Özel mesajlarýna bakýyor";
$lang['Viewing_FAQ'] = "SSS'ý görüntülüyor";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "Moderator Kontrol Paneli";
$lang['Mod_CP_explain'] = "Aþaðýdaki formu kullanarak bu forumda moderasyon yapabilirsiniz. Ýstediðiniz sayýda forumu silebilir, taþýyabilir, kilitleyebilir ya da kilidini açabilirsiniz";

$lang['Select'] = "Seç";
$lang['Delete'] = "Sil";
$lang['Move'] = "Taþý";
$lang['Lock'] = "Kilitle";
$lang['Unlock'] = "Kilidi Aç";

$lang['Topics_Removed'] = "Seçtiðiniz baþlýklar veritabanýndan baþarýyla silinmiþlerdir";
$lang['Topics_Locked'] = "Seçtiðiniz baþlýklar kilitlenmiþlerdir";
$lang['Topics_Moved'] = "Seçtiðiniz baþlýklar taþýnmýþtýr";
$lang['Topics_Unlocked'] = "Seçtiðiniz baþlýklarýn kilidi açýlmýþtýr";
$lang['No_Topics_Moved'] = "Hiçbir baþlýk taþýnmamýþtýr";

$lang['Confirm_delete_topic'] = "Seçtiðiniz baþlýðýn/baþlýklarýn silinmesini istediðinize emin misiniz?";
$lang['Confirm_lock_topic'] = "Seçtiðiniz baþlýðýn/baþlýklarýn kilitlenmesini istediðinize emin misiniz?";
$lang['Confirm_unlock_topic'] = "Seçtiðiniz baþlýðýn/baþlýklarýn kilitlerinin açýlmasýný istediðinize emin misiniz?";
$lang['Confirm_move_topic'] = "Seçtiðiniz baþlýðýn/baþlýklarýn taþýnmasýný istediðinize emin misiniz?";

$lang['Move_to_forum'] = "Bu foruma taþý:";
$lang['Leave_shadow_topic'] = "Eski forumda gölgesini býrak";

$lang['Split_Topic'] = "Baþlýk Bölme Kontrol Paneli";
$lang['Split_Topic_explain'] = "Bu form ile bir ana baþlýðý, ister tek tek mesaj seçerek ister belli bir mesajdan ayýrarak ikiye bölebilirsiniz";
$lang['Split_title'] = "Yeni konu baþlýðý";
$lang['Split_forum'] = "Yeni baþlýðýn forumu";
$lang['Split_posts'] = "Seçilen mesajlarý ayýr";
$lang['Split_after'] = "Seçilen mesajdan ayýr";
$lang['Topic_split'] = "Seçilen baþlý baþarýyla ayrýlmýþtýr";

$lang['Too_many_error'] = "Çok fazla mesaj seçtiniz. Baþlýðý sadece bir mesajdan ayýrabilirsiniz!";

$lang['None_selected'] = "Bu iþlemi yapmak için hiçbir baþlýðý seçmediniz. Lütfen geri dönüp bir tane seçiniz";
$lang['New_forum'] = "Yeni forum";

$lang['This_posts_IP'] = "Bu mesajý gönderenin IP adresi";
$lang['Other_IP_this_user'] = "Bu kullanýcýnýn diðer IP adresleri";
$lang['Users_this_IP'] = "Bu IP adresini kullanan diðer kullanýcýlar";
$lang['IP_info'] = "IP bilgisi";
$lang['Lookup_IP'] = "Bu IP adresini ara";


//
// Timezones ... for display on each page
//
$lang['All_times'] = "Tüm saatler %s"; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = "GMT - 12 Saat";
$lang['-11'] = "GMT - 11 Saat";
$lang['-10'] = "HST (Hawaii)";
$lang['-9'] = "GMT - 9 Saat";
$lang['-8'] = "PST (U.S./Kanada)";
$lang['-7'] = "MST (U.S./Kanada)";
$lang['-6'] = "CST (U.S./Kanada)";
$lang['-5'] = "EST (U.S./Kanada)";
$lang['-4'] = "GMT - 4 Saat";
$lang['-3.5'] = "GMT - 3.5 Saat";
$lang['-3'] = "GMT - 3 Saat";
$lang['-2'] = "Orta-Atlantik";
$lang['-1'] = "GMT - 1 Saat";
$lang['0'] = "GMT";
$lang['1'] = "CET (Avrupa)";
$lang['2'] = "EET (Avrupa)";
$lang['3'] = "GMT + 3 Saat";
$lang['3.5'] = "GMT + 3.5 Saat";
$lang['4'] = "GMT + 4 Saat";
$lang['4.5'] = "GMT + 4.5 Saat";
$lang['5'] = "GMT + 5 Saat";
$lang['5.5'] = "GMT + 5.5 Saat";
$lang['6'] = "GMT + 6 Saat";
$lang['7'] = "GMT + 7 Saat";
$lang['8'] = "WST (Avusturalya)";
$lang['9'] = "GMT + 9 Saat";
$lang['9.5'] = "CST (Avusturalya)";
$lang['10'] = "EST (Avusturalya)";
$lang['11'] = "GMT + 11 Hours";
$lang['12'] = "GMT + 12 Hours";

// These are displayed in the timezone select box
$lang['tz']['-12'] = "(GMT -12:00 Saat) Eniwetok, Kwajalein";
$lang['tz']['-11'] = "(GMT -11:00 Saat) Midway Island, Samoa";
$lang['tz']['-10'] = "(GMT -10:00 Saat) Hawaii";
$lang['tz']['-9'] = "(GMT -9:00 Saat) Alaska";
$lang['tz']['-8'] = "(GMT -8:00 Saat) Pacific Time (US &amp; Canada), Tijuana";
$lang['tz']['-7'] = "(GMT -7:00 Saat) Mountain Time (US &amp; Canada), Arizona";
$lang['tz']['-6'] = "(GMT -6:00 Saat) Central Time (US &amp; Canada), Mexico City";
$lang['tz']['-5'] = "(GMT -5:00 Saat) Eastern Time (US &amp; Canada), Bogota, Lima, Quito";
$lang['tz']['-4'] = "(GMT -4:00 Saat) Atlantic Time (Canada), Caracas, La Paz";
$lang['tz']['-3.5'] = "(GMT -3:30 Saat) Newfoundland";
$lang['tz']['-3'] = "(GMT -3:00 Saat) Brassila, Buenos Aires, Georgetown, Falkland Is";
$lang['tz']['-2'] = "(GMT -2:00 Saat) Mid-Atlantic, Ascension Is., St. Helena";
$lang['tz']['-1'] = "(GMT -1:00 Saat) Azores, Cape Verde Islands";
$lang['tz']['0'] = "(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia";
$lang['tz']['1'] = "(GMT +1:00 Saat) Amsterdam, Berlin, Brussels, Madrid, Paris, Rome";
$lang['tz']['2'] = "(GMT +2:00 Saat) Ýstanbul, Cairo, Helsinki, Kaliningrad, South Africa";
$lang['tz']['3'] = "(GMT +3:00 Saat) Baghdad, Riyadh, Moscow, Nairobi";
$lang['tz']['3.5'] = "(GMT +3:30 Saat) Tehran";
$lang['tz']['4'] = "(GMT +4:00 Saat) Abu Dhabi, Baku, Muscat, Tbilisi";
$lang['tz']['4.5'] = "(GMT +4:30 Saat) Kabul";
$lang['tz']['5'] = "(GMT +5:00 Saat) Ekaterinburg, Islamabad, Karachi, Tashkent";
$lang['tz']['5.5'] = "(GMT +5:30 Saat) Bombay, Calcutta, Madras, New Delhi";
$lang['tz']['6'] = "(GMT +6:00 Saat) Almaty, Colombo, Dhaka, Novosibirsk";
$lang['tz']['6.5'] = "(GMT +6:30 Saat) Rangoon";
$lang['tz']['7'] = "(GMT +7:00 Saat) Bangkok, Hanoi, Jakarta";
$lang['tz']['8'] = "(GMT +8:00 Saat) Beijing, Hong Kong, Perth, Singapore, Taipei";
$lang['tz']['9'] = "(GMT +9:00 Saat) Osaka, Sapporo, Seoul, Tokyo, Yakutsk";
$lang['tz']['9.5'] = "(GMT +9:30 Saat) Adelaide, Darwin";
$lang['tz']['10'] = "(GMT +10:00 Saat) Canberra, Guam, Melbourne, Sydney, Vladivostok";
$lang['tz']['11'] = "(GMT +11:00 Saat) Magadan, New Caledonia, Solomon Islands";
$lang['tz']['12'] = "(GMT +12:00 Saat) Auckland, Wellington, Fiji, Marshall Island";

$lang['days_long'][0] = "Pazar";
$lang['days_long'][1] = "Pazartesi";
$lang['days_long'][2] = "Salý";
$lang['days_long'][3] = "Çarþamba";
$lang['days_long'][4] = "Perþembe";
$lang['days_long'][5] = "Cuma";
$lang['days_long'][6] = "Cumartesi";

$lang['days_short'][0] = "Pzr";
$lang['days_short'][1] = "Pzts";
$lang['days_short'][2] = "Sal";
$lang['days_short'][3] = "Çrþ";
$lang['days_short'][4] = "Prþ";
$lang['days_short'][5] = "Cum";
$lang['days_short'][6] = "Cmt";

$lang['months_long'][0] = "Ocak";
$lang['months_long'][1] = "Þubat";
$lang['months_long'][2] = "Mart";
$lang['months_long'][3] = "Nisan";
$lang['months_long'][4] = "Mayýs";
$lang['months_long'][5] = "Haziran";
$lang['months_long'][6] = "Temmuz";
$lang['months_long'][7] = "Aðustos";
$lang['months_long'][8] = "Eylül";
$lang['months_long'][9] = "Ekim";
$lang['months_long'][10] = "Kasým";
$lang['months_long'][11] = "Aralýk";

$lang['months_short'][0] = "Oca";
$lang['months_short'][1] = "Þub";
$lang['months_short'][2] = "Mar";
$lang['months_short'][3] = "Nis";
$lang['months_short'][4] = "May";
$lang['months_short'][5] = "Hzr";
$lang['months_short'][6] = "Tem";
$lang['months_short'][7] = "Aðu";
$lang['months_short'][8] = "Eyl";
$lang['months_short'][9] = "Ekm";
$lang['months_short'][10] = "Ksm";
$lang['months_short'][11] = "Arl";

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = "Bilgi";
$lang['Critical_Information'] = "Kritik Bilgi";

$lang['General_Error'] = "Genel Hata";
$lang['Critical_Error'] = "Kritik Hata";
$lang['An_error_occured'] = "Bir hata oluþtu";
$lang['A_critical_error'] = "Kritik bir hata oluþtu";

//
// That's all Folks!
// -------------------------------------------------

?>