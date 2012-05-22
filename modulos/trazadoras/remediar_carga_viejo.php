<?

require_once ("../../config.php");
include_once("../facturacion/funciones.php");

extract($_POST,EXTR_SKIP);
if ($parametros) extract($parametros,EXTR_OVERWRITE);
cargar_calendario();

if ($_POST['guardar']=="Guardar Planilla"){
   $fecha_carga=date("Y-m-d H:m:s");
   $usuario=$_ses_user['id'];
   $db->StartTrans();         
    
 $fecha_nac=Fecha_db($fecha_nac);
   $fecha_control=Fecha_db($fecha_control);
   if($hta=='on'){
   	$hta=1;
   }else{
   	$hta=0;
   }
   if($tabaquismo=='on'){
   	$tabaquismo=1;
   }else{
   	$tabaquismo=0;
   }
   if($dislipemia=='on'){
   	$dislipemia=1;
   }else{
   	$dislipemia=0;
   }
   if($obesidad=='on'){
   	$obesidad=1;
   }else{
   	$obesidad=0;
   }
   
    $ta_sist=str_replace(',','.',$ta_sist);
	if($ta_sist==''){
		$ta_sist=0;
	}
	$ta_diast=str_replace(',','.',$ta_diast);
	if($ta_diast==''){
		$ta_diast=0;	
	}
	$col_tot=str_replace(',','.',$col_tot);
	if($col_tot==''){
		$col_tot=0;	
	}
	$hdl=str_replace(',','.',$hdl);
	if($hdl==''){
		$hdl=0;	
	}
	$ldl=str_replace(',','.',$ldl);
	if($ldl==''){
		$ldl=0;	
	}
	$tagss=str_replace(',','.',$tagss);
	if($tagss==''){
		$tagss=0;	
	}
	$gluc=str_replace(',','.',$gluc);
	if($gluc==''){
		$gluc=0;	
	}
	$hba1=str_replace(',','.',$hba1);
	if($hba1==''){
		$hba1=0;	
	}
	$enalapril_mg=str_replace(',','.',$enalapril_mg);
	if($enalapril_mg==''){
		$enalapril_mg=0;	
	}
	$furosemida_mg=str_replace(',','.',$furosemida_mg);
	if($furosemida_mg==''){
		$furosemida_mg=0;	
	}
	$glibenclam_mg=str_replace(',','.',$glibenclam_mg);
	if($glibenclam_mg==''){
		$glibenclam_mg=0;	
	}
	$simvastat_mg=str_replace(',','.',$simvastat_mg);
	if($simvastat_mg==''){
		$simvastat_mg=0;	
	}
	$otras_drogas_mg=str_replace(',','.',$otras_drogas_mg);
	if($otras_drogas_mg==''){
		$otras_drogas_mg=0;	
	}
	$atenolol_mg=str_replace(',','.',$atenolol_mg);
	if($atenolol_mg==''){
		$atenolol_mg=0;	
	}
	$hidroclorot_mg=str_replace(',','.',$hidroclorot_mg);
	if($hidroclorot_mg==''){
		$hidroclorot_mg=0;	
	}
	$metformina_mg=str_replace(',','.',$metformina_mg);
	if($metformina_mg==''){
		$metformina_mg=0;	
	}
	$ass_mg=str_replace(',','.',$ass_mg);
	if($ass_mg==''){
		$ass_mg=0;	
	}
	$otras_drogas2_mg=str_replace(',','.',$otras_drogas2_mg);
	if($otras_drogas2_mg==''){
		$otras_drogas2_mg=0;	
	}
	$peso=str_replace(',','.',$peso);
	if($peso==''){
		$peso=0;
	}
	$talla=str_replace(',','.',$talla);
	if($talla==''){
		$talla=0;
	} 
	if($id_medico==''){
		$accion='Falta el medico.';
	}elseif($id_medico=='new'){
		$queryx="SELECT  id_medico
					FROM planillas.medicos 
				  where apellido_medico=upper('$apellido_medico') and nombre_medico=upper('$nombre_medico') and dni_medico='$dni_medico'";
				$res_comp_num_comp=sql($queryx, "Error al traer el Comprobantes") or fin_pagina();
				if ($res_comp_num_comp->recordcount()==0){
				
					$query1="insert into planillas.medicos  (id_medico,apellido_medico,nombre_medico,dni_medico) 
					values (nextval('planillas.medicos_id_medico_seq'),upper('$apellido_medico'),upper('$nombre_medico'),'$dni_medico') RETURNING id_medico";
					$res_extras1=sql($query1, "Error al insertar la Planilla") or fin_pagina();
					$id_medico=$res_extras1->fields['id_medico'];
				}
				if ($res_comp_num_comp->recordcount()>0){ 
					$accion='Imposible guardar el medico.';
				}
	}
   if (!$id_planilla && $id_medico>0) {// echo $id_medico;
		  //,apellido_medico,nombre_medico,matricula_medico
		$queryx="SELECT  clave,num_doc,apellido,nombre
				FROM trazadoras.clasificacion_remediar  
			  where nro_clasificacion='$nro_clasificacion'";
			$res_comp_num_comp=sql($queryx, "Error al traer el Comprobantes") or fin_pagina();
			if ($res_comp_num_comp->recordcount()==0){
				$query="insert into trazadoras.clasificacion_remediar
				 (id_clasificacion,nro_clasificacion,cuie,clave,clase_doc,tipo_doc,num_doc,apellido,nombre,fecha_nac,fecha_control,peso,talla
					,nino_edad,fecha_carga,usuario
					,dbt,hta,tabaquismo,dislipemia,obesidad,rcvg,ta_sist,ta_diast,col_tot,hdl,ldl,tagss,gluc,hba1
					,enalapril_mg,furosemida_mg,glibenclam_mg,simvastat_mg,otras_drogas,otras_drogas_mg,atenolol_mg,hidroclorot_mg
					,metformina_mg,insulina,ass_mg,otras_drogas2,otras_drogas2_mg,id_medico)
				 values (nextval('trazadoras.clasificacion_remediar_id_clasificacion_seq'),'$nro_clasificacion','$cuie','$clave','$clase_doc','$tipo_doc','$num_doc'
				 ,'$apellido'	 ,'$nombre'	 ,'$fecha_nac','$fecha_control',$peso,$talla,$nino_edad,'$fecha_carga','$usuario'
					,'$dbt','$hta','$tabaquismo','$dislipemia','$obesidad',UPPER('$rcvg'),$ta_sist,$ta_diast,$col_tot,$hdl,$ldl,$tagss,$gluc,$hba1
					,$enalapril_mg,$furosemida_mg,$glibenclam_mg,$simvastat_mg,'$otras_drogas',$otras_drogas_mg,$atenolol_mg,$hidroclorot_mg
					,$metformina_mg,'$insulina',$ass_mg,'$otras_drogas2',$otras_drogas2_mg,$id_medico) RETURNING id_clasificacion";
	
			$res_extras=sql($query, "Error al insertar la Planilla") or fin_pagina();
			$id_planilla=$res_extras->fields['id_clasificacion'];
			$accion='';		
			$accion2="Se guardo la Planilla Clasificacion n� ".$nro_clasificacion; 
/***************************** *********************************************************/			
/***************************** GENIAL IDE A DE BETTINA**********************************************************/
/***************************** *********************************************************/		
					//$fecha_carga=date("Y-m-d H:i:s");
					
					$sql="select id_categoria_prestacion 
							from nomenclador.grupo_prestacion 
							where codigo='$tema' and id_nomenclador_detalle='$id_nomenclador_detalle'";
					$result_id_tema=sql($sql,'error en el tema');
					$id_tema=$result_id_tema->fields['id_categoria_prestacion'];
					if (($pagina_viene=='comprobante_admin_total.php')||(valida_prestacion3($id_comprobante,$id_tema))){
					
						$db->StartTrans();
						(($profesional=='-1')||($profesional==''))?$profesional="P99":$profesional;
						
						$fecha_nacimiento_cod=str_replace('-','',$fecha_nacimiento);
						$fecha_comprobante_cod=substr(str_replace('-','',$fecha_comprobante),0,8);
						
						$codigo=$cuie.$fecha_comprobante_cod.$clave_beneficiario.$fecha_nacimiento_cod.$sexo_codigo.$edad.$prestacion.$tema.$patologia.$profesional; 		
						
						$res_dia_mes_anio=dia_mes_anio($fecha_nacimiento,$fecha_comprobante);
						$anios_desde_nac=$res_dia_mes_anio['anios'];
						$meses_desde_nac=$res_dia_mes_anio['meses'];
						$dias_desde_nac=$res_dia_mes_anio['dias'];
						
						$query_precio="select precio from nomenclador.grupo_prestacion where codigo='$tema'";
						$query_precio=sql($query_precio) or fin_pagina();
						$precio=$query_precio->fields['precio'];
						
							
						 //cargo la nueva prestacion - con nomenclador 2011
						$consulta= "insert into nomenclador.prestaciones_n_op
										(id_prestaciones_n_op,id_comprobante,fecha_nacimiento,fecha_comprobante,sexo_codigo, edad, prestacion, tema,patologia, profesional,codigo,precio,anio,mes,dias)
									values 
										(nextval('nomenclador.prestaciones_n_op_id_prestaciones_n_op_seq'),'$id_comprobante','$fecha_nacimiento_cod','$fecha_comprobante_cod','$sexo_codigo','$edad','$prestacion','$tema','$patologia','$profesional','$codigo','$precio','$anios_desde_nac','$meses_desde_nac','$dias_desde_nac') RETURNING id_prestaciones_n_op";
						$id_prestacion=sql($consulta) or fin_pagina();
						
						$id_prestacion=$id_prestacion->fields['id_prestaciones_n_op'];
						$db->CompleteTrans();   
						$accion2.=" y la Prestacion: ".$id_prestacion;
					}
/***************************** *********************************************************/			
/***************************** FIN GENIAL IDE A DE BETTINA**********************************************************/
/***************************** *********************************************************/		
			}
			if ($res_comp_num_comp->recordcount()>0){
				$accion2='';
				$accion='El Nro. de Clasificacion '.$nro_clasificacion.' ya existe para '.$res_comp_num_comp->fields['apellido'].' '.$res_comp_num_comp->fields['nombre'].', '.$res_comp_num_comp->fields['num_doc'].'.';
			}
	}elseif ($id_planilla && $id_medico>0){
			$query="Update trazadoras.clasificacion_remediar set nro_clasificacion='$nro_clasificacion',fecha_control='$fecha_control',peso=$peso,talla=$talla
			,fecha_modif='$fecha_carga',usuario_modif='$usuario',dbt='$dbt',hta='$hta',tabaquismo='$tabaquismo',dislipemia='$dislipemia'
			,obesidad='$obesidad',rcvg='$rcvg',ta_sist=$ta_sist,ta_diast=$ta_diast,col_tot=$col_tot,hdl=$hdl,ldl=$ldl,tagss=$tagss,gluc=$gluc
			,hba1=$hba1,enalapril_mg=$enalapril_mg,furosemida_mg=$furosemida_mg,glibenclam_mg=$glibenclam_mg,simvastat_mg=$simvastat_mg
			,otras_drogas='$otras_drogas',otras_drogas_mg=$otras_drogas_mg,atenolol_mg=$atenolol_mg,hidroclorot_mg=$hidroclorot_mg,id_medico=$id_medico
			,metformina_mg=$metformina_mg,insulina='$insulina',ass_mg=$ass_mg,otras_drogas2='$otras_drogas2',otras_drogas2_mg=$otras_drogas2_mg	
				where id_clasificacion=$id_planilla";
					
			$res_extras=sql($query, "Error al insertar la Planilla") or fin_pagina();
			$accion='';
			$accion2="Se actualizo la Planilla"; 	
	}

    $db->CompleteTrans(); 
    
    /*if ($pagina=="prestacion_admin.php") echo "<script>window.close()</script>";   */
           
    
}//de if ($_POST['guardar']=="Guardar nuevo Muleto")

