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
$lang['Disallow'] = "Proibição de Nomes";
$lang['Prune'] = "Desbastar";
$lang['Mass_Email'] = "Email Maciço";
$lang['Ranks'] = "Escalões";
$lang['Smilies'] = "Smileys";
$lang['Ban_Management'] = "Controle de Expulsões";
$lang['Word_Censor'] = "Censura de Palavras";
$lang['Export'] = "Exportar";
$lang['Create_new'] = "Criar";
$lang['Add_new'] = "Adicionar";
$lang['Backup_DB'] = "Copiar Base de Dados";
$lang['Restore_DB'] = "Repor Base de Dados";


//
// Index
//
$lang['Admin'] = "Administração";
$lang['Not_admin'] = "Você não está autorizado a administrar este painel";
$lang['Welcome_phpBB'] = "Bem-vindo ao phpBB";
$lang['Admin_intro'] = "Obrigado por escolher phpBB como seu fórum. Este painel dá-lhe uma visualização glogal estatística dos seus fóruns. Poderá voltar aqui premindo <u>Índice de Admin</u> no painel esquerdo. Para voltar ao Índice global dos fóruns prima no logo phpBB. Os outros atalhos dão-lhe acesso aos diversos paineis de controle dos fóruns, cada um com instrucções de uso.";
$lang['Main_index'] = "Índice do Fórum";
$lang['Forum_stats'] = "Estatísticas do Fórum";
$lang['Admin_Index'] = "Índice de Admin";
$lang['Preview_forum'] = "Rever Fórum";

$lang['Click_return_admin_index'] = "Premir %sAqui%s para voltar ao Índice de Admin";

$lang['Statistic'] = "Estatística";
$lang['Value'] = "Valor";
$lang['Number_posts'] = "Número de Mensagens";
$lang['Posts_per_day'] = "Mensagens por Dia";
$lang['Number_topics'] = "Número de Tópicos";
$lang['Topics_per_day'] = "Tópicos por Dia";
$lang['Number_users'] = "Número de Utilizadores";
$lang['Users_per_day'] = "Utilizadores por Dia";
$lang['Board_started'] = "Início dos Fóruns";
$lang['Avatar_dir_size'] = "Pasta de Avatars";
$lang['Database_size'] = "Tamanho da Base de Dados";
$lang['Gzip_compression'] ="Compressão Gzip";
$lang['Not_available'] = "Não Disponível";

$lang['ON'] = "Activo"; // This is for GZip compression
$lang['OFF'] = "Inactiva"; 


//
// DB Utils
//
$lang['Database_Utilities'] = "Utilitários da Base de Dados";

$lang['Restore'] = "Repor";
$lang['Backup'] = "Copiar";
$lang['Restore_explain'] = "Pode ser feita a partir deste painel a reposição de um ficheiro de tabelas do phpBB previamente guardado. Caso o seu server permita poderá usar o ficheiro comprimido em Gzip que será automaticamente descomprimido. <b>ATENÇÃO</b> Esta operação repõe a informação existente. Dependendo do tamanho da sua base de dados este processo poderá levar algum tempo- não mude esta página para outra.";
$lang['Backup_explain'] = "Pode ser feita a partir deste painel uma cópia de toda a informação do seu phpBB. Se pretender copiar tabelas adicionais que contenha na base de dados escreva na caixa de texto de Tabelas Adicionais abaixo os seus nomes separados por vírgulas. Caso o seu server permita pode comprimir o ficheiro em gzip de forma a reduzir o seu tamanho antes de o copiar.";

$lang['Backup_options'] = "Opções de Cópia";
$lang['Start_backup'] = "Iniciar a Cópia";
$lang['Full_backup'] = "Cópia Total";
$lang['Structure_backup'] = "Copiar apenas a Estrutura";
$lang['Data_backup'] = "Copiar apenas os Dados";
$lang['Additional_tables'] = "Tabelas Adicionais";
$lang['Gzip_compress'] = "Comprimir ficheiro em Gzip";
$lang['Select_file'] = "Seleccionar um ficheiro";
$lang['Start_Restore'] = "Iniciar a Reposição";

$lang['Restore_success'] = "A Base de Dados foi reposta com sucesso.<br /><br />Os seus fóruns deverão voltar agora ao estado em que se encontravam na altura da cópia.";
$lang['Backup_download'] = "A cópia deverá iniciar em breve. Por favor aguarde até que comece.";
$lang['Backups_not_supported'] = "O seu sistema de Base de Dados não permite presentemente efectuar cópias de Dados";

