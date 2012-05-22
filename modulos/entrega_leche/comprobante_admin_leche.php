<?


require_once ("../../config.php");


extract($_POST,EXTR_SKIP);
if ($parametros) extract($parametros,EXTR_OVERWRITE);
cargar_calendario();

if($marcar=="True"){	
	 $db->StartTrans();
	 $query="delete from leche.detalle_leche
			where id_detalle_leche='$id_detalle_leche'";

     sql($query, "Error al eliminar el comprobante") or fin_pagina();
     $accion="Se Elimino Entrega";    
     $db->CompleteTrans();   
}

if ($_POST['guardar']=="Guardar Comprobante"){		
	
	$cuie=$_POST['efector'];
	
	$fecha_comprobante=$_POST['fecha_comprobante'];	
	$fecha_comprobante=Fecha_db($fecha_comprobante);
	$periodo=$_POST['periodo'];
	$motivo=$_POST['motivo'];
	$producto=$_POST['producto'];
	$cantidad=$_POST['cantidad'];	
	$comentario=$_POST['comentario'];
	
	if ($entidad_alta=='na'){
		$id_smiafiliados=$id;
		$id_beneficiarios='0';
	}
	else{
		$id_smiafiliados='0';
		$id_beneficiarios=$id;
	}
	
	       
      $db->StartTrans();
		$q="select nextval('leche.detalle_leche_id_detalle_leche_seq') as id_comprobante";
	    $id_comprobante=sql($q) or fin_pagina();
	    $id_comprobante=$id_comprobante->fields['id_comprobante'];	
	    $query="insert into leche.detalle_leche
	             (id_detalle_leche,id_smiafiliados,id_beneficiarios,cuie,cantidad,id_periodo,
  					id_producto,id_motivo,comentario,fecha)
	             values
	             ($id_comprobante,'$id_smiafiliados','$id_beneficiarios',
	              '$cuie','$cantidad','$periodo','$producto',
	              '$motivo','$comentario','$fecha_comprobante')";	
	    sql($query, "Error al insertar el comprobante") or fin_pagina();	
	    
	        
	    $accion="Se guardo el Comprobante Numero: $id_comprobante";	    /*cargo los log*/ 
	    	 
	    $db->CompleteTrans();   
        
}//de if ($_POST['guardar']=="Guardar nuevo Muleto")

if ($entidad_alta=='na'){
$sql="select 
		  nacer.smiafiliados.id_smiafiliados as id,
		  nacer.smiafiliados.afiapellido as a,
		  nacer.smiafiliados.afinombre as b,
		  nacer.smiafiliados.afidni as c,
		  nacer.smiafiliados.afifechanac as d,
		  nacer.smiafiliados.afidomlocalidad as e
     from nacer.smiafiliados	 
	 where id_smiafiliados=$id";
}
else{
$sql="select 
		  leche.beneficiarios.id_beneficiarios as id,
		  leche.beneficiarios.apellido as a,
		  leche.beneficiarios.nombre as b,
		  leche.beneficiarios.documento as c,
		  leche.beneficiarios.fecha_nac as d,
		  leche.beneficiarios.domicilio as e
	 from leche.beneficiarios	 
	 where id_beneficiarios=$id";
}
$res_comprobante=sql($sql, "Error al traer los Comprobantes") or fin_pagina();


$a=$res_comprobante->fields['a'];
$b=$res_comprobante->fields['b'];
$c=$res_comprobante->fields['c'];
$d=$res_comprobante->fields['d'];
$e=$res_comprobante->fields['e'];


echo $html_header;
?>
<script>
//controlan que ingresen todos los datos necesarios par el muleto
function control_nuevos()
{
 if(document.all.efector.value=="-1"){
  alert('Debe Seleccionar un EFECTOR');
  return false;
 }
 if(document.all.periodo.value=="-1"){
  alert('Debe Seleccionar un PERIODO');
  return false;
 }
 if(document.all.motivo.value=="-1"){
  alert('Debe Seleccionar un Motivo');
  return false;
 }
 if(document.all.producto.value=="-1"){
  alert('Debe Seleccionar un Producto');
  return false;
 }
 if(document.all.cantidad.value==""){
  alert('Debe Seleccionar una Cantidad');
  return false;
 }
 if (confirm('Esta Seguro que Desea Agregar Comprobante?'))return true;
 else return false;	
}//de function control_nuevos()

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

<form name='form1' action='comprobante_admin_leche.php' method='POST'>

<?echo "<center><b><font size='+2' color='red'>$accion</font></b></center>";
?>