if ($_POST['borrar']=="Borrar"){
	$query="delete from trazadoras.clasificacion_remediar
			where id_clasificacion=$id_planilla";
	sql($query, "Error al insertar la Planilla") or fin_pagina();
	$accion="Se elimino la planilla $id_planilla de Ni�os"; 	
}

if ($_POST['buscar_clasificacion']=="b"){
	if ($_POST['nro_clasificacion']!=''){
		$query="SELECT  id_clasificacion,nro_clasificacion
				FROM trazadoras.clasificacion_remediar  
			  where nro_clasificacion='$nro_clasificacion' and clave='$clave'";
			$res_factura=sql($query, "Error al traer el Comprobantes") or fin_pagina();
			if ($res_factura->recordcount()>0){
				$id_planilla=$res_factura->fields['id_clasificacion'];
				$nro_clasificacion=$res_factura->fields['nro_clasificacion'];
				$accion='Clasificacion con N�'.$nro_clasificacion.' encontrada.';
			}
			if ($res_factura->recordcount()==0){
				$accion2='No se encuentra Clasificacion con N�'.$nro_clasificacion.' para este beneficiario';
				$fecha_control='';
				//$id_planilla=0;
				$peso='';
				$talla='';
				$nino_edad='';
				$observaciones='';
				$fecha_carga='';
				$usuario='';
				$hta='';
				$tabaquismo='';
				$dislipemia='';
				$obesidad='';
				$rcvg='';
				$ta_sist='';
				$ta_diast='';
				$col_tot='';
				$hdl='';
				$ldl='';
				$tagss='';
				$gluc='';
				$hba1='';
				$enalapril_mg='';
				$furosemida_mg='';
				$glibenclam_mg='';
				$simvastat_mg='';
				$otras_drogas='';
				$otras_drogas_mg='';
				$atenolol_mg='';
				$hidroclorot_mg='';
				$metformina_mg='';
				$insulina='';
				$ass_mg='';
				$otras_drogas2='';
				$otras_drogas2_mg='';
				
				$apellido_medico='';
				$nombre_medico='';
				$matricula_medico='';
				//$id_smiafiliados=0;
			}
			
	}else{ echo "<SCRIPT Language='Javascript'> alert('Debe Cargar el N� de Clasificacion'); </SCRIPT>";}
}

