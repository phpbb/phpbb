<?php
/***************************************************************************
 *                          lang_faq.php [asturian]
 *                            -------------------
 *   begin                : Wednesday Oct 3, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *   
 *   tradución al asturianu : Mikel González (mikelglez@iespana.es)
 *
 *   $Id: lang_faq.php,v 0.9 2002/03/05 16:42:08 Pato[100%Q]
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   Esta ye una aplicación de software llibre, pue re-distribuila y/o modificala
 *   enbaxu los terminos de la GNU (Llicencia Pública Xeneral), la cual foy publicá
 *   per la Free Software Foundation (Fundación del Software Llibre). Esto en la llicencia
 *   de la versión 2 o posterior.
 *
 ***************************************************************************/
 
// 
// Pa enxertar una entrada a les Entrugues Frecuentes namás meta una llinea col siguiente formatu:
// $faq[] = array("entruga", "rempuesta");
// Si quier separar una secion plumie $faq[] = array("--","Un testu de separtación pue dir equí si quier");
// Los enllaces creanse automaticamente :)
//
// NUN OLVIDE poner ; al final de cada llinia.
// NUN PONGA dobles comilles (") enes entraes de sus Entrugues Frecuentes, si ye absolutamente necesariu entos plumielo asi: \"testu\"
//
// Los temas ya entraes de les Entrugues Frecuentes apaecerán nel mesmu orden nel que tean n'esti archivu.
//

  
$faq[] = array("--","Acerca del ingresu (login) y rexistru");
$faq[] = array("¿Por qué nun pueo conectame?", "¿Ya se rexistró? Tien que rexistrase nel sistema enantes de poer acceder a él. ¿Foy bloqueau nel foru? (si ye asi un aparecerá-y un mensaxe). Si esto pasa unvie un mensaxe al alministraor del foru pa alcontrar la causa. Si rexistrose y nun foy bloqueau compruebe que'l so nome d'usuariu y contraseña coinciden, ye'l problema mas común. Si ta seguru de que tean correutos los datos, unvie un mensaxe al alministraor, ye posible que'l foru te mal configurau y/o tea fallos ena programación.");
$faq[] = array("¿Por qué teo que registrame pa too?", "Nun ta obligau a facelu, la decisión tomenla los alministraores y moderaores. Ensin embargu tar rexistrau y-da munches ventaxes que como usuariu invitau nun esfrutaría, como tener el su gráficu personalizau (avatar), mensaxes privaos, suscrición a grupos d'usuarios, etc.... Namás y-tomará unos segundinos, Ye mui recomendable.");
$faq[] = array("¿Por qué la mio sesión d'usuariu expira automaticamente?", "Si nun activa la casilla <i>Ingresar automáticamente</i> cuandu se coneuta'l foru, los sus datos guardanse nuna cookie que eliminase al salir de la páxina o en ciertu tiempu. Esto previen que la su cuenta puea ser usada por dalguien más. Pa que'l sistema y-reconoza automáticamente namás tien que activar la casilla al conectase. NUN ye recomendable si coneutase al foru dende una computaora compartia (ciber-chigre, biblioteca, facultá ...)");
$faq[] = array("¿Como evito apaecer enes llistes d'usuarios coneutaos?", "Nel so perfil, atopará la opción <i>Ocultar el mio estau de conexión</i>, si activa esta opción apaecerá namás pa los alministraores, moderaores y pa usté mesmu, pa los demas será un usuariu ocultu.");
$faq[] = array("¡Perdiera la mio contraseña!", "Calma, si la su contraseña no pue recuperase pue desautivala o cambiala. Pa facer esto dirixase a la páxina de rexistru y calque en <u>Olvidé la mio contraseña</u>, siga les instruciones y tará dientru en mui pocu tiempu");
$faq[] = array("¡Rexistreme y nun pueo coneutame!", "Primeru verifique los sus datos (usuariu y contraseña). Si too ye correutu hay dos posibles razones. Si'l Sistema de Proteción Infantil (COPPA) ta activau y cuandu rexistrose elixió la opción <u>Soi menor de 13 años</u> entos tendrá que seguir dalgunes instruciones que se-y darán pa activar la cuenta. N'otros casos l'alministraor pide que les cuentas  activense mediante un correu lletrónico, asi que revise'l so correu y confirme la suscrición. Dalgunos foros necesiten confirmación de rexistru. Si nun sucede na d'esto contaute col alministraor del foru.");
$faq[] = array("Fay tiempu ya que me rexistrara, pero agora nun pueo coneutame", "Les posibles razones son: enxertara un nome d'usuariu o contraseña incorreutos (verifique'l mensaxe que se-y unvia al rexistrase). Yes posible que'l alministraor haya esborriau la su cuenta, esto ye mui frecuente, pues si nun escribiera nengún mensaxe en ciertu tiempu l'alministraor pue esborriar l'usuariu pa que la base datos nun se-y sature de rexistros. Si ye asi rexistrese de nueu y participe :)");


