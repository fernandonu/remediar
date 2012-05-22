<?
/*
Author: ferni

modificada por
$Author: ferni $
$Revision: 1.42 $
$Date: 2006/05/23 13:53:00 $
*/

require_once ("../../config.php");


extract($_POST,EXTR_SKIP);
if ($parametros) extract($parametros,EXTR_OVERWRITE);
cargar_calendario();

if ($_POST['guardar_editar']=="Guardar"){
   $db->StartTrans();
      
   $fecha_nac=Fecha_db($fecha_nac);
   $fecha_vac=Fecha_db($fecha_vac);
      
   $query="update trazadoras.vacunas set 
             id_vac_apli='$id_vac_apli', 
             id_dosis_apli='$id_dosis_apli', 
             apellido='$apellido', 
             nombre='$nombre', 
             dni='$dni',
             sexo='$sexo',
             fecha_nac='$fecha_nac',
             nom_resp='$nom_resp',
             fecha_vac='$fecha_vac',
             comentario='$comentario',
             cuie='$cuie'   
             
             where id_vacunas=$id_planilla";

   sql($query, "Error al insertar/actualizar el muleto") or fin_pagina();
    
    
	 
    $db->CompleteTrans();    
   $accion="Los datos se actualizaron";  
}

if ($_POST['guardar']=="Guardar Planilla"){   
   $db->StartTrans();         
    
   $q="select nextval('trazadoras.vacunas_id_vacunas_seq') as id_planilla";
    $id_planilla=sql($q) or fin_pagina();
    $id_planilla=$id_planilla->fields['id_planilla'];
   
   $fecha_nac=Fecha_db($fecha_nac);
   $fecha_vac=Fecha_db($fecha_vac);
         
    $query="insert into trazadoras.vacunas
             (id_vacunas,id_vac_apli,id_dosis_apli,apellido,nombre,dni,sexo,
  				fecha_nac,domicilio,fecha_vac,nom_resp,comentario,cuie)
             values
             ('$id_planilla','$id_vac_apli','$id_dosis_apli','$apellido','$nombre',
  				'$dni','$sexo','$fecha_nac','$domicilio','$fecha_vac',
  				'$nom_resp','$comentario','$cuie')";

    sql($query, "Error al insertar la Planilla") or fin_pagina();
    
    $accion="Se guardo la Planilla";       
	 
    $db->CompleteTrans();    
    //valida si esta captado
    $q="select * from nacer.smiafiliados where afidni='$num_doc'";
    $res_captado=sql($q) or fin_pagina();
    if ($res_captado->RecordCount()==0)
    {
    	$accion2="La Persona NO esta Captada por el Plan Nacer";
    }
    else
    {
    	$accion2="";
    }
    
}//de if ($_POST['guardar']=="Guardar nuevo Muleto")

if ($_POST['borrar']=="Borrar"){
	$query="delete from trazadoras.vacunas
			where id_nino=$id_planilla";
	sql($query, "Error al insertar la Planilla") or fin_pagina();
	$accion="Se elimino la planilla $id_planilla de vacunas"; 	
}

if ($id_planilla) {
$query="SELECT 
  *
FROM
  trazadoras.vacunas  
  where id_vacunas=$id_planilla";

$res_factura=sql($query, "Error al traer el Comprobantes") or fin_pagina();

$cuie=$res_factura->fields['cuie'];
$id_vac_apli=$res_factura->fields['id_vac_apli'];
$id_dosis_apli=$res_factura->fields['id_dosis_apli'];
$apellido=$res_factura->fields['apellido'];
$nombre=$res_factura->fields['nombre'];
$dni=$res_factura->fields['dni'];
$sexo=$res_factura->fields['sexo'];
$fecha_nac=$res_factura->fields['fecha_nac'];
$domicilio=$res_factura->fields['domicilio'];
$fecha_vac=$res_factura->fields['fecha_vac'];
$nom_resp=$res_factura->fields['nom_resp'];
$comentario=$res_factura->fields['comentario'];

}
echo $html_header;
?>
<script>
//controlan que ingresen todos los datos necesarios par el muleto
function control_nuevos()
{
 
 if(document.all.cuie.value=="-1"){
  alert('Debe Seleccionar un Efector');
  return false;
 }
 if(document.all.clase_doc.value=="-1"){
  alert('Debe Seleccionar una Clase');
  return false; 
 } 
 if(document.all.tipo_doc.value=="-1"){
  alert('Debe Seleccionar un Tipo de Documento');
  return false; 
 }   
 if(document.all.dni.value==""){
  alert('Debe Ingresar un Documento');
  return false;
 }
 if(document.all.apellido.value==""){
  alert('Debe Ingresar un apellido');
  return false;
 }
 if(document.all.nombre.value==""){
  alert('Debe Ingresar un nombre');
  return false;
 }

 if(document.all.sexo.value=="-1"){
  alert('Debe Seleccionar un Sexo');
  return false; 
 } 
 
 if(document.all.fecha_nac.value==""){
  alert('Debe Ingresar una Fecha de Nacimiento');
  return false;
 } 
 
 if(document.all.domicilio.value==""){
  alert('Debe Ingresar un Domicilio');
  return false;
 }
 
 if(document.all.id_vac_apli.value=="-1"){
  alert('Debe Seleccionar una Vacuna Aplicada');
  return false; 
 } 
 
 if(document.all.id_dosis_apli.value=="-1"){
  alert('Debe Seleccionar una dosis Aplicada');
  return false; 
 } 
 
 if(document.all.fecha_vac.value==""){
  alert('Debe Ingresar una Fecha de Vacunacion');
  return false;
 } 
 
 if(document.all.nom_resp.value==""){
  alert('Debe Ingresar un Responsable');
  return false;
 } 
 
}//de function control_nuevos()