if ($pagina=='prestacion_admin.php' || $pagina=='listado_beneficiarios_leche.php'){
	if($pagina=='listado_beneficiarios_leche.php'){
		$desabil='disabled';
	}else{
            $desabiledi='disabled';
        }
        
	//echo $pagina.'*'.$id_smiafiliados;
	 $sql="select clave_beneficiario,tipo_documento,clase_documento_benef,numero_doc,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro
				,fecha_nacimiento_benef,sexo,telefono,provincia_nac,departamento,municipio,localidad,calle,barrio,numero_calle,manzana,piso
				,dpto,formulario.centro_inscriptor,fechaempadronamiento,os,cual_os 
				from uad.beneficiarios	  
			inner join uad.remediar_x_beneficiario on beneficiarios.clave_beneficiario=remediar_x_beneficiario.clavebeneficiario
			inner join remediar.formulario on remediar_x_beneficiario.nroformulario=formulario.nroformulario
			 where id_beneficiarios=$id_smiafiliados 
			 group by clave_beneficiario,tipo_documento,clase_documento_benef,numero_doc,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro
				,fecha_nacimiento_benef,sexo,telefono,provincia_nac,departamento,municipio,localidad,calle,barrio,numero_calle,manzana,piso
				,dpto,formulario.centro_inscriptor,fechaempadronamiento,os,cual_os";
	$res_extra=sql($sql, "Error al traer el beneficiario") or fin_pagina();
	if ($res_extra->RecordCount()>0){
		$clave=$res_extra->fields['clave_beneficiario'];
		$tipo_doc=$res_extra->fields['tipo_documento'];
		$clase_doc=$res_extra->fields['clase_documento_benef'];
		$num_doc=number_format($res_extra->fields['numero_doc'],0,'.','');
		$apellido=$res_extra->fields['apellido_benef'].' '.$res_extra->fields['apellido_benef_otro'];
		$nombre=$res_extra->fields['nombre_benef'].' '.$res_extra->fields['nombre_benef_otro'];
		$fecha_nac=fecha($res_extra->fields['fecha_nacimiento_benef']);
		$sexo=$res_extra->fields['sexo'];
		$telefono=$res_extra->fields['telefono'];
		$provincia_nac=$res_extra->fields['provincia_nac'];
		$departamento=$res_extra->fields['departamento'];
		$municipio=$res_extra->fields['municipio'];
		$localidad=$res_extra->fields['localidad'];
		$calle=$res_extra->fields['calle'];
		$barrio=$res_extra->fields['barrio'];
		$numero_calle=$res_extra->fields['numero_calle'];
		$manzana=$res_extra->fields['manzana'];
		$piso=$res_extra->fields['piso'];
		$dpto=$res_extra->fields['dpto'];
	
		$cuie=$res_extra->fields['centro_inscriptor'];
		 $fechaempadronamiento=fecha($res_extra->fields['fechaempadronamiento']);
		$os=$res_extra->fields['os'];
		$cual_os=$res_extra->fields['cual_os'];
		
		$query="SELECT  id_clasificacion,nro_clasificacion
				FROM trazadoras.clasificacion_remediar  
			  where clave='$clave'";
			$res_factura=sql($query, "Error al traer el Comprobantes") or fin_pagina();
			if ($res_factura->recordcount()>0){
				$id_planilla=$res_factura->fields['id_clasificacion'];
				$nro_clasificacion=$res_factura->fields['nro_clasificacion']; //echo rtrim(substr($accion,3,9));
				if(rtrim(substr($accion2,3,6))!="guardo" && rtrim(substr($accion2,3,9))!="actualizo" && $accion!='Imposible guardar el medico.' && $accion!='Falta el medico.'){
					$accion='Beneficiario ya posee Clasificacion con Nro.'.$nro_clasificacion;
				}
			}
		
	}else{
			echo '<script>
			alert("Esta persona no posee Remediar");
			window.close();
			</script>';
		}

}

