<?php
/***************************************************************************
 *                          lang_admin.php [portuguese]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id$
 *
 ****************************************************************************/

 /****************************************************************************
 * Translation by:
 * LuizCB (Pincel) LuizCB@pincel.net || http://pincel.net
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
$lang['General'] = "Admin Geral";
$lang['Users'] = "Admin de Utilizadores";
$lang['Groups'] = "Admin de Grupo";
$lang['Forums'] = "Admin de Fórum";
$lang['Styles'] = "Admin de Estilos";

$lang['Configuration'] = "Configuração";
$lang['Permissions'] = "Permissões";
$lang['Manage'] = "Gerência";
$lang['Disallow'] = "Impedir Nomes";
$lang['Prune'] = "Desbastar";
$lang['Mass_Email'] = "Email Maciço";
$lang['Ranks'] = "Escalões";
$lang['Smilies'] = "Smileys";
$lang['Ban_Management'] = "Controle de Ban";
$lang['Word_Censor'] = "Filtros de Palavras";
$lang['Export'] = "Exportar";
$lang['Create_new'] = "Criar";
$lang['Add_new'] = "Adicionar";
$lang['Backup_DB'] = "Copiar a Base de Dados";
$lang['Restore_DB'] = "Repor a Base de Dados";


//
// Index
//
$lang['Admin'] = "Administração";
$lang['Not_admin'] = "Você não está autorizado a administrar este painel";
$lang['Welcome_phpBB'] = "Bem-vindo a phpBB";
$lang['Admin_intro'] = "Obrigado por escolher phpBB como seu fórum. Este painel fornece-lhe um aspecto estatístico global dos seus fóruns. Poderá voltar a esta página premindo <u>Índice de Admin</u> no painel esquerdo. Para voltar ao Índice global dos fóruns prima no logo phpBB igualmente no painel esquerdo. Os outros atalhos no lado esquerdo proporcionam-lhe controlar todos os aspectos do fórum, cada um com instrucções de uso.";
$lang['Main_index'] = "Índice do Fórum";
$lang['Forum_stats'] = "Estatísticas do Fórum";
$lang['Admin_Index'] = "Índice de Admin";
$lang['Preview_forum'] = "Rever Fórum";

$lang['Click_return_admin_index'] = "Premir %sAqui%s para voltar ao Índice de Admin";

$lang['Statistic'] = "Estatística";
$lang['Value'] = "Valor";
$lang['Number_posts'] = "Númeo de Mensagens";
$lang['Posts_per_day'] = "Mensagens por Dia";
$lang['Number_topics'] = "Número de Tópicos";
$lang['Topics_per_day'] = "Tópicos por Dia";
$lang['Number_users'] = "Número de Utilizadores";
$lang['Users_per_day'] = "Utilizadores por Dia";
$lang['Board_started'] = "Início dos Fóruns";
$lang['Avatar_dir_size'] = "Tamanho da pasta de Avatars";
$lang['Database_size'] = "Tamanho da Base de Dados";
$lang['Gzip_compression'] ="Compressão Gzip";
$lang['Not_available'] = "Não Disponível";

$lang['ON'] = "Activo"; // This is for GZip compression
$lang['OFF'] = "Inactivo"; 


//
// DB Utils
//
$lang['Database_Utilities'] = "Utilitários da Base de Dados";

$lang['Restore'] = "Repor";
$lang['Backup'] = "Copiar";
$lang['Restore_explain'] = "Isto efectua uma reposição de todas as tabelas do phpBB a partir de um ficheiro previamente guardado. Se o seu server permite poderá colocar aqui um  ficheiro de texto comprimido em gzip que será automaticamente descomprimido. <b>ATENÇÃO</b> Isto sobrepõe a informação existente. A operação poderá levar muito tempo a processar-se, por favor não mude esta página para outra.";
$lang['Backup_explain'] = "Pode efectuar aqui uma cópia de toda a informação do seu phpBB. Se possui tabelas adicionais na sua base de dados que também pretenda copiar por favor escreva os seus nomes separados por vírgulas na caixa de texto de Tabelas Adicionais abaixo. Caso o seu server permita pode comprimir o ficheiro em gzip de forma a reduzir o seu tamanho antes de o baixar.";

$lang['Backup_options'] = "Opções de Cópia";
$lang['Start_backup'] = "Iniciar a Cópia";
$lang['Full_backup'] = "Cópia Total";
$lang['Structure_backup'] = "Cópia apenas da Estrutura";
$lang['Data_backup'] = "Cópia apenas dos Dados";
$lang['Additional_tables'] = "Tabelas Adicionais";
$lang['Gzip_compress'] = "Ficheiro comprimido em Gzip";
$lang['Select_file'] = "Selecionar um ficheiro";
$lang['Start_Restore'] = "Iniciar a Reposição";

$lang['Restore_success'] = "A Base de Dados foi reposta com sucesso.<br /><br />Os seus fóruns deveráo voltar agora ao estado em que se encontravam na altura da cópia.";
$lang['Backup_download'] = "A cópia deverá iniciar em breve, por favor aguarde até que comece";
$lang['Backups_not_supported'] = "O seu sistema de Base de Dados náo permite presentemente efectuar cópias de Dados";

$lang['Restore_Error_uploading'] = "Erro a repor o ficheiro";
$lang['Restore_Error_filename'] = "Problema no nome do ficheiro, por favor tentar um alternativo";
$lang['Restore_Error_decompress'] = "Náo é possível descomprimir um ficheiro gzip file, por favor repor uma versão em texto";
$lang['Restore_Error_no_file'] = "Nenhum ficheiro foi reposto";


//
// Auth pages
//
$lang['Select_a_User'] = "Selecionar um Utilizador";
$lang['Select_a_Group'] = "Selecionar um Grupo";
$lang['Select_a_Forum'] = "Selecionar um Fórum";
$lang['Auth_Control_User'] = "Controle de Permissões de Utilizador"; 
$lang['Auth_Control_Group'] = "Controle de Permissões de Grupo"; 
$lang['Auth_Control_Forum'] = "Controle de Permissões de Fórum"; 
$lang['Look_up_User'] = "Verificar o Utilizador"; 
$lang['Look_up_Group'] = "Verificar o Grupo"; 
$lang['Look_up_Forum'] = "Verificar o Fórum"; 

$lang['Group_auth_explain'] = "Pode alterar aqui as permissões e estatuto de moderador de cada Grupo de Utilizadores. Não esquecer que quando muda as permissões de um grupo essas permissões em particular poderão ainda permitir que o utilizador entre nos fóruns, etc. Caso isso aconteça será devidamente avisado.";
$lang['User_auth_explain'] = "Pode alterar aqui as permissões e estatuto de moderador de cada utilizador individualmente. Não esquecer que quando muda as permissões de um utilizador essas permissões de grupo poderão ainda permitir que o utilizador entre nos fóruns, etc.  Caso isso aconteça será devidamente avisado.";
$lang['Forum_auth_explain'] = "Pode alterar aqui os níveis de permissões para cada fórum. Existe tanto um método simples como um avançado, oferecendo o avançado um maior controle de configuração. Ter em mente que ao alterar os níveis de permissões irá afectar automaticamente a forma em como cada utilizador possa efectuar as várias operações nesse fórum.";

$lang['Simple_mode'] = "Modo Simples";
$lang['Advanced_mode'] = "Modo Avançado";
$lang['Moderator_status'] = "Estatuto de Moderador";

$lang['Allowed_Access'] = "Acesso Permitido";
$lang['Disallowed_Access'] = "Acesso Impedido";
$lang['Is_Moderator'] = "É Moderador";
$lang['Not_Moderator'] = "Não é Moderador";

$lang['Conflict_warning'] = "Aviso de Conflito de Autorização";
$lang['Conflict_access_userauth'] = "Este utilizador ainda possui direitos de acesso a este fórum através do seu registo no Grupo. Você talvez queira alterar as permissões de Grupo ou remover este utilizador desse Grupo para bloquear por completo os seus direitos de acesso. As permissões dos Grupos (e os fóruns envolvidos) estão indicados abaixo.";
$lang['Conflict_mod_userauth'] = "Este utilizador ainda possui direitos de modrador a este fórum através do seu registo no Grupo. Você talvez queira alterar as permissões de Grupo ou remover este utilizador desse Grupo para bloquear por completo os seus direitos de acesso. As permissões dos Grupos (e os fóruns envolvidos) estão indicados abaixo.";

$lang['Conflict_access_groupauth'] = "O utilizador seguinte (ou utilizadores) ainda possuem direitos de acesso a este fórum via a sua configuração de permissões individuais. Você talvez queira alterar as permissões de utilizador para bloquear por completo os seus direitos de acesso. As permissões dos utilizadores (e os fóruns envolvidos) estão indicados abaixo.";

$lang['Conflict_mod_groupauth'] = "O utilizador seguinte (ou utilizadores) ainda possuem direitos de moderador a este fórum via a sua configuração de permissões individuais. Você talvez queira alterar as permissões de utilizador para bloquear por completo os seus direitos de acesso. As permissões dos utilizadores (e os fóruns envolvidos) estão indicados abaixo.";

$lang['Public'] = "Público";
$lang['Private'] = "Privado";
$lang['Registered'] = "Registado";
$lang['Administrators'] = "Administradores";
$lang['Hidden'] = "Invisível";

$lang['View'] = "Verificar";
$lang['Read'] = "Ler";
$lang['Post'] = "Afixar";
$lang['Reply'] = "Responder";
$lang['Edit'] = "Editar";
$lang['Delete'] = "Remover";
$lang['Sticky'] = "Inamovível";
$lang['Announce'] = "Anunciar"; 
$lang['Vote'] = "Votar";
$lang['Pollcreate'] = "Criar Votação";

$lang['Permissions'] = "Permissões";
$lang['Simple_Permission'] = "Permissão Simples";

$lang['User_Level'] = "Nível de Utilizador"; 
$lang['Auth_User'] = "Utilizador";
$lang['Auth_Admin'] = "Administrador";
$lang['Group_memberships'] = "Membros de Grupos de Utilizadores";
$lang['Usergroup_members'] = "Este Grupo tem os seguintes membros";

$lang['Forum_auth_updated'] = "Permissões do Fórum actualizadas";
$lang['User_auth_updated'] = "Permissões d~e Utilizador actualizadas";
$lang['Group_auth_updated'] = "Permissões de Grupo actualizadas";

$lang['Auth_updated'] = "As permissões foram actualizadas";
$lang['Click_return_userauth'] = "Premir %sAqui%s para voltar a Permissões de Utilizador";
$lang['Click_return_groupauth'] = "Click %sAqui%s para voltar a Permissões de Grupo";
$lang['Click_return_forumauth'] = "Click %sHere%s para voltar a Permissões de Fórum";


//
// Banning
//
$lang['Ban_control'] = "Controle de Expulsões";
$lang['Ban_explain'] = "Pode controlar aqui a expulsão de utilizadores. Pode ser expulso um utilizador específico ou um grupo de endereços de IP ou hostnames. Estes métodos impedem um utilizador de alcançar sequer a página inicial dos fóruns. Para evitar que um utilizador se registe com um nome diferente pode ser também especificado um endereço de email a ser banido. De notar que a banir um email só por si não impede um utilizador de se ligar ou colocar mensagens nos fóruns. Para tal deve ser empregue um dos métodos descritos inicialmente.";
$lang['Ban_explain_warn'] = "De notar que ao especificar um grupo de endereços de IP resulta em todos os endereços incluidos nessa seleção, ou seja desde o primeiro IP até ao último, serem adicionados á lista de IPs banidos. O sistema de phpBB tenta minimizar o número de endereços a adicionar á base de dados empregando automaticamente filtros de seleção sempre que seja possível aplicar. Se você realmente tem que entrar um grupo de IPs tente fazê-lo de forma a abranger uma quantidade reduzida de endereços ou, melhor ainda, use endereços específicos.";

$lang['Select_username'] = "Selecionar um Utilizador";
$lang['Select_ip'] = "Selecionar um IP";
$lang['Select_email'] = "Selecionar um endereço de Email";

$lang['Ban_username'] = "Expulsar um ou mais utilizadores específicos";
$lang['Ban_username_explain'] = "Pode expulsar utilizadores múltiplos de uma vez usando a combinação apropriada de teclas e rato para o seu computador ou browser.";

$lang['Ban_IP'] = "Expulsar um ou mais endereços de IP ou hostnames";
$lang['IP_hostname'] = "Endereços de IP ou hostnames";
$lang['Ban_IP_explain'] = "Para especificar vários IPs e hostnames separa-los com vírgulas. pare especificar um grupo de endereços de IP separar o início do fim com um traço (-). Pode também usar asteriscos (*)";

$lang['Ban_email'] = "Expulsar um ou mais endereços de email";
$lang['Ban_email_explain'] = "To specify more than one email address separate them with commas. To specify a wildcard username use *, for example *@hotmail.com";

$lang['Unban_username'] = "Un-ban one more specific users";
$lang['Unban_username_explain'] = "You can unban multiple users in one go using the appropriate combination of mouse and keyboard for your computer and browser";

$lang['Unban_IP'] = "Un-ban one or more IP addresses";
$lang['Unban_IP_explain'] = "You can unban multiple IP addresses in one go using the appropriate combination of mouse and keyboard for your computer and browser";

$lang['Unban_email'] = "Un-ban one or more email addresses";
$lang['Unban_email_explain'] = "You can unban multiple email addresses in one go using the appropriate combination of mouse and keyboard for your computer and browser";

$lang['No_banned_users'] = "No banned usernames";
$lang['No_banned_ip'] = "No banned IP addresses";
$lang['No_banned_email'] = "No banned email addresses";

$lang['Ban_update_sucessful'] = "The banlist has been updated successfully";
$lang['Click_return_banadmin'] = "Click %sHere%s to return to Ban Control";


//
// Configuration
//
$lang['General_Config'] = "General Configuration";
$lang['Config_explain'] = "The form below will allow you to customize all the general board options. For User and Forum configurations use the related links on the left hand side.";

$lang['Click_return_config'] = "Click %sHere%s to return to General Configuration";

$lang['General_settings'] = "General Board Settings";
$lang['Site_name'] = "Nome do local do Fórum";
$lang['Site_desc'] = "Descrição";
$lang['Board_disable'] = "Desactivar";
$lang['Board_disable_explain'] = "Isto torna os fóruns inacessíveis a utilizadores. NÃO SE DESLIGUE - após desactivar os fóruns neste local não conseguirá voltar a entrar!";
$lang['Acct_activation'] = "Ligar a função de activação de registo";
$lang['Acc_None'] = "Nunca"; // These three entries are the type of activation
$lang['Acc_User'] = "Utilizadores";
$lang['Acc_Admin'] = "Administradores";

$lang['Abilities_settings'] = "Configuração básica de utilizadores e fórum";
$lang['Max_poll_options'] = "Número máximo de opções em votação";
$lang['Flood_Interval'] = "Intervalo de 'Flood'";
$lang['Flood_Interval_explain'] = "Tempo em segundos que um utilizador deva aguardar entre mensagens"; 
$lang['Board_email_form'] = "Email de utilizadores via fórum";
$lang['Board_email_form_explain'] = "Função que permite utilizadores enviar email a outros via fórum";
$lang['Topics_per_page'] = "Tópicos por página";
$lang['Posts_per_page'] = "Mensagens por página";
$lang['Hot_threshold'] = "Mensagens por assunto popular";
$lang['Default_style'] = "Estilo básico";
$lang['Override_style'] = "Repor estilo do utilizador";
$lang['Override_style_explain'] = "Repõe o estilo escolhido pelo utilizador pelo básico";
$lang['Default_language'] = "Língua básica";
$lang['Date_format'] = "Formato de Data";
$lang['System_timezone'] = "Fuso Horário do sistema";
$lang['Enable_gzip'] = "Activar compressão por GZip";
$lang['Enable_prune'] = "Activar Desbastar Fórum";
$lang['Allow_HTML'] = "Permitir HTML";
$lang['Allow_BBCode'] = "Permitir BBCode";
$lang['Allowed_tags'] = "Códigos de HTML permitidos";
$lang['Allowed_tags_explain'] = "Separar os códigos com vírgulas";
$lang['Allow_smilies'] = "Permitir Smileys";
$lang['Smilies_path'] = "'Path' dos Smileys";
$lang['Smilies_path_explain'] = "'Path'para a o local onde se encontram os Smileys na directoria do phpBB, ou seja, images/smileys";
$lang['Allow_sig'] = "Permitir Assinaturas";
$lang['Max_sig_length'] = "Extensão máxima da assinatura";
$lang['Max_sig_length_explain'] = "Número máximo de caracteres na assinatura do utilizador";
$lang['Allow_name_change'] = "Permitir mudança de Nome de Utilizador";

$lang['Avatar_settings'] = "Configuração de Avatars";
$lang['Allow_local'] = "Activar galeria de Avatars";
$lang['Allow_remote'] = "Permitir Avatars remotos";
$lang['Allow_remote_explain'] = "Avatars ligados a partir de outra página no WWW";
$lang['Allow_upload'] = "Permitir carregar Avatars";
$lang['Max_filesize'] = "Tamanho máximo do arquivo de Avatar";
$lang['Max_filesize_explain'] = "Para Avatars carregados";
$lang['Max_avatar_size'] = "Dimensões máximas dos Avatars";
$lang['Max_avatar_size_explain'] = "(Altura x Largura em pixels)";
$lang['Avatar_storage_path'] = "'Path' de armazenamento dos Avatars";
$lang['Avatar_storage_path_explain'] = "'Path'para o local onde se encontram os Avatars na directoria do phpBB, ou seja, images/avatars";
$lang['Avatar_gallery_path'] = "'Path' a Galeria dos Avatars";
$lang['Avatar_gallery_path_explain'] = "'Path' para o local onde se encontram os as imagens armazenadas na directoria do phpBB, ou seja, images/avatars/gallery";

$lang['COPPA_settings'] = "Configuração de COPPA";
$lang['COPPA_fax'] = "Fax para COPPA";
$lang['COPPA_mail'] = "Endereço de email para COPPA";
$lang['COPPA_mail_explain'] = "Este é um endereço da lista de correspondência para a qual os pais enviam os formulários de registo de COPPA";

$lang['Email_settings'] = "Configuração de Email";
$lang['Admin_email'] = "Endereço de Email do Admin";
$lang['Email_sig'] = "Assinatura do Email";
$lang['Email_sig_explain'] = "Este texto será anexo a todos os emails enviados pelo fórum";
$lang['Use_SMTP'] = "Usar Servidor de SMTP para o email";
$lang['Use_SMTP_explain'] = "Responder Sim caso queira ou tenha que enviar Email via um dado server no lugar da função local para esse efeito";
$lang['SMTP_server'] = "Endereço do servidor para SMTP";

$lang['Disable_privmsg'] = "Mensagens Privadas";
$lang['Inbox_limits'] = "Número total de mensagens na Caixa de Entrada";
$lang['Sentbox_limits'] = "Número total de mensagens na Caixa de Saída";
$lang['Savebox_limits'] = "Número total de mensagens na Caixa de Reserva Savebox";

$lang['Cookie_settings'] = "Configuração dos 'Cookies'"; 
$lang['Cookie_settings_explain'] = "Isto define como deverão ser enviados os 'Cookies' para os 'browsers'. Na maior parte dos casos a configuração básica á suficiente. Se pretende mudar faça-o com cuidado, uma configuração incorrecta pode impedir os utilizadores de se ligar ao fórum.";
$lang['Cookie_name'] = "Nome do Cookie";
$lang['Cookie_domain'] = "Domínio do Cookie";
$lang['Cookie_path'] = "'Path' do Cookie";
$lang['Session_length'] = "Tempo da sessão [ segundos ]";
$lang['Cookie_secure'] = "'Cookie secure' [ https ]";


//
// Forum Management
//
$lang['Forum_admin'] = "Administração do Fórum";
$lang['Forum_admin_explain'] = "A partir deste painel pode adicionar, remover, editar, reordenar e sincronizar as categorias e fóruns";
$lang['Edit_forum'] = "Editar fórum";
$lang['Create_forum'] = "Criar fórum novo";
$lang['Create_category'] = "Criar categoria nova";
$lang['Remove'] = "Remover";
$lang['Action'] = "Acção";
$lang['Update_order'] = "Actualizar a Ordem";
$lang['Config_updated'] = "Configuração do Fórum actualizada com sucesso";
$lang['Edit'] = "Editar";
$lang['Delete'] = "Remover";
$lang['Move_up'] = "Mover para cima";
$lang['Move_down'] = "Mover para baixo";
$lang['Resync'] = "Sincronizar";
$lang['No_mode'] = "Não foi configurado nenhum modo";
$lang['Forum_edit_delete_explain'] = "O formulário abaixo permite-lhe modificar todas as opções gerais do fórum. Usar os atalhos no painel do lado esquerdo para configuração específica de utilizadores ou algum fórum.";

$lang['Move_contents'] = "Mover todo o conteúdo";
$lang['Forum_delete'] = "Remover Fórum";
$lang['Forum_delete_explain'] = "O formulário abaixo permite-lhe remover um fórum (ou categoria) e decidir onde pretende colocar todos os tópicos (ou fóruns) existentes.";

$lang['Forum_settings'] = "Configuração Geral dos Fóruns";
$lang['Forum_name'] = "Nome do Fórum";
$lang['Forum_desc'] = "Descrição";
$lang['Forum_status'] = "Estado";
$lang['Forum_pruning'] = "Auto-desbastar";

$lang['prune_freq'] = 'Verificar a idade dos tópicos em cada';
$lang['prune_days'] = "Remover tópicos que não tenham tido respostas em";
$lang['Set_prune_data'] = "Activou a função para desbastar o fórum automaticamente mas não especificou a frequência ou número de dias em que o mesmo deve ser feito. Voltar atrás e especificar esse valor";

$lang['Move_and_Delete'] = "Mover e Remover";

$lang['Delete_all_posts'] = "Remover todas as mensagens";
$lang['Nowhere_to_move'] = "Não há local para onde mover";

$lang['Edit_Category'] = "Editar Categoria";
$lang['Edit_Category_explain'] = "Usar este formulário para mudar o nome da categoria.";

$lang['Forums_updated'] = "Informação de Fórum e Categoria actualizada com sucesso ";

$lang['Must_delete_forums'] = "Necessita remover todos os fóruns antes de remover esta categoria";

$lang['Click_return_forumadmin'] = "Premir %sAqui%s para voltar a Administração de Fórum";


//
// Smiley Management
//
$lang['smiley_title'] = "Utilitário para Editar Smiles";
$lang['smile_desc'] = "Pode adicionar, remover e editar neste painel as emoções ou smileys que os utilizadores poderão usar nas suas mensagens, tanto públicas como privadas.";

$lang['smiley_config'] = "Administração de Smileys";
$lang['smiley_code'] = "Código de Smiley";
$lang['smiley_url'] = "Ficheiro da imagem do Smiley";
$lang['smiley_emot'] = "Emoção do Smiley";
$lang['smile_add'] = "Adicionar um Smiley novo";
$lang['Smile'] = "Smile";
$lang['Emotion'] = "Emoção";

$lang['Select_pak'] = "Select um ficheiro com o 'pacote' (.pak)";
$lang['replace_existing'] = "Repor o Smiley existente";
$lang['keep_existing'] = "Conservar o Smiley existente";
$lang['smiley_import_inst'] = "Deverá fazer unzip ao pacote dos smiles e colocar todos os ficheiros na pasta apropriada de Smiley na instalação do phpBB 2.  Selecionar depois a informação correcta neste formulário para importar o pacote de smiles.";
$lang['smiley_import'] = "Importar pacote de Smiley";
$lang['choose_smile_pak'] = "Escolher um ficheiro do pacote de Smile (.pak)";
$lang['import'] = "Importar Smileys";
$lang['smile_conflicts'] = "O que se deva fazer em caso de conflitos";
$lang['del_existing_smileys'] = "Remover os smileys existentes antes de importar";
$lang['import_smile_pack'] = "Importar o pacote de Smiley";
$lang['export_smile_pack'] = "Criar pacote de Smiley";
$lang['export_smiles'] = "Para criar um pacote de smiley a partir dos correntemente instalados, premir %sAqui%s para fazer o download do pacote smiles.pak. Dar o nome a este ficheiro apropriadamente, mantendo a extensão .pak.  Criar então um ficheiro zipcontendo todos as suas imagens de smiley mais este ficheirop de configuração (.pak).";

$lang['smiley_add_success'] = "O Smiley foi adicionado com sucesso";
$lang['smiley_edit_success'] = "O Smiley foi actualizado com sucesso";
$lang['smiley_import_success'] = "O pacote de Smiley foi importado com sucesso!";
$lang['smiley_del_success'] = "O Smiley foi removido com sucesso";
$lang['Click_return_smileadmin'] = "Premir %sAqui%s para voltar a Administração de Smileys";


//
// User Management
//
$lang['User_admin'] = "Adminstração de Utilizadores";
$lang['User_admin_explain'] = "Poderá mudar aqui a informação dos seus utilizadores além de algumas opções específicas. Para modificar as permissões de utilizadores usar o painel de Permissões para Utilizadores e Grupos.";

$lang['Look_up_user'] = "Verificar um Utilizador";

$lang['Admin_user_fail'] = "Não foi possível actualizar o perfil de utilizadores.";
$lang['Admin_user_updated'] = "O perfil de utilizadores foi actualizado com sucesso.";
$lang['Click_return_useradmin'] = "Premir %sAqui%s Para voltar a Adminstração de Utilizadores";

$lang['User_delete'] = "Remover este Utilizador";
$lang['User_delete_explain'] = "Premir aqui para remover este utilizador, esta operação tem efeitos permanentes.";
$lang['User_deleted'] = "Utilizador removido com sucesso.";

$lang['User_status'] = "Utilizador está activo";
$lang['User_allowpm'] = "Não pode enviar Mensagens Privadas";
$lang['User_allowavatar'] = "Pode mostrar Avatar";

$lang['Admin_avatar_explain'] = "Aqui poderá ver e remover o Avatar corrente do utilizador.";

$lang['User_special'] = "Entradas especiais apenas para admin";
$lang['User_special_explain'] = "Estes parâmetors não podem ser modificados por utilizadores. Pode especificar aqui o seu estado bem como outras opções que não são dadas aos utilizadores.";


//
// Group Management
//
$lang['Group_administration'] = "Administração de Grupos";
$lang['Group_admin_explain'] = "A partir deste painel pode administrar todos os Grupos de Utilizadores, podendo criar, editar e remover grupos. Pode escolher moderadores, mudar o estado de aberto/encerrado bem como estipular um nome para grupo e respectiva descrição";
$lang['Error_updating_groups'] = "Houve um erro ao actualizar os grupos";
$lang['Updated_group'] = "O grupo foi actualizado com sucesso";
$lang['Added_new_group'] = "O novo grupo foi criado com sucesso";
$lang['Deleted_group'] = "O grupo foi removido com sucesso";
$lang['New_group'] = "Criar um Grupo";
$lang['Edit_group'] = "Editar o Grupo";
$lang['group_name'] = "Nome do Grupo";
$lang['group_description'] = "Descrição do Grupo";
$lang['group_moderator'] = "Moderador do Grupo";
$lang['group_status'] = "Estado do Grupo";
$lang['group_open'] = "Aberto";
$lang['group_closed'] = "Encerrado";
$lang['group_hidden'] = "Invisível";
$lang['group_delete'] = "Remover grupo";
$lang['group_delete_check'] = "Remover este grupo";
$lang['submit_group_changes'] = "Submeter as mudanças";
$lang['reset_group_changes'] = "Voltar as mudanças aos seus valores iniciais";
$lang['No_group_name'] = "Deve ser especificado um nome para este grupo";
$lang['No_group_moderator'] = "Deve ser especificado um moderador para este grupo";
$lang['No_group_mode'] = "Deve ser especificado um modo para este grupo, aberto ou encerrado";
$lang['delete_group_moderator'] = "Remover o moderador antigo do grupo?";
$lang['delete_moderator_explain'] = "Se está a mudar o moderador do grupo assinale aqui para remover o moderador anterior.  Caso contrário nao assinale e o utilizador passará a ser um membro normal do grupo.";
$lang['Click_return_groupsadmin'] = "Premir %sAqui%s para voltar á Administração de Grupos.";
$lang['Select_group'] = "Selecionar um Grupo";
$lang['Look_up_group'] = "Verificar o Grupo";


//
// Prune Administration
//
$lang['Forum_Prune'] = "Desbastar Fórum";
$lang['Forum_Prune_explain'] = "Esta operação removerá qualquer tópico que não possua resposta dentro do limite de dias especificado. Se não for especificado um número de dias todos os tópicos serão removidos. Isto não remove tópicos com uma votação activa nem remove anúncios. Terá que remover esses tópicos manualmente.";
$lang['Do_Prune'] = "Desbastar";
$lang['All_Forums'] = "Todos os Fóruns";
$lang['Prune_topics_not_posted'] = "Remover tópicos sem resposta durante um período de ";
$lang['Topics_pruned'] = "Tópicos Removidos";
$lang['Posts_pruned'] = "Mensagens Removidas";
$lang['Prune_success'] = "Desbaste de fóruns concluído com sucesso";


//
// Word censor
//
$lang['Words_title'] = "Censura de Palavras";
$lang['Words_explain'] = "Deste painel de controle pode adicionar, editar e remover palavras que serão automaticamente censuradas nos fóruns. Adicionalmente, as pessoas não poderão registar os seus Nomes de Utilizadores caso contenham essas palavras. Podem ser usados asteriscos (*) aumentando as possibilidades de abranger variantes da mesma palavra. Por exemplo, *testa* abrangerá detestável, testa* abrangerá testando, *testa abrangerá detesta.";
$lang['Word'] = "Palavra";
$lang['Edit_word_censor'] = "Editar a palavra a censurar";
$lang['Replacement'] = "Substituição";
$lang['Add_new_word'] = "Adicionar nova palavra";
$lang['Update_word'] = "Actualizar palavra a censurar";

$lang['Must_enter_word'] = "Deverá escrever a palavra e o que a vá substituir";
$lang['No_word_selected'] = "Não foi escolhida palavra para editar";

$lang['Word_updated'] = "A palavra censurada foi actualizada com sucesso";
$lang['Word_added'] = "A palavra a censurar foi adicionada com sucesso";
$lang['Word_removed'] = "A palavra censurada foi removida com sucesso";

$lang['Click_return_wordadmin'] = "Premir %sAqui%s para voltar á Administração de Censura de Palavras";


//
// Mass Email
//
$lang['Mass_email_explain'] = "Pode enviar aqui uma mensagem de email para todos os utilizadores, ou todos os utilizadores membros de um grupo específico.  Para tal, um email será enviado a partir do endereço de email administrativo configurado com uma cópia para todos os destinatários. Se está a enviar o email para um número elevado de pessoas por favor ter paciência após submeter e não parar a página a meio. É normal num envio de email em massa demorar um pouco mas você será notificado quando o processo estiver concluído";
$lang['Compose'] = "Compor"; 

$lang['Recipients'] = "Destinatários"; 
$lang['All_users'] = "Todos os utilizadores";

$lang['Email_successfull'] = "A sua mensagem foi enviada";
$lang['Click_return_massemail'] = "Premir %sAqui%s para voltar ao formulário de email maciço";


//
// Ranks admin
//
$lang['Ranks_title'] = "Administração de Escalões";
$lang['Ranks_explain'] = "Usando este formulário poderá adicionar, editar, ver e remover escalões. Poderá também criar escalões á sua maneira podendo os mesmos ser aplicados a um utilizador via painel de Admin de Utilizadores";

$lang['Add_new_rank'] = "Adicionar um Escalão novo";

$lang['Rank_title'] = "Título do Escalão";
$lang['Rank_special'] = "Escalão Especial";
$lang['Rank_minimum'] = "Número Mínimo de Mensagens";
$lang['Rank_maximum'] = "Número Máximo de Mensagens";
$lang['Rank_image'] = "Imagem do Escalão (Relativamente ao path do phpBB2)";
$lang['Rank_image_explain'] = "Usar isto para definir uma pequena imagem associada ao escalão";

$lang['Must_select_rank'] = "Deve escolher um Escalão";
$lang['No_assigned_rank'] = "Não foi especificado nenhum Escalão Especial";

$lang['Rank_updated'] = "O escalão foi actualizado com sucesso";
$lang['Rank_added'] = "O escalão foi adicionado com sucesso";
$lang['Rank_removed'] = "O escalão foi removido com sucesso";

$lang['Click_return_rankadmin'] = "Premir %sAqui%s para voltar a Administração de Escalões";


//
// Disallow Username Admin
//
$lang['Disallow_control'] = "Controle de Nomes de Utilizadores Proibidos";
$lang['Disallow_explain'] = "Poderá controlar aqui nomes de utilizadores que não sejam permitidos nos fóruns.  Podem ser empregues asteriscos '*'.  De notar que não podem ser especificados Nomes de Utilizadores que já se encontrem registados, devendo primeiro removê-los e então especificá-los aqui para n]ao mais serem permitidos";

$lang['Delete_disallow'] = "Remover";
$lang['Delete_disallow_title'] = "Remover um Nome de Utilizador Proibido";
$lang['Delete_disallow_explain'] = "Pode remover um nome proibido escolhendo um Nome de Utilizador nesta lista e carregando em Submeter";

$lang['Add_disallow'] = "Adicionar";
$lang['Add_disallow_title'] = "Adicionar um Nome de Utilizador Proibido";
$lang['Add_disallow_explain'] = "Pode especificar um nome proibido usando asteriscos '*' para abranger qualquer caracter";

$lang['No_disallowed'] = "Não há Nomes de Utilizadores Proibidos";

$lang['Disallowed_deleted'] = "O Nome de Utilizador Proibido foi removido com sucesso";
$lang['Disallow_successful'] = "O Nome de Utilizador Proibido foi adicionado com sucesso";
$lang['Disallowed_already'] = "O nome que especificou não pode ser proibido. Pode acontecer já existir na lista, existir na lista de palavras censuradas ou um existe um Nome de Utilizador registado com esse nome";

$lang['Click_return_disallowadmin'] = "Premir %sAqui%s para voltar a Administração de Nomes de Utilizadores Proibidos";


//
// Styles Admin
//
$lang['Styles_admin'] = "Administração de Estilos";
$lang['Styles_explain'] = "Usando este painel poderá adicionar, remover e administrar estilos (Modelos e Temas) disponíveis para os seus utilizadores";
$lang['Styles_addnew_explain'] = "A lista seguinte contém os Temas para os Modelos que presentemente possúi. Os itens na lista náo foram ainda instalados na base de dados do phpBB. Para instalar um tema específico premir o atalho assinalado com 'Instalar' ao lado desse item";

$lang['Select_template'] = "Selecionar um Modelo";

$lang['Style'] = "Estilo";
$lang['Template'] = "Modelo";
$lang['Install'] = "Instalar";
$lang['Download'] = "Download";

$lang['Edit_theme'] = "Editar Tema";
$lang['Edit_theme_explain'] = "Configurar o tema selecionado no formulário abaixo";

$lang['Create_theme'] = "Criar Tema";
$lang['Create_theme_explain'] = "Use o formulário abaixo para criar um Tema novo para o Modelo selecionado. Quando aplicar cores (que devem ser escritas num formato hexadecimal) não deverá incluir o # inicial, ou seja, CCCCCC é a forma correcta, #CCCCCC é incorrecto";

$lang['Export_themes'] = "Exportar Temas";
$lang['Export_explain'] = "Neste painel poderá exportar a informação do Tema para um Modelo selecionado. Selecione um Modelo a partir da lista abaixo e será criado um ficheiro de configuração do tema que irá ser guardado e instalado na pasta do Modelo selecionado. Caso não for possível guardar o ficheiro por si próprio será dada a opção para ser feito o download do mesmo. De forma a que seja guardado devem ser dadas permissões de escrita ao servidor de WEB para a pasta do Modelo selecionado. Para mais informação sobre esta operação ver o 'phpBB 2 users guide'.";

$lang['Theme_installed'] = "O tema selecionado foi instalado com sucesso";
$lang['Style_removed'] = "O estilo selecionado foi removido da base de dados. Para remover completamente este estilo do seu sistema deve apagar o estilo apropriado na pasta dos Modelos.";
$lang['Theme_info_saved'] = "A informação do tema para o Modelo selecionado foi guardada. Você deve agora mudar as permissões para 'read-only'mo ficheiro theme_info.cfg (e caso aplicável na pasta de Modelos)";
$lang['Theme_updated'] = "O tema selecionado foi actualizado. Você deve agora exportar a nova configuração do tema";
$lang['Theme_created'] = "Tema criado. Você deve agora exportar o tema para o ficheiro de configuração do tema como segurança ou usar noutro local";

$lang['Confirm_delete_style'] = "Tem a certeza que quer remover este estilo?";

$lang['Download_theme_cfg'] = "Não foi possível escrever o ficheiro de informação do tema. Premir o botão abaixo para fazer o 'download' deste ficheiro com o seu 'browser'. Logo que termine o download poderá trasferir o ficheiro para a pasta contendo os ficheiros do Modelo. Pode depois arrumar os ficheiros para distribuição ou usar noutro local, se assim o pretender";
$lang['No_themes'] = "O Modelo que selecionou nao possúi temas anexos. Para criar um tema novo premir em Criar no painel do lado esquerdo";
$lang['No_template_dir'] = "Não foi possível abrir a pasta de Modelos. Pode ser que não haja possibilidade de ser lido pelo servidor de Web ou a pasta não exista";
$lang['Cannot_remove_style'] = "Não pode remover o estilo selecionado porque é presentemente o estilo básico do fórum. Mudar o estilo básico e tentar novamente.";
$lang['Style_exists'] = "O nome para o estilo que selecionou já existe, voltar atrás e escolher um nome diferente.";

$lang['Click_return_styleadmin'] = "Premir %sAqui%s para voltar á Administração de Estilos";

$lang['Theme_settings'] = "Configuração de Temas";
$lang['Theme_element'] = "Elemento de Tema";
$lang['Simple_name'] = "Nome Simples";
$lang['Value'] = "Valor";
$lang['Save_Settings'] = "Guardar Configuração";

$lang['Stylesheet'] = "CSS Stylesheet";
$lang['Background_image'] = "Imagem de Background";
$lang['Background_color'] = "Cor de Background";
$lang['Theme_name'] = "Nome do Tema";
$lang['Link_color'] = "Cor de Atalho";
$lang['Text_color'] = "Cor de Texto";
$lang['VLink_color'] = "Cor de Atalho Visitado";
$lang['ALink_color'] = "Cor de Atalho Activo";
$lang['HLink_color'] = "Cor de Atalho Hover";
$lang['Tr_color1'] = "Cor 1 de Coluna de Tabela";
$lang['Tr_color2'] = "Cor 2 de Coluna de Tabela";
$lang['Tr_color3'] = "Cor 3 de Coluna de Tabela";
$lang['Tr_class1'] = "Classe 1 de Coluna de Tabela";
$lang['Tr_class2'] = "Classe 2 de Coluna de Tabela";
$lang['Tr_class3'] = "Classe 3 de Coluna de Tabela";
$lang['Th_color1'] = "Cor 1 de Cabeça de Tabela";
$lang['Th_color2'] = "Cor 2 de Cabeça de Tabela";
$lang['Th_color3'] = "Cor 3 de Cabeça de Tabela";
$lang['Th_class1'] = "Classe 1 de Cabeça de Tabela";
$lang['Th_class2'] = "Classe 2 de Cabeça de Tabela";
$lang['Th_class3'] = "Classe 3 de Cabeça de Tabela";
$lang['Td_color1'] = "Cor 1 de Célula de Tabela";
$lang['Td_color2'] = "Cor 2 de Célula de Tabela";
$lang['Td_color3'] = "Cor 3 de Célula de Tabela";
$lang['Td_class1'] = "Classe 1 de Célula de Tabela";
$lang['Td_class2'] = "Classe 2 de Célula de Tabela";
$lang['Td_class3'] = "Classe 3 de Célula de Tabela";
$lang['fontface1'] = "Fonte Face 1";
$lang['fontface2'] = "Fonte Face 2";
$lang['fontface3'] = "Fonte Face 3";
$lang['fontsize1'] = "Tamanho 1 de Fonte";
$lang['fontsize2'] = "Tamanho 2 de Fonte";
$lang['fontsize3'] = "Tamanho 3 de Fonte";
$lang['fontcolor1'] = "Cor 1 de Fonte";
$lang['fontcolor2'] = "Cor 2 de Fonte";
$lang['fontcolor3'] = "Cor 3 de Fonte";
$lang['span_class1'] = "Classe 1 - Extensão";
$lang['span_class2'] = "Classe 2 - Extensão";
$lang['span_class3'] = "Classe 3 - Extensão";
$lang['img_poll_size'] = "Tamanho da Imagem da Votação [px]";
$lang['img_pm_size'] = "Tamanho de Estado de Mensagem Privada [px]";


//
// Install Process
//
$lang['Welcome_install'] = "Bem-vindo á Instalação do phpBB 2";
$lang['Initial_config'] = "Configuração Básica";
$lang['DB_config'] = "Configuração de Base de Dados";
$lang['Admin_config'] = "Configuração de Admin";
$lang['continue_upgrade'] = "Logo que tenha terminado o 'download' do ficheiro de configuração para o computador poderá premir \"Continuar a Actualização\" abaixo para continuar o processo.  Aguardar que seja feito o 'upload' do ficheiro de configuração ate que o processo de actualização esteja completo.";
$lang['upgrade_submit'] = "Continuar a Actualização";

$lang['Installer_Error'] = "Ocorreu um erro durante a instalação";
$lang['Previous_Install'] = "Foi detectada uma instalação anterior";
$lang['Install_db_error'] = "Ocorreu um erro ao tentar actualizar a base de dados";

$lang['Re_install'] = "A sua instalação anterior ainda se encontra activa. <br /><br />Se pretende reinstalar phpBB 2 deverá carregar no butáo Sim abaixo. Ter em atenção que ao fazê-lo irá destruir toda a informação existente, não sendo feitas cópias de segurança! O Nome de Utilizador e Senha de administrador que tem usado para ligar ao fórum será recriada após esta reinstalação, nao sendo qualquer outros dados de configuração serão guardados. <br /><br />Pense cautelosamente antes de carregar em Sim!";

$lang['Inst_Step_0'] = "Obrigado por ter escolhido phpBB 2. De modo a completar esta instalação preencher os detalhes pedidos abaixo. De notar que a base de dados onde a informação do fórum será instalada deve existir já. Caso se encontre a instalar numa base de dados que use ODBC, ou seja, MS Access, deve primeiro ser criado um DSN.";

$lang['Start_Install'] = "Começar a Instalação";
$lang['Finish_Install'] = "Terminar a Instalação";

$lang['Default_lang'] = "Língua Base do Fórum";
$lang['DB_Host'] = "Hostname do Sevidor da Base de Dados / DSN";
$lang['DB_Name'] = "Nome da Base de Dados";
$lang['DB_Username'] = "Nome de Utilizador na Base de Dados";
$lang['DB_Password'] = "Senha na Base de Dados";
$lang['Database'] = "Sua Base de Dados";
$lang['Install_lang'] = "Escolher a Língua para a Instalação";
$lang['dbms'] = "Tipo de Base de Dados";
$lang['Table_Prefix'] = "Prefixo para as tabelas na Base de Dados";
$lang['Admin_Username'] = "Nome de Utilizador do Administrador";
$lang['Admin_Password'] = "Senha do Administrador";
$lang['Admin_Password_confirm'] = "Senha do Administrador [ Confirmar ]";

$lang['Inst_Step_2'] = "O seu Nome de Utilizador para Administrador foi criado.  Neste momento a Instalação Básica está concluída. Irá ser conduzido agora a um painel onde poderá administrar a sua nova instalação. Verificar os detalhes de Configuração Geral e proceder as mudanças necessárias. Obrigado por usar phpBB 2.";

$lang['Unwriteable_config'] = "O seu ficheiro de configuração não pode ser escrito neste momento. Será feita uma cópia do ficheiro quando carregar no butão abaixo. Deverá colocar este ficheiro na mesma pasta que o phpBB 2. Uma vez concluido, você deverá ligar-se usando o Nome de Utilizador de administrador e respectiva senha que forneceu anteriormente visitando de seguida o Painel de Administração (um atalho irá surgir na parte iferior de cada janela) para verificar a configuração geral. Obrigado por escolher phpBB 2.";
$lang['Download_config'] = "Download a Configuração";

$lang['ftp_choose'] = "Escolher um método para Download";
$lang['ftp_option'] = "<br />Visto as extensões de FTP se encontrarem activas nesta versão de PHP deve-lhe ter sido também dada a opção para primeiro tentar automaticamente FTP o ficheiro de configuração para o local certo.";
$lang['ftp_instructs'] = "Escolheu para FTP automaticamente o ficheiro para a conta contendo phpBB 2.  Por favor forneça a informação abaixo para facilitar o processo. De notar que o 'path' do FTP deverá ser exactamente o mesmo via ftp para a instalação do seu phpBB 2 como se estivesse a efectuar ftp usando um cliente normal.";
$lang['ftp_info'] = "Escrever a informação do FTP";
$lang['Attempt_ftp'] = "Tentando FTP o ficheiro de configuração para o local corecto";
$lang['Send_file'] = "Apenas enviar o ficheiro para mim e eu farei o FTP manualmente";
$lang['ftp_path'] = "Path de FTP para o phpBB 2";
$lang['ftp_username'] = "O seu nome de utilizador para o FTP";
$lang['ftp_password'] = "A sua senha para o FTP";
$lang['Transfer_config'] = "Iniciar a Transferência";
$lang['NoFTP_config'] = "A tentativa de FTP o ficheiro de configuração para o local correcto falhou.  Por favor fazer o download do mesmo e efectuar o FTP manualmente.";

$lang['Install'] = "Instalar";
$lang['Upgrade'] = "Actualizar";


$lang['Install_Method'] = "Escolher o seu método de instalação";

//
// That's all Folks!
// -------------------------------------------------

?>