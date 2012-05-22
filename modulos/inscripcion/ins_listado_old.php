<?php
require_once("../../config.php");
variables_form_busqueda("ins_listado_old");
$queryfunciones="SELECT accion,nombre
			FROM sistema.funciones
                    where habilitado='s' and ((pagina='ins_admin' and nombre='Estados') or nombre='Puco')";
    $res_fun=sql($queryfunciones) or fin_pagina();
    while (!$res_fun->EOF){
        	$estado_intermedio='s';
        	$estado_intermedio_sql=" or uad.beneficiarios.estado_envio='p' ";
			 $periodo_puco='"'.substr($res_fun->fields['accion'],4,2).'/'.substr($res_fun->fields['accion'],0,4).'"';
        $res_fun->movenext();
    }
$orden = array(
        "default" => "1",
        "1" => "beneficiarios.numero_doc",
        "2" => "beneficiarios.cuie_ea",
              
       );
$filtro = array(		
		"numero_doc" => "Número de Documento",		
		"apellido_benef" => "Apellido",
		"beneficiarios.cuie_ea" => "Efector",		
				
       );
       

if($estado_intermedio=='s'){
    if ($cmd == "")  $cmd="p";
     $datos_barra = array(
          array(
            "descripcion"=> "Pendientes",
            "cmd"        => "p"
         ),
         array(
            "descripcion"=> "No Enviados",
            "cmd"        => "n"
         ),
         array(
            "descripcion"=> "Borrados / No Enviados",
            "cmd"        => "d"
         ),
         array(
            "descripcion"=> "Enviados",
            "cmd"        => "e"
         ),
         array(
            "descripcion"=> "Todos",
            "cmd"        => "todos"
         )
    );
}else{
    if ($cmd == "")  $cmd="n";
    $datos_barra = array(
         array(
            "descripcion"=> "No Enviados",
            "cmd"        => "n"
         ),
         array(
            "descripcion"=> "Borrados / No Enviados",
            "cmd"        => "d"
         ),
         array(
            "descripcion"=> "Enviados",
            "cmd"        => "e"
         ),
         array(
            "descripcion"=> "Todos",
            "cmd"        => "todos"
         )
    );
}

generar_barra_nav($datos_barra);

       
$sql_tmp="SELECT beneficiarios.id_beneficiarios,beneficiarios.clave_beneficiario,beneficiarios.numero_doc,beneficiarios.apellido_benef
			,beneficiarios.apellido_benef_otro,beneficiarios.nombre_benef,beneficiarios.nombre_benef_otro
			, smiefectores.nombreefector, case beneficiarios.estado_envio when 'p' then 'Pendiente' when 'n' then 'No Enviado' end  estado_envio
			 FROM uad.beneficiarios
			left join facturacion.smiefectores on beneficiarios.cuie_ea=smiefectores.cuie
			left join remediar.listado_enviados on beneficiarios.id_beneficiarios=listado_enviados.id_beneficiarios";

if ($cmd=="p")
     $where_tmp=" (uad.beneficiarios.estado_envio='p' and tipo_ficha in ('1','3') and activo !='0') "; // Muestro pendientes

if ($cmd=="n")
    $where_tmp=" (uad.beneficiarios.estado_envio='n' and tipo_ficha in ('1','3') and activo !='0') and listado_enviados.id_beneficiarios is  null"; // Muestro los no enviados

if ($cmd=="d")
    $where_tmp=" ((uad.beneficiarios.estado_envio='n' $estado_intermedio_sql) and tipo_ficha in ('1','3') and activo = '0') " ; // Muestro los no enviados pero borrados
    

if ($cmd=="e")
    $where_tmp=" (tipo_ficha in ('1','3')) and listado_enviados.id_beneficiarios is not null"; // Muestro todos los enviados incluso los borrados
    
    
if ($cmd=="todos")
    $where_tmp=" ( tipo_ficha in ('1','3'))"; //Muestro todo enviado, no enviado y borrados en ambos casos

echo $html_header;

if (permisos_check("inicio","genera_archivo_permiso")) $permiso="";
else $permiso="disabled";

$where_tmp.=" group by beneficiarios.id_beneficiarios,beneficiarios.clave_beneficiario,beneficiarios.numero_doc,beneficiarios.apellido_benef
			,beneficiarios.apellido_benef_otro,beneficiarios.nombre_benef,beneficiarios.nombre_benef_otro
			, smiefectores.nombreefector, beneficiarios.estado_envio";
			

if ($_POST['generarnino']){
	echo  "<center><b><font size='+1' color='Blue'>Aguarde por favor. Esta operaci&oacute;n puede demorar varios minutos.</font></b></center>";
            $fechaemp=Fecha_db($_POST['fechaemp']);
			$fechakrga=FechaHora_db($_POST['fechakrga']);
			$periodo_puco=$_POST['periodo_puco'];
		$resultN=sql("select * from uad.archivos_enviados where id_archivos_enviados in (select max(id_archivos_enviados) from  uad.archivos_enviados)" ) or die;
    	$resultN->movefirst();
    	$id_nov = $resultN->fields['cantidad_registros_enviados'];
    	if ($id_nov == null) {$id_nov = 0;}
		
        if($estado_intermedio=='s'){
				include($_POST['archivo_cual'].'.php');
         }
}