function editar_campos()
{
	document.all.cuie.disabled=false;
	document.all.clase_doc.disabled=false;
	document.all.tipo_doc.disabled=false;
	document.all.dni.readOnly=false;
	document.all.apellido.readOnly=false;
	document.all.nombre.readOnly=false;
	document.all.sexo.disabled=false;
	document.all.domicilio.readOnly=false;
	document.all.id_vac_apli.disabled=false;
	document.all.id_dosis_apli.disabled=false;
	document.all.nom_resp.readOnly=false;
	document.all.comentario.readOnly=false;
	
		
	document.all.cancelar_editar.disabled=false;
	document.all.guardar_editar.disabled=false;
	document.all.editar.disabled=true;
 	return true;
}//de function control_nuevos()

/**********************************************************/
//funciones para busqueda abreviada utilizando teclas en la lista que muestra los clientes.
var digitos=10; //cantidad de digitos buscados
var puntero=0;
var buffer=new Array(digitos); //declaración del array Buffer
var cadena="";

function buscar_combo(obj)
{
   var letra = String.fromCharCode(event.keyCode)
   if(puntero >= digitos)
   {
       cadena="";
       puntero=0;
   }   
   //sino busco la cadena tipeada dentro del combo...
   else
   {
       buffer[puntero]=letra;
       //guardo en la posicion puntero la letra tipeada
       cadena=cadena+buffer[puntero]; //armo una cadena con los datos que van ingresando al array
       puntero++;

       //barro todas las opciones que contiene el combo y las comparo la cadena...
       //en el indice cero la opcion no es valida
       for (var opcombo=1;opcombo < obj.length;opcombo++){
          if(obj[opcombo].text.substr(0,puntero).toLowerCase()==cadena.toLowerCase()){
          obj.selectedIndex=opcombo;break;
          }
       }
    }//del else de if (event.keyCode == 13)
   event.returnValue = false; //invalida la acción de pulsado de tecla para evitar busqueda del primer caracter
}//de function buscar_op_submit(obj)
</script>

