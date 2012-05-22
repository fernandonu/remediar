<?
/*
Author: Gaby $
$Revision: 1.00 $
$Date: 2011/04/05 00:00:00 $
*/

require_once ("../../config.php");


extract($_POST,EXTR_SKIP);
if ($parametros) extract($parametros,EXTR_OVERWRITE);
cargar_calendario();

//-------------------

if ($_POST['relacionar']=="Relacionar") {
	
	$error = 0;
	$seleccionados = $_POST["chk_producto"] or Error("No se seleccionó ningun usuario para asignarle el Area");
	
  if (!$error) {
		$sql_array = array();
		
				$db->StartTrans();
				$n=0;
 			foreach ($seleccionados as $id_usuario) {
 				$q="select nextval('expediente.permiso_areas_id_permiso_area_seq') as id_permiso_area";
				   
					$id_permiso=sql($q, "error al buscar id_permiso_area ") or fin_pagina();
				    $id_permiso_area=$id_permiso->fields['id_permiso_area'];    
					
				    $query_3 = "insert into expediente.permiso_areas 
							(id_permiso_area, id_usuarios, id_area)
		   					values 	('$id_permiso_area', '$id_usuario', '$id_area')";
					 sql($query_3,"error al insertar el dato") or fin_pagina();
					 $sql_array[$n++];
 			
		}
				$result = sql($sql_array) or fin_pagina();
		 $db->CompleteTrans(); 
		Aviso("Se Vinculo correctamente");
		
	}
}

if ($_POST['desvincular']=="Desvincular") {
	
	$error = 0;
	$seleccionados = $_POST["chk_producto"] or Error("No se seleccionó ninguna usuario para desvincular");
	
  if (!$error) {
		$sql_array = array();
		foreach ($seleccionados as $id_usuario) {
			$sql_array[] = "delete from expediente.permiso_usuarios WHERE id_usuarios='id_usuario'";
		}
		$result = sql($sql_array) or fin_pagina();
		Aviso("Se desvinculo correctamente");
	}
}


//-------------------



if ($_POST['guardar_editar']=='Guardar'){
	$db->StartTrans();
   $nom_area=strtoupper($nom_area);     
   $nom_responsable=strtoupper($nom_responsable);
   
   $query="update expediente.area set 
		nom_area='$nom_area',
		nom_responsable='$nom_responsable'
		where id_area=$id_area";	

   sql($query, "Error al actualizar el Area") or fin_pagina();
 	 
    $db->CompleteTrans();    
   $accion="Los datos se actualizaron";  
}

if ($_POST['guardar']=='Guardar'){
	$db->StartTrans();
	
    $q="select nextval('expediente.area_id_area_seq') as id_area";
    $id_area=sql($q) or fin_pagina();
    $id_area=$id_area->fields['id_area']; 

   
   $nom_area=strtoupper($nom_area);
   $nom_responsable=strtoupper($nom_responsable);
   
   $query="insert into expediente.area
   	(id_area, nom_area, nom_responsable)
   	values
   	('$id_area', '$nom_area', '$nom_responsable')";
	
   sql($query, "Error al insertar el Area") or fin_pagina();
 	 
   $accion="Los datos se han guardado correctamente"; 
   
   $db->CompleteTrans();   
         
}

if ($_POST['borrar']=='Borrar'){

	$consulta="SELECT * from expediente.movimiento
				where id_area='$id_area'";
	$res_consulta=sql($consulta,"<br>Error alverificar relacion de Area<br>") or fin_pagina();
	if ($res_consulta->RecordCount()==0){
		
		$query="delete from expediente.area  
				where id_area=$id_area";
	
	sql($query, "Error al eliminar el Entidad") or fin_pagina(); 
	
	$accion="Los datos se han borrado";
	}
	else{
		$accion="No se puede Borrar el Area seleccionada, se perderia la relacion";
	}
}

if ($id_area) {
			$query=" SELECT *
					FROM
					expediente.area 
		  			where id_area=$id_area";

$res_area =sql($query, "Error al traer el Area") or fin_pagina();
$nom_area=$res_area->fields['nom_area'];
$nom_responsable=$res_area->fields['nom_responsable'];
}
echo $html_header;
?>
<script>
function seleccionar(chkbox)
{
for (var i=0;i < document.forms["form1"].elements.length;i++)
{
var elemento = document.forms[0].elements[i];
if (elemento.type == "checkbox")
{
elemento.checked = chkbox.checked
}
}
} 
//empieza funcion mostrar tabla
var img_ext='<?=$img_ext='../../imagenes/rigth2.gif' ?>';//imagen extendido
var img_cont='<?=$img_cont='../../imagenes/down2.gif' ?>';//imagen contraido