$lang['Restore_Error_uploading'] = "Erro a repor o ficheiro";
$lang['Restore_Error_filename'] = "Problema no nome do ficheiro, por favor tentar um alternativo";
$lang['Restore_Error_decompress'] = "Não é possível descomprimir um ficheiro gzip file, por favor repor uma versão em texto";
$lang['Restore_Error_no_file'] = "Nenhum ficheiro foi reposto";


//
// Auth pages
//
$lang['Select_a_User'] = "Seleccionar um Utilizador";
$lang['Select_a_Group'] = "Seleccionar um Grupo";
$lang['Select_a_Forum'] = "Seleccionar um Fórum";
$lang['Auth_Control_User'] = "Controle de Permissões de Utilizador"; 
$lang['Auth_Control_Group'] = "Controle de Permissões de Grupo"; 
$lang['Auth_Control_Forum'] = "Controle de Permissões de Fórum"; 
$lang['Look_up_User'] = "Verificar"; 
$lang['Look_up_Group'] = "Verificar"; 
$lang['Look_up_Forum'] = "Verificar"; 

$lang['Group_auth_explain'] = "Pode alterar aqui as permissões e estatuto de moderador de Grupos de Utilizadores. Não esquecer que quando as altera, essas permissões em particular poderão não invalidar que o utilizador entre nos fóruns, etc. Caso isso aconteça será devidamente avisado.";
$lang['User_auth_explain'] = "Pode alterar aqui as permissões e estatuto de moderador de cada utilizador individualmente. Não esquecer que quando muda as permissões de um utilizador essas permissões de grupo poderão não invalidar que o utilizador entre nos fóruns, etc.  Caso isso aconteça será devidamente avisado.";
$lang['Forum_auth_explain'] = "Os níveis de permissões em cada fórum são configurados neste painel . Após seleccionar um fórum poderá escolher entre um método simples e um avançado, proporcionando este último um maior controle de configuração. Ter em mente que a forma em como cada utilizador possa efectuar as várias operações nos fóruns pode ser afectada com qualquer mudança nos níveis de permissões.";

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
                                              
// These are displayed in the drop down boxes 
// mode forum auth, try and keep them short!  
$lang['Forum_ALL'] = "TODOS";                   
$lang['Forum_REG'] = "REGIST";                   
$lang['Forum_PRIVATE'] = "PRIVADO";           
$lang['Forum_MOD'] = "MODERAD";                   
$lang['Forum_ADMIN'] = "ADMIN";               

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
$lang['Ban_explain'] = "Pode ser a partir deste painel expulso  um utilizador específico ou um grupo de endereços de IP ou 'hostnames'. Estes métodos impedem utilizadores de alcançar sequer a página inicial dos fóruns. Para evitar que um utilizador se registe com um nome diferente pode ser também banido um endereço de email. De notar que ao banir um email só por si não impede um utilizador de se ligar ou colocar mensagens nos fóruns. Para que isso aconteça empregue um dos métodos descritos inicialmente.";
$lang['Ban_explain_warn'] = "De notar que ao especificar um grupo de endereços de IP resulta em TODOS os endereços incluidos nessa seleção, ou seja desde o primeiro IP até ao último, sejam adicionados à lista de IPs banidos. O sistema de phpBB tenta minimizar o número de endereços a adicionar à base de dados empregando automaticamente filtros de seleção sempre que seja aplicável. Se você realmente tenha que mencionar um grupo de IPs tente fazê-lo de forma a abranger uma quantidade reduzida de endereços ou, melhor ainda, use endereços específícos.";

$lang['Select_username'] = "Seleccionar um Utilizador";
$lang['Select_ip'] = "Seleccionar um IP";
$lang['Select_email'] = "Seleccionar um endereço de Email";

$lang['Ban_username'] = "Banir um ou mais utilizadores específicos";
$lang['Ban_username_explain'] = "Pode banir vários utilizadores de uma vez usando a combinação apropriada de teclas e rato no seu computador ou browser.";

$lang['Ban_IP'] = "Banir um ou mais endereços de IP ou hostnames";
$lang['IP_hostname'] = "Endereços de IP ou hostnames";
$lang['Ban_IP_explain'] = "Quando usar mais que um IP e 'hostname' separar cada item com uma vírgula. Para especificar um grupo de endereços de IP separar o início do fim com um traço (-). Pode também usar asteriscos (*)";