$faq[] = array("--","Preferencies d'usuariu y configuraciones");
$faq[] = array("¿Cómu pueo camudar la mio configuración?", "Tolos sus datos y configuraciones (si ta rexistrau) tán archivaes ena nuesa base datos. Pa camudalos calque'l enllaz <u>Perfil</u>, xeneralmente alcuentrase no cimero de cada páxina.");
$faq[] = array("El tiempu enos foros no ye correuto (hores)!", "Les horas son correutas, pue que te güeyando les hores correspondientes a otra zuna horaria, si ye'l casu, entre al so perfil y elixa la su zona horaria d'alcuerdu a la so ubicación (ex. Llondres, Paris, Nuea York, Sydney, etc.) En camudando esto les horas deben apaecer d'alcuerdu a la su zona y tiempu. Si nun ta rexistrau ye tiempu de facelu :)");
$faq[] = array("Camudra la zuna horarian nel mio perfil, pero el tiempu sigue siendu incorreuto", "Si ta seguru de que la zuna horaria ye correuta ye posible que seya polos horarios de branu implementaos por dalgunos paises.");
$faq[] = array("El mio idioma nun tá ena llista!", "Esto pue ser porque l'alministraor nun instalara'l paquete del so idioma pa'l foru o naide ficiera una tradución :(  si ye asi, sientase llibre de facer una tradución (miles de personas agradecerán-ylo), la información encuentrase nel phpBB Group website (Calque'l enllaz que s'alcuentra nel pie de la páxina)");
$faq[] = array("Cómu pueo poner una imaxen enbaxu del mio nome d'usuariu?", "Hay dos tipos d'imáxenes enbaxu'l so nome d'usuariu, la primera ye'l RANK, que tá asociada col númberu de mensaxes que plumiara nel foru (xeneralmente son estrelles o bloques), la segunda ye l'AVATAR, que ye un gráficu xeneralmente únicu y personal, l'alministraor decide si pueden usase o non, si ta permitiu usalos pue introducilo nel su perfil. En casu de que nun exista esa opción, contaute col alministraor y pida-y qu'autive esa opción :)");
$faq[] = array("¿Comu pueo camudar el mio RANK?", "No pue camudar el so RANK direutamente, ya qu'esti ta asociau col númberu de mensaxes plumiaos o el so estau de moderaor, alministraor o RANKs especiales. Por favor, nun abuse de plumiar innecesariamente pa aumentar el so RANK.");
$faq[] = array("Cuandu calco sobre'l enllaz de correu pideme rexistru", "Pa poer unviar correu a un usuariu via formulariu (si l'alministraor tienlo activau) necesita tar rexistrau, esto ye pa evitar SPAM o mensaxes maliciosos d'usuarios anonimos.");