if ($_POST['editar']=="Editar Planilla"){
$desabil='';
$desabiledi='disabled';
} 

if ($id_planilla) { //echo 'aaa';
$query="SELECT  *
	FROM
  trazadoras.clasificacion_remediar  a
  left join planillas.medicos b on a.id_medico=b.id_medico
  where id_clasificacion=$id_planilla";
// VER AQU� TAMBI�N
$res_factura=sql($query, "Error al traer el Comprobantes") or fin_pagina();

$cuie=$res_factura->fields['cuie'];
$clave=$res_factura->fields['clave'];
$clase_doc=$res_factura->fields['clase_doc'];
$tipo_doc=$res_factura->fields['tipo_doc'];
$num_doc=number_format($res_factura->fields['num_doc'],0,'.','');
$apellido=$res_factura->fields['apellido'];
$nombre=$res_factura->fields['nombre'];
$fecha_nac=fecha($res_factura->fields['fecha_nac']);
$fecha_control=fecha($res_factura->fields['fecha_control']);
$peso=number_format($res_factura->fields['peso'],3,'.','');
$talla=number_format($res_factura->fields['talla'],0,'','');
$nino_edad=$res_factura->fields['nino_edad'];


$dbt=$res_factura->fields['dbt'];
$hta=$res_factura->fields['hta'];
$tabaquismo=$res_factura->fields['tabaquismo'];
$dislipemia=$res_factura->fields['dislipemia'];
$obesidad=$res_factura->fields['obesidad'];
$rcvg=$res_factura->fields['rcvg'];
$ta_sist=$res_factura->fields['ta_sist'];
$ta_diast=$res_factura->fields['ta_diast'];
$col_tot=$res_factura->fields['col_tot'];
$hdl=$res_factura->fields['hdl'];
$ldl=$res_factura->fields['ldl'];
$tagss=$res_factura->fields['tagss'];
$gluc=$res_factura->fields['gluc'];
$hba1=$res_factura->fields['hba1'];
$enalapril_mg=$res_factura->fields['enalapril_mg'];
$furosemida_mg=$res_factura->fields['furosemida_mg'];
$glibenclam_mg=$res_factura->fields['glibenclam_mg'];
$simvastat_mg=$res_factura->fields['simvastat_mg'];
$otras_drogas=$res_factura->fields['otras_drogas'];
$otras_drogas_mg=$res_factura->fields['otras_drogas_mg'];
$atenolol_mg=$res_factura->fields['atenolol_mg'];
$hidroclorot_mg=$res_factura->fields['hidroclorot_mg'];
$metformina_mg=$res_factura->fields['metformina_mg'];
$insulina=$res_factura->fields['insulina'];
$ass_mg=$res_factura->fields['ass_mg'];
$otras_drogas2=$res_factura->fields['otras_drogas2'];
$otras_drogas2_mg=$res_factura->fields['otras_drogas2_mg'];

$apellido_medico=$res_factura->fields['apellido_medico'];
$nombre_medico=$res_factura->fields['nombre_medico'];
$dni_medico=$res_factura->fields['dni_medico'];
$id_medico=$res_factura->fields['id_medico'];
}
echo $html_header; 
?>
<script>
//controlan que ingresen todos los datos necesarios par el muleto
function control_nuevos()
{

//ta_sist
if((document.all.ta_sist.value!='' )&&(document.all.ta_sist.value<80)){ 
	alert('Debe completar la presion arterial con datos validos');
	return false;
}

//ta_diast
if((document.all.ta_diast.value!='')&&(document.all.ta_diast.value<50)){ 
	alert('Debe completar la presion con datos validos');
	return false;
}

//col_tot
if((document.all.col_tot.value!='')&&(document.all.col_tot.value<130)){ 
	alert('Debe completar el colesterol con datos validos');
	return false;
	}

/* RCVG*/
if(document.all.rcvg[0].checked==false && document.all.rcvg[1].checked==false && document.all.rcvg[2].checked==false){
	alert("Debe seleccionar al menos una opcion en RCVG");
	 //document.all.nro_clasificacion.focus();
	 return false;
}
        
/* DBT
if(document.all.dbt[0].checked==false && document.all.dbt[1].checked==false && document.all.hta.checked==false){
	alert("Debe seleccionar al menos una opcion (DBT1 o DBT2 o HTA)");
	 //document.all.nro_clasificacion.focus();
	 return false;
}*/
        
if (document.all.nro_clasificacion.value==""){
	alert("Debe completar el campo Numero de Clasificacion");
	 document.all.nro_clasificacion.focus();
	 return false;
}
var dni_medico=document.all.dni_medico.value;
 if(dni_medico.replace(/^\s+|\s+$/g,"")==""){
                 alert("Debe completar el campo Num. Doc. Medico");
                 document.all.dni_medico.focus();
                 return false;
  }else{
           var dni_medico=document.all.dni_medico.value;
         	if(isNaN(dni_medico)){
                  alert('El dato ingresado en numero de formulario debe ser entero');
                  document.all.dni_medico.focus();
                   return false;
               }
        }
				 
var apellido_medico=document.all.apellido_medico.value;
    if(apellido_medico.replace(/^\s+|\s+$/g,"")==""){
	 alert("Debe completar el campo Apellido Medico");
	 document.all.apellido_medico.focus();
	 return false;
         }else{
	 var charpos = document.all.apellido_medico.value.search("/[^A-Za-z\s]/");
	   if( charpos >= 0)
	    {
	     alert( "El campo Apellido Medico solo permite letras ");
	     document.all.apellido_medico.focus();
	     return false;
	    }
	 }	
	 		var nombre_medico=document.all.nombre_medico.value;
          if(nombre_medico.replace(/^\s+|\s+$/g,"")==""){
	 alert("Debe completar el campo nombre Medico");
	 document.all.nombre_medico.focus();
	 return false;
	 }else{
		 var charpos = document.all.nombre_medico.value.search("/[^A-Za-z\s]/");
		   if( charpos >= 0)
		    {
		     alert( "El campo Nombre Medico solo permite letras ");
		     document.all.nombre_medico.focus();
		     return false;
		    }
		 }

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
		alert($mensaje+'. De lo contrario comun�quese a Plan Nacer');
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
// Convierto fechas para poder compararlas despu�s
var vfecha_control=new Date();
vfecha_control = f_fecha(document.all.fecha_control.value,vfecha_control);	

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
verif_vacio(document.all.fecha_control,""," Fecha de Control");

if (variable==false){
 ////////---------------- HASTA 1 A�O ---------------------------/////////////////////////
if(document.all.nino_edad.value==0){
mayor_menor(document.all.peso,15,2.5,"El peso debe encontrarse entre 2.5 y 15 Kg en ni�os menores de un a�o");
mayor_menor(document.all.talla,100,30,"La talla debe encontrarse entre 30 y 100 cm");

}//CIERRE NI�O EDAD = 0
 /////////-------------- DE 1 A 6 A�OS-------------------////////////////////////////////
if (document.all.nino_edad.value=="1"){
mayor_menor(document.all.peso,50,5,"El peso debe encontrarse entre 5 y 50 kg para ni�os de entre 1 a 5 a�os");	
mayor_menor(document.all.talla,160,40,"La talla del ni�o debe encontrarse entre 40 y 160 cm para ni�os de entre 1 a 5 a�os");	

} //CIERRO NI�OS DE 1 A 5 A�OS
} // CIERRA variable == false

	 // ************* TRIPLE VIRAL MAYOR A FECHA NACIMIENTO *****************
if (variable==true){
	return false;
}	 
}//de function control_nuevos()