$lang['Ban_email'] = "Banir um ou mais endereços de email";
$lang['Ban_email_explain'] = "Quando usar mais que um email separar cada item com uma vírgula. Para abranger um número mais vasto de possibilidades na secção do 'username' do email use asteriscos '*', por exemplo, *@hotmail.com";

$lang['Unban_username'] = "Remover a expulsão de um ou mais utilizadores";
$lang['Unban_username_explain'] = "Pode remover a expulsão de vários utilizadores simultaneamente usando a combinação apropriada de teclas e rato no seu computador ou browser.";

$lang['Unban_IP'] = "Remover a expulsão de um ou mais endereços de IP";
$lang['Unban_IP_explain'] = "Pode remover a expulsão de vários endereços de IP simultaneamente usando a combinação apropriada de teclas e rato no seu computador ou browser.";

$lang['Unban_email'] = "Remover a expulsão de um ou mais endereços de email";
$lang['Unban_email_explain'] = "Pode remover a expulsão de vários endereços de email simultaneamente usando a combinação apropriada de teclas e rato no seu computador ou browser.";

$lang['No_banned_users'] = "Não há Nomes de Utilizadores banidos";
$lang['No_banned_ip'] = "Não há endereços de IP banidos";
$lang['No_banned_email'] = "Não há endereços de email banidos";

$lang['Ban_update_sucessful'] = "A lista de expulsões foi actualizada com sucesso";
$lang['Click_return_banadmin'] = "Premir %sAqui%s para voltar ao Painel de Controle de Expulsões";


//
// Configuration
//
$lang['General_Config'] = "Configuração Geral";
$lang['Config_explain'] = "Usar este formulário para ajustar todas as opções gerais do seu phpBB. Para configuração específica de fóruns ou utilizadores use os respectivos atalhos no painel esquerdo.";

$lang['Click_return_config'] = "Premir %sAqui%s para voltar à Configuração Geral";

$lang['General_settings'] = "Configuração geral do phpBB";
$lang['Server_name'] = "Nome do Domínio";
$lang['Server_name_explain'] = "O nome do Domínio de onde este fórum reside";
$lang['Script_path'] = "O 'path' do programa";
$lang['Script_path_explain'] = "O 'path' onde se encontra o phpBB2 em relação ao Dominio";
$lang['Server_port'] = "Porta do Servidor";
$lang['Server_port_explain'] = "A porta que o servidor usa, normalmente 80 - mudar apenas se diferente";
$lang['Site_name'] = "Nome do local do Fórum";
$lang['Site_desc'] = "Descrição";
$lang['Board_disable'] = "Desactivar";
$lang['Board_disable_explain'] = "Isto torna os fóruns inacessíveis a utilizadores. NÃO SE DESLIGUE - após desactivar os fóruns neste local não conseguirá voltar a entrar!";
$lang['Acct_activation'] = "Usar a função de activação de registo";
$lang['Acc_None'] = "Nunca"; // These three entries are the type of activation
$lang['Acc_User'] = "Utilizadores";
$lang['Acc_Admin'] = "Administradores";

$lang['Abilities_settings'] = "Configuração básica de utilizadores e fóruns";
$lang['Max_poll_options'] = "Número máximo de opções nas votações";
$lang['Flood_Interval'] = "Intervalo de 'Flood'";
$lang['Flood_Interval_explain'] = "Tempo em segundos que um utilizador deva aguardar entre o envio de mensagens"; 
$lang['Board_email_form'] = "Email de utilizadores via fórum";
$lang['Board_email_form_explain'] = "Função que permite utilizadores enviar email a outros via phpBB";
$lang['Topics_per_page'] = "Máx. número de Tópicos por página";
$lang['Posts_per_page'] = "Máx. número de Mensagens por página";
$lang['Hot_threshold'] = "Máx. número de Mensagens por assunto popular";
$lang['Default_style'] = "Estilo básico";
$lang['Override_style'] = "Repor estilo do utilizador";
$lang['Override_style_explain'] = "Força o uso do estilo básico em vez do escolhido pelos utilizadores";
$lang['Default_language'] = "Língua básica";
$lang['Date_format'] = "Formato da Data";
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
$lang['Max_sig_length'] = "Tamanho máximo da assinatura";
$lang['Max_sig_length_explain'] = "Número máximo de caracteres permitidos na assinatura do utilizador";
$lang['Allow_name_change'] = "Permitir mudança de Nome de Utilizador";