<input type="hidden" name="id" value="<?=$id?>">
<input type="hidden" name="entidad_alta" value="<?=$entidad_alta?>">
<table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?=$bgcolor_out?>' class="bordes">
 <tr id="mo">
    <td>
     <font size=+1><b>Beneficiario</b></font>    
    </td>
 </tr>
 <tr><td>
  <table width=70% align="center" class="bordes">
     <tr>
      <td id=mo colspan="2">
       <b> Descripción del Beneficiario</b>
      </td>
     </tr>
     <tr>
       <td>
        <table>
         
         <tr>
         	<td align="right">
         	  <b>Apellido:
         	</td>         	
            <td align='left'>
              <input type='text' name='a' value='<?=$a;?>' size=60 align='right' readonly></b>
            </td>
         </tr>
         <tr>
            <td align="right">
         	  <b> Nombre:
         	</td>   
           <td  colspan="2">
             <input type='text' name='b' value='<?=$b;?>' size=60 align='right' readonly></b>
           </td>
          </tr>
          <tr>
           <td align="right">
         	  <b> Documento:
         	</td> 
           <td colspan="2">
             <input type='text' name='c' value='<?=$c;?>' size=60 align='right' readonly></b>
           </td>
          </tr>
          <tr>
           <td align="right">
         	  <b> Fecha de Nacimiento:
         	</td> 
           <td colspan="2">
             <input type='text' name='d' value='<?=Fecha($d);?>' size=60 align='right' readonly></b>
           </td>
          </tr>
          <tr>
           <td align="right">
         	  <b> Domicilio:
         	</td> 
           <td colspan="2">
             <input type='text' name='e' value='<?=$e;?>' size=60 align='right' readonly></b>
           </td>
          </tr>
          
        </table>
      </td>      
     </tr>
   </table>     
	 <table class="bordes" align="center" width="70%">
		 <tr align="center" id="sub_tabla">
		 	<td colspan="2">	
		 		Nuevo Comprobante
		 	</td>
		 </tr>
		 <tr><td class="bordes"><table>
			 <tr>
				 <td>
					 <tr>
					    <td align="right">
					    	<b>Efector:</b>
					    </td>
					    <td align="left">		          			
				 			<select name=efector Style="width:450px"
				 			onKeypress="buscar_combo(this);"
				 			onblur="borrar_buffer();"
				 			onchange="borrar_buffer();"
           					>
			     			<option value=-1>Seleccione</option>
			                 <?
			                 $sql= "select n.cuie, nombreefector, upper(trim(com_gestion))as com_gestion from nacer.efe_conv n inner join facturacion.smiefectores s on n.cuie=s.cuie order by nombreefector";
			                 $res_efectores=sql($sql) or fin_pagina();
			                 while (!$res_efectores->EOF){ 								
			                 	$cuie=$res_efectores->fields['cuie'];
			                 	$nombre_efector=$res_efectores->fields['nombreefector'];								
			                 ?>
			                   <option value=<?=$cuie;?> ><?=$cuie." - ".$nombre_efector?></option>
			                 <?
			                 $res_efectores->movenext();
			                 }
			                 ?>
			      			</select>
					    </td>
					 </tr>					 
					 
					 <tr>
					 	<td align="right">
					    	<b>Fecha Prestación:</b>
					    </td>
					    <td align="left">
					    						    	
					    	<?$fecha_comprobante=date("d/m/Y");?>
					    	 <input type=text id=fecha_comprobante name=fecha_comprobante value='<?=$fecha_comprobante;?>' size=15 readonly>
					    	 <?=link_calendario("fecha_comprobante");?>					    	 
					    </td>		    
					 </tr>
					 
		<tr>
         	<td align="right">
				<b>Periodo:</b>
			</td>
			<td align="left">		          			
			 <select name=periodo Style="width:450px" >
			 <option value=-1>Seleccione</option>
			  <?
			  $sql = "select * from leche.periodo order by periodo";
			  $result=sql($sql,"No se puede traer el periodo");
			  while (!$result->EOF) {?>
			  			  
			  <option value=<?=$result->fields['id_periodo']?> <?if ($periodo==$result->fields['periodo']) echo "selected"?>><?=$result->fields['periodo']?></option>
			  <?
			  $result->movenext();
			  }
			  ?>			
			  </select>
			</td>
         </tr>
         
         <tr>
         <td align="right">
				<b>Motivo:</b>
			</td>
			<td align="left">		          			
			 <select name=motivo Style="width:450px" >
			 <option value=-1>Seleccione</option>
			  <?
			  $sql = "select * from leche.motivo order by desc_motivo";
			  $result=sql($sql,"No se puede traer el periodo");
			  while (!$result->EOF) {?>
			  			  
			  <option value=<?=$result->fields['id_motivo']?> ><?=$result->fields['desc_motivo']?></option>
			  <?
			  $result->movenext();
			  }
			  ?>			
			  </select>
			</td>
         </tr>
         
         <tr>
         <td align="right">
				<b>Producto:</b>
			</td>
			<td align="left">		          			
			 <select name=producto Style="width:450px" >
			 <option value=-1>Seleccione</option>
			  <?
			  $sql = "select * from leche.producto order by desc_producto";
			  $result=sql($sql,"No se puede traer el periodo");
			  while (!$result->EOF) {?>
			  			  
			  <option value=<?=$result->fields['id_producto']?> ><?=$result->fields['desc_producto']?></option>
			  <?
			  $result->movenext();
			  }
			  ?>			
			  </select>
			</td>			
         </tr>
         
         <tr>
         	<td align="right">
         	  <b>Cantidad:
         	</td>         	
            <td align='left'>
              <input type='text' name='cantidad' value='' size=10 align='right'></b>
            </td>
         </tr>
         
					 <tr>
         				<td align="right">
         	  				<b>Comentario:</b>
         				</td>         	
            			<td align='left'>
              				<textarea cols='70' rows='3' name='comentario' ></textarea>
            			</td>
         			</tr>   					 
				  </td>
			 </tr>
		 </table></td></tr>	 
		 <tr>
		  	<td align="center" colspan="2" class="bordes">		      
		    	<input type="submit" name="guardar" value="Guardar Comprobante" title="Guardar Comprobante" Style="width:300px" onclick="return control_nuevos()">
		    </td>
		 </tr> 
	 </table>	
 </td></tr>
 
