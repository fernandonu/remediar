<?
require_once ("../../config.php");
include_once('lib_inscripcion.php');


extract($_POST,EXTR_SKIP);
if ($parametros) extract($parametros,EXTR_OVERWRITE);
cargar_calendario();

if($fecha_nac==''){
($_POST['fecha_nac']=='')?$fecha_nac='':$fecha_nac=$_POST['fecha_nac'];
}
($_POST['fecha_diagnostico_embarazo']=='')?$fecha_diagnostico_embarazo=date("d/m/Y"):$fecha_diagnostico_embarazo=$_POST['fecha_diagnostico_embarazo'];
($_POST['fecha_probable_parto']=='')?$fecha_probable_parto=date("d/m/Y"):$fecha_probable_parto=$_POST['fecha_probable_parto'];
($_POST['fecha_efectiva_parto']=='')?$fecha_efectiva_parto=date("d/m/Y"):$fecha_efectiva_parto=$_POST['fecha_efectiva_parto'];
if($fecha_inscripcion==''){
($_POST['fecha_inscripcion']=='')?$fecha_inscripcion=date("d/m/Y"):$fecha_inscripcion=$_POST['fecha_inscripcion'];
}
$estado_intermedio='';
$estado_envio_ins='n';
$ape_nom='';
$remediar='';
$uad_benef='';
$prov_uso='';
$queryfunciones="SELECT accion,nombre
			FROM sistema.funciones
                    where habilitado='s' and (pagina='ins_admin' or pagina='all')";
    $res_fun=sql($queryfunciones) or fin_pagina();
    while (!$res_fun->EOF){
        if($res_fun->fields['nombre']=='Guarda Remediar'){
            $remediar='s';//$res_fun->fields['accion'];

        }elseif($res_fun->fields['nombre']=='Estados'){
            $estado_nuevo='s';//$res_fun->fields['accion'];
            $estado_intermedio="estado_envio='p',";
            $estado_envio_ins='p';

        }elseif($res_fun->fields['nombre']=='Otros Ape-Nom'){
            $ape_nom='s';
            $ape_nom_update="";
        }elseif($res_fun->fields['nombre']=='Uad Benef'){
            $uad_benef='s';
        }elseif($res_fun->fields['nombre']=='Provincia'){
            $prov_uso=$res_fun->fields['accion'];
        }
        $res_fun->movenext();
    }
if($id_planilla){
	$queryCategoria="SELECT beneficiarios.*, smiefectores.nombreefector, smiefectores.cuie
			FROM uad.beneficiarios
			left join facturacion.smiefectores on beneficiarios.cuie_ea=smiefectores.cuie 
  	where id_beneficiarios=$id_planilla";

	$resultado=sql($queryCategoria, "Error al traer el Comprobantes") or fin_pagina();
        if($id_categoria==''){
            $id_categoria=$resultado->fields['id_categoria'];
        }
	$semanas_embarazo=$resultado->fields['semanas_embarazo'];
        if($pais_nac==''){
            $pais_nac=$resultado->fields['pais_nac'];
        }
        if($provincia_nac==''){
            $provincia_nac=$resultado->fields['provincia_nac'];
        }
        if($localidad_proc==''){
            $localidad_proc=$resultado->fields['localidad_nac'];
        }
        if($departamento==''){
            $departamento=$resultado->fields['departamento'];
        }
        if($localidad==''){
            $localidad=$resultado->fields['localidad'];
        }
        if($municipio==''){
            $municipio=$resultado->fields['municipio'];
        }
        if($barrio==''){
            $barrio=$resultado->fields['barrio'];
        }
         if($indigena==''){
            $indigena= $resultado->fields['indigena'];
         }
         if($id_tribu==''){
            $id_tribu= $resultado->fields['id_tribu'];
         }
         if($id_lengua==''){
            $id_lengua= $resultado->fields['id_lengua'];
         }
   	$responsable=$resultado->fields['responsable'];
   	$menor_convive_con_adulto=$resultado->fields['menor_convive_con_adulto'];
   	$tipo_doc_madre=$resultado->fields['tipo_doc_madre'];
   	$nro_doc_madre=$resultado->fields['nro_doc_madre'];
   	$apellido_madre=$resultado->fields['apellido_madre'];
   	$nombre_madre=$resultado->fields['nombre_madre'];
   	$sexo=$resultado->fields['sexo'];
   	$clave_beneficiario=$resultado->fields['clave_beneficiario'];
   	$trans=$resultado->fields['tipo_transaccion'];
   	$estado_envio=$resultado->fields['estado_envio'];
   	if ($trans == 'B'){
   		$trans="Borrado";
   	}
   	
}

if($id_categoria=='-1'){
	$embarazada= none; 
	$datos_resp= none;
}
if ($id_categoria=='1'){
	$embarazada=inline;
	$datos_resp=none;
	$puerpera=none;
	if(! $id_planilla){
		$semanas_embarazo=$_POST['semanas_embarazo'];
	}
}else {
	$embarazada=none;
	$datos_resp=none;
	$puerpera=none;
}

if ($id_categoria == '2'){
	$embarazada=none;
	$datos_resp=none;
	$puerpera=inline;
	}

if(($id_categoria=='3')||($id_categoria=='4')){ 
	$datos_resp=inline;
	$embarazada=none;
}