<form name='form1' action='vac_admin.php' method='POST'>
<input type="hidden" value="<?=$id_planilla?>" name="id_planilla">
<?echo "<center><b><font size='+1' color='red'>$accion</font></b></center>";?>
<?echo "<center><b><font size='+1' color='Blue'>$accion2</font></b></center>";?>
<table width="85%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?=$bgcolor_out?>' class="bordes">
 <tr id="mo">
    <td>
    	<?
    	if (!$id_planilla) {
    	?>  
    	<font size=+1><b>Nuevo Dato</b></font>   
    	<? }
        else {
        ?>
        <font size=+1><b>Dato</b></font>   
        <? } ?>
       
    </td>
 </tr>
 <tr><td>
  <table width=90% align="center" class="bordes">
     <tr>
      <td id=mo colspan="2">
       <b> Descripción de la PLANILLA</b>
      </td>
     </tr>
     <tr>
       <td>
        <table>
         <tr>	           
           <td align="center" colspan="2">
            <b> Número del Dato: <font size="+1" color="Red"><?=($id_planilla)? $id_planilla : "Nuevo Dato"?></font> </b>
           </td>
         </tr>
         <tr>	           
           <td align="center" colspan="2">
             <b><font size="2" color="Red">Nota: Los valores numericos se ingresan SIN separadores de miles, y con "." como separador DECIMAL</font> </b>
           </td>
         </tr>
                  
         <tr>
         	<td align="right">
				<b>Efector:</b>
			</td>
			<td align="left">			 	
			 <select name=cuie Style="width=257px" 
        		onKeypress="buscar_combo(this);"
				onblur="borrar_buffer();"
				onchange="borrar_buffer();" 
				<?if ($id_planilla) echo "disabled"?>>
			 <option value=-1>Seleccione</option>
			 <?
			 $sql= "select * from facturacion.smiefectores order by nombreefector";
			 $res_efectores=sql($sql) or fin_pagina();
			 while (!$res_efectores->EOF){ 
			 	$cuiel=$res_efectores->fields['cuie'];
			    $nombre_efector=$res_efectores->fields['nombreefector'];			    
			    ?>
				<option value='<?=$cuiel?>' <?if ($cuie==$cuiel) echo "selected"?> ><?=$nombre_efector." - ".$cuiel?></option>
			    <?
			    $res_efectores->movenext();
			    }?>
			</select>
			</td>
         </tr>
                          
        <tr>
            <td align="right">
				<b>Clase de Documento:</b>
			</td>
			<td align="left">			 	
			 <select name=clase_doc Style="width=257px" <?if ($id_planilla) echo "disabled"?>>
			  <option value=-1>Seleccione</option>
			  <option value=R <?if (($clase_doc=='R')or($clase_doc=='')) echo "selected"?>>Propio</option>
			  <option value=M <?if ($clase_doc=='M') echo "selected"?>>Madre</option>
			  <option value=P <?if ($clase_doc=='P') echo "selected"?>>Padre</option>
			  <option value=T <?if ($clase_doc=='T') echo "selected"?>>Tutor</option>
			 </select>
			</td>
         </tr>
         
         <tr>
           <td align="right">
				<b>Tipo de Documento:</b>
			</td>
			<td align="left">			 	
			 <select name=tipo_doc Style="width=257px" <?if ($id_planilla) echo "disabled"?>>
			  <option value=-1>Seleccione</option>
			  <option value=DNI <?if (($tipo_doc=='DNI')or($tipo_doc=='')) echo "selected"?>>Documento Nacional de Identidad</option>
			  <option value=LE <?if ($tipo_doc=='LE') echo "selected"?>>Libreta de Enrolamiento</option>
			  <option value=LC <?if ($tipo_doc=='LC') echo "selected"?>>Libreta Civica</option>
			  <option value=PA <?if ($tipo_doc=='PA') echo "selected"?>>Pasaporte Argentino</option>
			  <option value=CM <?if ($tipo_doc=='CM') echo "selected"?>>Certificado Migratorio</option>
			 </select>
			</td>
         </tr>
         
         <tr>
         	<td align="right">
         	  <b>Número de Documento:</b>
         	</td>         	
            <td align='left'>
              <input type="text" size="40" value="<?=$dni?>" name="dni" <? if ($id_planilla) echo "readonly"?>><font color="Red">Sin Puntos</font>
            </td>
         </tr> 
         
         <tr>
         	<td align="right">
         	  <b>Apellido:</b>
         	</td>         	
            <td align='left'>
              <input type="text" size="40" value="<?=$apellido?>" name="apellido" <? if ($id_planilla) echo "readonly"?>>
            </td>
         </tr> 
         
         <tr>
         	<td align="right">
         	  <b>Nombre:</b>
         	</td>         	
            <td align='left'>
              <input type="text" size="40" value="<?=$nombre?>" name="nombre" <? if ($id_planilla) echo "readonly"?>>
            </td>
         </tr> 

		<tr>
         <td align="right">
				<b>Sexo:</b>
			</td>
			<td align="left">			 	
			 <select name=sexo Style="width=257px" <?if ($id_planilla) echo "disabled"?>>
			  <option value=-1>Seleccione</option>
			  <option value=MASCULINO <?if ($sexo=='MASCULINO') echo "selected"?>>MASCULINO</option>
			  <option value=FEMENINO <?if ($sexo=='FEMENINO') echo "selected"?>>FEMENINO</option>
			 </select>
			</td>
         </tr>        
                
         <tr>
			<td align="right">
				<b>Fecha de Nacimiento:</b>
			</td>
		    <td align="left">
		    	<?$fecha_comprobante=date("d/m/Y");?>
		    	 <input type=text id=fecha_nac name=fecha_nac value='<?=fecha($fecha_nac);?>' size=15 readonly>
		    	 <?=link_calendario("fecha_nac");?>					    	 
		    </td>		    
		</tr>
		
		<tr>
         	<td align="right">
         	  <b>Domicilio:</b>
         	</td>         	
            <td align='left'>
              <input type="text" size="40" value="<?=$domicilio?>" name="domicilio" <? if ($id_planilla) echo "readonly"?>>
            </td>
         </tr> 
		
         <tr>
         	<td align="right">
				<b>Vacuna Aplicada:</b>
			</td>
			<td align="left">			 	
			 <select name=id_vac_apli Style="width=257px" 
        		onKeypress="buscar_combo(this);"
				onblur="borrar_buffer();"
				onchange="borrar_buffer();" 
				<?if ($id_planilla) echo "disabled"?>>
			 <option value=-1>Seleccione</option>
			 <?
			 $sql= "select * from trazadoras.vac_apli order by nombre";
			 $res_efectores=sql($sql) or fin_pagina();
			 while (!$res_efectores->EOF){ 
			 	$cuiel=$res_efectores->fields['id_vac_apli'];
			    $nombre_efector=$res_efectores->fields['nombre'];			    
			    ?>
				<option value='<?=$cuiel?>' <?if ($id_vac_apli==$cuiel) echo "selected"?> ><?=$nombre_efector?></option>
			    <?
			    $res_efectores->movenext();
			    }?>
			</select>
			</td>
         </tr>
         
        <tr>
         	<td align="right">
				<b>Dosis Aplicada:</b>
			</td>
			<td align="left">			 	
			 <select name=id_dosis_apli Style="width=257px" 
        		onKeypress="buscar_combo(this);"
				onblur="borrar_buffer();"
				onchange="borrar_buffer();" 
				<?if ($id_planilla) echo "disabled"?>>
			 <option value=-1>Seleccione</option>
			 <?
			 $sql= "select * from trazadoras.dosis_apli order by nombre";
			 $res_efectores=sql($sql) or fin_pagina();
			 while (!$res_efectores->EOF){ 
			 	$cuiel=$res_efectores->fields['id_dosis_apli'];
			    $nombre_efector=$res_efectores->fields['nombre'];			    
			    ?>
				<option value='<?=$cuiel?>' <?if ($id_dosis_apli==$cuiel) echo "selected"?> ><?=$nombre_efector?></option>
			    <?
			    $res_efectores->movenext();
			    }?>
			</select>
			</td>
         </tr>   
		
		<tr>
			<td align="right">
				<b>Fecha Vacunacion:</b>
			</td>
		    <td align="left">
		    	<?$fecha_comprobante=date("d/m/Y");?>
		    	 <input type=text id=fecha_vac name=fecha_vac value='<?=fecha($fecha_vac);?>' size=15  readonly> 
		    	 <?=link_calendario("fecha_vac");?>
		    </td>		    
		</tr>
				
		  <tr>
         	<td align="right">
         	  <b>Nombre del Responsable:</b>
         	</td>         	
            <td align='left'>
              <input type="text" size="40" value="<?=$nom_resp?>" name="nom_resp" <? if ($id_planilla) echo "readonly"?>>
            </td>
         </tr> 
          
         <tr>
         	<td align="right">
         	  <b>Observaciones:</b>
         	</td>         	
            <td align='left'>
              <textarea cols='40' rows='4' name='comentario' <? if ($id_planilla) echo "readonly"?>><?=$comentario;?></textarea>
            </td>
         </tr>              
        </table>
      </td>      
     </tr> 
   

   <?if (!($id_planilla)){?>
	 
	 <tr id="mo">
  		<td align=center colspan="2">
  			<b>Guarda Planilla</b>
  		</td>
  	</tr>  
      <tr align="center">
       <td>
        <input type='submit' name='guardar' value='Guardar Planilla' onclick="return control_nuevos()"
         title="Guardar datos de la Planilla">
       </td>
      </tr>
     
     <?}?>
     
 </table>           
