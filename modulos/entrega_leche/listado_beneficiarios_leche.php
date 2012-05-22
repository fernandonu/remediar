<?php
require_once("../../config.php");

variables_form_busqueda("listado_beneficiarios_leche");

$fecha_hoy=date("Y-m-d H:i:s");
$fecha_hoy=fecha($fecha_hoy);

$orden = array(
        "default" => "1",
        "1" => "a",
        "2" => "b",
        "3" => "c",
        "4" => "d",
        "5" => "e"        
       );
$filtro = array(
		"c" => "DNI",
        "a" => "Apellido"                       
       );


$sql_tmp="
select * from (
select 
  nacer.smiafiliados.id_smiafiliados as id,
  nacer.smiafiliados.afiapellido as a,
  nacer.smiafiliados.afinombre as b,
  nacer.smiafiliados.afidni as c,
  nacer.smiafiliados.afifechanac as d,
  nacer.smiafiliados.afidomlocalidad as e,
  'na' as f,
  '' as g
  from nacer.smiafiliados

UNION

select 
  leche.beneficiarios.id_beneficiarios as id,
  leche.beneficiarios.apellido as a,
  leche.beneficiarios.nombre as b,
  leche.beneficiarios.documento as c,
  leche.beneficiarios.fecha_nac as d,
  leche.beneficiarios.domicilio as e,
  'nu' as f,
  '' as g
  from leche.beneficiarios
  
  UNION

select 
  uad.beneficiarios.id_beneficiarios as id,
  trim(uad.beneficiarios.apellido_benef||' '||uad.beneficiarios.apellido_benef_otro) as a,
  trim(uad.beneficiarios.nombre_benef||' '||uad.beneficiarios.nombre_benef_otro) as b,
  uad.beneficiarios.numero_doc as c,
  uad.beneficiarios.fecha_nacimiento_benef as d,
  uad.beneficiarios.calle as e,
  'in' as f,
  cast(id_clasificacion as text)as g
  from uad.beneficiarios 
  left join trazadoras.clasificacion_remediar on beneficiarios.clave_beneficiario=clasificacion_remediar.clave
  )as cc";


