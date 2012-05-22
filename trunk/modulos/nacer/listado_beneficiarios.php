<?php

require_once("../../config.php");

variables_form_busqueda("listado_beneficiarios");

$fecha_hoy=date("Y-m-d H:i:s");
$fecha_hoy=fecha($fecha_hoy);

if ($cmd == "")  $cmd="activos";

$orden = array(
        "default" => "1",
        "1" => "afiapellido",
        "2" => "afinombre",
        "3" => "afidni",
        "4" => "afitipocategoria",
        "5" => "nombreefector",
        "6" => "activo",
        "7" => "clavebeneficiario",
        "8" => "activo",
        "9" => "fechainscripcion",
        "10" => "fechacarga",
        "11" => "usuariocarga",
        "12" => "motivobaja",
        "13" => "mensajebaja"
       );
$filtro = array(
		"afidni" => "DNI",
        "afiapellido" => "Apellido",
        "afinombre" => "Nombre",
        "descripcion" => "Tipo Afiliado",
        "nombreefector"=>"Nombre Efector",
        "activo"=>"Activo",
        "clavebeneficiario"=>"Clave Beneficiario", 
        "fechainscripcion"=>"Fecha de Inscripcion",
        "fechacarga"=>"Fecha de Carga",
        "usuariocarga"=>"Usuario Carga",     
        "motivobaja"=>"Cod. Baja",     
        "mensajebaja"=>"Mensaje Baja"     
       );
$datos_barra = array(
     array(
        "descripcion"=> "Activos",
        "cmd"        => "activos"
     ),
     array(
        "descripcion"=> "Inactivos",
        "cmd"        => "inactivos"
     ),
     array(
        "descripcion"=> "Todos",
        "cmd"        => "todos"
     )
);

generar_barra_nav($datos_barra);

$sql_tmp="select * from nacer.smiafiliados
	 left join nacer.smitiposcategorias on (afitipocategoria=codcategoria)
	 left join facturacion.smiefectores on (cuieefectorasignado=cuie)";


if ($cmd=="activos")
    $where_tmp=" (smiafiliados.activo='S')";
    

if ($cmd=="inactivos")
    $where_tmp=" (smiafiliados.activo='N')";

echo $html_header;
?>
<form name=form1 action="listado_beneficiarios.php" method=POST>
<table cellspacing=2 cellpadding=2 border=0 width=100% align=center>
     <tr>
      <td align=center>
		<?list($sql,$total_muletos,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");?>
	    &nbsp;&nbsp;<input type=submit name="buscar" value='Buscar'>
	     &nbsp;&nbsp;
	    <? $link=encode_link("listado_beneficiarios_excel.php",array("cmd"=>$cmd));?>
        <img src="../../imagenes/excel.gif" style='cursor:pointer;'  onclick="window.open('<?=$link?>')">
	  </td>
     </tr>
</table>

<?$result = sql($sql) or die;?>

<table border=0 width=100% cellspacing=2 cellpadding=2 bgcolor='<?=$bgcolor3?>' align=center>
  <tr>
  	<td colspan=9 align=left id=ma>
     <table width=100%>
      <tr id=ma>
       <td width=30% align=left><b>Total:</b> <?=$total_muletos?></td>       
       <td width=40% align=right><?=$link_pagina?></td>
      </tr>
    </table>
   </td>
  </tr>
  

  <tr>
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_beneficiarios.php",array("sort"=>"1","up"=>$up))?>' >Apellido</a></td>      	
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_beneficiarios.php",array("sort"=>"2","up"=>$up))?>'>Nombre</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_beneficiarios.php",array("sort"=>"3","up"=>$up))?>'>DNI</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_beneficiarios.php",array("sort"=>"4","up"=>$up))?>'>Tipo Beneficiario</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_beneficiarios.php",array("sort"=>"5","up"=>$up))?>'>Nombre Efector</a></td>
    <?if (($cmd=="todos")||($cmd=="inactivos")){?>
    	<td align=right id=mo><a id=mo href='<?=encode_link("listado_beneficiarios.php",array("sort"=>"8","up"=>$up))?>'>Activo</a></td>
    	<td align=right id=mo><a id=mo href='<?=encode_link("listado_beneficiarios.php",array("sort"=>"12","up"=>$up))?>'>Cod Baja</td>
    	<td align=right id=mo><a id=mo href='<?=encode_link("listado_beneficiarios.php",array("sort"=>"13","up"=>$up))?>'>Mensaje Baja</td>    
    <?}?>  
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_beneficiarios.php",array("sort"=>"7","up"=>$up))?>'>Clave Beneficiario</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_beneficiarios.php",array("sort"=>"9","up"=>$up))?>'>F Ins</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_beneficiarios.php",array("sort"=>"10","up"=>$up))?>'>F Carga</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_beneficiarios.php",array("sort"=>"11","up"=>$up))?>'>Usu Carga</a></td>
    <?if ($cmd=="todos"){?>
    	<td align=right id=mo>Certif.</td>
    <?}?>  
  </tr>
 <?
   while (!$result->EOF) {?>
  
    <tr <?=atrib_tr()?>>     
     <td><?=$result->fields['afiapellido']?></td>
     <td><?=$result->fields['afinombre']?></td>
     <td><?=$result->fields['afidni']?></td>     
     <td><?=$result->fields['descripcion']?></td>     
     <td><?=$result->fields['nombreefector']?></td> 
     <?if (($cmd=="todos")||($cmd=="inactivos")){?>    
     	<td><?=$result->fields['activo']?></td> 
     	<td><?=$result->fields['motivobaja']?></td> 
     	<td><?=$result->fields['mensajebaja']?></td> 
     <?}?>     
      <td><?=$result->fields['clavebeneficiario']?></td>  
      <td><?=fecha($result->fields['fechainscripcion'])?></td>  
      <td><?=fecha($result->fields['fechacarga'])?></td>  
      <td><?=$result->fields['usuariocarga']?></td>  
      <?if ($cmd=="todos"){?>
    	 <td align="center">
       	 <?$link=encode_link("certificado_pdf.php", array("id_smiafiliados"=>$result->fields['id_smiafiliados']));	
		   echo "<a target='_blank' href='".$link."' title='Imprime Factura'><IMG src='$html_root/imagenes/pdf_logo.gif' height='20' width='20' border='0'></a>";?>
       </td>
     <?}?>
    </tr>
	<?$result->MoveNext();
    }?>
    
</table>
</form>
</body>
</html>
<?echo fin_pagina();// aca termino ?>