$faq[] = array("--","Publicación de mensaxes");
$faq[] = array("¿Comu pueo publicar un mensaxe nel foru?", "Facil, rexistrese comu miembru del foru (calcando nel enllaz de rexistru, xeneralmente no cimero de cada páxina), depues del rexistru calque'n <i>Unviar nueu mensaxe<i>, se-y presentará un panel col que facilmente espublizará un mensaxe :)");
$faq[] = array("¿Cómu pueo editar o esborriar un mensaxe?", "Si nun ye l'alministraor o moderaor del foru, namás pue'sborriar los mensaxes qu'hubiere unviau usté mesmu. Pue editar un mensaxe calcando n'<i>editar</i> si d'aquien ya respondiera al so mensaxe, alcontrá un piqueñu testu nel mesmu diciendo que foy modificau y les vegaes que lo ficiere, nun apaez si foy un moderaor o l'administraor el que lu editara (la mayoria les vegaes dexen un mensaxe aclaratoriu).");
$faq[] = array("¿Comu pueo axuntar una firma al mio mensaxe?", "Pa enxertar una firma nel so mensaxe primeru ha facer una, esto faise modificando'l so perfil. Una vez creada active la opción <i>Axuntar firma</i> cuando plumie un mensaxe. Tamién pue facer que tolos sus mensaxes tean la so firma, autivando la opción nel su perfil.");
$faq[] = array("¿Cómu faigo una encuesta?", "Facer una encuesta ye cenciellu, encuantes entame un nueu tema notará la opción <i>Facer una encuesta</i>, introduz los datos de la encuesta, como titulu y opciones, tien la posibilidá de poner llimite al numberu de participantes (0 ye infinitu)");
$faq[] = array("¿Cómu edito o esborrio una encuesta?", "Si ye usté'l que'ntamó la encuesta pues editala de la mesma mena que'l su mensaxe, pero esto namás funcionará si la encuesta entá nun tien rempuestes, si les toviere namás l'alministraor o moderaores podrán editala o esborriala");
$faq[] = array("¿Por qué nun pueo aceder a dalgún foru?", "Dalgunos foros tán llimitaos a ciertos grupos d'usuarios, pa velos, plumiar, editar, etc, necesita tener ciertes autorizaciones, que namás pue da-ylas un moderaor o alministraor del foru.");
$faq[] = array("¿Por qué nun pueo votar nes encuestes?", "Namás miembros rexistraos puen votar enes encuestes (pa prevenir resultaos trucaos), si tubiere rexistrau pero nun pue votar, ye posible que nun tea autorización pa votar nesa encuesta :(.");



$faq[] = array("--","Dando-y forma a los mensaxes y tipos de temes");
$faq[] = array("¿Qué ye'l códigu BBCode?", "BBCode ye una implementación especial del HTML, la mena ena que'l BBCode s'emplega ye determiná po'l alministraor, ye mui paeciu al HTML, les etiquetes van entre corchetes [ y ] pa mas información pues char un qüeyu el manual de BBCode, l'enllaz apaez ca vegá que publiques un mensaxe.");
$faq[] = array("¿Pueo usar HTML?", "Depende de que l'alministraor tea autivada la oción y de cuales etiquetes HTML tén autivaes, ya que munches etiquetes HTML podrien dañar severamente la estrutura del mensaxe.");
$faq[] = array("¿Qué son los smileys?", "Smileys, emotíconos o icionos xestuales son pequeños gráficos que puen ser usaos pa'spresar emociones, apaecen enxertando un pequenu códigu, por exemplu:  :) significa feliz, :( significa atristayau. La llista completa de'smileys despliegase cuandu unvies un mensaxe.");
$faq[] = array("¿Pueo unviar imaxenes?", "Les imaxenes puen ser xuntaes al mensaxe, enxertandolas al momentu de redatalu. Nun pue haber imaxenes de sitios de correu, busquea o cualisquier autentificacion (Yahoo, Hotmail...).");
$faq[] = array("¿Qué son los anuncios?", "Los anuncios caltienen información importante pa los usuarios.");
$faq[] = array("¿Qué son los Temes Importantes?", "Los Temes Importantes apaecen embaxu de los anuncios y namás ena primera páxina, ye información perimportante que debería lleer :)");
$faq[] = array("¿Qué son los temes trancaos o bloqueaos?", "Los temes trancaos o bloqueaos son eso mesmo, temes enos que ya nun se puen plumiar mensaxes, esto decidilo l'alministraor o moderaores.");