$lang['Avatar_settings'] = "Configuração de Avatars";
$lang['Allow_local'] = "Activar galeria de Avatars";
$lang['Allow_remote'] = "Permitir Avatars remotos";
$lang['Allow_remote_explain'] = "Avatars ligados a partir de outro local no WWW";
$lang['Allow_upload'] = "Permitir carregar Avatars";
$lang['Max_filesize'] = "Tamanho máximo do arquivo de Avatars";
$lang['Max_filesize_explain'] = "Para Avatars carregados";
$lang['Max_avatar_size'] = "Dimensões máximas dos Avatars";
$lang['Max_avatar_size_explain'] = "(Altura x Largura em pixels)";
$lang['Avatar_storage_path'] = "'Path' de armazenamento dos Avatars";
$lang['Avatar_storage_path_explain'] = "'Path' para o local onde se irão guardar os Avatars na directoria do phpBB, ou seja, images/avatars";
$lang['Avatar_gallery_path'] = "'Path' a Galeria dos Avatars";
$lang['Avatar_gallery_path_explain'] = "'Path' para o local onde se encontram as imagens previamente guardadas na directoria do phpBB, ou seja, images/avatars/gallery";

$lang['COPPA_settings'] = "Configuração de COPPA";
$lang['COPPA_fax'] = "Fax para COPPA";
$lang['COPPA_mail'] = "Endereço de email para COPPA";
$lang['COPPA_mail_explain'] = "Este é um endereço da lista de correspondência para o qual os pais enviam os formulários de registo de COPPA";

$lang['Email_settings'] = "Configuração de Email";
$lang['Admin_email'] = "Endereço de Email Administrativo";
$lang['Email_sig'] = "Assinatura do Email";
$lang['Email_sig_explain'] = "Este texto será anexo a todos os emails enviados pelo fórum";
$lang['Use_SMTP'] = "Usar Servidor de SMTP para o email";
$lang['Use_SMTP_explain'] = "Caso queira ou tenha que enviar Email via um dado server em vez da função do phpBB para esse efeito";
$lang['SMTP_server'] = "Endereço do servidor de SMTP";
$lang['SMTP_username'] = "Nome de utilizador do SMTP";
$lang['SMTP_username_explain'] = "Apenas escrever o nome de utilizador se o seu servidor de smtp assim o requeira";
$lang['SMTP_password'] = "Senha para o SMTP";
$lang['SMTP_password_explain'] = "Apenas escrever a senha caso o seu servidor de smtp assim o requeira";

$lang['Disable_privmsg'] = "Mensagens Privadas";
$lang['Inbox_limits'] = "Número total de mensagens permitidas na Caixa de Entrada";
$lang['Sentbox_limits'] = "Número total de mensagens permitidas na Caixa de Saída";
$lang['Savebox_limits'] = "Número total de mensagens permitidas na Caixa de Reserva";

$lang['Cookie_settings'] = "Configuração dos 'Cookies'"; 
$lang['Cookie_settings_explain'] = "Estas especificaçóes definem como os 'cookies' são enviados aos 'browsers' dos seus utilizadores. Na maioria dos casos os valores básicos para a configuração dos 'cookies' deverão ser suficientes mas caso os necessite mudar faça-o com cuidado porque uma configuração incorrecta poderá impedir os utilizadores de se ligar.";
$lang['Cookie_domain'] = "Domínio do Cookie";
$lang['Cookie_name'] = "Nome do Cookie";
$lang['Cookie_path'] = "'Path' do Cookie";
$lang['Cookie_secure'] = "'Cookie secure' [ https ]";
$lang['Cookie_secure_explain'] = "Se o seu uservidor se encontra a funcionar via SSL active isto, caso negativo deixe desligado";
$lang['Session_length'] = "Tempo da sessão [ segundos ]";

