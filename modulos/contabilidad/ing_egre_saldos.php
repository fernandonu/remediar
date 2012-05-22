<?php

require_once("../../config.php");

variables_form_busqueda("ing_egre_saldos");

$fecha_hoy=date("Y-m-d H:i:s");
$fecha_hoy=fecha($fecha_hoy);

if ($cmd == "")  $cmd="VERDADERO";

$orden = array(
        "default" => "1",
        "1" => "cuie",
        "2" => "nombre",
        "3" => "domicilio",
        "4" => "cuidad",         
       );
$filtro = array(
		"cuie" => "CUIE",
        "nombre" => "Nombre",                
       );
$datos_barra = array(
     array(
        "descripcion"=> "Convenio",
        "cmd"        => "VERDADERO"
     ),
     array(
        "descripcion"=> "Sin Convenio",
        "cmd"        => "FALSO"
     ),
     array(
        "descripcion"=> "Todos",
        "cmd"        => "TODOS"
     )
);

generar_barra_nav($datos_barra);

$sql_tmp="SELECT 
  nacer.efe_conv.id_efe_conv,
  facturacion.smiefectores.nombreefector,
  facturacion.smiefectores.domicilio,
  facturacion.smiefectores.departamento,
  facturacion.smiefectores.localidad,
  facturacion.smiefectores.cod_pos,
  facturacion.smiefectores.ciudad,
  facturacion.smiefectores.referente,
  facturacion.smiefectores.tel,
  nacer.efe_conv.mail,
  nacer.efe_conv.com_gestion,
  nacer.efe_conv.com_gestion_firmante,
  nacer.efe_conv.fecha_comp_ges,
  nacer.efe_conv.fecha_fin_comp_ges,
  nacer.efe_conv.com_gestion_pago_indirecto,
  nacer.efe_conv.tercero_admin,
  nacer.efe_conv.tercero_admin_firmante,
  nacer.efe_conv.fecha_tercero_admin,
  nacer.efe_conv.fecha_fin_tercero_admin,
  nacer.efe_conv.cuie
FROM
  nacer.efe_conv inner join facturacion.smiefectores on (nacer.efe_conv.cuie=facturacion.smiefectores.cuie)";


if ($cmd=="VERDADERO")
    $where_tmp=" (efe_conv.com_gestion='VERDADERO')";
    

if ($cmd=="FALSO")
    $where_tmp=" (efe_conv.com_gestion='FALSO')";

echo $html_header;
?>
<form name=form1 action="ing_egre_saldos.php" method=POST>
<table cellspacing=2 cellpadding=2 border=0 width=100% align=center>
     <tr>
      <td align=center>
		<?list($sql,$total_muletos,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");?>
	    &nbsp;&nbsp;<input type=submit name="buscar" value='Buscar'>	    
	    <? $link=encode_link("ing_egre_saldos_excel.php",array("cmd"=>$cmd));?>
        <img src="../../imagenes/excel.gif" style='cursor:pointer;'  onclick="window.open('<?=$link?>')" title="Listado Detallado">
        <? $link=encode_link("ing_egre_saldos_excel_resumido.php",array("cmd"=>$cmd));?>
        <img src="../../imagenes/excel.gif" style='cursor:pointer;'  onclick="window.open('<?=$link?>')" title="Listado Resumido">
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
    <td align=right id=mo><a id=mo href='<?=encode_link("ing_egre_saldos.php",array("sort"=>"1","up"=>$up))?>'>CUIE</a></td>      	
    <td align=right id=mo><a id=mo href='<?=encode_link("ing_egre_saldos.php",array("sort"=>"2","up"=>$up))?>'>Nombre</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("ing_egre_saldos.php",array("sort"=>"3","up"=>$up))?>'>Domicilio</a></td>    
    <td align=right id=mo><a id=mo href='<?=encode_link("ing_egre_saldos.php",array("sort"=>"4","up"=>$up))?>'>Cuidad</a></td>        
    <td align=right id=mo>Ingreso</td>        
    <td align=right id=mo>Egreso</td>        
    <td align=right id=mo>Saldo</td>            
  </tr>
 <?
   while (!$result->EOF) {
   		$cuie=$result->fields['cuie'];
  		$sql="select monto_egreso from contabilidad.egreso
		where cuie='$cuie'";
		$res_egreso=sql($sql,"no puede calcular el saldo");
		
		if ($res_egreso->recordCount()==0){
			$sql="select ingre as total, ingre,egre from
				(select sum (monto_deposito)as ingre from contabilidad.ingreso
				where cuie='$cuie') as ingreso,
				(select sum (monto_egreso)as egre from contabilidad.egreso
				where cuie='$cuie') as egreso";
		
		}
		else{
		$sql="select ingre-egre as total, ingre,egre from
				(select sum (monto_deposito)as ingre from contabilidad.ingreso
				where cuie='$cuie') as ingreso,
				(select sum (monto_egreso)as egre from contabilidad.egreso
				where cuie='$cuie') as egreso";
		}
		$res_saldo=sql($sql,"no puede calcular el saldo")?>
    
    <tr <?=atrib_tr()?>>        
     <td ><?=$result->fields['cuie']?></td>
     <td ><?=$result->fields['nombreefector']?></td>
     <td ><?=$result->fields['domicilio']?></td>     
     <td ><?=$result->fields['cuidad']?></td>         
     <td ><?=number_format($res_saldo->fields['ingre'],2,',','.')?></td>         
     <td ><?=number_format($res_saldo->fields['egre'],2,',','.')?></td>         
     <td ><?=number_format($res_saldo->fields['total'],2,',','.')?></td>         
    </tr>
	<?$result->MoveNext();
    }?>
    
</table>
</form>
</body>
</html>
<?echo fin_pagina();// aca termino ?>