function muestra_tabla(obj_tabla,nro){
 oimg=eval("document.all.imagen_"+nro);//objeto tipo IMG
 if (obj_tabla.style.display=='none'){
 	obj_tabla.style.display='inline';
    oimg.show=0;
    oimg.src=img_ext;
 }
 else{
 	obj_tabla.style.display='none';
    oimg.show=1;
	oimg.src=img_cont;
 }
}
//controlan que ingresen todos los datos necesarios par el muleto
function control_nuevos()
{ 
 if(document.all.nom_area.value==""){
  alert('Debe ingresar el nombre del Area');
  return false;
 } 
  if(document.all.nom_responsable.value==""){
  alert('Debe ingresar el nombre del Responsable del Area');
  return false;
 } 
}//de function control_nuevos()

function editar_campos()
{	
	document.all.nom_area.disabled=false;
	document.all.nom_responsable.disabled=false;
	document.all.guardar_editar.disabled=false;
	document.all.cancelar_editar.disabled=false;
	document.all.borrar.disabled=false;
	document.all.guardar.enaible=false;
	return true;
}
//de function control_nuevos()


</script>

<form name='form1' action='area_admin.php' method='POST'>
<input type="hidden" value="<?=$id_area?>" name="id_area">
<?echo "<center><b><font size='+1' color='red'>$accion</font></b></center>";?>
<table width="85%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?=$bgcolor_out?>' class="bordes">
 <tr id="mo">
    <td>
    	<?
    	if (!$id_area) {
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
       <b>Administracion de Areas</b>
      </td>
     </tr>
     <tr>
       <td>
        <table>
         <tr>	           
           <td align="center" colspan="2">
            <b> Número del Dato: <font size="+1" color="Red"><?=($id_area)? $id_area : "Nuevo Dato"?></font> </b>
           </td>
         </tr>
         
        <tr>
         	<td align="right">
         	  <b>Area:</b>
         	</td>         	
            <td align='left'>
              <input type="text" size="60" value="<?=$nom_area;?>" name="nom_area" <? if ($id_area) echo "disabled"?>>
            </td>
         </tr> 
		<tr>
         	<td align="right">
         	  <b>Nombre del Responsable:</b>
         	</td>         	
            <td align='left'>
              <input type="text" size="60" value="<?=$nom_responsable;?>" name="nom_responsable" <? if ($id_area) echo "disabled"?>>
            </td>
         </tr> 
      </table>           
<br>
<?if ($id_area){?>
<table class="bordes" align="center" width="100%">
		 <tr align="center" id="sub_tabla">
		 	<td>	
		 		Editar DATO
		 	</td>
		 </tr>
		 
		 <tr>
		    <td align="center">
		      <input type=button name="editar" value="Editar" onclick="editar_campos()" title="Edita Campos" style="width=130px"> &nbsp;&nbsp;
		      <input type="submit" name="guardar_editar" value="Guardar" title="Guardar" disabled style="width=130px" onclick="return control_nuevos()">&nbsp;&nbsp;
		      <input type="button" name="cancelar_editar" value="Cancelar" title="Cancela Edicion" disabled style="width=130px" onclick="document.location.reload()">		      
		      <input type="submit" name="borrar" value="Borrar" style="width=130px" onclick="return confirm('Esta seguro que desea eliminar')" >
		    </td>
		 </tr> 
	 </table>	
	
	 <?}
	 else {?>
	 	<tr>
		    <td align="center">
		      <input type="submit" name="guardar" value="Guardar" title="Guardar" style="width=130px" onclick="return control_nuevos()">&nbsp;&nbsp;
		      <input type="button" name="cancelar_editar" value="Cancelar" title="Cancela Edicion" style="width=130px" onclick="document.location.reload()">		      
		      <input type="submit" name="borrar" value="Borrar" style="width=130px" onclick="return confirm('Esta seguro que desea eliminar')" >
		    </td>
	 
	 <? } ?>
	 
	 <? //---------------------------------------------------- 
	 
if ($id_area!=''){?>
	 <tr><td><table width="100%" class="bordes" align="center">
			<tr align="center" id="mo">
			  <td align="center" width="3%">
			   <img id="imagen_2" src="<?=$img_ext?>" border=0 title="Mostrar Documentacion" align="left" style="cursor:pointer;" onclick="muestra_tabla(document.all.prueba_vida,2)" >
			  </td>
			  <td align="center">
			   <b>Usuarios Vinculados</b>
			  </td>
			</tr>
	</table></td></tr>
	
	<tr><td><table id="prueba_vida" border="1" width="100%" style="display:none;border:thin groove">
	<?//tabla de comprobantes

				$query2="SELECT
						  *
						FROM
						  expediente.area
						  INNER JOIN expediente.permiso_areas ON (expediente.area.id_area = expediente.permiso_areas.id_area)
						  INNER JOIN sistema.usuarios ON (expediente.permiso_areas.id_usuarios = sistema.usuarios.id_usuario)
  					where expediente.area.id_area='$id_area'";
						 
			
				$res_area=sql($query2, "Error al traer el/los usuarios relacionados al Area") or fin_pagina();
					
			
			if ($res_area->RecordCount()==0){?>
				 <tr>
				  <td align="center">
				   <font size="3" color="Red"><b>No existe ningun usuario relacionado al Area</b></font>
				  </td>
				</tr>
				 <?}
				 else{	 	
				 	?>
				 	<tr id="sub_tabla">	
				 		<td> Seleccionar </td>
				 	    <td >Nombre</td>
				 		<td >Apellido</td>
				 		
				 	</tr>
				 		 <?
						   while (!$res_area->EOF){
						   	$id_area=$res_area->fields['id_area'];
						   ?>
				 		<tr <?=atrib_tr()?>>				 
				 			<td align="center"> <input type=checkbox name="chk_producto[]" value="<?=$id_solicitud?>"> </td>				
					 		<td ><?=$res_area->fields['nombre']?></td>
					 		<td ><?=$res_area->fields['apellido']?></td>					 		
					 	</tr>	
					 	 <?$res_area->movenext();
				 		}// fin while
				 	} //fin del else?>	 	
		</table></td></tr>
		 
		 <tr><td><table width=100% align="center" class="bordes">
			  <tr align="center">
			   <td>
			   	 <input type=submit name="desvincular" value="Desvincular" onclick="return confirm ('Esta Seguro que desea desvincular la solicitud?')" title="Desvincular" style="width=150px">     			   
			  </td>
			  </tr>
		 </table></td></tr>
	
		   </table></td></tr>	
<? //---------------------------------------------------- ?>

	 <? //---------------------------------------------------- ?>
	 <tr><td><table width="100%" class="bordes" align="center">
			<tr align="center" id="mo">
			  <td align="center" width="3%">
			   <img id="imagen_2" src="<?=$img_ext?>" border=0 title="Mostrar Documentacion" align="left" style="cursor:pointer;" onclick="muestra_tabla(document.all.prueba_vida1,2)" >
			  </td>
			  <td align="center">
			   <b>Usuarios para Vincular al Area</b>
			  </td>
			</tr>
	</table></td></tr>
	
	<tr><td><table id="prueba_vida1" border="1" width="100%" style="display:none;border:thin groove">
	<?//tabla de comprobantes

				$query1="SELECT 
							*
						FROM
						  expediente.area
						  RIGHT JOIN expediente.permiso_areas ON (expediente.area.id_area = expediente.permiso_areas.id_area)
						  RIGHT JOIN sistema.usuarios ON (expediente.permiso_areas.id_usuarios = sistema.usuarios.id_usuario)";
						 
			
				$res_area1=sql($query1, "Error al traer el/los usuarios relacionados al Area") or fin_pagina();
					
			
			if ($res_area1->RecordCount()==0){?>
				 <tr>
				  <td align="center">
				   <font size="3" color="Red"><b>No existe ningun usuario relacionado al Area</b></font>
				  </td>
				</tr>
				 <?}
				 else{	 	
				 	?>
				 	<tr id="sub_tabla">	
						<td width="10%">Seleccionar</td>
				 	    <td >Nombre</td>
				 		<td >Apellido</td>
				 		
				 	</tr>
				 		 <?
						   while (!$res_area1->EOF){
						   	
						   	$id_usuario=$res_area1->fields['id_usuario'];
						   	if($res_area1->fields['id_area'] ==null){
							   ?>
						 		<tr <?=atrib_tr()?>>				 
						 			<td align="center"> <input type=checkbox name="chk_producto[]" value="<?=$res_area1->fields['id_usuario']?>"> </td>				
							 		<td ><?=$res_area1->fields['nombre']?></td>
							 		<td ><?=$res_area1->fields['apellido']?></td>					 		
							 	</tr>
								
					 	 <?}
					 	 $res_area1->movenext();
				 		}// fin while
				 	} //fin del else?>	 	
		</table></td></tr>
		 
		 <tr><td><table width=100% align="center" class="bordes">
			  <tr align="center">
			   <td>
			   	 <input type=submit name="relacionar" value="Relacionar" onclick="return confirm ('Esta Seguro?')" title="Relacionar" style="width=150px">     
			   </td>
			  </tr>
		 </table></td></tr>
	
</table></td></tr>	
<? //---------------------------------------------------- 
}?>
 <tr><td><table width=100% align="center" class="bordes">
  <tr align="center">
   <td>
     <input type=button name="volver" value="Volver" onclick="document.location='area_listado.php'"title="Volver al Listado" style="width=150px">     
     </td>
  </tr>
 </table></td></tr>
 
 <tr><td><table width=100% align="center" class="bordes">
  <tr align="center">
   
  </tr>  
 </table></td></tr>
 </td></tr>
 </table>
 </form>
 
 <?=fin_pagina();// aca termino ?>