$faq[] = array("--","Niveles d'usuariu y grupos");
$faq[] = array("¿Qué son los alministraores?", "Los alministraores son xente con altu nivel de control sobre'l foru enteru, puen controlar permisos, moderaores y tou tipu de configuraciones.");
$faq[] = array("¿Qué son los moderaores?", "Moderaores son persones que tienen el poer d'editar o esborriar foros, trancalos o abrirlos. Son designaos po'l alministraor  tienen menos opciones qu'esti.");
$faq[] = array("¿Qué son los grupos d'usuarios?", "los Grupos d'usuarios ye una de les formes enas que'l alministraor del foru pue agrupar usuarios. Un usuario pue pertenecer a varios grupos. Esto faise cola fin de conceder permisos coleutivos sobre'l foru (como volver a tou un grupu moderaores).");
$faq[] = array("¿comu puedo pertenecer a un Grupu d'usuarios?", "Calque en Grupos d'usuarios y pida-y la so inscripcion, recibirá un correu si ye aceptau. Nun tolos grupos son abiertos.");
$faq[] = array("¿Cómu conviertome nel moderador d'un grupu d'usuarios?", "Solo l'alministraor pue asignar esi permisu, contaute con el :)");


$faq[] = array("--","Mensaxería privada");
$faq[] = array("Nun pueo unviar mensaxes privaos!", "Hay tres posibles razones: Nun ta rexistrau o nun ta coneutau, l'alministraor trancó el sistema de mensaxes privaos o l'alministraor nun-y parmiti a usté l'usu de la mensaxería.");
$faq[] = array("Quiero evitar mensaxes privaos nun deseaos!", "N'un futuru agregarase la carauterística d'ignorar mensaxes, por agora namás unvie un mensaxe al alministraor si recibe mensaxes nun deseaos :(.");
$faq[] = array("Recibiera spam o correos maliciosos d'alguien n'esti foru!", "Sentimoslo muncho, la carauteristica de mandar correos tien amplios conceutos de seguridá y privacidá. Unvie el correu al alministraor, tal comu vinia, incluyendu headers y demas, el tomará aciones.");

//
// These entries should remain in all languages and for all modifications
//
$faq[] = array("--","Acerca de phpBB2");
$faq[] = array("¿Quien programó esti foru??", "Esta aplicación (ena so forma orixinal) ye producia, lliberada y con drechos d'autor pertenecientes al <a href=\"http://www.phpbb.com/\" target=\"_blank\">phpBB Group</a>. Tá fechu embaxu la GNU (Llicencia Pública Xeneral) y ye de llibre distribución (calque nel enllace pa conocer mas detalles)");
$faq[] = array("¿Por qué esti foru nun tien X cosa?", "Esti foru foy escritu y llicenciau a traviés de phpBB Group. Si cree que deberia tener dalguna otra opción o carauterística visite phpbb.com y mire lo que'l phpBB Group tien que decir. Por favor, nun publique mensaxes d'ese tipu enos foros de phpBB.com, los miembros de Sourceforge tan enllenos d'idegues y en constante innovación pa agrega-y meyores a esti foru.");
$faq[] = array("¿A quien pueo contautar acerca d'abusos o usos illegales rellacionaos con esti foru?", "Pue contautar al alministraor del foru, si nun alcuentra mena de facelo intente contautando a cualisquier de los moderaores. Si nun recibe rempuesta deberia ponese en contautu col propietariu del dominiu (faiga una busca whois) o, si ta corriendo en serviores gratuitos (ex. yahoo, free.fr, f2s.com, etc.), contaute col departamentu d'abusos d'esi serviciu. Entienda que phpBB Group nun tien control algunu sobre'l foru y nun pue facese responsable sobre esti foru y los sus contenios. It is absolutely pointless contacting phpBB Group in relation to any legal (cease and desist, liable, defamatory comment, etc.) matter not directly related to the phpbb.com website or the discrete software of phpBB itself. If you do email phpBB Group about any third party use of this software then you should expect a terse response or no response at all.");

//
// Equí finen les FAQ :)
//

?>