<?php
/***************************************************************************
 *                            lang_admin.php [Polish]
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
// Translation by Mike Paluchowski and Radek Kmiecicki
// http://www.phpbb.pl/
//


$lang['General'] = "Ogólne";
$lang['Users'] = "U¿ytkownicy";
$lang['Groups'] = "Grupy";
$lang['Forums'] = "Fora";
$lang['Styles'] = "Style";

$lang['Configuration'] = "Konfiguracja";
$lang['Permissions'] = "Zezwolenia";
$lang['Manage'] = "Zarz±dzaj";
$lang['Disallow'] = "Zabroñ nazwy";
$lang['Prune'] = "Czyszczenie";
$lang['Mass_Email'] = "Mas. Korespondencja";
$lang['Ranks'] = "Rangi";
$lang['Smilies'] = "U¶mieszki";
$lang['Ban_Management'] = "Banicja";
$lang['Word_Censor'] = "Cenzura S³ów";
$lang['Export'] = "Eksport";
$lang['Create_new'] = "Utwórz";
$lang['Add_new'] = "Dodaj";
$lang['Backup_DB'] = "Kopia Zapasowa";
$lang['Restore_DB'] = "Odtwarzanie";


//
// Index
//
$lang['Admin'] = "Administracja";
$lang['Not_admin'] = "Nie masz autoryzacji do administracji tym forum";
$lang['Welcome_phpBB'] = "Witamy w phpBB";
$lang['Admin_intro'] = "Dziêkujemy, ¿e wybra³e¶ phpBB do obs³ugi Twojego forum. Ten ekran przedstawia krótki przegl±d ró¿norodnych danych statystycznych, dotycz±cych forum. Mo¿esz wróciæ do tej strony klikaj±c odno¶nik <u>Indeks Administracji</u> na lewym panelu. Aby powróciæ do strony g³ównej forum kliknij logo phpBB na w lewym panelu. Inne odno¶niki po lewej stronie ekranu daj± dostêp do narzêdzi kontroluj±cych ka¿dy aspekt zachowania forum. Ka¿de z nich zawiera osobne instrukcje u¿ycia.";
$lang['Main_index'] = "Str. G³ówna Forum";
$lang['Forum_stats'] = "Statystyki Forum";
$lang['Admin_Index'] = "Indeks Administracji";
$lang['Preview_forum'] = "Podgl±d Forum";

$lang['Click_return_admin_index'] = "Kliknij %sTutaj%s aby powróciæ do Indeksu Administracji";

$lang['Statistic'] = "Statystyki";
$lang['Value'] = "Warto¶æ";
$lang['Number_posts'] = "Liczba postów";
$lang['Posts_per_day'] = "Postów dziennie";
$lang['Number_topics'] = "Liczba tematów";
$lang['Topics_per_day'] = "Tematów dziennie";
$lang['Number_users'] = "Liczba u¿ytkowników";
$lang['Users_per_day'] = "U¿ytk. dziennie";
$lang['Board_started'] = "Start Forum";
$lang['Avatar_dir_size'] = "Katalog Emblematów";
$lang['Database_size'] = "Baza Danych";
$lang['Gzip_compression'] ="Kompresja Gzip";
$lang['Not_available'] = "Niedostêpne";

$lang['ON'] = "TAK"; // This is for GZip compression
$lang['OFF'] = "NIE"; 


//
// DB Utils
//
$lang['Database_Utilities'] = "Narzêdzia Bazy Danych";

$lang['Restore'] = "Przywracanie";
$lang['Backup'] = "Kopia Zapasowa";
$lang['Restore_explain'] = "Tutaj przeprowadzone zostanie odtwarzanie wszystkich tabeli phpBB z zapisanego pliku. Je¿eli twój serwer na to pozwala mo¿esz podaæ plik skompresowany w gzip a zostanie on automatycznie rozpakowany. <b>UWAGA</b> Nadpisane zostan± wszystkie istniej±ce dane. Proces przywracania mo¿e d³ugo potrwaæ i do jego zakoñczenia nie wolno odej¶æ z tej strony.";
$lang['Backup_explain'] = "Tutaj mo¿esz utworzyæ kopiê zapasow± wszystkich danych phpBB. Je¿eli masz dodatkowo utworzone tabele w tej samej bazie danych co phpBB, które chcia³by¶ skopiowaæ wpisz ich nazwy oddzielone przecinkami w pole Dodatkowe Tabele. Je¿eli Twój serwer ma tak± funkcjê mo¿esz tak¿e skompresowaæ plik w gzip aby zmniejszyæ jego rozmiar przed ¶ci±gniêciem.";

$lang['Backup_options'] = "Opcje Kopii";
$lang['Start_backup'] = "Zacznij Kopiowanie";
$lang['Full_backup'] = "Pe³na Kopia";
$lang['Structure_backup'] = "Tylko Struktura";
$lang['Data_backup'] = "Tylko Dane";
$lang['Additional_tables'] = "Dodatkowe Tabele";
$lang['Gzip_compress'] = "Skompresuj plik w Gzip";
$lang['Select_file'] = "Wybierz plik";
$lang['Start_Restore'] = "Zacznij Odtwarzanie";

$lang['Restore_success'] = "Baza Danych zosta³a odtworzona.<br /><br />Twoje forum powinno ju¿ wróciæ do stanu sprzed wykonania kopii.";
$lang['Backup_download'] = "¦ci±ganie rozpocznie siê lada chwila, zaczekaj";
$lang['Backups_not_supported'] = "Przepraszamy, ale kopie zapasowe nie s± obecnie obs³ugiwane dla Twojego systemu";

$lang['Restore_Error_uploading'] = "B³±d w wysy³aniu pliku z danymi";
$lang['Restore_Error_filename'] = "Problem z nazw± pliku, spróbuj wys³aæ inny";
$lang['Restore_Error_decompress'] = "Nie mogê zdekompresowaæ pliku, wy¶lij sam plik tekstowy";
$lang['Restore_Error_no_file'] = "Nie wys³ano ¿adnego pliku";


//
// Auth pages
//
$lang['Select_a_User'] = "Wybierz U¿ytkownika";
$lang['Select_a_Group'] = "Wybierz Grupê";
$lang['Select_a_Forum'] = "Wybierz Forum";
$lang['Auth_Control_User'] = "Kontrola Zezwoleñ U¿ytkowników"; 
$lang['Auth_Control_Group'] = "Kontrola Zezwoleñ Grup"; 
$lang['Auth_Control_Forum'] = "Kontrola Zezwoleñ For"; 
$lang['Look_up_User'] = "Wybierz U¿ytkownika"; 
$lang['Look_up_Group'] = "Wybierz Grupy"; 
$lang['Look_up_Forum'] = "Wybierz Forum"; 

$lang['Group_auth_explain'] = "Tutaj mo¿esz zmieniaæ zezwolenia i status moderatora przydzielony ka¿dej grupie u¿ytkowników. Nie zapomnij zmieniaj±c ustawienia, ¿e indywidualne uprawnienia mog± nadal zezwalaæ u¿ytkownikowi na dostêp do for itp. Zostaniesz ostrze¿ony gdy tak siê stanie.";
$lang['User_auth_explain'] = "Tutaj mo¿esz zmieniaæ zezwolenia i status moderatora dla ka¿dego u¿ytkownika. Nie zapomnij zmieniaj±c ustawienia, ¿e uprawnienia grupowe mog± nadal zezwalaæ u¿ytkownikowi na dostêp do for itp. Zostaniesz ostrze¿ony gdy tak siê stanie.";
$lang['Forum_auth_explain'] = "Tutaj mo¿esz zmieniæ poziomy autoryzacji dla ka¿dego forum. Masz do dyspozycji metodê prost± i zaawansowan±, z których druga oferuje wiêksze mo¿liwo¶ci kontroli ustawieñ. Pamiêtaj, ¿e zmiana ustawieñ dotycz±cych for zadecyduje o tym, co u¿ytkownicy bêd± mogli na nich robiæ.";

$lang['Simple_mode'] = "Tryb Prosty";
$lang['Advanced_mode'] = "Tryb Zaawansowany";
$lang['Moderator_status'] = "Status Moderatora";

$lang['Allowed_Access'] = "Dostêp Zezwolony";
$lang['Disallowed_Access'] = "Dostêp Zabroniony";
$lang['Is_Moderator'] = "Moderator";
$lang['Not_Moderator'] = "Nie Moderator";

$lang['Conflict_warning'] = "Ostrze¿enie o Konflikcie Autoryzacji";
$lang['Conflict_access_userauth'] = "Ten u¿ytkownik nadal ma dostêp do tego forum dziêki uprawnieniom grupowym. Aby w pe³ni pozbawiæ go tych uprawnieñ musisz zmieniæ ustawienia danej grupy, lub go z niej usun±æ. Grupy daj±ce mu prawa (i fora, których to dotyczy) s± wypisane poni¿ej.";
$lang['Conflict_mod_userauth'] = "Ten u¿ytkownik nadal ma prawa moderatora dziêki uprawnieniom grupowym. Aby w pe³ni pozbawiæ go tych uprawnieñ musisz zmieniæ ustawienia danej grupy, lub go z niej usun±æ. Grupy daj±ce mu prawa (i fora, których to dotyczy) s± wypisane poni¿ej.";

$lang['Conflict_access_groupauth'] = "Poni¿szy u¿ytkownik (lub u¿ytkownicy) nadal ma dostêp do tego forum dziêki ustawieniom indywidualnym. Aby pozbawiæ go tych uprawnieñ musisz zmieniæ ich zezwolenia. U¿ytkownicy o takich prawach (i fora, których to dotyczy) s± wypisane poni¿ej.";
$lang['Conflict_mod_groupauth'] = "Poni¿szy u¿ytkownik (lub u¿ytkownicy) nadal ma prawa moderatora na tym forum dziêki ustawieniom indywidualnym. Aby pozbawiæ go tych uprawnieñ musisz zmieniæ ich zezwolenia. U¿ytkownicy o takich prawach (i fora, których to dotyczy) s± wypisane poni¿ej.";

$lang['Public'] = "Publiczne";
$lang['Private'] = "Prywatne";
$lang['Registered'] = "Zarejestrowani";
$lang['Administrators'] = "Administratorzy";
$lang['Hidden'] = "Ukryte";

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = "WSZYSCY";
$lang['Forum_REG'] = "ZAREJESTR.";
$lang['Forum_PRIVATE'] = "PRYWATNE";
$lang['Forum_MOD'] = "MODERAT.";
$lang['Forum_ADMIN'] = "ADMIN";

$lang['View'] = "Widoczny";
$lang['Read'] = "Czytanie";
$lang['Post'] = "Pisanie";
$lang['Reply'] = "Odpowiedzi";
$lang['Edit'] = "Edycja";
$lang['Delete'] = "Usuwanie";
$lang['Sticky'] = "Przyklejone";
$lang['Announce'] = "Og³oszenia"; 
$lang['Vote'] = "G³osowanie";
$lang['Pollcreate'] = "Tworzenie ankiet";

$lang['Permissions'] = "Zezwolenia";
$lang['Simple_Permission'] = "Proste Zezwolenia";

$lang['User_Level'] = "Poziom u¿ytkownika"; 
$lang['Auth_User'] = "U¿ytkownik";
$lang['Auth_Admin'] = "Administrator";
$lang['Group_memberships'] = "Cz³onkostwo w grupach";
$lang['Usergroup_members'] = "Tak grupa ma nastêpuj±cyh cz³onków";

$lang['Forum_auth_updated'] = "Zezwolenia For zosta³y zaktualizowane";
$lang['User_auth_updated'] = "Zezwolenia U¿ytkowników zosta³y zaktualizowane";
$lang['Group_auth_updated'] = "Zezwolenia Grup zosta³y zaktualizowane";

$lang['Auth_updated'] = "Zezwolenia zosta³y zmienione";
$lang['Click_return_userauth'] = "Kliknij %sTutaj%s aby powróciæ do Zezwoleñ U¿ytkowników";
$lang['Click_return_groupauth'] = "Kliknij %sTutaj%s aby powróciæ do Zezwoleñ Grup";
$lang['Click_return_forumauth'] = "Kliknij %sTutaj%s aby powróciæ do Zezwoleñ For";


//
// Banning
//
$lang['Ban_control'] = "Kontrola Banicji";
$lang['Ban_explain'] = "Tutaj mozesz kontrolowaæ banicje u¿ytkowników. Uzyskasz to przez banowanie danego u¿ytkownika, zakresu numerów IP lub hostów. Dziêki tym metodom u¿ytkownik nie dostanie siê nawet na stronê g³ówn±. Aby zapobiec rejestracji pod innymi nazwami mo¿esz tak¿e zbanowaæ konkretny adres email. Pamiêtaj jednak, ¿e zbanowanie adresu email nie uniemo¿liwia uczestnictwa w dzia³alno¶ci forum, to tego s³u¿± dwie pierwsze metody.";
$lang['Ban_explain_warn'] = "Pamiêtaj, ¿e wpisanie zakresu adresów IP oznacza dopisanie do listy ka¿dego adresu z podanego zakresu. Gdzie tylko bêdzie to mo¿liwe dodawane bêd± znaki zamienne, ograniczaj±ce liczbê wpisów. Je¿eli naprawdê musisz wpisaæ zakres, staraj siê by by³ najmniejszy lub lepiej podaj konkretne adresy.";

$lang['Select_username'] = "Wybierz Nazwê U¿ytkownika";
$lang['Select_ip'] = "Wybierz IP";
$lang['Select_email'] = "Wybierz adres Email";

$lang['Ban_username'] = "Zbanuj jednego lub wielu konkretnych u¿ytkowników";
$lang['Ban_username_explain'] = "Mo¿esz zbanowaæ wielu u¿ytkowników jednocze¶nie korzystaj±c z kombinacji przycisków myszy i klawiatury odpowiednich dla twojego komputera i przegl±darki";

$lang['Ban_IP'] = "Zbanuj jeden lub wiêcej adresów IP lub hostów";
$lang['IP_hostname'] = "Adresy IP lub hosty";
$lang['Ban_IP_explain'] = "Aby podaæ kilka adresów IP lub hostów oddziel je przecinkami. Aby okre¶liæ zakres adresów IP oddziel pocz±tkowy i koñcowy my¶lnikiem (-), znakiem zamiennym jest *.";

$lang['Ban_email'] = "Zbanuj jeden lub wiêcej adresów email";
$lang['Ban_email_explain'] = "Aby podaæ wiêcej ni¿ jeden adres email, oddziel je przecinkami. Znakiem zamiennym jest *, np. *@hotmail.com.";

$lang['Unban_username'] = "Odbanuj jednego lub wiêcej u¿ytkowników";
$lang['Unban_username_explain'] = "Mo¿esz odbanowaæ wielu u¿ytkowników jednocze¶nie korzystaj±c z kombinacji przycisków myszy i klawiatury odpowiednich dla twojego komputera i przegl±darki.";

$lang['Unban_IP'] = "Odbanuj jeden lub wiêcej adresów IP";
$lang['Unban_IP_explain'] = "Mo¿esz odbanowaæ wiele adresów IP jednocze¶nie korzystaj±c z kombinacji przycisków myszy i klawiatury odpowiednich dla twojego komputera i przegl±darki.";

$lang['Unban_email'] = "Odbanuj jeden lub wiêcej adresów email";
$lang['Unban_email_explain'] = "Mo¿esz odbanowaæ wiele adresów email jednocze¶nie korzystaj±c z kombinacji przycisków myszy i klawiatury odpowiednich dla twojego komputera i przegl±darki.";

$lang['No_banned_users'] = "Brak zbanowanych nazw";
$lang['No_banned_ip'] = "Brak zbanowanych adresów IP";
$lang['No_banned_email'] = "Brak zbanowanych adresów email";

$lang['Ban_update_sucessful'] = "Lista banicji zosta³a zaktualizowana";
$lang['Click_return_banadmin'] = "Kliknij %sTutaj%s aby powróciæ do Kontroli Banicji";


//
// Configuration
//
$lang['General_Config'] = "Ustawienia G³ówne";
$lang['Config_explain'] = "Poni¿szy formularz pozwala dostosowaæ wszystkie g³ówne opcje forum. Szczegó³owa konfiguracja For i U¿ytkowników jest dostêpna z odno¶ników po lewej stronie.";

$lang['Click_return_config'] = "Kliknij %sTutaj%s aby powróciæ do Ustawieñ G³ównych";

$lang['General_settings'] = "Generalne Ustawienia Forum";
$lang['Site_name'] = "Nazwa Strony";
$lang['Site_desc'] = "Opis Strony";
$lang['Board_disable'] = "Wy³±cz forum";
$lang['Board_disable_explain'] = "To uczyni forum niedostêpnym dla u¿ytkowników. Pozostañ w tym czasie zalogowany, inaczej nie bêdziesz móg³ siê powtórnie zalogowaæ!";
$lang['Acct_activation'] = "W³±cz aktywacjê kont";
$lang['Acc_None'] = "Brak"; // These three entries are the type of activation
$lang['Acc_User'] = "U¿ytkownik";
$lang['Acc_Admin'] = "Admin";

$lang['Abilities_settings'] = "Podstawowe Ustawienia Forum i U¿ytkowników";
$lang['Max_poll_options'] = "Maksymalna liczba opcji ankiety";
$lang['Flood_Interval'] = "Interwa³ Anty-Floodowy";
$lang['Flood_Interval_explain'] = "Ilo¶æ sekund, zanim mo¿na wys³aæ nowy post"; 
$lang['Board_email_form'] = "Email przez forum";
$lang['Board_email_form_explain'] = "U¿ytkownicy wysy³aj± email'e przez forum";
$lang['Topics_per_page'] = "Tematów na Stronê";
$lang['Posts_per_page'] = "Postów na Stronê";
$lang['Hot_threshold'] = "Postów do okre¶lenia Popularny";
$lang['Default_style'] = "Domy¶lny Styl";
$lang['Override_style'] = "Zignoruj Styl U¿ytkownika";
$lang['Override_style_explain'] = "Zamienia styl u¿ytkownika na domy¶lny";
$lang['Default_language'] = "Domy¶lny Jêzyk";
$lang['Date_format'] = "Format Daty";
$lang['System_timezone'] = "Strefa Czasowa Systemu";
$lang['Enable_gzip'] = "W³±cz Komprecjê GZip";
$lang['Enable_prune'] = "W³±cz Czyszczenie Forum";
$lang['Allow_HTML'] = "Zezwól na HTML";
$lang['Allow_BBCode'] = "Zezwól na BBCode";
$lang['Allowed_tags'] = "Dozwolone tagi HTML";
$lang['Allowed_tags_explain'] = "Oddziel znaczniki przecinkami";
$lang['Allow_smilies'] = "Zezwól na U¶mieszki";
$lang['Smilies_path'] = "¦cie¿ka dostêpu do U¶mieszków";
$lang['Smilies_path_explain'] = "¦cie¿ka od katalogu g³ównego forum, np. images/smilies";
$lang['Allow_sig'] = "Zezwól na Pospisy";
$lang['Max_sig_length'] = "Maksymalna d³ugo¶æ podpisu";
$lang['Max_sig_length_explain'] = "Maksymalna ilo¶æ znaków w podpisie u¿ytkownika";
$lang['Allow_name_change'] = "Zezwól na zmiany Nazw U¿ytkownika";

$lang['Avatar_settings'] = "Ustawienia Emblematów";
$lang['Allow_local'] = "W³±cz galeriê emblematów";
$lang['Allow_remote'] = "W³±cz zdalne emblematy";
$lang['Allow_remote_explain'] = "Emblematy bêd± wy¶wietlane z innych serwerów";
$lang['Allow_upload'] = "W³±cz wysy³anie emblematów";
$lang['Max_filesize'] = "Maksymalny rozmiar pliku emblematu";
$lang['Max_filesize_explain'] = "Dla obrazków wysy³anych na serwer";
$lang['Max_avatar_size'] = "Maksymalne Rozmiary Emblematu";
$lang['Max_avatar_size_explain'] = "(Wysoko¶æ x Szeroko¶æ w pikselach)";
$lang['Avatar_storage_path'] = "¦cie¿ka Zapisu Emblematów";
$lang['Avatar_storage_path_explain'] = "¦cie¿ka od katalogu g³ównego phpBB, np. images/avatars";
$lang['Avatar_gallery_path'] = "¦cie¿ka Galerii Emblematów";
$lang['Avatar_gallery_path_explain'] = "¦cie¿ka od katalogu g³ównego phpBB dla wcze¶niej ³adowanych plików, np. images/avatars/gallery";

$lang['COPPA_settings'] = "Ustawienia COPPA";
$lang['COPPA_fax'] = "Numer Faxu COPPA";
$lang['COPPA_mail'] = "Adres Pocztowy COPPA";
$lang['COPPA_mail_explain'] = "To jest adres pocztowy, pod który rodzice bêd± przesy³ali formularze rejestracji COPPA";

$lang['Email_settings'] = "Ustawienia Email'i";
$lang['Admin_email'] = "Adres Email Admina";
$lang['Email_sig'] = "Podpis pod Email";
$lang['Email_sig_explain'] = "Ten tekst bêdzie dodawany do ka¿dej wiadomo¶ci wysy³anej przez forum";
$lang['Use_SMTP'] = "U¿ywaj Serwera SMTP";
$lang['Use_SMTP_explain'] = "Powiedz tak, je¶li chcesz aby wiadomo¶ci email by³y przesy³ane przez odpowiedni serwer zamiast lokalnej funkcji mail";
$lang['SMTP_server'] = "Adres Serwera SMTP";

$lang['Disable_privmsg'] = "Prywatne Wiadomo¶ci";
$lang['Inbox_limits'] = "Maks. wiadomo¶ci w Skrzynce";
$lang['Sentbox_limits'] = "Maks. wiadomo¶ci w Wys³anych";
$lang['Savebox_limits'] = "Maks. wiadomo¶ci w Zapisanych";

$lang['Cookie_settings'] = "Ustawienia Cookies"; 
$lang['Cookie_settings_explain'] = "Kontroluj± w jaki sposób zdefiniowane zostanie cookie wys³ane do przegl±darki. W wiêkszo¶ci przypadków powinny wystarczyæ warto¶ci domy¶lne. Je¶li zechcesz je zmieniæ rób to ostro¿nie, nieprawid³owe ustawienia mog± uniemo¿liwiæ logowanie.";
$lang['Cookie_name'] = "Nazwa Cookie";
$lang['Cookie_domain'] = "Domena Cookie";
$lang['Cookie_path'] = "¦cie¿ka Cookie";
$lang['Session_length'] = "D³ugo¶æ Sesji [ sekundy ]";
$lang['Cookie_secure'] = "Bezpieczne Cookie [ https ]";


//
// Forum Management
//
$lang['Forum_admin'] = "Administracja Forum";
$lang['Forum_admin_explain'] = "W tym miejscu mo¿esz dodawaæ, usuwaæ, modyfikowaæ, zmieniaæ kolejno¶æ i ponownie synchronizowaæ kategorie i fora.";
$lang['Edit_forum'] = "Edytuj forum";
$lang['Create_forum'] = "Nowe Forum";
$lang['Create_category'] = "Nowa Kategoria";
$lang['Remove'] = "Usuñ";
$lang['Action'] = "Dzia³anie";
$lang['Update_order'] = "Aktualizuj Porz±dek";
$lang['Config_updated'] = "Konfiguracja Forum Zosta³a Zaktualizowana";
$lang['Edit'] = "Edycja";
$lang['Delete'] = "Usuñ";
$lang['Move_up'] = "W górê";
$lang['Move_down'] = "W dó³";
$lang['Resync'] = "Synch.";
$lang['No_mode'] = "Nie okre¶lono trybu";
$lang['Forum_edit_delete_explain'] = "Poni¿szy formularz pozwoli zmieniæ wszystkie podstawowe opcje forum. Aby zmieniæ szczegó³owe ustawienia U¿ytkowników i For skorzystaj z odno¶ników po lewej.";

$lang['Move_contents'] = "Przenie¶ ca³± zawarto¶æ";
$lang['Forum_delete'] = "Usuñ Forum";
$lang['Forum_delete_explain'] = "Poni¿szy formularz pozwoli na usuniêcie forum (lub kategorii) i zdecydowanie co zrobiæ z tematami (lub forami), które by³y w nim zawarte.";

$lang['Forum_settings'] = "Generalne Ustawienia Forum";
$lang['Forum_name'] = "Nazwa Forum";
$lang['Forum_desc'] = "Opis";
$lang['Forum_status'] = "Status Forum";
$lang['Forum_pruning'] = "Automatyczne Czyszczenie";

$lang['prune_freq'] = 'Sprawd¼ wiek tematu co';
$lang['prune_days'] = "Usuñ tematy, w których nie pisano nic przez";
$lang['Set_prune_data'] = "W³±czy³e¶ automatyczne czyszczenie dla tego forum ale nie okresli³e¶ jego parametrów. Wróæ i wpisz wszystkie dane.";

$lang['Move_and_Delete'] = "Przenie¶ i Usuñ";

$lang['Delete_all_posts'] = "Usuñ wszystkie posty";
$lang['Nowhere_to_move'] = "Nie ma dok±d przenie¶æ";

$lang['Edit_Category'] = "Edytuj Kategoriê";
$lang['Edit_Category_explain'] = "U¿yj tego formularza do zmiany nazwy kategorii.";

$lang['Forums_updated'] = "Dane dotycz±ce For i Kategorii zosta³y zaktualizowane";

$lang['Must_delete_forums'] = "Musisz usun±æ wszystkie fora przed usuniêciem tej kategorii";

$lang['Click_return_forumadmin'] = "Kliknij %sTutaj%s aby powróciæ do Administracji Forum";


//
// Smiley Management
//
$lang['smiley_title'] = "Edycja U¶mieszków";
$lang['smile_desc'] = "Na tej stronie mo¿esz dodawaæ, usuwaæ i zmieniaæ ikony emocji lub u¶mieszki, które u¿ytkownicy mog± u¿ywaæ w swoich postach i prywatnych wiadomo¶ciach.";

$lang['smiley_config'] = "Konfiguracja U¶mieszku";
$lang['smiley_code'] = "Kod U¶mieszku";
$lang['smiley_url'] = "Plik Obrazka U¶mieszku";
$lang['smiley_emot'] = "Emocja U¶mieszku";
$lang['smile_add'] = "Nowy U¶mieszek";
$lang['Smile'] = "U¶miech";
$lang['Emotion'] = "Emocja";

$lang['Select_pak'] = "Wybierz Plik Paczki (.pak)";
$lang['replace_existing'] = "Zamieñ Istniej±cy U¶miech";
$lang['keep_existing'] = "Zachowaj Istniej±cy U¶miech";
$lang['smiley_import_inst'] = "Powiniene¶ rozpakowaæ paczkê u¶mieszków i wys³aæ pliki do odpowiedniego katalogu U¶mieszków. Potem ustaw odpowiednio poni¿szy formularz i importuj paczkê.";
$lang['smiley_import'] = "Import Paczki U¶mieszków";
$lang['choose_smile_pak'] = "Wybierz Plik .pak Paczki U¶mieszków";
$lang['import'] = "Importuj U¶mieszki";
$lang['smile_conflicts'] = "Co zrobiæ w przypadku konfliktów";
$lang['del_existing_smileys'] = "Usuñ istniej±ce u¶mieczki przed importem";
$lang['import_smile_pack'] = "Importuj Paczkê";
$lang['export_smile_pack'] = "Utwórz Paczkê";
$lang['export_smiles'] = "Aby utworzyæ paczkê u¶mieszków z obecnie zainstalowanych kliknij %sTutaj%s aby ¶ci±gn±æ plik .pak u¶mieszków. Nazwij go odpowiednio zachowuj±c rozszerzenie .pak. Potem spakuj ten plik razem z obrazkami u¶mieszków w archiwum zip.";

$lang['smiley_add_success'] = "U¶mieszek zosya³ dodany";
$lang['smiley_edit_success'] = "U¶mieszek zosta³ zaktualizowany";
$lang['smiley_import_success'] = "Paczka U¶mieszków zosta³a zaimportowana!";
$lang['smiley_del_success'] = "U¶mieszek zosta³ usuniêty";
$lang['Click_return_smileadmin'] = "Kliknij %sTutaj%s aby powróciæ do Administracji U¶mieszkami";


//
// User Management
//
$lang['User_admin'] = "Administracja U¿ytkownikami";
$lang['User_admin_explain'] = "Tutaj mo¿esz zmieniæ informacje o u¿ytkowniku i ustawione przez niego opcje. Aby zmieniæ jego prawa dostêpu skorzystaj z systemu zmiany zezwoleñ.";

$lang['Look_up_user'] = "Poka¿ u¿ytkownika";

$lang['Admin_user_fail'] = "Nie mo¿na by³o zaktualizowaæ profilu u¿ytkownika.";
$lang['Admin_user_updated'] = "Profil u¿ytkownika zosta³ zaktualizowany.";
$lang['Click_return_useradmin'] = "Kliknij %sTutaj%s aby powróciæ do Administracji U¿ytkownikami";

$lang['User_delete'] = "Usuñ tego u¿ytkownika";
$lang['User_delete_explain'] = "Kliknij tutaj aby usun±æ tego u¿ytkownika, nie mo¿na tego cofn±æ.";
$lang['User_deleted'] = "U¿ytkownik zosta³ usuniêty.";

$lang['User_status'] = "U¿ytkownik jest aktywny";
$lang['User_allowpm'] = "Mo¿e wysy³aæ Prywatne Wiadomo¶ci";
$lang['User_allowavatar'] = "Mo¿e pokazywaæ Emblemat";

$lang['Admin_avatar_explain'] = "Tutaj mo¿esz zobaczyæ i usun±æ obecny Emblemat u¿ytkownika.";

$lang['User_special'] = "Specjalne pola administratora";
$lang['User_special_explain'] = "Tych pól nie mog± zmieniaæ sami u¿ytkownicy. Mo¿esz tutaj zmodyfikowaæ ich status i inne opcje dotycz±ce ich mo¿liwo¶ci dzia³ania.";


//
// Group Management
//
$lang['Group_administration'] = "Administracja Grupami";
$lang['Group_admin_explain'] = "Z tego panelu mo¿esz administrowaæ wszystkimi grupami u¿ytkowników; mo¿esz je usuwaæ, tworzyæ i zmieniaæ ustawienia. Mo¿esz wybieraæ moderatorów, zmieniaæ na otwarte lub zamkniête i modyfikowaæ nazwê oraz opis.";
$lang['Error_updating_groups'] = "Wyst±pi³ b³±d podczas aktualizacji grup";
$lang['Updated_group'] = "Grupa zosta³a zaktualizowana";
$lang['Added_new_group'] = "Nowa grupa zosta³a utworzona";
$lang['Deleted_group'] = "Grupa zosta³a usuniêta";
$lang['New_group'] = "Utwórz now± grupê";
$lang['Edit_group'] = "Edytuj grupê";
$lang['group_name'] = "Nazwa Grupy";
$lang['group_description'] = "Opis Grupy";
$lang['group_moderator'] = "Moderator Grupy";
$lang['group_status'] = "Status Grupy";
$lang['group_open'] = "Otwarta";
$lang['group_closed'] = "Zamkniêta";
$lang['group_hidden'] = "Ukryta";
$lang['group_delete'] = "Usuñ Grupê";
$lang['group_delete_check'] = "Usuñ t± grupê";
$lang['submit_group_changes'] = "Wy¶lij Zmiany";
$lang['reset_group_changes'] = "Anuluj Zmiany";
$lang['No_group_name'] = "Musisz wpisaæ nazwê dla tej grupy";
$lang['No_group_moderator'] = "Musisz podaæ moderatora tej grupy";
$lang['No_group_mode'] = "Musisz podaæ tryb dzia³ania grupy, otwarta lub zamkniêta";
$lang['delete_group_moderator'] = "Usun±æ poprzedniego moderatora grupy?";
$lang['delete_moderator_explain'] = "Je¿eli zmieniasz moderatora zaznacz to pole aby usun±æ starego moderatora. Je¿eli tego nie zrobisz stanie siê on zwyk³ym cz³onkiem grupy.";
$lang['Click_return_groupsadmin'] = "Kliknij %sTutaj%s aby powróciæ do Administracji Grupami.";
$lang['Select_group'] = "Wybierz grupê";
$lang['Look_up_group'] = "Poka¿ grupê";


//
// Prune Administration
//
$lang['Forum_Prune'] = "Czyszczenie Forum";
$lang['Forum_Prune_explain'] = "Usuniête zostan± tematy, w których nie napisano nic nowego przez okre¶lon± liczbê dni. Je¿eli nie wpiszesz ¿adnej liczby usuniête zostan± wszystkie tematy. Nietkniête pozostan± tematy z aktywnymi ankietami oraz og³oszenia. Bêdziesz musia³ usun±æ je rêcznie.";
$lang['Do_Prune'] = "Wykonaj";
$lang['All_Forums'] = "Wszystkie Fora";
$lang['Prune_topics_not_posted'] = "Wyczy¶æ tematy bez nowych odpowiedzi przez dni";
$lang['Topics_pruned'] = "Usuniêto tematów";
$lang['Posts_pruned'] = "Usuniêto postów";
$lang['Prune_success'] = "Czyszczenie zosta³o wykonane";


//
// Word censor
//
$lang['Words_title'] = "Cenzura S³ów";
$lang['Words_explain'] = "Z tego miejsca mo¿esz dodawaæ, zmieniaæ i usuwaæ s³owa, które zostan± automatycznie ocenzurowane na Twoich forach. Dodatkowo ludzie nie bêd± mogli siê rejestrowaæ z nazwami zawieraj±cymi te s³owa. Znaki uniwersalne (*) s± dozwolone, np. *test* obejmie przetestowanie, test* obejmie testowanie, *test obejmie przedtest.";
$lang['Word'] = "S³owo";
$lang['Edit_word_censor'] = "Zmieñ Cenzurê";
$lang['Replacement'] = "Zamiennik";
$lang['Add_new_word'] = "Dodaj nowe s³owo";
$lang['Update_word'] = "Aktualizuj cenzora";

$lang['Must_enter_word'] = "Musisz wpisaæ s³owo i jego zamiennik";
$lang['No_word_selected'] = "Nie wybrano s³owa do edycji";

$lang['Word_updated'] = "Wybrana cenzura zosta³a zaktualizowana";
$lang['Word_added'] = "Nowa cenzura zosta³a dodana";
$lang['Word_removed'] = "Wybrana cenzura zosta³a usuniêta";

$lang['Click_return_wordadmin'] = "Kliknij %sTutaj%s aby powróciæ do Administracji Cenzur±";


//
// Mass Email
//
$lang['Mass_email_explain'] = "St±d mo¿esz wys³aæ wiadomo¶æ do wszystkich u¿ytkowników lub wszystkich cz³onków której¶ grupy. Zostanie to wykonane przez wys³anie email'a pod podany adres administrcyjny, wraz z kopia BCC (pol. UDW) wys³an± do wszystkich u¿ytkowników. Je¿eli wysy³asz list do du¿ej grupy osób czekaj cierpliwie na zakoñczenie procesu i nie przerywaj go. Wysy³anie masowej korespondencji mo¿e zaj±æ du¿o czasu, i po zakoñczeniu procesu zostaniesz o tym powiadomiony.";
$lang['Compose'] = "Utwórz"; 

$lang['Recipients'] = "Odbiorcy"; 
$lang['All_users'] = "Wszyscy U¿ytkownicy";

$lang['Email_successfull'] = "Twoja wiadomo¶æ zosta³a wys³ana";
$lang['Click_return_massemail'] = "Kliknij %sTutaj%s aby powróciæ do formularza Masowej Korespondencji";


//
// Ranks admin
//
$lang['Ranks_title'] = "Administracja Rangami";
$lang['Ranks_explain'] = "U¿ywaj±c tego formularza mo¿esz dodawaæ, zmieniaæ, przegl±daæ i usuwaæ rangi. Mo¿esz te¿ tworzyæ specjalne rangi i przydzielaæ je poprzez system zarz±dzania u¿ytkowników.";

$lang['Add_new_rank'] = "Dodaj now± rangê";

$lang['Rank_title'] = "Nazwa Rangi";
$lang['Rank_special'] = "Jest Rang± Specjaln±";
$lang['Rank_minimum'] = "Minimum Postów";
$lang['Rank_maximum'] = "Maksimum Postów";
$lang['Rank_image'] = "Obraz Rangi";
$lang['Rank_image_explain'] = "Mo¿esz tutaj okre¶liæ ma³y obrazek zwi±zany z dan± rang±";

$lang['Must_select_rank'] = "Musisz wybraæ rangê";
$lang['No_assigned_rank'] = "Nie okre¶lono rang specjalnych";

$lang['Rank_updated'] = "Ranga zosta³a zaktualizowana";
$lang['Rank_added'] = "Ranga zosta³a dodana";
$lang['Rank_removed'] = "Ranga zosta³a usuniêta";

$lang['Click_return_rankadmin'] = "Kliknij %sTutaj%s aby powróciæ do Administracji Rangami";


//
// Disallow Username Admin
//
$lang['Disallow_control'] = "Kontrola Zabronionych Nazw";
$lang['Disallow_explain'] = "Tutaj mo¿esz kontrolowaæ nazwy u¿ytkowników, których nie wolno u¿ywaæ. Zabronione nazwy mog± zawieraæ znak zamienny *. Pamiêtaj, ¿e nie mo¿esz zabroniæ nazwy, która ju¿ zosta³a zarejestrowana. Najpierw usuñ tego u¿ytkownika a potem dopisz tutaj nazwê.";

$lang['Delete_disallow'] = "Usuñ";
$lang['Delete_disallow_title'] = "Usuñ Zabronion± Nazwê";
$lang['Delete_disallow_explain'] = "Mo¿esz usun±æ zabronion± nazwê wybieraj±c j± z tej listy i klikaj±c Wy¶lij.";

$lang['Add_disallow'] = "Dodaj";
$lang['Add_disallow_title'] = "Dodaj Zabronion± Nazwê";
$lang['Add_disallow_explain'] = "Mo¿esz zabroniæ korzystania z jakiej¶ nazwy wykorzystuj±c znak * równowa¿ny dowolnemu ci±gowi znaków";

$lang['No_disallowed'] = "Brak Zabronionych Nazw";

$lang['Disallowed_deleted'] = "Zabroniona nazwa zosta³a usuniêta";
$lang['Disallow_successful'] = "Zabroniona nazwa zosta³a dodana";
$lang['Disallowed_already'] = "Nazwa, któr± wpisa³e¶, nie mo¿e byæ zakazana. Albo jest ju¿ na li¶cie albo istnieje ju¿ taki u¿ytkownik.";

$lang['Click_return_disallowadmin'] = "Kliknij %sTutaj%s aby powróciæ do Administracji Zabronionymi Nazwami";


//
// Styles Admin
//
$lang['Styles_admin'] = "Administracja Stylami";
$lang['Styles_explain'] = "Korzystaj±c z tego narzêdzia mo¿esz dodawaæ, usuwaæ i zarz±dzaæ stylami (oraz szablonami) dostêpnymi dla u¿ytkowników";
$lang['Styles_addnew_explain'] = "Poni¿sza lista zawiera wszystkie style, które s± dostêpne dla posiadanych przez ciebie szablonów. Elementy na tej li¶cie nie zosta³y jeszcze zainstalowane w bazie danych phpBB. Aby zainstalowaæ styl po prostu kliknij odno¶nik Instaluj obok wpisu";

$lang['Select_template'] = "Wybierz Szablon";

$lang['Style'] = "Styl";
$lang['Template'] = "Szablon";
$lang['Install'] = "Instaluj";
$lang['Download'] = "¦ci±gnij";

$lang['Edit_theme'] = "Edytuj Styl";
$lang['Edit_theme_explain'] = "W formularzu poni¿ej mo¿esz zmieniæ ustawienia dla wybranego stylu";

$lang['Create_theme'] = "Utwórz Styl";
$lang['Create_theme_explain'] = "U¿yj formularza poni¿ej aby utworzyæ nowy styl dla wybranego szablonu. Wpisuj±c kolory (do których mo¿esz u¿ywaæ jedynie warto¶ci szesnastkowych) nie dodawaj pocz±tkowego #, np. CCCCCC jest poprawne ale #CCCCCC ju¿ nie.";

$lang['Export_themes'] = "Eksportuj Styl";
$lang['Export_explain'] = "Z tego panelu mo¿esz eksportowaæ dane stylu dla wybranego szablonu. Wybierz styl z poni¿szej listy, a skrypt utworzy plik jego konfiguracji i spróbuje zapisaæ go w wybranym katalogu stylów. Je¿eli nie bêdzie mo¿liwe zapisanie pliku otrzymasz mo¿liwo¶æ ¶ci±gniêcia go. Aby plik zosta³ zapisany serwer musi mieæ uprawnienia zapisu w danym katalogu. Wiêcej informacji znajdziesz w podrêczniku phpBB 2.";

$lang['Theme_installed'] = "Wybrany styl zosta³ zainstalowany";
$lang['Style_removed'] = "Wybrany styl zosta³ usuniêty z bazy danych. Aby ca³kowicie usun±æ styl z systemu musisz usun±æ go rêcznie z katalogu szablonów.";
$lang['Theme_info_saved'] = "Informacje o stylu dla wybranego szablonu zosta³y zapisane. Powiniene¶ teraz przywróciæ uprawnienia dostêpu pliku theme_info.cfg (i je¶li to potrzebne tak¿e dla katalogu szablonów) na 'tylko do odczytu'.";
$lang['Theme_updated'] = "Wybrany styl zosta³ zaktualizowany. Powiniene¶ wyeksportowaæ nowe ustawienia.";
$lang['Theme_created'] = "Styl utworzony. Powiniene¶ teraz wyeksportowaæ go do pliku konfiguracyjnego aby go zabezpieczyæ lub u¿yæ gdzie indziej.";

$lang['Confirm_delete_style'] = "Czy na pewno chcesz usun±æ ten styl";

$lang['Download_theme_cfg'] = "Eksporter nie móg³ zapisaæ pliku z informacjami o stylu. Kliknij przycisk poni¿ej aby ¶ci±gn±æ ten plik przez przegl±darkê. Kiedy ju¿ go ¶ci±gniesz wy¶lij go rêcznie do katalogu z plikami szablonu. Mo¿esz wtedy spakowaæ pliki dla dystrybucji lub u¿ycia gdzie indziej.";
$lang['No_themes'] = "Wybrany szablon nie ma ¿adnych do³±czonych stylów. Aby utworzyæ nowy styl kliknij odno¶nik Utwórz Nowy na lewym panelu.";
$lang['No_template_dir'] = "Otwarcie katalogu szablonów by³o niemo¿liwe. Byæ mo¿e nie istnieje lub serwer nie ma do niego praw dostêpu.";
$lang['Cannot_remove_style'] = "Nie mo¿esz usun±æ wybranego stylu, poniewa¿ jest obecnie domy¶lnym dla forum. Zmieñ ustawienia domy¶lne i spróbuj ponownie.";
$lang['Style_exists'] = "Nazwa stylu, któr± wybra³e¶ ju¿ istnieje, wróæ i spróbuj z inn± nazw±.";

$lang['Click_return_styleadmin'] = "Kliknij %sTutaj%s aby powróciæ do Administracji Stylami";

$lang['Theme_settings'] = "Ustawienia Tematu";
$lang['Theme_element'] = "Element Tematu";
$lang['Simple_name'] = "Prosta Nazwa";
$lang['Value'] = "Warto¶æ";
$lang['Save_Settings'] = "Zapisz Ustawienia";

$lang['Stylesheet'] = "Arkusz CSS";
$lang['Background_image'] = "Obrazek T³a";
$lang['Background_color'] = "Kolor T³a";
$lang['Theme_name'] = "Nazwa Tematu";
$lang['Link_color'] = "Kolor Odno¶nika";
$lang['Text_color'] = "Kolor Tekstu";
$lang['VLink_color'] = "Kolor Odwiedzonego Odno¶nika";
$lang['ALink_color'] = "Kolor Aktywnego Odno¶nika";
$lang['HLink_color'] = "Kolor Odno¶nika pod Kursorem";
$lang['Tr_color1'] = "Kolor Rzêdu Tabeli 1";
$lang['Tr_color2'] = "Kolor Rzêdu Tabeli 2";
$lang['Tr_color3'] = "Kolor Rzêdu Tabeli 3";
$lang['Tr_class1'] = "Klasa Rzêdu Tabeli 1";
$lang['Tr_class2'] = "Klasa Rzêdu Tabeli 2";
$lang['Tr_class3'] = "Klasa Rzêdu Tabeli 3";
$lang['Th_color1'] = "Kolor Nag³ówka Tabeli 1";
$lang['Th_color2'] = "Kolor Nag³ówka Tabeli 2";
$lang['Th_color3'] = "Kolor Nag³ówka Tabeli 3";
$lang['Th_class1'] = "Klasa Nag³ówka Tabeli 1";
$lang['Th_class2'] = "Klasa Nag³ówka Tabeli 2";
$lang['Th_class3'] = "Klasa Nag³ówka Tabeli 3";
$lang['Td_color1'] = "Kolor Komórki Tabeli 1";
$lang['Td_color2'] = "Kolor Komórki Tabeli 2";
$lang['Td_color3'] = "Kolor Komórki Tabeli 3";
$lang['Td_class1'] = "Klasa Komórki Tabeli 1";
$lang['Td_class2'] = "Klasa Komórki Tabeli 2";
$lang['Td_class3'] = "Klasa Komórki Tabeli 3";
$lang['fontface1'] = "Czcionka 1";
$lang['fontface2'] = "Czcionka 2";
$lang['fontface3'] = "Czcionka 3";
$lang['fontsize1'] = "Rozmiar Czcionki 1";
$lang['fontsize2'] = "Rozmiar Czcionki 2";
$lang['fontsize3'] = "Rozmiar Czcionki 3";
$lang['fontcolor1'] = "Kolor Czcionki 1";
$lang['fontcolor2'] = "Kolor Czcionki 2";
$lang['fontcolor3'] = "Kolor Czcionki 3";
$lang['span_class1'] = "Klasa Span 1";
$lang['span_class2'] = "Klasa Span 2";
$lang['span_class3'] = "Klasa Span 3";
$lang['img_poll_size'] = "Rozmiar Obrazka G³osowania [px]";
$lang['img_pm_size'] = "Rozmiar Statustu Pr. Wiadom. [px]";


//
// Install Process
//
$lang['Welcome_install'] = "Witamy w Instalacji phpBB 2";
$lang['Initial_config'] = "Podstawowa Konfiguracja";
$lang['DB_config'] = "Konfiguracja Bazy Danych";
$lang['Admin_config'] = "Konfiguracja Admina";
$lang['continue_upgrade'] = "Kiedy ¶ci±gniesz plik konfiguracyjny na swój komputer mo¿esz klikn±æ przycisk \"Kontynuuj Aktualizacjê\" aby przej¶æ dalej. Zaczekaj z wys³aniem pliku konfiguracyjnego na serwer do zakoñczenia aktualizacji.";
$lang['upgrade_submit'] = "Kontynuuj Aktualizacjê";

$lang['Installer_Error'] = "Wyst±pi³ b³±d podczas instalacji";
$lang['Previous_Install'] = "Wykryto poprzedni± instalacjê";
$lang['Install_db_error'] = "Wyst±pi³ b³±d przy aktualizacji bazy danych";

$lang['Re_install'] = "Twoja poprzednia instalacja jest nadal aktywna.<br /><br />Je¿eli chcesz ponownie zainstalowaæ phpBB 2 kliknij przycisk Tak poni¿ej. Pamiêtaj, ¿e wykonanie tego usunie wszystkie istniej±ce dane bez ¿adnych kopii zapasowych! Konto administratora zostanie odtworzone z t± sam± nazw± i has³em co przed ponown± instalacj± ale bez innych ustawieñ.<br /><br />Zastanów siê przed wci¶niêciem Tak!";

$lang['Inst_Step_0'] = "Dziêkujemy, ¿e wybra³e¶ phpBB 2. Aby zainstalowaæ forym wpisz poni¿sze szczegó³y. Pamiêtaj, ¿e baza danych, w której chcesz zainstalowaæ forum powinna wcze¶niej istnieæ. Je¿eli instalujesz w bazie danych u¿ywaj±cej ODBC, np. MS Access powiniene¶ najpierw utworzyæ odpowiednie DSN.";

$lang['Start_Install'] = "Zacznij Instalacjê";
$lang['Finish_Install'] = "Zakoñcz Instalacjê";

$lang['Default_lang'] = "Domy¶lny Jêzyk Forum";
$lang['DB_Host'] = "Server Bazy Danych / DSN";
$lang['DB_Name'] = "Nazwa Bazy Danych";
$lang['DB_Username'] = "U¿ytkownik Bazy Danych";
$lang['DB_Password'] = "Has³o Bazy Danych";
$lang['Database'] = "Baza Danych";
$lang['Install_lang'] = "Wybierz Jêzyk Instalacji";
$lang['dbms'] = "Typ Bazy Danych";
$lang['Table_Prefix'] = "Prefiks dla tabel w bazie danych";
$lang['Admin_Username'] = "Nazwa Administratora";
$lang['Admin_Password'] = "Has³o Administratora";
$lang['Admin_Password_confirm'] = "Has³o Administratora [ Potwierd¼ ]";

$lang['Inst_Step_2'] = "Konto administratora zosta³o utworzone. W tej chwili podstawowa instalacja jest zakoñczona. Zostaniesz przeniesiony do strony, która pozwoli ci zmieniæ wszelkie ustawienia forum. Pamiêtaj o sprawdzeniu Konfiguracji G³ównej i zmianie tych opcji, które tego wymagaj±. Dziêkujemy, ¿e wybra³e¶ phpBB 2.";

$lang['Unwriteable_config'] = "Twój plik konfigiracyjny nie mo¿e zostaæ zapisany. Jego kopia zostanie wys³ana do ciebie je¶li wci¶niesz poni¿szy przycisk. Powiniene¶ wys³aæ j± rêcznie do katalogu z phpBB 2. Kiedy to zrobisz zaloguj siê do nowego forum, u¿ywaj±c twoich danych podanych wcze¶niej, oraz odwiedziæ centrum administracji forum (do którego odno¶nik pojawi siê na dole ka¿dej strony forum, kiedy siê zalogujesz) aby zmieniæ opcje. Dziêkujemy, ¿e wybra³e¶ phpBB 2.";
$lang['Download_config'] = "¦ci±gnij Plik Konfiguracyjny";

$lang['ftp_choose'] = "Wybierz Metodê ¦ci±gania";
$lang['ftp_option'] = "<br />Je¿eli opcje FTP s± dostêpne w tej wersji PHP mo¿esz mieæ tak¿e mo¿liwo¶æ automatycznego wys³ania pliku w odpowiednie miejsce przez FTP.";
$lang['ftp_instructs'] = "Wybra³e¶ opcjê automatycznego wys³ania pliku do katalogu zawieraj±cego phpBB 2. Poni¿ej wpisz informacje potrzebne do wykonania tego polecenia. Pamiêtaj, ¿e ¶cie¿ka do phpBB 2 powinna byæ dok³adnie taka jak± u¿ywasz przy po³±czeniach z FTP przez inne programy.";
$lang['ftp_info'] = "Wpisz informacjê o twoim FTP";
$lang['Attempt_ftp'] = "Spróbuj wys³aæ plik przez ftp automatycznie";
$lang['Send_file'] = "Wy¶lij plik do mnie a ja umieszczê go rêcznie na serwerze";
$lang['ftp_path'] = "¦cie¿ka FTP do phpBB 2";
$lang['ftp_username'] = "U¿ytkownik FTP";
$lang['ftp_password'] = "Has³o FTP";
$lang['Transfer_config'] = "Rozpocznij Transfer";
$lang['NoFTP_config'] = "Próba wys³ania pliku drog± ftp na swoje miejsce nie powiod³a siê. ¦ci±gnij plik konfiguracyjny i wy¶lij go na miejsce samodzielnie.";

$lang['Install'] = "Instalacja";
$lang['Upgrade'] = "Aktualizacja";


$lang['Install_Method'] = "Wybierz metodê instalacji";

$lang['Install_No_Ext'] = "Konfiguracja php na serwerze nie obs³uguje wybranej bazy danych";

$lang['Install_No_PCRE'] = "phpBB2 wymaga kompatybilnego z Perlem Modu³u Wyra¿eñ Regularnych, którego twoja konfiguracja php najwyraŸniej nie obs³uguje!";

//
// That's all Folks!
// -------------------------------------------------

?>