if ($_POST['guardar_editar']=="Guardar"){
   $db->StartTrans();  
   
   $fecha_carga=date("Y-m-d HH:mm:ss");
   $usuario=$_ses_user['id'];
   if($tipo_ficha==2){ $tipo_ficha=3; }elseif($tipo_ficha!=3){ $tipo_ficha=1; }
   	$fecha_nac=Fecha_db($fecha_nac);
   	$fecha_diagnostico_embarazo=Fecha_db($fecha_diagnostico_embarazo);
   	$semanas_embarazo=$_POST['semanas_embarazo'];
   	//////////////
   	$clave_beneficiario=$_POST['clave_beneficiario'];
   	$sexo=$_POST['sexo'];
   	$pais_nac=$_POST['pais_nacn'];
   	$localidad_proc=$_POST['localidad_procn'];
    $provincia_nac=$_POST['provincia_nacn'];
    $indigena=$_POST['indigena'];
    $id_tribu=$_POST['id_tribu'];
    $id_lengua= $_POST['id_lengua'];
    $departamento=$_POST['departamenton'];
   	$localidad=$_POST['localidad'];
   	$municipio=$_POST['municipio'];
   	$barrio=$_POST['barrio'];
   	$id_categoria=$_POST['id_categoria'];
	$responsable=$_POST['responsable'];
	$menor_convive_con_adulto=$_POST['menor_convive_con_adulto'];
	$tipo_doc_madre=$_POST['tipo_doc_madre'];
	$nro_doc_madre=$_POST['nro_doc_madre'];
	$apellido_madre=$_POST['apellido_madre'];
	$nombre_madre=$_POST['nombre_madre'];
	
	$fecha_probable_parto=Fecha_db($fecha_probable_parto);
	$fecha_efectiva_parto=Fecha_db($fecha_efectiva_parto);
	$fecha_inscripcion=Fecha_db($fecha_inscripcion);
 
	 if($ape_nom=='s'){
             $ape_nom_update="nombre_benef=upper('$nombre'),nombre_benef_otro=upper('$nombre_otro'),
                 apellido_benef=upper('$apellido'),apellido_benef_otro=upper('$apellido_otro'),";
        }else{
        $ape_nom_update="nombre_benef=upper('$nombre'),
             apellido_benef=upper('$apellido'),";
        }
  if($responsable =='MADRE'){
   			$query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',sexo='$sexo',
             indigena=upper('$indigena'),id_tribu='$id_tribu',id_lengua=$id_lengua,
             id_categoria=$id_categoria,
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamento'), localidad=upper('$localidad'), municipio=upper('$municipio'), 
             barrio=upper('$barrio'),telefono='$telefono',cod_pos='$cod_pos',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             provincia_nac=upper('$provincia_nac'),localidad_nac=upper('$localidad_proc'),pais_nac=upper('$pais_nac'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_madre=upper('$nombre_madre'),
             apellido_madre=upper('$apellido_madre'), nro_doc_madre='$nro_doc_madre', 
             tipo_doc_madre=upper('$tipo_doc_madre'),nombre_padre='',apellido_padre='', 
             nro_doc_padre='',tipo_doc_padre='',nombre_tutor='', apellido_tutor='', 
             nro_doc_tutor='',tipo_doc_tutor='', tipo_transaccion='M'
                       
             where id_beneficiarios=".$id_planilla;
   }elseif($responsable =='PADRE'){
   		$query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',sexo='$sexo',
             indigena=upper('$indigena'),id_tribu='$id_tribu',id_lengua=$id_lengua,
             id_categoria=$id_categoria,
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamento'), localidad=upper('$localidad'), municipio=upper('$municipio'), 
             barrio=upper('$barrio'),telefono='$telefono',cod_pos='$cod_pos',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             provincia_nac=upper('$provincia_nac'),localidad_nac=upper('$localidad_proc'),pais_nac=upper('$pais_nac'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_padre=upper('$nombre_madre'),
             apellido_padre=upper('$apellido_madre'), nro_doc_padre='$nro_doc_madre', 
             tipo_doc_padre=upper('$tipo_doc_madre'),nombre_madre='', 
             apellido_madre='', nro_doc_madre='',tipo_doc_madre='',nombre_tutor='', 
             apellido_tutor='', nro_doc_tutor='',tipo_doc_tutor='', tipo_transaccion='M'                        
              
         where id_beneficiarios=".$id_planilla;
  		 }elseif($responsable =='TUTOR') {
   			$query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',sexo='$sexo',
             indigena=upper('$indigena'),id_tribu='$id_tribu',id_lengua=$id_lengua,
             id_categoria=$id_categoria,
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamento'), localidad=upper('$localidad'), municipio=upper('$municipio'), 
             barrio=upper('$barrio'),telefono='$telefono',cod_pos='$cod_pos',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             provincia_nac=upper('$provincia_nac'),localidad_nac=upper('$localidad_proc'),pais_nac=upper('$pais_nac'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_tutor=upper('$nombre_madre'),
             apellido_tutor=upper('$apellido_madre'), nro_doc_tutor='$nro_doc_madre', 
             tipo_doc_tutor=upper('$tipo_doc_madre'),nombre_madre='', 
             apellido_madre='', nro_doc_madre='',tipo_doc_madre='',nombre_padre='',
             apellido_padre='', nro_doc_padre='',tipo_doc_padre='', tipo_transaccion='M'                              
                       
             where id_beneficiarios=".$id_planilla;
  		 }
  	
  		 if ($id_categoria=='1') {
   			$query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',sexo='$sexo',
             indigena=upper('$indigena'),id_tribu='$id_tribu',id_lengua=$id_lengua,id_categoria=$id_categoria,
			 fecha_diagnostico_embarazo='$fecha_diagnostico_embarazo',semanas_embarazo='$semanas_embarazo',fecha_probable_parto='$fecha_probable_parto',
			 calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',
			 entre_calle_1=upper('$entre_calle_1'),entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamento'), 
			 localidad=upper('$localidad'), municipio=upper('$municipio'), barrio=upper('$barrio'),
			 telefono='$telefono',cod_pos='$cod_pos',observaciones=upper('$observaciones'),
			 fecha_inscripcion='$fecha_inscripcion', provincia_nac=upper('$provincia_nac'), localidad_nac=upper('$localidad_proc'), pais_nac=upper('$pais_nac'), 
			 tipo_ficha='$tipo_ficha', tipo_transaccion='M'
                       
             where id_beneficiarios=".$id_planilla;
  		 }elseif ($id_categoria=='2') {
   			$query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',sexo='$sexo',
             indigena=upper('$indigena'),id_tribu='$id_tribu',id_lengua=$id_lengua,id_categoria=$id_categoria,
			 fecha_efectiva_parto='$fecha_efectiva_parto',
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',
			 entre_calle_1=upper('$entre_calle_1'),entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamento'), 
			 localidad=upper('$localidad'), municipio=upper('$municipio'), barrio=upper('$barrio'),
			 telefono='$telefono',cod_pos='$cod_pos',observaciones=upper('$observaciones'),
			 fecha_inscripcion='$fecha_inscripcion', provincia_nac=upper('$provincia_nac'), localidad_nac=upper('$localidad_proc'), pais_nac=upper('$pais_nac'), 
			 tipo_ficha='$tipo_ficha', tipo_transaccion='M'
                       
             where id_beneficiarios=".$id_planilla;
  		 }elseif ($id_categoria=='7') {
   			$query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',sexo='$sexo',
             indigena=upper('$indigena'),id_tribu='$id_tribu',id_lengua=$id_lengua,id_categoria=$id_categoria,
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',
			 entre_calle_1=upper('$entre_calle_1'),entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamento'),
			 localidad=upper('$localidad'), municipio=upper('$municipio'), barrio=upper('$barrio'),
			 telefono='$telefono',cod_pos='$cod_pos',observaciones=upper('$observaciones'),
			 fecha_inscripcion='$fecha_inscripcion', provincia_nac=upper('$provincia_nac'), localidad_nac=upper('$localidad_proc'), pais_nac=upper('$pais_nac'),
			 tipo_ficha='$tipo_ficha', tipo_transaccion='M'

             where id_beneficiarios=".$id_planilla;
  		 }
   
if(($responsable =='MADRE') && ($estado_envio== 'e')){
   			$query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',sexo='$sexo',
             indigena=upper('$indigena'),id_tribu='$id_tribu',id_lengua=$id_lengua,
             id_categoria=$id_categoria,
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamento'), localidad=upper('$localidad'), municipio=upper('$municipio'), 
             barrio=upper('$barrio'),telefono='$telefono',cod_pos='$cod_pos',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             provincia_nac=upper('$provincia_nac'),localidad_nac=upper('$localidad_proc'),pais_nac=upper('$pais_nac'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_madre=upper('$nombre_madre'),
             apellido_madre=upper('$apellido_madre'), nro_doc_madre='$nro_doc_madre', 
             tipo_doc_madre=upper('$tipo_doc_madre'),nombre_padre='',apellido_padre='', 
             nro_doc_padre='',tipo_doc_padre='',nombre_tutor='', apellido_tutor='', 
             nro_doc_tutor='',tipo_doc_tutor='', tipo_transaccion='M'
                       
             where id_beneficiarios=".$id_planilla;
   }elseif(($responsable =='PADRE') && ($estado_envio== 'e')){
   		$query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',sexo='$sexo',
             indigena=upper('$indigena'),id_tribu='$id_tribu',id_lengua=$id_lengua,
             id_categoria=$id_categoria,
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamento'), localidad=upper('$localidad'), municipio=upper('$municipio'), 
             barrio=upper('$barrio'),telefono='$telefono',cod_pos='$cod_pos',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             provincia_nac=upper('$provincia_nac'),localidad_nac=upper('$localidad_proc'),pais_nac=upper('$pais_nac'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_padre=upper('$nombre_madre'),
             apellido_padre=upper('$apellido_madre'), nro_doc_padre='$nro_doc_madre', 
             tipo_doc_padre=upper('$tipo_doc_madre'),nombre_madre='', 
             apellido_madre='', nro_doc_madre='',tipo_doc_madre='',nombre_tutor='', 
             apellido_tutor='', nro_doc_tutor='',tipo_doc_tutor='', tipo_transaccion='M'                       
              
         where id_beneficiarios=".$id_planilla;
  		 }elseif(($responsable =='TUTOR') && ($estado_envio== 'e')) {
   			$query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',sexo='$sexo',
             indigena=upper('$indigena'),id_tribu='$id_tribu',id_lengua=$id_lengua,
             id_categoria=$id_categoria,
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamento'), localidad=upper('$localidad'), municipio=upper('$municipio'), 
             barrio=upper('$barrio'),telefono='$telefono',cod_pos='$cod_pos',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             provincia_nac=upper('$provincia_nac'),localidad_nac=upper('$localidad_proc'),pais_nac=upper('$pais_nac'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_tutor=upper('$nombre_madre'),
             apellido_tutor=upper('$apellido_madre'), nro_doc_tutor='$nro_doc_madre', 
             tipo_doc_tutor=upper('$tipo_doc_madre'),nombre_madre='', 
             apellido_madre='', nro_doc_madre='',tipo_doc_madre='',nombre_padre='', 
             apellido_padre='', nro_doc_padre='',tipo_doc_padre='', tipo_transaccion='M'                            
                       
             where id_beneficiarios=".$id_planilla;
  		 }
  		
  		 if (($estado_envio=='e') && ($id_categoria=='1')) {
   			$query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',sexo='$sexo',
             indigena=upper('$indigena'),id_tribu='$id_tribu',id_lengua=$id_lengua,id_categoria=$id_categoria,
			 fecha_diagnostico_embarazo='$fecha_diagnostico_embarazo',semanas_embarazo='$semanas_embarazo',fecha_probable_parto='$fecha_probable_parto',
			 calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',
			 entre_calle_1=upper('$entre_calle_1'),entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamento'), 
			 localidad=upper('$localidad'), municipio=upper('$municipio'), barrio=upper('$barrio'),
			 telefono='$telefono',cod_pos='$cod_pos',observaciones=upper('$observaciones'),
			 fecha_inscripcion='$fecha_inscripcion', provincia_nac=upper('$provincia_nac'), localidad_nac=upper('$localidad_proc'), pais_nac=upper('$pais_nac'), 
			 tipo_ficha='$tipo_ficha', tipo_transaccion='M', estado_envio='n'
                       
             where id_beneficiarios=".$id_planilla;
  		 }elseif (($estado_envio=='e') && ($id_categoria=='2')) {
   			$query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',sexo='$sexo',
             indigena=upper('$indigena'),id_tribu='$id_tribu',id_lengua=$id_lengua,id_categoria=$id_categoria,
			 fecha_efectiva_parto='$fecha_efectiva_parto',
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',
			 entre_calle_1=upper('$entre_calle_1'),entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamento'), 
			 localidad=upper('$localidad'), municipio=upper('$municipio'), barrio=upper('$barrio'),
			 telefono='$telefono',cod_pos='$cod_pos',observaciones=upper('$observaciones'),
			 fecha_inscripcion='$fecha_inscripcion', provincia_nac=upper('$provincia_nac'), localidad_nac=upper('$localidad_proc'), pais_nac=upper('$pais_nac'), 
			 tipo_ficha='$tipo_ficha', tipo_transaccion='M'
                       
             where id_beneficiarios=".$id_planilla;
  		 }elseif (($estado_envio=='e') && ($id_categoria=='7')) {
   			$query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',sexo='$sexo',
             indigena=upper('$indigena'),id_tribu='$id_tribu',id_lengua=$id_lengua,id_categoria=$id_categoria,
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',
			 entre_calle_1=upper('$entre_calle_1'),entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamento'),
			 localidad=upper('$localidad'), municipio=upper('$municipio'), barrio=upper('$barrio'),
			 telefono='$telefono',cod_pos='$cod_pos',observaciones=upper('$observaciones'),
			 fecha_inscripcion='$fecha_inscripcion', provincia_nac=upper('$provincia_nac'), localidad_nac=upper('$localidad_proc'), pais_nac=upper('$pais_nac'),
			 tipo_ficha='$tipo_ficha', tipo_transaccion='M'

             where id_beneficiarios=".$id_planilla;
  		 }

   sql($query, "Error al insertar/actualizar el muleto") or fin_pagina();   
	 
   $db->CompleteTrans();    
   $accion="Los datos se actualizaron";

   
}

if ($_POST['guardar']=="Guardar Planilla"){
	
    $fecha_carga= date("Y-m-d HH:mm:ss");
    $usuario=$_ses_user['id'];   
    
    $fecha_nac=Fecha_db($fecha_nac);
    $fecha_diagnostico_embarazo=Fecha_db($fecha_diagnostico_embarazo);
 
    $fecha_probable_parto=Fecha_db($fecha_probable_parto);
    $fecha_efectiva_parto=Fecha_db($fecha_efectiva_parto);
    $fecha_inscripcion=Fecha_db($fecha_inscripcion);
	
    $db->StartTrans();      

    $sql_parametros="select * from uad.parametros";
    if($uad_benef=='s'){
        $sql_parametros.=" a
                    inner join uad.uad_x_usuario b on a.codigo_uad=b.cod_uad
                    where id_usuario=".$_ses_user['id'];
    }
    $result_parametros=sql($sql_parametros) or fin_pagina();
    $codigo_provincia=$result_parametros->fields['codigo_provincia'];
    $codigo_ci=$result_parametros->fields['codigo_ci'];   
    
    $q="select nextval('uad.beneficiarios_id_beneficiarios_seq') as id_planilla";
    $id_planilla=sql($q) or fin_pagina();

    $id_planilla=$id_planilla->fields['id_planilla'];
   
    $id_planilla_clave= str_pad($id_planilla, 7-strlen($id_planilla), '0', STR_PAD_LEFT);
    
    $clave_beneficiario=$codigo_provincia.'001'.$codigo_ci.$id_planilla_clave;
    //echo $clave_beneficiario;
	 
    $usuario = substr($usuario,0,9);
    
    $responsable=$_POST['responsable'];
    $departamento=$_POST['departamenton'];
   $sql="Select puco.documento from puco.puco where puco.documento = '$num_doc'";
   $res_extra=sql($sql, "Error al traer el beneficiario") or fin_pagina();
		
    if (($res_extra->recordcount()>0) && ($responsable=='MADRE')){
      if($ape_nom=='s'){
        $query="insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,
             cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_madre,
             nro_doc_madre,apellido_madre,nombre_madre,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$apellido_otro'),upper('$nombre'),upper('$nombre_otro'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_procn'),upper('$pais_nacn'),
             upper('$indigena'),'$id_tribu',$id_lengua,'1899-12-30',null,
             '1899-12-30','1899-12-30',
             upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),
             upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamento'),upper('$localidad'),upper('$municipio'),upper('$barrio'),
             '$cod_pos',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','1', upper('$responsable'))";
      }else{
        $query="insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,nombre_benef,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,
             cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_madre,
             nro_doc_madre,apellido_madre,nombre_madre,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$nombre'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_procn'),upper('$pais_nacn'),
             upper('$indigena'),'$id_tribu',$id_lengua,'1899-12-30',null,
             '1899-12-30','1899-12-30',
             upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),
             upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamento'),upper('$localidad'),upper('$municipio'),upper('$barrio'),
             '$cod_pos',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','1', upper('$responsable'))";
      }

    	sql($query, "Error al insertar la Planilla") or fin_pagina();
    
    	$accion="Se guardo la Planilla - El inscripto esta en el PUCO";

    	$db->CompleteTrans();
    	   
   }elseif (($res_extra->recordcount()== 0) && ($responsable=='MADRE'))  {
       if($ape_nom=='s'){
        $query="insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,
             cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_madre,
             nro_doc_madre,apellido_madre,nombre_madre,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$apellido_otro'),upper('$nombre'),upper('$nombre_otro'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_procn'),upper('$pais_nacn'),
             upper('$indigena'),'$id_tribu',$id_lengua,'1899-12-30',null,
             '1899-12-30','1899-12-30',
             upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),
             upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamento'),upper('$localidad'),upper('$municipio'),upper('$barrio'),
             '$cod_pos',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','1', upper('$responsable'))";
      }else{
   		$query="insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,nombre_benef,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,
             cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_madre,
             nro_doc_madre,apellido_madre,nombre_madre,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$nombre'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_procn'),upper('$pais_nacn'),
             upper('$indigena'),'$id_tribu',$id_lengua,'1899-12-30',null,
             '1899-12-30','1899-12-30',
             upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),
             upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamento'),upper('$localidad'),upper('$municipio'),upper('$barrio'),
             '$cod_pos',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','1', upper('$responsable'))";
      }

    	sql($query, "Error al insertar la Planilla") or fin_pagina();
    
    	$accion="Se guardo la Planilla";       
	 
    	$db->CompleteTrans();
   }
   if (($res_extra->recordcount()>0) && ($responsable=='PADRE')) {
   	if($ape_nom=='s'){
        $query="insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,
             cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_padre,
             nro_doc_padre,apellido_padre,nombre_padre,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$apellido_otro'),upper('$nombre'),upper('$nombre_otro'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_procn'),upper('$pais_nacn'),
             upper('$indigena'),'$id_tribu',$id_lengua,'1899-12-30',null,
             '1899-12-30','1899-12-30',
             upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),
             upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamento'),upper('$localidad'),upper('$municipio'),upper('$barrio'),
             '$cod_pos',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','1', upper('$responsable'))";
      }else{
   				$query="insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,nombre_benef,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,
             cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_padre,
             nro_doc_padre,apellido_padre,nombre_padre,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$nombre'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_procn'),upper('$pais_nacn'),
             upper('$indigena'),'$id_tribu',$id_lengua,'1899-12-30',null,
             '1899-12-30','1899-12-30',
             upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),
             upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamento'),upper('$localidad'),upper('$municipio'),upper('$barrio'),
             '$cod_pos',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','1', upper('$responsable'))";
      }

    	sql($query, "Error al insertar la Planilla") or fin_pagina();
    
    	$accion="Se guardo la Planilla - El inscripto esta en el PUCO";       
	 
    	$db->CompleteTrans();			
   
   		}elseif (($res_extra->recordcount()== 0) && ($responsable=='PADRE')) {

                if($ape_nom=='s'){
                   $query="insert into uad.beneficiarios
                     (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,
                     tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
                     indigena,id_tribu,id_lengua,fecha_diagnostico_embarazo,semanas_embarazo,
                     fecha_probable_parto,fecha_efectiva_parto,
                     cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_padre,
                     nro_doc_padre,apellido_padre,nombre_padre,calle,numero_calle,
                     piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
                                 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable)
                     values
                     ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$apellido_otro'),upper('$nombre'),upper('$nombre_otro'),upper('$clase_doc'),
                     upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_procn'),upper('$pais_nacn'),
                     upper('$indigena'),'$id_tribu',$id_lengua,'1899-12-30',null,
                     '1899-12-30','1899-12-30',
                     upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
                     upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),
                     upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
                     upper('$entre_calle_2'),'$telefono',upper('$departamento'),upper('$localidad'),upper('$municipio'),upper('$barrio'),
                     '$cod_pos',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','1', upper('$responsable'))";
                  }else{
                        $query="insert into uad.beneficiarios
                     (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,nombre_benef,clase_documento_benef,
                     tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
                     indigena,id_tribu,id_lengua,fecha_diagnostico_embarazo,semanas_embarazo,
                     fecha_probable_parto,fecha_efectiva_parto,
                     cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_padre,
                     nro_doc_padre,apellido_padre,nombre_padre,calle,numero_calle,
                     piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
                                 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable)
                     values
                     ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$nombre'),upper('$clase_doc'),
                     upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_procn'),upper('$pais_nacn'),
                     upper('$indigena'),'$id_tribu',$id_lengua,'1899-12-30',null,
                     '1899-12-30','1899-12-30',
                     upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
                     upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),
                     upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
                     upper('$entre_calle_2'),'$telefono',upper('$departamento'),upper('$localidad'),upper('$municipio'),upper('$barrio'),
                     '$cod_pos',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','1', upper('$responsable'))";
                  }

    	sql($query, "Error al insertar la Planilla") or fin_pagina();
    
    	$accion="Se guardo la Planilla";       
	$num_doc='';
        $apellido='';
        $nombre='';
        $nombre_otro='';
        $apellido_otro='';
        $fecha_nac='';
        $calle='';
        $numero_calle='';
        $piso='';
        $dpto='';
        $manzana='';
        $entre_calle_1='';
        $entre_calle_2='';
        $cod_pos='';
        $fecha_inscripcion='';
        $observaciones='';
        $pais_nac='';
        $provincia_nac='';
        $localidad_proc='';
        $departamento='';
        $id_categoria='';
        $indigena='';
        $id_tribu='';
        $id_lengua='';
    	$db->CompleteTrans();
    }
