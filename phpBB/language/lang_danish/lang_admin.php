<?php

/***************************************************************************
 *                            lang_admin.php [English]
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
// Translation done by Ken Christensen (Dalixam)

// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = "Normal Administrator";
$lang['Users'] = "Bruger Administrator";
$lang['Groups'] = "Gruppe Administrator";
$lang['Forums'] = "Forum Administrator";
$lang['Styles'] = "Layout Administrator";

$lang['Configuration'] = "Konfiguration";
$lang['Permissions'] = "Tilladelser";
$lang['Manage'] = "Redigering";
$lang['Disallow'] = "Forbyd specifikke Navne";
$lang['Prune'] = "Beskær";
$lang['Mass_Email'] = "Gruppe email";
$lang['Ranks'] = "Niveauer";
$lang['Smilies'] = "Smilies";
$lang['Ban_Management'] = "Forbyd Kontrol";
$lang['Word_Censor'] = "Ordcensurerer";
$lang['Export'] = "Eksportér";
$lang['Create_new'] = "Opret";
$lang['Add_new'] = "Tilføj";
$lang['Backup_DB'] = "Backup Database";
$lang['Restore_DB'] = "Genopret Database";


//
// Index
//
$lang['Admin'] = "Administration";
$lang['Not_admin'] = "Du er ikke autoriseret til at redigere dette forum";
$lang['Welcome_phpBB'] = "Velkommen til phpBB";
$lang['Admin_intro'] = "Tak fordi du valgte phpBB som dit forum. Denne skærm vil give dig et hurtigt overblik over alle de forskellige statistikker, der er tilgængelige om dit forum. Du kan komme tilbage til denne side ved at klikke på <u>Administrationsindex</u> linket i venstre side. For at vende tilbage til indexsiden af dit forum skal du klikke på phpBB logoet, der også befinder sig i venstre side. De andre links i venstre side lader dig kontrollere og redigere hver eneste aspekt af dit forum, hver enkelt side indeholder instruktioner om, hvordan du bruger de forskellige værktøjer.";
$lang['Main_index'] = "Forumindex";
$lang['Forum_stats'] = "Forum Statistikker";
$lang['Admin_Index'] = "Administrationsindex";
$lang['Preview_forum'] = "Se et smugkig på Forumet";

$lang['Click_return_admin_index'] = "Klik %sHer%s for at vende tilbage til Administrationsindexet";

$lang['Statistic'] = "Statistikker";
$lang['Value'] = "Værdi";
$lang['Number_posts'] = "Antal indlæg";
$lang['Posts_per_day'] = "Indlæg pr dag";
$lang['Number_topics'] = "Antal emner";
$lang['Topics_per_day'] = "Emner pr dag";
$lang['Number_users'] = "Antal brugere";
$lang['Users_per_day'] = "Brugere pr dag";
$lang['Board_started'] = "Forum oprettelse";
$lang['Avatar_dir_size'] = "Avatar-bibliotekets størrelse";
$lang['Database_size'] = "Database-størrelse";
$lang['Gzip_compression'] ="Gzip komprimering";
$lang['Not_available'] = "Ikke tilgængelig";

$lang['ON'] = "Slået til"; // Dette er for GZip komprimering
$lang['OFF'] = "Slået fra"; 


//
// DB Utils
//
$lang['Database_Utilities'] = "Database-værktøjer";

$lang['Restore'] = "Genopret";
$lang['Backup'] = "Backup";
$lang['Restore_explain'] = "Dette vil genoprette alle phpBB tabeller fra en gemt fil. Hvis din server understøtter det, kan du uploade en gzip komprimeret tekstfil og så bliver den automatisk dekomprimeret. <b>ADVARSEL</b> Dette vil overskrive alle eksisterende data. Genoprettelsen kan tage lang tid at udføre, forlad venligst ikke denne side før den er fuldført.";
$lang['Backup_explain'] = "Her kan du lave en backup, af alle dine data, der relaterer til phpBB. Hvis du har andre tabeller i din database, som du også ønsker, at lave en backup af, skal du indtaste deres navne adskilt af kommaer i Yderligere Tabeller feltet nedenunder. Hvis din server understøtter det, kan du gzip komprimere filen før du downloader den for at reducere dens størrrelse.";

$lang['Backup_options'] = "Backup muligheder";
$lang['Start_backup'] = "Start backup";
$lang['Full_backup'] = "Fuld backup";
$lang['Structure_backup'] = "Kun Struktur backup";
$lang['Data_backup'] = "Kun Data backup";
$lang['Additional_tables'] = "Yderligere Tabeller";
$lang['Gzip_compress'] = "Gzip komprimér fil";
$lang['Select_file'] = "Vælg en fil";
$lang['Start_Restore'] = "Start Genopret";

$lang['Restore_success'] = "Databasen er blevet succesfuldt genoprettet.<br /><br />Dit forum skulle nu være i den tilstand, det var, da backuppen blev foretaget";
$lang['Backup_download'] = "Din download vil begynde om kort tid, vent venligst til den begynder";
$lang['Backups_not_supported'] = "Desværre, backups af databaser fra dit database-system understøttes på nuværende tidspunkt ikke";

$lang['Restore_Error_uploading'] = "Der skete en fejl under uploading af backup-filen";
$lang['Restore_Error_filename'] = "Der er et problem med filens navn, prøv venligst med en alternativ fil";
$lang['Restore_Error_decompress'] = "Gzip-filen kan ikke dekomprimeres, upload venligst en ren tekstversion";
$lang['Restore_Error_no_file'] = "Ingen fil blev uploaded";


//
// Auth pages
//
$lang['Select_a_User'] = "Vælg en Bruger";
$lang['Select_a_Group'] = "Vælg en Gruppe";
$lang['Select_a_Forum'] = "Vælg et Forum";
$lang['Auth_Control_User'] = "Brugerrettigheder kontrolside"; 
$lang['Auth_Control_Group'] = "Grupperettigheder kontrolside"; 
$lang['Auth_Control_Forum'] = "Forumrettigheder kontrolside"; 
$lang['Look_up_User'] = "Slå Brugeren op"; 
$lang['Look_up_Group'] = "Slå Gruppen op"; 
$lang['Look_up_Forum'] = "Vis forum"; 

$lang['Group_auth_explain'] = "Her kan du ændre rettighederne og redaktørstatussen, tilskrevet til hver enkelt brugergruppe. Glem ikke når du ændrer grupperettigheder, at individuelle brugerrettigheder måske stadig tillader, at brugeren har adgang til forumerne, etc. Du bliver advaret, hvis dette er tilfældet.";
$lang['User_auth_explain'] = "Her kan du ændre rettighederne og redaktørstatussen, tilskrevet til hver enkelt bruger. Glem ikke når du ændrer brugerrettigheder, at grupperettigheder måske stadig tillader, at brugeren har adgang til forumerne, etc. Du bliver advaret, hvis dette er tilfældet.";
$lang['Forum_auth_explain'] = "Her kan du ændre autoriseringsniveauer for de enkelte forumer. Du har både en simpel og avanceret metode til rådighed. Den avancerede metode tillader større kontrol af hvert enkelt forum. Husk, at når du ændrer forumernes autoriseringsniveauerne, påvirker det hvilke brugere, der kan udføre de forskellige ting i dem.";

$lang['Simple_mode'] = "Simpel Metode";
$lang['Advanced_mode'] = "Avanceret Metode";
$lang['Moderator_status'] = "Redaktør status";

$lang['Allowed_Access'] = "Tilladt Adgang";
$lang['Disallowed_Access'] = "Nægtet Adgang";
$lang['Is_Moderator'] = "Er Redaktør";
$lang['Not_Moderator'] = "Er ikke Redaktør";

$lang['Conflict_warning'] = "Advarsel om Autoriseringskonflikt";
$lang['Conflict_access_userauth'] = "Denne bruger har stadig adgangsrettigheder til dette forum via et gruppemedlemsskab. Du skal nok ændre gruppens rettigheder eller fjerne denne bruger fra gruppen, for at forhindre, at de har adgangsrettigheder. Gruppens rettigheder (og forumerne involveret) er nævnt nedenunder.";
$lang['Conflict_mod_userauth'] = "Denne bruger har stadig redaktørrettigheder til dette forum via et gruppemedlemsskab. Du skal nok ændre gruppens rettigheder eller fjerne denne bruger fra gruppen, for at forhindre, at de har redaktørrettigheder. Gruppens rettigheder (og forumerne involveret) er nævnt nedenunder.";

$lang['Conflict_access_groupauth'] = "Den følgende bruger (eller brugere) har stadig adgangsrettigheder til dette forum via deres brugerrettigheder. Du skal nok brugerrettighederne for at forhindre dem i, at have adgangsrettigheder. Brugernes rettigheder (og forumerne involveret) er nævnt nedenunder.";
$lang['Conflict_mod_groupauth'] = "Den følgende bruger (eller brugere) har stadig redaktørrettigheder til dette forum via deres brugerrettigheder. Du skal nok brugerrettighederne for at forhindre dem i, at have redaktørrettigheder. Brugernes rettigheder (og forumerne involveret) er nævnt nedenunder.";

$lang['Public'] = "Offentlig";
$lang['Private'] = "Privat";
$lang['Registered'] = "Registeret";
$lang['Administrators'] = "Administratorer";
$lang['Hidden'] = "Skjult";

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = "ALLE";
$lang['Forum_REG'] = "REGISTRERET";
$lang['Forum_PRIVATE'] = "PRIVAT";
$lang['Forum_MOD'] = "REDAKTØR";
$lang['Forum_ADMIN'] = "ADMIN";

$lang['View'] = "Se";
$lang['Read'] = "Læse";
$lang['Post'] = "Indlæg";
$lang['Reply'] = "Svar";
$lang['Edit'] = "Ændre";
$lang['Delete'] = "Slet";
$lang['Sticky'] = "Oplag";
$lang['Announce'] = "Bekendtgøre"; 
$lang['Vote'] = "Stem";
$lang['Pollcreate'] = "Opret afstemning";

$lang['Permissions'] = "Rettigheder";
$lang['Simple_Permission'] = "Simpel Rettighed";

$lang['User_Level'] = "Brugerniveau"; 
$lang['Auth_User'] = "Bruger";
$lang['Auth_Admin'] = "Administrator";
$lang['Group_memberships'] = "Medlemsskaber til brugergrupper";
$lang['Usergroup_members'] = "Denne gruppe har følgende medlemmer";

$lang['Forum_auth_updated'] = "Forumrettigheder opdateret";
$lang['User_auth_updated'] = "Brugerrettigheder opdateret";
$lang['Group_auth_updated'] = "Grupperettigheder opdateret";

$lang['Auth_updated'] = "Rettigheder er blevet opdateret";
$lang['Click_return_userauth'] = "Klik %sHer%s for at vende tilbage til Brugerrettigheder";
$lang['Click_return_groupauth'] = "Klik %sHer%s for at vende tilbage til Grupperettigheder";
$lang['Click_return_forumauth'] = "Klik %sHer%s for at vende tilbage til Forumrettigheder";


//
// Banning
//
$lang['Ban_control'] = "Kontrollering af bandlysninger";
$lang['Ban_explain'] = "Her kan du kontrollere bandlysning af brugere. Du kan opnå dette, ved enten at bandlyse en specifik bruger, en eller flere IP adresser eller domainnavne. Disse metoder forhindrer en bruger i overhovedet at få adgang til dit forums indexside. For at forhindre, at en bruger registrerer sig under en andet brugernavn, kan du også bandlyse en email adresse. Husk, bandlysning af en email adresse vil ikke forhindre den bruger i at logge på eller komme med nye indlæg på dit forum, du skal bruge en af de førstnævnte metoder for, at forhindre dette.";
$lang['Ban_explain_warn'] = "Bemærk venligst, at hvis du indtaster en række IP adresser vil dette resultere i, at alle adresserne mellem start og slut bliver tilføjet listen over bandlyste IP adresser. Forsøg på, at minimere antallet af adresser tilføjet til databasen, vil blive foretaget ved automatisk at introducere jokere, når det er passende. Hvis du virkelig vil indtaste en række adresser, så prøv at minimere rækken eller endnu bedre, indtast specifikke adresser.";

$lang['Select_username'] = "Vælg et Brugernavn";
$lang['Select_ip'] = "Vælg en IP Adresse";
$lang['Select_email'] = "Vælg en Email Adresse";

$lang['Ban_username'] = "Bandlys en eller flere specifikke brugere";
$lang['Ban_username_explain'] = "Du kan bandlyse flere brugere på én gang, ved at bruge den passende kombination af mus og tastatur til din computer og browser.";

$lang['Ban_IP'] = "Bandlys en eller flere IP adresser eller domainnavne";
$lang['IP_hostname'] = "IP adresser eller domainnavne";
$lang['Ban_IP_explain'] = "For at angive flere IP adresser eller domainnavne skal du adskille dem med kommaer. For at angive en række IP adresser skal du adskille starten og slutningen med en bindestreg (-), for at angive en joker skal du bruge *";

$lang['Ban_email'] = "Bandlys en eller flere adresser";
$lang['Ban_email_explain'] = "For at angive flere email adresser skal du adskille dem med kommaer. For at angive en joker skal du bruge *, for eksempel *@hotmail.com";

$lang['Unban_username'] = "Ophæv bandlysning på en eller flere brugere";
$lang['Unban_username_explain'] = "Du kan ophæve bandlysning af flere brugere på en gang, ved at bruge den passende kombination af mus og tastatur til din computer og browser.";

$lang['Unban_IP'] = "Ophæv bandlysning på en eller flere IP adresser";
$lang['Unban_IP_explain'] = "Du kan ophæve bandlysning på en eller flere IP adresser på en gang, ved at bruge den passende kombination af mus og tastatur til din computer og browser.";

$lang['Unban_email'] = "Ophæv bandlysning på en eller flere email adresser.";
$lang['Unban_email_explain'] = "Du kan ophæve bandlysning på flere email adresser på en gang, ved at bruge den passende kombination af mus og tastatur til din computer og browser.";

$lang['No_banned_users'] = "Ingen bandlyste brugernavne";
$lang['No_banned_ip'] = "Ingen bandlyste IP adresser";
$lang['No_banned_email'] = "Ingen bandlyste email adresser";

$lang['Ban_update_sucessful'] = "Listen med oplysningerne om bandlysninger er succesfuldt opdateret.";
$lang['Click_return_banadmin'] = "Klik %sHer%s for at vende tilbage til Kontrollering af bandlysninger";


//
// Configuration
//
$lang['General_Config'] = "Generel Konfiguration";
$lang['Config_explain'] = "Nedenstående skema giver dig mulighed for, at tilpasse alle de generelle forumindstillinger. For Bruger og Forumindstilninger, brug de relaterende links i venstre side.";

$lang['Click_return_config'] = "Klik %sHer%s for at vende tilbage til Generel Konfiguration";

$lang['General_settings'] = "Generelle forumindstillinger";
$lang['Server_name'] = "Domænenavn";
$lang['Server_name_explain'] = "Domænet dette forum ligger på";
$lang['Script_path'] = "Script Adresse";
$lang['Script_path_explain'] = "Adressen til phpBB2 på domænenavnet";
$lang['Server_port'] = "Server Port";
$lang['Server_port_explain'] = "Porten hvorpå din server kører. Det er normalt 80, ændre kun hvis nødvendigt";
$lang['Site_name'] = "Sidens navn";
$lang['Site_desc'] = "Beskrivelse af siden";
$lang['Board_disable'] = "Slå forumet fra";
$lang['Board_disable_explain'] = "Dette vil gøre forumet utilgængeligt for brugere. Du må ikke logge ud, når du har slået forumet fra. Du vil ikke kunne logge på igen!";
$lang['Acct_activation'] = "Aktivering af Konto";
$lang['Acc_None'] = "Ingen"; // These three entries are the type of activation
$lang['Acc_User'] = "Bruger";
$lang['Acc_Admin'] = "Administrator";

$lang['Abilities_settings'] = "Generelle Bruger- og Forumindstillinger";
$lang['Max_poll_options'] = "Maximum antal afstemningsmuligheder";
$lang['Flood_Interval'] = "Sekunder mellem indlæg";
$lang['Flood_Interval_explain'] = "Antal sekunder en bruger skal vente, før et nyt indlæg kan skrives."; 
$lang['Board_email_form'] = "Bruger-email via forum";
$lang['Board_email_form_explain'] = "Brugere kan sende hinanden email via dette forum";
$lang['Topics_per_page'] = "Emner pr side";
$lang['Posts_per_page'] = "Indlæg pr side";
$lang['Hot_threshold'] = "Indlæg for populære emner";
$lang['Default_style'] = "Standard layout";
$lang['Override_style'] = "Overskriv brugers valg";
$lang['Override_style_explain'] = "Erstatter brugernes valg med det, der er standard";
$lang['Default_language'] = "Standard Sprog";
$lang['Date_format'] = "Dato Format";
$lang['System_timezone'] = "Tidszone";
$lang['Enable_gzip'] = "Slå GZip Komprimering til";
$lang['Enable_prune'] = "Slå Forumbeskæring til";
$lang['Allow_HTML'] = "Tillad HTML";
$lang['Allow_BBCode'] = "Tillad BBCode";
$lang['Allowed_tags'] = "Tilladte HTML kommandoer (tags)";
$lang['Allowed_tags_explain'] = "Adskil kommandoer med kommaer";
$lang['Allow_smilies'] = "Tillad Smilies";
$lang['Smilies_path'] = "Adressen hvor dine Smilies opbevares";
$lang['Smilies_path_explain'] = "Adressen under din phpBB folder, f.eks. images/smilies";
$lang['Allow_sig'] = "Tillad Underskrifter";
$lang['Max_sig_length'] = "Maksimal lændge på underskrifter";
$lang['Max_sig_length_explain'] = "Maksimale antal tegn i brugernes underskrifter";
$lang['Allow_name_change'] = "Tillad, at brugerne kan ændre brugernavn";

$lang['Avatar_settings'] = "Avatar Indstillinger";
$lang['Allow_local'] = "Slå galleri-avatars til";
$lang['Allow_remote'] = "Slå udefra avatars til";
$lang['Allow_remote_explain'] = "Avatars, der befinder sig på en anden hjemmeside";
$lang['Allow_upload'] = "Slå uploading af avatar til";
$lang['Max_filesize'] = "Maksimal størrelse på Avatar-Filer";
$lang['Max_filesize_explain'] = "Gælder de avatars, der uploades";
$lang['Max_avatar_size'] = "Maksimal størrelse på Avatars";
$lang['Max_avatar_size_explain'] = "(Højde x Bredde i pixels)";
$lang['Avatar_storage_path'] = "Adressen hvor Avatars opbevares";
$lang['Avatar_storage_path_explain'] = "Adressen under din phpBB folder, f.eks. images/avatars";
$lang['Avatar_gallery_path'] = "Adressen på Avatar-Galleriet";
$lang['Avatar_gallery_path_explain'] = "Adressen under din phpBB folder, f.eks. images/avatars/gallery";

$lang['COPPA_settings'] = "COPPA Indstillinger";
$lang['COPPA_fax'] = "COPPA Fax Nummer";
$lang['COPPA_mail'] = "COPPA Adresse";
$lang['COPPA_mail_explain'] = "Dette er adressen, hvortil forældre vil sende COPPA blanketterne";

$lang['Email_settings'] = "Email Indstillinger";
$lang['Admin_email'] = "Email adresse på Administrator";
$lang['Email_sig'] = "Email Underskrift";
$lang['Email_sig_explain'] = "Dette tekst vil slutte alle emails, afsendt af forumet";
$lang['Use_SMTP'] = "Brug en SMTP Server for email";
$lang['Use_SMTP_explain'] = "Vælg ja hvis du vil, eller skal, sende email via en specifik server istedet for den normale email funktion.";
$lang['SMTP_server'] = "SMTP Server Adresse";
$lang['SMTP_username'] = "SMTP Brugernavn";
$lang['SMTP_username_explain'] = "Indtast kun et brugernavn, hvis din smtp server kræver det";
$lang['SMTP_password'] = "SMTP Kodeord";
$lang['SMTP_password_explain'] = "Indtast kun et kodeord, hvis din smtp server kræver det";

$lang['Disable_privmsg'] = "Private Beskeder";
$lang['Inbox_limits'] = "Maksimale antal indlæg i Indbakke";
$lang['Sentbox_limits'] = "Maksimale antal indlæg i Sendt-bakken";
$lang['Savebox_limits'] = "Maksimale antal indlæg i Gemt-bakken";

$lang['Cookie_settings'] = "Cookie indstillinger"; 
$lang['Cookie_settings_explain'] = "Disse kontrollere, hvordan en cookie defineres af en browser. I de fleste tilfælde skulle standard indstillingerne være gode nok. Hvis du har brug for at ændre dem, så pas på, forkerte indstillinger kan resultere i, at brugere ikke kan logge på.";
$lang['Cookie_name'] = "Cookie navn";
$lang['Cookie_domain'] = "Cookie domain";
$lang['Cookie_path'] = "Cookie adresse";
$lang['Session_length'] = "Længde på Session [ sekunder ]";
$lang['Cookie_secure'] = "Cookie sikkerhed [ https ]";
$lang['Cookie_secure_explain'] = "Slå dette til hvis din server kører via SSL ellers lad det være slået fra";


//
// Forum Management
//
$lang['Forum_admin'] = "Forum Administration";
$lang['Forum_admin_explain'] = "Fra denne kan du tilføje, slette, ændre, omorganisere og gensynkronisere kategorier og forums.";
$lang['Edit_forum'] = "Ændre forum";
$lang['Create_forum'] = "Opret nyt forum";
$lang['Create_category'] = "Opret ny kategori";
$lang['Remove'] = "Fjern";
$lang['Action'] = "Udfør";
$lang['Update_order'] = "Opdatér Order";
$lang['Config_updated'] = "Forum Konfiguration Succesfuldt Opdateret";
$lang['Edit'] = "Ændre";
$lang['Delete'] = "Slet";
$lang['Move_up'] = "Ryk op";
$lang['Move_down'] = "Ryk ned";
$lang['Resync'] = "Re-synkronisér";
$lang['No_mode'] = "Ingen memode blev valgt";
$lang['Forum_edit_delete_explain'] = "Skemaet nedenunder vil gøre det muligt for dig, at tilpasse alle de generelle forum indstillinger. For Bruger og de enkelte Forums indstillinger skal du bruge linkene til venstre.";

$lang['Move_contents'] = "Flyt alt indhold";
$lang['Forum_delete'] = "Slet Forum";
$lang['Forum_delete_explain'] = "Skemaet nedenunder vil gøre det muligt for dig, at slette et forum (eller en kategori) og bestemme, hvor du vil placere alle emner (eller forums) det/den indeholdt.";

$lang['Forum_settings'] = "Generelle Forum Indstillinger";
$lang['Forum_name'] = "Forumets navn";
$lang['Forum_desc'] = "Beskrivelse";
$lang['Forum_status'] = "Forum status";
$lang['Forum_pruning'] = "Automatisk sletning";

$lang['prune_freq'] = 'Tjek for emners alder hver';
$lang['prune_days'] = "Fjern emner, der ikke er blevet skrevet indlæg til i";
$lang['Set_prune_data'] = "Du har slået automatisk sletning til for dette forum, men du indtastede ikke et antal dage, hvorefter emner skal slettes. Gå venligst tilbage og gør dette.";

$lang['Move_and_Delete'] = "Flyt og Slet";

$lang['Delete_all_posts'] = "Slet alle indlæg";
$lang['Nowhere_to_move'] = "Ingen steder at flytte til";

$lang['Edit_Category'] = "Ændre Kategori";
$lang['Edit_Category_explain'] = "Brug dette skema til at ændre navnet på en kategori.";

$lang['Forums_updated'] = "Forum og Kategori information succesfuldt opdateret";

$lang['Must_delete_forums'] = "Du skal slette alle forums, før du kan slette denne kategori";

$lang['Click_return_forumadmin'] = "Klik %sHer%s for at vende tilbage til Forum Administration";


//
// Smiley Management
//
$lang['smiley_title'] = "Smiles Værktøjer";
$lang['smile_desc'] = "Fra denne side, kan du tilføje, slette og ændre de smileys dine brugere kan bruge i deres indlæg og private beskeder.";

$lang['smiley_config'] = "Smiley Indstillinger";
$lang['smiley_code'] = "Smiley Kode";
$lang['smiley_url'] = "Smiley Billede Fil";
$lang['smiley_emot'] = "Smiley Udtryk";
$lang['smile_add'] = "Tilføj en ny Smiley";
$lang['Smile'] = "Smil";
$lang['Emotion'] = "Udtryk";

$lang['Select_pak'] = "Vælg Pakke (.pak) Fil";
$lang['replace_existing'] = "Erstat eksisterende Smiley";
$lang['keep_existing'] = "Behold eksisterende Smiley";
$lang['smiley_import_inst'] = "Du skal pakke zip filen ud og uploade alle filerne til den påregnede Smiley folder til din installation.  Så skal du vælge den korrekte information på dette skema for at importere smiley pakken.";
$lang['smiley_import'] = "Smiley Pakke Import";
$lang['choose_smile_pak'] = "Vælg en Smile Pakke .pak fil";
$lang['import'] = "Importér Smileys";
$lang['smile_conflicts'] = "Hvad skal gøres i tilfælde af konflikter";
$lang['del_existing_smileys'] = "Slet eksisterende smileys inden importering";
$lang['import_smile_pack'] = "Importér Smiley Pakke";
$lang['export_smile_pack'] = "Opret Smiley Pakke";
$lang['export_smiles'] = "For at oprette en smiley pakke med dine nuværende installerede smileys, klik %sHer%s for at downloade smiles.pak filen. Giv den et passende navn og sørg for, at den beholder .pak efternavnet. Lav så en zip fil der indeholder alle smiley billederne plus denne .pak konfigurationsfil.";

$lang['smiley_add_success'] = "Smileyen blev succesfuldt tilføjet";
$lang['smiley_edit_success'] = "Smileyen blev succesfuldt opdateret";
$lang['smiley_import_success'] = "Smiley Pakken blev succesfuldt importeret!";
$lang['smiley_del_success'] = "Smileyen blev succesfuldt fjernet";
$lang['Click_return_smileadmin'] = "Klik %sHer%s for at vende tilbage til Smiley Administration";


//
// User Management
//
$lang['User_admin'] = "Bruger Administration";
$lang['User_admin_explain'] = "Her kan du ændre dine brugers informationer og specifikke indstillinger. For at ændre brugernes rettigheder, brug venligst bruger og gruppe indstillingssystemet.";

$lang['Look_up_user'] = "Slå bruger op";

$lang['Admin_user_fail'] = "Brugerens profil kunne ikke opdaters.";
$lang['Admin_user_updated'] = "Brugerens profil blev succesfuldt opdateret.";
$lang['Click_return_useradmin'] = "Klik %sHer%s for at vende tilbage til Bruger Administration";

$lang['User_delete'] = "Slet denne bruger";
$lang['User_delete_explain'] = "Klik her for at slette denne bruger. Det er permenent.";
$lang['User_deleted'] = "Brugeren blev succesfuldt slettet.";

$lang['User_status'] = "Brugeren er aktiv";
$lang['User_allowpm'] = "Kan sende Private Beskeder";
$lang['User_allowavatar'] = "Kan vise en avatar";

$lang['Admin_avatar_explain'] = "Her kan du se og slette brugerens nuværende avatar.";

$lang['User_special'] = "Specialle kun-administrator felter";
$lang['User_special_explain'] = "Disse felter kan ikke ændres af brugerne. Her kan du bestemme deres status og ændre indstillinger, de ikke har adgang til.";


//
// Group Management
//
$lang['Group_administration'] = "Gruppe Administration";
$lang['Group_admin_explain'] = "Fra dette panel kan du administere alle dine brugergrupper, du kan; slette, oprette og ændre eksisterende grupper. Du kan vælge redaktører, slå grupper til og fra og bestemme gruppens navn og beskrivelse";
$lang['Error_updating_groups'] = "Der opstod en fejl under opdateringen af grupperne";
$lang['Updated_group'] = "Gruppen blev succesfuldt opdateret";
$lang['Added_new_group'] = "Den nye gruppe blev succesfuldt oprettet";
$lang['Deleted_group'] = "Gruppen blev succesfuldt slettet";
$lang['New_group'] = "Opret en ny gruppe";
$lang['Edit_group'] = "Ændre gruppe";
$lang['group_name'] = "Gruppens navn";
$lang['group_description'] = "Gruppens beskrivelse";
$lang['group_moderator'] = "Grupperedaktør";
$lang['group_status'] = "Gruppestatus";
$lang['group_open'] = "Åben gruppe";
$lang['group_closed'] = "Lukket gruppe";
$lang['group_hidden'] = "Skjult gruppe";
$lang['group_delete'] = "Slet gruppe";
$lang['group_delete_check'] = "Slet denne gruppe";
$lang['submit_group_changes'] = "Tilføj ændringer";
$lang['reset_group_changes'] = "Nulstil ændringer";
$lang['No_group_name'] = "Du skal vælge et navn til denne gruppe";
$lang['No_group_moderator'] = "Du skal vælge en redaktør til denne gruppe";
$lang['No_group_mode'] = "Du skal vælge en status for denne gruppe, åben eller lukket";
$lang['delete_group_moderator'] = "Slet den gamle grupperedaktør?";
$lang['delete_moderator_explain'] = "Hvis du ændrer grupperedaktøren, sæt kryds i dette felt for at slette den gamle redaktør fra gruppen. Hvis du ikke sætter kryds, bliver brugeren et normalt medlem af gruppen.";
$lang['Click_return_groupsadmin'] = "Klik %sHer%s for at vende tilbage til Gruppe Administration.";
$lang['Select_group'] = "Vælg en gruppe";
$lang['Look_up_group'] = "Vis Gruppe";


//
// Prune Administration
//
$lang['Forum_Prune'] = "Forum Beskæring";
$lang['Forum_Prune_explain'] = "Dette vil slette enhvert emne, som der ikke er skrevet indlæg til, i det antal dage du vælger. Hvis du ikke indtaster et nummer, bliver alle emner slettet. Emner, hvor der stadig er åbne afstemninger, vil ikke blive slettet, ligesom Annonceringer heller ikke slettes. Du skal slette disse emner manuelt.";
$lang['Do_Prune'] = "Udfør beskæring";
$lang['All_Forums'] = "Alle Forums";
$lang['Prune_topics_not_posted'] = "Slet emner uden nye indlæg i";
$lang['Topics_pruned'] = "Emner slettet";
$lang['Posts_pruned'] = "Indlæg slettet";
$lang['Prune_success'] = "Beskæring af forums var succesfuldt";


//
// Word censor
//
$lang['Words_title'] = "Censurering af ord";
$lang['Words_explain'] = "Fra denne side kan du tilføje, ændre og fjerne ord, der automatisk censureres i dine forums. Folk kan heller ikke registrere med et brugernavn, der indeholder disse ord. Jokere (*) kan bruges i ordfelterne, f.eks. *test* vil matche utestet, test* vil matche tester, *test ville matche utest.";
$lang['Word'] = "Ord";
$lang['Edit_word_censor'] = "Ændre ordcensurér";
$lang['Replacement'] = "Erstatning";
$lang['Add_new_word'] = "Tilføj nyt ord";
$lang['Update_word'] = "Opdatér ordcensurér";

$lang['Must_enter_word'] = "Du skal indtaste et ord og det ords erstatning";
$lang['No_word_selected'] = "Intet ord er valgt til ændring";

$lang['Word_updated'] = "Den valgte ordcensurér blev succesfuldt opdateret";
$lang['Word_added'] = "Ordcensureren blev succesfuldt tilføjet";
$lang['Word_removed'] = "Den valgte ordcensurér blev succesfuldt fjernet";

$lang['Click_return_wordadmin'] = "Klik %sHer%s for at vende tilbage til Censurering af ord";


//
// Mass Email
//
$lang['Mass_email_explain'] = "Her kan du sende email til alle dine brugere eller alle brugere i en specifik gruppe. For at gøre dette, bliver en email sent til alle de administrative email adresser angivet og en kopi sendes til alle modtagere. Hvis du emailer en stor gruppe mennesker, skal du være tålmodig efter, at du har sendt emailen og stop ikke siden, når den er halvvejs. Det er normalt, at det tager lang tid, du får at vide, når emailen er færdigsendt.";
$lang['Compose'] = "Skriv"; 

$lang['Recipients'] = "Modtagere"; 
$lang['All_users'] = "Alle Brugere";

$lang['Email_successfull'] = "Din besked er afsendt";
$lang['Click_return_massemail'] = "Klik %sHer%s for at vende tilbage til Gruppe email";


//
// Ranks admin
//
$lang['Ranks_title'] = "Niveau Administration";
$lang['Ranks_explain'] = "Ved at bruge denne side kan du tilføje, ændre, se og slette niveauer. Du kan også oprettte specifikke niveauer, som kan gives til en bruger, gennem brugerværktøjerne.";

$lang['Add_new_rank'] = "Tilføj nyt niveau";

$lang['Rank_title'] = "Niveau Titel";
$lang['Rank_special'] = "Lav til Specialt Niveau";
$lang['Rank_minimum'] = "Minimum Indlæg";
$lang['Rank_maximum'] = "Maximum Indlæg";
$lang['Rank_image'] = "Niveau Billede (Relativ til phpBB2 hovedfolderen)";
$lang['Rank_image_explain'] = "Bestem her, om et lille billede skal bruges sammen med niveauet.";

$lang['Must_select_rank'] = "Du skal vælge et niveau";
$lang['No_assigned_rank'] = "Intet specialt niveau tilvalgt";

$lang['Rank_updated'] = "Niveauet blev succesfuldt opdateret";
$lang['Rank_added'] = "Niveauet blev succesfuldt tilføjet";
$lang['Rank_removed'] = "Niveauet blev succesfuldt slettet";

$lang['Click_return_rankadmin'] = "Klik %sHer%s for at vende tilbage til Niveau Administration";


//
// Disallow Username Admin
//
$lang['Disallow_control'] = "Forbyd Brugernavn Værktøjer";
$lang['Disallow_explain'] = "Her kan du kontrollere, hvilke brugernavne der er tilladt. Forbudte brugernavne må indeholde en joker, betegnet som et *. Bemærk venligst, at du ikke kan forbyde et brugernavn, der allerede er registreret, du skal først slette det navn og så forbyde det.";

$lang['Delete_disallow'] = "Slet";
$lang['Delete_disallow_title'] = "Fjern et forbudt brugernavn";
$lang['Delete_disallow_explain'] = "Du kan fjerne et forbudt brugernavn ved at vælge brugernavnet fra denne liste og trykke på knappen";

$lang['Add_disallow'] = "Tilføj";
$lang['Add_disallow_title'] = "Tilføj et forbudt brugernavn";
$lang['Add_disallow_explain'] = "Du kan forbyde et brugernavn ved at bruge jokeren * istedet for et tegn";

$lang['No_disallowed'] = "Ingen forbudte brugernavne";

$lang['Disallowed_deleted'] = "Det forbudte brugernavn blev succesfuldt fjernet";
$lang['Disallow_successful'] = "Det forbudte brugernavn blev succesfuldt tilføjet";
$lang['Disallowed_already'] = "Navnet du indtastede, kunne ikke forbydes. Enten eksisterer det allerede i listen, eksisterer i ord censureringslisten eller en bruger har valgt det brugernavn";

$lang['Click_return_disallowadmin'] = "Klik %sHer%s for at vende tilbage til Forbyd Brugernavn Værktøjer";


//
// Styles Admin
//
$lang['Styles_admin'] = "Layout Administration";
$lang['Styles_explain'] = "Ved brug af disse værktøjer kan du tilføje, fjerne og ændre layouts (formskemaer og design), der er tilgængelige for dine brugere";
$lang['Styles_addnew_explain'] = "Den følgende liste indeholder alle de designs, der er tilgængelige for de formskemaer, du har. Navnene på listen er endnu ikke installeret i phpBB databasen. For at installere skal du bare klikke på installér linket ved siden af navnet";

$lang['Select_template'] = "Vælg et formskema";

$lang['Style'] = "Layout";
$lang['Template'] = "Formskema";
$lang['Install'] = "Installér";
$lang['Download'] = "Download";

$lang['Edit_theme'] = "Ændre Design";
$lang['Edit_theme_explain'] = "I skemaet nedenunder kan du ændre indstillingerne for det valgte design";

$lang['Create_theme'] = "Opret Design";
$lang['Create_theme_explain'] = "Brug skemaet nedenunder for at oprette et nyt design til det valgte formskema. Når du indtaster farver (for hvilket du skal bruge hexadecimal systemet) skal du ikke bruge # tegnet, f.eks. CCCCCC er brugbart, #CCCCCC er ikke";

$lang['Export_themes'] = "Eksportér Designs";
$lang['Export_explain'] = "I dette panel kan du eksportere design-dataen for et valgt formskema. Vælg formskemaet fra listen nedenunder og databasen vil skabe design konfigurationsfilen og forsøge at gemme den i det valgte formskemas bibliotek. Hvis den ikke kan gemme filen, vil du få muligheden for, at downloade den. For at databasen kan gemme filen, skal du give den lov til at gemme i biblioteket for det valgte formskema. For mere information om dette, se phpBB2 Brugerguiden.";

$lang['Theme_installed'] = "Det valgte design blev succesfuldt installeret.";
$lang['Style_removed'] = "Det valgte design blev succesfuldt fjernet fra databasen. For permanent at fjerne dette design fra dit system, skal du slette designet fra dit formskema bibliotek.";
$lang['Theme_info_saved'] = "Design informationen for det valgte formskema er blevet gemt. Du skal nu give rettighederne tilbage i theme_info.cfg filen (og hvis du ønsker, sætte det valgte formskema bibliotek) til læs-kun (read-only)";
$lang['Theme_updated'] = "Det valgte design er opdateret. Du skal nu eksportere de nye design indstillinger";
$lang['Theme_created'] = "Design oprettet. Du skal nu eksportere designet og design konfigurationsfilen som backup eller brug andensteds.";

$lang['Confirm_delete_style'] = "Er du sikker på, at du vil slette dette layout";

$lang['Download_theme_cfg'] = "Databasen kunne ikke skrive til design informationsfilen. Klik på knappen nedenunder for at downloade filen. Når den er downloadet skal du uploade den i det bibliotek, der indeholder formskema filerne. Du kan så pakke filerne til distribution eller brug andensteds, hvis du ønsker.";
$lang['No_themes'] = "Formskemaet du valgte, har ingen designs. For at oprette et nyt design skal du klikke på Opret Nyt Design i venstre side";
$lang['No_template_dir'] = "Formskema biblioteket kunne ikke åbnes. Det er muligvis ikke læseligt af serveren eller også eksisterer det ikke.";
$lang['Cannot_remove_style'] = "Du kan ikke fjerne layoutet eftersom det på nuværende tidspunkt er det layout, der er standard for forumet. Ændre standardlayoutet og prøv igen.";
$lang['Style_exists'] = "Det valgte layout navn eksisterer allerede, gå venligst tilbage og vælg et andet.";

$lang['Click_return_styleadmin'] = "Klik %sHer%s for at vende tilbage til Layout Administration";

$lang['Theme_settings'] = "Design indstillinger";
$lang['Theme_element'] = "Design Element";
$lang['Simple_name'] = "Simpelt Navn";
$lang['Value'] = "Værdi";
$lang['Save_Settings'] = "Gem Indstillinger";

$lang['Stylesheet'] = "CSS Stylesheet";
$lang['Background_image'] = "Baggrundsbillede";
$lang['Background_color'] = "Baggrundsfarve";
$lang['Theme_name'] = "Design Navn";
$lang['Link_color'] = "Linkfarve";
$lang['Text_color'] = "Tekstfarve";
$lang['VLink_color'] = "Besøgt Linkfarve";
$lang['ALink_color'] = "Aktiv Linkfarve";
$lang['HLink_color'] = "Svæver Linkfarve";
$lang['Tr_color1'] = "Tabelrække Farve 1";
$lang['Tr_color2'] = "Tabelrække Farve 2";
$lang['Tr_color3'] = "Tabelrække Farve 3";
$lang['Tr_class1'] = "Tabelrække Klasse 1";
$lang['Tr_class2'] = "Tabelrække Klasse 2";
$lang['Tr_class3'] = "Tabelrække Klasse 3";
$lang['Th_color1'] = "Tabeltop Farve 1";
$lang['Th_color2'] = "Tabeltop Farve 2";
$lang['Th_color3'] = "Tabeltop Farve 3";
$lang['Th_class1'] = "Tabeltop Klasse 1";
$lang['Th_class2'] = "Tabeltop Klasse 2";
$lang['Th_class3'] = "Tabeltop Klasse 3";
$lang['Td_color1'] = "Tabelcelle Farve 1";
$lang['Td_color2'] = "Tabelcelle Farve 2";
$lang['Td_color3'] = "Tabelcelle Farve 3";
$lang['Td_class1'] = "Tabelcelle Klasse 1";
$lang['Td_class2'] = "Tabelcelle Klasse 2";
$lang['Td_class3'] = "Tabelcelle Klasse 3";
$lang['fontface1'] = "Skrifttype 1";
$lang['fontface2'] = "Skrifttype 2";
$lang['fontface3'] = "Skrifttype 3";
$lang['fontsize1'] = "Skriftstørrelse 1";
$lang['fontsize2'] = "Skriftstørrelse 2";
$lang['fontsize3'] = "Skriftstørrelse 3";
$lang['fontcolor1'] = "Skriftfarve 1";
$lang['fontcolor2'] = "Skriftfarve 2";
$lang['fontcolor3'] = "Skriftfarve 3";
$lang['span_class1'] = "Span Klasse 1";
$lang['span_class2'] = "Span Klasse 2";
$lang['span_class3'] = "Span Klasse 3";
$lang['img_poll_size'] = "Afstemningsbillede Størrelse [px]";
$lang['img_pm_size'] = "Private Beskeder Status Størrelse [px]";


//
// Install Process
//
$lang['Welcome_install'] = "Velkommen til phpBB 2 Installationen";
$lang['Initial_config'] = "Generel Konfiguration";
$lang['DB_config'] = "Database Konfiguration";
$lang['Admin_config'] = "Administrator Konfiguration";
$lang['continue_upgrade'] = "Når du har downloadet din config fil til din harddisk, kan du\"Fortsæt Opgradering\" knap nedenunder for at for at fortsætte opgraderingen. Vent venligst med at uploade config filen indtil opgraderingen er færdig.";
$lang['upgrade_submit'] = "Fortsæt Opgradering";

$lang['Installer_Error'] = "En fejl opstod under installationen";
$lang['Previous_Install'] = "En tidligere installation er fundet";
$lang['Install_db_error'] = "En fejl opstod under forsøget på at opgradere databasen";

$lang['Re_install'] = "Din tidligere installation er stadig aktiv. <br /><br />Hvis du vil geninstallere phpBB 2 skal du klikke på Ja knappen nedenunder. Bemærk venligst, at hvis du gør det, vil det slette alle eksisterende data. Ingen backups vil blive lavet! Det administrator brugernavn og kodeord du har brugt til at logge på forumet, vil blive genskabt efter geninstallationen. Ingen andre indstillinger bliver gemt. <br /><br />Tænk dig om inden du trykker Ja!";

$lang['Inst_Step_0'] = "Tak fordi du valgte phpBB 2. For at færdiggøre installationen skal du indtaste de relevante oplysninger nedenunder. Bemærk venligst, at den database du installer i, skal allerede være oprettet. Hvis du installerer en database, der bruger ODBC, f.eks. MS Access skal du oprette en DSN for den inden du fortsætter.";

$lang['Start_Install'] = "Start Installation";
$lang['Finish_Install'] = "Færdiggør Installation";

$lang['Default_lang'] = "Standardsproget på forumet";
$lang['DB_Host'] = "Database Server Hostname / DSN";
$lang['DB_Name'] = "Navnet på din Database";
$lang['DB_Username'] = "Database Brugernavn";
$lang['DB_Password'] = "Database Kodeord";
$lang['Database'] = "Din Database";
$lang['Install_lang'] = "Vælg Sprog for Installationen";
$lang['dbms'] = "Database Type";
$lang['Table_Prefix'] = "Fornavn for tabeller i databasen";
$lang['Admin_Username'] = "Administrator Brugernavn";
$lang['Admin_Password'] = "Administrator Kodeord";
$lang['Admin_Password_confirm'] = "Administrator Kodeord [ Bekræft ]";

$lang['Inst_Step_2'] = "Dit administrator brugernavn er oprettet.  På nuværende tidspunkt er den basale installation færdig. Du bliver nu sendt videre til en side, hvor du kan administrere din nye installation. Tjek venligst de Generelle Konfigurations indstillinger og lav de ønskede ændringer. Tak fordi du valgte phpBB 2.";

$lang['Unwriteable_config'] = "Der kan i øjeblikket ikke skrives til din config fil. En kopi af config filen vil blive downloadet til dig, når du trykker på knappen nedenunder. Du skal uploade denne fil til det samme bibliotek, som phpBB 2. Når dette er gjort, skal du logge på, med dit administrator navn og kodeord du valgte på det forrige skema, og gå ind på administrator kontrol centeret (et link vil være tilgængeligt nederst på hver side, når først du er logget på) for at tjekke den genrelle konfiguration. Tak fordi du valgte phpBB 2.";
$lang['Download_config'] = "Download Config";

$lang['ftp_choose'] = "Vælg Download Metode";
$lang['ftp_option'] = "<br />Eftersom FTP udvidelser er tilgængelige i denne version af PHP kan du også først prøve automatisk at uploade config filen til det rette bibliotek.";
$lang['ftp_instructs'] = "Du har valgt automatisk at uploade filen til det bibliotek, der indeholder phpBB 2.  Indtast venligst de krævede oplysninger nedenunder, så den automatiske uploading kan foretages. Bemærk at FTP adressen skal være den nøjagtige adresse via FTP til din phpBB2 installation, som hvis du brugte en FTP klient til at uploade filen.";
$lang['ftp_info'] = "Indtast din FTP Information";
$lang['Attempt_ftp'] = "Forsøg at uploade config filen til det passende bibliotek";
$lang['Send_file'] = "Bare send filen til mig og så uploader jeg den manuelt";
$lang['ftp_path'] = "FTP adresse til phpBB 2";
$lang['ftp_username'] = "Dit FTP Brugernavn";
$lang['ftp_password'] = "Dit FTP Kodeord";
$lang['Transfer_config'] = "Start Upload";
$lang['NoFTP_config'] = "Forsøget på automatisk at uploade config filen slog fejl. Download venligst filen og upload den manuelt.";

$lang['Install'] = "Installér";
$lang['Upgrade'] = "Upgradér";


$lang['Install_Method'] = "Vælg installationsmetode";

$lang['Install_No_Ext'] = "Php Konfigurationen på din server understøtter ikke den type af database, som du har valgt"; 

$lang['Install_No_PCRE'] = "PhpBB2 Kræver \"Perl-Compatible Regular Expressions\" Modulet til php, hvilket din php konfiguration ikke lader til at understøtte!"; 

//
// That's all Folks!
// -------------------------------------------------

?>