if ($_POST['cargaDevueltos']){
    include('modifica_beneficiarios_devueltos.php');
}

?>
<script>
function control(){
    var fecha=document.getElementById('fechakrga');
    if (fecha != undefined && fecha.value != "" ){
        if (!/^\d{2}\/\d{2}\/\d{4}$/.test(fecha.value)){
            alert("formato de fecha no válido (dd/mm/aaaa)");
            return false;
        }
        var dia  =  parseInt(fecha.value.substring(0,2),10);
        var mes  =  parseInt(fecha.value.substring(3,5),10);
        var anio =  parseInt(fecha.value.substring(6),10);
 
        switch(mes){
            case 1:
            case 3:
            case 5:
            case 7:
            case 8:
            case 10:
            case 12:
                numDias=31;
                break;
            case 4: case 6: case 9: case 11:
                numDias=30;
                break;
            case 2:
                if (comprobarSiBisisesto(anio)){ numDias=29 }else{ numDias=28};
                break;
            default:
                alert("Fecha introducida errónea");
                return false;
        }
 
        if (dia>numDias || dia==0){
            alert("Fecha introducida errónea");
            return false;
        }
        //return true;
    }
	
    if(document.getElementById('fechakrga')==null){
        alert("Debe completar el campo fecha de Carga");
        return false;
    }
		 
		 
    var fecha=document.getElementById('fechaemp');
    if (fecha != undefined && fecha.value != "" ){
        if (!/^\d{2}\/\d{2}\/\d{4}$/.test(fecha.value)){
            alert("formato de fecha no válido (dd/mm/aaaa)");
            return false;
        }
        var dia  =  parseInt(fecha.value.substring(0,2),10);
        var mes  =  parseInt(fecha.value.substring(3,5),10);
        var anio =  parseInt(fecha.value.substring(6),10);
 
        switch(mes){
            case 1:
            case 3:
            case 5:
            case 7:
            case 8:
            case 10:
            case 12:
                numDias=31;
                break;
            case 4: case 6: case 9: case 11:
                numDias=30;
                break;
            case 2:
                if (comprobarSiBisisesto(anio)){ numDias=29 }else{ numDias=28};
                break;
            default:
                alert("Fecha introducida errónea");
                return false;
        }
 
        if (dia>numDias || dia==0){
            alert("Fecha introducida errónea");
            return false;
        }
        //return true;
    }
	
    if(document.getElementById('fechaemp').value==null){
        alert("Debe completar el campo fecha de Empadronamiento");
        return false;
    }
		 
    var respuesta=confirm("Esta por generar archivo con el PUCO "+<?=$periodo_puco?>+". Desea continuar?");
    if (respuesta==true)
        return true;
    else
        return false;
    
}	// Fin Control()


	//Validar Fechas
function esFechaValida(fecha){
    if (fecha != undefined && fecha.value != "" ){
        if (!/^\d{2}\/\d{2}\/\d{4}$/.test(fecha.value)){
            alert("formato de fecha no valido (dd/mm/aaaa)");
            return false;
        }
        var dia  =  parseInt(fecha.value.substring(0,2),10);
        var mes  =  parseInt(fecha.value.substring(3,5),10);
        var anio =  parseInt(fecha.value.substring(6),10);
 
    switch(mes){
        case 1:
        case 3:
        case 5:
        case 7:
        case 8:
        case 10:
        case 12:
            numDias=31;
            break;
        case 4: case 6: case 9: case 11:
            numDias=30;
            break;
        case 2:
            if (comprobarSiBisisesto(anio)){ numDias=29 }else{ numDias=28};
            break;
        default:
            alert("Fecha introducida erronea");
            return false;
    }
 
    if (dia>numDias || dia==0){
        alert("Fecha introducida erronea");
            return false;
        }
    return true;
    }
}

function comprobarSiBisisesto(anio){
    if ( ( anio % 100 != 0) && ((anio % 4 == 0) || (anio % 400 == 0))) {
        return true;
    }
    else {
        return false;
    }
}

function controlArchivo(){    
    if(document.getElementById('archivodevuelto').value!=''){
        var devueltos=document.getElementById('archivodevuelto').value;
        if(devueltos.substr(devueltos.length-4, 4)!= ".txt"){
            alert("El Archivo debe tener extencion 'txt'");
            return false;
        }
        return true;                      
    }else{
        alert("Seleccione un Archivo");
        return false; 
    }
}
</script>
<!--form name=form2 action="ins_listado_old.php" method="post" enctype="multipart/form-data" accept-charset='utf-8'>
     <table cellspacing=2 cellpadding=2 border=0 width=100% align=center>
     <tr>
        <td align=center style="padding-top: 15px;">
                &nbsp;&nbsp;<b>Seleccione el archivo devuelto (beneficiarios rechazados):</b>&nbsp;&nbsp;<input type="file" id="archivodevuelto" name="archivodevuelto"  size=20/>
		&nbsp;&nbsp;<input type=submit name="cargaDevueltos" value='Cargar' <?=$permiso?> onclick="return controlArchivo();">
        </td>
     </tr>
     </table>
