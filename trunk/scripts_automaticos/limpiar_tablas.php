<?

include("funciones_generales.php");

$sql = "DELETE FROM permisos.phpss_session";
$result = $db->Execute($sql) or die("Error borrando las sesiones\n");

$sql = "DELETE FROM permisos.phpss_svars";
$result = $db->Execute($sql) or die("Error borrando las variables de sesion\n");
?>