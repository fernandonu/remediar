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
   $fecha_control=Fecha_db($fecha_control);
   $triple_viral=Fecha_db($triple_viral);
   $fecha_carga=date("Y-m-d H:m:s");
   $usuario=$_ses_user['name'];
   
   $query="update trazadoras.nino_new set 
           		cuie='$cuie',
           		clave='$clave',
           		clase_doc='$clase_doc',
           		tipo_doc='$tipo_doc',
           		num_doc='$num_doc',
           		apellido='$apellido',
           		nombre='$nombre',
           		fecha_nac='$fecha_nac',
           		fecha_control='$fecha_control',
           		peso='$peso',
           		talla='$talla',
           		percen_peso_edad='$percen_peso_edad',
           		percen_talla_edad='$percen_talla_edad',
           		perim_cefalico='$perim_cefalico',
           		percen_perim_cefali_edad='$percen_perim_cefali_edad',
           		imc='$imc',
           		percen_imc_edad='$percen_imc_edad',
           		percen_peso_talla='$percen_peso_talla',
           		triple_viral='$triple_viral',
           		nino_edad='$nino_edad',
           		observaciones='$observaciones',
           		fecha_carga='$fecha_carga',
           		usuario='$usuario'
             
             where id_nino_new=$id_planilla";

   sql($query, "Error al insertar/actualizar el muleto") or fin_pagina();
    
    
	 
    $db->CompleteTrans();    
   $accion="Los datos se actualizaron";  
}

if ($_POST['guardar']=="Guardar Planilla"){
   $fecha_carga=date("Y-m-d H:m:s");
   $usuario=$_ses_user['name'];
   $db->StartTrans();         
    
   $q="select nextval('trazadoras.nino_new_id_nino_new_seq') as id_planilla";
    $id_planilla=sql($q) or fin_pagina();
    $id_planilla=$id_planilla->fields['id_planilla'];
   
   $fecha_nac=Fecha_db($fecha_nac);
   $fecha_control=Fecha_db($fecha_control);
   if ($triple_viral!="")$triple_viral=Fecha_db($triple_viral);
   else $triple_viral="1980-01-01";  
      
    $query="insert into trazadoras.nino_new
             (id_nino_new,cuie,clave,clase_doc,tipo_doc,num_doc,apellido,nombre,fecha_nac,fecha_control,peso,talla,
  				percen_peso_edad,percen_talla_edad,perim_cefalico,percen_perim_cefali_edad,imc,percen_imc_edad,percen_peso_talla,
  				triple_viral,nino_edad,observaciones,fecha_carga,usuario)
             values
             ('$id_planilla','$cuie','$clave','$clase_doc','$tipo_doc','$num_doc','$apellido','$nombre','$fecha_nac',
             	'$fecha_control','$peso','$talla','$percen_peso_edad','$percen_talla_edad','$perim_cefalico',
             	'$percen_perim_cefali_edad','$imc','$percen_imc_edad','$percen_peso_talla','$triple_viral',
             	'$nino_edad','$observaciones','$fecha_carga','$usuario')";

    sql($query, "Error al insertar la Planilla") or fin_pagina();
    
    $accion="Se guardo la Planilla";       
	 
    $db->CompleteTrans(); 
    
    if ($pagina=="prestacion_admin.php") echo "<script>window.close()</script>";   
           
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
	$query="delete from trazadoras.nino_new
			where id_nino=$id_planilla";
	sql($query, "Error al insertar la Planilla") or fin_pagina();
	$accion="Se elimino la planilla $id_planilla de Niños"; 	
}
if ($pagina=='prestacion_admin.php'){
	
	$sql="select * from nacer.smiafiliados	  
	 where id_smiafiliados=$id_smiafiliados";
	$res_extra=sql($sql, "Error al traer el beneficiario") or fin_pagina();
	
	$clave=$res_extra->fields['clavebeneficiario'];
	$tipo_doc=$res_extra->fields['afitipodoc'];
	$num_doc=number_format($res_extra->fields['afidni'],0,'.','');
	$apellido=$res_extra->fields['afiapellido'];
	$nombre=$res_extra->fields['afinombre'];
	$fecha_nac=$res_extra->fields['afifechanac'];
	$nino_edad=1;
	$clase_doc='R';
	
	$fecha_control=$fecha_comprobante;
	$fpcp=$fecha_comprobante;
	
}

