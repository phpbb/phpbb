<?php
/***************************************************************************
 *                            lang_admin.php [Czech]
 *                            ----------------------
 *     characterset         : Windows-1250
 *     phpBB version        : 2.0.2
 *     copyright            : (c) 2002 The phpBB CZ Group
 *     translation          : azu@atmplus.cz
 *                          : emilio@emilio.cz
 *     www                  : http://phpbb.atmplus.cz
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
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = "Obecné";
$lang['Users'] = "Uivatelé";
$lang['Groups'] = "Skupiny";
$lang['Forums'] = "Fórum";
$lang['Styles'] = "Styly";

$lang['Configuration'] = "Konfigurace";
$lang['Permissions'] = "Svolení";
$lang['Manage'] = "Administrace";
$lang['Disallow'] = "Nepovolená jména";
$lang['Prune'] = "Proèištìní";
$lang['Mass_Email'] = "Hromadnı e-mail";
$lang['Ranks'] = "Hodnocení";
$lang['Smilies'] = "Emotikony (smajlíky)";
$lang['Ban_Management'] = "Zakázání vstupu";
$lang['Word_Censor'] = "Cenzura slov";
$lang['Export'] = "Exportovat";
$lang['Create_new'] = "Vytvoøit";
$lang['Add_new'] = "Pøidat";
$lang['Backup_DB'] = "Zálohovat databázi";
$lang['Restore_DB'] = "Obnovit databázi";


//
// Index
//
$lang['Admin'] = "Administrace";
$lang['Not_admin'] = "Nemáte oprávnìní k administraci tohoto fóra";
$lang['Welcome_phpBB'] = "Vítejte na phpBB";
$lang['Admin_intro'] = "Dìkujeme e jste si zvolil(a) phpBB jako øešení pro vaše fórum. Tato stránka slouí k rychlému zobrazení rùznıch statistik vašeho fóra. Pokud se budete chtít vrátit zpìt na tuto stránku kliknìte na odkaz <u>Obsah administrace</u> v levém panelu. Pro návrat na obsah vašeho fóra, kliknìte na logo fóra umístìném té na levém panelu. Ostatní odkazy na levém panelu této stránky vás dovedou k jednotlivım polokám moného nastavení fóra dle vašich poadavkù, kadá stránka obsahuje návod jak pouít danou funkci.";
$lang['Main_index'] = "Obsah fóra";
$lang['Forum_stats'] = "Statistiky fóra";
$lang['Admin_Index'] = "Obsah administrace";
$lang['Preview_forum'] = "Náhled na fórum";

$lang['Click_return_admin_index'] = "Kliknìte %szde%s pro návrat na obsah administrace";

$lang['Statistic'] = "Statistiky";
$lang['Value'] = "Hodnota";
$lang['Number_posts'] = "Poèet pøíspìvkù";
$lang['Posts_per_day'] = "Pøíspìvkù za den";
$lang['Number_topics'] = "Poèet témat";
$lang['Topics_per_day'] = "Témat za den";
$lang['Number_users'] = "Poèet uivatelù";
$lang['Users_per_day'] = "Uivatelù za den";
$lang['Board_started'] = "Fórum spuštìno";
$lang['Avatar_dir_size'] = "Velikost adresáøe s obrázky postavièek";
$lang['Database_size'] = "Velikost databáze";
$lang['Gzip_compression'] ="GZIP komprese";
$lang['Not_available'] = "Nedostupné";

$lang['ON'] = "Ano"; // This is for GZip compression
$lang['OFF'] = "Ne";


//
// DB Utils
//
$lang['Database_Utilities'] = "Databázové nástroje";

$lang['Restore'] = "Obnovení";
$lang['Backup'] = "Zálohování";
$lang['Restore_explain'] = "Tato funkce je urèena k úplnému obnovení všech databázovıch tabulek phpBB fóra z uloenıch souborù. Jestlie to váš server podporuje, mùete pouít GZIP komprimované textové soubory a ty pak budou automaticky dekomprimovány. <b>POZOR</b> Tímto budou pøepsána veškerá existující data. Obnovení potøebuje delší èas na zpracování, proto prosím neodcházejte z této stránky dokud nebude vše dokonèeno.";
$lang['Backup_explain'] = "Tato funkce je urèena ke kompletní záloze dat phpBB fóra. Jestlie pouíváte nìkteré další tabulky spoleènì s phpBB databází, doporuèujeme je té zazálohovat, zadejte proto prosím názvy tabulek a oddìlte je oddìlovaèem (,). Jestlie to váš server podporuje, mùete pouít GZIP kompresy dat pro zmenšení velikosti souborù pøed jejich staením do vašeho poèítaèe.";

$lang['Backup_options'] = "Nastavení zálohy";
$lang['Start_backup'] = "Spustit zálohování";
$lang['Full_backup'] = "Kompletní záloha";
$lang['Structure_backup'] = "Zálohovat pouze strukturu";
$lang['Data_backup'] = "Zálohovat pouze data";
$lang['Additional_tables'] = "Další tabulky";
$lang['Gzip_compress'] = "GZIP komprese souborù";
$lang['Select_file'] = "Zvolit soubor";
$lang['Start_Restore'] = "Spustit obnovení";

$lang['Restore_success'] = "Databáze byla úspìšnì obnovena.<br><br>Vaše fórum by nyní mìlo bıt ve stavu pøed provedením zálohy.";
$lang['Backup_download'] = "Prosím vyèkejte zaèátku stahování";
$lang['Backups_not_supported'] = "Lituji, ale zálohování databáze není v souèasné dobì ve vešem databázovém systému podporováno";

$lang['Restore_Error_uploading'] = "Vyskytla se chyba pøi nahrávání souboru zálohy";
$lang['Restore_Error_filename'] = "Vyskytl se problém s názvem souboru, zkuste jinı";
$lang['Restore_Error_decompress'] = "Nebylo moné dekomprimovat GZIP soubor, pouijte textovı soubor";
$lang['Restore_Error_no_file'] = "Nebyl nahrán ádnı soubor";


//
// Auth pages
//
$lang['Select_a_User'] = "Zvolit uivatele";
$lang['Select_a_Group'] = "Zvolit skupinu";
$lang['Select_a_Forum'] = "Zvolit fórum";
$lang['Auth_Control_User'] = "Uivatelská oprávnìní";
$lang['Auth_Control_Group'] = "Oprávnìní skupiny";
$lang['Auth_Control_Forum'] = "Oprávnìní fóra";
$lang['Look_up_User'] = "Zvolit uivatele";
$lang['Look_up_Group'] = "Zvolit skupinu";
$lang['Look_up_Forum'] = "Zvolit fórum";

$lang['Group_auth_explain'] = "Zde mùete mìnit oprávnìní a pøiøadit moderování skupinì uivatelù. Nezapomeòte pøed zmìnou oprávnìní aby skupina oprávnìnıch mìla stále povolen vstup uivatele na fórum.";
$lang['User_auth_explain'] = "Zde mùete mìnit oprávnìní a pøiøadit moderování zvolenému uivateli. Nezapomeòte pøed zmìnou oprávnìní aby skupina oprávnìnıch mìla stále povolen vstup uivatele na fórum.";
$lang['Forum_auth_explain'] = "Zde mùete nastavit úroveò zabezpeèení fóra. Mùete zvolit základní nebo rozšíøenı mód pro tuto èinnost. Rozšíøenı mód nabízí mnohem vìtší škálu moností pro nastavení fóra. Pamatujte, e pøed zmìnou zabezpeèení fóra by se na fóru nemìli provádìt jiné operace.";

$lang['Simple_mode'] = "Základní reim";
$lang['Advanced_mode'] = "Rozšíøenı reim";
$lang['Moderator_status'] = "Moderátor";

$lang['Allowed_Access'] = "Pøístup povolen";
$lang['Disallowed_Access'] = "Pøístup zamítnout";
$lang['Is_Moderator'] = "Je moderátorem";
$lang['Not_Moderator'] = "Není moderátorem";

$lang['Conflict_warning'] = "Varování, autorizaèní konflikt";
$lang['Conflict_access_userauth'] = "Tento uivatel má poadovaná pøístupová práva k tomuto fóru pøes èlenství ve skupinì. Mùete povolit oprávnìní skupinì nebo odstranit tohoto uivatele ze skupiny pro úplné zabránìní poadovanıch pøístupovıch práv.";
$lang['Conflict_mod_userauth'] = "Tento moderátorská má poadovaná práva pro toto fórum pøes èlenství ve skupinì. Mùete povolit oprávnìní skupinì nebo odstranit tohoto uivatele ze skupiny pro úplné zabránìní poadovanıch pøístupovıch práv.";
$lang['Conflict_access_groupauth'] = "Následující uivatel (uivatelé) mají poadovaná práva pro toto fórum pøes jejich nastavené oprávnìní. Mùete povolit oprávnìní skupinì nebo odstranit tohoto uivatele ze skupiny pro úplné zabránìní poadovanıch pøístupovıch práv.";
$lang['Conflict_mod_groupauth'] = "Následující uivatel (uivatelé) mají poadovaná práva pro toto fórum pøes jejich nastavené oprávnìní. Mùete povolit oprávnìní skupinì nebo odstranit tohoto uivatele ze skupiny pro úplné zabránìní poadovanıch pøístupovıch práv.";

$lang['Public'] = "Veøejnı";
$lang['Private'] = "Soukromı";
$lang['Registered'] = "Registrovanı";
$lang['Administrators'] = "Administrátor";
$lang['Hidden'] = "skrytı";

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = "Všichni";
$lang['Forum_REG'] = "Registrovaní";
$lang['Forum_PRIVATE'] = "Soukromı";
$lang['Forum_MOD'] = "Moderátor";
$lang['Forum_ADMIN'] = "Administrátor";

$lang['View'] = "Zobrazit";
$lang['Read'] = "Èíst";
$lang['Post'] = "Odeslat";
$lang['Reply'] = "Odpovìdìt";
$lang['Edit'] = "Upravit";
$lang['Delete'] = "Odstranit";
$lang['Sticky'] = "Dùleité";
$lang['Announce'] = "Oznámení";
$lang['Vote'] = "Hlasování";
$lang['Pollcreate'] = "Hlas pøidán";

$lang['Permissions'] = "Oprávnìní";
$lang['Simple_Permission'] = "Základní oprávnìní";

$lang['User_Level'] = "Uivatelská úroveò";
$lang['Auth_User'] = "Uivatel";
$lang['Auth_Admin'] = "Administrátor";
$lang['Group_memberships'] = "Èlenství uivatelské skupiny";
$lang['Usergroup_members'] = "Tato skupina má následující èleny";

$lang['Forum_auth_updated'] = "Oprávnìní fóra aktualizováno";
$lang['User_auth_updated'] = "Uivatelské oprávnìní aktualizováno";
$lang['Group_auth_updated'] = "Oprávnìní skupiny aktualizováno";

$lang['Auth_updated'] = "Oprávnìní bylo aktualizováno";
$lang['Click_return_userauth'] = "Kliknìte %szde%s pro návrat do uivatelského oprávnìní";
$lang['Click_return_groupauth'] = "Kliknìte %szde%s pro návrat do oprávnìní skupiny";
$lang['Click_return_forumauth'] = "Kliknìte %szde%s pro návrat na oprávnìní fóra";


//
// Banning
//
$lang['Ban_control'] = "Zakázání vstupu";
$lang['Ban_explain'] = "Zde mùete zakázat vstup zvolenım uivatelùm. Mùete zakázat konkrétního uivatele nebo rozsah IP adres nebo jméno poèítaèe. Touto metodou ochráníte vaše fórum proti vstupu neádoucích uivatelù na stránky fóra. Proti registraci uivatele pod jinım jménem mùete zakázat jeho e-mailovou adresu.";
$lang['Ban_explain_warn'] = "Dávejte si prosím pozor pøi zadávání rozsahu IP adres zda jsou všechny adresy od zaèátku do konce v seznamu. Doporuèuje se aby byl seznam uloenıch IP adres v databázi co nejmenší, proto se pokuste radìji pouít znaku \"*\" pro specifikaci namísto zadávání rozsahu IP adres. Pokud je pøesto nutno zadat rozsah IP adres, pokuste se aby byl seznam co nejkratší.";

$lang['Select_username'] = "Zvolte uivatele";
$lang['Select_ip'] = "Zvolte IP";
$lang['Select_email'] = "Zvolte e-mailovou adresu";

$lang['Ban_username'] = "Zakázání vstupu zadanım uivatelùm";
$lang['Ban_username_explain'] = "Chcete-li pøidat do zakázanıch nìkterého uivatele, zadejte zde jeho jméno, pøípadnì jej vyhledejte ze seznamu registrovanıch uivatelù.";

$lang['Ban_IP'] = "Zakázání vstupu dle IP adresy nebo jména poèítaèe";
$lang['IP_hostname'] = "IP adresa nebo jméno poèítaèe";
$lang['Ban_IP_explain'] = "Zde mùete zadat název poèítaèe, èi IP adresy, kterım chcete zakázat vstup. Jednotlivé adresy èi jména od sebe oddìlte oddìlovèem. Chcete-li zadat rozsah IP adres, oddìlte je od sebe znakem \"-\". Mùete pouít i znak \"*\" pro nahrazení èásti øetìzce.";

$lang['Ban_email'] = "Zakázání vstupu dle e-mailovıch adres";
$lang['Ban_email_explain'] = "Zde mùete zadat seznam emailovıch adres, kterım chcete zamezit vstup, jednotlivé adresy od sebe oddìlte oddìlovaèem. Mùete pouít i znak \"*\" pro nahrazení èásti adresy, napø. *@hotmail.com";

$lang['Unban_username'] = "Vyjmutí uivatelù ze seznamu zakázanıch";
$lang['Unban_username_explain'] = "Jestlie chcete vyjmout nìkteré uivatele z tohoto seznamu, oznaète je pomocí myši èi klávesnice a potvrïte odesláním.";

$lang['Unban_IP'] = "Vyjmutí IP adres ze seznamu zakázanıch";
$lang['Unban_IP_explain'] = "Jestlie chcete vyjmout nìkteré IP adresz z tohoto seznamu, oznaète je pomocí myši èi klávesnice a potvrïte odesláním.";

$lang['Unban_email'] = "Vyjmutí e-mailovıch adres ze seznamu zakázanıch";
$lang['Unban_email_explain'] = "Jestlie chcete vyjmout nìkteré e-mailové adresy z tohoto seznamu, oznaète je pomocí myši èi klávesnice a potvrïte odesláním.";

$lang['No_banned_users'] = "ádní zakázaní uivatelé";
$lang['No_banned_ip'] = "ádné zakázané IP adresy";
$lang['No_banned_email'] = "ádné zakázané e-mail adresy";

$lang['Ban_update_sucessful'] = "Seznam zakázanıch uivatelù byl úspìšnì aktualizován";
$lang['Click_return_banadmin'] = "Kliknìte %szde%s pro návrat do ovládacího panelu zakázaní vstupu";


//
// Configuration
//
$lang['General_Config'] = "Konfigurace";
$lang['Config_explain'] = "Níe uvedené poloky vám umoní nastavit fórum dle vašich poadavkù. Pro nastavení uivatelù a fóra pouívejte odkazy v levé èásti stránky.";

$lang['Click_return_config'] = "Kliknìte %szde%s pro návrat do konfigurace";

$lang['General_settings'] = "Obecné nastavení fóra";
$lang['Server_name'] = "Jméno domény";
$lang['Server_name_explain'] = "Doménové jméno tohoto fóra bìí na";
$lang['Script_path'] = "Cesta ke skriptùm";
$lang['Script_path_explain'] = "Cesta ke skriptùm phpBB, relativní umístìní v doménì";
$lang['Server_port'] = "Port serveru";
$lang['Server_port_explain'] = "Port na kterém bìí váš server, standardnì 80";
$lang['Site_name'] = "Jméno fóra";
$lang['Site_desc'] = "Popis fóra";
$lang['Board_disable'] = "Zablokovat fórum";
$lang['Board_disable_explain'] = "Tímto znepøístupníte fórum pro uivatele. Neodhlašujte se pokud jste znepøístupnil fórum, jinak se nebudete moci nalogovat zpìt!";

$lang['Acct_activation'] = "Zpùsob aktivace úètu";
$lang['Acc_None'] = "ádnı"; // These three entries are the type of activation
$lang['Acc_User'] = "Uivatelem";
$lang['Acc_Admin'] = "Administrátorem";

$lang['Abilities_settings'] = "Základní nastavení pro uivatele a fórum";
$lang['Max_poll_options'] = "Maximální hodnota pøi hlasování";
$lang['Flood_Interval'] = "Ochrannı interval";
$lang['Flood_Interval_explain'] = "Poèet vteøin, po které musí uivatel poèkat mezi pøíspìvky";
$lang['Board_email_form'] = "E-mail uivatele pøes toto fórum";
$lang['Board_email_form_explain'] = "Umoòuje zasílání e-mailù jinım uivatelùm pøes toto fórum";
$lang['Topics_per_page'] = "Témat na stránku";
$lang['Posts_per_page'] = "Pøíspìvkù na stránku";
$lang['Hot_threshold'] = "Pøíspìvky do pøípustné hranice";
$lang['Default_style'] = "Vıchozí vzhled";
$lang['Override_style'] = "Nahradit uivatelem zvolenı vzhled";
$lang['Override_style_explain'] = "Pouije vıchozí vzhled namísto zvoleného uivatelem";
$lang['Default_language'] = "Vıchozí jazyk";
$lang['Date_format'] = "Formát datumu";
$lang['System_timezone'] = "Èasové pásmo fóra";
$lang['Enable_gzip'] = "Povolit GZIP kompresi";
$lang['Enable_prune'] = "Povolit proèištìní fóra";
$lang['Allow_HTML'] = "Povolit HTML";
$lang['Allow_BBCode'] = "Povolit znaèky";
$lang['Allowed_tags'] = "Povolené HTML znaèky";
$lang['Allowed_tags_explain'] = "oddìlte znaèky oddìlovaèem (,)";
$lang['Allow_smilies'] = "Povolit emotikony (smajlíky)";
$lang['Smilies_path'] = "Cesta k umístìní smajlíkù";
$lang['Smilies_path_explain'] = "Cesta mimo váš phpBB koøenovı adresáø, pø.: images/smilies";
$lang['Allow_sig'] = "Povolit podpisy";
$lang['Max_sig_length'] = "Maximální délka podpisu";
$lang['Max_sig_length_explain'] = "Maximální poèet znakù uivatelova podpisu";
$lang['Allow_name_change'] = "Povolit zmìnu uivatelského jména";

$lang['Avatar_settings'] = "Nastavení obrázkù postavièek";
$lang['Allow_local'] = "Povolit galerii postavièek";
$lang['Allow_remote'] = "Povolit vzdálené obrázky postavièek";
$lang['Allow_remote_explain'] = "Obrázek postavièky propojen na jinı WWW server";
$lang['Allow_upload'] = "Povolit pøihrávání obrázkù postavièek";
$lang['Max_filesize'] = "Maximální velikost souboru s obrázkem postavièky";
$lang['Max_filesize_explain'] = "Pro pøihrávání souborù obrázkù postavièek";
$lang['Max_avatar_size'] = "Maximální rozmìry obrázku postavièky";
$lang['Max_avatar_size_explain'] = "(vıška x šíøka v bodech)";
$lang['Avatar_storage_path'] = "Cesta k ukládání obrázkù postavièek";
$lang['Avatar_storage_path_explain'] = "Cesta mimo váš phpBB koøenovı adresáø, pø.: images/avatars";
$lang['Avatar_gallery_path'] = "Cesta ke galerii obrázkù postavièek";
$lang['Avatar_gallery_path_explain'] = "Cesta mimo váš phpBB koøenovı adresáø pro pøednastavené obrázky, pø.: images/avatars/gallery";

$lang['COPPA_settings'] = "COPPA nastavení";
$lang['COPPA_fax'] = "COPPA faxové èíslo";
$lang['COPPA_mail'] = "COPPA mailové adresy";
$lang['COPPA_mail_explain'] = "Toto je seznam adres na které budou rodièe zasílat COPPA registraèní formuláø";

$lang['Email_settings'] = "Nastavení e-mailù";
$lang['Admin_email'] = "Administrátorova e-mailová adresa:";
$lang['Email_sig'] = "Podpis e-mailu";
$lang['Email_sig_explain'] = "Tento text bude pøipojen ke všem e-mailùm odeslanım z tohoto fóra";

$lang['Use_SMTP'] = "Pouít SMTP Server pro e-mail";
$lang['Use_SMTP_explain'] = "Zvolte Ano jestlie chcete odesílat e-maily pøes jméno serveru namísto lokální mail funkce.";
$lang['SMTP_server'] = "Adresa SMTP serveru";
$lang['SMTP_username'] = "SMTP úèet";
$lang['SMTP_username_explain'] = "Zadejte pouze v pøípadì, e to váš SMTP server vyaduje";
$lang['SMTP_password'] = "SMTP heslo";
$lang['SMTP_password_explain'] = "Zadejte pouze v pøípadì, e to váš SMTP server vyaduje";

$lang['Disable_privmsg'] = "Soukromé zprávy";
$lang['Inbox_limits'] = "Max. poèet pøíspìvkù ve sloce doruèené";
$lang['Sentbox_limits'] = "Max. poèet pøíspìvkù ve sloce odeslané";
$lang['Savebox_limits'] = "Max. poèet pøíspìvkù ve sloce uloené";

$lang['Cookie_settings'] = "Nastavení Cookie";
$lang['Cookie_settings_explain'] = "Toto detailní nastavení definuje jak budou zasílány cookies ve vašem prohlíeèi. Doporuèujeme ponechat vıchozí hodnoty nastavení cookie ale je mono zmìnit hodnoty dle vašich poadavkù, nastavení se projeví a po pøíštím pøihlášení.";
$lang['Cookie_domain'] = "Doména Cookie";
$lang['Cookie_name'] = "Jméno Cookie";
$lang['Cookie_path'] = "Cesta k Cookie";
$lang['Cookie_secure'] = "Zabezpeèení Cookie";
$lang['Cookie_secure_explain'] = "Jestlie váš server bìí pøes SSL nastavte na povoleno, jeslie ne tak nastavte zakázáno";
$lang['Session_length'] = "Délka platnosti Session [ vteøin ]";


//
// Forum Management
//
$lang['Forum_admin'] = "Administratce fóra";
$lang['Forum_admin_explain'] = "Z tohoto panelu mùete pøidávat, odstranit, upravovat, tøídit a synchronizovat kategorie a fóra";
$lang['Edit_forum'] = "Úprava fóra";
$lang['Create_forum'] = "Vytvoøit nové fórum";
$lang['Create_category'] = "vytvoøit novou kategorii";
$lang['Remove'] = "Vyjmout";
$lang['Action'] = "Akce";
$lang['Update_order'] = "Aktualizovat instrukce";
$lang['Config_updated'] = "Zmìna konfigurace fóra byla úspìšnì provedena";
$lang['Edit'] = "Upravit";
$lang['Delete'] = "Odstranit";
$lang['Move_up'] = "pøesunout nahoru";
$lang['Move_down'] = "pøesunout dolu";
$lang['Resync'] = "Synchronizovat";
$lang['No_mode'] = "Mód nebyl pøiøazen";
$lang['Forum_edit_delete_explain'] = "Níe uvedenı formuláø vám umoní úpravy obecného nastavení fóra. Pro nastavení uivatelù a fóra pouívejte odkazy v levé èásti stránky.";

$lang['Move_contents'] = "Pøesunout veškerı obsah";
$lang['Forum_delete'] = "Odstranit fórum";
$lang['Forum_delete_explain'] = "Níe uvedenı formuláø vám umoní odstranit fóra èi kategorie a rozhodnout kam chcete dát všechna témata která jsou v nìm obsaeny.";

$lang['Status_locked'] = 'Zamknuto';
$lang['Status_unlocked'] = 'Odemknuto';
$lang['Forum_settings'] = "Obecné nastavení fóra";
$lang['Forum_name'] = "Jméno fóra";
$lang['Forum_desc'] = "Popis";
$lang['Forum_status'] = "Stav fóra";
$lang['Forum_pruning'] = "Automatické proèištìní";

$lang['prune_freq'] = "Kontrolovat starší témata kadıch";
$lang['prune_days'] = "Odstranit témata která jsou starší";
$lang['Set_prune_data'] = "Chcete nastavit povolení automatického proèištìní tohoto fóra, ale nemáte nastavenu èetnost nebo poèet dní. Vrate se prosím zpìt a zadejte poadované hodnoty.";

$lang['Move_and_Delete'] = "Pøesunout a odstranit";

$lang['Delete_all_posts'] = "Odstranit všechny pøíspìvky";
$lang['Nowhere_to_move'] = "Sem to nelze pøesunout";

$lang['Edit_Category'] = "Úprava kategorie";
$lang['Edit_Category_explain'] = "Pouijte rento formuláø pro úpravu jména kategorie.";

$lang['Forums_updated'] = "Fórum a informace o skupinì byly aktualizovány";

$lang['Must_delete_forums'] = "Musíte odstranit všechna fóra ještì pøed odstranìním této kategorie";

$lang['Click_return_forumadmin'] = "Kliknìte %szde%s pro návrat do administrace fóra";


//
// Smiley Management
//
$lang['smiley_title'] = "Úprava emotikon (smajlíkù)";
$lang['smile_desc'] = "Na této stránce mùete pøidávat, odebírat a upravovat emotikony (smajlíky), které mohou Vaši uivatelé pouívat v pøíspìvcích a soukromıch zprávách.";

$lang['smiley_config'] = "Nastavení smajlíku";
$lang['smiley_code'] = "Kód smajlíku";
$lang['smiley_url'] = "Grafickı soubor smajlíku";
$lang['smiley_emot'] = "Vıraz smajlíku";
$lang['smile_add'] = "Pøidej novı smajlík";
$lang['Smile'] = "Smajlík";
$lang['Emotion'] = "Vıraz";

$lang['Select_pak'] = "Vyberte (.pak) soubor";
$lang['replace_existing'] = "Nahradit dosavadní smajlík";
$lang['keep_existing'] = "Keep Existing Smiley";
$lang['smiley_import_inst'] = "Rozbalte kolekci smajlíkù a nahrajte všechny soubory do pøíslušného adresáøe smajlíkù pro instalaci.  Pak vyberte správnou informaci v tomto formuláøi k importování kolekce smajlíkù.";
$lang['smiley_import'] = "Import kolekce smajlíkù";
$lang['choose_smile_pak'] = "Vyberte soubor smajlíkù (.pak)";
$lang['import'] = "Importuj smajlíky";
$lang['smile_conflicts'] = "Co udìlat v pøípadì konfliktù ?";
$lang['del_existing_smileys'] = "Pøed importováním smate dosavadní smajlíky";
$lang['import_smile_pack'] = "Importovat kolekci smajlíkù";
$lang['export_smile_pack'] = "Vytvoøit kolekci smajlíkù";
$lang['export_smiles'] = "Pokud chcete vytvoøit kolekci smajlíkù z dosud uívanıch smajlíkù, kliknìte %szde%s a stáhnìte soubor smiles.pak. Pojmenujte tento pøíslušnı soubor, ale nezapomeòte zachovat pøíponu (.pak). Pak vytvoøte komprimovanı soubor všech vašich smajlíkù i s vaším souborem nastavení .pak";

$lang['smiley_add_success'] = "Smajlík byl úspìšnì pøidán !";
$lang['smiley_edit_success'] = "Smajlík byl úspìšnì zmìnìn !";
$lang['smiley_import_success'] = "Soubor smajlíkù byl úspìšnì importován !";
$lang['smiley_del_success'] = "Smajlík byl úspìšnì odstranìn";
$lang['Click_return_smileadmin'] = "Kliknìte %szde%s k návratu na administraci smajlíkù";


//
// User Management
//
$lang['User_admin'] = "Uivatelská administrace";
$lang['User_admin_explain'] = "Zde mùete zmìnit informaci o uivateli a nìkterá specifická nastavení. K úpravì práv pouijte uivatele a skupinovı povolovací systém.";

$lang['Look_up_user'] = "Zvolit uivatele";

$lang['Admin_user_fail'] = "Nelze zmìnit nastavení uivatele.";
$lang['Admin_user_updated'] = "Zmìna nastavení uivatele probìhla úspìšnì.";
$lang['Click_return_useradmin'] = "Kliknìte %szde%s k návratu do Uivatelské administrace";

$lang['User_delete'] = "Odstranit tohoto uivatele";
$lang['User_delete_explain'] = "Zde smaete tohoto uivatele. Nelze vzít zpìt !";
$lang['User_deleted'] = "Uivatel úspìšnì odstranìn.";

$lang['User_status'] = "Uivatel je aktivní";
$lang['User_allowpm'] = "Mùe posílat soukromé zprávy";
$lang['User_allowavatar'] = "Mùe zobrazovat postavièky";

$lang['Admin_avatar_explain'] = "Zde mùete vidìt a odstranit souèasnou uivatelovu postavièku.";

$lang['User_special'] = "Zvláštní oblasti administrátorskıch nastavení";
$lang['User_special_explain'] = "Tyto oblasti nemùou bıt upravovány uivateli. Zde mùete nastavit jejich zaøazení a další oblasti, které nejsou uivatelùm pøiøazeny.";


//
// Group Management
//
$lang['Group_administration'] = "Skupinová administrace";
$lang['Group_admin_explain'] = "Z tohoto panelu mùete spravovat všechny uivatelské skupiny, mùete odstranit, vytvoøit a mìnit souèasné skupiny. Mùete vybírat moderátory, zamknout otevøené/uzavøené skupiny a nastavit jméno a popis skupiny";
$lang['Error_updating_groups'] = "Pøi nahrávání skupin došlo k chybì";
$lang['Updated_group'] = "Skupina byla úspìšnì nahrána";
$lang['Added_new_group'] = "Nová skupina byla úspìšnì vytvoøena";
$lang['Deleted_group'] = "Skupina byla úspìšnì odstranìna";
$lang['New_group'] = "Vytvoøit novou skupinu";
$lang['Edit_group'] = "Zmìnit skupinu";
$lang['group_name'] = "Jméno skupiny";
$lang['group_description'] = "Popis skupiny";
$lang['group_moderator'] = "Moderátor skupiny";
$lang['group_status'] = "Nastavení skupiny";
$lang['group_open'] = "Otevøená skupina";
$lang['group_closed'] = "Uzavøená skupina";
$lang['group_hidden'] = "Skrytá skupina";
$lang['group_delete'] = "odstranit skupinu";
$lang['group_delete_check'] = "odstranit tuto skupinu";
$lang['submit_group_changes'] = "Odeslat zmìny";
$lang['reset_group_changes'] = "Obnovit zmìny";
$lang['No_group_name'] = "Musíte zadat jméno pro tuto skupinu";
$lang['No_group_moderator'] = "Musíte zadat moderátora pro tuto skupinu";
$lang['No_group_mode'] = "Musíte zadat nastavení této skupiny, otevøená nebo uzavøená.";
$lang['No_group_action'] = "Nebyla vybrána ádná akce";
$lang['delete_group_moderator'] = "odstranit moderátora pùvodní skupiny ?";
$lang['delete_moderator_explain'] = "Pokud mìníte moderátora skupiny, zatrhnìte toto políèko k odstranìní pùvodního moderátora z této skupiny.  V opaèném pøípadì se tento uivatel stane bìnım èlenem této skupiny.";
$lang['Click_return_groupsadmin'] = "Kliknìte %sude%s k návratu do Skupinové administrace.";
$lang['Select_group'] = "Vyberte skupinu";
$lang['Look_up_group'] = "Vyhledejte skupinu";


//
// Prune Administration
//
$lang['Forum_Prune'] = "Proèištìní fóra";
$lang['Forum_Prune_explain'] = "Tato funkce odstraní všechna témata, ke kterım nebyly pøidány pøíspìvky za Vámi zadanı poèet dní. Pokud nezadáte poèet dní, pak budou odstranìna všechna témata. nebudou odstranìna témata, v nich bìí hlasování a stejnì tak se neodstraní oznámení. Tato témata budete muset odstranit ruènì.";
$lang['Do_Prune'] = "Proèistit";
$lang['All_Forums'] = "Všechna fóra";
$lang['Prune_topics_not_posted'] = "Proèistit témata bez odpovìdi starší";
$lang['Topics_pruned'] = "Témata proèištìna";
$lang['Posts_pruned'] = "Pøíspìvky proèištìny";
$lang['Prune_success'] = "Proèištìní fór probìhlo úspìšnì.";


//
// Word censor
//
$lang['Words_title'] = "Slovní cenzura";
$lang['Words_explain'] = "Z tohoto kontrolního panelu mùete pøidat, zmìnit a odstranit slova, která budou automaticky cenzurována na vašich fórech. Stejnì tak nebude moné zaregistrovat uivatelská jména obsahující tyto vırazy. Hvìzdièku (*) lze pouít za èást slova, take napø. vıraz 'test' vyhledá vıraz 'protestovat', test* potom 'testovat' a *test slovo 'protest'.";
$lang['Word'] = "Slovo";
$lang['Edit_word_censor'] = "Zmìòte slovní cenzuru";
$lang['Replacement'] = "Náhrada";
$lang['Add_new_word'] = "Pøidejte nové slovo";
$lang['Update_word'] = "Nahrajte slovní cenzuru";

$lang['Must_enter_word'] = "Musíte vloit slovo a jeho náhradu";
$lang['No_word_selected'] = "K úpravì nebylo vybráno ádné slovo";

$lang['Word_updated'] = "Vybraná slovo bylo úspìšnì nahráno do cenzury";
$lang['Word_added'] = "Slovo bylo úspìšnì pøidáno do cenzury";
$lang['Word_removed'] = "Cenzurované slovo bylo úspìšnì odstranìno";

$lang['Click_return_wordadmin'] = "Kliknìte %szde%s k návratu do Administrace slovní cenzury";


//
// Mass Email
//
$lang['Mass_email_explain'] = "Odtud mùete zaslat vzkaz jakémukoliv uivateli nebo všem z vybrané skupiny. Stane se tak zasláním e-mailu na zadanou administrátorskou adresu, pøièem uivatelùm bude zaslána slepá kopie. Pokud zasíláte vzkaz vìtší skupinì, prosím, mìjte chvilku strpení a nezastavujte akci, kdy se provádí. Je zcela bìné, e hromadná korespondence trvá delší dobu a budete upozornìni, kdy se akce dokonèí";
$lang['Compose'] = "Napsat";

$lang['Recipients'] = "Pøíjemci";
$lang['All_users'] = "Všichni uivatelé";

$lang['Email_successfull'] = "Vaše zpráva byla odeslána";
$lang['Click_return_massemail'] = "Kliknìte %szde%s k návratu na formuláø Hromadné korespondence";


//
// Ranks admin
//
$lang['Ranks_title'] = "Administrace hodnocení";
$lang['Ranks_explain'] = "Tímto formuláøem pøidáváte, mìníte, prohlííte a maete hodnocení. Mùete rovnì vytvoøit vlastní nastavení hodnocení, která mohou bıt pøiøazena pøes funkci nastavení uivatele.";

$lang['Add_new_rank'] = "Pøidat nové hodnocení";

$lang['Rank_title'] = "Název hodnocení";
$lang['Rank_special'] = "Nastavit jako zvláštní hodnocení";
$lang['Rank_minimum'] = "Minimální poèet pøíspìvkù";
$lang['Rank_maximum'] = "Maximální poèet pøíspìvkù";
$lang['Rank_image'] = "Obrázek hodnocení";
$lang['Rank_image_explain'] = "Pouijte tuto funkci k definování malého obrázku spojeného s danım hodnocením. Cesta mimo váš phpBB koøenovı adresáø a název souboru, pø.: images/ranks/images1.gif";

$lang['Must_select_rank'] = "Musíte vybrat hodnocení";
$lang['No_assigned_rank'] = "Nebylo zadáno ádné zvláštní hodnocení";

$lang['Rank_updated'] = "Hodnocení bylo úspìšnì zmìnìno";
$lang['Rank_added'] = "Hodnocení bylo úspìšnì pøidáno";
$lang['Rank_removed'] = "Hodnocení bylo úspìšnì zrušeno";
$lang['No_update_ranks'] = "Hodnocení byla úspìšnì odstranìno, ovšem uivatelské úèty s tímto hodnocením se nezmìnily. Bude zapotøebí tato hodnocení upravit ruènì";

$lang['Click_return_rankadmin'] = "Kliknìte %szde%s na návrat do Administrace hodnocení";


//
// Disallow Username Admin
//
$lang['Disallow_control'] = "Správa nepovolenıch uivatelskıch jmen";
$lang['Disallow_explain'] = "Zde mùete spravovat uivatelská jména, která nebudou povolena k pouití.  Nepovolená uivatelská jména mohou obsahovat \*\.  Upozoròujeme, e nebudete moci urèit ji registrované uivatelské jméno. Nejdøíve ho musíte odstranit a následnì jej nepovolit";

$lang['Delete_disallow'] = "odstranit";
$lang['Delete_disallow_title'] = "Odstranit nepovolené uivatelské jméno";
$lang['Delete_disallow_explain'] = "Mùete odstranit nepovolené uivatelské jméno tak, e jej vyberete ze seznamu a zmáèknete tlaèítko Odstranit";

$lang['Add_disallow'] = "Pøidat";
$lang['Add_disallow_title'] = "Pøidat nepovolené uivatelské jméno";
$lang['Add_disallow_explain'] = "Mùete zakázat nìkterá nepovolená uivatelská jména. Tato jména si nebude moci ádnı uivatel zaregistrovat. Mùete pouít i znak \"*\" pro nahrazení èásti jména";

$lang['No_disallowed'] = "ádná nepovolená uivatelská jména";

$lang['Disallowed_deleted'] = "Nepovolené uivatelské jméno bylo úspìšnì odstranìno";
$lang['Disallow_successful'] = "Nepovolené uivatelské jméno bylo úspìšnì pøidáno";
$lang['Disallowed_already'] = "Jméno, které jste zadali, nemùe bıt zakázáno. Buï se ji vyskytuje v tomto seznamu nebo v seznamu cenzurovanıch slov, nebo existuje totoné uivatelské jméno";

$lang['Click_return_disallowadmin'] = "Kliknìte %szde%s pro návrat do Administrace nastavení nepovolenıch uivatelskıch jmen";


//
// Styles Admin
//
$lang['Styles_admin'] = "Administrace stylù";
$lang['Styles_explain'] = "Zde mùete pøidávat, odebírat a spravovat styly (vzory a motivy) dostupné pro vaše uivatele";
$lang['Styles_addnew_explain'] = "Tento seznam obsahuje všechny motivy, které jsou dostupné pro vzory, které nyní máte. Èásti na tomto seznamu ještì nebyly nainstalovány do odpovídající databáze phpBB. Pro nainstalování kliknìte na instalaèní odkaz vedle zadání";

$lang['Select_template'] = "Vybrat vzor";

$lang['Style'] = "Styl";
$lang['Template'] = "Vzor";
$lang['Install'] = "Nainstalovat";
$lang['Download'] = "Stáhnout";

$lang['Edit_theme'] = "Upravit motiv";
$lang['Edit_theme_explain'] = "Ve spodním formuláøi mùete upravovat nastavení pro zvolenı vzor";

$lang['Create_theme'] = "Vytvoøit motiv";
$lang['Create_theme_explain'] = "Ve spodním formuláøi mùete vytvoøit novı motiv. Pøi zadávání barev (pro které pouijete hexadecimální hodnoty) neuvádìjte znak #, tzn. hodnota CCCCCC je platná, #CCCCCC nikoliv";

$lang['Export_themes'] = "Exportovat motivy";
$lang['Export_explain'] = "V tomto panelu mùete exportovat zadání motivu pro zvolenı vzor. Vyberte vzor ze spodního vıbìru a skript vytvoøí konfiguraèní soubor motiv a bude jej chtít uloit do vybraného adresáøe vzorù. Pokud se mu to nepodaøí, nabídne vám monost soubor stáhnout na disk. Aby se mohl soubor uloit, je nutné, aby byl zapisovatelnı pøístup pro pøíslušnı adresáø. Pro více informací se podívejte na uivatelskı manuál k phpBB 2.";

$lang['Theme_installed'] = "Vybranı motiv byl úspìšnì nainstalován";
$lang['Style_removed'] = "Vybranı styl byl odstranìn z databáze. K plnému odstranìní tohoto stylu ze systému musíte odstranit pøíslušnı styl z adresáøe vzorù.";
$lang['Theme_info_saved'] = "Informace ke zvolenému vzoru byly uloeny. Teï nastavte povolení na theme_info.cfg (a také vybraného adresáøi vzorù) na 'jen ke ètení'";
$lang['Theme_updated'] = "Vybranı motiv byl zmìnìn. Vyexportujte nyní nastavení nového motivu";
$lang['Theme_created'] = "Motiv vytvoøen. Vyexportujte nyní novı motiv do konfiguraèního souboru kvùli bezpeènému uloení nebo pouití pro jiné pøípady";

$lang['Confirm_delete_style'] = "Jste si jisti, ,e chcete odstranit tento styl";

$lang['Download_theme_cfg'] = "Nelze zapisovat do informací konfiguraèního souboru. Kliknìte na spodní tlaèítko ke staení souboru vaším prohlíeèem. A jej stáhnete, mùete jej pøesunout do adresáøe obsahujícího soubory vzorù. Pak mùete zabalit soubory pro distribuci nebo pouít jinde, kdy chcete";
$lang['No_themes'] = "Ke vzoru, kterı jste vybrali, se neváou ádné motivy. Novı motiv vytvoøíte kliknutím na 'Vytvoøit nové' na levé stranì panelu";
$lang['No_template_dir'] = "Nelze otevøít adresáø se vzory. Moná, e jej nelze pøes server èíst nebo neexistuje";
$lang['Cannot_remove_style'] = "Nelze odstranit vybranı styl, je-li stanoven jako pùvodní pro fórum. Zmìòte, prosím,  pùvodní styl a zkuste to znovu.";
$lang['Style_exists'] = "Jméno stylu ji existuje. Prosím vrate se a zvolte jiné jméno.";

$lang['Click_return_styleadmin'] = "Kliknìte %szde%s k návratu do Administrace stylù";

$lang['Theme_settings'] = "Nastavení motivu";
$lang['Theme_element'] = "Souèást vzoru";
$lang['Simple_name'] = "Jednoduchı název";
$lang['Value'] = "Hodnota";
$lang['Save_Settings'] = "Ulo nastavení";

$lang['Stylesheet'] = "Zadání stylu CSS";
$lang['Background_image'] = "Obrázek pozadí";
$lang['Background_color'] = "Barva pozadí";
$lang['Theme_name'] = "Jméno motivu";
$lang['Link_color'] = "Barva odkazu";
$lang['Text_color'] = "Barva textu";
$lang['VLink_color'] = "Barva navštíveného odkazu";
$lang['ALink_color'] = "Barva aktivního odkazu";
$lang['HLink_color'] = "Hover Link Colour";
$lang['Tr_color1'] = "Barva øádku tabulky 1";
$lang['Tr_color2'] = "Barva øádku tabulky 2";
$lang['Tr_color3'] = "Barva øádku tabulky 3";
$lang['Tr_class1'] = "Tøída øádku tabulky 1";
$lang['Tr_class2'] = "Tøída øádku tabulky 2";
$lang['Tr_class3'] = "Tøída øádku tabulky 3";
$lang['Th_color1'] = "Barva titulu tabulky 1";
$lang['Th_color2'] = "Barva titulu tabulky 2";
$lang['Th_color3'] = "Barva titulu tabulky 3";
$lang['Th_class1'] = "Table Header Class 1";
$lang['Th_class2'] = "Table Header Class 2";
$lang['Th_class3'] = "Table Header Class 3";
$lang['Td_color1'] = "Barva buòky tabulky 1";
$lang['Td_color2'] = "Barva buòky tabulky 2";
$lang['Td_color3'] = "Barva buòky tabulky 3";
$lang['Td_class1'] = "Table Cell Class 1";
$lang['Td_class2'] = "Table Cell Class 2";
$lang['Td_class3'] = "Table Cell Class 3";
$lang['fontface1'] = "Vzhled písma 1";
$lang['fontface2'] = "Vzhled písma 2";
$lang['fontface3'] = "Vzhled písma 3";
$lang['fontsize1'] = "Velikost písma 1";
$lang['fontsize2'] = "Velikost písma 2";
$lang['fontsize3'] = "Velikost písma 3";
$lang['fontcolor1'] = "Barva písma 1";
$lang['fontcolor2'] = "Barva písma 2";
$lang['fontcolor3'] = "Barva písma 3";
$lang['span_class1'] = "Rozpìtí tøíd 1";
$lang['span_class2'] = "Rozpìtí tøíd 2";
$lang['span_class3'] = "Rozpìtí tøídy 3";
$lang['img_poll_size'] = "Velikost obrázku pro hlasování [v pixelech]";
$lang['img_pm_size'] = "Velikost obrázku pro soukromou zprávu [v pixelech]";


//
// Install Process
//
$lang['Welcome_install'] = "Vítejte v instalaci phpBB 2";
$lang['Initial_config'] = "Základní nastavení";
$lang['DB_config'] = "Nastavení databáze";
$lang['Admin_config'] = "Administrátorské nastavení";
$lang['continue_upgrade'] = "Po té, co jste stáhli konfiguraèní soubor na váš disk mùete spodním tlaèítkem 'Pokraèovat v instalaci novìjší verze'.Poèkejte s nahráním konfiguraèního souboru dokud není ukonèena instalace novìjší verze.";
$lang['upgrade_submit'] = "Pokraèujte v instalování novìjší verze";

$lang['Installer_Error'] = "Bìhem instalace se objevila chyba";
$lang['Previous_Install'] = "Byla nalezena pøedešlá instalace";
$lang['Install_db_error'] = "Bìhem nahrávání novìjší instalace databáze se objevila chyba";

$lang['Re_install'] = "Vaše pøedešlá instalace je stále aktivní. <br /><br />Pokud si pøejete pøeinstalovat phpBB 2, pokraèujte tlaèítkem 'Ano'. Uvìdomte se, prosím, e v tomto pøípadì se znièí veškerá data; nedojde k zálohování. Administrátorské uivatelské jméno a heslo, které jste pouívali k nalogování budou po reinstalaci pøedìlány, ádná další nastavení nebudou zachována.<br /><br />Zamyslete se pozornì pøed zmáèknutím tlaèítka 'Ano'!";

$lang['Inst_Step_0'] = "Thank you for choosing phpBB 2. In order to complete this install please fill out the details requested below. Please note that the database you install into should already exist. If you are installing to a database that uses ODBC, e.g. MS Access you should first create a DSN for it before proceeding.";

$lang['Start_Install'] = "Zaèít instalaci";
$lang['Finish_Install'] = "Ukonèit instalaci";

$lang['Default_lang'] = "Pùvodní jazyk boardu";
$lang['DB_Host'] = "Database Server Hostname / DSN";
$lang['DB_Name'] = "Název vaší databáze";
$lang['DB_Username'] = "Uivatelské jméno databáze";
$lang['DB_Password'] = "Heslo databáze";
$lang['Database'] = "Vaše databáze";
$lang['Install_lang'] = "Vyberte jazyk pro instalaci";
$lang['dbms'] = "Typ databáze";
$lang['Table_Prefix'] = "Pøedpona pro tabulky v databázi";
$lang['Admin_Username'] = "Administrátorské uiv. jméno";
$lang['Admin_Password'] = "Administrátorské heslo";
$lang['Admin_Password_confirm'] = "Administrátorské heslo [ Potvrdit ]";

$lang['Inst_Step_2'] = "Vaše uivatelské jméno bylo vytvoøeno. V tomto momentu je základní instalace u konce. Nyní budete pøeneseni na další èást, která vám umoní další administraci nové instalace. Zkontrolujte, prosím, detaily Obecnıch preferencí a udìlejte nezbytné zmìny. Díky, e jste si vybrali phpBB 2.";

$lang['Unwriteable_config'] = "Na váš konfiguraèní soubor nelze nyní zapisovat. Kopie tohoto souboru bude staena, kdy kliknete tlaèítko dole. Posléze nahrajte tento soubor do adresáøe phpBB 2. Poté se pøihlašte jako administrátor s heslem, které jste zadali v pøedchozím formuláøi a navštivte administrátorské centrum (odkaz se objeví ve spodní èásti kadé stránky poté, co se nalogujete) a zkontrolujte obecnou konfiguraci. Díky, e jste si vybrali phpBB 2.";
$lang['Download_config'] = "Stáhnout konfiguraèní soubor";

$lang['ftp_choose'] = "Vyberte si zpùsob stáhnutí";
$lang['ftp_option'] = "<br />Vzhledem k tomu, e je v této verzi umonìn rozšíøenı pøenos php mùe vám bıt dán prostor nejdøíve pøenést váš konfiguraèní soubor automaticky.";
$lang['ftp_instructs'] = "Vybrali jste automatickou volbu pøenosu. Zadejte, prosím, informace k uskuteènìní tohoto procesu. Prosím, pamatujte na to, e cesta pøenosu má bıt pøesnì taková, jakou byste zadávali cestu pøes jakéhokoliv bìného klienta.";
$lang['ftp_info'] = "Zadejte vaše informace pøenosu FTP";
$lang['Attempt_ftp'] = "Pokus o pøenos konfiguraèního souboru na místo";
$lang['Send_file'] = "Pošlete mi soubor a já jej pøenesu sám";
$lang['ftp_path'] = "Cesta FTP na phpBB";
$lang['ftp_username'] = "Vaše uivatelské jméno FTP";
$lang['ftp_password'] = "Vaše heslo FTP";
$lang['Transfer_config'] = "Zaèít pøenos";
$lang['NoFTP_config'] = "Pokus pøenést soubor na místo selhal. Prosím, stáhnìte a pak nahrajte konfiguraèní soubor sami.";

$lang['Install'] = "Instalovat";
$lang['Upgrade'] = "Inovovat verzi";

$lang['Install_Method'] = "Vyberte druh instalace";

$lang['Install_No_Ext'] = "Nastavení php na vašem serveru nepodporuje databázi, kterou jste zvolili";

$lang['Install_No_PCRE'] = "phpBB2 poaduje the Perl-Compatible Regular Expressions Module pro php, co vaše konfigurace php zøejmì nepodporuje!";

//
// That's all Folks!
// -------------------------------------------------


?>