//
// Forum Management
//
$lang['Forum_admin'] = "Gerência dos Fóruns";
$lang['Forum_admin_explain'] = "Usar este painel para adicionar, remover, editar, reordenar e sincronizar categorias e fóruns.";
$lang['Edit_forum'] = "Editar fórum";
$lang['Create_forum'] = "Criar fórum";
$lang['Create_category'] = "Criar categoria";
$lang['Remove'] = "Remover";
$lang['Action'] = "Acção";
$lang['Update_order'] = "Actualizar a Ordem";
$lang['Config_updated'] = "Configuração do Fórum actualizada com sucesso";
$lang['Edit'] = "Editar";
$lang['Delete'] = "Remover";
$lang['Move_up'] = "Mover/cima";
$lang['Move_down'] = "Mover/baixo";
$lang['Resync'] = "Sincronizar";
$lang['No_mode'] = "Não foi configurado nenhum modo";
$lang['Forum_edit_delete_explain'] = "O formulário abaixo permite-lhe especificar todas as opções globais do fórum. Usar os atalhos no painel do lado esquerdo para configurações específicas de utilizadores ou fóruns.";

$lang['Move_contents'] = "Mover todo o conteúdo";
$lang['Forum_delete'] = "Remover Fórum";
$lang['Forum_delete_explain'] = "O formulário abaixo permite-lhe remover um fórum (ou categoria) e decidir onde pretende colocar todos os tópicos (ou fóruns) existentes.";

$lang['Forum_settings'] = "Configuração Geral dos Fóruns";
$lang['Forum_name'] = "Nome do Fórum";
$lang['Forum_desc'] = "Descrição";
$lang['Forum_status'] = "Estado";
$lang['Forum_pruning'] = "Auto-desbastar";

$lang['prune_freq'] = 'Verificar o tempo dos tópicos em cada';
$lang['prune_days'] = "Remover tópicos que não tenham tido respostas em";
$lang['Set_prune_data'] = "Activou a função para desbastar o fórum automaticamente mas não especificou a frequência ou número de dias em que o mesmo deve ser feito. Voltar atrás e especificar esse valor";

$lang['Move_and_Delete'] = "Mover e Remover";

$lang['Delete_all_posts'] = "Remover todas as mensagens";
$lang['Nowhere_to_move'] = "Não há local para onde mover";

$lang['Edit_Category'] = "Editar Categoria";
$lang['Edit_Category_explain'] = "Usar este formulário para mudar o nome da categoria.";

$lang['Forums_updated'] = "Informação de Fórum e Categoria actualizada com sucesso ";

$lang['Must_delete_forums'] = "Necessita remover todos os fóruns antes de remover esta categoria";

$lang['Click_return_forumadmin'] = "Premir %sAqui%s para voltar a Gerência dos Fóruns";


//
// Smiley Management
//
$lang['smiley_title'] = "Painel de Gerência de Smileys";
$lang['smile_desc'] = "Pode adicionar, remover e editar neste painel as emoções ou smileys que os utilizadores poderão usar nas suas mensagens, tanto públicas como privadas. Podem ser igualmente importados a partir daqui pacotes de Smileys.";

$lang['smiley_config'] = "Gerência de Smileys";
$lang['smiley_code'] = "Código para o Smiley";
$lang['smiley_url'] = "Ficheiro da imagem do Smiley";
$lang['smiley_emot'] = "Emoção do Smiley";
$lang['smile_add'] = "Adicionar um Smiley";
$lang['Smile'] = "Smiley";
$lang['Emotion'] = "Emoção";

$lang['Select_pak'] = "Seleccionar um ficheiro com o 'pacote' (.pak)";
$lang['replace_existing'] = "Repor o Smiley existente";
$lang['keep_existing'] = "Conservar o Smiley existente";
$lang['smiley_import_inst'] = "Deverá descomprimir o pacote dos Smileys e colocar todos os ficheiros na pasta respectiva na instalação do phpBB 2.  Especificar depois a informação correcta neste formulário para importar o pacote.";
$lang['smiley_import'] = "Importar pacote";
$lang['choose_smile_pak'] = "Escolher ficheiro do pacote de Smileys (.pak)";
$lang['import'] = "Importar";
$lang['smile_conflicts'] = "Em caso de conflitos:";
$lang['del_existing_smileys'] = "Remover os Smileys existentes antes de importar o pacote";
$lang['import_smile_pack'] = "Importar pacote de Smiley";
$lang['export_smile_pack'] = "Criar pacote";
$lang['export_smiles'] = "Para criar um pacote de Smileys a partir dos correntemente instalados, primeiro premir %sAqui%s para fazer o 'download' do pacote smiles.pak. Dar o nome a este ficheiro apropriadamente, mantendo a extensão .pak.  Criar depois um ficheiro zip contendo todos as suas imagens de Smiley mais este ficheiro de configuração (.pak).";

