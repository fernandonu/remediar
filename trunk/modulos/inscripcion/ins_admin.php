<?
require_once ("../../config.php");
include_once('lib_inscripcion.php');

extract($_POST, EXTR_SKIP);
if ($parametros)
    extract($parametros, EXTR_OVERWRITE);
cargar_calendario();

($_POST['anio_mayor_nivel'] == '') ? $anio_mayor_nivel = 0 : $anio_mayor_nivel = $_POST['anio_mayor_nivel'];
($_POST['anio_mayor_nivel_madre'] == '') ? $anio_mayor_nivel_madre = 0 : $anio_mayor_nivel_madre = $_POST['anio_mayor_nivel_madre'];
($_POST['fecha_nac'] == '') ? $fecha_nac = '' : $fecha_nac = $_POST['fecha_nac'];
($_POST['fum'] == '') ? $fum = '30/12/1899' : $fum = $_POST['fum'];
($_POST['fecha_diagnostico_embarazo'] == '' || $_POST['fecha_diagnostico_embarazo'] == '0') ? $fecha_diagnostico_embarazo = date("d/m/Y") : $fecha_diagnostico_embarazo = $_POST['fecha_diagnostico_embarazo'];
($_POST['fecha_probable_parto'] == '' || $_POST['fecha_probable_parto'] == '0') ? $fecha_probable_parto = date("d/m/Y") : $fecha_probable_parto = $_POST['fecha_probable_parto'];
($_POST['fecha_efectiva_parto'] == '' || $_POST['fecha_efectiva_parto'] == '0') ? $fecha_efectiva_parto = date("d/m/Y") : $fecha_efectiva_parto = $_POST['fecha_efectiva_parto'];
($_POST['fecha_inscripcion'] == '') ? $fecha_inscripcion = date("d/m/Y") : $fecha_inscripcion = $_POST['fecha_inscripcion'];
$edad = $_POST['edades'];

$estado_intermedio = '';
$estado_envio_ins = 'n';
$ape_nom = '';
$remediar = '';
$uad_benef = '';
$prov_uso = '';
$agentes_sql = '';
$agentes_sql2 = '';
$agentes = 'n';
$queryfunciones = "SELECT accion,nombre
			FROM sistema.funciones
                    where habilitado='s' and (pagina='ins_admin' or pagina='all')";
$res_fun = sql($queryfunciones) or fin_pagina();
while (!$res_fun->EOF) {
    if ($res_fun->fields['nombre'] == 'Guarda Remediar') {
        $remediar = 's'; //$res_fun->fields['accion'];
    } elseif ($res_fun->fields['nombre'] == 'Estados') {
        $estado_nuevo = 's'; //$res_fun->fields['accion'];
        $estado_intermedio = "estado_envio='p',";
        $estado_envio_ins = 'p';
    } elseif ($res_fun->fields['nombre'] == 'Otros Ape-Nom') {
        $ape_nom = 's';
        $ape_nom_update = "";
    } elseif ($res_fun->fields['nombre'] == 'Uad Benef') {
        $uad_benef = 's';
    } elseif ($res_fun->fields['nombre'] == 'Provincia') {
        $prov_uso = $res_fun->fields['accion'];
    } elseif ($res_fun->fields['nombre'] == 'Datos Agente') {
        $agentes = $res_fun->fields['accion'];
    }
    $res_fun->movenext();
}

if ($id_planilla) {
    $queryCategoria = "SELECT beneficiarios.*, smiefectores.nombreefector, smiefectores.cuie
			FROM uad.beneficiarios
			left join facturacion.smiefectores on beneficiarios.cuie_ea=smiefectores.cuie 
  	where id_beneficiarios=$id_planilla";

    $resultado = sql($queryCategoria, "Error al traer el Comprobantes") or fin_pagina();
    if ($resultado->fields['id_categoria'] == 7) {
        $id_categoria = 6;
    } else {
        $id_categoria = $resultado->fields['id_categoria'];
    }
    $semanas_embarazo = $resultado->fields['semanas_embarazo'];
    $pais_nac = $resultado->fields['pais_nac'];
    $paisn = $resultado->fields['pais_nac'];
    $departamento = $resultado->fields['departamento'];
    $localidad = $resultado->fields['localidad'];
    $municipio = $resultado->fields['municipio'];
    $barrio = $resultado->fields['barrio'];
    $barrion = $resultado->fields['barrio'];
    $estudios = $resultado->fields['estudios'];
    $anio_mayor_nivel = $resultado->fields['anio_mayor_nivel'];
    $indigena = $resultado->fields['indigena'];
    $id_tribu = $resultado->fields['id_tribu'];
    $id_lengua = $resultado->fields['id_lengua'];
    $responsable = $resultado->fields['responsable'];
    $menor_convive_con_adulto = $resultado->fields['menor_convive_con_adulto'];
    $tipo_doc_madre = $resultado->fields['tipo_doc_madre'];
    $nro_doc_madre = $resultado->fields['nro_doc_madre'];
    $apellido_madre = $resultado->fields['apellido_madre'];
    $nombre_madre = $resultado->fields['nombre_madre'];
    $estudios_madre = $resultado->fields['estudios_madre'];
    $anio_mayor_nivel_madre = $resultado->fields['anio_mayor_nivel_madre'];
    $sexo = $resultado->fields['sexo'];
    $alfabeta = $resultado->fields['alfabeta'];
    $estudios = $resultado->fields['estudios'];
    $clave_beneficiario = $resultado->fields['clave_beneficiario'];
    $trans = $resultado->fields['tipo_transaccion'];
    $mail = $resultado->fields['mail'];
    $celular = $resultado->fields['celular'];
    $otrotel = $resultado->fields['otrotel'];
    $estadoest = $resultado->fields['estadoest'];
    $discv = $resultado->fields['discv'];
    $disca = $resultado->fields['disca'];
    $discmo = $resultado->fields['discmo'];
    $discme = $resultado->fields['discme'];
    $otradisc = $resultado->fields['otradisc'];
    $obsgenerales = $resultado->fields['obsgenerales'];
    $estadoest_madre = $resultado->fields['estadoest'];
    $menor_embarazada = $resultado->fields['menor_embarazada'];
    $edad = $resultado->fields['edades'];
    $apellidoagente = $resultado->fields['apellidoagente'];
    $nombreagente = $resultado->fields['nombreagente'];
    $cuie_agente = $resultado->fields['centro_inscriptor'];
    $num_doc_agente = $resultado->fields['dni_agente'];

    // Marca Borrado al beneficiario.
    if ($trans == 'B') {
        $trans = "Borrado";
    }
}