<br>
<?if ($id_planilla){?>
<table class="bordes" align="center" width="100%">
		 <tr align="center" id="sub_tabla">
		 	<td>	
		 		Editar DATO
		 	</td>
		 </tr>
		 
		 <tr>
		    <td align="center">
		      <input type=button name="editar" value="Editar" onclick="editar_campos()" title="Edita Campos" style="width=130px"> &nbsp;&nbsp;
		      <input type="submit" name="guardar_editar" value="Guardar" title="Guarda Muleto" disabled style="width=130px" onclick="return control_nuevos()">&nbsp;&nbsp;
		      <input type="button" name="cancelar_editar" value="Cancelar" title="Cancela Edicion de Muletos" disabled style="width=130px" onclick="document.location.reload()">		      
		      <?if (permisos_check("inicio","permiso_borrar")) $permiso="";
			  else $permiso="disabled";?>
		      <input type="submit" name="borrar" value="Borrar" style="width=130px" <?=$permiso?>>
		    </td>
		 </tr> 
	 </table>	
	 <br>
	 <?}?>
 <tr><td><table width=100% align="center" class="bordes">
  <tr align="center">
   <td>
     <input type=button name="volver" value="Volver" onclick="document.location='vac_listado.php'"title="Volver al Listado" style="width=150px">     
   </td>
  </tr>
 </table></td></tr>
 
  
 </table>
 </form>
 
 <?=fin_pagina();// aca termino ?>