if (($res_extra->recordcount()>0) && ($responsable=='TUTOR')) {
     if($ape_nom=='s'){
            $query="insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,
             cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_tutor,
             nro_doc_tutor,apellido_tutor,nombre_tutor,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
	     fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$apellido_otro'),upper('$nombre'),upper('$nombre_otro'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_procn'),upper('$pais_nacn'),
             upper('$indigena'),'$id_tribu',$id_lengua,'1899-12-30',null,
             '1899-12-30','1899-12-30',
             upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),
             upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamento'),upper('$localidad'),upper('$municipio'),upper('$barrio'),
             '$cod_pos',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','1', upper('$responsable'))";
        }else{
   	$query="insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,nombre_benef,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,
             cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_tutor,
             nro_doc_tutor,apellido_tutor,nombre_tutor,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
	     fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$nombre'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_procn'),upper('$pais_nacn'),
             upper('$indigena'),'$id_tribu',$id_lengua,'1899-12-30',null,
             '1899-12-30','1899-12-30',
             upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),
             upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamento'),upper('$localidad'),upper('$municipio'),upper('$barrio'),
             '$cod_pos',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','1', upper('$responsable'))";
        }

    	sql($query, "Error al insertar la Planilla") or fin_pagina();
    
    	$accion="Se guardo la Planilla - El inscripto esta en el PUCO";       
	 
    	$db->CompleteTrans();
   } elseif (($res_extra->recordcount()== 0) && ($responsable=='TUTOR')) {
       if($ape_nom=='s'){
            $query="insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,
             cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_tutor,
             nro_doc_tutor,apellido_tutor,nombre_tutor,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
	     fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$apellido_otro'),upper('$nombre'),upper('$nombre_otro'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_procn'),upper('$pais_nacn'),
             upper('$indigena'),'$id_tribu',$id_lengua,'1899-12-30',null,
             '1899-12-30','1899-12-30',
             upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),
             upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamento'),upper('$localidad'),upper('$municipio'),upper('$barrio'),
             '$cod_pos',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','1', upper('$responsable'))";
        }else{
   	$query="insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,nombre_benef,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,
             cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_tutor,
             nro_doc_tutor,apellido_tutor,nombre_tutor,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
	     fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$nombre'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_procn'),upper('$pais_nacn'),
             upper('$indigena'),'$id_tribu',$id_lengua,'1899-12-30',null,
             '1899-12-30','1899-12-30',
             upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),
             upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamento'),upper('$localidad'),upper('$municipio'),upper('$barrio'),
             '$cod_pos',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','1', upper('$responsable'))";
        }

    	sql($query, "Error al insertar la Planilla") or fin_pagina();
    
    	$accion="Se guardo la Planilla";       
	 
    	$db->CompleteTrans();
   } 
   if (($res_extra->recordcount()>0) && ($id_categoria == '1')){
       if($ape_nom=='s'){
            $query="insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,cuie_ea,cuie_ah,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$apellido_otro'),upper('$nombre'),upper('$nombre_otro'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_procn'),upper('$pais_nacn'),
             upper('$indigena'),'$id_tribu',$id_lengua,'$fecha_diagnostico_embarazo','$semanas_embarazo',
             '$fecha_probable_parto','1899-12-30',upper('$cuie'),upper('$cuie'),upper('$calle'),'$numero_calle',
             '$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),upper('$entre_calle_2'),'$telefono',upper('$departamento'),
             upper('$localidad'),upper('$municipio'),upper('$barrio'),'$cod_pos',upper('$observaciones'), '$fecha_inscripcion',
             '$fecha_carga',upper('$usuario'),'1','1')";
        }else{
      	$query="insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,nombre_benef,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,cuie_ea,cuie_ah,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$nombre'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_procn'),upper('$pais_nacn'),
             upper('$indigena'),'$id_tribu',$id_lengua,'$fecha_diagnostico_embarazo','$semanas_embarazo',
             '$fecha_probable_parto','1899-12-30',upper('$cuie'),upper('$cuie'),upper('$calle'),'$numero_calle',
             '$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),upper('$entre_calle_2'),'$telefono',upper('$departamento'),
             upper('$localidad'),upper('$municipio'),upper('$barrio'),'$cod_pos',upper('$observaciones'), '$fecha_inscripcion',
             '$fecha_carga',upper('$usuario'),'1','1')";
        }
    	sql($query, "Error al insertar la Planilla") or fin_pagina();
    
    	$accion="Se guardo la Planilla - El inscripto esta en el PUCO";       
	 
    	$db->CompleteTrans(); 
    }   elseif (($res_extra->recordcount()== 0) && ($id_categoria == '1')) {
        if($ape_nom=='s'){
            $query="insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,cuie_ea,cuie_ah,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$apellido_otro'),upper('$nombre'),upper('$nombre_otro'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_procn'),upper('$pais_nacn'),
             upper('$indigena'),'$id_tribu',$id_lengua,'$fecha_diagnostico_embarazo','$semanas_embarazo',
             '$fecha_probable_parto','1899-12-30',upper('$cuie'),upper('$cuie'),upper('$calle'),'$numero_calle',
             '$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),upper('$entre_calle_2'),'$telefono',upper('$departamento'),
             upper('$localidad'),upper('$municipio'),upper('$barrio'),'$cod_pos',upper('$observaciones'), '$fecha_inscripcion',
             '$fecha_carga',upper('$usuario'),'1','1')";
        }else{
    	$query="insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,nombre_benef,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,cuie_ea,cuie_ah,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$nombre'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_procn'),upper('$pais_nacn'),
             upper('$indigena'),'$id_tribu',$id_lengua,'$fecha_diagnostico_embarazo','$semanas_embarazo',
             '$fecha_probable_parto','1899-12-30',upper('$cuie'),upper('$cuie'),upper('$calle'),'$numero_calle',
             '$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),upper('$entre_calle_2'),'$telefono',upper('$departamento'),
             upper('$localidad'),upper('$municipio'),upper('$barrio'),'$cod_pos',upper('$observaciones'), '$fecha_inscripcion',
             '$fecha_carga',upper('$usuario'),'1','1')";
        }
    	sql($query, "Error al insertar la Planilla") or fin_pagina();
    
    	$accion="Se guardo la Planilla";       
	 
    	$db->CompleteTrans(); 
    }
   if (($res_extra->recordcount()>0) && ($id_categoria == '2')) {
       if($ape_nom=='s'){
            $query="insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,cuie_ea,cuie_ah,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$apellido_otro'),upper('$nombre'),upper('$nombre_otro'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_procn'),upper('$pais_nacn'),
             upper('$indigena'),'$id_tribu',$id_lengua,'1899-12-30',null,
             '1899-12-30','$fecha_efectiva_parto',upper('$cuie'),upper('$cuie'),upper('$calle'),'$numero_calle',
             '$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),upper('$entre_calle_2'),'$telefono',upper('$departamento'),
             upper('$localidad'),upper('$municipio'),upper('$barrio'),'$cod_pos',upper('$observaciones'), '$fecha_inscripcion',
             '$fecha_carga',upper('$usuario'),'1','1')";
        }else{
        	$query="insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,nombre_benef,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,cuie_ea,cuie_ah,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$nombre'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_procn'),upper('$pais_nacn'),
             upper('$indigena'),'$id_tribu',$id_lengua,'1899-12-30',null,
             '1899-12-30','$fecha_efectiva_parto',upper('$cuie'),upper('$cuie'),upper('$calle'),'$numero_calle',
             '$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),upper('$entre_calle_2'),'$telefono',upper('$departamento'),
             upper('$localidad'),upper('$municipio'),upper('$barrio'),'$cod_pos',upper('$observaciones'), '$fecha_inscripcion',
             '$fecha_carga',upper('$usuario'),'1','1')";
        }
    	sql($query, "Error al insertar la Planilla") or fin_pagina();
    
    	$accion="Se guardo la Planilla - El inscripto esta en el PUCO";       
	 
    	$db->CompleteTrans(); 
    }elseif (($res_extra->recordcount()== 0) && ($id_categoria == '2')) {
    	if($ape_nom=='s'){
            $query="insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,cuie_ea,cuie_ah,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$apellido_otro'),upper('$nombre'),upper('$nombre_otro'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_procn'),upper('$pais_nacn'),
             upper('$indigena'),'$id_tribu',$id_lengua,'1899-12-30',null,
             '1899-12-30','$fecha_efectiva_parto',upper('$cuie'),upper('$cuie'),upper('$calle'),'$numero_calle',
             '$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),upper('$entre_calle_2'),'$telefono',upper('$departamento'),
             upper('$localidad'),upper('$municipio'),upper('$barrio'),'$cod_pos',upper('$observaciones'), '$fecha_inscripcion',
             '$fecha_carga',upper('$usuario'),'1','1')";
        }else{
    	$query="insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,nombre_benef,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,cuie_ea,cuie_ah,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$nombre'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_procn'),upper('$pais_nacn'),
             upper('$indigena'),'$id_tribu',$id_lengua,'1899-12-30',null,
             '1899-12-30','$fecha_efectiva_parto',upper('$cuie'),upper('$cuie'),upper('$calle'),'$numero_calle',
             '$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),upper('$entre_calle_2'),'$telefono',upper('$departamento'),
             upper('$localidad'),upper('$municipio'),upper('$barrio'),'$cod_pos',upper('$observaciones'), '$fecha_inscripcion',
             '$fecha_carga',upper('$usuario'),'1','1')";
        }
    	sql($query, "Error al insertar la Planilla") or fin_pagina();
    
    	$accion="Se guardo la Planilla";       
	 
    	$db->CompleteTrans(); 
    	
    }elseif($id_categoria == '7'){
         $query="insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,cuie_ea,cuie_ah,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$apellido_otro'),upper('$nombre'),upper('$nombre_otro'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_procn'),upper('$pais_nacn'),
             upper('$indigena'),'$id_tribu',$id_lengua,'1899-12-30',null,
             '1899-12-30',null,upper('$cuie'),upper('$cuie'),upper('$calle'),'$numero_calle',
             '$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),upper('$entre_calle_2'),'$telefono',upper('$departamento'),
             upper('$localidad'),upper('$municipio'),upper('$barrio'),'$cod_pos',upper('$observaciones'), '$fecha_inscripcion',
             '$fecha_carga',upper('$usuario'),'1','1')";
         sql($query, "Error al insertar la Planilla") or fin_pagina();

    	$accion="Se guardo la Planilla";

    	$db->CompleteTrans(); 
    }
   
      
       
} ///de if ($_POST['guardar']=="Guardar nuevo Muleto")

if ($_POST['borrar']=="Borrar"){
	 echo "<SCRIPT Language='Javascript'> alert(".$estado_envio.");</SCRIPT>";
	if ($estado_envio == 'n' || $estado_envio == 'p'){
	$query="UPDATE uad.beneficiarios  SET activo='0', tipo_transaccion= 'B'  WHERE (id_beneficiarios= $id_planilla)";
	sql($query, "Error al insertar la Planilla") or fin_pagina();
	$accion="Se elimino la planilla $id_planilla de Ni�os";
	}
	if ($estado_envio == 'e'){
		$query="UPDATE uad.beneficiarios  SET activo='0', tipo_transaccion= 'B', estado_envio='n'  WHERE (id_beneficiarios= $id_planilla)";
	sql($query, "Error al insertar la Planilla") or fin_pagina();
	$accion="Se elimino la planilla $id_planilla de Ni�os";
	} 	
}