// comenzamos sin mostrar nada referente a embarazo, menor convive o no con adulto.
if (($id_categoria == '') && ($edad == '')) {
    $embarazada = none;
    $datos_resp = none;
    $mva1 = none;
    $memb = none;
    $menor_embarazada = none;
} //FIN
// Femenino mayor de 19 años, muestra la información de embarazo pero no la información de menor vive con adulto
if (($id_categoria == '6') && ($sexo == 'F')) {
    $embarazada = inline;
    $datos_resp = none;
    $mva1 = none;
    $memb = none;
    if (!$id_planilla) {
        if ($_POST['semanas_embarazo'] == "") {
            $semanas_embarazo = 0;
        } else {
            $semanas_embarazo = $_POST['semanas_embarazo'];
        }
    }// Femenino menor de 9 años, muestra la información de menor vive con adulto y no la de embarazo
} elseif (($id_categoria == '5') && ($sexo == 'F') && ($edad <= '9')) {
    $embarazada = none;
    $mva1 = inline;
    $datos_resp = inline;
    $memb = none;
    $menor_embarazada = none;
}// Femenino menor de 10 años hasta 18 años, muestra la información de menor vive con adulto y pregunta si esta o no embarazada
elseif (($id_categoria == '5') && ($sexo == 'F') && ($edad >= '10')) {

    $embarazada = none;
    $mva1 = inline;
    $datos_resp = inline;
    $memb = inline;
    //Si esta embarazada muestra la información de embarazo.
    if ($menor_embarazada != 'N') {
        $embarazada = inline;
        if ($_POST['semanas_embarazo'] == "") {
            $semanas_embarazo = 0;
        } else {
            $semanas_embarazo = $_POST['semanas_embarazo'];
        }
    } // Si no esta embarazada no la muestra.
}// FIN
// Masculino menor de 19 años, muestra la información de menor vive con adulto y no la de embarazo
if (($id_categoria == '5') && ($sexo == 'M')) {
    $mva1 = inline;
    $datos_resp = inline;
    $embarazada = none;
    $memb = none;
} // Masculino mayor de 19 años, no muesta la información de embarazo ni tampoco la de menor vive con adulto.
elseif (($id_categoria == '6') && ($sexo == 'M')) {
    $embarazada = none;
    $datos_resp = none;
    $mva1 = none;
    $memb = none;
} // FIN
// Update de Beneficiarios
if ($_POST['guardar_editar'] == "Guardar") {
    $db->StartTrans();

    if ($tipo_ficha == 1) {
        $tipo_ficha = 3;
    } elseif ($tipo_ficha != 3) {
        $tipo_ficha = 2;
    }
    $fecha_carga = date("Y-m-d H:m:s");
    $usuario = $_ses_user['id'];
    $usuario = substr($usuario, 0, 9);

    $fecha_nac = Fecha_db($fecha_nac);
    $fum = Fecha_db($fum);
    $fecha_diagnostico_embarazo = Fecha_db($fecha_diagnostico_embarazo);
    if ($fecha_diagnostico_embarazo == "") {
        $fecha_diagnostico_embarazo = "1980-01-01";
    }
    if ($_POST['semanas_embarazo'] == "") {
        $semanas_embarazo = 0;
    } else {
        $semanas_embarazo = $_POST['semanas_embarazo'];
    }
    $fecha_probable_parto = Fecha_db($fecha_probable_parto);
    $clave_beneficiario = $_POST['clave_beneficiario'];
    $alfabeta = $_POST['alfabeta'];
    $sexo = $_POST['sexo'];
    $pais_nac = $_POST['pais_nac'];
    $paisn = $_POST['pais_nac'];
    $indigena = $_POST['indigena'];
    $id_tribu = $_POST['id_tribu'];
    $id_lengua = $_POST['id_lengua'];
    $departamento = $_POST['departamento'];
    $localidad = $_POST['localidad'];
    $municipio = $_POST['municipio'];
    $barrio = $_POST['barrio'];
    $barrion = $_POST['barrio'];
    $estudios = $_POST['estudios'];
    $id_categoria = $_POST['id_categoria'];
    ($_POST['anio_mayor_nivel'] == '') ? $anio_mayor_nivel = 0 : $anio_mayor_nivel = $_POST['anio_mayor_nivel'];
    $responsable = $_POST['responsable'];
    $menor_convive_con_adulto = $_POST['menor_convive_con_adulto'];
    $tipo_doc_madre = $_POST['tipo_doc_madre'];
    $nro_doc_madre = $_POST['nro_doc_madre'];
    $apellido_madre = $_POST['apellido_madre'];
    $nombre_madre = $_POST['nombre_madre'];
    $estudios_madre = $_POST['estudios_madre'];
    ($_POST['anio_mayor_nivel_madre'] == '') ? $anio_mayor_nivel_madre = 0 : $anio_mayor_nivel_madre = $_POST['anio_mayor_nivel_madre'];
    ($_POST['score_riesgo'] == '') ? $score_riesgo = 0 : $score_riesgo = $_POST['score_riesgo'];
    $mail = $_POST['mail'];
    $celular = $_POST['celular'];
    $otrotel = $_POST['otrotel'];
    $estadoest = $_POST['estadoest'];
    $discv = $_POST['discv'];
    $disca = $_POST['disca'];
    $discmo = $_POST['discmo'];
    $discme = $_POST['discme'];
    $otradisc = $_POST['otradisc'];
    $obsgenerales = $_POST['obsgenerales'];
    $estadoest_madre = $_POST['estadoest_madre'];
    $menor_embarazada = $_POST['menor_embarazada'];
    $apellidoagente = $_POST['apellidoagente'];
    $nombreagente = $_POST['nombreagente'];
    $num_doc_agente = $_POST['num_doc_agente'];
    $cuie_agente = $_POST['cuie_agente'];
    $edad = $_POST['edades'];
    if ($_POST['edades'] == '') {
        $edad = 0;
    } else {
        $edad = $_POST['edades'];
    }
    $fecha_inscripcion = Fecha_db($fecha_inscripcion);

    if ($ape_nom == 's') {
        $ape_nom_update = "nombre_benef=upper('$nombre'),nombre_benef_otro=upper('$nombre_otro'),
                 apellido_benef=upper('$apellido'),apellido_benef_otro=upper('$apellido_otro'),";
    } else {
        $ape_nom_update = "nombre_benef=upper('$nombre'),
             apellido_benef=upper('$apellido'),";
    }
    if ($agentes == 's') {
        $agentes_sql = ",apellidoagente=upper('$apellidoagente'),nombreagente=upper('$nombreagente'),centro_inscriptor=upper('$cuie_agente'),dni_agente=upper('$num_doc_agente') ";
    }
    $verifica_insercion = 'n';
    //Menor de 10 años hasta 18 años con responsable madre y embarazada (Update)
    if (($responsable == 'MADRE') && ($menor_embarazada == 'S')) {
        $verifica_insercion = 's';
        $query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie',$ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',
             anio_mayor_nivel='$anio_mayor_nivel',indigena=upper('$indigena'),id_tribu=upper('$id_tribu'),id_lengua=$id_lengua,alfabeta=upper('$alfabeta'),
             estudios=upper('$estudios'),id_categoria=$id_categoria,fecha_diagnostico_embarazo='$fecha_diagnostico_embarazo',
             semanas_embarazo='$semanas_embarazo',fecha_probable_parto='$fecha_probable_parto', 
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamenton'), localidad=upper('$localidadn'), municipio=upper('$municipion'), 
             barrio=upper('$barrion'),telefono='$telefono',cod_pos='$cod_posn',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             score_riesgo='$score_riesgo',pais_nac=upper('$paisn'),fecha_carga='$fecha_carga', usuario_carga=upper('$usuario'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_madre=upper('$nombre_madre'),anio_mayor_nivel_madre=$anio_mayor_nivel_madre,alfabeta_madre=upper('$alfabeta_madre'),
             estudios_madre=upper('$estudios_madre'), apellido_madre=upper('$apellido_madre'), nro_doc_madre='$nro_doc_madre', 
             tipo_doc_madre=upper('$tipo_doc_madre'),nombre_padre='',anio_mayor_nivel_padre='0',alfabeta_padre='',estudios_padre='', apellido_padre='', 
             nro_doc_padre='',tipo_doc_padre='', tipo_transaccion='M', mail=upper('$mail'), celular='$celular',otrotel='$otrotel',
             estadoest=upper('$estadoest'), fum='$fum',discv=upper('$discv'),disca=upper('$disca'),discmo=upper('$discmo'),
             discme=upper('$discme'),otradisc=upper('$otradisc'), obsgenerales=upper('$obsgenerales'),estadoest_madre=upper('$estadoest_madre'),
             estadoest_padre='',edades=$edad,sexo=upper('$sexo'),tipo_doc_tutor='',apellido_tutor='',nombre_tutor=''
			 ,alfabeta_tutor='',estudios_tutor='',estadoest_tutor='' ,anio_mayor_nivel_tutor='0', nro_doc_tutor='' $agentes_sql
                       
             where id_beneficiarios=" . $id_planilla;
    }//Menor de 10 años hasta 18 años con responsable padre y embarazada (Update)
    elseif (($responsable == 'PADRE') && ($menor_embarazada == 'S')) {
        $verifica_insercion = 's';
        $query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',
             anio_mayor_nivel='$anio_mayor_nivel',indigena=upper('$indigena'),id_tribu=upper('$id_tribu'),id_lengua=$id_lengua,alfabeta=upper('$alfabeta'),
             estudios=upper('$estudios'),id_categoria=$id_categoria,fecha_diagnostico_embarazo='$fecha_diagnostico_embarazo',
             semanas_embarazo='$semanas_embarazo',fecha_probable_parto='$fecha_probable_parto', fecha_efectiva_parto='1899-12-30',
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamenton'), localidad=upper('$localidadn'), municipio=upper('$municipion'), 
             barrio=upper('$barrion'),telefono='$telefono',cod_pos='$cod_posn',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             score_riesgo='$score_riesgo' ,pais_nac=upper('$paisn'),fecha_carga='$fecha_carga', usuario_carga=upper('$usuario'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_padre=upper('$nombre_madre'),anio_mayor_nivel_padre=$anio_mayor_nivel_madre,alfabeta_padre=upper('$alfabeta_madre'),
             estudios_padre=upper('$estudios_madre'), apellido_padre=upper('$apellido_madre'), nro_doc_padre='$nro_doc_madre', 
             tipo_doc_padre=upper('$tipo_doc_madre'),nombre_madre='',anio_mayor_nivel_madre='0',alfabeta_madre='',estudios_madre='', 
             apellido_madre='', nro_doc_madre='',tipo_doc_madre='', tipo_transaccion='M', mail=upper('$mail'), 
             celular='$celular',otrotel='$otrotel',estadoest=upper('$estadoest'), fum='$fum',discv=upper('$discv'),disca=upper('$disca'),discmo=upper('$discmo'),
             discme=upper('$discme'),otradisc=upper('$otradisc'), obsgenerales=upper('$obsgenerales'),estadoest_padre=upper('$estadoest_madre'),estadoest_madre='' ,edades=$edad,sexo=upper('$sexo') ,tipo_doc_tutor='',apellido_tutor='',nombre_tutor=''
			 ,alfabeta_tutor='',estudios_tutor='',estadoest_tutor='' ,anio_mayor_nivel_tutor='0', nro_doc_tutor=''
			  $agentes_sql                             
            
         where id_beneficiarios=" . $id_planilla;
    }//FIN
    //Menor de 10 años hasta 18 años con responsable madre, embarazada e información envíada. (Update)
    if (($responsable == 'MADRE') && ($estado_envio == 'e') && ($menor_embarazada == 'S')) {
        $verifica_insercion = 's';
        $query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',
             anio_mayor_nivel='$anio_mayor_nivel',indigena=upper('$indigena'),id_tribu=upper('$id_tribu'),id_lengua=$id_lengua,alfabeta=upper('$alfabeta'),
             estudios=upper('$estudios'),id_categoria=$id_categoria,fecha_diagnostico_embarazo='$fecha_diagnostico_embarazo',
             semanas_embarazo='$semanas_embarazo',fecha_probable_parto='$fecha_probable_parto',
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamenton'), localidad=upper('$localidadn'), municipio=upper('$municipion'), 
             barrio=upper('$barrion'),telefono='$telefono',cod_pos='$cod_posn',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             score_riesgo='$score_riesgo' , pais_nac=upper('$paisn'),fecha_carga='$fecha_carga', usuario_carga=upper('$usuario'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_madre=upper('$nombre_madre'),anio_mayor_nivel_madre=$anio_mayor_nivel_madre,alfabeta_madre=upper('$alfabeta_madre'),
             estudios_madre=upper('$estudios_madre'), apellido_madre=upper('$apellido_madre'), nro_doc_madre='$nro_doc_madre', 
             tipo_doc_madre=upper('$tipo_doc_madre'),nombre_padre='',anio_mayor_nivel_padre='0',alfabeta_padre='',estudios_padre='', apellido_padre='', 
             nro_doc_padre='',tipo_doc_padre='', tipo_transaccion='M', 
             estado_envio='n', mail=upper('$mail'), celular='$celular',otrotel='$otrotel',
             estadoest=upper('$estadoest'), fum='$fum',discv=upper('$discv'),disca=upper('$disca'),discmo=upper('$discmo'),
             discme=upper('$discme'),otradisc=upper('$otradisc'), obsgenerales=upper('$obsgenerales'),estadoest_madre=upper('$estadoest_madre'),
             estadoest_padre='' ,edades=$edad,sexo=upper('$sexo') ,tipo_doc_tutor='',apellido_tutor='',nombre_tutor=''
			 ,alfabeta_tutor='',estudios_tutor='',estadoest_tutor='' ,anio_mayor_nivel_tutor='0', nro_doc_tutor='' $agentes_sql
                       
             where id_beneficiarios=" . $id_planilla;
    }//Menor de 10 años hasta 18 años con responsable padre, embarazada e información envíada. (Update)
    elseif (($responsable == 'PADRE') && ($estado_envio == 'e') && ($menor_embarazada == 'S')) {
        $verifica_insercion = 's';
        $query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',
             anio_mayor_nivel='$anio_mayor_nivel',indigena=upper('$indigena'),id_tribu=upper('$id_tribu'),id_lengua=$id_lengua,alfabeta=upper('$alfabeta'),
             estudios=upper('$estudios'),id_categoria=$id_categoria,fecha_diagnostico_embarazo='$fecha_diagnostico_embarazo',
             semanas_embarazo='$semanas_embarazo',fecha_probable_parto='$fecha_probable_parto',
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamenton'), localidad=upper('$localidadn'), municipio=upper('$municipion'), 
             barrio=upper('$barrion'),telefono='$telefono',cod_pos='$cod_posn',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             score_riesgo='$score_riesgo' , pais_nac=upper('$paisn'),fecha_carga='$fecha_carga', usuario_carga=upper('$usuario'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_padre=upper('$nombre_madre'),anio_mayor_nivel_padre=$anio_mayor_nivel_madre,alfabeta_padre=upper('$alfabeta_madre'),
             estudios_padre=upper('$estudios_madre'), apellido_padre=upper('$apellido_madre'), nro_doc_padre='$nro_doc_madre', 
             tipo_doc_padre=upper('$tipo_doc_madre'),nombre_madre='',anio_mayor_nivel_madre='0',alfabeta_madre='',estudios_madre='', 
             apellido_madre='', nro_doc_madre='',tipo_doc_madre='',tipo_transaccion='M', estado_envio='n', mail=upper('$mail'), 
             celular='$celular',otrotel='$otrotel',estadoest=upper('$estadoest'), fum='$fum',discv=upper('$discv'),disca=upper('$disca'),discmo=upper('$discmo'),
             discme=upper('$discme'),otradisc=upper('$otradisc'), obsgenerales=upper('$obsgenerales'),estadoest_padre=upper('$estadoest_madre'),
             estadoest_madre='',edades=$edad,sexo=upper('$sexo')  ,tipo_doc_tutor='',apellido_tutor='',nombre_tutor=''
			 ,alfabeta_tutor='',estudios_tutor='',estadoest_tutor='' ,anio_mayor_nivel_tutor='0', nro_doc_tutor=''  $agentes_sql                            
              
         where id_beneficiarios=" . $id_planilla;
    }//FIN
    elseif (($responsable == 'TUTOR') && ($estado_envio == 'e') && ($menor_embarazada == 'S')) {
        $verifica_insercion = 's';
        $query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_updat
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',
             anio_mayor_nivel='$anio_mayor_nivel',indigena=upper('$indigena'),id_tribu=upper('$id_tribu'),id_lengua=$id_lengua,alfabeta=upper('$alfabeta'),
             estudios=upper('$estudios'),id_categoria=$id_categoria,fecha_diagnostico_embarazo='$fecha_diagnostico_embarazo',
             semanas_embarazo='$semanas_embarazo',fecha_probable_parto='$fecha_probable_parto',
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamenton'), localidad=upper('$localidadn'), municipio=upper('$municipion'), 
             barrio=upper('$barrion'),telefono='$telefono',cod_pos='$cod_posn',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             score_riesgo='$score_riesgo' ,pais_nac=upper('$paisn'),fecha_carga='$fecha_carga', usuario_carga=upper('$usuario'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_tutor=upper('$nombre_madre'),anio_mayor_nivel_tutor=$anio_mayor_nivel_madre,alfabeta_tutor=upper('$alfabeta_madre')
			 ,estudios_tutor=upper('$estudios_madre'),apellido_tutor=upper('$apellido_madre'), nro_doc_tutor='$nro_doc_madre'
			 , tipo_doc_tutor=upper('$tipo_doc_madre'),nombre_madre='',anio_mayor_nivel_madre='0', alfabeta_madre='',estudios_madre=''
			 ,apellido_madre='' , nro_doc_madre='',tipo_doc_madre='', tipo_transaccion='M',estado_envio='n',  mail=upper('$mail')
			 ,celular='$celular',otrotel='$otrotel', fum='$fum',discv=upper('$discv'),disca=upper('$disca'),discmo=upper('$discmo'),
             discme=upper('$discme'),otradisc=upper('$otradisc'), obsgenerales=upper('$obsgenerales'),estadoest_tutor=upper('$estadoest_madre')
			 ,estadoest_padre='', 	 estadoest_madre='' ,   nombre_padre='', apellido_padre='', nro_doc_padre='',tipo_doc_padre='',
             anio_mayor_nivel_padre='0',alfabeta_padre='', estudios_padre='', estadoest=upper('$estadoest'),
			 menor_embarazada=upper('$menor_embarazada'),edades=$edad,sexo=upper('$sexo') $agentes_sql             
         where id_beneficiarios=" . $id_planilla;
    }
//Menor de 18 años con responsable madre y no embarazada (Update)
    if (($responsable == 'MADRE') && ($menor_embarazada == 'N')) {
        $verifica_insercion = 's';
        $query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',
             anio_mayor_nivel='$anio_mayor_nivel',indigena=upper('$indigena'),id_tribu=upper('$id_tribu'),id_lengua=$id_lengua,alfabeta=upper('$alfabeta'),
             estudios=upper('$estudios'),id_categoria=$id_categoria,
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamenton'), localidad=upper('$localidadn'), municipio=upper('$municipion'), 
             barrio=upper('$barrion'),telefono='$telefono',cod_pos='$cod_posn',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             pais_nac=upper('$paisn'),fecha_carga='$fecha_carga', usuario_carga=upper('$usuario'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_madre=upper('$nombre_madre'),anio_mayor_nivel_madre=$anio_mayor_nivel_madre,alfabeta_madre=upper('$alfabeta_madre'),
             estudios_madre=upper('$estudios_madre'), apellido_madre=upper('$apellido_madre'), nro_doc_madre='$nro_doc_madre', 
             tipo_doc_madre=upper('$tipo_doc_madre'),nombre_padre='',anio_mayor_nivel_padre='0',alfabeta_padre='',estudios_padre='', apellido_padre='', 
             nro_doc_padre='',tipo_doc_padre='', tipo_transaccion='M', mail=upper('$mail'), celular='$celular',otrotel='$otrotel',
             estadoest=upper('$estadoest'), discv=upper('$discv'),disca=upper('$disca'),discmo=upper('$discmo'),
             discme=upper('$discme'),otradisc=upper('$otradisc'), obsgenerales=upper('$obsgenerales'),estadoest_madre=upper('$estadoest_madre'),
             estadoest_padre='' ,edades=$edad,sexo=upper('$sexo') ,tipo_doc_tutor='',apellido_tutor='',nombre_tutor=''
			 ,alfabeta_tutor='',estudios_tutor='',estadoest_tutor='' ,anio_mayor_nivel_tutor='0', nro_doc_tutor='' $agentes_sql
                       
             where id_beneficiarios=" . $id_planilla;
    }//Menor de 18 años con responsable padre y no embarazada (Update)
    elseif (($responsable == 'PADRE') && ($menor_embarazada == 'N')) {
        $verifica_insercion = 's';
        $query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',
             anio_mayor_nivel='$anio_mayor_nivel',indigena=upper('$indigena'),id_tribu=upper('$id_tribu'),id_lengua=$id_lengua,alfabeta=upper('$alfabeta'),
             estudios=upper('$estudios'),id_categoria=$id_categoria,
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamenton'), localidad=upper('$localidadn'), municipio=upper('$municipion'), 
             barrio=upper('$barrion'),telefono='$telefono',cod_pos='$cod_posn',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             pais_nac=upper('$paisn'),fecha_carga='$fecha_carga', usuario_carga=upper('$usuario'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_padre=upper('$nombre_madre'),anio_mayor_nivel_padre=$anio_mayor_nivel_madre,alfabeta_padre=upper('$alfabeta_madre'),
             estudios_padre=upper('$estudios_madre'), apellido_padre=upper('$apellido_madre'), nro_doc_padre='$nro_doc_madre', 
             tipo_doc_padre=upper('$tipo_doc_madre'),nombre_madre='',anio_mayor_nivel_madre='0',alfabeta_madre='',estudios_madre='', 
             apellido_madre='', nro_doc_madre='',tipo_doc_madre='', tipo_transaccion='M', mail=upper('$mail'), 
             celular='$celular',otrotel='$otrotel',estadoest=upper('$estadoest'),discv=upper('$discv'),disca=upper('$disca'),discmo=upper('$discmo'),
             discme=upper('$discme'),otradisc=upper('$otradisc'), obsgenerales=upper('$obsgenerales'),estadoest_padre=upper('$estadoest_madre'),estadoest_madre='' ,edades=$edad,sexo=upper('$sexo') ,tipo_doc_tutor='',apellido_tutor='',nombre_tutor=''
			 ,alfabeta_tutor='',estudios_tutor='',estadoest_tutor='' ,anio_mayor_nivel_tutor='0', nro_doc_tutor=''	  $agentes_sql                                  
              
         where id_beneficiarios=" . $id_planilla;
    }//FIN
    elseif (($responsable == 'TUTOR') && ($menor_embarazada == 'N')) {
        $verifica_insercion = 's';
        $query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',
             anio_mayor_nivel='$anio_mayor_nivel',indigena=upper('$indigena'),id_tribu=upper('$id_tribu'),id_lengua=$id_lengua,alfabeta=upper('$alfabeta'),
             estudios=upper('$estudios'),id_categoria=$id_categoria,
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamenton'), localidad=upper('$localidadn'), municipio=upper('$municipion'), 
             barrio=upper('$barrion'),telefono='$telefono',cod_pos='$cod_posn',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             pais_nac=upper('$paisn'),fecha_carga='$fecha_carga', usuario_carga=upper('$usuario'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_tutor=upper('$nombre_madre'),anio_mayor_nivel_tutor=$anio_mayor_nivel_madre,alfabeta_tutor=upper('$alfabeta_madre'),
             estudios_tutor=upper('$estudios_madre'), apellido_tutor=upper('$apellido_madre'), nro_doc_tutor='$nro_doc_madre', 
             tipo_doc_tutor=upper('$tipo_doc_madre'),nombre_madre='',anio_mayor_nivel_madre='0',alfabeta_madre='',estudios_madre='', 
             apellido_madre='', nro_doc_madre='',tipo_doc_madre='', tipo_transaccion='M', mail=upper('$mail'), 
             celular='$celular',otrotel='$otrotel',estadoest=upper('$estadoest'),discv=upper('$discv'),disca=upper('$disca'),discmo=upper('$discmo'),
             discme=upper('$discme'),otradisc=upper('$otradisc'), obsgenerales=upper('$obsgenerales'),estadoest_tutor=upper('$estadoest_madre'),estadoest_madre='' ,edades=$edad,sexo=upper('$sexo')	,tipo_doc_padre='',apellido_padre='',nombre_padre=''
			 ,alfabeta_padre='',estudios_padre='',estadoest_padre=''  ,anio_mayor_nivel_padre='0', nro_doc_padre=''		  $agentes_sql                                  
              
         where id_beneficiarios=" . $id_planilla;
    }//FIN
    //Menor de 18 años con responsable madre, no embarazada e información envíada. (Update)
    if (($responsable == 'MADRE') && ($estado_envio == 'e') && ($menor_embarazada == 'N')) {
        $verifica_insercion = 's';
        $query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',
             anio_mayor_nivel='$anio_mayor_nivel',indigena=upper('$indigena'),id_tribu=upper('$id_tribu'),id_lengua=$id_lengua,alfabeta=upper('$alfabeta'),
             estudios=upper('$estudios'),id_categoria=$id_categoria,
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamenton'), localidad=upper('$localidadn'), municipio=upper('$municipion'), 
             barrio=upper('$barrion'),telefono='$telefono',cod_pos='$cod_posn',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             pais_nac=upper('$paisn'),fecha_carga='$fecha_carga', usuario_carga=upper('$usuario'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_madre=upper('$nombre_madre'),anio_mayor_nivel_madre=$anio_mayor_nivel_madre,alfabeta_madre=upper('$alfabeta_madre'),
             estudios_madre=upper('$estudios_madre'), apellido_madre=upper('$apellido_madre'), nro_doc_madre='$nro_doc_madre', 
             tipo_doc_madre=upper('$tipo_doc_madre'),nombre_padre='',anio_mayor_nivel_padre='0',alfabeta_padre='',estudios_padre='', apellido_padre='', 
             nro_doc_padre='',tipo_doc_padre='', tipo_transaccion='M', 
             estado_envio='n', mail=upper('$mail'), celular='$celular',otrotel='$otrotel',
             estadoest=upper('$estadoest'),discv=upper('$discv'),disca=upper('$disca'),discmo=upper('$discmo'),
             discme=upper('$discme'),otradisc=upper('$otradisc'), obsgenerales=upper('$obsgenerales'),estadoest_madre=upper('$estadoest_madre'),
             estadoest_padre='' ,edades=$edad,sexo=upper('$sexo') ,tipo_doc_tutor='',apellido_tutor='',nombre_tutor=''
			 ,alfabeta_tutor='',estudios_tutor='',estadoest_tutor='' ,anio_mayor_nivel_tutor='0', nro_doc_tutor='' $agentes_sql
                       
             where id_beneficiarios=" . $id_planilla;
    }//Menor 18 años con responsable padre, no embarazada e información envíada. (Update)
    elseif (($responsable == 'PADRE') && ($estado_envio == 'e') && ($menor_embarazada == 'N')) {
        $verifica_insercion = 's';
        $query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',
             anio_mayor_nivel='$anio_mayor_nivel',indigena=upper('$indigena'),id_tribu=upper('$id_tribu'),id_lengua=$id_lengua,alfabeta=upper('$alfabeta'),
             estudios=upper('$estudios'),id_categoria=$id_categoria,
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamenton'), localidad=upper('$localidadn'), municipio=upper('$municipion'), 
             barrio=upper('$barrion'),telefono='$telefono',cod_pos='$cod_posn',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             pais_nac=upper('$paisn'),fecha_carga='$fecha_carga', usuario_carga=upper('$usuario'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_padre=upper('$nombre_madre'),anio_mayor_nivel_padre=$anio_mayor_nivel_madre,alfabeta_padre=upper('$alfabeta_madre'),
             estudios_padre=upper('$estudios_madre'), apellido_padre=upper('$apellido_madre'), nro_doc_padre='$nro_doc_madre', 
             tipo_doc_padre=upper('$tipo_doc_madre'),nombre_madre='',anio_mayor_nivel_madre='0',alfabeta_madre='',estudios_madre='', 
             apellido_madre='', nro_doc_madre='',tipo_doc_madre='',tipo_transaccion='M', estado_envio='n', mail=upper('$mail'), 
             celular='$celular',otrotel='$otrotel',estadoest=upper('$estadoest'),discv=upper('$discv'),disca=upper('$disca'),discmo=upper('$discmo'),
             discme=upper('$discme'),otradisc=upper('$otradisc'), obsgenerales=upper('$obsgenerales'),estadoest_padre=upper('$estadoest_madre'),
             estadoest_madre='' ,edades=$edad,sexo=upper('$sexo')  ,tipo_doc_tutor='',apellido_tutor='',nombre_tutor=''
			 ,alfabeta_tutor='',estudios_tutor='',estadoest_tutor='' ,anio_mayor_nivel_tutor='0', nro_doc_tutor='' $agentes_sql                                    
              
         where id_beneficiarios=" . $id_planilla;
    } //FIN
    elseif (($responsable == 'TUTOR') && ($estado_envio == 'e') && ($menor_embarazada == 'N')) {
        $verifica_insercion = 's';
        $query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',
             anio_mayor_nivel='$anio_mayor_nivel',indigena=upper('$indigena'),id_tribu=upper('$id_tribu'),id_lengua=$id_lengua,alfabeta=upper('$alfabeta'),
             estudios=upper('$estudios'),id_categoria=$id_categoria,
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamenton'), localidad=upper('$localidadn'), municipio=upper('$municipion'), 
             barrio=upper('$barrion'),telefono='$telefono',cod_pos='$cod_posn',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             pais_nac=upper('$paisn'),fecha_carga='$fecha_carga', usuario_carga=upper('$usuario'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_tutor=upper('$nombre_madre'),anio_mayor_nivel_tutor=$anio_mayor_nivel_madre,alfabeta_tutor=upper('$alfabeta_madre'),
             estudios_tutor=upper('$estudios_madre'), apellido_tutor=upper('$apellido_madre'), nro_doc_tutor='$nro_doc_madre', 
             tipo_doc_tutor=upper('$tipo_doc_madre'),nombre_madre='',anio_mayor_nivel_madre='0',alfabeta_madre='',estudios_madre='', 
             apellido_madre='', nro_doc_madre='',tipo_doc_madre='',tipo_transaccion='M', estado_envio='n', mail=upper('$mail'), 
             celular='$celular',otrotel='$otrotel',estadoest=upper('$estadoest'),discv=upper('$discv'),disca=upper('$disca'),discmo=upper('$discmo'),
             discme=upper('$discme'),otradisc=upper('$otradisc'), obsgenerales=upper('$obsgenerales'),estadoest_tutor=upper('$estadoest_madre'),
             estadoest_madre='' ,edades=$edad,sexo=upper('$sexo'),tipo_doc_padre='',apellido_padre='',nombre_padre=''
			 ,alfabeta_padre='',estudios_padre='',estadoest_padre=''  ,anio_mayor_nivel_padre='0', nro_doc_padre='' $agentes_sql                                    
              
         where id_beneficiarios=" . $id_planilla;
    }
    //Menor de 18 años con responsable madre y masculino (Update)
    if (($responsable == 'MADRE') && ($sexo == 'M')) {
        $verifica_insercion = 's';
        $query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',
             anio_mayor_nivel='$anio_mayor_nivel',indigena=upper('$indigena'),id_tribu=upper('$id_tribu'),id_lengua=$id_lengua,alfabeta=upper('$alfabeta'),
             estudios=upper('$estudios'),id_categoria=$id_categoria,
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamenton'), localidad=upper('$localidadn'), municipio=upper('$municipion'), 
             barrio=upper('$barrion'),telefono='$telefono',cod_pos='$cod_posn',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             pais_nac=upper('$paisn'),fecha_carga='$fecha_carga', usuario_carga=upper('$usuario'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_madre=upper('$nombre_madre'),anio_mayor_nivel_madre=$anio_mayor_nivel_madre,alfabeta_madre=upper('$alfabeta_madre'),
             estudios_madre=upper('$estudios_madre'), apellido_madre=upper('$apellido_madre'), nro_doc_madre='$nro_doc_madre', 
             tipo_doc_madre=upper('$tipo_doc_madre'),nombre_padre='',anio_mayor_nivel_padre='0',alfabeta_padre='',estudios_padre='', apellido_padre='', 
             nro_doc_padre='',tipo_doc_padre='', tipo_transaccion='M', mail=upper('$mail'), celular='$celular',otrotel='$otrotel',
             estadoest=upper('$estadoest'), discv=upper('$discv'),disca=upper('$disca'),discmo=upper('$discmo'),
             discme=upper('$discme'),otradisc=upper('$otradisc'), obsgenerales=upper('$obsgenerales'),estadoest_madre=upper('$estadoest_madre'),
             estadoest_padre='' ,edades=$edad,sexo=upper('$sexo') ,tipo_doc_tutor='',apellido_tutor='',nombre_tutor=''
			 ,alfabeta_tutor='',estudios_tutor='',estadoest_tutor=''  ,anio_mayor_nivel_tutor='0', nro_doc_tutor='' $agentes_sql
                       
             where id_beneficiarios=" . $id_planilla;
    }//Menor de 18 años con responsable padre y masculino (Update)
    elseif (($responsable == 'PADRE') && ($sexo == 'M')) {
        $verifica_insercion = 's';
        $query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',
             anio_mayor_nivel='$anio_mayor_nivel',indigena=upper('$indigena'),id_tribu=upper('$id_tribu'),id_lengua=$id_lengua,alfabeta=upper('$alfabeta'),
             estudios=upper('$estudios'),id_categoria=$id_categoria,
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamenton'), localidad=upper('$localidadn'), municipio=upper('$municipion'), 
             barrio=upper('$barrion'),telefono='$telefono',cod_pos='$cod_posn',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             pais_nac=upper('$paisn'),fecha_carga='$fecha_carga', usuario_carga=upper('$usuario'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_padre=upper('$nombre_madre'),anio_mayor_nivel_padre=$anio_mayor_nivel_madre,alfabeta_padre=upper('$alfabeta_madre'),
             estudios_padre=upper('$estudios_madre'), apellido_padre=upper('$apellido_madre'), nro_doc_padre='$nro_doc_madre', 
             tipo_doc_padre=upper('$tipo_doc_madre'),nombre_madre='',anio_mayor_nivel_madre='0',alfabeta_madre='',estudios_madre='', 
             apellido_madre='', nro_doc_madre='',tipo_doc_madre='', tipo_transaccion='M', mail=upper('$mail'), 
             celular='$celular',otrotel='$otrotel',estadoest=upper('$estadoest'),discv=upper('$discv'),disca=upper('$disca'),discmo=upper('$discmo'),
             discme=upper('$discme'),otradisc=upper('$otradisc'), obsgenerales=upper('$obsgenerales'),estadoest_padre=upper('$estadoest_madre'),estadoest_madre='' ,edades=$edad,sexo=upper('$sexo')   ,tipo_doc_tutor='',apellido_tutor='',nombre_tutor=''
			 ,alfabeta_tutor='',estudios_tutor='',estadoest_tutor='' ,anio_mayor_nivel_tutor='0', nro_doc_tutor=''                 $agentes_sql             
              
         where id_beneficiarios=" . $id_planilla;
    }//FIN
    elseif (($responsable == 'TUTOR') && ($sexo == 'M') && ($estado_envio == 'e')) {
        $verifica_insercion = 's';
        $query = "update uad.beneficiarios set 
		             cuie_ea='$cuie', nombre_benef=upper('$nombre'),
		             apellido_benef=upper('$apellido'),
		             numero_doc='$num_doc',clase_documento_benef=upper('$clase_doc'),fecha_nacimiento_benef='$fecha_nac',
		             anio_mayor_nivel='$anio_mayor_nivel',indigena=upper('$indigena'),id_tribu=$id_tribu,id_lengua=$id_lengua,alfabeta=upper('$alfabeta'),
		             estudios=upper('$estudios'),id_categoria=$id_categoria,
		             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
		             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamenton'), localidad=upper('$localidadn'), municipio=upper('$municipion'), 
		             barrio=upper('$barrion'),telefono='$telefono',cod_pos='$cod_posn',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
		             pais_nac=upper('$paisn'),fecha_carga='$fecha_carga', usuario_carga=upper('$usuario'),
		             tipo_ficha='2',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
		             nombre_tutor=upper('$nombre_madre'),apellido_tutor=upper('$apellido_madre'), nro_doc_tutor='$nro_doc_madre', tipo_doc_tutor=upper('$tipo_doc_madre'),
		             anio_mayor_nivel_tutor=$anio_mayor_nivel_madre,alfabeta_tutor=upper('$alfabeta_madre'),estudios_tutor=upper('$estudios_madre'),estadoest_tutor=upper('$estadoest_madre'),
		             nombre_madre='', apellido_madre='', nro_doc_madre='',tipo_doc_madre='',
		             anio_mayor_nivel_madre='0',alfabeta_madre='',estudios_madre='',estadoest_madre='',
		             nombre_padre='', apellido_padre='', nro_doc_padre='',tipo_doc_padre='',
		             anio_mayor_nivel_padre='0',alfabeta_padre='', estudios_padre='', estadoest_padre='',
		             tipo_transaccion='M', mail=upper('$mail'), celular='$celular',otrotel='$otrotel',estadoest=upper('$estadoest'),discv=upper('$discv'),disca=upper('$disca'),discmo=upper('$discmo'),
		             discme=upper('$discme'),otradisc=upper('$otradisc'), obsgenerales=upper('$obsgenerales'), estado_envio='n'
					  ,edades=$edad,sexo=upper('$sexo')   ,$agentes_sql     
		                                   
		              
		         where id_beneficiarios=" . $id_planilla;
    }//FIN 
    //Menor de 18 años con responsable madre, masculino e información enviada (Update)
    if (($responsable == 'MADRE') && ($sexo == 'M') && ($estado_envio == 'e')) {
        $verifica_insercion = 's';
        $query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',
             anio_mayor_nivel='$anio_mayor_nivel',indigena=upper('$indigena'),id_tribu=upper('$id_tribu'),id_lengua=$id_lengua,alfabeta=upper('$alfabeta'),
             estudios=upper('$estudios'),id_categoria=$id_categoria,
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamenton'), localidad=upper('$localidadn'), municipio=upper('$municipion'), 
             barrio=upper('$barrion'),telefono='$telefono',cod_pos='$cod_posn',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             pais_nac=upper('$paisn'),fecha_carga='$fecha_carga', usuario_carga=upper('$usuario'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_madre=upper('$nombre_madre'),anio_mayor_nivel_madre=$anio_mayor_nivel_madre,alfabeta_madre=upper('$alfabeta_madre'),
             estudios_madre=upper('$estudios_madre'), apellido_madre=upper('$apellido_madre'), nro_doc_madre='$nro_doc_madre', 
             tipo_doc_madre=upper('$tipo_doc_madre'),nombre_padre='',anio_mayor_nivel_padre='0',alfabeta_padre='',estudios_padre='', apellido_padre='', 
             nro_doc_padre='',tipo_doc_padre='', tipo_transaccion='M', mail=upper('$mail'), celular='$celular',otrotel='$otrotel',
             estadoest=upper('$estadoest'), discv=upper('$discv'),disca=upper('$disca'),discmo=upper('$discmo'),
             discme=upper('$discme'),otradisc=upper('$otradisc'), obsgenerales=upper('$obsgenerales'),estadoest_madre=upper('$estadoest_madre'),
             estadoest_padre='' ,edades=$edad,sexo=upper('$sexo') ,tipo_doc_tutor='',apellido_tutor='',nombre_tutor=''
			 ,alfabeta_tutor='',estudios_tutor='',estadoest_tutor='' ,anio_mayor_nivel_tutor='0', nro_doc_tutor='' $agentes_sql
                       
             where id_beneficiarios=" . $id_planilla;
    }//Menor de 18 años con responsable padre , masculino e información enviada (Update)
    elseif (($responsable == 'PADRE') && ($sexo == 'M') && ($estado_envio == 'e')) {
        $verifica_insercion = 's';
        $query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',
             anio_mayor_nivel='$anio_mayor_nivel',indigena=upper('$indigena'),id_tribu=upper('$id_tribu'),id_lengua=$id_lengua,alfabeta=upper('$alfabeta'),
             estudios=upper('$estudios'),id_categoria=$id_categoria,
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamenton'), localidad=upper('$localidadn'), municipio=upper('$municipion'), 
             barrio=upper('$barrion'),telefono='$telefono',cod_pos='$cod_posn',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             pais_nac=upper('$paisn'),fecha_carga='$fecha_carga', usuario_carga=upper('$usuario'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_padre=upper('$nombre_madre'),anio_mayor_nivel_padre=$anio_mayor_nivel_madre,alfabeta_padre=upper('$alfabeta_madre'),
             estudios_padre=upper('$estudios_madre'), apellido_padre=upper('$apellido_madre'), nro_doc_padre='$nro_doc_madre', 
             tipo_doc_padre=upper('$tipo_doc_madre'),nombre_madre='',anio_mayor_nivel_madre='0',alfabeta_madre='',estudios_madre='', 
             apellido_madre='', nro_doc_madre='',tipo_doc_madre='', tipo_transaccion='M', mail=upper('$mail'), 
             celular='$celular',otrotel='$otrotel',estadoest=upper('$estadoest'),discv=upper('$discv'),disca=upper('$disca'),discmo=upper('$discmo'),
             discme=upper('$discme'),otradisc=upper('$otradisc'), obsgenerales=upper('$obsgenerales'),estadoest_padre=upper('$estadoest_madre'),estadoest_madre='' ,edades=$edad,sexo=upper('$sexo') ,tipo_doc_tutor='',apellido_tutor='',nombre_tutor=''
			 ,alfabeta_tutor='',estudios_tutor='',estadoest_tutor='' ,anio_mayor_nivel_tutor='0', nro_doc_tutor=''                       $agentes_sql             
              
         where id_beneficiarios=" . $id_planilla;
    }//FIN
    //Mayor de 18 años embarazada (Update)
    if ($id_categoria == '6') {
        $verifica_insercion = 's';
        $query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',
             anio_mayor_nivel='$anio_mayor_nivel',indigena=upper('$indigena'),id_tribu=upper('$id_tribu'),id_lengua=$id_lengua,alfabeta=upper('$alfabeta'),
             estudios=upper('$estudios'),id_categoria=$id_categoria,fecha_diagnostico_embarazo='$fecha_diagnostico_embarazo',
             semanas_embarazo='$semanas_embarazo',fecha_probable_parto='$fecha_probable_parto',
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamenton'), localidad=upper('$localidadn'), municipio=upper('$municipion'), 
             barrio=upper('$barrion'),telefono='$telefono',cod_pos='$cod_posn',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             score_riesgo='$score_riesgo' , pais_nac=upper('$paisn'),fecha_carga='$fecha_carga', usuario_carga=upper('$usuario'),
             tipo_ficha='$tipo_ficha',tipo_transaccion='M', mail=upper('$mail'), celular='$celular',otrotel='$otrotel',
             estadoest=upper('$estadoest'), fum='$fum',discv=upper('$discv'),disca=upper('$disca'),discmo=upper('$discmo'),
             discme=upper('$discme'),otradisc=upper('$otradisc'), obsgenerales=upper('$obsgenerales') ,edades=$edad,sexo=upper('$sexo')
                        $agentes_sql
             where id_beneficiarios=" . $id_planilla;
    }//Mayor de 18 años embrazada e información enviada (Update)
    elseif (($id_categoria == '6') && ($estado_envio == 'e')) {
        $verifica_insercion = 's';
        $query = "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',
             anio_mayor_nivel='$anio_mayor_nivel',indigena=upper('$indigena'),id_tribu=upper('$id_tribu'),id_lengua=$id_lengua,alfabeta=upper('$alfabeta'),
             estudios=upper('$estudios'),id_categoria=$id_categoria,fecha_diagnostico_embarazo='$fecha_diagnostico_embarazo',
             semanas_embarazo='$semanas_embarazo',fecha_probable_parto='$fecha_probable_parto',
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamenton'), localidad=upper('$localidadn'), municipio=upper('$municipion'), 
             barrio=upper('$barrion'),telefono='$telefono',cod_pos='$cod_posn',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             score_riesgo='$score_riesgo' , pais_nac=upper('$paisn'),fecha_carga='$fecha_carga', usuario_carga=upper('$usuario'),
             tipo_ficha='$tipo_ficha',tipo_transaccion='M', mail=upper('$mail'), celular='$celular',otrotel='$otrotel',
             estadoest=upper('$estadoest'), fum='$fum',discv=upper('$discv'),disca=upper('$disca'),discmo=upper('$discmo'),
             discme=upper('$discme'),otradisc=upper('$otradisc'), obsgenerales=upper('$obsgenerales') ,edades=$edad,sexo=upper('$sexo')
                           $agentes_sql         
              
         where id_beneficiarios=" . $id_planilla;
    }//FIN

    if ($verifica_insercion == 'n') {
        echo "update uad.beneficiarios set $estado_intermedio
             cuie_ea='$cuie', $ape_nom_update
             numero_doc='$num_doc',fecha_nacimiento_benef='$fecha_nac',
             anio_mayor_nivel='$anio_mayor_nivel',indigena=upper('$indigena'),id_tribu=upper('$id_tribu'),id_lengua=$id_lengua,alfabeta=upper('$alfabeta'),
             estudios=upper('$estudios'),id_categoria=$id_categoria,fecha_diagnostico_embarazo='$fecha_diagnostico_embarazo',
             semanas_embarazo='$semanas_embarazo',fecha_probable_parto='$fecha_probable_parto', fecha_efectiva_parto='1899-12-30',
             calle=upper('$calle'),numero_calle='$numero_calle',piso='$piso',dpto=upper('$dpto'),manzana='$manzana',entre_calle_1=upper('$entre_calle_1'),
             entre_calle_2=upper('$entre_calle_2'), departamento=upper('$departamenton'), localidad=upper('$localidadn'), municipio=upper('$municipion'), 
             barrio=upper('$barrion'),telefono='$telefono',cod_pos='$cod_posn',observaciones=upper('$observaciones'),fecha_inscripcion='$fecha_inscripcion',
             score_riesgo='$score_riesgo' ,pais_nac=upper('$paisn'),fecha_carga='$fecha_carga', usuario_carga=upper('$usuario'),
             tipo_ficha='$tipo_ficha',responsable=upper('$responsable'), menor_convive_con_adulto=upper('$menor_convive_con_adulto'), 
             nombre_padre=upper('$nombre_madre'),anio_mayor_nivel_padre=$anio_mayor_nivel_madre,alfabeta_padre=upper('$alfabeta_madre'),
             estudios_padre=upper('$estudios_madre'), apellido_padre=upper('$apellido_madre'), nro_doc_padre='$nro_doc_madre', 
             tipo_doc_padre=upper('$tipo_doc_madre'),nombre_madre='',anio_mayor_nivel_madre='0',alfabeta_madre='',estudios_madre='', 
             apellido_madre='', nro_doc_madre='',tipo_doc_madre='', tipo_transaccion='M', mail=upper('$mail'), 
             celular='$celular',otrotel='$otrotel',estadoest=upper('$estadoest'), fum='$fum',discv=upper('$discv'),disca=upper('$disca'),discmo=upper('$discmo'),
             discme=upper('$discme'),otradisc=upper('$otradisc'), obsgenerales=upper('$obsgenerales'),estadoest_padre=upper('$estadoest_madre'),estadoest_madre='' ,edades=$edad,sexo=upper('$sexo')
			  $agentes_sql                             
            
         where id_beneficiarios=" . $id_planilla;
    }
    sql($query, "Error al insertar/actualizar el muleto") or fin_pagina();
    $db->CompleteTrans();
    $accion = "Los datos se actualizaron";
} //FIN Update
// Insert de Beneficiarios
if ($_POST['guardar'] == "Guardar Planilla") {

    $fecha_carga = date("Y-m-d H:m:s");
    $usuario = $_ses_user['id'];

    //if($tipo_ficha==1){ $tipo_ficha=3; }elseif($tipo_ficha!=3){ $tipo_ficha=2; }

    $fecha_nac = Fecha_db($fecha_nac);
    $fum = Fecha_db($fum);

    $fecha_diagnostico_embarazo = Fecha_db($fecha_diagnostico_embarazo);
    if ($fecha_diagnostico_embarazo == "") {
        $fecha_diagnostico_embarazo = "1980-01-01";
    }
    /* if ($fecha_diganostico_embarazo!=""){$fecha_diagnostico_embarazo=Fecha_db($fecha_diagnostico_embarazo);}
      else{ $fecha_diagnostico_embarazo="1980-01-01";  } */

    $fecha_probable_parto = Fecha_db($fecha_probable_parto);
    if ($fecha_probable_parto == "") {
        $fecha_probable_parto = "1980-01-01";
        $fecha_efectiva_parto = $fecha_probable_parto;
    }

    $fecha_inscripcion = Fecha_db($fecha_inscripcion);

    $db->StartTrans();

    $sql_parametros = "select * from uad.parametros ";
    if ($uad_benef == 's') {
        $sql_parametros.=" a
                    inner join uad.uad_x_usuario b on a.codigo_uad=b.cod_uad
                    where id_usuario=" . $_ses_user['id'];
    }
    $result_parametros = sql($sql_parametros) or fin_pagina();
    $codigo_provincia = $result_parametros->fields['codigo_provincia'];
    $codigo_ci = $result_parametros->fields['codigo_ci'];
    $codigo_uad = $result_parametros->fields['codigo_uad'];

    $q = "select nextval('uad.beneficiarios_id_beneficiarios_seq') as id_planilla";
    $id_planilla = sql($q) or fin_pagina();

    $id_planilla = $id_planilla->fields['id_planilla'];

    $id_planilla_clave = str_pad($id_planilla, 6, '0', STR_PAD_LEFT);

    $clave_beneficiario = $codigo_provincia . $codigo_uad . $codigo_ci . $id_planilla_clave;
    //echo $clave_beneficiario;

    $usuario = substr($usuario, 0, 9);

    if ($agentes == 's') {
        $agentes_sql = ",apellidoagente,nombreagente,centro_inscriptor,dni_agente";
        $agentes_sql2 = ",upper('$apellidoagente'),upper('$nombreagente'),upper('$cuie_agente'),upper('$num_doc_agente')";
    }

    if ($menor_embarazada != 'S') {
        $menor_embarazada = 'N';
    }
    $responsable = $_POST['responsable'];
    $verifica_insercion = 'n';
    /*   $sql="Select puco.documento from puco.puco where puco.documento = '$num_doc'";
      $res_extra=sql($sql, "Error al traer el beneficiario") or fin_pagina(); */

    //Responsable Padre, menor no embarazada o menor de 9 años (Insert)
    if (($responsable == 'PADRE') && ($sexo == 'F') && ($menor_embarazada == 'N')) {
        $verifica_insercion = 's';
        if ($ape_nom == 's') {
            $query = "insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,alfabeta,estudios,anio_mayor_nivel,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,score_riesgo,cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_padre,
             nro_doc_padre,apellido_padre,nombre_padre,alfabeta_padre,estudios_padre,anio_mayor_nivel_padre,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable,mail,celular,otrotel,estadoest,fum,
			 discv,disca,discmo,discme,otradisc,obsgenerales,estadoest_padre,menor_embarazada,edades $agentes_sql )
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$apellido_otro'),upper('$nombre'),upper('$nombre_otro'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_proc'),upper('$paisn'),
             upper('$indigena'),upper('$id_tribu'),$id_lengua,upper('$alfabeta'),upper('$estudios'),'$anio_mayor_nivel',
             '1899-12-30',null, '1899-12-30','1899-12-30','$score_riesgo',upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),upper('$alfabeta_madre'),upper('$estudios_madre'),
             '$anio_mayor_nivel_madre',upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamenton'),upper('$localidadn'),upper('$municipion'),upper('$barrion'),
             '$cod_posn',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','2', 
             upper('$responsable'),upper('$mail'),'$celular','$otrotel',upper('$estadoest'),'1899-12-30',
             upper('$discv'),upper('$disca'),upper('$discmo'),upper('$discme'),upper('$otradisc'),upper('$obsgenerales'),upper('$estadoest_madre'),upper('$menor_embarazada'),$edad $agentes_sql2)";
        } else {
            $query = "insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,nombre_benef,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,alfabeta,estudios,anio_mayor_nivel,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,score_riesgo,cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_padre,
             nro_doc_padre,apellido_padre,nombre_padre,alfabeta_padre,estudios_padre,anio_mayor_nivel_padre,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable,mail,celular,otrotel,estadoest,fum,
			 discv,disca,discmo,discme,otradisc,obsgenerales,estadoest_padre,menor_embarazada ,edades $agentes_sql)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$nombre'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_proc'),upper('$paisn'),
             upper('$indigena'),upper('$id_tribu'),$id_lengua,upper('$alfabeta'),upper('$estudios'),'$anio_mayor_nivel',
             '1899-12-30',null, '1899-12-30','1899-12-30','$score_riesgo',upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),upper('$alfabeta_madre'),upper('$estudios_madre'),
             '$anio_mayor_nivel_madre',upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamenton'),upper('$localidadn'),upper('$municipion'),upper('$barrion'),
             '$cod_posn',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','2', 
             upper('$responsable'),upper('$mail'),'$celular','$otrotel',upper('$estadoest'),'1899-12-30',
             upper('$discv'),upper('$disca'),upper('$discmo'),upper('$discme'),upper('$otradisc'),upper('$obsgenerales'),upper('$estadoest_madre'),upper('$menor_embarazada'),$edad $agentes_sql2)";
        }



        // Busca antes de hacer el insert si el beneficiario esta o no en el PUCO
        /* if ($res_extra->recordcount()>0) {
          sql($query, "Error al insertar la Planilla") or fin_pagina();
          $accion="Se guardo la Planilla - El inscripto esta en el PUCO";
          $db->CompleteTrans();
          }
          if ($res_extra->recordcount()==0){ */
        sql($query, "Error al insertar la Planilla") or fin_pagina();
        $accion = "Se guardo la Planilla";
        $db->CompleteTrans();
        //	} //FIN
    } //FIN
    //Responsable Madre, menor no embarazada o menor de 9 años (Insert)
    if (($responsable == 'MADRE' ) && ($sexo == 'F') && ($menor_embarazada == 'N')) {
        $verifica_insercion = 's';
        if ($ape_nom == 's') {
            $query = "insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,alfabeta,estudios,anio_mayor_nivel,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,score_riesgo,cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_madre,
             nro_doc_madre,apellido_madre,nombre_madre,alfabeta_madre,estudios_madre,anio_mayor_nivel_madre,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable,mail,celular,otrotel,estadoest,fum,
			 discv,disca,discmo,discme,otradisc,obsgenerales,estadoest_madre,menor_embarazada ,edades $agentes_sql)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$apellido_otro'),upper('$nombre'),upper('$nombre_otro'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_proc'),upper('$paisn'),
             upper('$indigena'),upper('$id_tribu'),$id_lengua,upper('$alfabeta'),upper('$estudios'),'$anio_mayor_nivel',
             '1899-12-30',null,'1899-12-30','1899-12-30','$score_riesgo',upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),upper('$alfabeta_madre'),upper('$estudios_madre'),
             '$anio_mayor_nivel_madre',upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamenton'),upper('$localidadn'),upper('$municipion'),upper('$barrion'),
             '$cod_posn',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','2', 
             upper('$responsable'),upper('$mail'),'$celular','$otrotel',upper('$estadoest'),'1899-12-30',
             upper('$discv'),upper('$disca'),upper('$discmo'),upper('$discme'),upper('$otradisc'),upper('$obsgenerales'),upper('$estadoest_madre'),upper('$menor_embarazada'),$edad $agentes_sql2)";
        } else {
            $query = "insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,nombre_benef,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,alfabeta,estudios,anio_mayor_nivel,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,score_riesgo,cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_madre,
             nro_doc_madre,apellido_madre,nombre_madre,alfabeta_madre,estudios_madre,anio_mayor_nivel_madre,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable,mail,celular,otrotel,estadoest,fum,
			 discv,disca,discmo,discme,otradisc,obsgenerales,estadoest_madre,menor_embarazada ,edades $agentes_sql)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$nombre'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_proc'),upper('$paisn'),
             upper('$indigena'),upper('$id_tribu'),$id_lengua,upper('$alfabeta'),upper('$estudios'),'$anio_mayor_nivel',
             '1899-12-30',null,'1899-12-30','1899-12-30','$score_riesgo',upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),upper('$alfabeta_madre'),upper('$estudios_madre'),
             '$anio_mayor_nivel_madre',upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamenton'),upper('$localidadn'),upper('$municipion'),upper('$barrion'),
             '$cod_posn',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','2', 
             upper('$responsable'),upper('$mail'),'$celular','$otrotel',upper('$estadoest'),'1899-12-30',
             upper('$discv'),upper('$disca'),upper('$discmo'),upper('$discme'),upper('$otradisc'),upper('$obsgenerales'),upper('$estadoest_madre'),upper('$menor_embarazada'),$edad $agentes_sql2)";
        }

        // Busca antes de hacer el insert si el beneficiario esta o no en el PUCO
        /* 	if ($res_extra->recordcount()>0) {
          sql($query, "Error al insertar la Planilla") or fin_pagina();
          $accion="Se guardo la Planilla - El inscripto esta en el PUCO";
          $db->CompleteTrans();
          }
          if ($res_extra->recordcount()==0){ */
        sql($query, "Error al insertar la Planilla") or fin_pagina();
        $accion = "Se guardo la Planilla";
        $db->CompleteTrans();
        //} //FIN
    } //FIN	

    if (($responsable == 'TUTOR' ) && ($sexo == 'F') && ($menor_embarazada == 'N')) {
        $verifica_insercion = 's';
        if ($ape_nom == 's') {
            $query = "insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,alfabeta,estudios,anio_mayor_nivel,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,score_riesgo,cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_tutor,
             nro_doc_tutor,apellido_tutor,nombre_tutor,alfabeta_tutor,estudios_tutor,anio_mayor_nivel_tutor,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable,mail,celular,otrotel,estadoest,fum,
			 discv,disca,discmo,discme,otradisc,obsgenerales,estadoest_tutor,menor_embarazada ,edades $agentes_sql)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$apellido_otro'),upper('$nombre'),upper('$nombre_otro'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_proc'),upper('$paisn'),
             upper('$indigena'),upper('$id_tribu'),$id_lengua,upper('$alfabeta'),upper('$estudios'),'$anio_mayor_nivel',
             '1899-12-30',null,'1899-12-30','1899-12-30','$score_riesgo',upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),upper('$alfabeta_madre'),upper('$estudios_madre'),
             '$anio_mayor_nivel_madre',upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamenton'),upper('$localidadn'),upper('$municipion'),upper('$barrion'),
             '$cod_posn',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','2', 
             upper('$responsable'),upper('$mail'),'$celular','$otrotel',upper('$estadoest'),'1899-12-30',
             upper('$discv'),upper('$disca'),upper('$discmo'),upper('$discme'),upper('$otradisc'),upper('$obsgenerales'),upper('$estadoest_madre'),upper('$menor_embarazada'),$edad $agentes_sql2)";
        } else {
            $query = "insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,nombre_benef,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,alfabeta,estudios,anio_mayor_nivel,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,score_riesgo,cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_tutor,
             nro_doc_tutor,apellido_tutor,nombre_tutor,alfabeta_tutor,estudios_tutor,anio_mayor_nivel_tutor,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable,mail,celular,otrotel,estadoest,fum,
			 discv,disca,discmo,discme,otradisc,obsgenerales,estadoest_tutor,menor_embarazada ,edades $agentes_sql)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$nombre'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_proc'),upper('$paisn'),
             upper('$indigena'),upper('$id_tribu'),$id_lengua,upper('$alfabeta'),upper('$estudios'),'$anio_mayor_nivel',
             '1899-12-30',null,'1899-12-30','1899-12-30','$score_riesgo',upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),upper('$alfabeta_madre'),upper('$estudios_madre'),
             '$anio_mayor_nivel_madre',upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamenton'),upper('$localidadn'),upper('$municipion'),upper('$barrion'),
             '$cod_posn',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','2', 
             upper('$responsable'),upper('$mail'),'$celular','$otrotel',upper('$estadoest'),'1899-12-30',
             upper('$discv'),upper('$disca'),upper('$discmo'),upper('$discme'),upper('$otradisc'),upper('$obsgenerales'),upper('$estadoest_madre'),upper('$menor_embarazada'),$edad $agentes_sql2)";
        }

        // Busca antes de hacer el insert si el beneficiario esta o no en el PUCO
        /* if ($res_extra->recordcount()>0) {
          sql($query, "Error al insertar la Planilla") or fin_pagina();
          $accion="Se guardo la Planilla - El inscripto esta en el PUCO";
          $db->CompleteTrans();
          }
          if ($res_extra->recordcount()==0){ */
        sql($query, "Error al insertar la Planilla") or fin_pagina();
        $accion = "Se guardo la Planilla";
        $db->CompleteTrans();
        //} //FIN
    } //FIN
    //Responsable Padre, menor de 10 años embarazada (Insert)
    if (($responsable == 'PADRE') && ($menor_embarazada == 'S')) {
        $verifica_insercion = 's';
        if ($ape_nom == 's') {
            $query = "insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,pais_nac,
             indigena,id_tribu,id_lengua,alfabeta,estudios,anio_mayor_nivel,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,score_riesgo,cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_padre,
             nro_doc_padre,apellido_padre,nombre_padre,alfabeta_padre,estudios_padre,anio_mayor_nivel_padre,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable,mail,celular,otrotel,estadoest,fum,
			 discv,disca,discmo,discme,otradisc,obsgenerales,estadoest_padre,menor_embarazada ,edades $agentes_sql)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$apellido_otro'),upper('$nombre'),upper('$nombre_otro'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$paisn'),
             upper('$indigena'),upper('$id_tribu'),$id_lengua,upper('$alfabeta'),upper('$estudios'),'$anio_mayor_nivel',
             '$fecha_diagnostico_embarazo','$semanas_embarazo', '$fecha_probable_parto','$score_riesgo',upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),upper('$alfabeta_madre'),upper('$estudios_madre'),
             '$anio_mayor_nivel_madre',upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamenton'),upper('$localidadn'),upper('$municipion'),upper('$barrion'),
             '$cod_posn',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','2', 
             upper('$responsable'),upper('$mail'),'$celular','$otrotel',upper('$estadoest'),'$fum',
             upper('$discv'),upper('$disca'),upper('$discmo'),upper('$discme'),upper('$otradisc'),upper('$obsgenerales'),upper('$estadoest_madre'),upper('$menor_embarazada'),$edad $agentes_sql2)";
        } else {
            $query = "insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,nombre_benef,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,pais_nac,
             indigena,id_tribu,id_lengua,alfabeta,estudios,anio_mayor_nivel,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,score_riesgo,cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_padre,
             nro_doc_padre,apellido_padre,nombre_padre,alfabeta_padre,estudios_padre,anio_mayor_nivel_padre,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable,mail,celular,otrotel,estadoest,fum,
			 discv,disca,discmo,discme,otradisc,obsgenerales,estadoest_padre,menor_embarazada ,edades $agentes_sql)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$nombre'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$paisn'),
             upper('$indigena'),upper('$id_tribu'),$id_lengua,upper('$alfabeta'),upper('$estudios'),'$anio_mayor_nivel',
             '$fecha_diagnostico_embarazo','$semanas_embarazo', '$fecha_probable_parto','$score_riesgo',upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),upper('$alfabeta_madre'),upper('$estudios_madre'),
             '$anio_mayor_nivel_madre',upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamenton'),upper('$localidadn'),upper('$municipion'),upper('$barrion'),
             '$cod_posn',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','2', 
             upper('$responsable'),upper('$mail'),'$celular','$otrotel',upper('$estadoest'),'$fum',
             upper('$discv'),upper('$disca'),upper('$discmo'),upper('$discme'),upper('$otradisc'),upper('$obsgenerales'),upper('$estadoest_madre'),upper('$menor_embarazada'),$edad $agentes_sql2)";
        }



        // Busca antes de hacer el insert si el beneficiario esta o no en el PUCO
        /* if ($res_extra->recordcount()>0) {
          sql($query, "Error al insertar la Planilla") or fin_pagina();
          $accion="Se guardo la Planilla - El inscripto esta en el PUCO";
          $db->CompleteTrans();
          }
          if ($res_extra->recordcount()==0){ */
        sql($query, "Error al insertar la Planilla") or fin_pagina();
        $accion = "Se guardo la Planilla";
        $db->CompleteTrans();
        //} //FIN
    } //FIN
    //Responsable Madre, menor de 10 años embarazada (Insert)
    if (($responsable == 'MADRE' ) && ($menor_embarazada == 'S')) {
        $verifica_insercion = 's';
        if ($ape_nom == 's') {
            $query = "insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,pais_nac,
             indigena,id_tribu,id_lengua,alfabeta,estudios,anio_mayor_nivel,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,score_riesgo,cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_madre,
             nro_doc_madre,apellido_madre,nombre_madre,alfabeta_madre,estudios_madre,anio_mayor_nivel_madre,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable,mail,celular,otrotel,estadoest,fum,
			 discv,disca,discmo,discme,otradisc,obsgenerales,estadoest_madre,menor_embarazada ,edades $agentes_sql)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$apellido_otro'),upper('$nombre'),upper('$nombre_otro'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$paisn'),
             upper('$indigena'),upper('$id_tribu'),$id_lengua,upper('$alfabeta'),upper('$estudios'),'$anio_mayor_nivel',
             '$fecha_diagnostico_embarazo','$semanas_embarazo', '$fecha_probable_parto','$score_riesgo',upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),upper('$alfabeta_madre'),upper('$estudios_madre'),
             '$anio_mayor_nivel_madre',upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamenton'),upper('$localidadn'),upper('$municipion'),upper('$barrion'),
             '$cod_posn',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','2', 
             upper('$responsable'),upper('$mail'),'$celular','$otrotel',upper('$estadoest'),'$fum',
             upper('$discv'),upper('$disca'),upper('$discmo'),upper('$discme'),upper('$otradisc'),upper('$obsgenerales'),upper('$estadoest_madre'),upper('$menor_embarazada'),$edad $agentes_sql2)";
        } else {
            $query = "insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,nombre_benef,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,pais_nac,
             indigena,id_tribu,id_lengua,alfabeta,estudios,anio_mayor_nivel,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,score_riesgo,cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_madre,
             nro_doc_madre,apellido_madre,nombre_madre,alfabeta_madre,estudios_madre,anio_mayor_nivel_madre,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable,mail,celular,otrotel,estadoest,fum,
			 discv,disca,discmo,discme,otradisc,obsgenerales,estadoest_madre,menor_embarazada ,edades $agentes_sql)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$nombre'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$paisn'),
             upper('$indigena'),upper('$id_tribu'),$id_lengua,upper('$alfabeta'),upper('$estudios'),'$anio_mayor_nivel',
             '$fecha_diagnostico_embarazo','$semanas_embarazo', '$fecha_probable_parto','$score_riesgo',upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),upper('$alfabeta_madre'),upper('$estudios_madre'),
             '$anio_mayor_nivel_madre',upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamenton'),upper('$localidadn'),upper('$municipion'),upper('$barrion'),
             '$cod_posn',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','2', 
             upper('$responsable'),upper('$mail'),'$celular','$otrotel',upper('$estadoest'),'$fum',
             upper('$discv'),upper('$disca'),upper('$discmo'),upper('$discme'),upper('$otradisc'),upper('$obsgenerales'),upper('$estadoest_madre'),upper('$menor_embarazada'),$edad $agentes_sql2)";
        }

        // Busca antes de hacer el insert si el beneficiario esta o no en el PUCO
        /* 	if ($res_extra->recordcount()>0) {
          sql($query, "Error al insertar la Planilla") or fin_pagina();
          $accion="Se guardo la Planilla - El inscripto esta en el PUCO";
          $db->CompleteTrans();
          }
          if ($res_extra->recordcount()==0){ */
        sql($query, "Error al insertar la Planilla") or fin_pagina();
        $accion = "Se guardo la Planilla";
        $db->CompleteTrans();
        //}//FIN
    } //FIN

    if (($responsable == 'TUTOR' ) && ($menor_embarazada == 'S')) {
        $verifica_insercion = 's';
        if ($ape_nom == 's') {
            $query = "insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,pais_nac,
             indigena,id_tribu,id_lengua,alfabeta,estudios,anio_mayor_nivel,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,score_riesgo,cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_tutor,
             nro_doc_tutor,apellido_tutor,nombre_tutor,alfabeta_tutor,estudios_tutor,anio_mayor_nivel_tutor,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable,mail,celular,otrotel,estadoest,fum,
			 discv,disca,discmo,discme,otradisc,obsgenerales,estadoest_tutor,menor_embarazada ,edades $agentes_sql)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$apellido_otro'),upper('$nombre'),upper('$nombre_otro'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$paisn'),
             upper('$indigena'),upper('$id_tribu'),$id_lengua,upper('$alfabeta'),upper('$estudios'),'$anio_mayor_nivel',
             '$fecha_diagnostico_embarazo','$semanas_embarazo', '$fecha_probable_parto','$score_riesgo',upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),upper('$alfabeta_madre'),upper('$estudios_madre'),
             '$anio_mayor_nivel_madre',upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamenton'),upper('$localidadn'),upper('$municipion'),upper('$barrion'),
             '$cod_posn',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','2', 
             upper('$responsable'),upper('$mail'),'$celular','$otrotel',upper('$estadoest'),'$fum',
             upper('$discv'),upper('$disca'),upper('$discmo'),upper('$discme'),upper('$otradisc'),upper('$obsgenerales'),upper('$estadoest_madre'),upper('$menor_embarazada'),$edad $agentes_sql2)";
        } else {
            $query = "insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,nombre_benef,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,pais_nac,
             indigena,id_tribu,id_lengua,alfabeta,estudios,anio_mayor_nivel,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,score_riesgo,cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_tutor,
             nro_doc_tutor,apellido_tutor,nombre_tutor,alfabeta_tutor,estudios_tutor,anio_mayor_nivel_tutor,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable,mail,celular,otrotel,estadoest,fum,
			 discv,disca,discmo,discme,otradisc,obsgenerales,estadoest_tutor,menor_embarazada ,edades $agentes_sql)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$nombre'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$paisn'),
             upper('$indigena'),upper('$id_tribu'),$id_lengua,upper('$alfabeta'),upper('$estudios'),'$anio_mayor_nivel',
             '$fecha_diagnostico_embarazo','$semanas_embarazo', '$fecha_probable_parto','$score_riesgo',upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),upper('$alfabeta_madre'),upper('$estudios_madre'),
             '$anio_mayor_nivel_madre',upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamenton'),upper('$localidadn'),upper('$municipion'),upper('$barrion'),
             '$cod_posn',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','2', 
             upper('$responsable'),upper('$mail'),'$celular','$otrotel',upper('$estadoest'),'$fum',
             upper('$discv'),upper('$disca'),upper('$discmo'),upper('$discme'),upper('$otradisc'),upper('$obsgenerales'),upper('$estadoest_madre'),upper('$menor_embarazada'),$edad $agentes_sql2)";
        }

        // Busca antes de hacer el insert si el beneficiario esta o no en el PUCO
        /* 	if ($res_extra->recordcount()>0) {
          sql($query, "Error al insertar la Planilla") or fin_pagina();
          $accion="Se guardo la Planilla - El inscripto esta en el PUCO";
          $db->CompleteTrans();
          }
          if ($res_extra->recordcount()==0){ */
        sql($query, "Error al insertar la Planilla") or fin_pagina();
        $accion = "Se guardo la Planilla";
        $db->CompleteTrans();
        //}//FIN
    } //FIN
    //Responsable Padre o Madre, menor masculino (Insert)
    if (($responsable == 'PADRE') && ($sexo == 'M')) {
        $verifica_insercion = 's';
        if ($ape_nom == 's') {
            $query = "insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,alfabeta,estudios,anio_mayor_nivel,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,score_riesgo,cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_padre,
             nro_doc_padre,apellido_padre,nombre_padre,alfabeta_padre,estudios_padre,anio_mayor_nivel_padre,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable,mail,celular,otrotel,estadoest,fum,
			 discv,disca,discmo,discme,otradisc,obsgenerales,estadoest_padre,menor_embarazada ,edades $agentes_sql)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$apellido_otro'),upper('$nombre'),upper('$nombre_otro'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_proc'),upper('$paisn'),
             upper('$indigena'),upper('$id_tribu'),$id_lengua,upper('$alfabeta'),upper('$estudios'),'$anio_mayor_nivel',
             '1899-12-30',null, '1899-12-30','1899-12-30',null,
             upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),upper('$alfabeta_madre'),upper('$estudios_madre'),
             '$anio_mayor_nivel_madre',upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamenton'),upper('$localidadn'),upper('$municipion'),upper('$barrion'),
             '$cod_posn',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','2', 
             upper('$responsable'),upper('$mail'),'$celular','$otrotel',upper('$estadoest'),'1899-12-30',
             upper('$discv'),upper('$disca'),upper('$discmo'),upper('$discme'),upper('$otradisc'),upper('$obsgenerales'),upper('$estadoest_madre'),upper('$menor_embarazada'),$edad $agentes_sql2)";
        } else {
            $query = "insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,nombre_benef,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,alfabeta,estudios,anio_mayor_nivel,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,score_riesgo,cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_padre,
             nro_doc_padre,apellido_padre,nombre_padre,alfabeta_padre,estudios_padre,anio_mayor_nivel_padre,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable,mail,celular,otrotel,estadoest,fum,
			 discv,disca,discmo,discme,otradisc,obsgenerales,estadoest_padre,menor_embarazada ,edades $agentes_sql)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$nombre'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_proc'),upper('$paisn'),
             upper('$indigena'),upper('$id_tribu'),$id_lengua,upper('$alfabeta'),upper('$estudios'),'$anio_mayor_nivel',
             '1899-12-30',null, '1899-12-30','1899-12-30',null,
             upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),upper('$alfabeta_madre'),upper('$estudios_madre'),
             '$anio_mayor_nivel_madre',upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamenton'),upper('$localidadn'),upper('$municipion'),upper('$barrion'),
             '$cod_posn',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','2', 
             upper('$responsable'),upper('$mail'),'$celular','$otrotel',upper('$estadoest'),'1899-12-30',
             upper('$discv'),upper('$disca'),upper('$discmo'),upper('$discme'),upper('$otradisc'),upper('$obsgenerales'),upper('$estadoest_madre'),upper('$menor_embarazada'),$edad $agentes_sql2)";
        }



        // Busca antes de hacer el insert si el beneficiario esta o no en el PUCO
        /* 	if ($res_extra->recordcount()>0) {
          sql($query, "Error al insertar la Planilla") or fin_pagina();
          $accion="Se guardo la Planilla - El inscripto esta en el PUCO";
          $db->CompleteTrans();
          }
          if ($res_extra->recordcount()==0){ */
        sql($query, "Error al insertar la Planilla") or fin_pagina();
        $accion = "Se guardo la Planilla";
        $db->CompleteTrans();
        //}//FIN
    } //FIN
    //Responsable Madre, menor masculino (Insert)
    if (($responsable == 'MADRE' ) && ($sexo == 'M')) {
        $verifica_insercion = 's';
        if ($ape_nom == 's') {
            $query = "insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,alfabeta,estudios,anio_mayor_nivel,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,score_riesgo,cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_madre,
             nro_doc_madre,apellido_madre,nombre_madre,alfabeta_madre,estudios_madre,anio_mayor_nivel_madre,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable,mail,celular,otrotel,estadoest,fum,
			 discv,disca,discmo,discme,otradisc,obsgenerales,estadoest_madre,menor_embarazada ,edades $agentes_sql)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$apellido_otro'),upper('$nombre'),upper('$nombre_otro'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_proc'),upper('$paisn'),
             upper('$indigena'),upper('$id_tribu'),$id_lengua,upper('$alfabeta'),upper('$estudios'),'$anio_mayor_nivel',
             '1899-12-30',null, '1899-12-30','1899-12-30',null,
             upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),upper('$alfabeta_madre'),upper('$estudios_madre'),
             '$anio_mayor_nivel_madre',upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamenton'),upper('$localidadn'),upper('$municipion'),upper('$barrion'),
             '$cod_posn',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','2', 
             upper('$responsable'),upper('$mail'),'$celular','$otrotel',upper('$estadoest'),'1899-12-30',
             upper('$discv'),upper('$disca'),upper('$discmo'),upper('$discme'),upper('$otradisc'),upper('$obsgenerales'),upper('$estadoest_madre'),upper('$menor_embarazada'),$edad $agentes_sql2)";
        } else {
            $query = "insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,nombre_benef,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,alfabeta,estudios,anio_mayor_nivel,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,score_riesgo,cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_madre,
             nro_doc_madre,apellido_madre,nombre_madre,alfabeta_madre,estudios_madre,anio_mayor_nivel_madre,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable,mail,celular,otrotel,estadoest,fum,
			 discv,disca,discmo,discme,otradisc,obsgenerales,estadoest_madre,menor_embarazada ,edades $agentes_sql)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$nombre'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_proc'),upper('$paisn'),
             upper('$indigena'),upper('$id_tribu'),$id_lengua,upper('$alfabeta'),upper('$estudios'),'$anio_mayor_nivel',
             '1899-12-30',null, '1899-12-30','1899-12-30',null,
             upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),upper('$alfabeta_madre'),upper('$estudios_madre'),
             '$anio_mayor_nivel_madre',upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamenton'),upper('$localidadn'),upper('$municipion'),upper('$barrion'),
             '$cod_posn',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','2', 
             upper('$responsable'),upper('$mail'),'$celular','$otrotel',upper('$estadoest'),'1899-12-30',
             upper('$discv'),upper('$disca'),upper('$discmo'),upper('$discme'),upper('$otradisc'),upper('$obsgenerales'),upper('$estadoest_madre'),upper('$menor_embarazada'),$edad $agentes_sql2)";
        }

        // Busca antes de hacer el insert si el beneficiario esta o no en el PUCO
        /* if ($res_extra->recordcount()>0) {
          sql($query, "Error al insertar la Planilla") or fin_pagina();
          $accion="Se guardo la Planilla - El inscripto esta en el PUCO";
          $db->CompleteTrans();
          }
          if ($res_extra->recordcount()==0){ */
        sql($query, "Error al insertar la Planilla") or fin_pagina();
        $accion = "Se guardo la Planilla";
        $db->CompleteTrans();
        //} //FIN
    } //FIN	  
    if (($responsable == 'TUTOR' ) && ($sexo == 'M')) {
        $verifica_insercion = 's';
        if ($ape_nom == 's') {
            $query = "insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,alfabeta,estudios,anio_mayor_nivel,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,score_riesgo,cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_tutor,
             nro_doc_tutor,apellido_tutor,nombre_tutor,alfabeta_tutor,estudios_tutor,anio_mayor_nivel_tutor,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable,mail,celular,otrotel,estadoest,fum,
			 discv,disca,discmo,discme,otradisc,obsgenerales,estadoest_tutor,menor_embarazada ,edades $agentes_sql)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$apellido_otro'),upper('$nombre'),upper('$nombre_otro'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_proc'),upper('$paisn'),
             upper('$indigena'),upper('$id_tribu'),$id_lengua,upper('$alfabeta'),upper('$estudios'),'$anio_mayor_nivel',
             '1899-12-30',null, '1899-12-30','1899-12-30',null,
             upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),upper('$alfabeta_madre'),upper('$estudios_madre'),
             '$anio_mayor_nivel_madre',upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamenton'),upper('$localidadn'),upper('$municipion'),upper('$barrion'),
             '$cod_posn',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','2', 
             upper('$responsable'),upper('$mail'),'$celular','$otrotel',upper('$estadoest'),'1899-12-30',
             upper('$discv'),upper('$disca'),upper('$discmo'),upper('$discme'),upper('$otradisc'),upper('$obsgenerales'),upper('$estadoest_madre'),upper('$menor_embarazada'),$edad $agentes_sql2)";
        } else {
            $query = "insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,nombre_benef,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,alfabeta,estudios,anio_mayor_nivel,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,score_riesgo,cuie_ea,cuie_ah,menor_convive_con_adulto,tipo_doc_tutor,
             nro_doc_tutor,apellido_tutor,nombre_tutor,alfabeta_tutor,estudios_tutor,anio_mayor_nivel_tutor,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,responsable,mail,celular,otrotel,estadoest,fum,
			 discv,disca,discmo,discme,otradisc,obsgenerales,estadoest_tutor,menor_embarazada ,edades $agentes_sql)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$nombre'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_proc'),upper('$paisn'),
             upper('$indigena'),upper('$id_tribu'),$id_lengua,upper('$alfabeta'),upper('$estudios'),'$anio_mayor_nivel',
             '1899-12-30',null, '1899-12-30','1899-12-30',null,
             upper('$cuie'),upper('$cuie'),upper('$menor_convive_con_adulto'),
             upper('$tipo_doc_madre'),'$nro_doc_madre',upper('$apellido_madre'),upper('$nombre_madre'),upper('$alfabeta_madre'),upper('$estudios_madre'),
             '$anio_mayor_nivel_madre',upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamenton'),upper('$localidadn'),upper('$municipion'),upper('$barrion'),
             '$cod_posn',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','2', 
             upper('$responsable'),upper('$mail'),'$celular','$otrotel',upper('$estadoest'),'1899-12-30',
             upper('$discv'),upper('$disca'),upper('$discmo'),upper('$discme'),upper('$otradisc'),upper('$obsgenerales'),upper('$estadoest_madre'),upper('$menor_embarazada'),$edad $agentes_sql2)";
        }

        // Busca antes de hacer el insert si el beneficiario esta o no en el PUCO
        /* 	if ($res_extra->recordcount()>0) {
          sql($query, "Error al insertar la Planilla") or fin_pagina();
          $accion="Se guardo la Planilla - El inscripto esta en el PUCO";
          $db->CompleteTrans();
          }
          if ($res_extra->recordcount()==0){ */
        sql($query, "Error al insertar la Planilla") or fin_pagina();
        $accion = "Se guardo la Planilla";
        $db->CompleteTrans();
        //} //FIN
    } //FIN
    //Femenino mayor de 19 años (Insert)
    if ($id_categoria == '6') {
        $verifica_insercion = 's';
        if ($ape_nom == 's') {
            $query = "insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,alfabeta,estudios,anio_mayor_nivel,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,score_riesgo,cuie_ea,cuie_ah,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,mail,celular,otrotel,estadoest,fum,
			 discv,disca,discmo,discme,otradisc,obsgenerales ,edades $agentes_sql)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$apellido_otro'),upper('$nombre'),upper('$nombre_otro'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_proc'),upper('$paisn'),
             upper('$indigena'),upper('$id_tribu'),$id_lengua,upper('$alfabeta'),upper('$estudios'),'$anio_mayor_nivel',
             '$fecha_diagnostico_embarazo','$semanas_embarazo', '$fecha_probable_parto','1899-12-30','$score_riesgo',
             upper('$cuie'),upper('$cuie'),upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamenton'),upper('$localidadn'),upper('$municipion'),upper('$barrion'),
             '$cod_posn',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','2', 
             upper('$mail'),'$celular','$otrotel',upper('$estadoest'),'$fum',
             upper('$discv'),upper('$disca'),upper('$discmo'),upper('$discme'),upper('$otradisc'),upper('$obsgenerales'),$edad $agentes_sql2)";
        } else {
            $query = "insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,nombre_benef,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,alfabeta,estudios,anio_mayor_nivel,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,score_riesgo,cuie_ea,cuie_ah,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,mail,celular,otrotel,estadoest,fum,
			 discv,disca,discmo,discme,otradisc,obsgenerales ,edades $agentes_sql)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$nombre'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_proc'),upper('$paisn'),
             upper('$indigena'),upper('$id_tribu'),$id_lengua,upper('$alfabeta'),upper('$estudios'),'$anio_mayor_nivel',
             '$fecha_diagnostico_embarazo','$semanas_embarazo', '$fecha_probable_parto','1899-12-30','$score_riesgo',
             upper('$cuie'),upper('$cuie'),upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamenton'),upper('$localidadn'),upper('$municipion'),upper('$barrion'),
             '$cod_posn',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','2', 
             upper('$mail'),'$celular','$otrotel',upper('$estadoest'),'$fum',
             upper('$discv'),upper('$disca'),upper('$discmo'),upper('$discme'),upper('$otradisc'),upper('$obsgenerales'),$edad $agentes_sql2)";
        }

        // Busca antes de hacer el insert si el beneficiario esta o no en el PUCO
        /* 	if ($res_extra->recordcount()>0) {
          sql($query, "Error al insertar la Planilla") or fin_pagina();
          $accion="Se guardo la Planilla - El inscripto esta en el PUCO";
          $db->CompleteTrans();
          }
          if ($res_extra->recordcount()==0){ */
        sql($query, "Error al insertar la Planilla") or fin_pagina();
        $accion = "Se guardo la Planilla";
        $db->CompleteTrans();
        //}//FIN
    } //FIN	  
    if ($verifica_insercion == 'n') {
        echo "insert into uad.beneficiarios
             (id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef,nombre_benef,clase_documento_benef,
             tipo_documento,numero_doc,id_categoria,sexo,fecha_nacimiento_benef,provincia_nac,localidad_nac,pais_nac,
             indigena,id_tribu,id_lengua,alfabeta,estudios,anio_mayor_nivel,fecha_diagnostico_embarazo,semanas_embarazo,
             fecha_probable_parto,fecha_efectiva_parto,score_riesgo,cuie_ea,cuie_ah,calle,numero_calle,
             piso,dpto,manzana,entre_calle_1,entre_calle_2,telefono,departamento,localidad,municipio,barrio,cod_pos,observaciones,
			 fecha_inscripcion,fecha_carga,usuario_carga,activo,tipo_ficha,mail,celular,otrotel,estadoest,fum,
			 discv,disca,discmo,discme,otradisc,obsgenerales,menor_embarazada ,edades $agentes_sql)
             values
             ($id_planilla,'$estado_envio_ins','$clave_beneficiario',upper('$tipo_transaccion'),upper('$apellido'),upper('$nombre'),upper('$clase_doc'),
             upper('$tipo_doc'),'$num_doc','$id_categoria',upper('$sexo'),'$fecha_nac',upper('$provincia_nac'),upper('$localidad_proc'),upper('$paisn'),
             upper('$indigena'),upper('$id_tribu'),$id_lengua,upper('$alfabeta'),upper('$estudios'),'$anio_mayor_nivel',
             '$fecha_diagnostico_embarazo','$semanas_embarazo', '$fecha_probable_parto','1899-12-30','$score_riesgo',
             upper('$cuie'),upper('$cuie'),upper('$calle'),'$numero_calle','$piso',upper('$dpto'), '$manzana',upper('$entre_calle_1'),
             upper('$entre_calle_2'),'$telefono',upper('$departamenton'),upper('$localidadn'),upper('$municipion'),upper('$barrion'),
             '$cod_posn',upper('$observaciones'), '$fecha_inscripcion','$fecha_carga',upper('$usuario'),'1','2', 
             upper('$mail'),'$celular','$otrotel',upper('$estadoest'),'$fum',
             upper('$discv'),upper('$disca'),upper('$discmo'),upper('$discme'),upper('$otradisc'),upper('$obsgenerales'),upper('$menor_embarazada'),$edad $agentes_sql2)";
    }
}//FIN Insert
// Borrado de Beneficiarios
if ($_POST['borrar'] == "Borrar") {

    if ($tipo_transaccion == 'B') {
        $query = "UPDATE uad.beneficiarios  SET activo='0', tipo_transaccion= 'B', estado_envio='n'  WHERE (id_beneficiarios= $id_planilla)";
        sql($query, "Error al insertar la Planilla") or fin_pagina();

        $accion = "Se elimino la planilla $id_planilla";
    }
} //FIN Borrado Beneficiarios
// Buscar Beneficiarios por DNI
if ($_POST['b'] == "b") {
    if ($num_doc != '') {
        $sql1 = "select * from uad.beneficiarios	  
			where numero_doc='$num_doc'and tipo_documento='$tipo_doc' and clase_documento_benef='$clase_doc'";
        $res_extra1 = sql($sql1, "Error al traer el beneficiario") or fin_pagina();
        if ($res_extra1->recordcount() > 0) {
            $accion = "El Beneficiario ya esta Empadronado";
            $tipo_transaccion = 'M';
            $id_planilla = $res_extra1->fields['id_beneficiarios'];
            $clave_beneficiario = $res_extra1->fields['clave_beneficiario'];
            $apellido = $res_extra1->fields['apellido_benef'];
            $nombre = $res_extra1->fields['nombre_benef'];
            $tipo_doc = $res_extra1->fields['tipo_documento'];
            $clase_doc = $res_extra1->fields['clase_documento_benef'];
            $mail = $res_extra1->fields['mail'];
            $celular = $res_extra1->fields['celular'];
            $sexo = $res_extra1->fields['sexo'];
            $fecha_nac = Fecha($res_extra1->fields['fecha_nacimiento_benef']);
            $pais_nac = $res_extra1->fields['pais_nac'];
            $paisn = $res_extra1->fields['pais_nac'];
            if ($res_extra1->fields['id_categoria'] == 4 || $res_extra1->fields['id_categoria'] == 3) {
                $id_categoria = 5;
            } elseif ($res_extra1->fields['id_categoria'] == 1 || $res_extra1->fields['id_categoria'] == 2 || $res_extra1->fields['id_categoria'] == 7) {
                $id_categoria = 6;
            } else {
                $id_categoria = $res_extra1->fields['id_categoria'];
            }
            $indigena = $res_extra1->fields['indigena'];
            $id_tribu = $res_extra1->fields['id_tribu'];
            $id_lengua = $res_extra1->fields['id_lengua'];
            $alfabeta = $res_extra1->fields['alfabeta'];
            $estudios = $res_extra1->fields['estudios'];
            $estadoest = $res_extra1->fields['estadoest'];
            $anio_mayor_nivel = $res_extra1->fields['anio_mayor_nivel'];
            $calle = $res_extra1->fields['calle'];
            $numero_calle = $res_extra1->fields['numero_calle'];
            $piso = $res_extra1->fields['piso'];
            $dpto = $res_extra1->fields['dpto'];
            $manzana = $res_extra1->fields['manzana'];
            $entre_calle_1 = $res_extra1->fields['entre_calle_1'];
            $entre_calle_2 = $res_extra1->fields['entre_calle_2'];
            $telefono = $res_extra1->fields['telefono'];
            $otrotel = $res_extra1->fields['otrotel'];
            $departamento = $res_extra1->fields['departamento'];
            $localidad = $res_extra1->fields['localidad'];
            $municipio = $res_extra1->fields['municipio'];
            $barrio = $res_extra1->fields['barrio'];
            $barrion = $res_extra1->fields['barrio'];
            $cod_pos = $res_extra1->fields['cod_pos'];
            $tipo_ficha = $res_extra1->fields['tipo_ficha'];
            $observaciones = $res_extra1->fields['observaciones'];
            $apellidoagente = $res_extra1->fields['apellidoagente'];
            $nombreagente = $res_extra1->fields['nombreagente'];
            $cuie_agente = $res_extra1->fields['centro_inscriptor'];
            $num_doc_agente = $res_extra1->fields['dni_agente'];
            // Menor de 9 años, no muestra la información de embarazo y muestra la información del menor_convive_con_adulto	
            if (($id_categoria == '5') && ($sexo == 'F') && ($menor_embarazada == 'N')) {
                $embarazada = none;
                $mva1 = inline;
                $datos_resp = inline;
                $memb = none;
                $menor_convive_con_adulto = $res_extra1->fields['menor_convive_con_adulto'];
                $responsable = $res_extra1->fields['responsable'];
                if ($responsable == 'MADRE') {
                    $tipo_doc_madre = $res_extra1->fields['tipo_doc_madre'];
                    $nro_doc_madre = $res_extra1->fields['nro_doc_madre'];
                    $apellido_madre = $res_extra1->fields['apellido_madre'];
                    $nombre_madre = $res_extra1->fields['nombre_madre'];
                    $alfabeta_madre = $res_extra1->fields['alfabeta_madre'];
                    $estudios_madre = $res_extra1->fields['estudios_madre'];
                    $estadoest_madre = $res_extra1->fields['estadoest_madre'];
                    $anio_mayor_nivel_madre = $res_extra1->fields['anio_mayor_nivel_madre'];
                } elseif ($responsable == 'PADRE') {
                    $tipo_doc_madre = $res_extra1->fields['tipo_doc_padre'];
                    $nro_doc_madre = $res_extra1->fields['nro_doc_padre'];
                    $apellido_madre = $res_extra1->fields['apellido_padre'];
                    $nombre_madre = $res_extra1->fields['nombre_padre'];
                    $alfabeta_madre = $res_extra1->fields['alfabeta_padre'];
                    $estudios_madre = $res_extra1->fields['estudios_padre'];
                    $estadoest_madre = $res_extra1->fields['estadoest_padre'];
                    $anio_mayor_nivel_madre = $res_extra1->fields['anio_mayor_nivel_padre'];
                } elseif ($responsable == 'TUTOR') {
                    $tipo_doc_madre = $res_extra1->fields['tipo_doc_tutor'];
                    $nro_doc_madre = $res_extra1->fields['nro_doc_tutor'];
                    $apellido_madre = $res_extra1->fields['apellido_tutor'];
                    $nombre_madre = $res_extra1->fields['nombre_tutor'];
                    $alfabeta_madre = $res_extra1->fields['alfabeta_tutor'];
                    $estudios_madre = $res_extra1->fields['estudios_tutor'];
                    $estadoest_madre = $res_extra1->fields['estadoest_tutor'];
                    $anio_mayor_nivel_madre = $res_extra1->fields['anio_mayor_nivel_tutor'];
                }
            } // Menor de 10 años hasta 18 años, pregunta si la menor esta o no embarazada y la información de menor_convive_con_adulto
            if (($id_categoria == '5') && ($sexo == 'F') && ($menor_embarazada == 'N')) {
                $embarazada = none;
                $mva1 = inline;
                $datos_resp = inline;
                $memb = inline;
                $menor_convive_con_adulto = $res_extra1->fields['menor_convive_con_adulto'];
                $responsable = $res_extra1->fields['responsable'];
                $menor_embarazada = $res_extra1->fields['menor_embarazada'];
                if ($responsable == 'MADRE') {
                    $tipo_doc_madre = $res_extra1->fields['tipo_doc_madre'];
                    $nro_doc_madre = $res_extra1->fields['nro_doc_madre'];
                    $apellido_madre = $res_extra1->fields['apellido_madre'];
                    $nombre_madre = $res_extra1->fields['nombre_madre'];
                    $alfabeta_madre = $res_extra1->fields['alfabeta_madre'];
                    $estudios_madre = $res_extra1->fields['estudios_madre'];
                    $estadoest_madre = $res_extra1->fields['estadoest_madre'];
                    $anio_mayor_nivel_madre = $res_extra1->fields['anio_mayor_nivel_madre'];
                } elseif ($responsable == 'PADRE') {
                    $tipo_doc_madre = $res_extra1->fields['tipo_doc_padre'];
                    $nro_doc_madre = $res_extra1->fields['nro_doc_padre'];
                    $apellido_madre = $res_extra1->fields['apellido_padre'];
                    $nombre_madre = $res_extra1->fields['nombre_padre'];
                    $alfabeta_madre = $res_extra1->fields['alfabeta_padre'];
                    $estudios_madre = $res_extra1->fields['estudios_padre'];
                    $estadoest_madre = $res_extra1->fields['estadoest_padre'];
                    $anio_mayor_nivel_madre = $res_extra1->fields['anio_mayor_nivel_padre'];
                } elseif ($responsable == 'TUTOR') {
                    $tipo_doc_madre = $res_extra1->fields['tipo_doc_tutor'];
                    $nro_doc_madre = $res_extra1->fields['nro_doc_tutor'];
                    $apellido_madre = $res_extra1->fields['apellido_tutor'];
                    $nombre_madre = $res_extra1->fields['nombre_tutor'];
                    $alfabeta_madre = $res_extra1->fields['alfabeta_tutor'];
                    $estudios_madre = $res_extra1->fields['estudios_tutor'];
                    $estadoest_madre = $res_extra1->fields['estadoest_tutor'];
                    $anio_mayor_nivel_madre = $res_extra1->fields['anio_mayor_nivel_tutor'];
                }
                //Si esta embarazada muestra la información de embarazo.
                if ($menor_embarazada == 'S') {
                    $embarazada = inline;
                    $fum = Fecha($res_extra1->fields['fum']);
                    $fecha_diagnostico_emabrazo = Fecha($res_extra1->fields['fecha_diagnostico_embarazo']);
                    $semanas_embarazo = $res_extra1->fields['semanas_embarazo'];
                    $fecha_probable_parto = Fecha($res_extra1->fields['fecha_probable_parto']);
                } // Si no esta embarazada no la muestra.
                else {
                    $embarazada = none;
                }
            }// FIN
            // Menor de 18 años, masculino muestra solo la información menor_convive_con_adulto
            if (($id_categoria == '5') && ($sexo == 'M')) {
                $mva1 = inline;
                $datos_resp = inline;
                $embarazada = none;
                $memb = none;
                $menor_convive_con_adulto = $res_extra1->fields['menor_convive_con_adulto'];
                $responsable = $res_extra1->fields['responsable'];
                if ($responsable == 'MADRE') {
                    $tipo_doc_madre = $res_extra1->fields['tipo_doc_madre'];
                    $nro_doc_madre = $res_extra1->fields['nro_doc_madre'];
                    $apellido_madre = $res_extra1->fields['apellido_madre'];
                    $nombre_madre = $res_extra1->fields['nombre_madre'];
                    $alfabeta_madre = $res_extra1->fields['alfabeta_madre'];
                    $estudios_madre = $res_extra1->fields['estudios_madre'];
                    $estadoest_madre = $res_extra1->fields['estadoest_madre'];
                    $anio_mayor_nivel_madre = $res_extra1->fields['anio_mayor_nivel_madre'];
                } elseif ($responsable == 'PADRE') {
                    $tipo_doc_madre = $res_extra1->fields['tipo_doc_padre'];
                    $nro_doc_madre = $res_extra1->fields['nro_doc_padre'];
                    $apellido_madre = $res_extra1->fields['apellido_padre'];
                    $nombre_madre = $res_extra1->fields['nombre_padre'];
                    $alfabeta_madre = $res_extra1->fields['alfabeta_padre'];
                    $estudios_madre = $res_extra1->fields['estudios_padre'];
                    $estadoest_madre = $res_extra1->fields['estadoest_padre'];
                    $anio_mayor_nivel_madre = $res_extra1->fields['anio_mayor_nivel_padre'];
                } elseif ($responsable == 'TUTOR') {
                    $tipo_doc_madre = $res_extra1->fields['tipo_doc_tutor'];
                    $nro_doc_madre = $res_extra1->fields['nro_doc_tutor'];
                    $apellido_madre = $res_extra1->fields['apellido_tutor'];
                    $nombre_madre = $res_extra1->fields['nombre_tutor'];
                    $alfabeta_madre = $res_extra1->fields['alfabeta_tutor'];
                    $estudios_madre = $res_extra1->fields['estudios_tutor'];
                    $estadoest_madre = $res_extra1->fields['estadoest_tutor'];
                    $anio_mayor_nivel_madre = $res_extra1->fields['anio_mayor_nivel_tutor'];
                }
            }// Mayor de 18 años Femenino muesta la información de embarazo.
            if (($id_categoria == '6') && ($sexo == 'F')) {
                $embarazada = inline;
                $datos_resp = none;
                $mva1 = none;
                $memb = none;
                $fum = Fecha($res_extra1->fields['fum']);
                $fecha_diagnostico_emabrazo = Fecha($res_extra1->fields['fecha_diagnostico_embarazo']);
                $semanas_embarazo = $res_extra1->fields['semanas_embarazo'];
                $fecha_probable_parto = Fecha($res_extra1->fields['fecha_probable_parto']);
            }// Mayor de 18 años Masuclino no muestra la información de embarazo.

            if (($id_categoria == '6') && ($sexo == 'M')) {
                $embarazada = none;
                $datos_resp = none;
                $mva1 = none;
                $memb = none;
            }//FIN

            $discv = $res_extra1->fields['discv'];
            $disca = $res_extra1->fields['disca'];
            $discmo = $res_extra1->fields['discmo'];
            $discme = $res_extra1->fields['discme'];
            $otradisc = $res_extra1->fields['otradisc'];
            $fecha_inscripcion = Fecha($res_extra1->fields['fecha_inscripcion']);
            $cuie = $res_extra1->fields['cuie_ea'];
            $obsgenerales = $res_extra1->fields['obsgenerales'];
            $tapa_ver = 'block';
            if ($fum == '' || $fum == '0') {
                $fum = date("d/m/Y");
            }
            if ($fecha_diagnostico_emabrazo == '' || $fecha_diagnostico_emabrazo == '0') {
                $fecha_diagnostico_emabrazo = date("d/m/Y");
            }
            if ($fecha_probable_parto == '' || $fecha_probable_parto == '0') {
                $fecha_probable_parto = date("d/m/Y");
            }
        } else {
            $clase_doc2 = str_replace('R', 'P', $clase_doc);
            $clase_doc2 = str_replace('M', 'A', $clase_doc2);
            $sql = "select * from nacer.smiafiliados
						 where afidni='$num_doc' and afitipodoc='$tipo_doc' and aficlasedoc='$clase_doc2'";
            $res_extra = sql($sql, "Error al traer el beneficiario") or fin_pagina();

            if ($res_extra->recordcount() > 0) {
                //$accion="El Beneficiario ya esta Empadronado POR FAVOR VERIFIQUE";
                $accion = "El Beneficiario ya esta Empadronado.";
                //$q="select nextval('uad.beneficiarios_id_beneficiarios_seq') as id_planilla";
                //$id_planilla=sql($q) or fin_pagina();
                //	$id_planilla=$id_planilla->fields['id_planilla'];
                $tipo_transaccion = 'M';
                $tipo_ficha = '2';
                $fecha_carga = date("Y-m-d H:m:s");
                $usuario = $_ses_user['id'];
                $usuario = substr($usuario, 0, 9);
                $sql2 = "insert into uad.beneficiarios
								(id_beneficiarios,estado_envio,clave_beneficiario,tipo_transaccion,apellido_benef      ,apellido_benef_otro,nombre_benef,nombre_benef_otro,clase_documento_benef,tipo_documento,numero_doc,id_categoria    ,sexo   ,fecha_nacimiento_benef,nro_doc_madre,apellido_madre,nombre_madre,fecha_diagnostico_embarazo,cuie_ea,cuie_ah                                    ,departamento      ,localidad,fecha_inscripcion,activo,fecha_carga,usuario_carga,tipo_ficha)
								select nextval('uad.beneficiarios_id_beneficiarios_seq')    ,'p'         ,clavebeneficiario ,'M'             ,case when position(' ' in afiapellido)=0 then afiapellido
																											else substring(afiapellido from 1 for (position(' ' in afiapellido)-1)) end
																											,case when position(' ' in afiapellido)=0 then ''
																											else substring(afiapellido from (position(' ' in afiapellido)+1) for char_length(afiapellido)) end
												,case when position(' ' in afinombre)=0 then afinombre
													else substring(afinombre from 1 for (position(' ' in afinombre)-1)) end, case when position(' ' in afinombre)=0 then ''
																															else substring(afinombre from (position(' ' in afinombre)+1) for char_length(afinombre)) end
									,'$clase_doc'          ,afitipodoc    ,afidni    ,case when afitipocategoria in (4,3) then 5 
																						when afitipocategoria in (1,2,7) then 6
																						end  afitipocategoria,afisexo,afifechanac           ,manrodocumento,maapellido,manombre      ,fechadiagnosticoembarazo   ,cuielugaratencionhabitual,cuielugaratencionhabitual,afidomdepartamento,afidomlocalidad,fechainscripcion,1,'$fecha_carga','$usuario','2'
								 from nacer.smiafiliados
							  where afidni='$num_doc' and afitipodoc='$tipo_doc' and aficlasedoc='$clase_doc2'
							   RETURNING id_beneficiarios";
                $res_extras = sql($sql2, "Error al i el beneficiario") or fin_pagina();
                $id_planilla = $res_extras->fields['id_beneficiarios'];
                $ref = encode_link("ins_admin.php", array("tapa_ver" => 'block', "id_planilla" => $id_planilla, "tipo_transaccion" => $tipo_transaccion, "tipo_ficha" => $tipo_ficha));
                echo "<SCRIPT Language='Javascript'> location.href='$ref'</SCRIPT>";
            } else {
                $accion2 = "Beneficiario no Encontrado";
                $tapa_ver = 'block';
                $datos_resp = 'none';
                $embarazada = 'none';
            }
        }
    } else {
        echo "<SCRIPT Language='Javascript'> alert('Debe Cargar el Nº de Documento'); </SCRIPT>";
    }
}//FIN Busqueda por DNI
//comienza agregado por sistemas Misiones- SS
if ($_POST['guardar'] == "Pasar a No Enviados") {
    $db->StartTrans();
    $fecha_carga = date("Y-m-d H:m:s");
    $usuario = $_ses_user['id'];
    $usuario = substr($usuario, 0, 9);
    $query = "update uad.beneficiarios set estado_envio='n',fecha_verificado='$fecha_carga',usuario_verificado='$usuario'
                where id_beneficiarios=$id_planilla";

    sql($query, "Error al insertar la Planilla") or fin_pagina();

    $accion = "Se guardo la Planilla en estado No Enviados";

    $db->CompleteTrans();


//termina agregado por sistemas Misiones -SS
}