$lang['smiley_add_success'] = "O Smiley foi adicionado com sucesso";
$lang['smiley_edit_success'] = "O Smiley foi actualizado com sucesso";
$lang['smiley_import_success'] = "O pacote de Smiley foi importado com sucesso!";
$lang['smiley_del_success'] = "O Smiley foi removido com sucesso";
$lang['Click_return_smileadmin'] = "Premir %sAqui%s para voltar a <b>Gerência de Smileys</b>";


//
// User Management
//
$lang['User_admin'] = "Gerência de Utilizadores";
$lang['User_admin_explain'] = "Poderá mudar aqui a informação dos seus utilizadores além de algumas opções específicas. Para modificar as permissões de utilizadores usar o painel de <b>Permissões</b> para Utilizadores e Grupos.";

$lang['Look_up_user'] = "Verificar";

$lang['Admin_user_fail'] = "Não foi possível actualizar o perfil de utilizadores.";
$lang['Admin_user_updated'] = "O perfil de utilizadores foi actualizado com sucesso.";
$lang['Click_return_useradmin'] = "Premir %sAqui%s Para voltar a Gerência de Utilizadores";

$lang['User_delete'] = "Remover este Utilizador";
$lang['User_delete_explain'] = "Assinalar para remover o utilizador. Esta operação tem efeitos permanentes.";
$lang['User_deleted'] = "Utilizador removido com sucesso.";

$lang['User_status'] = "Utilizador está activo";
$lang['User_allowpm'] = "Pode enviar Mensagens Privadas";
$lang['User_allowavatar'] = "Pode mostrar Avatar";

$lang['Admin_avatar_explain'] = "Aqui poderá ver e remover o Avatar corrente do utilizador.";

$lang['User_special'] = "Configuração especial apenas para administradores";
$lang['User_special_explain'] = "Estes parâmetors não podem ser modificados por utilizadores. Pode especificar aqui o seu estado bem como outras opções que não são dadas aos utilizadores.";


//
// Group Management
//
$lang['Group_administration'] = "Gerência de Grupos";
$lang['Group_admin_explain'] = "Usar este painel para criar, editar e remover Grupos de Utilizadores. Poderá aqui também escolher moderadores, abrir ou encerrar grupos, estipular os seus nomes e respectivas descrições";
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
$lang['Click_return_groupsadmin'] = "Premir %sAqui%s para voltar à Gerência de Grupos.";
$lang['Select_group'] = "Seleccionar um Grupo";
$lang['Look_up_group'] = "Verificar";


//
// Prune Administration
//
$lang['Forum_Prune'] = "Desbastar Fórum";
$lang['Forum_Prune_explain'] = "Esta operação removerá qualquer tópico que não possua resposta dentro do limite de dias especificado. Se não for mencionado um número de dias todos os tópicos serão removidos. Isto não remove tópicos com uma votação activa nem anúncios. Terá que os remover manualmente.";
$lang['Do_Prune'] = "Desbastar";
$lang['All_Forums'] = "Todos os Fóruns";
$lang['Prune_topics_not_posted'] = "Remover todos os tópicos sem resposta durante um período de ";
$lang['Topics_pruned'] = "Tópicos Removidos";
$lang['Posts_pruned'] = "Mensagens Removidas";
$lang['Prune_success'] = "Desbaste de fóruns concluído com sucesso";


//
// Word censor
//
$lang['Words_title'] = "Gerência de Censura de Palavras";
$lang['Words_explain'] = "Usar este painel para adicionar, editar e remover palavras que serão automaticamente censuradas nos fóruns. O uso dessas palavras será também interdito no registo de Nomes de Utilizadores. Podem ser usados asteriscos (*) aumentando as possibilidades de abranger variantes da mesma palavra. Por exemplo, *testa* abrangerá detestável, testa* abrangerá testando, *testa abrangerá detesta. Pode ser escolhido em <b>Substituição</b> o que vá repor automaticamente essas palavras quando escritas.";
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

$lang['Click_return_wordadmin'] = "Premir %sAqui%s para voltar à Gerência de Censura de Palavras";


//
// Mass Email
//
$lang['Mass_email_explain'] = "Pode ser a partir daqui enviada uma mensagem de email para todos os utilizadores dos fóruns ou utilizadores membros de um dado grupo, sendo empregue para o efeito o endereço de <b>Email Administrativo</b> préviamente configurado. Caso seja enviado um email para número elevado de pessoas aguardar um pouco após premir abaixo em <b>Email</b> e não parar a página a meio - é normal o envio de um email em massa demorar um pouco, mas você será avisado quando o processo estiver concluído.";
$lang['Compose'] = "Compor"; 