if ($_POST['b']=="b" ){
	if ($num_doc!=''){
			/*MISIONES*/
		 $sql="select * from uad.beneficiarios
				where numero_doc='$num_doc' and tipo_documento='$tipo_doc' and clase_documento_benef='$clase_doc'";
				$res_extra=sql($sql, "Error al traer el beneficiario") or fin_pagina();
			if ($res_extra->recordcount()>0){
					//$accion="El Beneficiario ya esta Empadronado en el Modulo de Inscripcion POR FAVOR VERIFIQUE";
								$accion="El Beneficiario ya esta Empadronado en el Modulo de Inscripcion";
								$id_planilla=$res_extra->fields['id_beneficiarios'];
								$tipo_transaccion=$res_extra->fields['tipo_transaccion'];
								$tipo_ficha=$res_extra->fields['tipo_ficha'];
			}
			else {
					$clase_doc2=str_replace('R','P',$clase_doc);
					$clase_doc2=str_replace('M','A',$clase_doc2);
							$sql="select * from nacer.smiafiliados
							 where afidni='$num_doc' and afitipodoc='$tipo_doc' and aficlasedoc='$clase_doc2'";
							$res_extra=sql($sql, "Error al traer el beneficiario") or fin_pagina();
		
							if ($res_extra->recordcount()>0){
								//$accion="El Beneficiario ya esta Empadronado POR FAVOR VERIFIQUE";
								//$accion="El Beneficiario ya esta Empadronado.";
								//$q="select nextval('uad.beneficiarios_id_beneficiarios_seq') as id_planilla";
								  //$id_planilla=sql($q) or fin_pagina();
								//	$id_planilla=$id_planilla->fields['id_planilla'];
									$tipo_transaccion='M';
									$fecha_carga= date("Y-m-d HH:mm:ss");
									$usuario=$_ses_user['id'];
									$usuario = substr($usuario,0,9);
									$tipo_ficha='1';
								$sql2="insert into uad.beneficiarios
									(id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef      ,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,tipo_documento,numero_doc,id_categoria    ,sexo   ,fecha_nacimiento_benef,nro_doc_madre,apellido_madre,nombre_madre,fecha_diagnostico_embarazo,cuie_ea,cuie_ah                                    ,departamento      ,localidad,fecha_inscripcion,activo,fecha_carga,usuario_carga)
									select nextval('uad.beneficiarios_id_beneficiarios_seq')    ,'p'         ,clavebeneficiario ,'M'             ,case when position(' ' in afiapellido)=0 then afiapellido
																												else substring(afiapellido from 1 for (position(' ' in afiapellido)-1)) end
																												,case when position(' ' in afiapellido)=0 then ''
																												else substring(afiapellido from (position(' ' in afiapellido)+1) for char_length(afiapellido)) end
													,case when position(' ' in afinombre)=0 then afinombre
														else substring(afinombre from 1 for (position(' ' in afinombre)-1)) end, case when position(' ' in afinombre)=0 then ''
																																else substring(afinombre from (position(' ' in afinombre)+1) for char_length(afinombre)) end
										,'$clase_doc'          ,afitipodoc    ,afidni    ,afitipocategoria,afisexo,afifechanac           ,manrodocumento,maapellido,manombre      ,fechadiagnosticoembarazo   ,cuielugaratencionhabitual,cuielugaratencionhabitual,afidomdepartamento,afidomlocalidad,fechainscripcion,1,'$fecha_carga','$usuario'
									 from nacer.smiafiliados
								  where afidni='$num_doc' and afitipodoc='$tipo_doc' and aficlasedoc='$clase_doc2'
								   RETURNING id_beneficiarios";
								$res_extras=sql($sql2, "Error al i el beneficiario") or fin_pagina();
								$id_planilla=$res_extras->fields['id_beneficiarios'];
							}
							else {
											$accion2="Beneficiario no Encontrado";
											$tapa_ver='block';
								 }
				}
			 if($accion2!="Beneficiario no Encontrado"){
				$ref = encode_link("ins_admin_old.php",array("tapa_ver"=>'block',"id_planilla"=>$id_planilla,"tipo_transaccion"=>$tipo_transaccion,"tipo_ficha"=>$tipo_ficha));
				echo "<SCRIPT Language='Javascript'> location.href='$ref'</SCRIPT>";
   			  }
	/*$sql="select * from nacer.smiafiliados
	 where afidni='$num_doc'";
	$res_extra=sql($sql, "Error al traer el beneficiario") or fin_pagina();
	
	if ($res_extra->recordcount()>0){
		$accion="El Beneficiario ya esta Empadronado POR FAVOR VERIFIQUE";
	}
	else {
		$sql="select * from uad.beneficiarios	  
	 	where numero_doc='$num_doc'";
		$res_extra=sql($sql, "Error al traer el beneficiario") or fin_pagina();
		if ($res_extra->recordcount()>0){
			$accion="El Beneficiario ya esta Empadronado en el Modulo de Inscripcion POR FAVOR VERIFIQUE";
		}
		else {
			$accion2="Beneficiario no Encontrado";
		}
	}*/
	}else{ echo "<SCRIPT Language='Javascript'> alert('Debe Cargar el N� de Documento'); </SCRIPT>";}
}
//comienza agregado por sistemas Misiones- SS
if ($_POST['guardar']=="Pasar a No Enviados"){
  $db->StartTrans();
    $fecha_carga= date("Y-m-d HH:mm:ss");
    $usuario=$_ses_user['id'];
    $usuario = substr($usuario,0,9);
	$query="update uad.beneficiarios set estado_envio='n',fecha_verificado='$fecha_carga',usuario_verificado='$usuario'
                where id_beneficiarios=$id_planilla";

    	sql($query, "Error al insertar la Planilla") or fin_pagina();
		$estado_envio='n';
    	$accion="Se guardo la Planilla en estado No Enviados";

    	$db->CompleteTrans();

//termina agregado por sistemas Misiones -SS
} 
if ($id_planilla) {

$query="SELECT beneficiarios.*, smiefectores.nombreefector, smiefectores.cuie
			FROM uad.beneficiarios
			left join facturacion.smiefectores on beneficiarios.cuie_ea=smiefectores.cuie 
  where id_beneficiarios=$id_planilla";

$res_factura=sql($query, "Error al traer el Comprobantes") or fin_pagina();

$es_padre=$res_factura->fields['apellido_padre'];
$es_madre=$res_factura->fields['apellido_madre'];
$es_tutor=$res_factura->fields['apellido_tutor'];
$tipo_ficha=$res_factura->fields['tipo_ficha'];
$usuario_carga=$res_factura->fields['usuario_carga'];
if($tipo_transaccion==''){
    $tipo_transaccion=$res_factura->fields['tipo_transaccion'];
}
if($es_padre != null){
	$responsable="PADRE";
	$tipo_doc_madre=$res_factura->fields['tipo_doc_padre'];
    $nro_doc_madre=$res_factura->fields['nro_doc_padre'];
    $apellido_madre=$res_factura->fields['apellido_padre']; 
    $nombre_madre=$res_factura->fields['nombre_padre'];
    $menor_convive_con_adulto=$res_factura->fields['menor_convive_con_adulto'];
    
	}
	elseif ($es_madre != null){
		$responsable="MADRE";
		$tipo_doc_madre=$res_factura->fields['tipo_doc_madre'];
    	$nro_doc_madre=$res_factura->fields['nro_doc_madre'];
    	$apellido_madre=$res_factura->fields['apellido_madre']; 
    	$nombre_madre=$res_factura->fields['nombre_madre'];
    	$menor_convive_con_adulto=$res_factura->fields['menor_convive_con_adulto'];
	}
	elseif ($es_tutor != null) {
		$responsable="TUTOR";
		$tipo_doc_madre=$res_factura->fields['tipo_doc_tutor'];
    	$nro_doc_madre=$res_factura->fields['nro_doc_tutor'];
    	$apellido_madre=$res_factura->fields['apellido_tutor']; 
    	$nombre_madre=$res_factura->fields['nombre_tutor'];
   	 	$menor_convive_con_adulto=$res_factura->fields['menor_convive_con_adulto'];
	}


if($num_doc==''){
    $num_doc=$res_factura->fields['numero_doc'];
}
if($apellido==''){
    $apellido= $res_factura->fields['apellido_benef'];
}
if($nombre==''){
    $nombre=$res_factura->fields['nombre_benef'];
}
if($nombre_otro==''){
    $nombre_otro=$res_factura->fields['nombre_benef_otro'];
}
if($apellido_otro==''){
    $apellido_otro= $res_factura->fields['apellido_benef_otro'];
}
$fecha_nac=fecha($fecha_nac);
if($fecha_nac=='' || $fecha_nac==date("d/m/Y")){
    $fecha_nac=fecha($res_factura->fields['fecha_nacimiento_benef']); 
}


$fecha_diagnostico_embarazo=fecha($res_factura->fields['fecha_diagnostico_embarazo']);


$fecha_probable_parto=fecha($res_factura->fields['fecha_probable_parto']);


$fecha_efectiva_parto=fecha($res_factura->fields['fecha_efectiva_parto']);
if($calle==''){
    $calle=$res_factura->fields['calle'];
}
if($numero_calle==''){
    $numero_calle=$res_factura->fields['numero_calle'];
}
if($piso==''){
    $piso=$res_factura->fields['piso'];
}
if($dpto==''){
    $dpto=$res_factura->fields['dpto'];
}
if($manzana==''){
    $manzana=$res_factura->fields['manzana'];
}
if($entre_calle_1==''){
    $entre_calle_1=$res_factura->fields['entre_calle_1'];
}
if($entre_calle_2==''){
    $entre_calle_2=$res_factura->fields['entre_calle_2'];
}
if($telefono==''){
    $telefono=$res_factura->fields['telefono'];
}
if($cod_pos==''){
    $cod_pos=$res_factura->fields['cod_pos'];
}
$fecha_inscripcion=fecha($fecha_inscripcion);
if($fecha_inscripcion=='' || $fecha_inscripcion==date("d/m/Y")){
    $fecha_inscripcion=fecha($res_factura->fields['fecha_inscripcion']);
}
if($observaciones==''){
    $observaciones=$res_factura->fields['observaciones'];
}
$cuie=$res_factura->fields['cuie'];
if($pais_nac==''){
    $pais_nac=$res_factura->fields['pais_nac'];
}
if($provincia_nac==''){
    $provincia_nac=$res_factura->fields['provincia_nac'];
}
if($localidad_proc==''){
    $localidad_proc=$res_factura->fields['localidad_nac'];
}
if($departamento==''){
    $departamento=$res_factura->fields['departamento'];
}
if($id_categoria==''){
    $id_categoria=$res_factura->fields['id_categoria'];
}
if($indigena==''){
    $indigena=$res_factura->fields['indigena'];
}
if($id_tribu==''){
    $id_tribu=$res_factura->fields['id_tribu'];
}
if($id_lengua==''){
    $id_lengua=$res_factura->fields['id_lengua'];
}
$responsable=$res_factura->fields['responsable'];
$estado_envio=$res_factura->fields['estado_envio'];
}


// Query que muestra la informacion guardada del Beneficiario del Pais de Nacimiento
	//echo $pais_nac.'*'.$pais_nacn;
	$strConsulta = "select id_pais, nombre from uad.pais order by nombre";
	$result = @pg_exec($strConsulta); 
	$pais_nacq = '<option value="-1"> Seleccione Pais </option>';
	$opciones6 = '<option value="-1"> Seleccione Provincia </option>';
	$opciones7 = '<option value="-1"> Seleccione Localidad </option>';
	
	while( $fila = pg_fetch_array($result) )
	{
		
		$pais_nacq.='<option value="'.$fila["id_pais"].'"'; if($pais_nacn==$fila["nombre"] || $pais_nac==$fila["nombre"]){ $pais_nacq.='selected';  $pais_nacn=$fila["nombre"];  $idpais_nacn=$fila["id_pais"];} $pais_nacq.='>'.$fila["nombre"].'</option>';
	} // FIN WHILE	
		


// Query que muestra la informacion guardada del Beneficiario de la Provincia de Nacimiento
	 $provincia_nacn=$provincia_nac;
	$strConsulta = "select id_provincia, nombre from uad.provincias where cast(id_pais as text) = '$idpais_nacn'  order by nombre";
	$result = @pg_exec($strConsulta); 
	while( $fila = pg_fetch_array($result) )
	{
		$opciones6.='<option value="'.$fila["nombre"].'"'; if($provincia_nacn==$fila["nombre"]){ $opciones6.='selected';  $provincia_nacn=$fila["nombre"];  $idprovincia_nacn=$fila["nombre"];} $opciones6.='>'.$fila["nombre"].'</option>';
	} // FIN WHILE

