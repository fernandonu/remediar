<?

if (ereg("/login.php",$_SERVER["SCRIPT_NAME"])) {
	$tmp=explode("/login.php",$_SERVER["SCRIPT_NAME"]);
	$html_root = $tmp[0];
}

?>
<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<title>Area de Sistemas-Ministerio de Salud Publica Misiones</title>
<head>
<link rel="icon" href="<? echo ((($_SERVER['HTTPS'])?"https":"http")."://".$_SERVER['HTTP_HOST']).$html_root; ?>/favicon.ico">
<link REL='SHORTCUT ICON' HREF='<? echo ((($_SERVER['HTTPS'])?"https":"http")."://".$_SERVER['HTTP_HOST']).$html_root; ?>/favicon.ico'>

<link type='text/css' href='<? echo $html_root; ?>/lib/estilos.css' REL='stylesheet'>
</head>
</head>

<body style="overflow:hidden;" onLoad="javascript: document.frm.username.focus();" topmargin="0" leftmargin="0" rightmargin="0" bottommargin="0" marginwidth="0" marginheight="0" background="<?="$html_root/imagenes/fondo.jpg"?>">
<form action='index.php' method='post' name='frm'>
<input type="hidden" name="resolucion_ancho" value="">
<input type="hidden" name="resolucion_largo" value="">
<div align="center">
<br>
<table cellpadding="0" cellspacing="0" border="0" align="center" width="780">
	<tr>
		<td bgcolor="#BDC2C7"></td>
		<td bgcolor="#AEB3B7">
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr> 
					<div align="center" >
						<td bgcolor="#ffffff"><img src="<?="$html_root/imagenes/sp.gif"?>" width="1" height="1"></td>
						<td width="100%"><img src="<?="$html_root/imagenes/sp.gif"?>" width="1" height="8"><br></td>
						<td bgcolor="#ffffff"><img src="<?="$html_root/imagenes/sp.gif"?>" width="1" height="1"></td>
					</div>
				</tr>
			</table>		
		</td>
		<td bgcolor="#BDC2C7"></td>
	</tr>
	<tr>
	  <td width="245"><p align="center"></td>
	  <td width="299"><p align="center"><img src="<?="$html_root/imagenes/logo_remediar.jpg"?>" width="120" height="60"/>&nbsp;&nbsp;<img src="<?="$html_root/imagenes/logo_nacer.jpg"?>" width="120" height="60"/></td>
	  <td width="249"><p align="center"><img src="<?="$html_root/imagenes/logo_saludchica.jpg"?>"width="180" height="50"/></td>
  	</tr>
  	
  	<tr>
  	<td colspan="3" >
    	<img src="<?="$html_root/imagenes/familia.jpg"?>" width="781" height="179" />
    </td>
  	</tr>
	<tr>
		<td bgcolor="#BDC2C7"></td>
		<td bgcolor="#AEB3B7">
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr> <div align="center" >
					<td bgcolor="#ffffff"><img src="<?="$html_root/imagenes/sp.gif"?>" width="1" height="1"></td>
					<td width="100%"><img src="<?="$html_root/imagenes/sp.gif"?>" width="1" height="8"><br></td>
					<td bgcolor="#ffffff"><img src="<?="$html_root/imagenes/sp.gif"?>" width="1" height="1"></td>
				</div></tr>
			</table>
		</td>
		<td bgcolor="#BDC2C7"></td>
	</tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" align="center" width="780">
	<tr>
		<td width="1" background="<?="$html_root/imagenes/dot.gif"?>">
    		<img src="<?="$html_root/imagenes/sp.gif"?>" width="1" height="200"/>
    	</td>
    	<td align="justify"><div style="padding:10px;text-align:justify;">
      	<div style="color:#006A9E;">
			
		</div>
		</td>
		<td width="1" background="<?="$html_root/imagenes/dot.gif"?>">
			<img src="<?="$html_root/imagenes/dot.gif"?>" width="1" height="1"/>
		</td>
		<td width="279" valign="top">
			<div style="padding:10px;text-align:justify;" id="formlogin">
				<p style="text-align: center"><b><font face="Arial" size="4" color="#006A9E">SISTEMA DE GESTION</font></b>
				<p style="text-align: center"><b><font face="Arial" size="2" color="#800000">PRODUCCION</font></b>
				<form method="POST" action="--WEBBOT-SELF--">
				<p style="text-align: right">
					<font face="Tahoma"size="2"><b>Usuario: </b></font>
					<INPUT name=username AUTOCOMPLETE="off" style="border-style: solid; border-width: 1px" size="23" tabindex="1">
				</p>
				<p style="text-align: right">
					<font face="Tahoma" size="2"><b>Contraseña: </b></font>
					<INPUT type=password name=password AUTOCOMPLETE="off" style="border-style: solid; border-width: 1px" size="23" tabindex="2">
				</p>
				<p style="text-align: center">
					<INPUT type=submit value="  Ingresar &gt;" name=loginform style="font-family: Tahoma; font-size: 10pt" tabindex="3">
				</p>
			</form>
			</div>
		</td>
		<td width="1" background="<?="$html_root/imagenes/dot.gif"?>">
			<img src="<?="$html_root/imagenes/dot.gif"?>" width="1" height="1"/>
		</td>
 	</tr>
</table>

<table cellpadding="0" cellspacing="0" border="0" align="center" width="785" height="25">
	<tr>
  		<td width="785" bgcolor="#006A9E">
  		<div > 
			<p align="center">
				<b><font color="#FFFFFF" face="Tahoma" size="2">2010 © Copyright - PLAN NACER. ACTUALIZACIONES Y MANTENIMIENTO: AREA DE SISTEMAS-MINISTERIO DE SALUD PUBLICA MISIONES</font></b>
			</p>
		</div>
		</td>
 	</tr>
</table>

</div>
<script>
//guardamos la resolucion de la pantalla del usuario en los hiddens para despues recuperarlas
//y guardarlas en las variable de sesion $_ses_user
document.all.resolucion_ancho.value=screen.width;
document.all.resolucion_largo.value=screen.height;

<!-- 
//Cargar la página según el explorador 
if (navigator.appName.indexOf("Explorer") != -1) { 
	document.getElementById("formlogin").style.visibility ='visible';
}else{
	document.getElementById("formlogin").style.visibility='hidden';
 } 
//--> 
</script>
</body>
</form>
</html>