if ($id_planilla) {

    $query = "SELECT beneficiarios.*, smiefectores.nombreefector, smiefectores.cuie
			FROM uad.beneficiarios
			left join facturacion.smiefectores on beneficiarios.cuie_ea=smiefectores.cuie 
  where id_beneficiarios=$id_planilla";

    $res_factura = sql($query, "Error al traer el Comprobantes") or fin_pagina();
    if ($tipo_transaccion == '') {
        $tipo_transaccion = $res_factura->fields['tipo_transaccion'];
    }
    $es_padre = $res_factura->fields['apellido_padre'];
    $es_madre = $res_factura->fields['apellido_madre'];
    $es_tutor = $res_factura->fields['apellido_tutor'];
    $tipo_ficha = $res_factura->fields['tipo_ficha'];
    if ($es_padre != null) {
        $responsable = "PADRE";
        $tipo_doc_madre = $res_factura->fields['tipo_doc_padre'];
        $nro_doc_madre = $res_factura->fields['nro_doc_padre'];
        $apellido_madre = $res_factura->fields['apellido_padre'];
        $nombre_madre = $res_factura->fields['nombre_padre'];
        $alfabeta_madre = $res_factura->fields['alfabeta_padre'];
        $estudios_madre = $res_factura->fields['estudios_padre'];
        $anio_mayor_nivel_madre = $res_factura->fields['anio_mayor_nivel_padre'];
        $menor_convive_con_adulto = $res_factura->fields['menor_convive_con_adulto'];
    } elseif ($es_madre != null) {
        $responsable = "MADRE";
        $tipo_doc_madre = $res_factura->fields['tipo_doc_madre'];
        $nro_doc_madre = $res_factura->fields['nro_doc_madre'];
        $apellido_madre = $res_factura->fields['apellido_madre'];
        $nombre_madre = $res_factura->fields['nombre_madre'];
        $alfabeta_madre = $res_factura->fields['alfabeta_madre'];
        $estudios_madre = $res_factura->fields['estudios_madre'];
        $anio_mayor_nivel_madre = $res_factura->fields['anio_mayor_nivel_madre'];
        $menor_convive_con_adulto = $res_factura->fields['menor_convive_con_adulto'];
    } elseif ($es_tutor != null) {
        $responsable = "TUTOR";
        $tipo_doc_madre = $res_factura->fields['tipo_doc_tutor'];
        $nro_doc_madre = $res_factura->fields['nro_doc_tutor'];
        $apellido_madre = $res_factura->fields['apellido_tutor'];
        $nombre_madre = $res_factura->fields['nombre_tutor'];
        $alfabeta_madre = $res_factura->fields['alfabeta_tutor'];
        $estudios_madre = $res_factura->fields['estudios_tutor'];
        $anio_mayor_nivel_madre = $res_factura->fields['anio_mayor_nivel_tutor'];
        $menor_convive_con_adulto = $res_factura->fields['menor_convive_con_adulto'];
    }

    $estado_envio = $res_factura->fields['estado_envio'];
    $usuario_carga = $res_factura->fields['usuario_carga'];

    $num_doc = $res_factura->fields['numero_doc'];
    $apellido = $res_factura->fields['apellido_benef'];
    $nombre = $res_factura->fields['nombre_benef'];
    $fecha_nac = fecha($res_factura->fields['fecha_nacimiento_benef']);
    if ($nombre_otro == '') {
        $nombre_otro = $res_factura->fields['nombre_benef_otro'];
    }
    if ($apellido_otro == '') {
        $apellido_otro = $res_factura->fields['apellido_benef_otro'];
    }
    $fum = fecha($res_factura->fields['fum']);
    $fecha_diagnostico_embarazo = fecha($res_factura->fields['fecha_diagnostico_embarazo']);
    $semanas_embarazo = $res_factura->fields['semanas_embarazo'];

    $fecha_probable_parto = fecha($res_factura->fields['fecha_probable_parto']);

    $calle = $res_factura->fields['calle'];
    $numero_calle = $res_factura->fields['numero_calle'];
    $anio_mayor_nivel = $res_factura->fields['anio_mayor_nivel'];
    $piso = $res_factura->fields['piso'];
    $dpto = $res_factura->fields['dpto'];
    $manzana = $res_factura->fields['manzana'];
    $entre_calle_1 = $res_factura->fields['entre_calle_1'];
    $entre_calle_2 = $res_factura->fields['entre_calle_2'];
    $telefono = $res_factura->fields['telefono'];
    $cod_pos = $res_factura->fields['cod_pos'];
    $fecha_inscripcion = fecha($res_factura->fields['fecha_inscripcion']);
    $observaciones = $res_factura->fields['observaciones'];
    $cuie = $res_factura->fields['cuie'];
    $score_riesgo = $res_factura->fields['score_riesgo'];
    $pais_nac = $res_factura->fields['pais_nac'];
    $paisn = $res_factura->fields['pais_nac'];
    $provincia_nac = $res_factura->fields['provincia_nac'];
    $localidad_proc = $res_factura->fields['localidad_nac'];
    $departamento = $res_factura->fields['departamento'];
    $localidad = $res_factura->fields['localidad'];
    $municipio = $res_factura->fields['municipio'];
    $barrio = $res_factura->fields['barrio'];

    if ($res_factura->fields['id_categoria'] == 4 || $res_factura->fields['id_categoria'] == 3) {
        $id_categoria = 5;
    } elseif ($res_factura->fields['id_categoria'] == 1 || $res_factura->fields['id_categoria'] == 2 || $res_factura->fields['id_categoria'] == 7) {
        $id_categoria = 6;
    } else {
        $id_categoria = $res_factura->fields['id_categoria'];
    }
    $indigena = $res_factura->fields['indigena'];
    $id_tribu = $res_factura->fields['id_tribu'];
    $id_lengua = $res_factura->fields['id_lengua'];
    $responsable = $res_factura->fields['responsable'];
    $mail = $res_factura->fields['mail'];
    $celular = $res_factura->fields['celular'];
    $otrotel = $res_factura->fields['otrotel'];
    $estadoest = $res_factura->fields['estadoest'];
    $discv = $res_factura->fields['discv'];
    $disca = $res_factura->fields['disca'];
    $discmo = $res_factura->fields['discmo'];
    $discme = $res_factura->fields['discme'];
    $otradisc = $res_factura->fields['otradisc'];
    $obsgenerales = $res_factura->fields['obsgenerales'];
    $menor_convive_con_adulto = $res_factura->fields['menor_convive_con_adulto'];
    $apellidoagente = $res_factura->fields['apellidoagente'];
    $nombreagente = $res_factura->fields['nombreagente'];
    $cuie_agente = $res_factura->fields['centro_inscriptor'];
    $num_doc_agente = $res_factura->fields['dni_agente'];
    if ($fum == '' || $fum == '0') {
        $fum = date("d/m/Y");
    }
    if ($fecha_diagnostico_emabrazo == '' || $fecha_diagnostico_emabrazo == '0') {
        $fecha_diagnostico_emabrazo = date("d/m/Y");
    }
    if ($fecha_probable_parto == '' || $fecha_probable_parto == '0') {
        $fecha_probable_parto = date("d/m/Y");
    }
} //FIN
// Query que muestra la informacion guardada del Beneficiario del Pais de Nacimiento
/* if ($id_planilla !=''){
  $strConsulta = "select pais_nac from uad.beneficiarios where id_beneficiarios = $id_planilla ";
  $result = @pg_exec($strConsulta);
  $fila= pg_fetch_array($result);
  $pais_nac.='<option value="'.$fila["pais_nac"].'">'.$fila["pais_nac"].'</option>';
  $paisn=$fila["pais_nac"];
  }// FIN
  elseif ($id_planilla == '') { */ // Query para traer los paises para luego ser utilizado con AJAX para que no refresque la pagina.