$lang['Recipients'] = "Destinatários"; 
$lang['All_users'] = "Todos os utilizadores";

$lang['Email_successfull'] = "A sua mensagem foi enviada";
$lang['Click_return_massemail'] = "Premir %sAqui%s para voltar ao formulário de email maciço";


//
// Ranks admin
//
$lang['Ranks_title'] = "Gerência de Escalões";
$lang['Ranks_explain'] = "Usando este painel poderá adicionar, editar, ver e remover escalões de utilizadores. Poderá também criar escalões específicos podendo os mesmos ser aplicados a um utilizador via painel de <b>Admin de Utilizadores</b>";

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

$lang['Click_return_rankadmin'] = "Premir %sAqui%s para voltar a Gerência de Escalões";


//
// Disallow Username Admin
//
$lang['Disallow_control'] = "Controle de Nomes de Utilizadores Proibidos";
$lang['Disallow_explain'] = "Poderá controlar aqui nomes de utilizadores cujo uso seja proibido nos fóruns.  De notar que não podem ser especificados Nomes de Utilizadores que já se encontrem registados, devendo primeiro ser removidos e então especificá-los aqui para não mais serem usados. Podem ser empregues asteriscos '*' para abranger um maior número de variantes na palavra.";

$lang['Delete_disallow'] = "Remover";
$lang['Delete_disallow_title'] = "Remover um Nome de Utilizador Proibido";
$lang['Delete_disallow_explain'] = "Escolher um Nome de Utilizador na lista e carregar em <b>Remover</b>";

$lang['Add_disallow'] = "Adicionar";
$lang['Add_disallow_title'] = "Adicionar um Nome de Utilizador Proibido";
$lang['Add_disallow_explain'] = "Usar asteriscos '*' se necessário";

$lang['No_disallowed'] = "Não há Nomes de Utilizadores Proibidos";

$lang['Disallowed_deleted'] = "O Nome de Utilizador Proibido foi removido com sucesso";
$lang['Disallow_successful'] = "O Nome de Utilizador Proibido foi adicionado com sucesso";
$lang['Disallowed_already'] = "O nome que especificou não pode ser proibido. Pode acontecer já existir na lista de Nomes Proibidos, na lista de Palavras Censuradas ou encontrar-se presentemente em uso por algum utilizador registado";

$lang['Click_return_disallowadmin'] = "Premir %sAqui%s para voltar a Painel de Controle de Nomes de Utilizadores Proibidos";


//
// Styles Admin
//
$lang['Styles_admin'] = "Gerência de Estilos";
$lang['Styles_explain'] = "Usando este painel poderá adicionar, remover e administrar estilos (Modelos e Temas) disponíveis aos utilizadores.";
$lang['Styles_addnew_explain'] = "Este painel é destinado à listagem dos Temas de fórum para os Modelos que presentemente possui e ainda não se encontram instalados na base de dados do phpBB. Para instalar um tema específico premir em <b>Instalar</b> ao lado desse item.";

$lang['Select_template'] = "Seleccionar um Modelo";

$lang['Style'] = "Estilo";
$lang['Template'] = "Modelo";
$lang['Install'] = "Instalar";
$lang['Download'] = "Download";

$lang['Edit_theme'] = "Editar Tema";
$lang['Edit_theme_explain'] = "Configurar o tema seleccionado no formulário abaixo";

$lang['Create_theme'] = "Criar Tema";
$lang['Create_theme_explain'] = "Use o formulário abaixo para criar um Tema novo para o Modelo existente. Quando aplicar cores (que devem ser escritas num formato hexadecimal) não deve ser incluido o # inicial, ou seja, CCCCCC é a forma correcta de escrever, #CCCCCC é incorrecto.";

$lang['Export_themes'] = "Exportar Temas";
$lang['Export_explain'] = "Usar este painel para exportar informação de um Tema para um dado Modelo. Escolha um Modelo na lista e será automaticamente criado um ficheiro de configuração do tema que irá ser guardado e instalado na pasta do Modelo seleccionado. Caso não for possível guardar o ficheiro por si próprio será dada a opção para ser feito o seu 'download'. Deve haver ou ser dada permissão de escrita ao servidor de WEB para a pasta do Modelo seleccionado de forma que que o ficheiro seja guardado. Para mais informação sobre esta operação ver o <b>phpBB 2 users guide</b>.";

