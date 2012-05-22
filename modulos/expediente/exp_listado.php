<?php
/*
Author: Gaby $
$Revision: 1.00 $
$Date: 2011/04/07 12:03:00 $
*/
require_once("../../config.php");

variables_form_busqueda("categoria_listado");

$orden = array(
        "default" => "1",
        "1" => "numero_caratula",
        "2" => "fecha_creacion",
        "3" => "nom_usuario"      
       );
$filtro = array(
		"numero_caratula" => "Numero",
		"fecha_creacion" => "Fechade Ingreso",
		"nom_usuario" => "Usuario ingreso"
       );
$sql_tmp="SELECT 
			  expediente.permiso_areas.id_usuarios,
			  sistema.usuarios.nombre,
			  sistema.usuarios.apellido,
			  expediente.caratula.id_caratula,
			  expediente.caratula.numero_caratula,
			  expediente.caratula.fecha_creacion,
			  expediente.caratula.observaciones_carat,
			  expediente.caratula.id_permiso_user,
			  expediente.caratula.id_permiso_area,
			  expediente.caratula.id_tipo_ingreso
			FROM
			  expediente.permiso_areas
			  INNER JOIN sistema.usuarios ON (expediente.permiso_areas.id_usuarios = sistema.usuarios.id_usuario)
			  INNER JOIN expediente.caratula ON (expediente.permiso_areas.id_permiso_area = expediente.caratula.id_permiso_user)";

echo $html_header;
?>
<form name=form1 action="exp_listado.php" method=POST>
<table cellspacing=2 cellpadding=2 border=0 width=100% align=center>
     <tr>
      <td align=center>
		<?list($sql,$total_exp,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");?>
	    &nbsp;&nbsp;<input type=submit name="buscar" value='Buscar'>
	    &nbsp;&nbsp;<input type='button' name="nuevo_exp" value='Nuevo' onclick="document.location='exp_admin.php'">
	  </td>
     </tr>
</table>

<?$result = sql($sql,"No se ejecuto en la consulta principal") or die;?>

<table border=0 width=80% cellspacing=2 cellpadding=2 bgcolor='<?=$bgcolor3?>' align=center>
	  <tr>
		  	<td colspan=12 align=left id=ma>
			     <table width=100%>
				      <tr id=ma>
					       <td width=30% align=left><b>Total:</b> <?=$total_exp?></td>       
					       <td width=40% align=right><?=$link_pagina?></td>
				      </tr>
			    </table>
		   </td>
	  </tr>
	  <tr> 
	    <td align=right id=mo><a id=mo href='<?=encode_link("exp_listado.php",array("sort"=>"1","up"=>$up))?>' >Numero</a></td>      	
	    <td align=right id=mo><a id=mo href='<?=encode_link("exp_listado.php",array("sort"=>"1","up"=>$up))?>' >Fecha de Ingreso</a></td>      	
	    <td align=right id=mo><a id=mo href='<?=encode_link("exp_listado.php",array("sort"=>"1","up"=>$up))?>' >Usuario Ingreso</a></td>      	
	   </tr>
  <?
   while (!$result->EOF) {
   		$ref = encode_link("exp_admin.php",array("id_caratula"=>$result->fields['id_caratula'],"pagina"=>"exp_listado"));
    	$onclick_elegir="location.href='$ref'";
   	?>
    <tr <?=atrib_tr()?>>
     <td onclick="<?=$onclick_elegir?>"><?=$result->fields['numero_caratula']?></td>
     <td onclick="<?=$onclick_elegir?>"><?=$result->fields['Fecha_creacion']?></td>
     <td onclick="<?=$onclick_elegir?>"><?=$result->fields['nombre'].', '.$result->fields['apellido']?></td>
     </tr>    
	<?$result->MoveNext();
    }?>
  	
</table>
</form>
</body>
</html>

<?echo fin_pagina();// aca termino ?>