// Query que muestra la informacion guardada del Beneficiario de la Localidad de Nacimiento
	$localidad_procn=$localidad_proc;
	$strConsulta = "select l.id_localidad, l.nombre from uad.localidades l,uad.provincias p, uad.departamentos d 
						where p.nombre = '$idprovincia_nacn' and d.id_provincia = p.id_provincia and 
						l.id_departamento = d.id_departamento order by nombre";
	$result = @pg_exec($strConsulta); 
	while( $fila = pg_fetch_array($result) )
	{
		$opciones7.='<option value="'.$fila["nombre"].'"';  if($localidad_procn==$fila["nombre"]){ $opciones7.='selected';  $localidad_procn=$fila["nombre"]; }  $opciones7.='>'.$fila["nombre"].'</option>';
	} // FIN WHILE

// Query que muestra la informacion guardada del Beneficiario del Departamento donde vive
//DEPARTAMENTO
		$provincia=$prov_uso; 
		 $departamenton=$departamento;
		 $strConsulta = "select d.id_departamento, upper(d.nombre)as nombre from uad.departamentos d inner join uad.provincias p using(id_provincia)
					where upper(p.nombre) = upper('$provincia') order by nombre";
		$result = @pg_exec($strConsulta); 
		//if ($id_planilla == ''){
			$departamento = '<option value="-1"> Seleccione Departamento </option>';
			$opciones2 = '<option value="-1" > Seleccione Localidad </option>';
			$opciones3 = '<option value="-1"> Seleccione Municipio </option>';
			$opciones4 = '<option value="-1"> Seleccione Barrio </option>
			<option value="S/D" '; if($barrio=="S/D"){ $opciones4.='selected'; $barrion=$fila["nombre"];}$opciones4.='> S/D</option>';
			$opciones5 = '<option value="-1"> Codigo Postal  </option>';
		//}	
		while( $fila = pg_fetch_array($result) )
		{
			
			$departamento.='<option value="'.$fila["id_departamento"].'"'; if($departamenton==$fila["nombre"]){ $departamento.='selected';  $departamenton=$fila["nombre"];}$departamento.='>'.$fila["nombre"].'</option>';
			
		} // FIN WHILE
		
//LOCALIDAD
	$localidadn=$localidad;
	$strConsulta = "select upper(l.nombre)as nombre
                            from uad.localidades l
                            inner join uad.departamentos d on l.id_departamento=d.id_departamento
                            where upper(d.nombre) = upper('$departamenton')";
	$result = @pg_exec($strConsulta); 
	while( $fila = pg_fetch_array($result) )
	{
		$opciones2.='<option value="'.$fila["nombre"].'"'; if($localidad==$fila["nombre"]){ $opciones2.='selected'; $localidadn=$fila["nombre"];}$opciones2.='>'.$fila["nombre"].'</option>';
		
	}
//CODIGO POSTAL
	 $strConsulta = "select codpost.codigopostal
				from uad.codpost
				inner join uad.localidades on codpost.id_localidad=localidades.idloc_provincial
				where localidades.nombre='$localidad'";
		$result = @pg_exec($strConsulta); 
		while( $fila = pg_fetch_array($result) )
		{
			$opciones5.='<option value="'.$fila["codigopostal"].'"'; if($cod_pos==$fila["codigopostal"]){ $opciones5.='selected'; $cod_posn=$fila["codigopostal"];}$opciones5.='>'.$fila["codigopostal"].'</option>';
		}

//MUNICIPIO
		$strConsulta = "select upper(nombre)as nombre
						from uad.municipios
						inner join uad.codpost on codpost.id_codpos=municipios.id_codpos
						where codpost.codigopostal='$cod_posn'
						order by nombre";
		$result = @pg_exec($strConsulta); 
		while( $fila = pg_fetch_array($result) )
		{
			$opciones3.='<option value="'.$fila["nombre"].'"'; if($municipio==$fila["nombre"]){ $opciones3.='selected'; $municipion=$fila["nombre"];}$opciones3.='>'.$fila["nombre"].'</option>';
		}
					
//BARRIO	
	 $strConsulta = "select upper(b.nombre)as nombre
					from uad.barrios b
					inner join uad.municipios m on m.idmuni_provincial=b.id_municipio
					where upper(m.nombre)=upper('$municipio') 
									order by b.nombre";
		$result = @pg_exec($strConsulta); 
		while( $fila = pg_fetch_array($result) )
		{
			$opciones4.='<option value="'.$fila["nombre"].'"'; if($barrio==$fila["nombre"]){ $opciones4.='selected'; $barrion=$fila["nombre"];}$opciones4.='>'.$fila["nombre"].'</option>';
			//$barrion=$fila["barrio"];
		}

echo $html_header;

$directorio_base=trim(substr(ROOT_DIR, strrpos(ROOT_DIR,chr(92))+1, strlen(ROOT_DIR)));
?>
<script type="text/javascript" src="/<?php echo $directorio_base?>/lib/jquery-1.5.1.js"> </script>
<script>
//Script para el manejo de combobox de Pais de Nacimiento - Provincia de Nacimiento y Localidad de Nacimiento
$(document).ready(function(){
	$("#pais_nac").change(function(){
		$.ajax({
			url:"procesa.php",
			type: "POST",
			data:"id_pais="+$("#pais_nac").val(),
			success: function(opciones){
				$("#provincia_nac").html(opciones);
						
			}
		})
	});
});
$(document).ready(function(){
	$("#provincia_nac").change(function(){
		$.ajax({
			url:"procesa.php",
			type: "POST",
			data:"id_provincia="+$("#provincia_nac").val(),
			success: function(opciones){
				$("#localidad_nac").html(opciones);
						
			}
		})
	});
}); //FIN

//Script para el manejo de combobox de Departamento - Localidad - Municipio y Barrio
$(document).ready(function(){
	$("#departamento").change(function(){
		$.ajax({
			url:"procesa.php",
			type: "POST",
			data:"id_departamento="+$("#departamento").val()+"&provincia="+document.all.prov_uso.value,
			success: function(opciones){
				$("#localidad").html(opciones);
						
			}
		})
	});
});
$(document).ready(function(){
	$("#localidad").change(function(){
		$.ajax({
			url:"procesa.php",
			type: "POST",
			data:"id_localidad="+$("#localidad").val(),
			success: function(opciones){
				$("#cod_pos").html(opciones);
				
				}
		})
	});
});
$(document).ready(function(){
	$("#cod_pos").change(function(){
		$.ajax({
			url:"procesa.php",
			type: "POST",
			data:"id_codpos="+$("#cod_pos").val()+"&provincia="+document.all.prov_uso.value+"&localidad="+document.all.localidad.value,
			success: function(opciones){
				$("#municipio").html(opciones);
										
			}
		})
	});
});

$(document).ready(function(){
	$("#municipio").change(function(){
		$.ajax({
			url:"procesa.php",
			type: "POST",
			data:"id_municipio="+$("#municipio").val(),
			success: function(opciones){
				$("#barrio").html(opciones);
				
				
			}
		})
	});
});// FIN

$(document).ready(function(){
	//$("#barrio").focus(function(){
	$("button[name='b_barrio']").focus(function(){
		$.ajax({
			url:"procesa.php",
			type: "POST",
			data:"id_municipio="+$("#municipio").val()+"&barrio="+document.all.barrion.value,//+$("#barrion").val(),
			success: function(opciones){
				$("#barrio").html(opciones);
				
				
			}
		})
	});
});
//Guarda el nombre del Pais
function showpais_nac(){
	var pais_nac = document.getElementById('pais_nac')[document.getElementById('pais_nac').selectedIndex].innerHTML;
	document.all.pais_nacn.value =  pais_nac;
}// FIN

//Guarda el nombre de la Provincia de Nacimiento
function showprovincia_nac(){
	var provincia_nac = document.getElementById('provincia_nac')[document.getElementById('provincia_nac').selectedIndex].innerHTML;
	document.all.provincia_nacn.value =  provincia_nac;
}// FIN

//Guarda el nombre de la Localidad de Nacimiento
function showlocalidad_nac(){
	var localidad_nac = document.getElementById('localidad_nac')[document.getElementById('localidad_nac').selectedIndex].innerHTML;
	document.all.localidad_procn.value =  localidad_nac;
}// FIN

//Guarda el nombre del Departamento
function showdepartamento(){
	var departamento = document.getElementById('departamento')[document.getElementById('departamento').selectedIndex].innerHTML;
	document.all.departamenton.value =  departamento;
} // FIN

//Guarda el nombre del Localidad
function showlocalidad(){
	var localidad = document.getElementById('localidad')[document.getElementById('localidad').selectedIndex].innerHTML;
	document.all.localidadn.value =  localidad;
}// FIN

// Guarda el Codigo Postal
function showcodpos(){
	var cod_pos = document.getElementById('cod_pos')[document.getElementById('cod_pos').selectedIndex].innerHTML;
	document.all.cod_posn.value =  cod_pos;
}// FIN

//Guarda el nombre del Municipio
function showmunicipio(){
	var municipio = document.getElementById('municipio')[document.getElementById('municipio').selectedIndex].innerHTML;
	document.all.municipion.value =  municipio;
}// FIN

//Guarda el nombre del Barrio
function showbarrio(){
	var barrio = document.getElementById('barrio')[document.getElementById('barrio').selectedIndex].innerHTML;
	document.all.barrion.value =  barrio;
}// FIN

function tapa(evt){
var key = nav4 ? evt.which : evt.keyCode; 
	if(document.all.tapa_ver.value=='block' && key!=9)
	{
		location.href='ins_admin_old.php?tapa_ver="none"';
	}
}

var nav4 = window.Event ? true : false;
function acceptNum(evt){ 
var key = nav4 ? evt.which : evt.keyCode; 
return (key <= 13 || (key >= 48 && key <= 57));
}
//controlan que ingresen todos los datos necesarios par el muleto
function control_nuevos()
{

 var fecha=document.getElementById('fecha_nac');
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
        //return true;
    }
	
var fecha=document.getElementById('fecha_inscripcion');
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
        //return true;
    }

 if(document.all.num_doc.value==""){
	 alert("Debe completar el campo numero de documento");
	 document.all.num_doc.focus();
	 return false;
	 }else{
 		var num_doc=document.all.num_doc.value;
		if(isNaN(num_doc)){
			alert('El dato ingresado en numero de documento debe ser entero');
			document.all.num_doc.focus();
			return false;
	 	}
	 }
 
 if(document.all.apellido.value==""){
	 alert("Debe completar el campo apellido");
	 document.all.apellido.focus();
	 return false;
 }else{
	 var charpos = document.all.apellido.value.search("/[^A-Za-z\s]/"); 
	   if( charpos >= 0) 
	    { 
	     alert( "El campo Apellido solo permite letras "); 
	     document.all.apellido.focus();
	     return false;
	    }
	 }	
 

 if(document.all.nombre.value==""){
	 alert("Debe completar el campo nombre");
	 document.all.nombre.focus();
	 return false;
	 }else{
		 var charpos = document.all.nombre.value.search("/[^A-Za-z\s]/"); 
		   if( charpos >= 0) 
		    { 
		     alert( "El campo Nombre solo permite letras "); 
		     document.all.nombre.focus();
		     return false;
		    }
		 }		
	
 if(document.all.sexo.value=="-1"){
			alert("Debe completar el campo sexo");
			document.all.sexo.focus();
			 return false;
		 }
 if(document.all.remediar.value!="s"){
     if(document.all.pais_nac.value=="-1"){
                    alert("Debe completar el campo pais");
                    document.all.pais_nac.focus();
                     return false;
                     }
     if(document.all.provincia_nac.value=="-1"){
                    alert("Debe completar el campo provincia");
                    document.all.provincia_nac.focus();
                     return false;
     }
 }
 if(document.all.calle.value==""){
		alert("Debe completar el campo calle");
		document.all.calle.focus();
		 return false;
		 }

 if(document.all.numero_calle.value==""){
		alert("Debe completar el campo numero calle");
		document.all.numero_calle.focus();
		 return false;
		 }
	if(document.all.departamento.value=="-1"){
		alert("Debe completar el campo departamento");
		document.all.departamento.focus();
		 return false;
		 }

	if(document.all.id_categoria.value=='-1'){
		  alert('Debe Ingresar una Categoria');
		  return false;
		 }
	 if(document.all.cuie.value=="-1"){
		  alert('Debe Seleccionar un Efector');
		  document.all.cuie.focus();
		  return false;
		 } 

	 
	 if (document.all.id_categoria.options[document.all.id_categoria.selectedIndex].text.substr(0,1)=='2'){
			if(document.all.responsable.value=="-1"){
				alert ("Debe completar el campo Datos del responsable");
				document.all.responsable.focus();
				return false;
			}
			if(document.all.tipo_doc_madre.value=="-1"){
				alert("Debe completar el campo tipo de documento del responsable");
				document.all.apellido_madre.focus();
				 return false;
			 }
			if(document.all.nro_doc_madre.value==""){
				
				alert("Debe completar el campo numero de documento del responsable");
			
				return false;
			 }else{
				 var num_doc_madre=document.all.nro_doc_madre.value;
				 if(isNaN(num_doc_madre)){
					alert('El dato ingresado en numero de documento del responsable debe ser entero');
					document.all.num_doc_madre.focus();
					return false;
				}
			}
			var anio_mayor_nivel=document.all.anio_mayor_nivel.value;
			 var anio_mayor_nivel_madre=document.all.anio_mayor_nivel_madre.value;
			 if(isNaN(anio_mayor_nivel) || isNaN(anio_mayor_nivel_madre) ){
				alert('El dato ingresado en a�os mayor nivel debe ser entero');
			 return false;
			 }
			if(document.all.apellido_madre.value==""){
				alert("Debe completar el campo apellido del responsable");
				document.all.apellido_madre.focus();
				 return false;
			 }else{
				 var charpos = document.all.apellido_madre.value.search("[^A-Za-z/\s/]"); 
				   if( charpos >= 0) 
				    { 
				     alert( "El campo apellido del responsable solo permite letras "); 
				     document.all.apellido_madre.focus();
				     return false;
				    }
				 }	
			if(document.all.nombre_madre.value==""){
				alert("Debe completar el campo nombre del responsable");
				document.all.nombre_madre.focus();
				 return false;
			 }else{
				 var charpos = document.all.nombre_madre.value.search("[^A-Za-z/\s/]"); 
				   if( charpos >= 0) 
				    { 
				     alert( "El campo Nombre del responsable solo permite letras "); 
				     document.all.nombre_madre.focus();
				     return false;
				    }
				 }	
				
			if(document.all.alfabeta_madre.value=="-1"){
			alert("Debe completar el campo alfabeto del responsable");
			 return false;
		 	}
		}
	 var docu=document.all.clase_doc.value;
		if(docu!='P'){
			var num1=document.all.nro_doc_madre.value;
			var num2=document.all.num_doc.value;
			if (num1 != num2){
				alert("Los numeros de documento deben coincidir");
				document.all.num_doc.focus();
				return false;
			}
		}
	

	//si esta embarazada o es puerpera menor de 45 dias