$strConsulta = "select id_pais, upper(nombre)as nombre from uad.pais order by nombre";
$result = @pg_exec($strConsulta);
$pais_nacq = '<option value="-1"> Seleccione Pais </option>';

while ($fila = pg_fetch_array($result)) {

    $pais_nacq.='<option value="' . $fila["nombre"] . '"';
    if ($pais_nac == $fila["nombre"] || $paisn == $fila["nombre"]) {
        $pais_nacq.='selected';
        $paisn = $fila["nombre"];
    }$pais_nacq.='>' . $fila["nombre"] . '</option>';
} // FIN WHILE	
//} // FIN ELSEIF
if ($prov_uso == 'Misiones') {
//DEPARTAMENTO
    $provincia = $prov_uso;
    $departamenton = $departamento;
    $strConsulta = "select d.id_departamento, upper(d.nombre)as nombre from uad.departamentos d inner join uad.provincias p using(id_provincia)
					where upper(p.nombre) = upper('$provincia') order by nombre";
    $result = @pg_exec($strConsulta);
    //if ($id_planilla == ''){
    $departamento = '<option value="-1"> Seleccione Departamento </option>';
    $opciones2 = '<option value="-1" > Seleccione Localidad </option>';
    $opciones3 = '<option value="-1"> Seleccione Municipio </option>';
    $opciones4 = '<option value="-1"> Seleccione Barrio </option>
			<option value="S/D" ';
    if ($barrio == "S/D") {
        $opciones4.='selected';
        $barrion = $fila["nombre"];
    }$opciones4.='> S/D</option>';
    $opciones5 = '<option value="-1"> Codigo Postal  </option>';
    //}	
    while ($fila = pg_fetch_array($result)) {

        $departamento.='<option value="' . $fila["id_departamento"] . '"';
        if ($departamenton == $fila["nombre"]) {
            $departamento.='selected';
            $departamenton = $fila["nombre"];
        }$departamento.='>' . $fila["nombre"] . '</option>';
    } // FIN WHILE
//LOCALIDAD
    $strConsulta = "select upper(l.nombre)as nombre
                            from uad.localidades l
                            inner join uad.departamentos d on l.id_departamento=d.id_departamento
                            where upper(d.nombre) = upper('$departamenton')";
    $result = @pg_exec($strConsulta);
    while ($fila = pg_fetch_array($result)) {
        $opciones2.='<option value="' . $fila["nombre"] . '"';
        if ($localidad == $fila["nombre"]) {
            $opciones2.='selected';
            $localidadn = $fila["nombre"];
        }$opciones2.='>' . $fila["nombre"] . '</option>';
    }
//CODIGO POSTAL
    $strConsulta = "select codpost.codigopostal
				from uad.codpost
				inner join uad.localidades on codpost.id_localidad=localidades.idloc_provincial
				where localidades.nombre='$localidad'";
    $result = @pg_exec($strConsulta);
    while ($fila = pg_fetch_array($result)) {
        $opciones5.='<option value="' . $fila["codigopostal"] . '"';
        if ($cod_pos == $fila["codigopostal"]) {
            $opciones5.='selected';
            $cod_posn = $fila["codigopostal"];
        }$opciones5.='>' . $fila["codigopostal"] . '</option>';
    }

//MUNICIPIO
    $strConsulta = "select upper(nombre)as nombre
						from uad.municipios
						inner join uad.codpost on codpost.id_codpos=municipios.id_codpos
						where codpost.codigopostal='$cod_posn'
						order by nombre";
    $result = @pg_exec($strConsulta);
    while ($fila = pg_fetch_array($result)) {
        $opciones3.='<option value="' . $fila["nombre"] . '"';
        if ($municipio == $fila["nombre"]) {
            $opciones3.='selected';
            $municipion = $fila["nombre"];
        }$opciones3.='>' . $fila["nombre"] . '</option>';
    }

//BARRIO	
    $strConsulta = "select upper(b.nombre)as nombre
					from uad.barrios b
					inner join uad.municipios m on m.idmuni_provincial=b.id_municipio
					where upper(m.nombre)=upper('$municipio') 
									order by b.nombre";
    $result = @pg_exec($strConsulta);
    while ($fila = pg_fetch_array($result)) {
        $opciones4.='<option value="' . $fila["nombre"] . '"';
        if ($barrio == $fila["nombre"]) {
            $opciones4.='selected';
            $barrion = $fila["nombre"];
        }$opciones4.='>' . $fila["nombre"] . '</option>';
        //$barrion=$fila["barrio"];
    }
} else {
    // Query que muestra la informacion guardada del Beneficiario del Departamento donde vive
    if ($id_planilla != '') {
        $strConsulta = "select departamento from uad.beneficiarios where id_beneficiarios = $id_planilla";
        $result = @pg_exec($strConsulta);
        $fila = pg_fetch_array($result);
        $departamento.='<option value="' . $fila["departamento"] . '">' . $fila["departamento"] . '</option>';
        $departamenton = $fila["departamento"];
    }// FIN	
    elseif ($id_planilla == '') {// Query para traer los departamentos para luego ser utilizado con AJAX para que no refresque la pagina.
        $strConsulta = "select id_departamento, nombre from uad.departamentos order by nombre";
        $result = @pg_exec($strConsulta);
        $departamento = '<option value="-1"> Seleccione Departamento </option>';
        $opciones2 = '<option value="-1"> Seleccione Localidad </option>';
        $opciones3 = '<option value="-1"> Seleccione Municipio </option>';
        $opciones4 = '<option value="-1"> Seleccione Barrio </option>';
        $opciones5 = '<option value="-1"> Codigo Postal  </option>';
        while ($fila = pg_fetch_array($result)) {

            $departamento.='<option value="' . $fila["id_departamento"] . '">' . $fila["nombre"] . '</option>';
        } // FIN WHILE
    } //FIN ELSEIF
    // Query que muestra la informacion guardada del Beneficiario de la Localidad donde vive
    if ($id_planilla != '') {
        $strConsulta = "select localidad from uad.beneficiarios where id_beneficiarios = $id_planilla";
        $result = @pg_exec($strConsulta);
        $fila = pg_fetch_array($result);
        $opciones2.='<option value="' . $fila["localidad"] . '">' . $fila["localidad"] . '</option>';
        $localidadn = $fila["localidad"];
    }// FIN
    // Query que muestra la informacion guardada del Beneficiario del Barrio donde vive
    if ($id_planilla != '') {
        $strConsulta = "select barrio from uad.beneficiarios where id_beneficiarios = $id_planilla";
        $result = @pg_exec($strConsulta);
        $fila = pg_fetch_array($result);
        $opciones4.='<option value="' . $fila["barrio"] . '">' . $fila["barrio"] . '</option>';
        $barrion = $fila["barrio"];
    }// FIN
    // Query que muestra la informacion guardada del Beneficiario del Municipio donde vive
    if ($id_planilla != '') {
        $strConsulta = "select cod_pos from uad.beneficiarios where id_beneficiarios = $id_planilla";
        $result = @pg_exec($strConsulta);
        $fila = pg_fetch_array($result);
        $opciones5.='<option value="' . $fila["cod_pos"] . '">' . $fila["cod_pos"] . '</option>';
        $cod_posn = $fila["cod_pos"];
    }// FIN
    // Query que muestra la informacion guardada del Beneficiario del Municipio donde vive
    if ($id_planilla != '') {
        $strConsulta = "select municipio from uad.beneficiarios where id_beneficiarios = $id_planilla";
        $result = @pg_exec($strConsulta);
        $fila = pg_fetch_array($result);
        $opciones3.='<option value="' . $fila["municipio"] . '">' . $fila["municipio"] . '</option>';
        $municipion = $fila["municipio"];
    }// FIN
}//FIN SI NO ES MISIONES