if ($_POST['b']=="b"){
	$sql="select * from nacer.smiafiliados	  
	 where afidni='$num_doc'";
	$res_extra=sql($sql, "Error al traer el beneficiario") or fin_pagina();
	
	if ($res_extra->recordcount()>0){
		$clave=$res_extra->fields['clavebeneficiario'];
		$tipo_doc=$res_extra->fields['afitipodoc'];
		$num_doc=number_format($res_extra->fields['afidni'],0,'.','');
		$apellido=$res_extra->fields['afiapellido'];
		$nombre=$res_extra->fields['afinombre'];
		$fecha_nac=$res_extra->fields['afifechanac'];
		$nino_edad=1;
		$clase_doc='R';
	}
	else {//VER AQUÍ
		$sql="select * from trazadoras.nino_new	  
	 	where num_doc='$num_doc'";
		$res_extra=sql($sql, "Error al traer el beneficiario") or fin_pagina();
		if ($res_extra->recordcount()>0){
			$clave=$res_extra->fields['clave'];
			$tipo_doc=$res_extra->fields['tipo_doc'];
			$num_doc=number_format($res_extra->fields['num_doc'],0,'.','');
			$apellido=$res_extra->fields['apellido'];
			$nombre=$res_extra->fields['nombre'];
			$fecha_nac=$res_extra->fields['fecha_nac'];
			$nino_edad=$res_extra->fields['nino_edad'];
			$clase_doc=$res_extra->fields['clase_doc'];
		}
		else {
			$accion2="Beneficiario no Encontrado";
		}
	}
}
if ($id_planilla) {
$query="SELECT 
  *
FROM
  trazadoras.nino_new  
  where id_nino_new=$id_planilla";
// VER AQUÍ TAMBIÉN
$res_factura=sql($query, "Error al traer el Comprobantes") or fin_pagina();

$cuie=$res_factura->fields['cuie'];
$clave=$res_factura->fields['clave'];
$clase_doc=$res_factura->fields['clase_doc'];
$tipo_doc=$res_factura->fields['tipo_doc'];
$num_doc=number_format($res_factura->fields['num_doc'],0,'.','');
$apellido=$res_factura->fields['apellido'];
$nombre=$res_factura->fields['nombre'];
$fecha_nac=$res_factura->fields['fecha_nac'];
$fecha_control=$res_factura->fields['fecha_control'];
$peso=number_format($res_factura->fields['peso'],3,'.','');
$talla=number_format($res_factura->fields['talla'],0,'','');
$perim_cefalico=number_format($res_factura->fields['perim_cefalico'],3,'.','');
$percen_peso_edad=$res_factura->fields['percen_peso_edad'];
$percen_talla_edad=$res_factura->fields['percen_talla_edad'];
$percen_perim_cefali_edad=$res_factura->fields['percen_perim_cefali_edad'];
$percen_peso_talla=$res_factura->fields['percen_peso_talla'];
$triple_viral=$res_factura->fields['triple_viral'];
$nino_edad=$res_factura->fields['nino_edad'];
$observaciones=$res_factura->fields['observaciones'];
$fecha_carga=$res_factura->fields['fecha_carga'];
$usuario=$res_factura->fields['usuario'];
}
echo $html_header;
?>
<script>
//controlan que ingresen todos los datos necesarios par el muleto
function control_nuevos()
{
function mayor_menor($dato,$mayor,$menor,$mensaje){
	kamikaze=false;
	if (variable == false){
	if ($mayor!=="vacio"){
	  if ($dato.value > $mayor){
		  kamikaze=true;
	  }
	}
	if ($mayor!=="vacio"){
	  if ($dato.value < $menor){
		  kamikaze=true;
	  }
	}
	if (kamikaze==true){
		alert($mensaje+'. De lo contrario comuníquese a Plan Nacer');
		$dato.focus();
		variable=true;
		return variable;
	}
	}
}			
// Funcion Convertir fecha
function f_fecha(fechaentrada,fechasalida) {
 var elem = fechaentrada.split('/');
 var dia = elem[0];
 var mes = elem[1]-1;
 var anio = elem[2];
 fechasalida.setFullYear(eval(anio),eval(mes),eval(dia));
 return fechasalida;
}
// Convierto fechas para poder compararlas después
var vfecha_nac=new Date();
vfecha_nac = f_fecha(document.all.fecha_nac.value,vfecha_nac);	
var vfecha_control=new Date();
vfecha_control = f_fecha(document.all.fecha_control.value,vfecha_control);	
var vtriple_viral=new Date();
vtriple_viral = f_fecha(document.all.triple_viral.value,vtriple_viral);	

variable = false;
$error = false;

function verif_vacio($dato,$vacio,$mensaje){
if (($dato.value==$vacio)&&(variable==false)){
	alert('Debe ingresar'+$mensaje);
	$dato.focus();	
    variable = true;
	return variable;
	}
}
function cambio_cero($dato){		 
	 if ($dato==""){
		 $dato="0";
	 }
}
verif_vacio(document.all.num_doc,""," Documento");
verif_vacio(document.all.cuie,"-1"," Efector");
verif_vacio(document.all.nino_edad,"-1"," Edad");
verif_vacio(document.all.clase_doc,"-1"," Clase de Documento");
verif_vacio(document.all.tipo_doc,"-1"," Tipo de Documento");
verif_vacio(document.all.apellido,""," Apellido");
verif_vacio(document.all.nombre,""," Nombre");
verif_vacio(document.all.fecha_nac,""," Fecha de Nacimiento");
verif_vacio(document.all.fecha_control,""," Fecha de Control");

// ******************  VALIDACION DE FECHA ********************
if(vfecha_nac > vfecha_control){
alert('La fecha de Nacimiento no puede ser mayor a la fecha de control');
document.all.fecha_nac.focus();
return false;
}
//******************************* SOLO VACUNACION *************************
 /// VALIDACION PARA FECHA
 if (document.all.vacuna.checked == true){
	 verif_vacio(document.all.triple_viral,""," fecha de la vacuna antisarampionosa");
	 // ************ TRIPLE VIRAL MAYOR A FECHA NACIMIENTO *****************
	 if (vfecha_nac > vtriple_viral){
	 alert('La fecha de Nacimiento no puede ser mayor a la fecha de colocación de la vacuna');
	 document.all.vfecha_nac.focus();
	 return false;
	 }
	 // CAMBIO CAMPOS VACÍOS POR CERO 00000000
cambio_cero(document.all.perim_cefalico.value);
cambio_cero(document.all.imc.value);
	} else {		
if (variable==false){
 ////////---------------- HASTA 1 AÑO ---------------------------/////////////////////////
if(document.all.nino_edad.value==0){
mayor_menor(document.all.peso,15,2.5,"El peso debe encontrarse entre 2.5 y 15 Kg en niños menores de un año");
mayor_menor(document.all.talla,100,30,"La talla debe encontrarse entre 30 y 100 cm");
verif_vacio(document.all.percen_talla_edad,""," Percentilo Talla Edad");
verif_vacio(document.all.percen_talla_edad,"-1"," Percentilo Talla Edad");
mayor_menor(document.all.perim_cefalico,60,30,"Perimetro Cefalico debe estar entre 30 a 60 cm (decimales con ., ej 35.650)");
verif_vacio(document.all.percen_perim_cefali_edad,""," Perimetro Cefalico Edad");
verif_vacio(document.all.percen_perim_cefali_edad,"-1"," Perimetro Cefalico Edad");
//LLENAR DATOS CON 0
if (document.all.imc.value==""){
document.all.imc.value="0";	
}
}//CIERRE NIÑO EDAD = 0
 /////////-------------- DE 1 A 6 AÑOS-------------------////////////////////////////////
if (document.all.nino_edad.value=="1"){
mayor_menor(document.all.peso,50,5,"El peso debe encontrarse entre 5 y 50 kg para niños de entre 1 a 5 años");	
mayor_menor(document.all.talla,160,40,"La talla del niño debe encontrarse entre 40 y 160 cm para niños de entre 1 a 5 años");	
verif_vacio(document.all.percen_talla_edad,""," Percentilo Talla Edad");
verif_vacio(document.all.percen_talla_edad,"-1"," Percentilo Talla Edad");
mayor_menor(document.all.imc,36,12,"IMC debe encontrarse entre 12 y 36 cm para niños de entre 1 a 5 años");
verif_vacio(document.all.percen_imc_edad,""," Percentilo IMC Edad");
verif_vacio(document.all.percen_imc_edad,"-1"," Percentilo IMC Edad");
//LLENAR DATOS CON 0
if (document.all.perim_cefalico.value==""){
	document.all.perim_cefalico.value="0";
}
} //CIERRO NIÑOS DE 1 A 5 AÑOS
} // CIERRA variable == false
} // CIERRE VACUNA FALSE
	 // ************* TRIPLE VIRAL MAYOR A FECHA NACIMIENTO *****************
	 if (document.all.triple_viral.value != '1980-01-01' || document.all.triple_viral.value != '1980/01/01'){
	 if ((document.all.triple_viral.value != "") && (vfecha_nac > vtriple_viral)){
	 alert('La fecha de Nacimiento no puede ser mayor a la colocación de la Vacuna');
	 //return false;
	 variable=true;
	 }
	 }
if (variable==true){
	return false;
}	 
}//de function control_nuevos()