/*	if(document.all.fecha_diagnostico_embarazo.value==""){
	alert("Debe completar el campo fecha de diagnostico de embarazo");
	 return false;
	 }
	if(document.all.fecha_probable_parto.value==""){
	alert("Debe completar el campo fecha probable de parto");
	 return false;
	 }*/
	if(document.all.fecha_nac.value==""){
		alert("Debe completar el campo fecha de nacimiento");
		 return false;
		 }

/*if ((document.all.indigena.value=="N")|| (document.all.indigena.value=="n")){
	
	alert("modifica los value");
	 }*/
	 //TODAVIA NO ESTAN CARGADAS LAS LOCALIDADES,MUNICIPIOS Y BARRIOS!!!!
	
	/*if(document.all.localidad_proc.value=="-1"){
	alert("Debe completar el campo Localidad");
	document.all.localidad_proc.focus();
	 return false;
	}*/

	if(document.all.localidad.value=="-1"){
		alert("Debe completar el campo Localidad");
		document.all.localidad.focus();
		 return false;
	}
	if(document.all.municipio.value=="-1"){
	alert("Debe completar el campo Municipio");
	document.all.municipio.focus();
	 return false;
	 }
	 if(document.all.cod_pos.value=="-1"){
	alert("Debe completar el campo Cod. Postal");
	document.all.cod_pos.focus();
	 return false;
	 }

	if(document.all.barrio.value=="-1" || document.all.barrio.value=="-1"){
	alert("Debe completar el campo Barrio");
	document.all.barrio.focus();
	 return false;
		 }

}
//de function control_nuevos()

