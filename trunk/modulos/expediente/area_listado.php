<?php
/*
Author: Gaby $
$Revision: 1.00 $
$Date: 2011/04/05 00:00:00 $
*/
require_once("../../config.php");

variables_form_busqueda("categoria_listado");

$orden = array(
        "default" => "1",
        "1" => "nom_area",
        "2" => "nom_responsable"
       );
$filtro = array(
		"nom_area" => "Nombre del Area",
		"nom_responsable" => "Nombre del Responsable"
       );
$sql_tmp="SELECT *
			FROM
			expediente.area";

echo $html_header;
?>
<form name=form1 action="area_listado.php" method=POST>
<table cellspacing=2 cellpadding=2 border=0 width=100% align=center>
     <tr>
      <td align=center>
		<?list($sql,$total_tipo_doc,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");?>
	    &nbsp;&nbsp;<input type=submit name="buscar" value='Buscar'>
	    &nbsp;&nbsp;<input type='button' name="nuevo_area" value='Nuevo' onclick="document.location='area_admin.php'">
	  </td>
     </tr>
</table>

<?$result = sql($sql,"No se ejecuto en la consulta principal") or die;?>

<table border=0 width=80% cellspacing=2 cellpadding=2 bgcolor='<?=$bgcolor3?>' align=center>
	  <tr>
		  	<td colspan=12 align=left id=ma>
			     <table width=100%>
				      <tr id=ma>
					       <td width=30% align=left><b>Total:</b> <?=$total_area?></td>       
					       <td width=40% align=right><?=$link_pagina?></td>
				      </tr>
			    </table>
		   </td>
	  </tr>
	  <tr> 
	    <td align=right id=mo><a id=mo href='<?=encode_link("area_listado.php",array("sort"=>"1","up"=>$up))?>' >Nombre del Area</a></td>      	
	    <td align=right id=mo><a id=mo href='<?=encode_link("area_listado.php",array("sort"=>"1","up"=>$up))?>' >Nombre del Responsable del Area</a></td>      	
	    </tr>
  <?
   while (!$result->EOF) {
   		$ref = encode_link("area_admin.php",array("id_area"=>$result->fields['id_area'],"pagina"=>"area_listado"));
    	$onclick_elegir="location.href='$ref'";
   	?>
    <tr <?=atrib_tr()?>>
     <td onclick="<?=$onclick_elegir?>"><?=$result->fields['nom_area']?></td>
     <td onclick="<?=$onclick_elegir?>"><?=$result->fields['nom_responsable']?></td>
     </tr>    
	<?$result->MoveNext();
    }?>
  	
</table>
</form>
</body>
</html>

<?echo fin_pagina();// aca termino ?>