echo $html_header;
?>
<form name=form1 action="listado_beneficiarios_leche.php" method=POST>
<table cellspacing=2 cellpadding=2 border=0 width=100% align=center>
     <tr>
      <td align=center>
		<?list($sql,$total_muletos,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");?>
	    &nbsp;&nbsp;<input type=submit name="buscar" value='Buscar'>
	    &nbsp;&nbsp;<input type='button' name="nuevo_dato" value='Nuevo Dato' onclick="document.location='leche_nuevo_admin.php'">
    	</td>
     </tr>
</table>


<table border=0 width=100% cellspacing=2 cellpadding=2 bgcolor='<?=$bgcolor3?>' align=center>
  <tr>
  	<td colspan=12 align=left id=ma>
     <table width=100%>
      <tr id=ma>
       <td width=30% align=left><b>Total:</b> <?=$total_muletos?></td>       
       <td width=40% align=right><?=$link_pagina?></td>
      </tr>
    </table>
    
   </td>
  </tr>
  

  <tr>
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_beneficiarios_leche.php",array("sort"=>"1","up"=>$up))?>' >Apellido</a></td>      	
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_beneficiarios_leche.php",array("sort"=>"2","up"=>$up))?>'>Nombre</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_beneficiarios_leche.php",array("sort"=>"3","up"=>$up))?>'>DNI</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_beneficiarios_leche.php",array("sort"=>"4","up"=>$up))?>'>Fecha Nacimiento</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_beneficiarios_leche.php",array("sort"=>"5","up"=>$up))?>'>Domicilio</a></td>        
    <td align=right id=mo>Modificar</a></td> 
    <td align=right id=mo>Entidad Alta</td>    
    <td align=right id=mo>Entrega Leche</td>
	<td align=right id=mo>Ver Clasificacion</td>    
  </tr>
 <?
 if ($_POST['buscar']){
   $result = sql($sql) or die;
   while (!$result->EOF){
   	$ref = encode_link("../facturacion/comprobante_admin.php",array("id_smiafiliados"=>$result->fields['id'],"entidad_alta"=>$result->fields['f'],"pagina_listado"=>"listado_beneficiarios_leche.php"));
    $onclick_elegir="location.href='$ref'";?>
     
     <tr <?=atrib_tr()?>>     
     <td onclick="<?=$onclick_elegir?>"><?=$result->fields['a']?></td>
     <td onclick="<?=$onclick_elegir?>"><?=$result->fields['b']?></td>
     <td onclick="<?=$onclick_elegir?>"><?=$result->fields['c']?></td>     
     <td onclick="<?=$onclick_elegir?>"><?=Fecha($result->fields['d'])?></td> 
     <td onclick="<?=$onclick_elegir?>"><?=$result->fields['e']?></td>     
     <?if ($result->fields['f']=='nu'){?>
      <?$id_1=$result->fields['id'];
      	 $link=encode_link("leche_nuevo_admin.php", array("id_planilla"=>$id_1));?>
	  <td align="center">  <a href="<?=$link?>" title="Modificar"><IMG src='<?=$html_root?>/imagenes/iconnote_resize.gif' height='20' width='20' border='0'></a></td> 
     <?}else echo "<td align='center'><img src='../../imagenes/salir.gif' style='cursor:pointer;'></td>";?> 	  
      <td onclick="<?=$onclick_elegir?>"><? if($result->fields['f']=='na'){ echo 'Nacer';}elseif($result->fields['f']=='in'){ echo 'Remediar';}else{echo'Externo';}?></td> 
     <?$ref_leche = encode_link("comprobante_admin_leche.php",array("id"=>$result->fields['id'],"entidad_alta"=>$result->fields['f']));?>
      <td align="center">  <a href="<?=$ref_leche?>" title="Entrega Leche"><IMG src='<?=$html_root?>/imagenes/iso.jpg' height='20' width='20' border='0'></a></td>  
      
      <? $ellink="../trazadoras/remediar_carga_viejo.php"; 
      if(rtrim($result->fields['g'])==''){
                                $c=$result->fields['c'];
                                $a=$result->fields['a'];
                                $sql_tmp="select * from (select nacer.smiafiliados.id_smiafiliados as id,
                                        nacer.smiafiliados.afiapellido as a,
                                        nacer.smiafiliados.afinombre as b,
                                        nacer.smiafiliados.afidni as c,
                                        nacer.smiafiliados.afifechanac as d,
                                        nacer.smiafiliados.afidomlocalidad as e,
                                        'na' as f,
                                        '' as g  from nacer.smiafiliados    UNION
                                        select  leche.beneficiarios.id_beneficiarios as id,
                                        leche.beneficiarios.apellido as a,
                                        leche.beneficiarios.nombre as b,
                                        leche.beneficiarios.documento as c,
                                        leche.beneficiarios.fecha_nac as d,
                                        leche.beneficiarios.domicilio as e,
                                        'nu' as f,
                                        '' as g
                                        from leche.beneficiarios    UNION
                                        select  uad.beneficiarios.id_beneficiarios as id,
                                        trim(uad.beneficiarios.apellido_benef||' '||uad.beneficiarios.apellido_benef_otro) as a,
                                        trim(uad.beneficiarios.nombre_benef||' '||uad.beneficiarios.nombre_benef_otro) as b,
                                        uad.beneficiarios.numero_doc as c,
                                        uad.beneficiarios.fecha_nacimiento_benef as d,
                                        uad.beneficiarios.calle as e,
                                        'in' as f,
                                        cast(id_clasificacion as text)as g
                                        from uad.beneficiarios   left join trazadoras.clasificacion_remediar2 on beneficiarios.clave_beneficiario=clasificacion_remediar2.clave_beneficiario)as cc
                                        WHERE (c ILIKE '%$c%' and a ILIKE '%$a%')";
                                
                                $result_aux = sql($sql_tmp) or die;
                                if(rtrim($result_aux->fields['g'])!=''){
                                   $ellink="../trazadoras/remediar_carga.php"; 
                                }
          }
                                
	  $ref = encode_link($ellink,array("id_smiafiliados"=>$result->fields['id'],"pagina"=>'listado_beneficiarios_leche.php'));?>
      
      <td align="center">  <input type=button name="ver_clasificacion" value="Ver" 
  	    		onclick="window.open('<?=$ref?>','Trazadoras','dependent:yes,width=900,height=700,top=1,left=60,scrollbars=yes');" 
  	    		title="Ver" Style="background-color:#F781F3;" <? if(rtrim($result_aux->fields['g']=='')&&(rtrim($result->fields['g'])=='')){
                                    echo 'disabled';
                                }   ?>/></td>
     </tr>    
    </tr>
	<?$result->MoveNext();
    }}?>
    
</table>
</form>
</body>
</html>
<?echo fin_pagina();// aca termino ?>