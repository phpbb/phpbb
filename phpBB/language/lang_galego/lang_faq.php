<?php
/***************************************************************************
 *                          lang_faq.php [Galician]
 *                            -------------------
 *   begin                : Wednesday Oct 3, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: lang_faq.php,v 0.9 2002/03/05 16:42:08 Pato[100%Q]
 *
 ***************************************************************************/

  /****************************************************************************
 * Translation by:
 * Sergio Ares Chao :: sergio@ciberagendas.com
 * Thanks to Marcos Ferreira for reviewing
 ****************************************************************************/

/***************************************************************************
 *
 *   Esta es una aplicación de software libre, puede re-distribuirla e/o modificarla
 *   bajo los terminos de la GNU (Licencia Pública General), la cual fue publicada
 *   por la Free Software Foundation (Fundación del Software Libre). Esto en la licencia
 *   de la versión 2 o posterior.
 *
 ***************************************************************************/
 
// 
// Para engadir unha entrada ás FAQ só engada unha liña co seguinte formato:
// $faq[] = array("pregunta", "resposta");
// Se queres separar unha sección escribe $faq[] = array("--","Un texto de separación pode ir aqui se o desexas");
// Os enlaces creanse automaticamente :)
//
// NON ESQUEZA poñer ; ó final de cada liña.
// NON PONGA dobres comiñas (") nas entradas das súas FAQ, se é absolutamente necesario entón escribao asi: \"texto\";
//
// Os temas e entradas das FAQ aparecerán na mesma orde na que están neste archivo.
//

  
$faq[] = array("--","Sobre o ingreso (login) e rexistro");
$faq[] = array("¿Por que non podo ingresar?", "¿Xa se rexistrou? Debe rexistrarse no sistema antes de poder acceder a el. ¿Foi bloquedado no foro? (se é así aparecerá unha mensaxe ). Se isto sucede envíe unha mensaxe ó administrador do foro para coñece-la causa. Se se rexistrou e non foi bloquedado verifique que o seu nome de usuario e contrasinal coincidan, é o problema máis común. Se está seguro de que están correctos os datos, envíe unha mensaxe ó administrador, é posible que o foro estea mal configurado e/ou teña erros na programación.");
$faq[] = array("¿Por que me teño que rexistrar para todo?", "Non está obrigado a facelo, a decisión a toman os administradores e moderadores. Sen embargo estar rexistrado dalle moitas avantaxes que como usuario convidado non tería, como te-lo seu gráfico personalizado (avatar), mensaxes privadas, subscrición a grupos de usuarios, etc.... Só lle levará uns segundos, é moi recomendable.");
$faq[] = array("¿Por que a miña sesión de usuario expira automaticamente?", "Se non activa a opción <i>Ingresar automaticamente</i> cando ingresa ó foro, os seus datos gárdanse nunha cookie que se elimina ó sair da páxina ou en certo tempo. Isto prevén que a súa conta poda ser usada por alguén máis. Para que o sistema o recoñeza automaticamente só active a opción ó ingresar. NON é recomendable se accede ó foro desde unha computadora compartida (café-internet, biblioteca, colexio ...)");
$faq[] = array("¿Como evito aparecer nas listas de usuarios conectados?", "No seu perfil, atopará a opción <i>Oculta-lo meu estado de conexión</i>, se activa esta opción aparecerá só para os administradores, moderadores e para si mesmo, para os demais será un usuario oculto.");
$faq[] = array("¡Perdín o meu contrasinal!", "Calma, aínda que o seu contrasinal non poda ser recuperado pode desactivalo ou cambialo. Para facer isto diríxase á páxina de rexistro e faga click en <u>Olvidei o meu contrasinal</u>, siga as instruccións e estará dentro en moi pouco tempo");
$faq[] = array("¡Rexistreime e non podo ingresar!", "Primeiramente verifique os seus datos (usuario e contrasinal). Se todo é correcto hai dúas posibles razóns. Se o Sistema de Protección Infantil (COPPA) está activado e cando se rexistrou elixiu a opción <u>Son menor de 13 anos</u> entón terá que seguir algunhas instruccións que se lle darán para activa-la conta. Noutros casos o administrador pide que as contas se activen mediante un correo electrónico, así que revise o seu correo e confirme a súa subscrición. Algúns foros necesitan confirmación de rexistro. Se non sucede nada disto contacte co administrador do foro.");
$faq[] = array("Hai un tempo rexistreime, pero agora non podo ingresar", "As posibles razóns son: ingresou un nome de usuario ou contrasinal incorrectos (verifique a mensaxe que se lle enviou ó rexistrarse). É posible que o administrador borrase a súa conta, isto é moi frecuente, pois se non escribiu ningunha mensaxe en certo tempo o administrador pode borra-lo seu usuario para que a base de datos non se sature de rexistros. Se é así rexístrese de novo e participe :)");


