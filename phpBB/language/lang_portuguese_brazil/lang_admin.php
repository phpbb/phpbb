<?php
/***************************************************************************
 *                          lang_admin.php [portuguese_br]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id$
 *
 ****************************************************************************/

/****************************************************************************
 * Traduzido por:
 * JuniorZ rs_junior@hotmail.com || http://usuarios.lycos.es/suportephpbb
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
$lang['General'] = 'Administração Geral';
$lang['Users'] = 'Administração de Usuários';
$lang['Groups'] = 'Administração de Grupos';
$lang['Forums'] = 'Administração do Fóruns';
$lang['Styles'] = 'Administração de Estilos';

$lang['Configuration'] = 'Configuração';
$lang['Permissions'] = 'Permissões';
$lang['Manage'] = 'Gerenciar';
$lang['Disallow'] = 'Nomes Proibídos';
$lang['Prune'] = 'Desbastar';
$lang['Mass_Email'] = 'Email em Massa';
$lang['Ranks'] = 'Ranks';
$lang['Smilies'] = 'Smileys';
$lang['Ban_Management'] = 'Controle de Expulsões';
$lang['Word_Censor'] = 'Palavras Censuradas';
$lang['Export'] = 'Exportar';
$lang['Create_new'] = 'Criar';
$lang['Add_new'] = 'Adicionar';
$lang['Backup_DB'] = 'Copiar Banco de Dados';
$lang['Restore_DB'] = 'Restaurar Banco de Dados';


//
// Index
//
$lang['Admin'] = 'Administração';
$lang['Not_admin'] = 'Você não está autorizado a administrar este painel';
$lang['Welcome_phpBB'] = 'Bem-vindo ao phpBB';
$lang['Admin_intro'] = 'Obrigado por escolher o phpBB como seu fórum. Este painel lhe dá uma visualização glogal das estatísticas dos seus fóruns. Você poderá voltar aqui clicando em <u>Índice de Administração</u> no painel esquerdo. Para voltar ao Índice global dos fóruns clique no logo phpBB. Os outros atalhos lhe dão acesso aos diversos painéis de controle dos fóruns, cada possui instruções de como usá-lo.';
$lang['Main_index'] = 'Índice do Fórum';
$lang['Forum_stats'] = 'Estatísticas do Fórum';
$lang['Admin_Index'] = 'Índice de Administração';
$lang['Preview_forum'] = 'Prever Fórum';

$lang['Click_return_admin_index'] = 'Clique %sAqui%s para retornar ao Índice de Administração';

$lang['Statistic'] = 'Estatística';
$lang['Value'] = 'Valor';
$lang['Number_posts'] = 'Número de Mensagens';
$lang['Posts_per_day'] = 'Mensagens / Dia';
$lang['Number_topics'] = 'Número de Tópicos';
$lang['Topics_per_day'] = 'Tópicos / Dia';
$lang['Number_users'] = 'Número de Usuários';
$lang['Users_per_day'] = 'Usuários / Dia';
$lang['Board_started'] = 'Início dos Fóruns';
$lang['Avatar_dir_size'] = 'Pasta de Avatars';
$lang['Database_size'] = 'Tamanho do Banco de Dados';
$lang['Gzip_compression'] = 'Compressão Gzip';
$lang['Not_available'] = 'Não Disponível';

$lang['ON'] = 'Habilitado'; // This is for GZip compression
$lang['OFF'] = 'Desabilitado';


//
// DB Utils
//
$lang['Database_Utilities'] = 'Utilitários de Banco de Dados';

$lang['Restore'] = 'Restaurar';
$lang['Backup'] = 'Copiar';
$lang['Restore_explain'] = 'Isso executará uma restauração completa de todas as tabelas do phpBB a partir de um arquivo salvo. Caso o seu servidor permita você poderá enviar um arquivo de texto compactado em Gzip e ele será descompactado automaticamente. <b>ATENÇÃO</b> Esta operação sobreescreverá qualquer informação existente. Dependendo do tamanho de seu Banco de Dados este processo poderá levar algum tempo. Não saia desta página para outra até que o processo seja finalizado.';
$lang['Backup_explain'] = 'Aqui você pode fazer um backup de todos os dados relacionados ao seu phpBB. Se pretender copiar tabelas adicionais que estejam no Banco de Dados escreva na caixa de texto abaixo: \"Tabelas Adicionais\" os nomes das tabelas separados por vírgulas. Caso o seu servidor permita, você pode compactar o arquivo em gzip de forma a reduzir o seu tamanho antes de copiá-lo.';

$lang['Backup_options'] = 'Opções de Backup';
$lang['Start_backup'] = 'Iniciar o Backup';
$lang['Full_backup'] = 'Backup Total';
$lang['Structure_backup'] = 'Backup apenas da Estrutura';
$lang['Data_backup'] = 'Backup apenas dos Dados';
$lang['Additional_tables'] = 'Tabelas Adicionais';
$lang['Gzip_compress'] = 'Compactar arquivo como Gzip';
$lang['Select_file'] = 'Selecione um arquivo';
$lang['Start_Restore'] = 'Iniciar a Restauração';

$lang['Restore_success'] = 'O  Banco de Dados foi restaurado com sucesso.<br /><br />Os seus fóruns deverão voltar agora ao estado em que se encontravam na época da cópia.';
$lang['Backup_download'] = 'A cópia deverá iniciar em breve. Por favor aguarde até que comece.';
$lang['Backups_not_supported'] = 'O seu sistema de Banco de Dados não permite efetuar cópias de Dados';

$lang['Restore_Error_uploading'] = 'Erro ao Enviar o arquivo';
$lang['Restore_Error_filename'] = 'Problema no nome do arquivo, por favor tente outro nome';
$lang['Restore_Error_decompress'] = 'Não é possível descompactar um arquivo Gzip, por favor envie uma versão em texto plano.';
$lang['Restore_Error_no_file'] = 'Nenhum arquivo foi Enviado';


//
// Auth pages
//
$lang['Select_a_User'] = 'Selecione um Usuário';
$lang['Select_a_Group'] = 'Selecione um Grupo';
$lang['Select_a_Forum'] = 'Selecione um Fórum';
$lang['Auth_Control_User'] = 'Controle de Permissões de Usuário';
$lang['Auth_Control_Group'] = 'Controle de Permissões de Grupo';
$lang['Auth_Control_Forum'] = 'Controle de Permissões de Fórum';
$lang['Look_up_User'] = 'Procurar Usuário';
$lang['Look_up_Group'] = 'Procurar Grupo';
$lang['Look_up_Forum'] = 'Procurar Fórum';

$lang['Group_auth_explain'] = 'Aqui você pode alterar as permissões e status do moderador de cada Grupo de Usuários. Não esqueça que quando as alterar, as permissões particulares não serão alteradas, como impedir que o Usuário entre nos fóruns, etc. Caso isso aconteça você será devidamente avisado.';
$lang['User_auth_explain'] = 'Aqui você pode alterar as permissões e status de moderador delegado a cada Usuário individualmente. Não esqueça que quando mudar as permissões de um Usuário, as permissões de Grupo não serão alteradas, como impedir que o Usuário entre nos fóruns, etc.  Caso isso aconteça você será devidamente avisado.';
$lang['Forum_auth_explain'] = 'Aqui você pode alterar os níveis de autorização de cada fórum. Você terá o método Simples e o Avançado para fazer isso, sendo que o Avançado oferece maior controle das operações de cada fórum. Lembre-se que a alteração do nível de permissão de seu fórum afetará o que cada usuário pode efetuar e aonde ele poderá efetuar.';

$lang['Simple_mode'] = 'Modo Simples';
$lang['Advanced_mode'] = 'Modo Avançado';
$lang['Moderator_status'] = 'Status do Moderador';

$lang['Allowed_Access'] = 'Acesso Permitido';
$lang['Disallowed_Access'] = 'Acesso Proibído';
$lang['Is_Moderator'] = 'É Moderador';
$lang['Not_Moderator'] = 'Não é Moderador';

$lang['Conflict_warning'] = 'Aviso de Conflito de Autorização';
$lang['Conflict_access_userauth'] = 'Este Usuário ainda possui direitos de acesso a este fórum através do seu registo no Grupo. Você talvez queira alterar as permissões de Grupo ou remover este Usuário desse Grupo para bloquear por completo os seus direitos de acesso. As permissões dos Grupos (e os fóruns envolvidos) estão indicados abaixo:';
$lang['Conflict_mod_userauth'] = 'Este Usuário ainda possui direitos de moderador neste fórum através do seu registo no Grupo. Você talvez queira alterar as permissões de Grupo ou remover este Usuário desse Grupo para bloquear por completo os seus direitos de acesso. As permissões dos Grupos (e os fóruns envolvidos) estão indicados abaixo:';

$lang['Conflict_access_groupauth'] = 'O seguinte Usuário (ou Usuários) ainda possuem direitos de acesso a este através de sua Configuração de Permissões de Usuário. Talvez você queira alterar as permissões de Usuário para bloquear por completo os seus direitos de acesso. As permissões dos Usuários (e os fóruns envolvidos) estão indicados abaixo:';

$lang['Conflict_mod_groupauth'] = 'O seguinte Usuário (ou Usuários) ainda possuem direitos de moderador neste fórum através de sua Configuração de Permissões de Usuário. Talvez você queira alterar as permissões de Usuário para bloquear por completo os seus direitos de acesso. As permissões dos Usuários (e os fóruns envolvidos) estão indicados abaixo:';

$lang['Public'] = 'Público';
$lang['Private'] = 'Particular';
$lang['Registered'] = 'Registados';
$lang['Administrators'] = 'Administradores';
$lang['Hidden'] = 'Oculto';

// These are displayed in the drop down boxes
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = 'TODOS';
$lang['Forum_REG'] = 'REGISTRADOS';
$lang['Forum_PRIVATE'] = 'PARTICULAR';
$lang['Forum_MOD'] = 'MODERADORES';
$lang['Forum_ADMIN'] = 'ADMINISTRADORES';

$lang['View'] = 'Ver';
$lang['Read'] = 'Ler';
$lang['Post'] = 'Enviar';
$lang['Reply'] = 'Responder';
$lang['Edit'] = 'Editar';
$lang['Delete'] = 'Remover';
$lang['Sticky'] = 'Fixo';
$lang['Announce'] = 'Anúncio';
$lang['Vote'] = 'Votar';
$lang['Pollcreate'] = 'Criar Enquete';

$lang['Permissions'] = 'Permissões';
$lang['Simple_Permission'] = 'Permissão Simples';

$lang['User_Level'] = 'Nível de Usuário';
$lang['Auth_User'] = 'Usuário';
$lang['Auth_Admin'] = 'Administrador';
$lang['Group_memberships'] = 'Membros do Grupo de Usuários';
$lang['Usergroup_members'] = 'Este Grupo tem os seguintes Membros';

$lang['Forum_auth_updated'] = 'Permissões do Fórum atualizadas';
$lang['User_auth_updated'] = 'Permissões de Usuário atualizadas';
$lang['Group_auth_updated'] = 'Permissões de Grupo atualizadas';

$lang['Auth_updated'] = 'As permissões foram atualizadas';
$lang['Click_return_userauth'] = 'Clique %sAqui%s para voltar à Permissões de Usuário';
$lang['Click_return_groupauth'] = 'Clique %sAqui%s para voltar à Permissões de Grupo';
$lang['Click_return_forumauth'] = 'Clique %sAqui%s para voltar à Permissões de Fórum';


//
// Banning
//
$lang['Ban_control'] = 'Controle de Expulsões';
$lang['Ban_explain'] = 'Aqui você pode controlar a expulsão de Usuários. Você pode expulsar um Usuário específico ou um grupo de endereços de IP ou \'hostnames\'. Estes métodos impedem Usuários de alcançar sequer a página inicial dos fóruns. Para evitar que um Usuário se registe com um nome de Usuário diferente você pode também banir um endereço de email específico. Note que ao banir um email só por si não impede um Usuário de se ligar ou colocar mensagens nos fóruns. Para que isso aconteça empregue um dos métodos descritos inicialmente.';
$lang['Ban_explain_warn'] = 'Note que ao especificar um grupo de endereços de IP resultará no banimento de TODOS os endereços incluidos nessa seleção, ou seja desde o primeiro IP até ao último, serão adicionados à lista de IPs banidos. O sistema de phpBB tenta minimizar o número de endereços a adicionar ao Banco de Dados empregando automaticamente filtros de seleção sempre que for possível. Se você realmente tiver que mencionar um grupo de IPs tente fazê-lo de forma a abranger uma quantidade reduzida de endereços ou, melhor ainda, use endereços específícos.';

$lang['Select_username'] = 'Selecionar um Usuário';
$lang['Select_ip'] = 'Selecionar um IP';
$lang['Select_email'] = 'Selecionar um endereço de Email';

$lang['Ban_username'] = 'Banir um ou mais Usuários específicos';

$lang['Ban_IP'] = 'Banir um ou mais endereços de IP ou hostnames';
$lang['IP_hostname'] = 'Endereços de IP ou hostnames';
$lang['Ban_IP_explain'] = 'Quando usar mais que um IP e \'hostname\' separe cada item com uma vírgula. Para especificar um grupo de endereços de IP separe o início do fim com um traço (-). Pode também usar asteriscos (*)';

$lang['Ban_email'] = 'Banir um ou mais endereços de email';
$lang['Ban_email_explain'] = 'Quando usar mais que um email separe cada item com uma vírgula. Para especificar um usuário qualquer de um certo provedor, utilize asteriscos '*', como por exemplo, *@hotmail.com';

$lang['Unban_username'] = 'Remover a expulsão de um ou mais Usuários';
$lang['Unban_username_explain'] = 'Você pode remover a expulsão de vários Usuários simultaneamente usando a combinação apropriada de teclas e cliques do mouse no seu computador ou browser.';

$lang['Unban_IP'] = 'Remover a expulsão de um ou mais endereços de IP';
$lang['Unban_IP_explain'] = 'Pode remover a expulsão de vários endereços de IP simultaneamente usando a combinação apropriada de teclas e cliques do mouse no seu computador ou browser.';

$lang['Unban_email'] = 'Remover a expulsão de um ou mais endereços de email';
$lang['Unban_email_explain'] = 'Pode remover a expulsão de vários endereços de email simultaneamente usando a combinação apropriada de teclas e cliques do mouse no seu computador ou browser.';

$lang['No_banned_users'] = 'Não há Nomes de Usuários banidos';
$lang['No_banned_ip'] = 'Não há endereços de IP banidos';
$lang['No_banned_email'] = 'Não há endereços de email banidos';

$lang['Ban_update_sucessful'] = 'A lista de expulsões foi atualizada com sucesso';
$lang['Click_return_banadmin'] = 'Clique %sAqui%s para voltar ao Painel de Controle de Expulsões';


//
// Configuration
//
$lang['General_Config'] = 'Configuração Geral';
$lang['Config_explain'] = 'O Formulário abaixo permitirá-lhe personalizar todas as opções gerais do seu phpBB. Para configurações de Fóruns ou Usuários use os respectivos links no painel esquerdo.';

$lang['Click_return_config'] = 'Clique %sAqui%s para voltar à Configuração Geral';

$lang['General_settings'] = 'Configuração geral do phpBB';
$lang['Server_name'] = 'Nome do Servidor';
$lang['Server_name_explain'] = 'O nome do Domínio de onde este fórum está rodando';
$lang['Script_path'] = 'Diretório do Script';
$lang['Script_path_explain'] = 'O diretório onde se encontra o phpBB em relação ao Dominio';
$lang['Server_port'] = 'Porta do Servidor';
$lang['Server_port_explain'] = 'A porta em que o seu servidor está rodando, normalmente 80 - mude apenas se for diferente';
$lang['Site_name'] = 'Nome do Site';
$lang['Site_desc'] = 'Descrição do Site';
//Change Lodo MOD | Início
$lang['Site_logo'] = 'Logotipo do Site';
$lang['Site_logo_explain'] = 'Essa é a URL do logotipo do seu site (em relação ao diretório raiz do seu phpBB)';
//Change Lodo MOD | Fim
$lang['Board_disable'] = 'Desativar';
$lang['Board_disable_explain'] = 'Isto torna os fóruns inacessíveis a Usuários. Não execute logout após Desativar os fóruns senão você não conseguirá entrar novamente.';
$lang['Board_disable_text'] = 'Texto de Desativação do Fórum';
$lang['Board_disable_text_explain'] = 'Digite uma justificativa para a desativação do Fórum.';

$lang['Acct_activation'] = 'Usar a função de ativação de registo';
$lang['Acc_None'] = 'Nunca'; // These three entries are the type of activation
$lang['Acc_User'] = 'Usuários';
$lang['Acc_Admin'] = 'Administradores';

$lang['Abilities_settings'] = 'Configurações básicas de Usuários e Fóruns';
$lang['Max_poll_options'] = 'Número máximo de opções nas Enquetes';
$lang['Flood_Interval'] = 'Intervalo de \'Flood\'';
$lang['Flood_Interval_explain'] = 'Tempo em segundos que um Usuário deve aguardar entre o envio de mensagens';
$lang['Board_email_form'] = 'Email de Usuários via fórum';
$lang['Board_email_form_explain'] = 'Função que permite Usuários enviar email a outros através do phpBB';
$lang['Topics_per_page'] = 'Núm. Máx. de Tópicos por página';
$lang['Posts_per_page'] = 'Núm. Máx. de Mensagens por página';
$lang['Hot_threshold'] = 'Núm. Máx. de Mensagens para Tornar um tópico Popular';
$lang['Default_style'] = 'Estilo padrão';
$lang['Override_style'] = 'Substituir estilo do Usuário';
$lang['Override_style_explain'] = 'Força o uso do estilo padrão em vez do escolhido pelos Usuários';
$lang['Default_language'] = 'Idioma padrão';
$lang['Date_format'] = 'Formato da Data';
$lang['System_timezone'] = 'Fuso Horário do sistema';
$lang['Enable_gzip'] = 'Ativar compressão GZip';
$lang['Enable_prune'] = 'Ativar Desbastar Fórum';
$lang['Allow_HTML'] = 'Permitir HTML';
$lang['Allow_BBCode'] = 'Permitir BBCode';
// Anti-Spam MOD | Início
$lang['Allow_BBCode_IMG_Post'] = 'Permitir tag [img] nas Mensagens?';
$lang['Allow_BBCode_URL_Post'] = 'Permitir tag [url] nas Mensagens?';
$lang['Allow_BBCode_IMG_Sig'] = 'Permitir tag [img] na Assinatura?';
$lang['Allow_BBCode_URL_Sig'] = 'Permitir tag [url] na Assinatura?';
// Anti-Spam MOD | Fim
$lang['Allowed_tags'] = 'Tags HTML permitidas';
$lang['Allowed_tags_explain'] = 'Separe as Tags com vírgulas';
$lang['Allow_smilies'] = 'Permitir Smileys';
$lang['Smilies_path'] = 'Pasta dos Smileys';
$lang['Smilies_path_explain'] = 'Pasta sob o diretório raiz do seu phpBB p.e. images/smilies';
$lang['Allow_sig'] = 'Permitir Assinaturas';
$lang['Max_sig_length'] = 'Comprimento Máx. da Assinatura';
$lang['Max_sig_length_explain'] = 'Número máximo de caracteres permitidos na assinatura do Usuário';
$lang['Allow_name_change'] = 'Permitir mudança de Nome de Usuário';

$lang['Avatar_settings'] = 'Configuração de Avatares';
$lang['Allow_local'] = 'Permitir Galeria de Avatares';
$lang['Allow_remote'] = 'Permitir Avatares remotos';
$lang['Allow_remote_explain'] = 'Avatares ligados a partir de algum outro site';
$lang['Allow_upload'] = 'Permitir o Envio de Avatares';
$lang['Max_filesize'] = 'Tamanho Máx. do Arquivo de Avatar';
$lang['Max_filesize_explain'] = 'Para arquivos de Avatares Enviados';
$lang['Max_avatar_size'] = 'Dimensões Máx. dos Avatares';
$lang['Max_avatar_size_explain'] = '(Altura x Largura em pixels)';
$lang['Avatar_storage_path'] = 'Pasta de armazenamento dos Avatares';
$lang['Avatar_storage_path_explain'] = 'Pasta sob o diretório raiz do seu phpBB p.e. images/avatars';
$lang['Avatar_gallery_path'] = 'Pasta da Galeria de Avatares';
$lang['Avatar_gallery_path_explain'] = 'Pasta sob o diretório raiz do seu phpBB p. e. images/avatars/gallery';

$lang['COPPA_settings'] = 'Configuração de COPPA';
$lang['COPPA_fax'] = 'Fax para COPPA';
$lang['COPPA_mail'] = 'Endereço de email para COPPA';
$lang['COPPA_mail_explain'] = 'Este é o endereço de email para o qual os pais enviarão os formulários de registo de COPPA';

$lang['Email_settings'] = 'Configuração de Email';
$lang['Admin_email'] = 'Endereço de Email do Administrador';
$lang['Email_sig'] = 'Assinatura do Email';
$lang['Email_sig_explain'] = 'Este texto será anexado a todos os emails enviados pelo fórum';
$lang['Use_SMTP'] = 'Usar Servidor de SMTP para o envio';
$lang['Use_SMTP_explain'] = 'Selecione SIM caso queira ou tenha que enviar Email através de um servidor em vez da função mail() do PHP';
$lang['SMTP_server'] = 'Endereço do servidor de SMTP';
$lang['SMTP_username'] = 'Nome de Usuário do SMTP';
$lang['SMTP_username_explain'] = 'Só escreva o nome de Usuário se o seu servidor de SMTP assim o exiga';
$lang['SMTP_password'] = 'Senha para o SMTP';
$lang['SMTP_password_explain'] = 'Só escreva a Senha caso o seu servidor de SMTP assim o exiga';

$lang['Disable_privmsg'] = 'Mensagens Particulares';
$lang['Inbox_limits'] = 'Núm. Total de mensagens permitidas na Caixa de Entrada';
$lang['Sentbox_limits'] = 'Núm. Total de mensagens permitidas na Caixa de Saída';
$lang['Savebox_limits'] = 'Núm. Total de mensagens permitidas na Caixa de Mensagens Salvas';

$lang['Custom_Footer_and_Header_settings'] = 'Configurações de Cabeçalhos e Rodapés Personalizados';
$lang['Custom_Overall_Header'] = 'Cabeçalho Geral Personalizado';
$lang['Custom_Overall_Footer'] = 'Rodapé Geral Personalizado';
$lang['Custom_Simple_Header'] = 'Cabeçalho Simples Personalizado';
$lang['Custom_Simple_Footer'] = 'Rodapé Simples Personalizado';

$lang['Cookie_settings'] = 'Configurações de Cookies';
$lang['Cookie_settings_explain'] = 'Esses detalhes definem como os cookies serão enviados para os browsers de seus Usuários. Na maioria dos casos os valores padrão para a configuração dos cookies deverão ser suficientes mas caso necessite mudá-los faça-o com cuidado pois uma configuração incorreta poderá impedir os Usuários de ligarem-se.';
$lang['Cookie_domain'] = 'Domínio do Cookie';
$lang['Cookie_name'] = 'Nome do Cookie';
$lang['Cookie_path'] = 'Pasta do Cookie';
$lang['Cookie_secure'] = 'Cookie Seguro [ https:// ]';
$lang['Cookie_secure_explain'] = 'Se o seu servidor estiver rodando através de SSL ative isto, caso contrário deixe desligado';
$lang['Session_length'] = 'Tempo da sessão [ segundos ]';

//
// Forum Management
//
$lang['Forum_admin'] = 'Administração dos Fóruns';
$lang['Forum_admin_explain'] = 'A partir desse Painel você pode adicionar, remover, editar, reordenar e resincronizar Categorias e Fóruns.';
$lang['Edit_forum'] = 'Editar fórum';
$lang['Create_forum'] = 'Criar novo Fórum';
$lang['Create_category'] = 'Criar nova Categoria';
$lang['Remove'] = 'Remover';
$lang['Action'] = 'Ação';
$lang['Update_order'] = 'Atualizar Ordem';
$lang['Config_updated'] = 'Configuração do Fórum atualizada com sucesso';
$lang['Edit'] = 'Editar';
$lang['Delete'] = 'Remover';
$lang['Move_up'] = 'Mover - Cima';
$lang['Move_down'] = 'Mover - Baixo';
$lang['Resync'] = 'Resincronizar';
$lang['No_mode'] = 'Não foi definido nenhum modo';
$lang['Forum_edit_delete_explain'] = 'O formulário abaixo lhe permitirá personalizar todas as Opções Gerais do Painel. Para Configurações de Usuários e use os atalhos no painel do lado esquerdo.';

$lang['Move_contents'] = 'Mover todo o conteúdo';
$lang['Forum_delete'] = 'Remover Fórum';
$lang['Forum_delete_explain'] = 'O formulário abaixo lhe permitirá remover um fórum (ou categoria) e decidir onde pretende colocar todos os tópicos (ou fóruns) existentes.';

$lang['Forum_settings'] = 'Configurações Gerais dos Fóruns';
$lang['Forum_name'] = 'Nome do Fórum';
$lang['Forum_desc'] = 'Descrição';
$lang['Forum_status'] = 'Estado';
$lang['Forum_pruning'] = 'Auto-desbastar';

$lang['prune_freq'] = 'Verificar o tempo dos tópicos a cada';
$lang['prune_days'] = 'Remover tópicos que não tenham sido respondidos em';
$lang['Set_prune_data'] = 'Você ativou a função para auto-desbastar o fórum mas não especificou a frequência ou número de dias em que o mesmo deve ser feito. Volte e especifice esse valor';

$lang['Move_and_Delete'] = 'Mover e Remover';

$lang['Delete_all_posts'] = 'Remover todas as mensagens';
$lang['Nowhere_to_move'] = 'Não há local para onde mover';

$lang['Edit_Category'] = 'Editar Categoria';
$lang['Edit_Category_explain'] = 'Use este formulário para mudar o nome da Categoria.';

$lang['Forums_updated'] = 'Informação de Fórum e Categoria atualizada com sucesso ';

$lang['Must_delete_forums'] = 'Você precisa remover todos os fóruns antes de remover esta categoria';

$lang['Click_return_forumadmin'] = 'Clique %sAqui%s para voltar à Administração dos Fóruns';


//
// Smiley Management
//
$lang['smiley_title'] = 'Administração de Smileys';
$lang['smile_desc'] = 'A partir dessa página você pode adicionar, remover e editar as emoções ou smileys que os Usuários poderão usar nas suas mensagens.';

$lang['smiley_config'] = 'Configuração de Smileys';
$lang['smiley_code'] = 'Código para o Smiley';
$lang['smiley_url'] = 'Arquivo da imagem do Smiley';
$lang['smiley_emot'] = 'Emoção do Smiley';
$lang['smile_add'] = 'Adicionar um Smiley';
$lang['Smile'] = 'Smiley';
$lang['Emotion'] = 'Emoção';

$lang['Select_pak'] = 'Selecionar arquivo de pacote (.pak)';
$lang['replace_existing'] = 'Substituir Smiley existente';
$lang['keep_existing'] = 'Manter o Smiley existente';
$lang['smiley_import_inst'] = 'Você deve descompactar o pacote de Smileys e colocar todos os arquivos na respectiva pasta da sua instalação do phpBB.  Selecione depois a informação correta neste formulário para importar o pacote.';
$lang['smiley_import'] = 'Importar pacote';
$lang['choose_smile_pak'] = 'Escolher arquivo de pacote de Smileys (.pak)';
$lang['import'] = 'Importar';
$lang['smile_conflicts'] = 'Em caso de conflitos:';
$lang['del_existing_smileys'] = 'Remova os Smileys existentes antes de importar o pacote';
$lang['import_smile_pack'] = 'Importar Pacote de Smiley';
$lang['export_smile_pack'] = 'Criar Pacote';
$lang['export_smiles'] = 'Para criar um pacote de Smileys a partir dos atualmente instalados, primeiro clique %sAqui%s para fazer download do pacote smiles.pak. Renomeie este arquivo apropriadamente, mantendo a extensão .pak.  Então crie um arquivo zip contendo todos as suas imagens de Smiley mais este arquivo de configuração (.pak).';

$lang['smiley_add_success'] = 'O Smiley foi adicionado com sucesso';
$lang['smiley_edit_success'] = 'O Smiley foi atualizado com sucesso';
$lang['smiley_import_success'] = 'O pacote de Smiley foi importado com sucesso!';
$lang['smiley_del_success'] = 'O Smiley foi removido com sucesso';
$lang['Click_return_smileadmin'] = 'Clique %sAqui%s para voltar à Administração de Smileys';


//
// User Management
//
$lang['User_admin'] = 'Gerência de Usuários';
$lang['User_admin_explain'] = 'Aqui você pode mudar a informação dos seus Usuários além de algumas opções específicas. Para modificar as permissões de Usuários usw o painel de <b>Permissões</b> para Usuários e Grupos.';

$lang['Look_up_user'] = 'Procurar Usuário';

$lang['Admin_user_fail'] = 'Não foi possível Atualizar o perfil de Usuário.';
$lang['Admin_user_updated'] = 'O perfil de Usuário foi atualizado com sucesso.';
$lang['Click_return_useradmin'] = 'Clique %sAqui%s para voltar à Gerência de Usuários';

$lang['User_delete'] = 'Remover este Usuário';
$lang['User_delete_explain'] = 'Clique aqui para remover o Usuário. Esta operação tem efeitos permanentes.';
$lang['User_deleted'] = 'Usuário removido com sucesso.';

$lang['User_status'] = 'Usuário está ativo';
$lang['User_allowpm'] = 'Pode enviar Mensagens Privadas';
$lang['User_allowavatar'] = 'Pode mostrar Avatar';

$lang['Admin_avatar_explain'] = 'Aqui você pode ver e remover o Avatar atual do Usuário.';

$lang['User_special'] = 'Campos especiais Apenas para Administradores';
$lang['User_special_explain'] = 'Estes campos não podem ser modificados por Usuários. Aqui você pode especificar estado do usuário bem como outras opções que não são dadas aos Usuários.';
// Added for enhanced user management
$lang['User_lookup_explain'] = 'Você pode consultar Usuários especificando um ou mais dos critérios abaixo. Não é necessário caracteres como (*, ?, etc), eles serão adicionados automaticamente.';
$lang['One_user_found'] = 'Foi encontrado apenas um Usuário, você será levado para esse usuário';
$lang['Click_goto_user'] = 'Clique %sAqui%s para editar o Perfil desse Usuário';
$lang['User_joined_explain'] = 'A Sintaxe usada é idêntica a função PHP <a href=\"http://www.php.net/strtotime\" target=\"_other\">strtotime()</a>';


//
// Group Management
//
$lang['Group_administration'] = 'Administração de Grupos';
$lang['Group_admin_explain'] = 'A partir desse painel você pode administrar todos os seus Grupos de Usuários, você pode: remover, criar e editar Grupos existentes. Você pode eleger moderadores, abrir/fechar Grupos e definir Nome e Descrição de cada grupo.';
$lang['Error_updating_groups'] = 'Houve um erro ao Atualizar os grupos';
$lang['Updated_group'] = 'O grupo foi atualizado com sucesso';
$lang['Added_new_group'] = 'O novo grupo foi criado com sucesso';
$lang['Deleted_group'] = 'O grupo foi removido com sucesso';
$lang['New_group'] = 'Criar um Grupo';
$lang['Edit_group'] = 'Editar o Grupo';
$lang['group_name'] = 'Nome do Grupo';
$lang['group_description'] = 'Descrição do Grupo';
$lang['group_moderator'] = 'Moderador do Grupo';
$lang['group_status'] = 'Estado do Grupo';
$lang['group_open'] = 'Aberto';
$lang['group_closed'] = 'Fechado';
$lang['group_hidden'] = 'Oculto';
$lang['group_delete'] = 'Remover grupo';
$lang['group_delete_check'] = 'Remover este grupo';
$lang['submit_group_changes'] = 'Enviar Alterações';
$lang['reset_group_changes'] = 'Restaurar Alterações';
$lang['No_group_name'] = 'Deve ser especificado um nome para este grupo';
$lang['No_group_moderator'] = 'Deve ser especificado um moderador para este grupo';
$lang['No_group_mode'] = 'Deve ser especificado um modo para este grupo, aberto ou fechado';
$lang['No_group_action'] = 'Nenhuma ação foi especificada';
$lang['delete_group_moderator'] = 'Remover o moderador antigo do grupo?';
$lang['delete_moderator_explain'] = 'Se esiver alterando o moderador do grupo assinale aqui para remover o moderador anterior.  Caso contrário não assinale e o Usuário passará a ser um membro normal do grupo.';
$lang['Click_return_groupsadmin'] = 'Clique %sAqui%s para voltar à Administração de Grupos.';
$lang['Select_group'] = 'Selecionar um Grupo';
$lang['Look_up_group'] = 'Procurar Grupo';


//
// Prune Administration
//
$lang['Forum_Prune'] = 'Desbastar Fórum';
$lang['Forum_Prune_explain'] = 'Esta operação removerá qualquer tópico que não possua resposta dentro do limite de dias especificado. Se não for mencionado um número de dias todos os tópicos serão removidos. Isto não remove tópicos que possuam uma Enquete ativa nem Anúncios. Você terá que removê-los manualmente.';
$lang['Do_Prune'] = 'Desbastar';
$lang['All_Forums'] = 'Todos os Fóruns';
$lang['Prune_topics_not_posted'] = 'Remover todos os tópicos sem resposta durante um período de ';
$lang['Topics_pruned'] = 'Tópicos Removidos';
$lang['Posts_pruned'] = 'Mensagens Removidas';
$lang['Prune_success'] = 'Desbaste de fóruns concluído com sucesso';


//
// Word censor
//
$lang['Words_title'] = 'Censura de Palavras';
$lang['Words_explain'] = 'A partir desse painel de controle você pode adicionar, editar e remover palavras que serão automaticamente censuradas em seus fóruns. O uso dessas palavras será também interditado no registo de Nomes de Usuários. Podem ser usados asteriscos (*) aumentando as possibilidades de abranger variações da mesma palavra. Por exemplo, *testa* abrangerá detestável, testa* abrangerá testando, *testa abrangerá detesta.';
$lang['Word'] = 'Palavra';
$lang['Edit_word_censor'] = 'Editar palavra Censurada';
$lang['Replacement'] = 'Substituição';
$lang['Add_new_word'] = 'Adicionar nova palavra';
$lang['Update_word'] = 'Atualizar palavra censurada';

$lang['Must_enter_word'] = 'Você deve escrever a palavra e o que irá substituí-la';
$lang['No_word_selected'] = 'Não foi escolhida palavra para editar';

$lang['Word_updated'] = 'A palavra censurada foi atualizada com sucesso';
$lang['Word_added'] = 'A palavra a censurar foi adicionada com sucesso';
$lang['Word_removed'] = 'A palavra censurada foi removida com sucesso';

$lang['Click_return_wordadmin'] = 'Clique %sAqui%s para voltar à Censura de Palavras';


//
// Mass Email
//
$lang['Mass_email_explain'] = 'A partir daqui você pode enviar um email para todos os Usuários dos fóruns ou Usuários membros de um certo grupo, pra tal empregue o endereço de <b>Email Administrativo</b> préviamente configurado. Caso seja enviado um email para um número elevado de pessoas aguarde um pouco após clicar abaixo em <b>Email</b> e não pare a página durante o processo - é normal que o envio de um email em massa demore um pouco, mas você será avisado quando o processo for concluído.';
$lang['Compose'] = 'Compor';

$lang['Recipients'] = 'Destinatários';
$lang['All_users'] = 'Todos os Usuários';

$lang['Email_successfull'] = 'A sua mensagem foi enviada';
$lang['Click_return_massemail'] = 'Clique %sAqui%s para voltar ao Formulário de Email em Massa';


//
// Ranks admin
//
$lang['Ranks_title'] = 'Gerência de Rank';
$lang['Ranks_explain'] = 'Usando este painel poderá adicionar, editar, ver e remover Ranks de Usuários. Poderá também criar Ranks específicos podendo os mesmos ser aplicados a um Usuário através do  painel de <b>Administração de Usuários</b>';

$lang['Add_new_rank'] = 'Adicionar um novo Rank';

$lang['Rank_title'] = 'Título do Rank';
$lang['Rank_special'] = 'Rank Especial';
$lang['Rank_minimum'] = 'Núm. Mín. de Mensagens';
$lang['Rank_maximum'] = 'Núm. Máx. de Mensagens';
$lang['Rank_image'] = 'Imagem do Rank (relativo ao diretório raiz do phpBB)';
$lang['Rank_image_short'] = 'Imagem do Rank';  //Display Rank Image on Overview MOD
$lang['Rank_image_explain'] = 'Use isto para definir uma pequena imagem associada ao Rank';

$lang['Must_select_rank'] = 'Você deve escolher um Rank';
$lang['No_assigned_rank'] = 'Não foi especificado nenhum Rank Especial';

$lang['Rank_updated'] = 'O Rank foi atualizado com sucesso';
$lang['Rank_added'] = 'O Rank foi adicionado com sucesso';
$lang['Rank_removed'] = 'O Rank foi removido com sucesso';

$lang['Click_return_rankadmin'] = 'Clique %sAqui%s para voltar a Gerência de Ranks';


//
// Disallow Username Admin
//
$lang['Disallow_control'] = 'Controle de Nomes de Usuários Proibídos';
$lang['Disallow_explain'] = 'Aqui você poderá controlar nomes de Usuários que não serão permitidos a serem usados.  Nomes proibídos podem conter asteriscos '*' para abranger um maior número de variações na palavra.  Note que não podem ser especificados Nomes de Usuários que já se encontrem registados, devendo primeiro ser removidos e então especificá-los aqui para não mais serem usados.';

$lang['Delete_disallow'] = 'Remover';
$lang['Delete_disallow_title'] = 'Remover um Nome de Usuário Proibido';
$lang['Delete_disallow_explain'] = 'Você pode remover um Nome de Usuário selecionando-o nessa lista e clicando em Remover';

$lang['Add_disallow'] = 'Adicionar';
$lang['Add_disallow_title'] = 'Adicionar um Nome de Usuário Proibido';
$lang['Add_disallow_explain'] = 'Use asteriscos '*' se necessário';

$lang['No_disallowed'] = 'Não há Nomes de Usuários Proibidos';

$lang['Disallowed_deleted'] = 'O Nome de Usuário Proibido foi removido com sucesso';
$lang['Disallow_successful'] = 'O Nome de Usuário Proibido foi adicionado com sucesso';
$lang['Disallowed_already'] = 'O nome que especificou não pode ser proibido. Pode ser que já exista na lista de Nomes Proibidos, na lista de Palavras Censuradas ou encontre-se atualmente em uso por algum Usuário registado';

$lang['Click_return_disallowadmin'] = 'Clique %sAqui%s para voltar aa Painel de Controle de Nomes de Usuários Proibidos';


//
// Styles Admin
//
$lang['Styles_admin'] = 'Gerência de Estilos';
$lang['Styles_explain'] = 'Usando este painel poderá adicionar, remover e administrar estilos (Modelos e Temas) disponíveis aos Usuários.';
$lang['Styles_addnew_explain'] = 'Este painel é destinado à listagem dos Temas de fórum para os Modelos que presentemente possui e ainda não se encontram instalados na base de dados do phpBB. Para instalar um tema específico clique em <b>Instalar</b> ao lado desse item.';

$lang['Select_template'] = 'selecionar um Modelo';

$lang['Style'] = 'Estilo';
$lang['Template'] = 'Modelo';
$lang['Install'] = 'Instalar';
$lang['Download'] = 'Download';

$lang['Edit_theme'] = 'Editar Tema';
$lang['Edit_theme_explain'] = 'Configurar o tema seleccionado no formulário abaixo';

$lang['Create_theme'] = 'Criar Tema';
$lang['Create_theme_explain'] = 'Use o formulário abaixo para criar um Tema novo para o Modelo existente. Quando aplicar cores (que devem ser escritas num formato hexadecimal) não deve ser incluido o # inicial, ou seja, CCCCCC é a forma correcta de escrever, #CCCCCC é incorrecto.';

$lang['Export_themes'] = 'Exportar Temas';
$lang['Export_explain'] = 'Usar este painel para exportar informação de um Tema para um dado Modelo. Escolha um Modelo na lista e será automaticamente criado um arquivo de configuração do tema que irá ser guardado e instalado na pasta do Modelo seleccionado. Caso não for possível guardar o arquivo por si próprio será dada a opção para ser feito o seu \'download\'. Deve haver ou ser dada permissão de escrita ao servidor de WEB para a pasta do Modelo seleccionado de forma que que o arquivo seja guardado. Para mais informação sobre esta operação ver o <b>phpBB 2 users guide</b>.';

$lang['Theme_installed'] = 'O tema seleccionado foi instalado com sucesso';
$lang['Style_removed'] = 'O estilo seleccionado foi removido da base de dados. Para remover completamente este estilo do seu sistema deve apagar o estilo apropriado na pasta dos Modelos.';
$lang['Theme_info_saved'] = 'A informação do tema para o Modelo seleccionado foi guardada. Você deve agora mudar as permissões para \'read-only\' no arquivo theme_info.cfg (e caso aplicável na pasta de Modelos)';
$lang['Theme_updated'] = 'O tema seleccionado foi actualizado. Você deve agora exportar a nova configuração do tema';
$lang['Theme_created'] = 'Tema criado. Você deve agora exportar o tema para o arquivo de configuração do tema como segurança ou usar noutro local';

$lang['Confirm_delete_style'] = 'Tem a certeza que quer remover este estilo?';

$lang['Download_theme_cfg'] = 'Não foi possível escrever o arquivo de informação do tema. clique o botão abaixo para fazer o \'download\' deste arquivo com o seu \'browser\'. Logo que termine o download poderá trasferir o arquivo para a pasta contendo os arquivos do Modelo. Pode depois arrumar os arquivos para distribuição ou usar noutro local, se assim o pretender';
$lang['No_themes'] = 'O Modelo que seleccionou nao possúi temas anexos. Para criar um tema novo clique em Criar no painel do lado esquerdo';
$lang['No_template_dir'] = 'Não foi possível abrir a pasta de Modelos. Pode ser que não haja possibilidade de ser lido pelo servidor de Web ou a pasta não exista';
$lang['Cannot_remove_style'] = 'Não pode remover o estilo seleccionado porque é presentemente o estilo básico do fórum. Mudar o estilo básico e tentar novamente.';
$lang['Style_exists'] = 'O nome para o estilo que seleccionou já existe, voltar atrás e escolher um nome diferente.';

$lang['Click_return_styleadmin'] = 'clique %sAqui%s para voltar à Gerência de Estilos';

$lang['Theme_settings'] = 'Configuração de Temas';
$lang['Theme_element'] = 'Elemento de Tema';
$lang['Simple_name'] = 'Nome Simples';
$lang['Value'] = 'Valor';
$lang['Save_Settings'] = 'Guardar Configuração';

$lang['Stylesheet'] = 'CSS Stylesheet';
$lang['Background_image'] = 'Imagem de Background';
$lang['Background_color'] = 'Cor de Background';
$lang['Theme_name'] = 'Nome do Tema';
$lang['Link_color'] = 'Cor de Atalho';
$lang['Text_color'] = 'Cor de Texto';
$lang['VLink_color'] = 'Cor de Atalho Visitado';
$lang['ALink_color'] = 'Cor de Atalho Ativo';
$lang['HLink_color'] = 'Cor de Atalho Hover';
$lang['Tr_color1'] = 'Cor 1 de Coluna de Tabela';
$lang['Tr_color2'] = 'Cor 2 de Coluna de Tabela';
$lang['Tr_color3'] = 'Cor 3 de Coluna de Tabela';
$lang['Tr_class1'] = 'Classe 1 de Coluna de Tabela';
$lang['Tr_class2'] = 'Classe 2 de Coluna de Tabela';
$lang['Tr_class3'] = 'Classe 3 de Coluna de Tabela';
$lang['Th_color1'] = 'Cor 1 de Cabeça de Tabela';
$lang['Th_color2'] = 'Cor 2 de Cabeça de Tabela';
$lang['Th_color3'] = 'Cor 3 de Cabeça de Tabela';
$lang['Th_class1'] = 'Classe 1 de Cabeça de Tabela';
$lang['Th_class2'] = 'Classe 2 de Cabeça de Tabela';
$lang['Th_class3'] = 'Classe 3 de Cabeça de Tabela';
$lang['Td_color1'] = 'Cor 1 de Célula de Tabela';
$lang['Td_color2'] = 'Cor 2 de Célula de Tabela';
$lang['Td_color3'] = 'Cor 3 de Célula de Tabela';
$lang['Td_class1'] = 'Classe 1 de Célula de Tabela';
$lang['Td_class2'] = 'Classe 2 de Célula de Tabela';
$lang['Td_class3'] = 'Classe 3 de Célula de Tabela';
$lang['fontface1'] = 'Fonte Face 1';
$lang['fontface2'] = 'Fonte Face 2';
$lang['fontface3'] = 'Fonte Face 3';
$lang['fontsize1'] = 'Tamanho 1 de Fonte';
$lang['fontsize2'] = 'Tamanho 2 de Fonte';
$lang['fontsize3'] = 'Tamanho 3 de Fonte';
$lang['fontcolor1'] = 'Cor 1 de Fonte';
$lang['fontcolor2'] = 'Cor 2 de Fonte';
$lang['fontcolor3'] = 'Cor 3 de Fonte';
$lang['span_class1'] = 'Classe 1 - Extensão';
$lang['span_class2'] = 'Classe 2 - Extensão';
$lang['span_class3'] = 'Classe 3 - Extensão';
$lang['img_poll_size'] = 'Tamanho da Imagem da Votação [px]';
$lang['img_pm_size'] = 'Tamanho de Estado de Mensagem Privada [px]';


// Global announcment MOD
$lang['Globalannounce'] = 'Anúncio Global';

// Last Visit MOD
$lang['Hidde_last_logon'] = 'Ocultar data de Última Visita';
$lang['Hidde_last_logon_expain'] = 'Se escolher SIM, a data da última visita do usuário, será oculta para os usuários exceto para ADMINISTRADORES';

// Real Name MOD
$lang['Hidde_real_name'] = 'Ocultar Nome Real';
$lang['Hidde_real_name_explainn'] = 'Se escolher SIM, o nome real dos usuários será oculto para os usuários exceto para ADMINISTRADORES.';

// Yellow Card Admin MOD
$lang['Ban'] = 'Banir';
$lang['Max_user_bancard'] = 'Núm. Máx. de cartões amarelos';
$lang['Max_user_bancard_explain'] = 'Se um usuário exceder esse limite de cartões amarelos recebidos, o usuário será eexpulso';
$lang['ban_card'] = 'Cartões Amarelos';
$lang['ban_card_explain'] = 'O usuário será banido quando ele/ela exceder %d cartões amarelos';
$lang['Greencard'] = 'Cartão Verde';
$lang['Bluecard'] = 'Cartão Azul';
$lang['Bluecard_limit'] = 'Intervalo de Cartões Azuis';
$lang['Bluecard_limit_explain'] = 'Notificar o moderador novamente a cada x cartões azuis dados a uma mensagem';
$lang['Bluecard_limit_2'] = 'Limite de Cartões Azuis';
$lang['Bluecard_limit_2_explain'] = 'A primeira notificação será enviada ao moderador, quando a mensagem receber essa quantidade de cartões azuis';


//
// Install Process
//
$lang['Welcome_install'] = 'Bem-vindo à Instalação do phpBB 2';
$lang['Initial_config'] = 'Configuração Básica';
$lang['DB_config'] = 'Configuração do Banco de Dados';
$lang['Admin_config'] = 'Configuração de Administração';
$lang['continue_upgrade'] = 'Logo que tenha terminado o download do arquivo de configuração para o seu computador poderá clicar em \"Continuar a Actualização\" abaixo para continuar o processo.  Aguarde que seja feito o upload do arquivo de configuração até que o processo de atualização esteja completo.';
$lang['upgrade_submit'] = 'Continuar a Atualização';

$lang['Installer_Error'] = 'Ocorreu um erro durante a instalação';
$lang['Previous_Install'] = 'Foi detectada uma instalação anterior';
$lang['Install_db_error'] = 'Ocorreu um erro ao tentar Atualizar o banco de dados';

$lang['Re_install'] = 'A sua instalação anterior ainda se encontra ativa. <br /><br />Se pretende reinstalar phpBB 2 deverá clicar no botão Sim abaixo. Ter em atenção que ao fazê-lo irá destruir toda a informação existente, não sendo feitas cópias de segurança! O Nome de Usuário e Senha de administrador que tem usado para ligar ao fórum será recriada após esta reinstalação, nao sendo qualquer outros dados de configuração serão guardados. <br /><br />Pense cautelosamente antes de carregar em Sim!';

$lang['Inst_Step_0'] = 'Obrigado por ter escolhido phpBB 2. De modo a completar esta instalação preencha os detalhes pedidos abaixo. De notar que a base de dados onde a informação do fórum será instalada deve existir já. Caso se encontre a instalar numa base de dados que use ODBC, ou seja, MS Access, deve primeiro ser criado um DSN.';

$lang['Start_Install'] = 'Começar a Instalação';
$lang['Finish_Install'] = 'Terminar a Instalação';

$lang['Default_lang'] = 'Idioma padrão do Fórum';
$lang['DB_Host'] = 'Hostname do Sevidor da Base de Dados / DSN';
$lang['DB_Name'] = 'Nome do Banco de Dados';
$lang['DB_Username'] = 'Nome de Usuário no Banco de Dados';
$lang['DB_Password'] = 'Senha no Banco de Dados';
$lang['Database'] = 'Seu Banco de Dados';
$lang['Install_lang'] = 'Escolher o Idioma para a Instalação';
$lang['dbms'] = 'Tipo de Banco de Dados';
$lang['Table_Prefix'] = 'Prefixo para as tabelas no Banco de Dados';
$lang['Admin_Username'] = 'Nome de Usuário do Administrador';
$lang['Admin_Password'] = 'Senha do Administrador';
$lang['Admin_Password_confirm'] = 'Senha do Administrador [Confirmar]';

$lang['Inst_Step_2'] = 'O seu Nome de Usuário para Administrador foi criado.  Neste momento a Instalação Básica está concluída. Irá ser conduzido agora a um painel onde poderá administrar a sua nova instalação. Verificar os detalhes de Configuração Geral e proceder as mudanças necessárias. Obrigado por usar phpBB 2.';

$lang['Unwriteable_config'] = 'O seu arquivo de configuração não pode ser escrito neste momento. Será feita uma cópia do arquivo quando carregar no botão abaixo. Deverá colocar este arquivo na mesma pasta que o phpBB 2. Uma vez concluido, você deverá ligar-se usando o Nome de Usuário de administrador e respectiva senha que forneceu anteriormente visitando de seguida o Painel de Administração (um atalho irá surgir na parte iferior de cada janela) para verificar a configuração geral. Obrigado por escolher phpBB 2.';
$lang['Download_config'] = 'Download a Configuração';

$lang['ftp_choose'] = 'Escolher um método para Download';
$lang['ftp_option'] = '<br />Visto as extensões de FTP se encontrarem ativas nesta versão de PHP deve-lhe ter sido também dada a opção para primeiro tentar automaticamente FTP o arquivo de configuração para o local certo.';
$lang['ftp_instructs'] = 'Escolheu para FTP automaticamente o arquivo para a conta contendo phpBB 2.  Por favor forneça a informação abaixo para facilitar o processo. De notar que o \'path\' do FTP deverá ser exactamente o mesmo via ftp para a instalação do seu phpBB 2 como se estivesse a efectuar ftp usando um cliente normal.';
$lang['ftp_info'] = 'Escrever a informação do FTP';
$lang['Attempt_ftp'] = 'Tentando FTP o arquivo de configuração para o local corecto';
$lang['Send_file'] = 'Apenas enviar o arquivo para mim e eu farei o FTP manualmente';
$lang['ftp_path'] = 'Path de FTP para o phpBB 2';
$lang['ftp_username'] = 'O seu nome de Usuário para o FTP';
$lang['ftp_password'] = 'A sua senha para o FTP';
$lang['Transfer_config'] = 'Iniciar a Transferência';
$lang['NoFTP_config'] = 'A tentativa de FTP o arquivo de configuração para o local correcto falhou.  Por favor fazer o download do mesmo e efectuar o FTP manualmente.';

$lang['Install'] = 'Instalar';
$lang['Upgrade'] = 'Atualizar';


$lang['Install_Method'] = 'Escolher o seu método de instalação';

$lang['Install_No_Ext'] = 'A configuração de php no seu server não aceita o tipo de base de dados que escolheu';

$lang['Install_No_PCRE'] = 'O phpBB2 requer o módulo para php \'Perl-Compatible Regular Expressions\' cuja configuração do seu php parece não aceitar!';

//
// That's all Folks!
// -------------------------------------------------

?>