<?

/*******************************************
 ** Constantes para usar en los include/require
 ** (Directorios relativos al sistema)
 *******************************************/

define("ROOT_DIR", dirname(__FILE__));			// Directorio raiz
define("LIB_DIR", ROOT_DIR."/lib");				// Librerias del sistema
define("MOD_DIR", ROOT_DIR."/modulos");			// Modulos del sistema
define("UPLOADS_DIR", ROOT_DIR."/uploads");		// Directorio para uploads
/*******************************************
 ** Headers para que el explorador no guarde
 ** las p�ginas en cache.
 *******************************************/

header("Cache-control: no-cache");
header("Expires: ".gmdate("D, d M Y H:i:s")." GMT");

/*******************************************
 ** Colores del sistema.
 *******************************************/

$bgcolor_frames = "#B7CEEF";  //color de frames del sistema

$bgcolor1 = "#5090C0";		// Primer color de fondo
$bgcolor2 = "#D5D5D5";		// Segundo color de fondo
$bgcolor3 = "#B7CEC4";		// Tercer color de fondo
$bgcolor4 = "#FF0000";		// Color de fondo para tareas vencidas
$bgcolor  = "#d1c294";


$bgcolor_out  = "#CFE8DD"; // Color de fondo (onmouseout)
$bgcolor_over = "#82B39E"; // Color de fondo (onmouseover)
$text_color_out  = "#000000";
$text_color_over = "#000000";//"#006699";
$fondo = "fondo_alter.gif"; //imagen de fondo
$fondo = "fondo.gif"; //imagen de fondo
$fondo1 = "fondo1.jpg"; //imagen de fondo del encabezado




// atributo de los tr de los listados
$atrib_tr="bgcolor=$bgcolor_out onmouseover=\"this.style.backgroundColor = '$bgcolor_over'; this.style.color = '$text_color_over'\" onmouseout=\"this.style.backgroundColor = '$bgcolor_out'; this.style.color = '$text_color_out'\"";

/*******************************************
 ** Cantidad de items a mostrar por p�gina.
 *******************************************/

$itemspp = 50;

/*******************************************
 ** Configuraci�n de la base de datos.
 *******************************************/

$db_type = 'postgres8';				// Tipo de base de datos.
$db_host = 'localhost';		// Host para desarrollo.
$db_user = 'projekt';				// Usuario.
$db_password = 'propcp';			// Contrase�a.
$db_name = 'nacer_misiones';


// IPs permitidas para conectarse al gestion, si el ip no esta aca, se envia un mail
$ip_permitidas = array(
	"127.0.0.1/32" => "Localhost",
);

$ADODB_CACHE_DIR = LIB_DIR."/adodb/cache";		// Directorio para cache de consultas
// Arreglo que contiene los nombres de los esquemas en la
// base de datos para poder acceder a las tablas sin tener
// que usar en nombre del esquema.
$db_schemas = array(	
	"general",
	"mensajes",
	"permisos",
	"sistema",			
	"facturacion",	
	"calidad",	
    "nacer",
    "planillas",
	"personal",
	"contabilidad",
	"tareas_divisionsoft"

);
$db_debug = FALSE;					// Debugger de las consultas.

/*******************************************
 ** Limite de tiempo de inactividad para la
 ** expiraci�n de la sesi�n (en minutos).
 *******************************************/

 $session_timeout = 90;

/*******************************************
 ** Variable $html_root que contiene la ruta
 ** a la ra�z de la p�gina.
 ** (Ruta relativa al URL de la p�gina)
 *******************************************/
//if (ereg("(/modulos)|(/lib)|(/index.php)|(/menu.php)|(/menu_xml.php)|(/aviso.php)",$_SERVER["SCRIPT_NAME"],$tmp)) {
//	$tmp=explode($tmp[1].$tmp[2].$tmp[3].$tmp[5].$tmp[6],$_SERVER["SCRIPT_NAME"]);
//	$html_root = $tmp[0];
//}
//unset($tmp);

if (ereg("(/modulos)|(/lib)|(/index.php)|(/menu_para_ayuda.php)|(/menu_xml.php)|(/aviso.php)",$_SERVER["SCRIPT_NAME"],$tmp)) {
	$tmp=explode($tmp[1].$tmp[2].$tmp[3].$tmp[4].$tmp[5].$tmp[6],$_SERVER["SCRIPT_NAME"]);
	$html_root = $tmp[0];
}
unset($tmp);



/*******************************************
 ** Variable $html_footer contiene el
 ** pie de la p�gina.
 *******************************************/

$html_footer = "
  </body>
</html>
";


/*******************************************
 ** Libreria principal del sistema.
 *******************************************/


require LIB_DIR."/lib.php";

/*******************************************
 ** Variable $html_header contiene el
 ** encabezamiento de la p�gina.
 *******************************************/
 
if ($_ses_cambiar_perfil_usuario == 1) 
   $actualizar_menu_perfil_usuario='false';
else $actualizar_menu_perfil_usuario='true';

$html_header = "
<html>
  <head>
	<link rel='icon' href='".((($_SERVER['HTTPS'])?"https":"http")."://".$_SERVER['HTTP_HOST'])."$html_root/favicon.ico'>
	<link REL='SHORTCUT ICON' HREF='".((($_SERVER['HTTPS'])?"https":"http")."://".$_SERVER['HTTP_HOST'])."$html_root/favicon.ico'>
	<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>
    <script languaje='javascript' src='$html_root/lib/funciones.js'></script>
    <script language='javascript'>
		var winW=window.screen.Width;
		var valor=(winW*25)/100;
		var nombre1;
		var titulo1;
		function insertar() {
			ventana.document.all.titulo.innerText=titulo1;
			ventana.frames.frame1.location=nombre1;
		}
		function abrir_ventana(nombre,titulo) {
			var winH=window.screen.availHeight;
			nombre1=nombre;
			titulo1=titulo;
			if ((typeof(ventana) == 'undefined') || ventana.closed) {
				ventana=window.open('$html_root/modulos/ayuda/titulos.htm','ventana_ayuda','width=' + valor + ',height=' + (winH)+ ', left=' + (winW - valor ) +'  ,top=0, scrollbars=0 ');
				window.top.resizeBy(-valor,0);
			}
			else { ventana.focus(); }
			setTimeout('insertar()',400);
		}
  function check_fix_size() {
			if (typeof(fix_size) == 'function') fix_size();
			    if(parent && parent.oPath && $actualizar_menu_perfil_usuario) parent.oPath.updateLink();//actualiza el link del menu actual
			   
		}
	</script>
  </head>
 <body topmargin=0 background=$html_root/imagenes/$fondo bgcolor=\"$bgcolor3\"  onload='check_fix_size();document.focus();' onresize='check_fix_size();'>";

?>