</form-->
<form name=form1 action="ins_listado_old.php" method="POST" accept-charset='utf-8'>
<table cellspacing=2 cellpadding=2 border=0 width=100% align=center>
    <tr>
      <td align=center style="padding-top: 10px;padding-bottom: 10px;border-bottom:thin solid grey; ">
                &nbsp;&nbsp;<input type=hidden name="periodo_puco"  value="<?=str_replace('"','',$periodo_puco)?>">
                &nbsp;&nbsp;<b>Fecha Empadronamiento/Clasificaci&oacute;n:</b>
		<input type=text id="fechaemp" name="fechaemp" size=9 maxlength="10" onchange="esFechaValida(this);"><?=link_calendario('fechaemp');?>
		&nbsp;&nbsp;<b>Fecha de Carga:</b><input type=text id="fechakrga" name="fechakrga"  size=9 maxlength="10" onchange="esFechaValida(this);"><?=link_calendario('fechakrga');?>
		&nbsp;&nbsp;<b>Archivo:</b><select name=archivo_cual>
									<option value="genera_archivo_remediar">BR</option>
									<option value="genera_archivo_remediar_clasif">C</option>
									</select>
	    &nbsp;&nbsp;<input type=submit name="generarnino" value='Generar' <?=$permiso?> onclick="return control();">
	  </td>
     </tr>     
    <tr>
      <td align=center style="padding-bottom: 5px;padding-top: 10px;">
		<?list($sql,$total_muletos,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");?>
	    &nbsp;&nbsp;<input type=submit name="buscar" value='Buscar'>
	    &nbsp;&nbsp;<input type='button' name="nuevo" value='Nuevo Dato' onclick="document.location='ins_admin_old.php'">
		&nbsp;&nbsp;<input type='button' name="auditoria" onclick="window.open('../remediar/auditoria.php','Auditoria','dependent:yes,width=900,height=700,top=1,left=60,scrollbars=yes');" value="Auditoria"/>
		</td>
                </tr>                
</table>

<? 
$result = sql($sql) or die;


?>

<table border=0 width=100% cellspacing=2 cellpadding=2 bgcolor='<?=$bgcolor3?>' align=center>
  <tr>
  	<td colspan=10 align=left id=ma>
     <table width=100%>
      <tr id=ma>
       <td width=30% align=left><b>Total:</b> <?=$total_muletos?></td>       
       <td width=40% align=right><?=$link_pagina?></td>
      </tr>
    </table>
   </td>
  </tr>
  

  <tr>
  	<td align=right id=mo>Clave Beneficiario</td>      	    
    <td align=right id=mo><a id=mo href='<?=encode_link("ins_listado_old.php",array("sort"=>"1","up"=>$up))?>'>Documento</a></td>      	    
    <td align=right id=mo>Apellido</a></td>      	    
    <td align=right id=mo>Nombre</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("ins_listado_old.php",array("sort"=>"2","up"=>$up))?>'>Efector</a></td> 
    <?if($cmd=='todos')echo('<td align=right id=mo><a id=mo href='. encode_link("ins_listado_old.php",array("sort"=>"2","up"=>$up)) .'>Estado</a></td>');?>
    
  </tr>
 <?
   while (!$result->EOF) {
   	$ref = encode_link("ins_admin_old.php",array("id_planilla"=>$result->fields['id_beneficiarios'],"tapa_ver"=>'block'));
   	
    $onclick_elegir="location.href='$ref'";?>
  
    <tr <?=atrib_tr()?>>     
     <td onclick="<?=$onclick_elegir?>"><?=$result->fields['clave_beneficiario']?></td>  
     <td onclick="<?=$onclick_elegir?>"><?=$result->fields['numero_doc']?></td>        
     <td onclick="<?=$onclick_elegir?>"><?=$result->fields['apellido_benef'].' '.$result->fields['apellido_benef_otro']?></td>
     <td onclick="<?=$onclick_elegir?>"><?=$result->fields['nombre_benef'].' '.$result->fields['nombre_benef_otro']?></td>
     <td onclick="<?=$onclick_elegir?>"><?=$result->fields['nombreefector']?></td>
     <? if($cmd=='todos')echo('<td onclick='.$onclick_elegir.'>'.$result->fields['estado_envio'].'</td>');?>
   </tr>
	<?$result->MoveNext();
    }?>
    
</table>
</form>
</body>
</html>
<? echo fin_pagina();// aca termino ?>