function editar_campos()
{
	inputs = document.form1.getElementsByTagName('input'); //Arma un arreglo con todos los campos tipo INPUT
	for (i=0; i<inputs.length; i++){
	    inputs[i].readOnly=false;
	}

	document.all.cancelar_editar.disabled=false;
	document.all.guardar_editar.disabled=false;
	document.all.editar.disabled=true;
 	return true;
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

function cambiar_patalla(){	
	
	//si no hay nada seleccionado en categoria no mostrar nada
	if (document.all.id_categoria.value=='-1' || document.all.id_categoria.value=='7'){
		document.all.cat_emb.style.display='none';
		document.all.cat_nino.style.display='none';
                document.all.cat_puerp.style.display='none';
	}	
	
	//si es masculino y recien nacio es una inscripcion con datos de padre madre o tutor
	if ((document.all.sexo.value=='M')&&(document.all.id_categoria.value=='3')){
		document.all.cat_emb.style.display='none';
		document.all.cat_nino.style.display='inline';
                document.all.cat_puerp.style.display='none';

	}
	
	//si es masculino y menor de 6 a�os es una inscripcion con datos de padre madre o tutor
	if ((document.all.sexo.value=='M')&&(document.all.id_categoria.value=='4')){
		document.all.cat_emb.style.display='none';
		document.all.cat_nino.style.display='inline';
                document.all.cat_puerp.style.display='none';
	}

	//femenino embarazada
	if(((document.all.sexo.value=='f')||(document.all.sexo.value=='F'))&&(document.all.id_categoria.value=='1')){
		document.all.cat_emb.style.display='inline';
		document.all.cat_nino.style.display='none';
                document.all.cat_puerp.style.display='none';
		
		}

	//femenino puerpera menor de 45 d�as
	if(((document.all.sexo.value=='f')||(document.all.sexo.value=='F'))&&(document.all.id_categoria.value=='2')){
		document.all.cat_emb.style.display='none';
		document.all.cat_nino.style.display='none';
		document.all.cat_puerp.style.display='inline';
		
		}
		
	//si es menor de 6 a�os y femenino una inscripcion con los datos de madre padro o tutor
	if(((document.all.sexo.value=='f')||(document.all.sexo.value=='F'))&&(document.all.id_categoria.value=='4' || document.all.id_categoria.value=='3')){
		document.all.cat_emb.style.display='none';
		document.all.cat_nino.style.display='inline';
		document.all.cat_puerp.style.display='none';
		
		}
	

}// Calcula la FPP 
var aFinMes = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

function finMes(nMes, nAno){
 return aFinMes[nMes - 1] + (((nMes == 2) && (nAno % 4) == 0)? 1: 0);
}

 function padNmb(nStr, nLen, sChr){
  var sRes = String(nStr);
  for (var i = 0; i < nLen - String(nStr).length; i++)
   sRes = sChr + sRes;
  return sRes;
 }

 function makeDateFormat(nDay, nMonth, nYear){
  var sRes;
  sRes = padNmb(nDay, 2, "0") + "/" + padNmb(nMonth, 2, "0") + "/" + padNmb(nYear, 4, "0");
  return sRes;
 }
 
function incDate(sFec0){
 var nDia = parseInt(sFec0.substr(0, 2), 10);
 var nMes = parseInt(sFec0.substr(3, 2), 10);
 var nAno = parseInt(sFec0.substr(6, 4), 10);
 nDia += 1;
 if (nDia > finMes(nMes, nAno)){
  nDia = 1;
  nMes += 1;
  if (nMes == 13){
   nMes = 1;
   nAno += 1;
  }
 }
 return makeDateFormat(nDia, nMes, nAno);
}

function decDate(sFec0){
 var nDia = Number(sFec0.substr(0, 2));
 var nMes = Number(sFec0.substr(3, 2));
 var nAno = Number(sFec0.substr(6, 4));
 nDia -= 1;
 if (nDia == 0){
  nMes -= 1;
  if (nMes == 0){
   nMes = 12;
   nAno -= 1;
  }
  nDia = finMes(nMes, nAno);
 }
 return makeDateFormat(nDia, nMes, nAno);
}

function addToDate(sFec0, sInc){
 var nInc = Math.abs(parseInt(sInc));
 var sRes = sFec0;
 if (parseInt(sInc) >= 0)
  for (var i = 0; i < nInc; i++) sRes = incDate(sRes);
 else
  for (var i = 0; i < nInc; i++) sRes = decDate(sRes);
 return sRes;
}

function recalcF1(){
 with (document.form1){
  fecha_probable_parto.value = addToDate(fecha_diagnostico_embarazo.value, 280 - (semanas_embarazo.value *7));
 }
}

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


</script>

<form name='form1' action='ins_admin_old.php' method='POST'>
<input type="hidden" value="<?=$id_planilla?>" name="id_planilla">
<input type="hidden" value="<?=$campo_actual?>" name="campo_actual">
<input type="hidden" value="<?=$remediar?>" name="remediar">
<input type="hidden" value="<?=$clave_beneficiario?>" name="clave_beneficiario">
<input type="hidden" value="<?=$tapa_ver?>" name="tapa_ver">
<input type="hidden" value="<?=$tipo_ficha?>" name="tipo_ficha">
<input type="hidden" value="<?=$prov_uso?>" name="prov_uso">
<?echo "<center><b><font size='+1' color='red'>$accion</font></b></center>";  ?>
<?echo "<center><b><font size='+1' color='Blue'>$accion2</font></b></center>";?>
<table width="97%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?=$bgcolor_out?>' class="bordes">
 <tr id="mo">
    <td>
    	<?
    	if (!$id_planilla) {
    	?>  
    	<font size=+1><b>Nuevo Formulario</b></font>   
    	<? }
        else {
        ?>
        <font size=+1><b>Formulario</b></font>   
        <? } ?>
       
    </td>
 </tr>
 <tr>
     <td>
  <table width=100% align="center" class="bordes">
      <tr>     
       <td>
        <table width=70% class="bordes" align="center">                 
         <tr>	           
           <td align="center" colspan="4" id="ma">
            <b> Numero de Formulario: <font size="+1" color="Blue"><?=($id_planilla)? $clave_beneficiario : "Nuevo"?></font> </b>  <? if ($trans == 'Borrado'){?> <input type="text" name=trans style="border:none;width:50px" value="<?=$trans?>" disabled > <?}?>
           </td>
         </tr>
         
         <tr>	           
           <td align="center" colspan="4">
             <b><font size="0" color="Red">Nota: Los valores numericos se ingresan SIN separadores de miles, y con "." como separador DECIMAL</font> </b>
           </td>
         </tr>
         
         <tr style="display:block">
             <td align="right" width="20%">
                <b>Nro de Documento:</b>
            </td>         	
            <td align='left' width="30%">
              <input type="text" size="30" value="<?=$num_doc?>" name="num_doc" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?> onKeyPress="tapa(event); return acceptNum(event); " onkeydown="tapa(event);" maxlength="8">
              <? if (!$id_planilla) {?><input type="submit" size="3" value="b" name="b"><? }?><br><font color="Red">Sin Puntos</font>
            </td>
            <td align="right" width="20%">
                <b>Tipo de Transaccion:</b>
            </td>
            <td align="left" width="30%">			 	
			 <select name=tipo_transaccion Style="width:200px" 
        		onKeypress="buscar_combo(this);"
				onblur="borrar_buffer();"
				onchange="borrar_buffer();document.forms[0].submit()" 
				<?php if ($trans == 'Borrado')echo "disabled"?>
				>
			 <option value='A' <?if ($tipo_transaccion=='A') echo "selected"?>>Inscripcion</option>
			 <option value='M'<?if ($tipo_transaccion=='M') echo "selected"?>>Modificacion</option>
			 <option value='B'<?if ($tipo_transaccion=='B') echo "selected"?>>Baja</option>
			 <!-- <option value='Reinscripcion'<?if ($tipo_transaccion=='R') echo "selected"?>>Reinscripcion</option> -->
			 
			</select>
            </td>            
         </tr>
          <?if($ape_nom=='s'){?>
         <tr id="tapa" style="display:<?=$tapa_ver?>">
         	<td align="right">
         	  <b><font color="Red">*</font>Primer Apellido:</b>
         	</td>
            <td align='left'>
              <input type="text" size="30" value="<?=$apellido?>" name="apellido" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "readOnly"?> maxlength="50">
            </td>
           	<td align="right">
         	  <b>Otros Apellidos:</b>
         	</td>
            <td align='left'>
              <input type="text" size="30" value="<?=$apellido_otro?>" name="apellido_otro" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "readOnly"?> maxlength="30">
            </td>
         </tr>
        <tr id="tapa" style="display:<?=$tapa_ver?>">
         	<td align="right">
         	  <b><font color="Red">*</font>Primer Nombre:</b>
         	</td>
            <td align='left'>
              <input type="text" size="30" value="<?=$nombre?>" name="nombre" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "readOnly"?> maxlength="50">
            </td>
           	<td align="right">
         	  <b>Otros Nombres:</b>
         	</td>
            <td align='left'>
              <input type="text" size="30" value="<?=$nombre_otro?>" name="nombre_otro" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "readOnly"?> maxlength="30">
            </td>
         </tr>
         <? }else{?>
         <tr id="tapa" style="display:<?=$tapa_ver?>">
         	<td align="right">
         	  <b><font color="Red">*</font>Apellido:</b>
         	</td>         	
            <td align='left'>
              <input type="text" size="30" value="<?=$apellido?>" name="apellido" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?> maxlength="50">
            </td>
           	<td align="right">
         	  <b><font color="Red">*</font>Nombre:</b>
         	</td>         	
            <td align='left'>
              <input type="text" size="30" value="<?=$nombre?>" name="nombre" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?> maxlength="50">
            </td>
         </tr> <? }?>
         
		<tr style="display:block">
            <td align="right">
				<b>Clase de Documento:</b>
			</td>
			<td align="left">			 	
			 <select name=clase_doc Style="width:200px" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?>>
			  <option value=P <?if ($clase_doc=='P') echo "selected"?>>Propio</option>
			  <option value=A <?if ($clase_doc=='A') echo "selected"?>>Ajeno</option>
			  <!-- <option value=M <?if ($clase_doc=='M') echo "selected"?>>Madre</option> -->
			  <!-- <option value=P <?if ($clase_doc=='P') echo "selected"?>>Padre</option>  -->
			  <!-- <option value=T <?if ($clase_doc=='T') echo "selected"?>>Tutor</option> --> 
			 </select>
			</td> 
         	<td align="right">
				<b>Tipo de Documento:</b>
			</td>
			<td align="left">			 	
			 <select name=tipo_doc Style="width:200px" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?>>
			  <option value=DNI <?if ($tipo_doc=='DNI') echo "selected"?>>Documento Nacional de Identidad</option>
			  <option value=LE <?if ($tipo_doc=='LE') echo "selected"?>>Libreta de Enrolamiento</option>
			  <option value=LC <?if ($tipo_doc=='LC') echo "selected"?>>Libreta Civica</option>
			  <option value=PA <?if ($tipo_doc=='PA') echo "selected"?>>Pasaporte Argentino</option>
			  <option value=CM <?if ($tipo_doc=='CM') echo "selected"?>>Certificado Migratorio</option>
			 </select>
			</td>
         </tr>

         <tr id="tapa" style="display:<?=$tapa_ver?>">	           
           <td align="center" colspan="4" id="ma">
            <b> Datos de Nacimiento </b>
           </td>
         </tr>
         
         <tr id="tapa" style="display:<?=$tapa_ver?>">
         	<td align="right">
				<b><font color="Red">*</font>Sexo:</b>
			</td>
			<td align="left">			 	
			<select name=sexo Style="width:200px" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?> >
			 <option value='-1' >Seleccione</option>
			  <option value=F <?if ($sexo=='F') echo "selected"?>>Femenino</option>
			  <option value=M <?if ($sexo=='M') echo "selected"?>>Masculino</option>
			  </select>
			  
			 
			</td> 
         	<td align="right">
				<b><font color="Red">*</font>Fecha de Nacimiento:</b>
			</td>
		    <td align="left">
		    	<input type=text name="fecha_nac" value='<?=$fecha_nac;?>' size=15 <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?> maxlength="10" onchange="esFechaValida(this);">
		    	<?=link_calendario('fecha_nac');?>   
		    </td>		    
		</tr>   

		<tr id="tapa" style="display:<?=$tapa_ver?>">
			<td align="right" >
				<b>Extranjero/Pais:</b> <input type="hidden" name="pais_nacn" value="<?=$pais_nacn?>">
			</td>
			<td align="left" >
			<select id="pais_nac" name="pais_nac" onchange="showpais_nac();" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?>><?php echo $pais_nacq;?></select>			 	
		   	</td> 
    
            <td align="right">
				<b>Provincia:</b> <input type="hidden" name="provincia_nacn" value="<?=$provincia_nacn?>">
			</td>
			<td align="left">	
			<select id="provincia_nac" name="provincia_nac" onchange="showprovincia_nac();" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?>><?php echo $opciones6;?></select>
			</td> 
         	
         </tr> 
         
         <tr  id="tapa" style="display:<?=$tapa_ver?>">
            <td align="right">
				<b>Localidad:</b> <input type="hidden" name="localidad_procn" value="<?=$localidad_procn?>">
				
			</td>
			<td align="left">			 	
			<select id="localidad_nac" name="localidad_nac" onchange="showlocalidad_nac();" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?>><?php echo $opciones7;?></select>
			
			</td>
			<td align="right">
         	   <b>Originario:</b>
         	   	
         	</td>         	
            <td align='left'>
				<input type="radio" name="indigena" value="N"  <?php if(($indigena == "N") or ($indigena==""))echo "checked" ;?> onclick="document.all.id_tribu.value='0';document.all.id_lengua.value='0';" > NO
				<input type="radio" name="indigena" value="S" <?php if($indigena == "S") echo "checked" ;?> onclick="document.all.id_tribu.disabled=false;document.all.id_lengua.disabled=false;"> SI
            </td>
					
         </tr> 
         
         <tr id="tapa" style="display:<?=$tapa_ver?>">
         	<td align="right">
         	  <b>Pueblo Indigena:</b>
         	</td>         	
            <td align='left'>
              <select name=id_tribu Style="width:200px" 
        		onKeypress="buscar_combo(this);"
				onblur="borrar_buffer();"
				onchange="borrar_buffer();" 
				<?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?>>
			 <option value='-1'>Seleccione</option>
			 <?
			 $sql= "select * from uad.tribus order by nombre";
			 $res_efectores=sql($sql) or fin_pagina();
			 while (!$res_efectores->EOF){ 
			 	$id=$res_efectores->fields['id_tribu'];
			    $nombre=$res_efectores->fields['nombre'];
			    
			    ?>
				<option value='<?=$id?>' <?if ($id_tribu==$id) echo "selected"?> ><?=$nombre?></option>
			    <?
			    $res_efectores->movenext();
			    }?>
			</select>
            </td>
           	<td align="right">
         	  <b>Idioma O Lengua:</b>
         	</td>         	
            <td align='left'>
             <select name=id_lengua Style="width:200px" 
        		onKeypress="buscar_combo(this);"
				onblur="borrar_buffer();"
				onchange="borrar_buffer();" 
				<?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?>>
			 <option value='-1'>Seleccione</option>
			 <?
			 $sql= "select * from uad.lenguas order by nombre";
			 $res_efectores=sql($sql) or fin_pagina();
			 while (!$res_efectores->EOF){ 
			 	$id=$res_efectores->fields['id_lengua'];
			    $nombre=$res_efectores->fields['nombre'];
			    
			    ?>
				<option value='<?=$id?>' <?if ($id_lengua==$id) echo "selected"?> ><?=$nombre?></option>
				
			    <?
			    $res_efectores->movenext();
			    }?>
			</select>
            </td>
         </tr> 
         
                              
         <tr id="tapa" style="display:<?=$tapa_ver?>">	           
           <td align="center" colspan="4" id="ma">
            <b> Categoria </b>
           </td>        
         </tr>
         
         <tr  align="center" id="tapa" style="display:<?=$tapa_ver?>">
         	<td align="right" width="20%" colspan="2">
				<b><font color="Red">*</font>Categoria del Beneficiario:</b>
			</td>
			<td align="left" width="30%" colspan="2">			 	
			 <select name=id_categoria Style="width:200px" 
        		onKeypress="buscar_combo(this);"
				onblur="borrar_buffer();"
				onchange="borrar_buffer(); cambiar_patalla();" 
				<?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?>>
			 <? /*$grupo_remediar='n';
                             $sql2= "select *
                                    from permisos.grupos_usuarios a
                                    inner join permisos.grupos b on a.id_grupo=b.id_grupo
                                    where upper(uname) like '%REMEDIAR' and id_usuario=".$_ses_user['id'];
                             $val_permis=sql($sql2, "Error permiso") or fin_pagina();
                          if ($val_permis->RecordCount()>0){ if (!$id_categoria)*/$id_categoria=7; $grupo_remediar='s';//}
                         

			 $sql= "select * from uad.categorias where tipo_ficha='1' or id_categoria=7 order by id_categoria ";
			 $res_efectores=sql($sql) or fin_pagina();?>
			 
			 <option value='-1' <?if ($id_categoria=='-1') echo "selected"?>>Seleccione</option>
			 <?while (!$res_efectores->EOF){ 
			 	$id_categorial=$res_efectores->fields['id_categoria'];
			 	$tipo_ficha=$res_efectores->fields['tipo_ficha'];
			    $categoria=$res_efectores->fields['categoria'];?>
				<option value='<?=$id_categorial?>'<?if ($id_categoria==$id_categorial) echo "selected";?>><?echo $categoria;?></option>
			    <?$res_efectores->movenext();
			    }?>
			</select>
			</td>            
         </tr> 
         
         
         
         <tr><td colspan="4"><table id="cat_nino" class="bordes" width="100%" style="display:<?=$datos_resp ?>;border:thin groove;">
         
         <tr>         
         <td align="center" colspan="4" id="ma">
            <b> Datos del Responsable </b>
         </td>        
         </tr>
         
         <tr>
         	<td align="right" >
				<b>Datos de Responsable:</b>
			</td>
			<td align="left" >			 	
<?php
				 if (($id_planilla) and ($tipo_transaccion == "M")){
			 		$sql= "select responsables.* from uad.responsables";
			 		$refrescar="";
			 }
			 else {
			 	
			 	$sql= "select responsables.* from uad.responsables
					where upper(responsables.nombre)=upper('$responsable')";
			 		$refrescar = "document.forms[0].submit()";
			 }
			 
			?> 	
			   <select name=responsable Style="width:200px" 
        		onKeypress="buscar_combo(this);"
				onblur="borrar_buffer();"
				<?php if ($refrescar=='') {echo "onchange='borrar_buffer();'";} 
				else {echo "onchange='borrar_buffer(); document.forms[0].submit()'";}
				if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?>>

			 <option value=''>Seleccione</option>
			 
			 <?php
			 $sql= "select responsables.* from uad.responsables";
			 $res_efectores=sql($sql) or fin_pagina();
			 while (!$res_efectores->EOF){ 
			 	$id=$res_efectores->fields['id_responsables'];
			    $nombre=$res_efectores->fields['nombre'];
			    
			    ?>
				<option value='<?=$nombre?>' <?if ($responsable==$nombre) echo "selected"?> ><?=$nombre?></option>
			    <?
			    $res_efectores->movenext();
			    }?>
			</select>		  
			<td align="right">
				<b>Menor Vive con Adulto:</b>
			</td>
			<td align="left" >			 	
			 <select name=menor_convive_con_adulto Style="width:200px" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?> >
			  <option value=S <?if ($menor_convive_con_adulto=='S') echo "selected"?>>SI</option>
			  <option value=N <?if ($menor_convive_con_adulto=='N') echo "selected"?>>NO</option>
			  </select>
			</td> 
		</tr>
         
          <tr>
          	<td align="right">
				<b>Tipo de Documento:</b>
			</td>
			<td align="left">			 	
			 <select name=tipo_doc_madre Style="width:200px" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?> >
			  <option value=DNI <?if ($tipo_doc_madre=='DNI') echo "selected"?>>Documento Nacional de Identidad</option>
			  <option value=LE <?if ($tipo_doc_madre=='LE') echo "selected"?>>Libreta de Enrolamiento</option>
			  <option value=LC <?if ($tipo_doc_madre=='LC') echo "selected"?>>Libreta Civica</option>
			  <option value=PA <?if ($tipo_doc_madre=='PA') echo "selected"?>>Pasaporte Argentino</option>
			  <option value=CM <?if ($tipo_doc_madre=='CM') echo "selected"?>>Certificado Migratorio</option>
			 </select>
			</td>          	
         	<td align="right" width="20%">
         	  <b>Documento:</b>
         	</td>         	
            <td align='left' width="30%">
              <input type="text" size="30" value="<?=$nro_doc_madre?>" name="nro_doc_madre" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?>maxlength="12">
            </td>            
         </tr>
         
         <tr>
         	<td align="right">
         	  <b>Apellido:</b>
         	</td>         	
            <td align='left'>
              <input type="text" size="30" value="<?=$apellido_madre?>" name="apellido_madre" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?> maxlength="30">
            </td>
           	<td align="right">
         	  <b>Nombre:</b>
         	</td>         	
            <td align='left'>
              <input type="text" size="30" value="<?=$nombre_madre?>" name="nombre_madre" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?> maxlength="30">
            </td>
         </tr> 
         <tr>	           
            
         </table>
         
         </td></tr>
         
         <tr><td colspan="4"><table id="cat_emb" class="bordes" width="100%" style="display:<?= $embarazada ?>;border:thin groove">
         
         <tr>	           
           <td align="center" colspan="4" id="ma">
            <b> Datos de Embarazo </b>
           </td>        
         </tr>
         
          <tr>
         	<td align="right">
				<b>Fecha de Diag. de Embarazo:</b>
			</td>
		    <td align="left">	       
		    	 <input type=text name=fecha_diagnostico_embarazo value='<?=$fecha_diagnostico_embarazo;?>' <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?> size=15 maxlength="10">
		    	 <?=link_calendario("fecha_diagnostico_embarazo");?>					    	 
		    </td>
		    <td align="right">
         	   <b>Semana de Embarazo:</b>         	   	
         	</td>         	
            <td align='left'>
            
            	<input type="text" name="semanas_embarazo"  value=<?=$semanas_embarazo;?> onblur="recalcF1()"  size="30"  <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?> maxlength="4">
            </td>		    
		</tr>   
		
		<tr>
         	<td align="right">
				<b>Fecha Probable de Parto:</b>
			</td>
		    <td align="left">
		    	
		    	 <input type=text name=fecha_probable_parto value='<?=$fecha_probable_parto;?>' size=15 <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?> maxlength="10">
		    	 <?=link_calendario("fecha_probable_parto");?>				    	 
		    </td>
		    </tr>   
		       
         </table>
          <tr><td colspan="4"><table id="cat_puerp" class="bordes" width="100%" style="display:<?= $puerpera ?>;border:thin groove">
         
         <tr>	           
           <td align="center" colspan="4" id="ma">
            <b> Puerpera menor de 45 d�as </b>
           </td>        
         </tr>
		    <td align="right">
         	   <b>Fecha Efectiva del Parto:</b>         	   	
         	</td>         	
            <td align='left'>
            	<? $fecha_comprobante=date("d/m/Y");?>
		    	<input type=text id=fecha_efectiva_parto name=fecha_efectiva_parto value='<?=$fecha_efectiva_parto;?>' size=15 <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?> maxlength="10">
		    	<?=link_calendario("fecha_efectiva_parto");?>
            </td>		    
		
          </tr>   
		       
         </table>
         <tr id="tapa" style="display:<?=$tapa_ver?>">	           
           <td align="center" colspan="4" id="ma">
            <b> Fecha de Inscripcion </b>
           </td>
         </tr>

         <tr id="tapa" style="display:<?=$tapa_ver?>">
         	<td align="right" colspan="2">
				<b>Fecha de Inscripcion:</b>
			</td>
		    <td align="left" colspan="2">
		    	 <input type=text name=fecha_inscripcion value='<?=$fecha_inscripcion;?>' size=15 <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"; if (($id_planilla) and ($tipo_transaccion != "M"))echo "readonly";?> maxlength="10"  onblur="esFechaValida(this);">
		    	 <?if (($id_planilla) and ($tipo_transaccion == "M")){}else{echo link_calendario("fecha_inscripcion");}?>
		    </td>		    	    
		</tr>
         
         <tr id="tapa" style="display:<?=$tapa_ver?>">	           
           <td align="center" colspan="4" id="ma">
            <b> Efector Habitual </b>
           </td>
         </tr>
         
         <tr id="tapa" style="display:<?=$tapa_ver?>">
         	<td align="right" width="20%" colspan="2">
				<b><font color="Red">*</font>Efector Habitual:</b>
			</td>
			<td align="left" width="30%" colspan="2">			 	
			 <select name=cuie Style="width:300px" 
        		onKeypress="buscar_combo(this);"
				onblur="borrar_buffer();"
				onchange="borrar_buffer();" 
				 <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?>>
                             <? $consulta_m=""; if (($id_planilla) && ($tipo_transaccion == "M") && strtoupper($prov_uso)=='MISIONES' && $grupo_remediar=='s'){ $consulta_m=" where cuie='$cuie'";}else{?>
			 <option value=-1>Seleccione</option>
			 <? }
			 $sql= "select * from facturacion.smiefectores $consulta_m order by cuie";
			 $res_efectores=sql($sql) or fin_pagina();
			 while (!$res_efectores->EOF){ 
			 	$cuiel=$res_efectores->fields['cuie'];
			    $nombre_efector=$res_efectores->fields['nombreefector'];
			    
			    ?>
				<option value='<?=$cuiel?>' <?if ($cuie==$cuiel) echo "selected"?> ><?=$nombre_efector?></option>
			    <?
			    $res_efectores->movenext();
			    }?>
			</select><? if((!$id_planilla) || $grupo_remediar!='s'){?><button onclick="window.open('busca_efector.php?qkmpo=cuie&grupo_remediar=<?=$grupo_remediar?>','Buscar','dependent:yes,width=900,height=700,top=1,left=60,scrollbars=yes');">b</button><?}?>
                                 
			</td>
         		    
		</tr>

		 <tr id="tapa" style="display:<?=$tapa_ver?>">	           
           <td align="center" colspan="4" id="ma">
            <b> Datos del Domicilio </b>
           </td>
         </tr>
         
         <tr id="tapa" style="display:<?=$tapa_ver?>">
         	<td align="right">
         	  <b><font color="Red">*</font>Calle:</b>
         	</td>         	
            <td align='left'>
              <input type="text" size="30" value="<?=$calle?>" name="calle" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?> maxlength="40">
            </td>
           	<td align="right">
         	  <b><font color="Red">*</font>Numero Calle:</b><input type="text" size="5" value="<?=$numero_calle?>" name="numero_calle" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?> maxlength="5">
         	</td>         	
            <td align='left'>
			  <b>Piso:</b><input type="text" size="5" value="<?=$piso?>" name="piso" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?> maxlength="2">         	
            </td>
         </tr>  
         
         <tr id="tapa" style="display:<?=$tapa_ver?>">
         	<td align="right">
         	  <b>Depto:</b>
         	  <input type="text" size="10" value="<?=$dpto?>" name="dpto" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?> maxlength="3">
         	</td>         	
            <td align='left'>
			  <b>Mz:</b>
         	  <input type="text" size="10" value="<?=$manzana?>" name="manzana" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?> maxlength="30">         	
            </td>
         	<td align="right">
         	  <b>Entre Calle:</b><input type="text" size="10" value="<?=$entre_calle_1?>" name="entre_calle_1" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?> maxlength="40">
         	</td>         	
            <td align='left'>
			  <b>Entre Calle:</b>
         	  <input type="text" size="10" value="<?=$entre_calle_2?>" name="entre_calle_2" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?> maxlength="40">         	
            </td>         	
         </tr>  
         
         <tr id="tapa" style="display:<?=$tapa_ver?>">
         	<td align="right">
         	  <b>Telefono:</b>
         	</td>         	
            <td align='left'>
         	  <input type="text" size="30" value="<?=$telefono?>" name="telefono" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?> maxlength="30">         	
            </td></tr>
         	 <tr id="tapa" style="display:<?=$tapa_ver?>">
    <td align="right">
    <b>Departamento:</b> <input type="hidden" name="departamenton" value="<?=$departamenton?>"> 
    </td>
    <td align="left">
    <select id="departamento" name="departamento" onchange="showdepartamento();" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?>><?php echo $departamento;?></select>
    </td>
    <td align="right">
    <b>Localidad:</b><input type="hidden" name="localidadn" value="<?=$localidadn?>">
    </td>
    <td align="left">
    <select id="localidad" name="localidad" onchange="showlocalidad();" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?>><?php echo $opciones2;?></select>
    </td>
    </tr>
    <tr id="tapa" style="display:<?=$tapa_ver?>">
    <td align="right">
         	  <b>Codigo Postal:</b> <input type="hidden" name="cod_posn" value="<?=$cod_posn?>"> 
         	</td>         
         	 <td align='left'>	
           <select id="cod_pos" name="cod_pos" onchange="showcodpos();" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?>><?php echo $opciones5; ?></select>
               </td>
    <td align="right">
    <b>Municipio:</b><input type="hidden" name="municipion" value="<?=$municipion?>">
    </td>
    <td align="left">
    <select id="municipio" name="municipio" onchange="document.all.b_barrio.disabled=false; showmunicipio();" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?>><?php echo $opciones3; ?></select>
    </td>
    
    </tr>
 
          	<tr id="tapa" style="display:<?=$tapa_ver?>">
    <td align="right">
    <b>Barrio:</b><input type="hidden" name="barrion" value="<?=$barrion?>">
    </td>
    <td align="left" colspan="2"><? $d_b_b='disabled'; if((!$id_planilla) || (($id_planilla) && $tipo_transaccion == "M")){ 
						if(($id_planilla) && $tipo_transaccion == "M"){$d_b_b='';}?>
			<button name="b_barrio" <?=$d_b_b?> onclick="window.open('busca_barrio.php?muni='+document.all.municipio.value+'&id_planilla='+document.all.id_planilla.value,'Buscar','dependent:yes,width=900,height=700,top=1,left=60,scrollbars=yes');" >b</button><?}?>	
    <select id="barrio" name="barrio" onchange="showbarrio();" <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?>><?php echo $opciones4; ?></select>
    </td>        
         </tr>
                       
         <tr id="tapa" style="display:<?=$tapa_ver?>">	           
           <td align="center" colspan="4" id="ma">
            <b> Observaciones </b>
           </td>        
         </tr>
         
         <tr align="center" id="tapa" style="display:<?=$tapa_ver?>">
         	<td align="right">
         	  <b>Observaciones:</b>
         	</td>         	
            <td align='left' colspan="3">
              <textarea cols='80' rows='4' name='observaciones' <?php if (($id_planilla) and ($tipo_transaccion != "M"))echo "disabled"?>> <?=$observaciones;?> </textarea>
            </td>
         </tr>   
                    
        </table>
      </td>      
     </tr> 
   

   <?if (!($id_planilla)){?>
	 
	 <tr id="mo" id="tapa" style="display:<?=$tapa_ver?>">
  		<td align=center colspan="2">
  			<b>Guardar Planilla</b>
  		</td>
  	</tr>  
  	 <tr align="center" id="tapa" style="display:<?=$tapa_ver?>">
	 	<td>
	 		<b><font size="0" color="Red">Nota: Verifique todos los datos antes de guardar</font> </b>
	 	</td>
	</tr>
      <tr align="center" id="tapa" style="display:<?=$tapa_ver?>">
       <td>
        <input type='submit' name='guardar' value='Guardar Planilla' onclick="return control_nuevos();"
         title="Guardar datos de la Planilla" >
       </td>
      </tr>
     
     <?}?>
     
 </table> 
     </td>
 </tr>
<?if ($id_planilla){?>
<table class="bordes" align="center" width="100%">
		 <tr align="center" id="sub_tabla">
		 	<td>	
		 		Editar DATO   
		 	</td>
		 </tr>
		 <tr align="center">
		 	<td>
		 		<b><font size="0" color="Red">Nota: Verifique todos los datos antes de guardar</font> </b>
		 	</td>
		 </tr>
		 
		 <tr>
		    <td align="center">
	          <input type="submit" name="guardar_editar" value="Guardar" title="Guardar"  style="width:130px" <?php if ($tipo_transaccion != "M") echo "disabled"?> onclick="return control_nuevos();">&nbsp;&nbsp;
		      <input type="button" name="cancelar_editar" value="Cancelar" title="Cancelar Edicion" disabled style="width:130px" onclick="document.location.reload()">
                      <? //echo $estado_envio.'***'.strtoupper($usuario_carga).'***'.substr(strtoupper($_ses_user['id']),0,9).'***'.$tipo_transaccion;
                      if (( $estado_envio=='p' && strtoupper($usuario_carga) != substr(strtoupper($_ses_user['id']),0,9)) && ($tipo_transaccion != "B")) $permiso="";
                       else $permiso="disabled";
                        if($estado_nuevo){ ?>
                      <input type="submit" name="guardar" value="Pasar a No Enviados" title="Pasar a No Enviados"  style="width:130px" <?=$permiso?>>&nbsp;&nbsp;

                        <?} if($remediar){?>
                      <input type=button name="carga_remediar" value="Remediar+Redes" onclick="window.open('<?=encode_link("../remediar/remediar_admin.php",array("estado_envio"=>$estado_envio,"clave_beneficiario"=>$clave_beneficiario,"sexo"=>$sexo,"fecha_nac"=>$fecha_nac,"vremediar"=>'s',"pagina"=>"ins_admin_old.php"))?>','Remediar','dependent:yes,width=900,height=700,top=1,left=60,scrollbars=yes');" title="Carga Remediar + Redes" <?if ($tipo_transaccion == "B") echo "disabled"?>>&nbsp;
                        <?}?>
		      <?if (permisos_check("inicio","permiso_borrar")) $permiso="";
			  else $permiso="disabled";?>
		      <input type="submit" name="borrar" value="Borrar" style="width:130px" <?=$permiso?> <?php if ($tipo_transaccion != "B") echo "disabled"?>>
		    </td>
		 </tr> 
	 </table>	
	 <br>
	 <?}?>
 <tr><td><table width=100% align="center" class="bordes">
  <tr align="center">
   <td>
     <input type=button name="volver" value="Volver" onclick="document.location='ins_listado_old.php'"title="Volver al Listado" style="width:150px">     
   </td>
  </tr>
 
 </table></td></tr>
 
 
 </table>
</form>
<script>
    //(($id_planilla) and ($tipo_transaccion != "M"))
if  (!(document.all.id_planilla.value!='' && document.all.tipo_transaccion.value!='M')){
    var campo_focus=document.all.campo_actual.value;
    if(campo_focus==''){
        document.getElementById('campo_actual').value='num_doc';
        campo_focus='num_doc';
    }
    document.getElementById(campo_focus).focus();
}
</script>
 
<?=fin_pagina();// aca termino ?>