echo $html_header;

$directorio_base = trim(substr(ROOT_DIR, strrpos(ROOT_DIR, chr(92)) + 1, strlen(ROOT_DIR)));
?>
<script type="text/javascript" src="/<?php echo $directorio_base ?>/lib/jquery-1.5.1.js"> </script>
<script>
    function tapa(evt){
        var key = nav4 ? evt.which : evt.keyCode; 
        if(document.all.tapa_ver.value=='block' && key!=9 )
        {
            location.href='ins_admin.php?tapa_ver="none"';
        }
    }

    var nav4 = window.Event ? true : false;
    function acceptNum(evt){ 
        var key = nav4 ? evt.which : evt.keyCode; 
        return (key <= 13 || (key >= 48 && key <= 57));
    }
    // Script para el manejo de combobox de Departamento - Localidad - Codigo Postal - Municipio y Barrio
    $(document).ready(function(){
        $("#departamento").change(function(){
            $.ajax({
                url:"procesa.php",
                type: "POST",
                data:"id_departamento="+$("#departamento").val()+"&provincia="+document.all.prov_uso.value,//$("#prov_uso").val(),
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
    });

    //function cambio_newbarrio(){
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
    //}

    /*$(document).ready(function(){
        $("input[name='nro_doc_madre']").focus(function(){ //alert();
                if(document.all.responsable.value==-1){
                        alert('Seleccione Tipo de Responsable');
                        document.all.responsable.focus();
                }
        });
});*/

    $(document).ready(function(){
        $("input[name='nro_doc_madre']").blur(function(){ //alert();
            //if(document.all.responsable.value!=-1){
            $.ajax({
                url:"procesa.php",
                type: "POST",
                data:"nro_doc_madre="+$(this).val()+"&responsable="+document.all.responsable.value,
                success: function(retorno){//alert(retorno);
                    var retorno = retorno.split("*"); 
                    //alert(retorno);
                    $("select[name='responsable']").val(retorno[0]);
                    $("select[name='tipo_doc_madre']").val(retorno[1]);
                    $("input[name='apellido_madre']").val(retorno[2]);
                    $("input[name='nombre_madre']").val(retorno[3]);
                    if(retorno[4]=='S'){
                        document.all.alfabeta_madre[0].checked=true;
                    }else{
                        document.all.alfabeta_madre[0].checked=true;
                        document.all.estudios_madre[0].checked=false;
                        document.all.estudios_madre[1].checked=false;
                        document.all.estudios_madre[2].checked=false;
                        document.all.anio_mayor_nivel_madre.value='0';
                    }
                    if(retorno[5].toUpperCase()=='INICIAL'){
                        document.all.estudios_madre[0].checked=true;
                    }else{
                        if(retorno[5].toUpperCase()=='PRIMARIO'){
                            document.all.estudios_madre[1].checked=true;
                        }else{
                            if(retorno[5].toUpperCase()=='SECUNDARIO'){ 
                                document.all.estudios_madre[2].checked=true;
                            }else{
                                if(retorno[5].toUpperCase()=='TERCIARIO'){
                                    document.all.estudios_madre[3].checked=true;
                                }else{
                                    if(retorno[5].toUpperCase()=='UNIVERSITARIO'){
                                        document.all.estudios_madre[4].checked=true;
                                    }
									
                                }
                            }
                        }
                    }
                    $("select[name='estadoest_madre']").val(retorno[6]);
                    $("input[name='anio_mayor_nivel_madre']").val(retorno[7]);
                }
            });
            //}
        });
    });


    /*enter por tab*/
 



    //Guarda el nombre del Pais
    function showpais_nac(){
        var pais_nac = document.getElementById('pais_nac')[document.getElementById('pais_nac').selectedIndex].innerHTML;
        document.all.paisn.value =  pais_nac;
    }// FIN

    // Guarda el nombre del Departamento
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

    //Validar Fechas
    function esFechaValida(fecha){
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


		 
                //controlan que ingresen todos los datos necesarios par el muleto
                function control_nuevos()
                {
                    var fecha=document.getElementById('fecha_nac');
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
	
                                var fecha=document.getElementById('fecha_inscripcion');
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
	
	
                                            // Calculo de días para fecha de Nacimiento Mayor a Fecha Actual
                                            function fechaNacAct(){  
                                                var d1 = $('#fecha_nac').val().split("/");  
                                                var dat1 = new Date(d1[2], parseFloat(d1[1])-1, parseFloat(d1[0]));  
                                                var d2 = $('#fecha_inscripcion').val().split("/");  
                                                var dat2 = new Date(d2[2], parseFloat(d2[1])-1, parseFloat(d2[0]));  
  
                                                var fin = dat2.getTime() - dat1.getTime();  
                                                var dias = Math.floor(fin / (1000 * 60 * 60 * 24))    
  
                                                return dias;  
                                            }  // FIN

                                            if ((fechaNacAct() <= '-1' ) )
	
                                            {
                                                alert("La Fecha de Nacimiento no puede ser igual o mayor al día de hoy");
                                                document.all.fecha_nac.focus();
                                                return false;
                                            }

                                            /*/ Documento Ajeno y Menor de 1 año de Edad.
                if((document.all.clase_doc.value =='A') && (ed == -1)){
                        var num1=document.all.nro_doc_madre.value;
                        var num2=document.all.num_doc.value;
                        if (num1 != num2){
                                alert("Los numeros de documento deben coincidir");
                                document.all.num_doc.focus();
                                return false;
                        }
                } // FIN*/
		
                                            // Fecha de Inscripcion mayor a 01/08/2004.
                                            if (document.all.fecha_inscripcion.value <= '01/08/2004'){
                                                alert ("La fecha de inscripcion debe ser mayor a 01/08/2004");
                                                document.all.fecha_inscripcion.focus();
                                                return false;
                                            } 	// FIN
			
                                            /*Control de Agente*/
                                            if(document.all.cuie_agente.value=="-1"){
                                                alert("Debe elegir un centro inscriptor");
                                                document.all.cuie_agente.focus();
                                                return false;
                                            }
                                            if(document.all.apellidoagente.value==""){
                                                alert("Debe completar el campo apellido Agente");
                                                document.all.apellidoagente.focus();
                                                return false;
                                            }else{
                                                var charpos = document.all.apellidoagente.value.search("[^A-Za-zñÑ/ \s/]")
	 
                                                if( charpos >= 0)
                                                {
                                                    alert( "El campo Apellido Agente solo permite letras ");
                                                    document.all.apellidoagente.focus();
                                                    return false;
                                                }
                                            }
                                            if(document.all.nombreagente.value==""){
                                                alert("Debe completar el campo nombre Agente");
                                                document.all.nombreagente.focus();
                                                return false;
                                            }else{
                                                var charpos = document.all.nombreagente.value.search("[^A-Za-zñÑ/ \s/]");
                                                if( charpos >= 0)
                                                {
                                                    alert( "El campo Nombre Agente solo permite letras ");
                                                    document.all.nombreagente.focus();
                                                    return false;
                                                }
                                            }
		 
		 
                                            if(document.all.mail.value!=""){ 
                                                if (!(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(document.all.mail.value))){  
	  
                                                    alert("La dirección de email es incorrecta.");  
                                                    document.all.mail.focus();
                                                    return false;
                                                }
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
                                                //var charpos = document.all.apellido.value.search("/[^A-Za-z\s]/"); 
                                                var charpos = document.all.apellido.value.search("[^A-Za-zñÑ/\s/]"); 
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
                                                var charpos = document.all.nombre.value.search("[^A-Za-zñÑ/\s/]"); 
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
                                            if(document.all.pais_nac.value=="-1"){
                                                alert("Debe completar el campo pais");
                                                document.all.pais_nac.focus();
                                                return false;
                                            }
                                            /*if(document.all.provincia_nac.value=="-1"){
                alert("Debe completar el campo localidad");
                document.all.provincia_nac.focus();
                 return false;
 }*/
		 
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
                                            if(document.all.departamento.value=="-1" || document.all.departamento.value==""){
                                                alert("Debe completar el campo departamento");
                                                document.all.departamento.focus();
                                                return false;
                                            }

                                            if(document.all.localidad.value=="-1" || document.all.localidad.value==""){
                                                alert("Debe completar el campo Localidad");
                                                document.all.localidad.focus();
                                                return false;
                                            }
                                            if(document.all.municipio.value=="-1" || document.all.municipio.value==""){
                                                alert("Debe completar el campo Municipio");
                                                document.all.municipio.focus();
                                                return false;
                                            }

                                            if(document.all.barrio.value=="-1" || document.all.barrio.value==""){
                                                alert("Debe completar el campo Barrio");
                                                document.all.barrio.focus();
                                                return false;
                                            }
                                            if(document.all.id_categoria.value=='-1'){
                                                edad(document.all.fecha_nac.value);
                                                return false;
                                            }
                                            if(document.all.cuie.value=="-1"){
                                                alert('Debe Seleccionar un Efector');
                                                document.all.cuie.focus();
                                                return false;
                                            } 

	 
                                            //if (document.all.id_categoria.options[document.all.id_categoria.selectedIndex].text.substr(0,1)=='5')
                                            if (document.all.id_categoria.value=='5'){
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
                                                    //document.all.num_doc_madre.focus();
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
                                                    alert('El dato ingresado en años mayor nivel debe ser entero');
                                                    return false;
                                                }
                                                if(document.all.apellido_madre.value==""){
                                                    alert("Debe completar el campo apellido del responsable");
                                                    document.all.apellido_madre.focus();
                                                    return false;
                                                }else{
                                                    var charpos = document.all.apellido_madre.value.search("[^A-Za-zñÑ/ \s/]"); 
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
                                                    var charpos = document.all.nombre_madre.value.search("[^A-Za-zñÑ/ \s/]"); 
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
                                            if(docu!='R' && docu!='P'){ 
                                                var num1=document.all.nro_doc_madre.value;
                                                var num2=document.all.num_doc.value;
                                                if (num1 != num2){
                                                    alert("Los numeros de documento deben coincidir");
                                                    document.all.num_doc.focus();
                                                    return false;
                                                }
                                            }

                                            if (document.all.id_categoria.value=='6' && document.all.sexo.value == 'F'){
                                                //si es mayor de 18 años y mujer
                                                if(document.all.fecha_diagnostico_embarazo.value==""){
                                                    alert("Debe completar el campo fecha de diagnostico de embarazo");
                                                    return false;
                                                }
                                                if(document.all.fecha_probable_parto.value==""){
                                                    alert("Debe completar el campo fecha probable de parto");
                                                    return false;
                                                }
                                            }
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
                                                alert("Debe completar el campo Codigo Postal");
                                                document.all.cod_pos.focus();
                                                return false;
                                            }
		 
                                            if(document.all.barrio.value=="-1"){
                                                alert("Debe completar el campo Barrio");
                                                document.all.barrio.focus();
                                                return false;
                                            }

                                        }//de function control_nuevos()

                                        function verificaFPP(){  
                                            var d1 = $('#fecha_probable_parto').val().split("/");  
                                            var dat1 = new Date(d1[2], parseFloat(d1[1])-1, parseFloat(d1[0]));  
                                            var d2 = $('#fecha_inscripcion').val().split("/");  
                                            var dat2 = new Date(d2[2], parseFloat(d2[1])-1, parseFloat(d2[0]));  
  
                                            var fin = dat2.getTime() - dat1.getTime();  
                                            var dias = Math.floor(fin / (1000 * 60 * 60 * 24))    
  
                                            return dias;
    
    
                                        }  // FIN

                                        // Valida que la Fecha Probable de Parto no supere los 45 días después del Parto
                                        function mostrarDias(){
                                            if (verificaFPP () >= '46'){
		
                                                alert ("No se puede Inscribir porque supero los 45 días después del Parto");
                                                document.all.fecha_probable_parto.focus();
                                                return false;
                                            }
                                        } // FIN

                                        // Fecha Diagnostico de Embarazo no puede ser superior a la Fecha de Inscripción
                                        function validaFDE(){  
                                            var d1 = $('#fecha_diagnostico_embarazo').val().split("/");  
                                            var dat1 = new Date(d1[2], parseFloat(d1[1])-1, parseFloat(d1[0]));  
                                            var d2 = $('#fecha_inscripcion').val().split("/");  
                                            var dat2 = new Date(d2[2], parseFloat(d2[1])-1, parseFloat(d2[0]));  
  
                                            var fin = dat2.getTime() - dat1.getTime();  
                                            var dias = Math.floor(fin / (1000 * 60 * 60 * 24))    
  
                                            return dias;  
                                        }  // FIN

                                        // Valida que la Fecha de Diagnostico de Embarazo sea menor a la Fecha de Inscripcion
                                        function mostrarFDE(){
                                            if ((validaFDE() <= '-1') || (validaFDE() == '0')) {
		
                                                alert ("La Fecha de Diagnostico de Embarazo tiene que ser menor a la Fecha de Inscripción");
                                            }
                                        } // FIN

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
                                        var buffer=new Array(digitos); //declaraciï¿½n del array Buffer
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

                                        // muestra o no lo información de Parto dependiendo del sexo y si vive o no con un adulto dependiendo de la edad
                                        function cambiar_patalla(){ 
                                            // Masculino - Menor de 18 años edad no muestra la información de embarazo, muestra la información de menor vive con adulto 
                                            //y pide la información del adulto aunque el menor no viva con el. 
                                            if ((document.all.sexo.value == 'M') && (document.all.id_categoria.value == '5')) {
                                                /*document.getElementById('cat_emb').setAttribute('style','display:none');
        document.getElementById('cat_nino').setAttribute('style','display:inline');
        document.getElementById('mva').setAttribute('style','display:inline');
        document.getElementById('memb').setAttribute('style','display:none');*/
                                                document.all.cat_emb.style.display='none';
                                                document.all.cat_nino.style.display='inline';
                                                document.all.mva.style.display='inline';
                                                document.all.memb.style.display='none';
			
                                            }//fin
	
                                            // Masculino - Mayor de edad 19 años no muestra la información de embarazo, no muestra la información de menor vive con adulto 
                                            if ((document.all.sexo.value == 'M') && (document.all.id_categoria.value == '6')) {
                                                document.all.cat_emb.style.display='none';
                                                document.all.cat_nino.style.display='none';
                                                document.all.mva.style.display='none';
                                                document.all.memb.style.display='none';
                                            } //fin

                                            // Femenino - Menor de 9 años no muestra la información de embarazo, muestra la información de menor vive con adulto 
                                            //y pide la información del adulto aunque el menor no viva con el. 
                                            if ((document.all.sexo.value == 'F') && (document.all.id_categoria.value == '5')&& (document.all.edades.value <= '9')) {
                                                document.all.cat_emb.style.display='none';
                                                document.all.cat_nino.style.display='inline';
                                                document.all.mva.style.display='inline';
                                                document.all.memb.style.display='none';
                                                document.all.menor_embarazada.display='none';
                                            }
                                            /*if ((document.all.sexo.value == 'F') && (document.all.id_categoria.value == '5')&& (document.all.edades.value <= '9')) {
                 document.getElementById('cat_emb').setAttribute('style','display:none');
         document.getElementById('cat_nino').setAttribute('style','display:inline');
         document.getElementById('mva').setAttribute('style','display:inline');
         document.getElementById('memb').setAttribute('style','display:none');
	
                }*/ //fin
	
                                            // Femenino - Mayor de 10 años puede o no estar embarazada, muestra la información de menor vive con adulto
                                            // pide la información del adulto aunque el menor no viva con el y pregunta si esta o no embarazada
                                            if ((document.all.sexo.value == 'F') && (document.all.id_categoria.value == '5') && (document.all.edades.value >= '10')) {
                                                document.all.cat_emb.style.display='none';
                                                document.all.cat_nino.style.display='inline';
                                                document.all.mva.style.display='inline';
                                                document.all.memb.style.display='inline';
                                                // Si esta embarazada muestra la información del embarazo
                                                if (document.all.menor_embarazada.value=='S'){
                                                    document.all.cat_emb.style.display='inline';
                                                } //fin
                                            } //fin
		
                                            // Femenino - Mayor de 19 años de edad muestra la información de embarazo, no muestra la información de menor vive con adulto 
                                            if ((document.all.sexo.value == 'F') && (document.all.id_categoria.value == '6')) {
                                                document.all.cat_emb.style.display='inline';
                                                document.all.cat_nino.style.display='none';
                                                document.all.mva.style.display='none';
                                                document.all.memb.style.display='none';
                                            } //fin

                                        } // FIN Cambiar_Patalla()

                                        //INICIO /////  agregado 01-11-2011
                                        function DiferenciaFechas (CadenaFecha1) {  
  
                                            //Obtiene dia, mes y año  
                                            var fecha1 = new fecha( CadenaFecha1 )     
     
                                            //Obtiene objetos Date  
                                            var miFecha1 = new Date( fecha1.anio, fecha1.mes - 1, fecha1.dia )  
                                            var hoy = new Date()  
                                            //alert(miFecha1)
                                            //alert(hoy)
                                            //Resta fechas y redondea  
                                            var diferencia = hoy.getTime() - miFecha1.getTime() 
                                            var anios = diferencia / (1000 * 60 * 60 * 24 * 365)  
                                            //var segundos = Math.floor(diferencia / 1000)  
                                            //alert ('La diferencia es de ' + dias + ' dias,\no ' + segundos + ' segundos.')  
     
                                            return anios  
                                        }  
  
                                        function fecha( cadena ) {  
  
                                            //Separador para la introduccion de las fechas  
                                            var separador = "/"  
  
                                            //Separa por dia, mes y año  
                                            if ( cadena.indexOf( separador ) != -1 ) {  
                                                var posi1 = 0  
                                                var posi2 = cadena.indexOf( separador, posi1 + 1 )  
                                                var posi3 = cadena.indexOf( separador, posi2 + 1 )  
                                                this.dia = cadena.substring( posi1, posi2 )  
                                                this.mes = cadena.substring( posi2 + 1, posi3 )  
                                                this.anio = cadena.substring( posi3 + 1, cadena.length )  
                                            } else {  
                                                this.dia = false  
                                                this.mes = false
                                                this.anio = false  
                                            }  
                                        }  
                                        // FIN /// agregado 01-11-2011

                                        // calcula la edad y da el valor de la categoria
                                        function edad(Fecha){
                                            //document.all.campo_actual.value='pais_nac';
                                            //fecha = new Date(Fecha)
                                            hoy = new Date()
                                            //ed = parseInt((hoy -fecha)/365/24/60/60/1000)

                                            var ed = DiferenciaFechas(Fecha)

                                            //alert(ed);
                                            //si es mayor de 19 años categoria 6
                                            if (ed >= 18) {
                                                document.getElementById('id_categoria').value = '6';
                                                document.getElementById("edades").value =  ed;	
                                                document.all.memb.style.display='none';
                                                document.all.cat_nino.style.display='none';
                                                //document.forms[0].submit();	
                                            }
                                            //si es menor de 18 años categoria 5
                                            if (ed < 18) {
                                                document.getElementById('id_categoria').value = '5';
                                                document.getElementById("edades").value =  ed;
                                                document.all.cat_nino.style.display='inline';
                                                if (ed >= '9') {
                                                    document.all.memb.style.display='inline';
                                                }
                                                //document.forms[0].submit();
                                            }
		
                                        } //FIN calculo de edad y categoría

                                        //Desarma la fecha para calcular la FPP
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
                                        } //FIN Fecha para calculo de  FPP

                                        // Calcula la FPP
                                        function recalcF1(){
                                            with (document.form1){
                                                fecha_probable_parto.value = addToDate(fecha_diagnostico_embarazo.value, 280 - (semanas_embarazo.value *7));
                                            }
                                        } // FIN FPP

                                        // Calcula la FPP en funcion fumm
                                        function recalcF1_fum(){
                                            with (document.form1){ 
                                                if(addToDate(fum.value, 40)!='NaN/NaN/0NaN'){
                                                    fecha_probable_parto.value = addToDate(fum.value, 40);
                                                }
                                            }
                                        } // FIN FPP

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

                                        function pulsar(e) {
                                            tecla = (document.all) ? e.keyCode :e.which;
                                            return (tecla!=13);
                                        } 
</script>
<form name='form1' action='ins_admin.php' accept-charset="utf-8" method='POST'>
    <input type="hidden" value="<?= $tipo_ficha ?>" name="tipo_ficha">
    <input type="hidden" value="<?= $id_planilla ?>" name="id_planilla">
    <input type="hidden" value="<?= $campo_actual ?>" name="campo_actual">
    <input type="hidden" value="<?= $remediar ?>" name="remediar">
    <input type="hidden" value="<?= $clave_beneficiario ?>" name="clave_beneficiario">
    <input type="hidden" value="<?= $prov_uso ?>" name="prov_uso">
    <input type="hidden" value="<?= $provincia_nac ?>" name="provincia_nac">
    <input type="hidden" value="<?= $localidad_proc ?>" name="localidad_proc">
    <input type="hidden" value="<?= $tapa_ver ?>" name="tapa_ver">
<? echo "<center><b><font size='+1' color='red'>$accion</font></b></center>"; ?>
<? echo "<center><b><font size='+1' color='Blue'>$accion2</font></b></center>"; ?>
    <table width="100%" cellspacing="0" border="1" bordercolor="#E0E0E0" align="center" bgcolor='<?= $bgcolor_out ?>' class="bordes">
        <select name="id_categoria" Style="display:none" onKeypress="buscar_combo(document);" onblur="borrar_buffer();" onchange="borrar_buffer(); cambiar_patalla(); document.forms[0].submit();" <?php if (($id_planilla) and ($tipo_transaccion != "M")) echo "disabled" ?>>
<?
$sql = "select * from uad.categorias order by id_categoria";
$res_efectores = sql($sql) or fin_pagina();
?>

            <option value='-1' selected>Seleccione</option>
<?
while (!$res_efectores->EOF) {
    $id_categorial = $res_efectores->fields['id_categoria'];
    $tipo_ficha = $res_efectores->fields['tipo_ficha'];
    $categoria = $res_efectores->fields['categoria'];
    ?>
                <option value='<?= $id_categorial ?>'<? if ($id_categoria == $id_categorial)
        echo "selected"; ?> <? echo $tipo_ficha . "-" . $categoria; ?>></option>
    <? $res_efectores->movenext();
} ?>
        </select>
        <tr id="mo">
            <td>
<?
if (!$id_planilla) {
    ?>  
                    <font size=+1><b>Nuevo Formulario</b></font>   
<?
} else {
    ?>
                    <font size=+1><b>Formulario</b></font>   
<? } ?>

            </td>
        </tr>
        <tr><td>
                <table width=100% align="center" class="bordes">
                    <tr>     
                        <td>
                            <table class="bordes" align="center">             
                                <tr>	           
                                    <td align="center" colspan="4" id="ma">
                                        <b> Número de Formulario: <font size="+1" color="Blue"><?= ($id_planilla) ? $clave_beneficiario : "Nuevo" ?></font> </b> <? if ($trans == 'Borrado') { ?> <b><font size="+1" color="Blue"><?= ($id_planilla) ? $trans : $trans ?></font></b><? } ?>           </td>
                                </tr>

                                <tr id="tapa" style="display:<?= $tapa_ver ?>">	           
                                    <td align="right" width="20%">
                                        <b>Tipo de Transaccion:</b>			</td>
                                    <td align="left" width="30%">			 	
                                        <select name=tipo_transaccion Style="width:200px"
                                                onKeypress="buscar_combo(this);"
                                                onblur="borrar_buffer();"
                                                onchange="borrar_buffer();document.forms[0].submit()" 
<?php if ($trans == 'Borrado')
    echo "disabled" ?>
                                                >
                                            <option value='A' <? if ($tipo_transaccion == 'A')
    echo "selected" ?>>Inscripcion</option>
                                            <option value='M'<? if ($tipo_transaccion == 'M')
    echo "selected" ?>>Modificacion</option>
                                            <option value='B'<? if ($tipo_transaccion == 'B')
    echo "selected" ?>>Baja</option>
                                        </select>			</td>            
                                    <td align="left" colspan="2">
                                        <b><font size="0" color="Red">Nota: Los valores numericos se ingresan SIN separadores de miles, y con "." como separador DECIMAL</font> </b>           </td>
                                </tr>  
<? if ($ape_nom == 's') { ?>
                                    <tr id="tapa" style="display:<?= $tapa_ver ?>">
                                        <td align="right">
                                            <b><font color="Red">*</font>Primer Apellido:</b>         	</td>
                                        <td align='left'><input type="text" size="30" value="<?= $apellido ?>" name="apellido" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
        echo "readOnly" ?> maxlength="50" onkeypress="return pulsar(event);"/></td>
                                        <td align="right">
                                            <b>Otros Apellidos:</b>         	</td>
                                        <td align='left'>
                                            <input type="text" size="30" value="<?= $apellido_otro ?>" name="apellido_otro" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
        echo "readOnly" ?> maxlength="30" onkeypress="return pulsar(event);">            </td>
                                    </tr>
                                    <tr id="tapa" style="display:<?= $tapa_ver ?>">
                                        <td align="right">
                                            <b><font color="Red">*</font>Primer Nombre:</b>         	</td>
                                        <td align='left'>
                                            <input type="text" size="30" value="<?= $nombre ?>" name="nombre" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
        echo "readOnly" ?> maxlength="50" onkeypress="return pulsar(event);">            </td>
                                        <td align="right">
                                            <b>Otros Nombres:</b>         	</td>
                                        <td align='left'>
                                            <input type="text" size="30" value="<?= $nombre_otro ?>" name="nombre_otro" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
        echo "readOnly" ?> maxlength="30" onkeypress="return pulsar(event);">            </td>
                                    </tr>
<? }else { ?>       
                                    <tr id="tapa" style="display:<?= $tapa_ver ?>">

                                        <td align="right">
                                            <b><font color="Red">*</font>Apellidos:</b>         	</td>         	
                                        <td align='left'>
                                            <input type="text" size="30" value="<?= $apellido ?>" name="apellido" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
        echo "disabled" ?> onkeypress="return pulsar(event);">            </td>
                                        <td align="right">
                                            <b><font color="Red">*</font>Nombres:</b>         	</td>         	
                                        <td align='left'>
                                            <input type="text" size="30" value="<?= $nombre ?>" name="nombre" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                        echo "disabled" ?> onkeypress="return pulsar(event);">            </td>
                                    </tr><? } ?>

                                <tr>
                                    <td align="right">
                                        <font color="Red">*</font><b>El Documento es:</b>			</td>
                                    <td align="left">			 	
                                        <select name=clase_doc Style="width:200px" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                    echo "disabled" ?>>
                                            <option value=P <? if ($clase_doc == 'P')
                                    echo "selected" ?>>Propio</option>
                                            <option value=A <? if ($clase_doc == 'A')
                                    echo "selected" ?>>Ajeno</option>
                                        </select>			</td> 
                                    <td align="right">
                                        <font color="Red">*</font><b>Tipo de Documento:</b>			</td>
                                    <td align="left">			 	
                                        <select name=tipo_doc Style="width:200px" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                    echo "disabled" ?>>
                                            <option value=DNI <? if ($tipo_doc == 'DNI')
                                    echo "selected" ?>>Documento Nacional de Identidad</option>
                                            <option value=LE <? if ($tipo_doc == 'LE')
                                    echo "selected" ?>>Libreta de Enrolamiento</option>
                                            <option value=LC <? if ($tipo_doc == 'LC')
                                            echo "selected" ?>>Libreta Civica</option>
                                            <option value=PA <? if ($tipo_doc == 'PA')
                                            echo "selected" ?>>Pasaporte Argentino</option>
                                            <option value=CM <? if ($tipo_doc == 'CM')
                                            echo "selected" ?>>Certificado Migratorio</option>
                                        </select>			</td>
                                </tr>
                                <tr>
                                    <td align="right" width="20%">
                                        <b><font color="Red">*</font>Número de Documento:</b>         	</td>         	
                                    <td align='left' width="30%">
                                        <input type="text" size="30" value="<?= $num_doc ?>" name="num_doc" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                            echo "disabled" ?> onKeyPress="tapa(event); return acceptNum(event);return pulsar(event);" maxlength="9" onkeydown="tapa(event);return pulsar(event);" >
<? if (!$id_planilla) { ?><input type="submit" size="3" value="b" name="b" onclick="document.all.campo_actual.value='apellido';"><? } ?><br><font color="Red">Sin Puntos</font>            </td>
                                </tr>
                                <tr id="tapa" style="display:<?= $tapa_ver ?>">
                                    <td align="right">
                                        <b> Mail: </b>                        </td>
                                    <td align="left">
                                        <input type="text" size="35" name="mail" value="<?= $mail ?>" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                                echo "disabled" ?> maxlength="35" onkeypress="return pulsar(event);">                        </td>
                                    <td align="right">
                                        <b>Celular:</b>                        </td>
                                    <td align="left">
                                        <input type="text" size="30" name="celular" value="<?= $celular ?>" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                                echo "disabled" ?> maxlength="40" onkeypress="return pulsar(event);">                        </td>
                                </tr>

                                <tr id="tapa" style="display:<?= $tapa_ver ?>">	           
                                    <td align="center" colspan="4" id="ma">
                                        <b> Datos de Nacimiento, Sexo, Origen y Estudios </b>           </td>
                                </tr>

                                <tr id="tapa" style="display:<?= $tapa_ver ?>">
                                    <td align="right">
                                        <b><font color="Red">*</font>Sexo:</b>			</td>

                                    <td align="left">			 	
                                        <select name=sexo Style="width:200px" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                            echo "disabled" ?> onchange="cambiar_patalla();">
                                            <option value='-1' >Seleccione</option>
                                            <option value=F <? if ($sexo == 'F')
                                            echo "selected" ?>>Femenino</option>
                                            <option value=M <? if ($sexo == 'M')
                                            echo "selected" ?>>Masculino</option>
                                        </select>			</td> 

                                    <td align="right">
                                        <b><font color="Red">*</font>Fecha de Nacimiento:</b> <input type="hidden" name="edades" id=edades value="<?= $edad ?>">			</td>

                                    <td align="left">
                                        <input type=text name=fecha_nac id=fecha_nac onchange="esFechaValida(this); cambiar_patalla();" onblur="edad(this.value); cambiar_patalla(); " value='<?= $fecha_nac; ?>' size=15 <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                            echo "disabled" ?> maxlength="10" onKeyUp="mascara(this,'/',patron,true); return pulsar(event);" onkeypress="return pulsar(event);">
<?= link_calendario('fecha_nac'); ?>		    	</td>		    	     
                                </tr>   

                                <tr id="tapa" style="display:<?= $tapa_ver ?>">
                                    <td align="right" >
                                        <b><font color="Red">*</font>Extranjero/Pais:</b> <input type="hidden" name="paisn" value="<?= $paisn ?>">    		</td>
                                    <td align="left">
                                        <select id="pais_nac" name="pais_nac" onchange="showpais_nac();" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
    echo "disabled" ?>><?php echo $pais_nacq; ?></select>    		</td>

                                    <td align="right">
                                        <b>¿Pertenece a algún Pueblo Indígena?</b>         	</td>         	
                                    <td align='left'>
                                        <input type="radio" name="indigena" value="N" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
    echo "disabled" ?> <?php if (($indigena == "N") or ($indigena == ""))
    echo "checked"; ?> onclick="document.all.id_tribu.value='0';document.all.id_lengua.value='0';" > NO
                                        <input type="radio" name="indigena" value="S" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
    echo "disabled" ?> <?php if ($indigena == "S")
    echo "checked"; ?> onclick="document.all.id_tribu.disabled=false;document.all.id_lengua.disabled=false;"> SI            </td>
                                </tr> 

                                <tr id="tapa" style="display:<?= $tapa_ver ?>">
                                    <td align="right">
                                        <b>Pueblo Indigena:</b>         	</td>         	
                                    <td align='left'>
                                        <select name=id_tribu Style="width:200px" 
                                                onKeypress="buscar_combo(this);"
                                                onblur="borrar_buffer();"
                                                onchange="borrar_buffer();" 
<?php if (($id_planilla) and ($tipo_transaccion != "M"))
    echo "disabled" ?>>
                                            <option value='-1'>Seleccione</option>
<?
$sql = "select * from uad.tribus order by nombre";
$res_efectores = sql($sql) or fin_pagina();
while (!$res_efectores->EOF) {
    $id = $res_efectores->fields['id_tribu'];
    $nombre = $res_efectores->fields['nombre'];
    ?>
                                                <option value='<?= $id ?>' <? if ($id_tribu == $id)
        echo "selected" ?> ><?= $nombre ?></option>
    <?
    $res_efectores->movenext();
}
?>
                                        </select>            </td>
                                    <td align="right">
                                        <b>Idioma O Lengua:</b>         	</td>         	
                                    <td align='left'>
                                        <select name=id_lengua Style="width:200px" 
                                                onKeypress="buscar_combo(this);"
                                                onblur="borrar_buffer();"
                                                onchange="borrar_buffer();" 
<?php if (($id_planilla) and ($tipo_transaccion != "M"))
    echo "disabled" ?>>
                                            <option value='-1'>Seleccione</option>
<?
$sql = "select * from uad.lenguas";
$res_efectores = sql($sql) or fin_pagina();
while (!$res_efectores->EOF) {
    $id = $res_efectores->fields['id_lengua'];
    $nombre = $res_efectores->fields['nombre'];
    ?>
                                                <option value='<?= $id ?>' <? if ($id_lengua == $id)
        echo "selected" ?> ><?= $nombre ?></option>

    <?
    $res_efectores->movenext();
}
?>
                                        </select>            </td>
                                </tr> 

                                <tr id="tapa" style="display:<?= $tapa_ver ?>">
                                    <td align="right">
                                        <b>Alfabetizado:</b>         	</td>         	
                                    <td align='left'>
                                        <input type="radio" name="alfabeta" value="S" onclick="document.all.estudios[1].checked=true" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
    echo "disabled" ?> <?php if (($alfabeta == "S") or ($alfabeta == ""))
    echo "checked"; ?>> SI
                                        <input type="radio" name="alfabeta" value="N" onclick="document.all.estudios[0].checked=false;document.all.estudios[1].checked=false;document.all.estudios[2].checked=false;document.all.anio_mayor_nivel.value='0';" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
    echo "disabled" ?> <?php if ($alfabeta == "N")
    echo "checked"; ?>> NO            </td>
                                    <td align="right">
                                        <b>Estado:</b>            </td>    
                                    <td align="left">			 	
                                        <select name=estadoest Style="width:200px" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
    echo "disabled" ?>>

                                            <option value=C <? if ($estadoest == 'C')
    echo "selected" ?>>Completo</option>
                                            <option value=I <? if ($estadoest == 'I')
    echo "selected" ?>>Incompleto</option>
                                        </select>			 </td>
                                </tr>

                                <tr id="tapa" style="display:<?= $tapa_ver ?>">
                                    <td align="right">
                                        <b>Estudios:</b>         	</td>         	
                                    <td align='left'>
                                        <input type="radio" name="estudios" value="Inicial" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
    echo "disabled" ?> <?php if (($estudios == "INICIAL") or ($estudios == "Inicial"))
    echo "checked"; ?>>Inicial
                                        <input type="radio" name="estudios" value="Primario" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
    echo "disabled" ?> <?php if (($estudios == "PRIMARIO") or ($estudios == "Primario"))
    echo "checked"; ?>>Primario
                                        <input type="radio" name="estudios" value="Secundario" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
    echo "disabled" ?> <?php if (($estudios == "SECUNDARIO") or ($estudios == "Secundario"))
    echo "checked"; ?>>Secundario
                                        <input type="radio" name="estudios" value="Terciario" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
    echo "disabled" ?> <?php if (($estudios == "TERCIARIO") or ($estudios == "Terciario"))
                                                echo "checked"; ?>>Terciario
                                        <input type="radio" name="estudios" value="Universitario" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                                echo "disabled" ?> <?php if (($estudios == "UNIVERSITARIO") or ($estudios == "Universitario"))
                                                echo "checked"; ?>>Universitario            </td>            

                                    <td align="right">
                                        <b>Años Mayor Nivel:</b>         	</td>         	
                                    <td align='left'>
                                        <input type="text" size="30" value='<?= $anio_mayor_nivel; ?>' name="anio_mayor_nivel" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                                echo "disabled" ?> onKeyPress="return acceptNum(event); return pulsar(event);"  maxlength="4">            </td>
                                </tr>


                                <tr id="tapa" style="display:<?= $tapa_ver ?>">	           
                                    <td align="center" colspan="4" id="ma">
                                        <b> Datos del Domicilio </b>           </td>
                                </tr>

                                <tr id="tapa" style="display:<?= $tapa_ver ?>">
                                    <td colspan="4" align="center" id="mva" style="display:<?= $mva1 ?>">
                                        <b>Menor convive con adulto:</b><select name=menor_convive_con_adulto id=menor_convive_con_adulto Style="width:200px" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                                echo "disabled" ?>>
                                            <option value='' >Seleccione</option>
                                            <option value=S <? if ($menor_convive_con_adulto == 'S')
                                                echo "selected" ?>>SI</option>
                                            <option value=N <? if ($menor_convive_con_adulto == 'N')
                                                echo "selected" ?>>NO</option>


                                        </select>			</td>
                                </tr>

                                <tr id="tapa" style="display:<?= $tapa_ver ?>">
                                    <td align="right">
                                        <b><font color="Red">*</font>Calle:</b>         	</td>         	
                                    <td align='left'>
                                        <input type="text" size="30" value="<?= $calle ?>" name="calle" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                                echo "disabled" ?> maxlength="40" onkeypress="return pulsar(event);">            </td>
                                    <td align="right">
                                        <b><font color="Red">*</font>N° de Puerta:</b>
                                        <input type="text" size="15" value="<?= $numero_calle ?>" name="numero_calle" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                                echo "disabled" ?> maxlength="5" onkeypress="return pulsar(event);">         	</td>         	
                                    <td align='left'>
                                        <b>Piso:</b>
                                        <input type="text" size="15" value="<?= $piso ?>" name="piso" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                                echo "disabled" ?> maxlength="2" onkeypress="return pulsar(event);">            </td>
                                </tr>  

                                <tr id="tapa" style="display:<?= $tapa_ver ?>">
                                    <td align="right">
                                        <b>Depto:</b>
                                        <input type="text" size="10" value="<?= $dpto ?>" name="dpto" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                                echo "disabled" ?> maxlength="3" onkeypress="return pulsar(event);">         	</td>         	
                                    <td align='left'>
                                        <b>Mz:</b>
                                        <input type="text" size="10" value="<?= $manzana ?>" name="manzana" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                                echo "disabled" ?> maxlength="30" onkeypress="return pulsar(event);">            </td>
                                    <td align="right">
                                        <b>Entre Calle:</b>
                                        <input type="text" size="15" value="<?= $entre_calle_1 ?>" name="entre_calle_1" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                                echo "disabled" ?> maxlength="40" onkeypress="return pulsar(event);">         	</td>         	
                                    <td align='left'>
                                        <b>Entre Calle:</b>
                                        <input type="text" size="15" value="<?= $entre_calle_2 ?>" name="entre_calle_2" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                                echo "disabled" ?> maxlength="40" onkeypress="return pulsar(event);">            </td>         	
                                </tr>  

                                <tr id="tapa" style="display:<?= $tapa_ver ?>">
                                    <td align="right">
                                        <b>Telefono:</b>         	</td>         	
                                    <td align='left'>
                                        <input type="text" size="30" value="<?= $telefono ?>" name="telefono" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                                echo "disabled" ?> maxlength="40" onkeypress="return pulsar(event);">            </td>
                                    <td align="right">
                                        <b>Otro</b>(ej: vecino)         	</td>
                                    <td align="left">
                                        <input type="text" size="30" name="otrotel" value="<?= $otrotel ?>" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                                echo "disabled" ?> maxlength="40" onkeypress="return pulsar(event);">         	</td>
                                </tr>
                                <!-- Ajax -->
                                <tr id="tapa" style="display:<?= $tapa_ver ?>">
                                    <td align="right">
                                        <b><font color="Red">*</font>Departamento:</b> <input type="hidden" name="departamenton" value="<?= $departamenton ?>">    </td>
                                    <td align="left">
                                        <select id="departamento" name="departamento" onchange="showdepartamento();" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                        echo "disabled" ?>><?php echo $departamento; ?></select>    </td>
                                    <td align="right">
                                        <b><font color="Red">*</font>Localidad:</b><input type="hidden" name="localidadn" value="<?= $localidadn ?>">    </td>
                                    <td align="left">
                                        <select id="localidad" name="localidad" onchange="showlocalidad();" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                        echo "disabled" ?>><?php echo $opciones2; ?></select>    </td>
                                </tr>
                                <tr id="tapa" style="display:<?= $tapa_ver ?>">
                                    <td align="right">
                                        <b><font color="Red">*</font>Codigo Postal:</b> <input type="hidden" name="cod_posn" value="<?= $cod_posn ?>">         	</td>         
                                    <td align='left'>	
                                        <select id="cod_pos" name="cod_pos" onchange="showcodpos();" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                    echo "disabled" ?>><?php echo $opciones5; ?></select>               </td>
                                    <td align="right">
                                        <b><font color="Red">*</font>Municipio:</b><input type="hidden" name="municipion" value="<?= $municipion ?>">    </td>
                                    <td align="left">
                                        <select id="municipio" name="municipio" onchange="document.all.b_barrio.disabled=false; showmunicipio();" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                    echo "disabled" ?>><?php echo $opciones3; ?></select>    </td>
                                </tr>

                                <tr id="tapa" style="display:<?= $tapa_ver ?>">
                                    <td align="right">
                                        <b><font color="Red">*</font>Barrio:</b><input type="hidden" name="barrion" value="<?= $barrion ?>">  </td>
                                    <td align="left"><? $d_b_b = 'disabled';
                                if ((!$id_planilla) || (($id_planilla) && $tipo_transaccion == "M")) {
                                    if (($id_planilla) && $tipo_transaccion == "M") {
                                        $d_b_b = '';
                                    } ?>
                                            <button name="b_barrio" <?= $d_b_b ?> onclick="window.open('busca_barrio.php?muni='+document.all.municipio.value+'&id_planilla='+document.all.id_planilla.value,'Buscar','dependent:yes,width=900,height=700,top=1,left=60,scrollbars=yes');" >b</button><? } ?>
                                        <select id="barrio" name="barrio" onchange="showbarrio();" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                    echo "disabled" ?>><?php echo $opciones4; ?></select>    </td>        
                                </tr>
                                <!--  Fin Ajax -->
                                <tr id="tapa" style="display:<?= $tapa_ver ?>">
                                    <td align="right">
                                        <b>Observaciones:</b>         	</td>         	
                                    <td align='left' colspan="3">
                                        <textarea cols='80' rows='4' name='observaciones' <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                    echo "disabled" ?>> <?= $observaciones; ?> </textarea>            </td>
                                </tr>                          

                                <tr ><td colspan="4"><table id="cat_nino" class="bordes" width="100%" style="display:<?= $datos_resp ?>;border:thin groove;">

                                            <tr>         
<? //if ($id_categoria!='6'){ ?>
                                                <td align="center" colspan="4" id="ma" >
                                                    <b> Datos del Responsable </b>         </td>        
                                            </tr>
                                            <tr>
                                                <td align="right" >
                                                    <b><font color="Red">*</font>Datos de Responsable:</b>			</td>
                                                <td align="left" >			 	
                                                    <select name=responsable Style="width:200px" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
    echo "disabled" ?> >
                                                        <option value='-1' <? if ($responsable == '-1')
    echo "selected" ?>>Seleccione</option> 
                                                        <option value=MADRE <? if ($responsable == 'MADRE')
    echo "selected" ?>>Madre</option>
                                                        <option value=PADRE <? if ($responsable == 'PADRE')
    echo "selected" ?>>Padre</option>
                                                        <option value=TUTOR <? if ($responsable == 'TUTOR')
    echo "selected" ?>>Tutor</option>
                                                    </select>			</td> 
                                            </tr>
                                            <tr>
                                                <td align="right">
                                                    <b>Tipo de Documento:</b>			</td>
                                                <td align="left">			 	
                                                    <select name=tipo_doc_madre Style="width:200px" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
    echo "disabled" ?>>
                                                        <option value=DNI <? if ($tipo_doc_madre == 'DNI')
    echo "selected" ?>>Documento Nacional de Identidad</option>
                                                        <option value=LE <? if ($tipo_doc_madre == 'LE')
    echo "selected" ?>>Libreta de Enrolamiento</option>
                                                        <option value=LC <? if ($tipo_doc_madre == 'LC')
    echo "selected" ?>>Libreta Civica</option>
                                                        <option value=PA <? if ($tipo_doc_madre == 'PA')
    echo "selected" ?>>Pasaporte Argentino</option>
                                                        <option value=CM <? if ($tipo_doc_madre == 'CM')
    echo "selected" ?>>Certificado Migratorio</option>
                                                    </select>			</td>          	
                                                <td align="right" width="20%">
                                                    <b><font color="Red">*</font>Documento:</b>         	</td>         	
                                                <td align='left' width="30%">
                                                    <input type="text" size="30" value="<?= $nro_doc_madre ?>" name="nro_doc_madre" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
    echo "disabled" ?> onKeyPress="return acceptNum(event); return pulsar(event);"  maxlength="9">            </td>            
                                            </tr>
                                            <tr>
                                                <td align="right">
                                                    <b><font color="Red">*</font>Apellidos:</b>         	</td>         	
                                                <td align='left'>
                                                    <input type="text" size="30" value="<?= $apellido_madre ?>" name="apellido_madre" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                                echo "disabled" ?> maxlength="50" onkeypress="return pulsar(event);">            </td>
                                                <td align="right">
                                                    <b><font color="Red">*</font>Nombres:</b>         	</td>         	
                                                <td align='left'>
                                                    <input type="text" size="30" value="<?= $nombre_madre ?>" name="nombre_madre" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                                echo "disabled" ?> maxlength="50" onkeypress="return pulsar(event);">            </td>
                                            </tr> 
                                            <tr>	           
                                                <td align="center" colspan="4" id="ma">
                                                    <b> Alfabetización </b>           </td>        
                                            </tr>
                                            <tr>
                                                <td align="right">
                                                    <b>Alfabeta:</b>         	</td>         	
                                                <td align='left'>
                                                    <input type="radio" name="alfabeta_madre" value="S" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                            echo "disabled" ?> onclick="document.all.estudios_madre[1].checked=true" checked> SI
                                                    <input type="radio" name="alfabeta_madre" value="N" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                            echo "disabled" ?> onclick="document.all.estudios_madre[0].checked=false;document.all.estudios_madre[1].checked=false;document.all.estudios_madre[2].checked=false;document.all.anio_mayor_nivel_madre.value='0';"> NO            </td>
                                                <td align="right">
                                                    <b>Estado:</b>            </td>    

                                                <td align="left">			 	
                                                    <select name=estadoest_madre Style="width:200px" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                            echo "disabled" ?>>

                                                        <option value=C <? if ($estadoest_madre == 'C')
                                            echo "selected" ?>>Completo</option>
                                                        <option value=I <? if ($estadoest_madre == 'I')
                                            echo "selected" ?>>Incompleto</option>
                                                    </select>			 </td>
                                            </tr>
                                            <tr>
                                                <td align="right">
                                                    <b>Estudios:</b>         	</td>         	
                                                <td align='left'>
                                                    <input type="radio" name="estudios_madre" value="Inicial" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                            echo "disabled" ?> <?php if (($estudios_madre == "INICIAL") or ($estudios_madre == "Inicial"))
                                            echo "checked"; ?>>Inicial
                                                    <input type="radio" name="estudios_madre" value="Primario" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                            echo "disabled" ?> <?php if (($estudios_madre == "PRIMARIO") or ($estudios_madre == "Primario"))
                                            echo "checked"; ?>>Primario
                                                    <input type="radio" name="estudios_madre" value="Secundario" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                            echo "disabled" ?> <?php if (($estudios_madre == "SECUNDARIO") or ($estudios_madre == "Secundario"))
                                            echo "checked"; ?>>Secundario
                                                    <input type="radio" name="estudios_madre" value="Terciario" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                            echo "disabled" ?> <?php if (($estudios_madre == "TERCIARIO") or ($estudios_madre == "Terciario"))
                                            echo "checked"; ?>>Terciario
                                                    <input type="radio" name="estudios_madre" value="Universitario" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                            echo "disabled" ?> <?php if (($estudios_madre == "UNIVERSITARIO") or ($estudios_madre == "Universitario"))
                                            echo "checked"; ?>>Universitario            </td>            
                                                <td align="right">
                                                    <b>Años Mayor Nivel:</b>         	</td>         	
                                                <td align='left'>
                                                    <input type="text" size="30" value='<?= $anio_mayor_nivel_madre ?>' name="anio_mayor_nivel_madre" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                                    echo "disabled" ?> onKeyPress="return acceptNum(event); return pulsar(event);" maxlength="4">            </td>
                                            </tr>


                                            <? //}?>
                                        </table>

                                    </td></tr>

                                <tr>
                                            <? //if ($edad >= '9') {?>
                                    <td align="center" colspan="4" id="memb" style="display:<?= $memb ?>;">
                                        <b>Menor embarazada:</b><select name=menor_embarazada id=menor_embarazada Style="width:200px" onchange="cambiar_patalla();" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                                echo "disabled" ?>>
                                            <option value=N >Seleccione</option>
                                            <option value=S <? if ($menor_embarazada == 'S')
                                                echo "selected" ?>>SI</option>
                                            <option value=N <? if ($menor_embarazada == 'N')
                                                echo "selected" ?>>NO</option>


                                        </select>			</td>
                                </tr>
<? //} ?>
                                <tr><td colspan="4"><table id="cat_emb" class="bordes" width="100%" style="display:<?= $embarazada ?>;border:thin groove">

<? if (strtoupper($sexo) != 'M') { ?>
                                                <tr>	           
                                                    <td align="center" colspan="4" id="ma">
                                                        <b> Datos de Embarazo </b>           </td>        
                                                </tr>
                                                <tr>
                                                    <td align="right">
                                                        <b>F.U.M.:</b>         	</td>         	
                                                    <td align='left'>
    <? $fecha_comprobante = date("d/m/Y"); ?>
                                                        <input type=text name=fum size=15 onblur="esFechaValida(this);recalcF1_fum();" value='<?= $fum; ?>' <?php if (($id_planilla) and ($tipo_transaccion != "M"))
        echo "disabled" ?> maxlength="10" onKeyUp="mascara(this,'/',patron,true);return pulsar(event);" onkeypress="return pulsar(event);">
    <?= link_calendario("fum"); ?>            </td>		    
                                                    <td align="right">
                                                        <b><font color="Red">*</font>Fecha de Diag. de Embarazo:</b>			</td>
                                                    <td align="left">	       
                                                        <input type=text name=fecha_diagnostico_embarazo onblur="esFechaValida(this);" value='<?= $fecha_diagnostico_embarazo; ?>' <?php if (($id_planilla) and ($tipo_transaccion != "M"))
        echo "disabled" ?> size=15  maxlength="10" onKeyUp="mascara(this,'/',patron,true);return pulsar(event);" onkeypress="return pulsar(event);">
                    <?= link_calendario("fecha_diagnostico_embarazo"); ?>		    </td>
                                                </tr>   
                                                <tr>
                                                    <td align="right">
                                                        <b>Semana de Embarazo:</b>         	</td>         	
                                                    <td align='left'>

                                                        <input type="text" name="semanas_embarazo"  value="<?= $semanas_embarazo; ?>" onblur="recalcF1();"  size="30"  <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                    echo "disabled" ?> onKeyPress="return acceptNum(event); return pulsar(event);" maxlength="4">            </td>		    
                                                    <td align="right">
                                                        <b><font color="Red">*</font>Fecha Probable de Parto:</b>			</td>

                                                    <td align="left">
                                                        <input type=text name=fecha_probable_parto onblur="esFechaValida(this);" value='<?= $fecha_probable_parto; ?>' size=15 <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                    echo "disabled" ?> maxlength="10" onKeyUp="mascara(this,'/',patron,true); return pulsar(event);"  onkeypress="return pulsar(event);">
    <?= link_calendario("fecha_probable_parto"); ?>		    </td>
                                                </tr>
                                                <tr>
                                                    <td align="center" colspan="4" id="ma">
                                                        <b> Riesgo Cardiovascular </b>           </td>       
                                                </tr>
                                                <tr>
                                                    <td align="right">
                                                        <b>Score de riesgo:</b>         	</td>         	
                                                    <td align='left'>
                                                        <input type="text" size="10" value='<?= $score_riesgo ?>' name="score_riesgo" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                                echo "disabled" ?> onKeyPress="return acceptNum(event); return pulsar(event);">            </td>
                                                </tr>
<? } ?>
                                        </table>

                                <tr id="tapa" style="display:<?= $tapa_ver ?>">	           
                                    <td align="center" colspan="4" id="ma">
                                        <b> Discapacidad </b>           </td>
                                </tr>
                                <tr id="tapa" style="display:<?= $tapa_ver ?>">
                                    <td align="center" colspan="4">
                                        <input type=checkbox name=discv value='Visual' <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                    echo "disabled" ?> <?php if ($discv == "VISUAL")
                    echo "checked"; ?> > Visual
                                        <input type=checkbox name=disca value='Auditiva' <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                    echo "disabled" ?> <?php if ($disca == "AUDITIVA")
                    echo "checked"; ?> > Auditiva
                                        <input type=checkbox name=discmo value='Motriz' <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                    echo "disabled" ?> <?php if ($discmo == "MOTRIZ")
                    echo "checked"; ?> > Motriz
                                        <input type=checkbox name=disme value='Mental' <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                    echo "disabled" ?> <?php if ($discme == "MENTAL")
                    echo "checked"; ?> > Mental
                                        <input type=checkbox name=otradisc value='Otra Discapacidad' <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                    echo "disabled" ?> <?php if ($otradisc == "OTRA DISCAPACIDAD")
                    echo "checked"; ?> > Otra discapacidad         </td>
                                </tr>
                                <tr id="tapa" style="display:<?= $tapa_ver ?>">	           
                                    <td align="center" colspan="4" id="ma">
                                        <b> Fecha de Inscripcion </b>           </td>
                                </tr>

                                <tr id="tapa" style="display:<?= $tapa_ver ?>">
                                    <td align="center" width="20%" colspan="4">
                                        <font color="Red">*</font><b>Fecha de Inscripcion:</b><input type=text onblur="esFechaValida(this);" name=fecha_inscripcion id=fecha_inscripcion value='<?= $fecha_inscripcion; ?>' size=15 <?php if (($id_planilla) and ($tipo_transaccion != "M"))
                    echo "disabled" ?> maxlength="10" onKeyUp="mascara(this,'/',patron,true); return pulsar(event);" onkeypress="return pulsar(event);">
<?= link_calendario("fecha_inscripcion"); ?>			</td>
                                </tr>

                                <tr id="tapa" style="display:<?= $tapa_ver ?>">	           
                                    <td align="center" colspan="4" id="ma">
                                        <b> Efector Habitual </b>           </td>
                                </tr>

                                <tr id="tapa" style="display:<?= $tapa_ver ?>">
                                    <td align="center" width="20%" colspan="4">
                                        <b><font color="Red">*</font>Efector Habitual:</b><select name=cuie Style="width:300px" 
                                                                                                  onKeypress="buscar_combo(this);"
                                                                                                  onblur="borrar_buffer();"
                                                                                                  onchange="borrar_buffer();" 
<?php if (($id_planilla) and ($tipo_transaccion != "M"))
    echo "disabled" ?> >
                                            <option value=-1>Seleccione</option>
<?
$sql = "select * from facturacion.smiefectores order by nombreefector";
$res_efectores = sql($sql) or fin_pagina();
while (!$res_efectores->EOF) {
    $cuiel = $res_efectores->fields['cuie'];
    $nombre_efector = $res_efectores->fields['nombreefector'];
    ?>
                                                <option value='<?= $cuiel ?>' <? if ($cuie == $cuiel)
        echo "selected" ?> ><?= ($nombre_efector) ?></option>
    <?
    $res_efectores->movenext();
}
?>
                                        </select><? if ((!$id_planilla) || (($id_planilla) && $tipo_transaccion == "M")) { ?>
                                            <button onclick="window.open('busca_efector.php?qkmpo=cuie','Buscar','dependent:yes,width=900,height=700,top=1,left=60,scrollbars=yes');">b</button><? } ?>			</td>
                                </tr>

                                <tr id="tapa" style="display:<?= $tapa_ver ?>">	           
                                    <td align="center" colspan="4" id="ma">
                                        <b> Observaciones Generales </b>           </td>        
                                </tr>

                                <tr align="center" id="tapa" style="display:<?= $tapa_ver ?>">

                                    <td align='center' colspan="4">
                                        <textarea cols='80' rows='4' name='obsgenerales' <?php if (($id_planilla) and ($tipo_transaccion != "M"))
    echo "disabled" ?> > <?= $obsgenerales; ?>  </textarea>            </td>
                                </tr>   

<? if ($agentes == 's') { ?>
                                    <tr id="ma" id="tapa" style="display:<?= $tapa_ver ?>">
                                        <td align="center" colspan="4">
    <? if ((!$id_planilla) || (($id_planilla) && $tipo_transaccion == "M")) { ?>
                                                <button onclick="window.open('../remediar/busca_promotor.php','Buscar','dependent:yes,width=900,height=700,top=1,left=60,scrollbars=yes');">Buscar</button>
    <? } ?> <b>Datos del Agente Inscriptor</b>           </td>
                                    </tr>
                                    <tr id="tapa" style="display:<?= $tapa_ver ?>">
                                        <td align="right">
                                            <b>Apellido:</b>         	</td>
                                        <td align='left'>
                                            <input type="text" size="30" value="<?= $apellidoagente ?>" name="apellidoagente" maxlength="50"  onkeypress="return pulsar(event);" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
        echo "readOnly" ?>>            </td>
                                        <td align="right">
                                            <b>Nombre:</b>         	</td>
                                        <td align='left'>
                                            <input type="text" size="30" value="<?= $nombreagente ?>" name="nombreagente" maxlength="50"  onkeypress="return pulsar(event);" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
        echo "readOnly" ?>>            </td>
                                    </tr>
                                    <tr id="tapa" style="display:<?= $tapa_ver ?>">
                                        <td align="right">
                                            <b>Nro. Doc.:</b>         	</td>
                                        <td align='left'>
                                            <input type="text" size="30" value="<?= $num_doc_agente ?>" name="num_doc_agente" maxlength="12" onkeypress="return pulsar(event);" <?php if (($id_planilla) and ($tipo_transaccion != "M"))
        echo "readOnly" ?>>            </td>
                                        <td align="right" >
                                            <b>Centro Inscriptor Lugar:</b>			</td>
                                        <td align="left">
                                            <select name=cuie_agente Style="width:300px"
                                                    onKeypress="buscar_combo(this);"
                                                    onblur="borrar_buffer();"
                                                    onchange="borrar_buffer();"
    <?php if (($id_planilla) and ($tipo_transaccion != "M"))
        echo "disabled" ?>>
                                                <option value=-1>Seleccione</option>
    <?
    $sql = "select * from facturacion.smiefectores order by nombreefector";
    $res_efectores = sql($sql) or fin_pagina();
    while (!$res_efectores->EOF) {
        $cuiec_agente = $res_efectores->fields['cuie'];
        $nombre_efector_agente = $res_efectores->fields['nombreefector'];
        ?>
                                                    <option value='<?= $cuiec_agente ?>' <? if ($cuie_agente == $cuiec_agente)
            echo "selected" ?> ><?= $nombre_efector_agente ?></option>
        <?
        $res_efectores->movenext();
    }
    ?>
                                            </select><? if ((!$id_planilla) || (($id_planilla) && $tipo_transaccion == "M")) { ?><button onclick="window.open('../inscripcion/busca_efector.php?qkmpo=cuie_agente','Buscar','dependent:yes,width=900,height=700,top=1,left=60,scrollbars=yes');">b</button><? } ?>			</td>
                                    </tr>
<? } ?>      
                            </table>
                        </td>      
                    </tr> 



<? if ((!($id_planilla)) and ($clave_beneficiario == '')) { ?>

                        <tr id="mo"  id="tapa" style="display:<?= $tapa_ver ?>">
                            <td align=center colspan="2">
                                <b>Guardar Planilla</b>
                            </td>
                        </tr>  
                        <tr align="center" id="tapa" style="display:<?= $tapa_ver ?>">
                            <td>
                                <b><font size="0" color="Red">Nota: Verifique todos los datos antes de guardar</font> </b>
                            </td>
                        </tr>
                        <tr align="center" id="tapa" style="display:<?= $tapa_ver ?>">
                            <td>
                                <input type='submit' name='guardar' value='Guardar Planilla' onclick="return control_nuevos();"     title="Guardar datos de la Planilla"  />
                            </td>
                        </tr>

<? } ?>
<?php if ($edad == "") { ?><script>edad(document.all.fecha_nac.value);</script> <? } ?>
                </table>           
                <br>
<? if ($clave_beneficiario != '') { ?>
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
                                <input type="submit" name="guardar_editar" value="Guardar" title="Guardar"  style="width:130px" <?php if ($tipo_transaccion != "M")
        echo "disabled" ?> onclick="return control_nuevos();">&nbsp;&nbsp;

    <?
    //echo $estado_envio.'***'.strtoupper($usuario_carga).'***'.substr(strtoupper($_ses_user['name']),0,9).'***'.$tipo_transaccion;
    if (( $estado_envio == 'p' && strtoupper($usuario_carga) != substr(strtoupper($_ses_user['id']), 0, 9)) && ($tipo_transaccion != "B"))
        $permiso = "";
    else
        $permiso = "disabled";
    if ($estado_nuevo) {
        ?>
                                    <input type="submit" name="guardar" value="Pasar a No Enviados" title="Pasar a No Enviados"  style="width:130px" <?= $permiso ?>>&nbsp;&nbsp;
    <? } ?>
                              <!--  <input type="button" name="cancelar_editar" value="Cancelar" title="Cancelar Edicion" style="width=130px" onclick="document.location.reload()" disabled>-->		      		  <input type="button" name="cancelar_editar" value="Cancelar" title="Cancelar Edicion" style="width:130px" <?php if ($tipo_transaccion != "M")
        echo "disabled" ?> onclick="history.back(-1);">	
    <? if (permisos_check("inicio", "permiso_borrar"))
        $permiso = "";
    else
        $permiso = "disabled"; ?>
                                <input type="submit" name="borrar" value="Borrar" style="width:130px" <?= $permiso ?> <?php if ($tipo_transaccion != "B")
        echo "disabled" ?>>
                            </td>
                        </tr> 
                    </table>	
                    <br>
<? } ?>
        <tr><td><table width=100% align="center" class="bordes">
                    <tr align="center">
                        <td>
                            <input type=button name="volver" value="Volver" onclick="document.location='ins_listado.php'"title="Volver al Listado" style="width:150px">     
                        </td>
                    </tr>

                </table></td></tr>


    </table>
</form>
<!--<script>
    //(($id_planilla) and ($tipo_transaccion != "M"))
if  (!(document.all.id_planilla.value!='' && document.all.tipo_transaccion.value!='M')){
    var campo_focus=document.all.campo_actual.value;
    if(campo_focus==''){
        document.getElementById('campo_actual').value='num_doc';
        campo_focus='num_doc';
    }
    document.getElementById(campo_focus).focus();
}
</script>-->
<?=
fin_pagina(); // aca termino ?>