<?//tabla de comprobantes
$query="SELECT 
  facturacion.smiefectores.nombreefector,
  leche.periodo.periodo,
  leche.motivo.desc_motivo,
  leche.producto.desc_producto,
  leche.detalle_leche.cantidad,
  leche.detalle_leche.fecha,
  leche.detalle_leche.comentario,
  leche.detalle_leche.id_detalle_leche
FROM
  leche.detalle_leche
  INNER JOIN facturacion.smiefectores ON (leche.detalle_leche.cuie = facturacion.smiefectores.cuie)
  INNER JOIN leche.periodo ON (leche.detalle_leche.id_periodo = leche.periodo.id_periodo)
  INNER JOIN leche.producto ON (leche.detalle_leche.id_producto = leche.producto.id_producto)
  INNER JOIN leche.motivo ON (leche.detalle_leche.id_motivo = leche.motivo.id_motivo)";

if ($entidad_alta=='na')
$query.=" where detalle_leche.id_smiafiliados=$id
			order by leche.detalle_leche.fecha DESC";
else
$query.=" where detalle_leche.id_beneficiarios=$id
			order by leche.detalle_leche.fecha DESC";

$res_comprobante=sql($query,"<br>Error al traer los comprobantes<br>") or fin_pagina();
?>
<tr><td><table width="100%" class="bordes" align="center">
	<tr align="center" id="mo">
	  <td align="center" width="3%">
	   <img id="imagen_2" src="<?=$img_ext?>" border=0 title="Mostrar Comprobantes" align="left" style="cursor:pointer;" onclick="muestra_tabla(document.all.prueba_vida,2);" >
	  </td>
	  <td align="center">
	   <b>Comprobantes</b>
	  </td>
	</tr>
</table></td></tr>
<tr><td><table id="prueba_vida" border="1" width="100%" style="display:none;border:thin groove">
	<?if ($res_comprobante->RecordCount()==0){?>
	 <tr>
	  <td align="center">
	   <font size="3" color="Red"><b>No existen comprobantes para este beneficiario</b></font>
	  </td>
	 </tr>
	 <?}
	 else{	 	
	 	?>
	 	<tr id="sub_tabla">	 	    
	 		<td >Efector</td>
	 		<td >Periodo</td>
	 		<td >Motivo</td>
	 		<td >Producto</td>
	 		<td >Cantidad</td>
	 		<td >Fecha</td>
	 		<td >Comentario</td>
	 		<td >Borrar</td>	 		
	 	</tr>
	 	<?
	 	$res_comprobante->movefirst();
	 		while (!$res_comprobante->EOF){
	 		$ref1 = encode_link("comprobante_admin_leche.php",array("id_detalle_leche"=>$res_comprobante->fields['id_detalle_leche'],"marcar"=>"True",'id'=>$id,'entidad_alta'=>$entidad_alta));            		
            $onclick_marcar="if (confirm('Esta Seguro que Desea Eliminar?')) location.href='$ref1'
            				else return false;	";?>
	 		<tr <?=atrib_tr()?>>	 			
		 		<td><?=$res_comprobante->fields['nombreefector']?></td>		 		
		 		<td><?=$res_comprobante->fields['periodo']?></td>		 		
		 		<td><?=$res_comprobante->fields['desc_motivo']?></td>		 		
		 		<td><?=$res_comprobante->fields['desc_producto']?></td>		 		
		 		<td><?=$res_comprobante->fields['cantidad']?></td>		 	 		
		 		<td><?=Fecha($res_comprobante->fields['fecha'])?></td>		 		
		 		<td><?=$res_comprobante->fields['comentario']?></td>
		 		<td onclick="<?=$onclick_marcar?>" align="center"><img src='../../imagenes/salir.gif' style='cursor:pointer;'></td>		 				
		 	</tr>	
		 	
	 		<?$res_comprobante->movenext();
	 	 }
	 	}
	 ?>
</table></td></tr>
 
 <tr><td><table width=100% align="center" class="bordes">
  <tr align="center">
   <td>
     <input type=button name="volver" value="Volver" onclick="document.location='listado_beneficiarios_leche.php'"title="Volver al Listado" style="width:150px">     
   </td>
  </tr>
 </table></td></tr>
 
</table>

    
</form>
<?=fin_pagina();// aca termino ?>