/**********************************************************/
//funciones para busqueda abreviada utilizando teclas en la lista que muestra los clientes.
var digitos=10; //cantidad de digitos buscados
var puntero=0;
var buffer=new Array(digitos); //declaraci�n del array Buffer
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
   event.returnValue = false; //invalida la acci�n de pulsado de tecla para evitar busqueda del primer caracter
}//de function buscar_op_submit(obj)
/**********************************************************/
//Validar Fechas
function esFechaValida(fecha){
    if (fecha != undefined && fecha.value != "" ){
        if (!/^\d{2}\/\d{2}\/\d{4}$/.test(fecha.value)){
            alert("formato de fecha no v�lido (dd/mm/aaaa)");
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
            alert("Fecha introducida err�nea");
            return false;
    }
 
        if (dia>numDias || dia==0){
            alert("Fecha introducida err�nea");
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
/**********************************************************/
var patron = new Array(2,2,4)
var patron2 = new Array(5,16)
function mascara(d,sep,pat,nums){
if(d.valant != d.value){
val = d.value
largo = val.length
val = val.split(sep)
val2 = ''
for(r=0;r<val.length;r++){
val2 += val[r]
}
if(nums){
for(z=0;z<val2.length;z++){
if(isNaN(val2.charAt(z))){
letra = new RegExp(val2.charAt(z),"g")
val2 = val2.replace(letra,"")
}
}
}
val = ''
val3 = new Array()
for(s=0; s<pat.length; s++){
val3[s] = val2.substring(0,pat[s])
val2 = val2.substr(pat[s])
}
for(q=0;q<val3.length; q++){
if(q ==0){
val = val3[q]

}
else{
if(val3[q] != ""){
val += sep + val3[q]
}
}
}
d.value = val
d.valant = val
}
}

</script>
<style type="text/css">
<!--
.Estilo1 {
	font-size: large;
	color: #FF6633;
}
-->
</style>
<?// echo $tema.'*'.$id_nomenclador_detalle.'*'.$cuie.'*'.$fecha_comprobante.'*'.$clave_beneficiario.'*'.$fecha_nacimiento.'*'.$sexo_codigo.'*'.$edad.'*'.$prestacion.'*'.$tema.'*'.$patologia.'*'.$profesional.'*'.$pagina_viene.'*'.$id_comprobante;?>

<form name='form1' action='remediar_carga_viejo.php' method='POST'>
<input type="hidden" value="<?=$id_planilla?>" name="id_planilla">
<input type="hidden" value="<?=$pagina?>" name="pagina">
<input type="hidden" value="<?=$id_smiafiliados?>" name="id_smiafiliados">
<input type="hidden" value="<?=$pagina_viene?>" name="pagina_viene">
<input type="hidden" value="<?=$tema?>" name="tema">
<input type="hidden" value="<?=$id_nomenclador_detalle?>" name="id_nomenclador_detalle">
<input type="hidden" value="<?=$cuie?>" name="cuie">
<input type="hidden" value="<?=$fecha_comprobante?>" name="fecha_comprobante">
<input type="hidden" value="<?=$clave_beneficiario?>" name="clave_beneficiario">
<input type="hidden" value="<?=$fecha_nacimiento?>" name="fecha_nacimiento">
<input type="hidden" value="<?=$sexo_codigo?>" name="sexo_codigo">
<input type="hidden" value="<?=$edad?>" name="edad">
<input type="hidden" value="<?=$prestacion?>" name="prestacion">
<input type="hidden" value="<?=$tema?>" name="tema">
<input type="hidden" value="<?=$patologia?>" name="patologia">
<input type="hidden" value="<?=$profesional?>" name="profesional">
<input type="hidden" value="<?=$id_comprobante?>" name="id_comprobante">
<?echo "<center><b><font size='+1' color='Blue'>$accion2</font></b></center>";?>
<?echo "<center><b><font size='+1' color='red'>$accion</font></b></center>";?>

<table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?=$bgcolor_out?>' class="bordes">
<tr>

             <tr id="mo">
                <td align="center" colspan="4" >
                    <b> N&uacute;mero de Clasificacion </b><input type="text" maxlength="10" name="nro_clasificacion" value="<?= $nro_clasificacion ?>" <?php if ($id_planilla) {
        //echo 'readOnly onclick="guardar.disabled=true; nro_clasificacion.readOnly=false;"';
    } echo $desabil; ?>> <input type=submit name="buscar_clasificacion" value="b" title="b" <?= $desabil ?> >                    
               </td>
             </tr>
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
<tr >
	<td>
		<table width=95% align="center" class="bordes">
		<tr>
			<td id=mo colspan="2">
                            <b> Descripci&oacute;n de la PLANILLA</b>
      		</td>
     	</tr>
		</table>
	
        <table width=95% align="center" class="bordes" >
         <tr>	           
           <td align="center" colspan="2">
            <b> N&uacute;mero del Dato: <font size="+1" color="Red"><?=($id_planilla)? $id_planilla : "Nuevo Dato"?></font> </b>           <label></label>
		   </td>
         </tr>
         <tr>	           
           <td align="center" colspan="2">
             <b><font size="2" color="Red">Nota: Los valores numericos se ingresan SIN separadores de miles, y con "." como separador DECIMAL</font> </b>           
		   </td>
         </tr>
		 <tr>
         	<td colspan="2" style="padding-left:10px">
         	  <b>Clave Beneficiario:</b><?=$clave?><input type="hidden" value="<?=$clave?>" name="clave" />
			</td>
         </tr> 
         <tr >
         	<td style="padding-left:10px">
				<b>Efector:</b> <?
			 $sql= "select * from facturacion.smiefectores where cuie='$cuie'";
			 $res_efectores=sql($sql) or fin_pagina();
			 	echo $res_efectores->fields['cuie'].'-'.$res_efectores->fields['nombreefector'];
			   ?><input type="hidden" value="<?=$cuie?>" name="cuie" />
			</td>
			<td style="padding-left:10px">
			
				<b>Fecha de Emp.:</b><?=$fechaempadronamiento?>
			</td>
         </tr>
		 <tr>
         	<td colspan="2" style="padding-left:10px">
         	  <b>Apellido:</b><?=$apellido?><input type="hidden" value="<?=$apellido?>" name="apellido" />
			  &nbsp;&nbsp;
			   <b>Nombre:</b><?=$nombre?><input type="hidden" value="<?=$nombre?>" name="nombre" />
			    &nbsp;&nbsp;
			   <b>Clase de Doc.:</b><?if ($clase_doc=='P')echo "Propio";
			   								if ($clase_doc=='A') echo "Ajeno"?><input type="hidden" value="<?=$clase_doc?>" name="clase_doc" />
	   		&nbsp;&nbsp;
				<b>Tipo de Doc.:</b> <?=$tipo_doc?><input type="hidden" value="<?=$tipo_doc?>" name="tipo_doc" />

        	 
		   </td>
         </tr> 
          <tr>
			<td colspan="2" style="padding-left:10px">
				
         	  <b>Nro. de Doc.:</b> <?=$num_doc?><input type="hidden" value="<?=$num_doc?>" name="num_doc" />
			  &nbsp;&nbsp;
				<b>Fecha de Nacimiento:</b>	<?=$fecha_nac?><input type="hidden" value="<?=$fecha_nac?>" name="fecha_nac" />
				 &nbsp;&nbsp;
                                 <b>Edad-A&ntilde;os:</b><?=substr($fechaempadronamiento,6,10)-substr($fecha_nac,6,10)?><input type="hidden" value="<?=substr($fechaempadronamiento,6,10)-substr($fecha_nac,6,10)?>" name="nino_edad" />
				 &nbsp;&nbsp;
				<b>Sexo:</b> <?if ($sexo=='F') echo "Femenino";
								  if ($sexo=='M') echo "Masculino";?>
				 
			</td>                  
          </tr>     
          <tr>
			<td colspan="2" style="padding-left:10px">
							  
				<b> Datos Cobertura: </b><?=$os?>  <?=$cual_os?>	
				&nbsp;&nbsp;
				<b>Telefono:</b>	<?=$telefono?>
				 &nbsp;&nbsp;
				<b>Provincia:</b><?=$provincia_nac?>
				 &nbsp;&nbsp;
				<b>Departamento:</b> <?=$departamento?>
				 	
			</td>                  
          </tr>  
		  <tr>
			<td colspan="2" style="padding-left:10px">					  
				<b> Municipio: </b><?=$municipio?>
			&nbsp;&nbsp;
			<b> Localidad: </b><?=$localidad?>
			 &nbsp;&nbsp;
			<b> Calle-Ruta: </b><?=$calle?>
			&nbsp;&nbsp;
			<b> Nro. de Puerta: </b><?=$numero_calle?>
			
			</td>                  
          </tr> 
		  <tr>
			<td colspan="2" style="padding-left:10px">
			<b> Barrio: </b><?=$barrio?>
			 &nbsp;&nbsp;
			<b> Mza.: </b><?=$manzana?>
			&nbsp;&nbsp;
			<b> Piso: </b><?=$piso?>
			 &nbsp;&nbsp;
			<b> Casa-Depto: </b><?=$dpto?>	
			</td>                  
          </tr>      
      </table>   
	</td>
</tr>
<tr>
	<td>
	<table width=95% align="center" class="bordes">
      <tr>
        <td colspan="2">
				<table width="100%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?=$bgcolor_out?>' class="bordes">
            	<tr>
              		<td align="center" id='ma'><b>Clasificaci&oacute;n</b> </td>
            	</tr>
            	<tr>
              		<td align="left" style="padding-left:10px">
                            <b title="Sin Diabetes">Sin DBT</b> 
						  <input type="radio" value="0" name="dbt" <?php if((strtoupper($dbt) == "")||strtoupper($dbt) == "0")echo "checked" ;?> title="Sin Diabetes"  <?=$desabil?>/>
						&nbsp;
					  <b title="Diabetes tipo 1">DBT 1</b> 
						  <input type="radio" value="1" name="dbt" <?php if(strtoupper($dbt) == "1")echo "checked" ;?> title="Diabetes tipo 1"  <?=$desabil?>/>
						&nbsp;
						<div style="display:inline; background-color:#68A8E8;"> 
							<b title="Diabetes tipo 2">DBT 2</b>
							<input type="radio" value="2" name="dbt" <?php if(strtoupper($dbt) == "2")echo "checked" ;?>  title="Diabetes tipo 2" <?=$desabil?>/>
							<b title="Hipertension Arterial">HTA</b>
							<input type="checkbox" name="hta" <?php if(strtoupper($hta) == 1)echo "checked" ;?>  title="Hipertension Arterial"  <?=$desabil?>/>
						</div> 
						<b>Dislipemia</b>  <input type="checkbox" name="dislipemia"<?php if(strtoupper($dislipemia) == 1)echo "checked" ;?>  <?=$desabil?>/>
						&nbsp;
						<b>Obesidad</b> <input type="checkbox" name="obesidad"<?php if(strtoupper($obesidad) == 1)echo "checked" ;?>  <?=$desabil?>/>
						 &nbsp;
						 <div style="display:inline; background-color:#68A8E8;">
							 <b>RCVG</b> 
						 </div>
						 <b>Bajo</b> <input type="radio" name="rcvg" value="bajo" <?php if(strtoupper($rcvg) == "BAJO")echo "checked" ;?> <?=$desabil?>/>
						  &nbsp;
						 <div style="display:inline; background-color:#68A8E8;">
							<b title="Moderado">Mod.</b> 
							<input type="radio" name="rcvg" value="mode" <?php if(strtoupper($rcvg) == "MODE")echo "checked" ;?>  title="Moderado" <?=$desabil?>/>
							&nbsp;
							<b title="Alto o Muy Alto">A/Ma</b> 
							<input type="radio" name="rcvg" value="alto" <?php if(strtoupper($rcvg) == "ALTO")echo "checked" ;?>  title="Alto o Muy Alto" <?=$desabil?>/>
						 </div>
              		</td>
           		</tr>
				<tr valign="top">
             		 <td align="left" style="padding-left:10px" valign="top">
                             <b>Tabaquismo</b>  <input type="checkbox" name="tabaquismo"<?php if(strtoupper($tabaquismo) == 1)echo "checked" ;?>  <?=$desabil?>/>
						&nbsp;
						<b title="Control de la Presion Arterial Sistolica">TA Sist</b>
						<input type="text" value="<?=$ta_sist?>" name="ta_sist" size="3" style="font-size:9px;" maxlength="3" title="Control de la Presion Arterial Sistolica" <?=$desabil?>/>
						&nbsp;
						<b title="Control de la Presion Arterial Diastolica">TA Diast</b>
						<input type="text" value="<?=$ta_diast?>" name="ta_diast" size="3" style="font-size:9px;" maxlength="3" title="Control de la Presion Arterial Diastolica" <?=$desabil?>/>
						&nbsp;
						<b title="Control del colesterol Total">Col. Tot.</b>
						<input type="text" value="<?=$col_tot?>" name="col_tot" size="3" style="font-size:9px;" maxlength="3" title="Control del colesterol Total" <?=$desabil?>/>
						&nbsp;
						<b title="Control del colesterol HDL">HDL</b>
						<input type="text" value="<?=$hdl?>" name="hdl" size="3" style="font-size:9px;" maxlength="2" title="Control del colesterol HDL"  <?=$desabil?>/>
						&nbsp;
						<b title="Control del colesterol LDL">LDL</b>
						<input type="text" value="<?=$ldl?>" name="ldl" size="3" style="font-size:9px;" maxlength="3" title="Control del colesterol LDL"  <?=$desabil?>/>
						&nbsp;
						<b title="Control de Trigliceridos">TAGs</b>
						<input type="text" value="<?=$tagss?>" name="tagss" size="3" style="font-size:9px;" maxlength="3" title="Control de Trigliceridos" <?=$desabil?>/>
						&nbsp;
						<b title="Control de la glucemia">Gluc</b>
						<input type="text" value="<?=$gluc?>" name="gluc" size="3" style="font-size:9px;" maxlength="3" title="Control de la glucemia"  <?=$desabil?>/>
						&nbsp;
						<b title="Hemoglobina glicosilada">HbA1</b>
						<input type="text" value="<?=$hba1?>" name="hba1" size="3" style="font-size:9px;" maxlength="2" title="Hemoglobina glicosilada" <?=$desabil?>>
              		</td>
            	</tr>
       			</table>
		</td>
      </tr>
	  <tr>
        <td colspan="2">
				<table width="100%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?=$bgcolor_out?>' class="bordes">
            	<tr>
              		<td align="center" id='ma'><b>Medicaci&oacute;n en miligramos por d&iacute;a</b> </td>
            	</tr>
            	<tr>
              		<td align="left" style="padding-left:10px">
					  <b>Enalapril</b> 					
						<input type="text" value="<?=$enalapril_mg?>" name="enalapril_mg" size="3" style="font-size:9px;" maxlength="4" onblur="if(this.value<2.5 || this.value>40){ this.value='0';}" <?=$desabil?>/>mg
						&nbsp;
						<b>Furosemida</b> 					
						<input type="text" value="<?=$furosemida_mg?>" name="furosemida_mg" size="3" style="font-size:9px;" maxlength="4" onblur="if(this.value<20 || this.value>80){ this.value='0';}" <?=$desabil?>/>mg
						&nbsp;
						<b title="Glibenclamida">Glibenclam</b> 					
						<input type="text" value="<?=$glibenclam_mg?>" name="glibenclam_mg" size="3" style="font-size:9px;" maxlength="4" title="Glibenclamida" onblur="if(this.value<2.5 || this.value>20){ this.value='0';}" <?=$desabil?>/>mg
						&nbsp;
						<b title="Simvastatina">Simvastat</b> 					
						<input type="text" value="<?=$simvastat_mg?>" name="simvastat_mg" size="3" style="font-size:9px;" maxlength="4" title="Simvastatina" onblur="if(this.value<10 || this.value>80){ this.value='0';}" <?=$desabil?>/>mg
						&nbsp;
						<b>Otras Drogas</b> 
						<input type="text" value="<?=$otras_drogas?>" name="otras_drogas" size="16" style="font-size:9px;" maxlength="60" <?=$desabil?>/>
						<input type="text" value="<?=$otras_drogas_mg?>" name="otras_drogas_mg" size="3" style="font-size:9px;" maxlength="4" <?=$desabil?>/>mg
              		</td>
            	</tr>
				<tr>
              		<td align="left" style="padding-left:10px">
					  <b>Atenolol</b> 					
						<input type="text" value="<?=$atenolol_mg?>" name="atenolol_mg" size="3" style="font-size:9px;" maxlength="4"  onblur="if(this.value<25 || this.value>100){ this.value='0';}" <?=$desabil?>/>mg
						&nbsp;
						<b title="Hidroclorotizida"> Hidroclorot</b> 					
						<input type="text" value="<?=$hidroclorot_mg?>" name="hidroclorot_mg" size="3" style="font-size:9px;" maxlength="4" title="Hidroclorotizida" onblur="if(this.value<12.5 || this.value>50){ this.value='0';}" <?=$desabil?>/>mg
						&nbsp;
						<b >Metformina</b> 					
						<input type="text" value="<?=$metformina_mg?>" name="metformina_mg" size="3" style="font-size:9px;" maxlength="4" onblur="if(this.value<500 || this.value>2000){ this.value='0';}" <?=$desabil?>/>mg
						&nbsp;
						<b>Insulina</b> 					
						si<input type="radio" value="1" name="insulina" <?php if(strtoupper($insulina) == "1")echo "checked" ;?>  <?=$desabil?>/>
						no<input type="radio" value="0" name="insulina" <?php if(strtoupper($insulina) == "0")echo "checked" ;?>  <?=$desabil?>/>
						&nbsp;
						<b title="Aspirina">ASS</b> 					
						<input type="text" value="<?=$ass_mg?>" name="ass_mg" size="3" style="font-size:9px;" maxlength="4" title="Aspirina" onblur="if(this.value<75 || this.value>325){ this.value='0';}" <?=$desabil?>/>mg
						&nbsp;
						<input type="text" value="<?=$otras_drogas2?>" name="otras_drogas2" size="16" style="font-size:9px;" maxlength="60" <?=$desabil?>/>
						<input type="text" value="<?=$otras_drogas2_mg?>" name="otras_drogas2_mg" size="3" style="font-size:9px;" maxlength="4" <?=$desabil?>/>mg
              		</td>
            	</tr>
       			</table>
		</td>
      </tr>
	  <tr>
        <td align="right" width="15%"><font color="Red">*</font><b>Fecha Actual:</b> </td>
        <td align="left"><?$fecha_comprobante=date("d/m/Y");?>
            <input type=text id=fecha_control name=fecha_control value='<?=$fecha_control;?>' size=15 onKeyUp="mascara(this,'/',patron,true);" onblur="esFechaValida(this);" <?=$desabil?>/>
            <?=link_calendario("fecha_control");?>
          &nbsp;&nbsp; </td>
      </tr>
      <tr>
        <td align="right"><b>Peso:</b> </td>
        <td align='left'><input type="text" size="40" value="<?=$peso?>" name="peso"  onblur="if(this.value<0.50 || this.value>90){ this.value='0';}" <?=$desabil?>/>
            <font color="Red">En Kg (Decimales con ".") - Ni&ntilde;os menores a 1 a&ntilde;o (2.5 a 5 kg) de 1 a 6 a&ntilde;os (5 a 50 kg)</font> </td>
      </tr>
      <tr>
        <td align="right"><b>Talla:</b> </td>
        <td align='left'><input type="text" size="40" value="<?=$talla?>" name="talla"  onblur="if(this.value<20 || this.value>180){ this.value='0';}" <?=$desabil?>/>
            <font color="Red">En Cm - (30.000 a 160.000 cm)</font> </td>
      </tr>
	  <tr>
	  	<td colspan="2">
			<table width="100%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?=$bgcolor_out?>' class="bordes">
            	<tr>
              		<td align="center" id='ma'>
						<button style="font-size:9px;" onclick="window.open('busca_medico.php','Buscar','dependent:yes,width=900,height=700,top=1,left=60,scrollbars=yes');" <?=$desabil?>>Buscar</button>
						<b>Datos del Medico</b> 
					 </td>
            	</tr>
				<tr>
              		<td align="left" style="padding-left:10px">
					<input type="hidden" size="30" value="<?=$id_medico?>" name="id_medico" maxlength="50" readonly>
					  <b>Apellido:</b>         	
					  <input type="text" size="30" value="<?=$apellido_medico?>" name="apellido_medico" maxlength="50" readonly>          
						&nbsp;
					  <b>Nombre:</b>     
					  <input type="text" size="30" value="<?=$nombre_medico?>" name="nombre_medico" maxlength="50" readonly>
              		&nbsp;
					<b>Doc. Medico:</b>         
             		 <input type="text" size="16" value="<?=$dni_medico?>" name="dni_medico" maxlength="12" readonly> 
					</td>
				</tr>
       			</table>
		</td>
	  </tr>
    </table>
	<? //if (!($id_planilla) && $clave!=''){?>
<table class="bordes" align="center" width="100%">
		 <tr align="center" id="sub_tabla">
                     <td>	
		 		<b>Editar Planilla</b>
		 	</td>
		 	<td>	
		 		<b>Guarda Planilla</b>
		 	</td>
		 </tr>
		 
	 
      <tr align="center">
          <td>
              
        <input type='submit' name='editar' value='Editar Planilla' onclick=""  
               title="Editar datos de la Planilla" <?=$desabiledi?>/>
       </td>
       <td>
        <input type='submit' name='guardar' value='Guardar Planilla' onclick="return control_nuevos()"
         title="Guardar datos de la Planilla" <?=$desabil?>/>
       </td>
      </tr>
     
     <? //}?>
     
 </table>           
<br>

</form>

<?
if(($accion2=="Se actualizo la Planilla")||($accion2=="Se guardo la Planilla Clasificacion n� " . $nro_clasificacion ." y la Prestacion: " . $id_prestacion)){
    sleep(2);
    echo('<script>window.close();</script>');
    } ?> 
 
 <?=fin_pagina();// aca termino ?>