$faq[] = array("--","Preferencias de usuario e configuracións");
$faq[] = array("¿Como podo cambia-la miña configuración?", "Tódolos seus datos e configuracións (se está rexistrado) están arquivados na nosa base de datos. Para modificalos prema no link <u>Perfil</u>, xeralmente atópase enriba de cada páxina.");
$faq[] = array("¡A hora nos foros non é correcta!", "As horas son correctas, é posible que estea vendo as horas correspondentes a outra zona horaria, se este é o caso, ingrese ó seu perfil e defina a súa zona horaria dacordo á súa ubicación (ex. Londres, Paris, New York, Sydney, etc.) Cambiando isto as horas deben aparecer dacordo á súa zona horaria. Se non se rexistrou é tempo de facelo :)");
$faq[] = array("Cambiei a zona horaria no meu perfil, pero o tempo segue a ser incorrecto", "Se está segur@ de que a zona horaria é correcta é posible que se deba ós horarios de veran implementados por algúns países.");
$faq[] = array("¡O meu idioma non está na lista!", "Isto pódese deber a que o administrador non instalou o paquete da súa linguaxe para o foro ou ninguén creou unha traducción :( se é así, síntase libre de facer unha traducción (miles de persoas agradeceranllo), a información atoparaa na páxina web do phpBB Group (Prema no enlace que se encontra ó final da páxina)");
$faq[] = array("¿Como podo poñer unha imaxe embaixo do meu nome de usuario?", "Hai dous tipos de imaxes embaixo do seu nome de usuario, a primeira é o RANGO, que está asociada co número de mensaxes que escribiu no foro (xeralmente son estrelas ou bloques), a segunda é o AVATAR, que é un gráfico xeralmente único e persoal, o administrador decide se se poden usar ou non, se é posible usalos podes introducilo no teu perfil. No caso de que non exista esa opción, contacte co administrador e pide que sexa activada esa opción :)");
$faq[] = array("¿Como podo cambia-lo meu RANGO?", "Non podes cambia-lo teu RANGO directamente, xa que este é asociado directamente co número de mensaxes posteadas ou ó teu estado de moderador, administrador ou RANGOS especiais. Por favor, non abuses de postear innecesariamente para incrementa-lo teu RANGO.");
$faq[] = array("Cando fago click sobre o link de e-mail pídeme que me rexistre.", "Para poder envia-lo e-mail a un usuario vía formulario (se o administrador o ten activado) necesitas estar rexistrado, isto para evitar SPAM ou mensaxes maliciosas de usuarios anónimos.");


$faq[] = array("--","Publicación de mensaxes");
$faq[] = array("¿Como podo publicar unha mensaxe no foro?", "Fácil, rexístrese como membro do foro (premendo no enlace de rexistro, xeralmente enriba de cada páxina), despois do rexistro prema en <i>Enviar nova mensaxe</i>, aí se lle presentará un panel co que facilmente publicará unha mensaxe :)");
$faq[] = array("¿Como podo editar ou borrar unha mensaxe?", "Se non é o administrador ou moderador do foro, só pode borra-las mensaxes que enviou vostede mesmo. Pode editar unha mensaxe facendo click en <i>editar</i>. Se alguén xa respondeu á súa mensaje, encontrará un pequeno texto ó pe da correción dicindo que foi modificado e as veces que o fixo, non aparece se foi un moderador ou o administrador quen o editou (a maioría das veces deixan unha mensaxe aclaratoria).");
$faq[] = array("¿Como podo engadir unha sinatura á miña mensaxe?", "Para inserir unha sinatura na súa mensaxe primeiro debe crear unha, isto faise modificando o seu perfil. Unha vez creada, active a opción <i>Engadir Sinatura</i> cando postee unha mensaxe. Tamén pode facer que tódalas súas mensaxes teñan a súa sinatura, activando a opción no seu perfil.");
$faq[] = array("¿Como creo unha enquisa?", "Crear unha enquisa é fácil, cando inicie un novo tema notará a opción <i>Crear unha enquisa</i>, introduza os datos da enquisa, como título e opcións, ten a posibilidade de poñer límite ó número de participantes (0 é infinito)");
$faq[] = array("¿Como edito ou borro unha enquisa?", "Se é quen iniciou a enquisa pode editala do mesmo xeito que a súa mensaxe, sen embargo isto só funcionará se a enquisa aínda non ten respostas, pois de telas só o administrador ou moderadores poderán editala o borrala");
$faq[] = array("¿Por que non podo acceder a algún foro?", "Algúns foros están limitados a certos grupos de usuarios, para velos, postear, editar, etc, necesita ter certas autorizacións, que só lle pode dar un moderador ou administrador do foro.");
$faq[] = array("¿Por que non podo votar nas enquisas?", "Só membros rexistrados poden votar nas enquisas (para previr resultados trucados), se se rexistrou pero non pode votar, é posible que non teña autorización para votar nesa enquisa :( .");