$lang['Theme_installed'] = "O tema seleccionado foi instalado com sucesso";
$lang['Style_removed'] = "O estilo seleccionado foi removido da base de dados. Para remover completamente este estilo do seu sistema deve apagar o estilo apropriado na pasta dos Modelos.";
$lang['Theme_info_saved'] = "A informação do tema para o Modelo seleccionado foi guardada. Você deve agora mudar as permissões para 'read-only'mo ficheiro theme_info.cfg (e caso aplicável na pasta de Modelos)";
$lang['Theme_updated'] = "O tema seleccionado foi actualizado. Você deve agora exportar a nova configuração do tema";
$lang['Theme_created'] = "Tema criado. Você deve agora exportar o tema para o ficheiro de configuração do tema como segurança ou usar noutro local";

$lang['Confirm_delete_style'] = "Tem a certeza que quer remover este estilo?";

$lang['Download_theme_cfg'] = "Não foi possível escrever o ficheiro de informação do tema. Premir o botão abaixo para fazer o 'download' deste ficheiro com o seu 'browser'. Logo que termine o download poderá trasferir o ficheiro para a pasta contendo os ficheiros do Modelo. Pode depois arrumar os ficheiros para distribuição ou usar noutro local, se assim o pretender";
$lang['No_themes'] = "O Modelo que seleccionou nao possúi temas anexos. Para criar um tema novo premir em Criar no painel do lado esquerdo";
$lang['No_template_dir'] = "Não foi possível abrir a pasta de Modelos. Pode ser que não haja possibilidade de ser lido pelo servidor de Web ou a pasta não exista";
$lang['Cannot_remove_style'] = "Não pode remover o estilo seleccionado porque é presentemente o estilo básico do fórum. Mudar o estilo básico e tentar novamente.";
$lang['Style_exists'] = "O nome para o estilo que seleccionou já existe, voltar atrás e escolher um nome diferente.";

$lang['Click_return_styleadmin'] = "Premir %sAqui%s para voltar à Gerência de Estilos";

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
$lang['Welcome_install'] = "Bem-vindo à Instalação do phpBB 2";
$lang['Initial_config'] = "Configuração Básica";
$lang['DB_config'] = "Configuração de Base de Dados";
$lang['Admin_config'] = "Configuração de Admin";
$lang['continue_upgrade'] = "Logo que tenha terminado o 'download' do ficheiro de configuração para o computador poderá premir \"Continuar a Actualização\" abaixo para continuar o processo.  Aguardar que seja feito o 'upload' do ficheiro de configuração ate que o processo de actualização esteja completo.";
$lang['upgrade_submit'] = "Continuar a Actualização";

$lang['Installer_Error'] = "Ocorreu um erro durante a instalação";
$lang['Previous_Install'] = "Foi detectada uma instalação anterior";
$lang['Install_db_error'] = "Ocorreu um erro ao tentar actualizar a base de dados";

$lang['Re_install'] = "A sua instalação anterior ainda se encontra activa. <br /><br />Se pretende reinstalar phpBB 2 deverá carregar no botão Sim abaixo. Ter em atenção que ao fazê-lo irá destruir toda a informação existente, não sendo feitas cópias de segurança! O Nome de Utilizador e Senha de administrador que tem usado para ligar ao fórum será recriada após esta reinstalação, nao sendo qualquer outros dados de configuração serão guardados. <br /><br />Pense cautelosamente antes de carregar em Sim!";

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

$lang['Unwriteable_config'] = "O seu ficheiro de configuração não pode ser escrito neste momento. Será feita uma cópia do ficheiro quando carregar no botão abaixo. Deverá colocar este ficheiro na mesma pasta que o phpBB 2. Uma vez concluido, você deverá ligar-se usando o Nome de Utilizador de administrador e respectiva senha que forneceu anteriormente visitando de seguida o Painel de Administração (um atalho irá surgir na parte iferior de cada janela) para verificar a configuração geral. Obrigado por escolher phpBB 2.";
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

$lang['Install_No_Ext'] = "A configuração de php no seu server não aceita o tipo de base de dados que escolheu";

$lang['Install_No_PCRE'] = "O phpBB2 requer o módulo para php 'Perl-Compatible Regular Expressions' cuja configuração do seu php parece não aceitar!";

//
// That's all Folks!
// -------------------------------------------------

?>