function editar_campos()
{
	document.all.nino_edad.disabled=false;
	document.all.cuie.disabled=false;
	document.all.clase_doc.disabled=false;
	document.all.tipo_doc.disabled=false;
	document.all.num_doc.readOnly=false;
	document.all.apellido.readOnly=false;
	document.all.nombre.readOnly=false;
	document.all.peso.readOnly=false;
	document.all.percen_peso_edad.disabled=false;
	document.all.talla.readOnly=false;
	document.all.percen_talla_edad.disabled=false;
	document.all.percen_peso_talla.disabled=false;
	document.all.perim_cefalico.readOnly=false;
	document.all.imc.readOnly=false;
	document.all.percen_perim_cefali_edad.disabled=false;
	document.all.percen_imc_edad.disabled=false;
	document.all.observaciones.readOnly=false;
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
<style type="text/css">
<!--
.Estilo1 {
	font-size: large;
	color: #FF6633;
}
-->
</style>


<form name='form1' action='nino_admin_new.php' method='POST'>
<input type="hidden" value="<?=$id_planilla?>" name="id_planilla">
<input type="hidden" value="<?=$pagina?>" name="pagina">
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
        <? } ?>Admin New --</td>
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
            <b> Número del Dato: <font size="+1" color="Red"><?=($id_planilla)? $id_planilla : "Nuevo Dato"?></font> </b>           <label></label></td>
         </tr>
         <tr>	           
           <td align="center" colspan="2">
             <b><font size="2" color="Red">Nota: Los valores numericos se ingresan SIN separadores de miles, y con "." como separador DECIMAL</font> </b>           </td>
         </tr>
         
         <tr>
         	<td align="right">
         	  <b>Número de Documento:</b>         	</td>         	
            <td align='left'>
              <input type="text" size="40" value="<?=$num_doc?>" name="num_doc" <? if ($id_planilla) echo "readonly"?>>
              <input type="submit" size="3" value="b" name="b" id="b"><font color="Red">Sin Puntos</font>            </td>
         </tr> 
         
         <tr>
         	<td align="right">
				<b>Efector:</b>			</td>
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
				<option value='<?=$cuiel?>' <?if ($cuie==$cuiel) echo "selected"?> ><?=$cuiel." - ".$nombre_efector?></option>
			    <?
			    $res_efectores->movenext();
			    }?>
			</select>
			 <input name="vacuna" type="checkbox" id="vacuna" value="true" />
             <span class="Estilo1">Clic para S&oacute;lo Vacuna</span></td>
         </tr>
         
         <td align="right">
				<b>Edad del Niño:</b>			</td>
			<td align="left">			 	
			 <select name=nino_edad Style="width=257px" <?if ($id_planilla) echo "disabled"?>>
			  <option value=-1>Seleccione</option>
			  <option value=0 <?if ($nino_edad=='0') echo "selected"?>>Hasta 1 Año</option>
			  <option value=1 <?if ($nino_edad=='1') echo "selected"?>>Mayor de 1 Año</option>			  
			 </select>			</td>
         </tr>
                           
         <tr>
         	<td align="right">
         	  <b>Clave Beneficiario:</b>         	</td>         	
            <td align='left'>
              <input type="text" size="40" value="<?=$clave?>" name="clave" <? if ($id_planilla) echo "readonly"?>> <font color="Red">No Obligatorio</font>            </td>
         </tr> 
         
         <td align="right">
				<b>Clase de Documento:</b>			</td>
			<td align="left">			 	
			 <select name=clase_doc Style="width=257px" <?if ($id_planilla) echo "disabled"?>>
			  <option value=-1>Seleccione</option>
			  <option value=R <?if ($clase_doc=='R') echo "selected"?>>Propio</option>
			  <option value=M <?if ($clase_doc=='M') echo "selected"?>>Madre</option>
			  <option value=P <?if ($clase_doc=='P') echo "selected"?>>Padre</option>
			  <option value=T <?if ($clase_doc=='T') echo "selected"?>>Tutor</option>
			 </select>			</td>
         </tr>
         
         <td align="right">
				<b>Tipo de Documento:</b>			</td>
			<td align="left">			 	
			 <select name=tipo_doc Style="width=257px" <?if ($id_planilla) echo "disabled"?>>
			  <option value=-1>Seleccione</option>
			  <option value=DNI <?if ($tipo_doc=='DNI') echo "selected"?>>Documento Nacional de Identidad</option>
			  <option value=LE <?if ($tipo_doc=='LE') echo "selected"?>>Libreta de Enrolamiento</option>
			  <option value=LC <?if ($tipo_doc=='LC') echo "selected"?>>Libreta Civica</option>
			  <option value=PA <?if ($tipo_doc=='PA') echo "selected"?>>Pasaporte Argentino</option>
			  <option value=CM <?if ($tipo_doc=='CM') echo "selected"?>>Certificado Migratorio</option>
			 </select>			</td>
         </tr>         
         
         <tr>
         	<td align="right">
         	  <b>Apellido:</b>         	</td>         	
            <td align='left'>
              <input type="text" size="40" value="<?=$apellido?>" name="apellido" <? if ($id_planilla) echo "readonly"?>>            </td>
         </tr> 
         
         <tr>
         	<td align="right">
         	  <b>Nombre:</b>         	</td>         	
            <td align='left'>
              <input type="text" size="40" value="<?=$nombre?>" name="nombre" <? if ($id_planilla) echo "readonly"?>>            </td>
         </tr>          
                
         <tr>
			<td align="right">
				<b>Fecha de Nacimiento:</b>			</td>
		    <td align="left">
		    	<?$fecha_comprobante=date("d/m/Y");?>
		    	 <input type=text id=fecha_nac name=fecha_nac value='<?=fecha($fecha_nac);?>' size=15 >
		    	 <?=link_calendario("fecha_nac");?>		    </td>		    
		</tr>
		
		<tr>
			<td align="right">
				<b>Fecha Control:</b>			</td>
		    <td align="left">
		    	<?$fecha_comprobante=date("d/m/Y");?>
		    	<input type=text id=fecha_control name=fecha_control value='<?=fecha($fecha_control);?>' size=15 /> 
		    	<?=link_calendario("fecha_control");?>&nbsp;&nbsp;<font color="Red">Fecha de Control o Fecha de Antisarampionosa</font></td>		    
		</tr>
				
		<tr>
         	<td align="right">
         	  <b>Peso:</b>         	</td>         	
            <td align='left'>
              <input type="text" size="40" value="<?=$peso?>" name="peso" <? if ($id_planilla) echo "readonly"?>>
              <font color="Red">En Kg (Decimales con ".") - Ni&ntilde;os menores a 1 a&ntilde;o (2.5 a 5 kg) de 1 a 6 a&ntilde;os (5 a 50 kg)</font></td>
        </tr>     
        
        <tr>
         	<td align="right">
         	  <b>Talla:</b>         	</td>         	
            <td align='left'>
              <input type="text" size="40" value="<?=$talla?>" name="talla" <? if ($id_planilla) echo "readonly"?>>
              <font color="Red">En Cm - (30.000 a 160.000 cm)</font></td>
        </tr> 
        
        <td align="right">
				<b>Percentilo Peso/Edad:</b>			</td>
			<td align="left">			 	
			 <select name=percen_peso_edad Style="width=257px" <?if ($id_planilla) echo "disabled"?>>
			  <option value=-1>Seleccione</option>
			  <option value=1 <?if ($percen_peso_edad=='1') echo "selected"?>> <3 </option>
			  <option value=2 <?if ($percen_peso_edad=='2') echo "selected"?>> 3-10 </option>
			  <option value=3 <?if ($percen_peso_edad=='3') echo "selected"?>> >10-90 </option>
			  <option value=4 <?if ($percen_peso_edad=='4') echo "selected"?>> >90-97 </option>
			  <option value=5 <?if ($percen_peso_edad=='5') echo "selected"?>> >97 </option>
			  <option value='' <?if ($percen_peso_edad=='') echo "selected"?>>Dato Sin Ingresar</option>			  
			 </select>			</td>
         </tr>
        
        <tr>
         	<td align="right">
         	  <b>Percentilo Talla/Edad:</b>         	</td>         	
            <td align="left">			 	
			 <select name=percen_talla_edad Style="width=257px" <?if ($id_planilla) echo "disabled"?>>
			  <option value=-1>Seleccione</option>
			  <option value=1 <?if ($percen_talla_edad=='1') echo "selected"?>>-3</option>
			  <option value=2 <?if ($percen_talla_edad=='2') echo "selected"?>>3-97</option>
			  <option value=3 <?if ($percen_talla_edad=='3') echo "selected"?>>+97</option>
			  <option value='' <?if ($percen_talla_edad=='') echo "selected"?>>Dato Sin Ingresar</option>			  
			 </select>			</td>
        </tr>
        
        <tr><td colspan="2"><table width="100%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?=$bgcolor_out?>' class="bordes">   
        <tr>
         	<td align="center" id='ma' colspan="2">
         	  <b>Niños Menores de 1 AÑO</b>         	</td>            
        </tr>      
        <tr>
         	<td align="right">
         	  <b>Perim. Cefalico: </b>         	</td>         	
            <td align='left'>
              <input type="text" size="40" value="<?=$perim_cefalico?>" name="perim_cefalico" <? if ($id_planilla) echo "readonly"?>>
              <font color="Red">En Centimetros (Decimales con ".") (entre 30.000 a 60.000 cm)</font>            </td>
        </tr>
        
        <tr>
         	<td align="right">
         	  <b>Per. Perim. Cefalico/Edad: </b>         	</td>         	
            <td align="left">			 	
			 <select name=percen_perim_cefali_edad Style="width=257px" <?if ($id_planilla) echo "disabled"?>>
			  <option value=-1>Seleccione</option>
			  <option value=1 <?if ($percen_perim_cefali_edad=='1') echo "selected"?>>-3</option>
			  <option value=2 <?if ($percen_perim_cefali_edad=='2') echo "selected"?>>3-97</option>
			  <option value=3 <?if ($percen_perim_cefali_edad=='3') echo "selected"?>>+97</option>
			  <option value='' <?if ($percen_perim_cefali_edad=='') echo "selected"?>>Dato Sin Ingresar</option>			  
			 </select>			</td>
        </tr>         
       </table></td></tr>
        
       <tr><td colspan="2"><table width="85%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?=$bgcolor_out?>' class="bordes">   
        <tr>
         	<td align="center" id='ma' colspan="2">
         	  <b>Niños Mayores de 1 AÑO</b>         	</td>            
        </tr>
       <tr>
         	<td align="right">
         	  <b>IMC: </b>         	</td>         	
            <td align='left'>
              <input type="text" size="40" value="<?=$imc?>" name="imc" <? if ($id_planilla) echo "readonly"?>>
              <font color="Red">"0" en caso de que este vacio. (entre 12 a 36)</font>            </td>
        </tr>
        
         <tr>
         	<td align="right">
         	  <b>Percentilo IMC/Edad: </b>         	</td>         	
            <td align="left">			 	
			 <select name=percen_imc_edad Style="width=257px" <?if ($id_planilla) echo "disabled"?>>
			  <option value=-1>Seleccione</option>
			  <option value=1 <?if ($percen_peso_edad=='1') echo "selected"?>> <3 </option>
			  <option value=2 <?if ($percen_peso_edad=='2') echo "selected"?>> 3-10 </option>
			  <option value=3 <?if ($percen_peso_edad=='3') echo "selected"?>> >10-85 </option>
			  <option value=4 <?if ($percen_peso_edad=='4') echo "selected"?>> >85-97 </option>
			  <option value=5 <?if ($percen_peso_edad=='5') echo "selected"?>> >97 </option>
			  <option value='' <?if ($percen_peso_talla=='') echo "selected"?>>Dato Sin Ingresar</option>			  
			 </select>			</td>
        </tr>        
        
        <tr>
         	<td align="right">
         	  <b>Percentilo Peso/Talla: </b>         	</td>         	
            <td align="left">			 	
			 <select name=percen_peso_talla Style="width=257px" <?if ($id_planilla) echo "disabled"?>>
			  <option value=-1>Seleccione</option>
			  <option value=1 <?if ($percen_peso_edad=='1') echo "selected"?>> <3 </option>
			  <option value=2 <?if ($percen_peso_edad=='2') echo "selected"?>> 3-10 </option>
			  <option value=3 <?if ($percen_peso_edad=='3') echo "selected"?>> >10-85 </option>
			  <option value=4 <?if ($percen_peso_edad=='4') echo "selected"?>> >85-97 </option>
			  <option value=5 <?if ($percen_peso_edad=='5') echo "selected"?>> >97 </option>
			  <option value='' <?if ($percen_peso_talla=='') echo "selected"?>>Dato Sin Ingresar</option>			  
			 </select>			</td>
        </tr> 
        </table></td></tr>
        
        <tr>
			<td align="right">
				<b>Fecha Antisaranpion o triple:</b>			</td>
		    <td align="left">
		    	<?$fecha_comprobante=date("d/m/Y");?>
		    	 <input type=text id=triple_viral name=triple_viral value='<?=fecha($triple_viral);?>' size=15 >
		    	 <?=link_calendario("triple_viral");?>		    </td>		    
		</tr>       
          
         <tr>
         	<td align="right">
         	  <b>Observaciones:</b>         	</td>         	
            <td align='left'>
              <textarea cols='40' rows='4' name='observaciones' <? if ($id_planilla) echo "readonly"?>><?=$observaciones;?></textarea>            </td>
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
     <input type=button name="volver" value="Volver" onclick="document.location='nino_listado_new.php'"title="Volver al Listado" style="width=150px">     
   </td>
  </tr>
 </table></td></tr>
 
 <tr><td><table width=100% align="center" class="bordes">
  <tr align="center">
   <td>
   	<font color="Black" size="3"> <b>En esta pantalla se miden 3 (TRES) TRAZADORAS y los datos minimos a cargar por Trazadora son:</b></font>
   </td>
  </tr>
  <tr align="left">
   <td>
   	<font size="2">Trazadora VI (Inmunizaciones): Campos Hasta Fecha de Nacimiento. Fecha de Control IGUAL a la Fecha de la Antisarapionosa y Fecha de Antisarampionosa.</font>
   </td>
  </tr>
  <tr align="left">
   <td>
   	<font size="2">Trazadora VIII (Control niño sano menor 1 año): Campos Hasta Fecha de Nacimiento. Fecha de Control. Peso. Percentilo Peso/Edad. Talla. Percentilo Talla/Edad o Percentilo Peso/Talla. Campos Recuadrados < 1 Año</font>
   </td>
  </tr>
  <tr align="left">
   <td>
   	<font size="2">Trazadora IX (Control niño sano mayor 1 año): Campos Hasta Fecha de Nacimiento. Fecha de Control. Peso. Percentilo Peso/Edad. Talla. Percentilo Talla/Edad o Percentilo Peso/Talla. Campos Recuadrados > 1 Año</font>
   </td>
  </tr>
 </table></td></tr>
 
 
 </table>
 </form>
 
 <?=fin_pagina();// aca termino ?>