$faq[] = array("--","Formateo de mensaxes e tipos de temas");
$faq[] = array("¿Que é o código BBCode?", "BBCode é unha implementación especial do HTM, a forma na que o BBCode se usa é determinada polo administrador, é moi similar ó HTML, as etiquetas van entre corchetes [ e ] para máis información pode ve-lo manual de BBCode, o enlace aparece cada vez que vai publicar unha mensaxe.");
$faq[] = array("¿Podo usar HTML?", "Depende de que o administrador teña habilitada a opción e de qué etiquetas HTML estean activadas, xa que moitas etiquetas HTML poderían danar severamente a estructura da mensaxe.");
$faq[] = array("¿Que son os smileis?", "Smileis ou emotíconos son pequenos gráficos que poden usarse para expresar emocións, aparecen introducindo un pequeno código, por exemplo:  :) significa feliz, :( significa triste. A lista completa de smileis desprégase cando envía unha mensaxe.");
$faq[] = array("¿Podo postear imaxes?", "As imaxes poden ser introducidas na mensaxe, inseríndoas no momento de redactala. Non pode haber imáxes de sitios de correo, busca ou cualquera que precise autentificación (Yahoo, Hotmail...).");
$faq[] = array("¿Que son os anuncios?", "Os anuncios conteñen información importante para os usuarios.");
$faq[] = array("¿Que son os Temas Importantes?", "Os Temas Importantes aparecen debaixo dos anuncios e só na primeira páxina, é información moi importante que debería ler :)");
$faq[] = array("¿Que son os temas pechados ou bloquedados?", "Os temas pechados ou bloqueados son precisamente iso, temas nos que xa non se poden postear, isto decídeo o administrador ou moderadores.");


$faq[] = array("--","Niveis de usuario e grupos");
$faq[] = array("¿Que son os administradores?", "Os administradores son xente asignada con alto nivel de control sobre o foro enteiro, poden controlar permisos, moderadores e todo tipo de configuracións.");
$faq[] = array("¿Que son os moderadores?", "Moderadores son persoas que teñen o poder de editar ou borrar foros, pechalos ou abrilos. Son designados polo administrador e teñen menos opcións ca este.");
$faq[] = array("¿Que son os grupos de usuarios?", "Os Grupos de usuarios é unha das formas nas que o administrador do foro pode agrupar usuarios. Un usuario pode pertencer a varios grupos. Isto faise co fin de conceder permisos selectivos sobre o foro (como facer a todo un grupo moderadores).");
$faq[] = array("¿Como podo pertencer a un Grupo de usuarios?", "Faga click en Grupos de usuarios e pida a súa inscrición, recibirá un mail se é aceptado. Non tódolos grupos son abertos.");
$faq[] = array("¿Como me fago moderador dun grupo de usuarios?", "Só o administrador pode asignar ese permiso, contacte con el :)");


$faq[] = array("--","Mensaxería privada");
$faq[] = array("¡Non podo enviar mensaxes privadas!", "Hai tres posibles razóns: Non esta rexistrado ou non ingresou, o administrador deshabilitou o sistema de mensaxes privadas ou o administrador deshabilitou para vostede a mensaxería.");
$faq[] = array("¡Quero evitar mensaxes privadas non desexadas!", "Nun futuro será engadida a característica de ignorar mensaxes, por agora só envíe unha mensaxe ó administrador se recibe mensaxes non desexadas :(.");
$faq[] = array("¡Recibín spam ou correos maliciosos de alguén neste foro!", "Sentímolo moito, a característica de mandar mails ten amplos conceptos de seguridade e privacidade. Envie o mail ó administrador, tal coma viña, incluindo headers e demais, el tomará accións.");

//
// These entries should remain in all languages and for all modifications
//
$faq[] = array("--","Sobre phpBB2");
$faq[] = array("¿Quen programou este foro?", "Esta aplicación (na súa forma orixinal) é producida, liberada e con dereitos de autor pertencentes a <a href=\"http://www.phpbb.com/\" target=\"_blank\">phpBB Group</a>. Está feito baixo a GNU (Licencia Pública Xeral) e é de libre distribución (click no enlace para coñecer máis detalles)");
$faq[] = array("¿Por que este foro non ten X cousa?", "Este foro foi escrito e licenciado a través de phpBB Group. Se cre que debería ter algunha outra opción ou característica visite phpbb.com e mire o que o phpBB Group ten que dicir. Por favor, non publique mensaxes dese tipo nos foros de phpBB.com, os membros de Sourceforge estan cheos de ideas e en constante innovación para engadirlle melloras a este foro.");
$faq[] = array("¿Con quen podo contactar sobre abusos ou usos ilegais relacionados con este foro?", "Pode contactar co administrador do foro, se non atopa a forma de contactar con el intente contactando con cualquera dos moderadores. Se aínda non recibe resposta contacte ó dono do dominio (faga un whois lookup) ou, se está nun servicio gratuito (e.g. yahoo, free.fr, f2s.com, etc.), a dirección ou departamento de abusos dese servicio.Por favor, teña en conta que phpBB Group non ten ningún control e non pode de ningún modo ser culpado de como, onde ou por quen este foro se usa. Non ten ningún sentido contacta-lo phpBB Group en relación con calquera asunto legal non directamente relacionado co sitio phpbb.com ou o sofware concreto do phpBB. Se manda un email ó phpBB Group sobre calquera uso dunha terceira parte deste software recibirá unha resposta pouco amable ou non recibirá resposta ningunha.");

//
// Acabáronse as FAQ :)
//

?>