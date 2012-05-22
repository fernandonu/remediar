<?
require_once ("../../config.php");
include_once("../facturacion/funciones.php");

extract($_POST, EXTR_SKIP);
if ($parametros)
    extract($parametros, EXTR_OVERWRITE);
cargar_calendario();

/* * ******
 * metodo de guardar
 * ******* */

if ($_POST['guardar'] == "Guardar Planilla") {
    $fecha_carga = date("Y-m-d H:m:s");
    $usuario = $_ses_user['id'];
    $db->StartTrans();

    $fecha_nac = Fecha_db($fecha_nac);
    $fecha_clasificacion = Fecha_db($fecha_clasificacion);
      
    if ($dmt == '') {
        $dmt = 0;
    }
    
    if ($acv == 'on') {
        $acv = 1;
    } else {
        $acv = 0;
    }
    if ($vas_per == 'on') {
        $vas_per = 1;
    } else {
        $vas_per = 0;
    }
    if ($car_isq == 'on') {
        $car_isq = 1;
    } else {
        $car_isq = 0;
    }
    if ($col310 == 'on') {
        $col310 = 1;
    } else {
        $col310 = 0;
    }
    if ($col_ldl == 'on') {
        $col_ldl = 1;
    } else {
        $col_ldl = 0;
    }
    if ($ct_hdl == 'on') {
        $ct_hdl = 1;
    } else {
        $ct_hdl = 0;
    }
    if ($pres_art == 'on') {
        $pres_art = 1;
    } else {
        $pres_art = 0;
    }
    if ($dmt2 == 'on') {
        $dmt2 = 1;
    } else {
        $dmt2 = 0;
    }
    if ($insu_renal == 'on') {
        $insu_renal = 1;
    } else {
        $insu_renal = 0;
    }    
    if ($dmt_menor == 'on') {
        $dmt_menor = 1;
    } else {
        $dmt_menor = 0;
    }
    if ($hta_menor == 'on') {
        $hta_menor = 1;
    } else {
        $hta_menor = 0;
    }
    if ($hta == 'on') {
        $hta = 1;
    } else {
        $hta = 0;
    }
    if ($tabaquismo == 'on') {
        $tabaquismo = 1;
    } else {
        $tabaquismo = 0;
    }
    if ($meno_prema == 'on') {
        $meno_prema = 1;
    } else {
        $meno_prema = 0;
    }
    if ($antihiper == 'on') {
        $antihiper = 1;
    } else {
        $antihiper = 0;
    }
    if ($obesi == 'on') {
        $obesi = 1;
    } else {
        $obesi = 0;
    }
    if ($acv_prema == 'on') {
        $acv_prema = 1;
    } else {
        $acv_prema = 0;
    }
    if ($trigli == 'on') {
        $trigli = 1;
    } else {
        $trigli = 0;
    }
    if ($hdl_col == 'on') {
        $hdl_col = 1;
    } else {
        $hdl_col = 0;
    }
    if ($hiperglu == 'on') {
        $hiperglu = 1;
    } else {
        $hiperglu = 0;
    }
    if ($microalbu == 'on') {
        $microalbu = 1;
    } else {
        $microalbu = 0;
    }            

    $ta_sist = str_replace(',', '.', $ta_sist);
    if ($ta_sist == '') {
        $ta_sist = 0;
    }
    $ta_diast = str_replace(',', '.', $ta_diast);
    if ($ta_diast == '') {
        $ta_diast = 0;
    }
    $col_tot = str_replace(',', '.', $col_tot);
    if ($col_tot == '') {
        $col_tot = 0;
    }
    
    if ($id_medico == '') {
        $accion = 'Falta el medico.';
    } elseif ($id_medico == 'new') {
        $queryx = "SELECT  id_medico
					FROM planillas.medicos 
				  where apellido_medico=upper('$apellido_medico') and nombre_medico=upper('$nombre_medico') and dni_medico='$dni_medico'";
        $res_comp_num_comp = sql($queryx, "Error al traer el Comprobantes") or fin_pagina();
        if ($res_comp_num_comp->recordcount() == 0) {

            $query1 = "insert into planillas.medicos  (id_medico,apellido_medico,nombre_medico,dni_medico) 
					values (nextval('planillas.medicos_id_medico_seq'),upper('$apellido_medico'),upper('$nombre_medico'),'$dni_medico') RETURNING id_medico";
            $res_extras1 = sql($query1, "Error al insertar la Planilla") or fin_pagina();
            $id_medico = $res_extras1->fields['id_medico'];
        }
        if ($res_comp_num_comp->recordcount() > 0) {
            $accion = 'Imposible guardar el medico.';
        }
    }
    if (!$id_planilla && $id_medico > 0) {// echo $id_medico;
        //,apellido_medico,nombre_medico,matricula_medico
        $queryx = "SELECT  clave_beneficiario,num_doc,apellido,nombre
				FROM trazadoras.clasificacion_remediar2  
			  where nro_clasificacion='$nro_clasificacion'";
        $res_comp_num_comp = sql($queryx, "Error al traer el Comprobantes") or fin_pagina();
        if ($res_comp_num_comp->recordcount() == 0) {
            
            //TODO cambiar las columnas por nombres mas descriptivos
            /*'$acv','$vas_per','$car_isq','$col310','$col_ldl','$ct_hdl','$pres_art','$dmt2',
            '$insu_renal','$dmt_menor','$hta_menor','$dmt','$hta','$tabaquismo','$ta_sist','$ta_diast','$col_tot','$menopausia','$antihiper','$obesi','$acv_prema',
            '$trigli','$hdl_col','$hiperglu','$microalbu','$riesgo_global'*/
            $query = "insert into trazadoras.clasificacion_remediar2 (id_clasificacion,nro_clasificacion,cuie,clave_beneficiario,tipo_doc,num_doc,apellido,nombre,
            fecha_nac,fecha_carga,usuario,fecha_control,id_medico,dmt,acv,vas_per,car_isq,col310,col_ldl,ct_hdl,pres_art
            ,dmt2,insu_renal,dmt_menor,hta_menor,hta,tabaquismo,ta_sist,ta_diast,col_tot,menopausia,antihiper,
            obesi,acv_prema,trigli,hdl_col,hiperglu,microalbu,rcvg)
            values (nextval('trazadoras.clasificacion_remediar_id_clasificacion_seq'),'$nro_clasificacion','$cuie','$clave','$tipo_doc','$num_doc','$apellido',
            '$nombre','$fecha_nac','$fecha_carga','$usuario','$fecha_clasificacion','$id_medico','$dmt','$acv','$vas_per','$car_isq','$col310','$col_ldl','$ct_hdl','$pres_art','$dmt2',
            '$insu_renal','$dmt_menor','$hta_menor','$hta','$tabaquismo','$ta_sist','$ta_diast','$col_tot','$menopausia','$antihiper','$obesi','$acv_prema',
            '$trigli','$hdl_col','$hiperglu','$microalbu','$riesgo_global') RETURNING id_clasificacion";

            $res_extras = sql($query, "Error al insertar la Planilla") or fin_pagina();
            $id_planilla = $res_extras->fields['id_clasificacion'];
            $accion = '';
            $accion2 = "Se guardo la Planilla Clasificacion Nro. " . $nro_clasificacion;
            
            /*             * *************************** ******************************************************** */
            /*             * *************************** GENIAL IDE A DE BETTINA********************************************************* */
            /*             * *************************** ******************************************************** */
            //$fecha_carga=date("Y-m-d H:i:s");

            $sql = "select id_categoria_prestacion 
							from nomenclador.grupo_prestacion 
							where codigo='$tema' and id_nomenclador_detalle='$id_nomenclador_detalle'";
            $result_id_tema = sql($sql, 'error en el tema');
            $id_tema = $result_id_tema->fields['id_categoria_prestacion'];
            if (($pagina_viene == 'comprobante_admin_total.php') || (valida_prestacion3($id_comprobante, $id_tema))) {

                $db->StartTrans();
                (($profesional == '-1') || ($profesional == '')) ? $profesional = "P99" : $profesional;

                $fecha_nacimiento_cod = str_replace('-', '', $fecha_nacimiento);
                $fecha_comprobante_cod = substr(str_replace('-', '', $fecha_comprobante), 0, 8);

                $codigo = $cuie . $fecha_comprobante_cod . $clave_beneficiario . $fecha_nacimiento_cod . $sexo_codigo . $edad . $prestacion . $tema . $patologia . $profesional;

                $res_dia_mes_anio = dia_mes_anio($fecha_nacimiento, $fecha_comprobante);
                $anios_desde_nac = $res_dia_mes_anio['anios'];
                $meses_desde_nac = $res_dia_mes_anio['meses'];
                $dias_desde_nac = $res_dia_mes_anio['dias'];

                $query_precio = "select precio from nomenclador.grupo_prestacion where codigo='$tema'";
                $query_precio = sql($query_precio) or fin_pagina();
                $precio = $query_precio->fields['precio'];


                //cargo la nueva prestacion - con nomenclador 2011
                $consulta = "insert into nomenclador.prestaciones_n_op
										(id_prestaciones_n_op,id_comprobante,fecha_nacimiento,fecha_comprobante,sexo_codigo, edad, prestacion, tema,patologia, profesional,codigo,precio,anio,mes,dias)
									values 
										(nextval('nomenclador.prestaciones_n_op_id_prestaciones_n_op_seq'),'$id_comprobante','$fecha_nacimiento_cod','$fecha_comprobante_cod','$sexo_codigo','$edad','$prestacion','$tema','$patologia','$profesional','$codigo','$precio','$anios_desde_nac','$meses_desde_nac','$dias_desde_nac') RETURNING id_prestaciones_n_op";
                $id_prestacion = sql($consulta) or fin_pagina();

                $id_prestacion = $id_prestacion->fields['id_prestaciones_n_op'];
                $db->CompleteTrans();
                $accion2.=" y la Prestacion: " . $id_prestacion;
            }
            /*             * *************************** ******************************************************** */
            /*             * *************************** FIN GENIAL IDE A DE BETTINA********************************************************* */
            /*             * *************************** ******************************************************** */
        }
        if ($res_comp_num_comp->recordcount() > 0) {
            $accion2 = '';
            $accion = 'El Nro. de Clasificacion ' . $nro_clasificacion . ' ya existe para ' . $res_comp_num_comp->fields['apellido'] . ' ' . $res_comp_num_comp->fields['nombre'] . ', ' . $res_comp_num_comp->fields['num_doc'] . '.';
        }
    } elseif ($id_planilla && $id_medico > 0) {
        $query = "Update trazadoras.clasificacion_remediar2 set nro_clasificacion='$nro_clasificacion',id_medico='$id_medico',dmt='$dmt',acv='$acv',vas_per='$vas_per',car_isq='$car_isq',col310='$col310',col_ldl='$col_ldl',ct_hdl='$ct_hdl',pres_art='$pres_art'
        ,dmt2='$dmt2',insu_renal='$insu_renal',dmt_menor='$dmt_menor',hta_menor='$hta_menor',hta='$hta',tabaquismo='$tabaquismo',ta_sist='$ta_sist',ta_diast='$ta_diast',col_tot='$col_tot',menopausia='$menopausia',antihiper='$antihiper'
        ,obesi='$obesi',acv_prema='$acv_prema',trigli='$trigli',hdl_col='$hdl_col',hiperglu='$hiperglu',microalbu='$microalbu',fecha_control='$fecha_clasificacion',rcvg='$riesgo_global' where id_clasificacion=$id_planilla";    
        
        $res_extras = sql($query, "Error al insertar la Planilla") or fin_pagina();
        $accion = '';
        $accion2 = "Se actualizo la Planilla";
    }

    $db->CompleteTrans();

    /* if ($pagina=="prestacion_admin.php") echo "<script>window.close()</script>";   */
}//de if ($_POST['guardar']=="Guardar nuevo Muleto")

/* * ******
 * metodo de borrar
 * ******* */

if ($_POST['borrar'] == "Borrar") {
    $query = "delete from trazadoras.clasificacion_remediar2
			where id_clasificacion=$id_planilla";
    sql($query, "Error al insertar la Planilla") or fin_pagina();
    $accion = "Se elimino la planilla $id_planilla de Ni�os";
}

/* * ******
 * metodo de buscar
 * ******* */

if ($_POST['buscar_clasificacion'] == "b") {
    if ($_POST['nro_clasificacion'] != '') {
        $query = "SELECT id_clasificacion,nro_clasificacion
				FROM trazadoras.clasificacion_remediar2  
			  where nro_clasificacion='$nro_clasificacion' and clave_beneficiario='$clave'";
        $res_factura = sql($query, "Error al traer el Comprobantes") or fin_pagina();
        if ($res_factura->recordcount() > 0) {
            $id_planilla = $res_factura->fields['id_clasificacion'];
            $nro_clasificacion = $res_factura->fields['nro_clasificacion'];
            $accion = 'Clasificacion con Nro.' . $nro_clasificacion . ' encontrada.';
        }
        if ($res_factura->recordcount() == 0) {
            $accion2 = 'No se encuentra Clasificacion con Nro.' . $nro_clasificacion . ' para este beneficiario';
            //$id_planilla=0;                               
            $fecha_carga = '';
            $acv = '';
            $vas_per = '';
            $car_isq = '';
            $col310 = '';
            $col_ldl = '';
            $ct_hdl = '';
            $pres_art = '';
            $dmt2 = '';
            $insu_renal = '';
            $dmt_menor = '';
            $hta_menor = '';

            $hta = '';
            $tabaquismo = '';
            $ta_sist = '';
            $ta_diast = '';
            $col_tot = '';
            $meno_prema = '';
            $antihiper = '';
            $obesi = '';
            $acv_prema = '';
            $trigli = '';
            $hdl_col = '';
            $hiperglu = '';
            $microalbu = '';
            $riesgo_global = '';

            $apellido_medico = '';
            $nombre_medico = '';
            $matricula_medico = '';
            //$id_smiafiliados=0;
        }
    } else {
        echo "<SCRIPT Language='Javascript'> alert('Debe Cargar el Nro. de Clasificacion'); </SCRIPT>";
    }
}/* * ******
 * fin metodo de buscar
 * ******* */


/* * ********
 * Carga el formulario al entrar
 */


if ($pagina == 'prestacion_admin.php' || $pagina == 'listado_beneficiarios_leche.php') {
    if ($pagina == 'listado_beneficiarios_leche.php') {
        $desabil = 'disabled';
    } else {
        $desabiledi = 'disabled';
    }

    //echo $pagina.'*'.$id_smiafiliados;
    $sql = "select clave_beneficiario,tipo_documento,clase_documento_benef,numero_doc,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro
				,fecha_nacimiento_benef,sexo,telefono,provincia_nac,departamento,municipio,localidad,calle,barrio,numero_calle,manzana,piso
				,dpto,formulario.centro_inscriptor,fechaempadronamiento,os,cual_os 
				from uad.beneficiarios	  
			inner join uad.remediar_x_beneficiario on beneficiarios.clave_beneficiario=remediar_x_beneficiario.clavebeneficiario
			inner join remediar.formulario on remediar_x_beneficiario.nroformulario=formulario.nroformulario
			 where id_beneficiarios=$id_smiafiliados 
			 group by clave_beneficiario,tipo_documento,clase_documento_benef,numero_doc,apellido_benef,apellido_benef_otro,nombre_benef,nombre_benef_otro
				,fecha_nacimiento_benef,sexo,telefono,provincia_nac,departamento,municipio,localidad,calle,barrio,numero_calle,manzana,piso
				,dpto,formulario.centro_inscriptor,fechaempadronamiento,os,cual_os";
    $res_extra = sql($sql, "Error al traer el beneficiario") or fin_pagina();
    if ($res_extra->RecordCount() > 0) {
        $clave = $res_extra->fields['clave_beneficiario'];
        $tipo_doc = $res_extra->fields['tipo_documento'];
        $clase_doc = $res_extra->fields['clase_documento_benef'];
        $num_doc = number_format($res_extra->fields['numero_doc'], 0, '.', '');
        $apellido = $res_extra->fields['apellido_benef'] . ' ' . $res_extra->fields['apellido_benef_otro'];
        $nombre = $res_extra->fields['nombre_benef'] . ' ' . $res_extra->fields['nombre_benef_otro'];
        $fecha_nac = fecha($res_extra->fields['fecha_nacimiento_benef']);
        $sexo = $res_extra->fields['sexo'];
        $telefono = $res_extra->fields['telefono'];
        $provincia_nac = $res_extra->fields['provincia_nac'];
        $departamento = $res_extra->fields['departamento'];
        $municipio = $res_extra->fields['municipio'];
        $localidad = $res_extra->fields['localidad'];
        $calle = $res_extra->fields['calle'];
        $barrio = $res_extra->fields['barrio'];
        $numero_calle = $res_extra->fields['numero_calle'];
        $manzana = $res_extra->fields['manzana'];
        $piso = $res_extra->fields['piso'];
        $dpto = $res_extra->fields['dpto'];

        $cuie = $res_extra->fields['centro_inscriptor'];
        $fechaempadronamiento = fecha($res_extra->fields['fechaempadronamiento']);
        $os = $res_extra->fields['os'];
        $cual_os = $res_extra->fields['cual_os'];

        $query = "SELECT  id_clasificacion,nro_clasificacion
				FROM trazadoras.clasificacion_remediar2  
			  where clave_beneficiario='$clave'";
        $res_factura = sql($query, "Error al traer el Comprobantes") or fin_pagina();
        if ($res_factura->recordcount() > 0) {
            $id_planilla = $res_factura->fields['id_clasificacion'];
            $nro_clasificacion = $res_factura->fields['nro_clasificacion']; //echo rtrim(substr($accion,3,9));
            if (rtrim(substr($accion2, 3, 6)) != "guardo" && rtrim(substr($accion2, 3, 9)) != "actualizo" && $accion != 'Imposible guardar el medico.' && $accion != 'Falta el medico.') {
                $accion = 'Beneficiario ya posee Clasificacion con Nro. ' . $nro_clasificacion;
            }
        }
    } else {
        echo '<script>
			alert("Esta persona no posee Remediar");
			window.close();
			</script>';
    }
}

if ($_POST['editar'] == "Editar Planilla") {
    $desabil = '';
    $desabiledi = 'disabled';
}

if ($id_planilla) { //echo 'aaa';
    $query = "SELECT  *
	FROM
  trazadoras.clasificacion_remediar2  a
  left join planillas.medicos b on a.id_medico=b.id_medico
  where id_clasificacion=$id_planilla";
// VER AQU� TAMBI�N
    $res_factura = sql($query, "Error al traer el Comprobantes") or fin_pagina();

    $cuie = $res_factura->fields['cuie'];
    $clave = $res_factura->fields['clave_beneficiario'];
    $tipo_doc = $res_factura->fields['tipo_doc'];
    $num_doc = number_format($res_factura->fields['num_doc'], 0, '.', '');
    $apellido = $res_factura->fields['apellido'];
    $nombre = $res_factura->fields['nombre'];
    $fecha_nac = fecha($res_factura->fields['fecha_nac']);
    $fecha_carga = fecha($res_factura->fields['fecha_carga']);

    $acv = $res_factura->fields['acv'];
    $vas_per = $res_factura->fields['vas_per'];
    $car_isq = $res_factura->fields['car_isq'];
    $col310 = $res_factura->fields['col310'];
    $col_ldl = $res_factura->fields['col_ldl'];
    $ct_hdl = $res_factura->fields['ct_hdl'];
    $pres_art = $res_factura->fields['pres_art'];
    $dmt2 = $res_factura->fields['dmt2'];
    $insu_renal = $res_factura->fields['insu_renal'];
    $dmt_menor = $res_factura->fields['dmt_menor'];
    $hta_menor = $res_factura->fields['hta_menor'];
    $dmt=$res_factura->fields['dmt'];

    $hta = $res_factura->fields['hta'];
    $tabaquismo = $res_factura->fields['tabaquismo'];
    $ta_sist = $res_factura->fields['ta_sist'];
    $ta_diast = $res_factura->fields['ta_diast'];
    $col_tot = $res_factura->fields['col_tot'];
    $menopausia = $res_factura->fields['menopausia'];
    $antihiper = $res_factura->fields['antihiper'];
    $obesi = $res_factura->fields['obesi'];
    $acv_prema = $res_factura->fields['acv_prema'];
    $trigli = $res_factura->fields['trigli'];
    $hdl_col = $res_factura->fields['hdl_col'];
    $hiperglu = $res_factura->fields['hiperglu'];
    $microalbu = $res_factura->fields['microalbu'];
    $riesgo_global = $res_factura->fields['rcvg'];
    $fecha_clasificacion=$res_factura->fields['fecha_control'];

    $apellido_medico = $res_factura->fields['apellido_medico'];
    $nombre_medico = $res_factura->fields['nombre_medico'];
    $dni_medico = $res_factura->fields['dni_medico'];
    $id_medico = $res_factura->fields['id_medico'];
}
echo $html_header;
?>
<script>
    //controlan que ingresen todos los datos necesarios par el muleto
    function control_nuevos()
    {

        //ta_sist
        if((document.all.ta_sist.value!='')&&(document.all.ta_sist.value<80 || document.all.ta_sist.value>220)){ 
            alert('Debe completar la presion arterial con datos validos');
            return false;
        }

        //ta_diast
        if((document.all.ta_diast.value!='')&&(document.all.ta_diast.value<50 || document.all.ta_diast.value>130)){ 
            alert('Debe completar la presion con datos validos');
            return false;
        }

        //col_tot
        if((document.all.col_tot.value!='')&&(document.all.col_tot.value<130)){ 
            alert('Debe completar el colesterol con datos validos');
            return false;
        }

        /* RCVG*/
        if(document.all.riesgo_global[0].checked==false && document.all.riesgo_global[1].checked==false && document.all.riesgo_global[2].checked==false && document.all.riesgo_global[3].checked==false){
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
                    alert($mensaje+'. De lo contrario comun&acuote;quese a Plan Nacer');
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
                mayor_menor(document.all.peso,15,2.5,"El peso debe encontrarse entre 2.5 y 15 Kg en ni&ntilde;os menores de un a&ntilde;o");
                mayor_menor(document.all.talla,100,30,"La talla debe encontrarse entre 30 y 100 cm");

            }//CIERRE NI�O EDAD = 0
            /////////-------------- DE 1 A 6 A�OS-------------------////////////////////////////////
            if (document.all.nino_edad.value=="1"){
                mayor_menor(document.all.peso,50,5,"El peso debe encontrarse entre 5 y 50 kg para ni&ntilde;os de entre 1 a 5 a&ntilde;os");	
                mayor_menor(document.all.talla,160,40,"La talla del ni&ntilde;o debe encontrarse entre 40 y 160 cm para ni&ntilde;os de entre 1 a 5 a&ntilde;os");	

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
                                alert("Fecha introducida err&oacute;nea");
                                return false;
                        }
 
                        if (dia>numDias || dia==0){
                            alert("Fecha introducida err&oacute;nea");
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
<? // echo $tema.'*'.$id_nomenclador_detalle.'*'.$cuie.'*'.$fecha_comprobante.'*'.$clave_beneficiario.'*'.$fecha_nacimiento.'*'.$sexo_codigo.'*'.$edad.'*'.$prestacion.'*'.$tema.'*'.$patologia.'*'.$profesional.'*'.$pagina_viene.'*'.$id_comprobante; ?>

<form name='form1' action='remediar_carga.php' method='POST'>
    <input type="hidden" value="<?= $id_planilla ?>" name="id_planilla">
    <input type="hidden" value="<?= $pagina ?>" name="pagina">
    <input type="hidden" value="<?= $id_smiafiliados ?>" name="id_smiafiliados">
    <input type="hidden" value="<?= $pagina_viene ?>" name="pagina_viene">
    <input type="hidden" value="<?= $tema ?>" name="tema">
    <input type="hidden" value="<?= $id_nomenclador_detalle ?>" name="id_nomenclador_detalle">
    <input type="hidden" value="<?= $cuie ?>" name="cuie">
    <input type="hidden" value="<?= $fecha_comprobante ?>" name="fecha_comprobante">
    <input type="hidden" value="<?= $clave_beneficiario ?>" name="clave_beneficiario">
    <input type="hidden" value="<?= $fecha_nacimiento ?>" name="fecha_nacimiento">
    <input type="hidden" value="<?= $sexo_codigo ?>" name="sexo_codigo">
    <input type="hidden" value="<?= $edad ?>" name="edad">
    <input type="hidden" value="<?= $prestacion ?>" name="prestacion">
    <input type="hidden" value="<?= $tema ?>" name="tema">
    <input type="hidden" value="<?= $patologia ?>" name="patologia">
    <input type="hidden" value="<?= $profesional ?>" name="profesional">
    <input type="hidden" value="<?= $id_comprobante ?>" name="id_comprobante">
<? echo "<center><b><font size='+1' color='Blue'>$accion2</font></b></center>"; ?>
<? echo "<center><b><font size='+1' color='red'>$accion</font></b></center>"; ?>   
    
    <table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?= $bgcolor_out ?>' class="bordes">
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
                <?
                } else {
                    ?>
                    <font size=+1><b>Dato</b></font>   
                <? } ?>
            </td>
        </tr>

        <!--********************
        Comienzo del formulario
        ************************-->  

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
                            <b> N&uacute;mero del Dato: <font size="+1" color="Red"><?= ($id_planilla) ? $id_planilla : "Nuevo Dato" ?></font> </b>           <label></label>
                        </td>
                    </tr>
                    <tr>	           
                        <td align="center" colspan="2">
                            <b><font size="2" color="Red">Nota: Los valores numericos se ingresan SIN separadores de miles, y con "." como separador DECIMAL</font> </b>           
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding-left:10px">
                            <b>Clave Beneficiario:</b><?= $clave ?><input type="hidden" value="<?= $clave ?>" name="clave" />
                        </td>
                    </tr> 
                    <tr >
                        <td style="padding-left:10px">
                            <b>Efector:</b> <?
                $sql = "select * from facturacion.smiefectores where cuie='$cuie'";
                $res_efectores = sql($sql) or fin_pagina();
                echo $res_efectores->fields['cuie'] . '-' . $res_efectores->fields['nombreefector'];
                ?><input type="hidden" value="<?= $cuie ?>" name="cuie" />
                        </td>
                        <td style="padding-left:10px">

                            <b>Fecha de Emp.:</b><?= $fechaempadronamiento ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding-left:10px">
                            <b>Apellido:</b><?= $apellido ?><input type="hidden" value="<?= $apellido ?>" name="apellido" />
                            &nbsp;&nbsp;
                            <b>Nombre:</b><?= $nombre ?><input type="hidden" value="<?= $nombre ?>" name="nombre" />
                            &nbsp;&nbsp;
                            <b>Clase de Doc.:</b><? if ($clase_doc == 'P')
                                echo "Propio";
                            if ($clase_doc == 'A')
                                echo "Ajeno" ?><input type="hidden" value="<?= $clase_doc ?>" name="clase_doc" />
                            &nbsp;&nbsp;
                            <b>Tipo de Doc.:</b> <?= $tipo_doc ?><input type="hidden" value="<?= $tipo_doc ?>" name="tipo_doc" />


                        </td>
                    </tr> 
                    <tr>
                        <td colspan="2" style="padding-left:10px">

                            <b>Nro. de Doc.:</b> <?= $num_doc ?><input type="hidden" value="<?= $num_doc ?>" name="num_doc" />
                            &nbsp;&nbsp;
                            <b>Fecha de Nacimiento:</b>	<?= $fecha_nac ?><input type="hidden" value="<?= $fecha_nac ?>" name="fecha_nac" />
                            &nbsp;&nbsp;
                            <b>Edad-A&ntilde;os:</b><?= substr($fechaempadronamiento, 6, 10) - substr($fecha_nac, 6, 10) ?><input type="hidden" value="<?= substr($fechaempadronamiento, 6, 10) - substr($fecha_nac, 6, 10) ?>" name="nino_edad" />
                            &nbsp;&nbsp;
                            <b>Sexo:</b> <? if ($sexo == 'F')
                                echo "Femenino";
                            if ($sexo == 'M')
                                echo "Masculino"; ?>

                        </td>                  
                    </tr>     
                    <tr>
                        <td colspan="2" style="padding-left:10px">

                            <b> Datos Cobertura: </b><?= $os ?>  <?= $cual_os ?>	
                            &nbsp;&nbsp;
                            <b>Telefono:</b>	<?= $telefono ?>
                            &nbsp;&nbsp;
                            <b>Provincia:</b><?= $provincia_nac ?>
                            &nbsp;&nbsp;
                            <b>Departamento:</b> <?= $departamento ?>

                        </td>                  
                    </tr>  
                    <tr>
                        <td colspan="2" style="padding-left:10px">					  
                            <b> Municipio: </b><?= $municipio ?>
                            &nbsp;&nbsp;
                            <b> Localidad: </b><?= $localidad ?>
                            &nbsp;&nbsp;
                            <b> Calle-Ruta: </b><?= $calle ?>
                            &nbsp;&nbsp;
                            <b> Nro. de Puerta: </b><?= $numero_calle ?>

                        </td>                  
                    </tr> 
                    <tr>
                        <td colspan="2" style="padding-left:10px">
                            <b> Barrio: </b><?= $barrio ?>
                            &nbsp;&nbsp;
                            <b> Mza.: </b><?= $manzana ?>
                            &nbsp;&nbsp;
                            <b> Piso: </b><?= $piso ?>
                            &nbsp;&nbsp;
                            <b> Casa-Depto: </b><?= $dpto ?>	
                        </td>                  
                    </tr>      
                </table>   
            </td>
        </tr>       


        </td>
        </tr>

        <!--********************
        Comienzo del 1body del formulario
        ************************-->  
        <td>
            <table width=95% align="center" class="bordes" style="margin-top:5px">
                <table width=95% align="center" class="bordes" >
                    <tr>
                        <td id=mo colspan="2">
                            <b> Evaluaci&oacute;n y Clasificaci&oacute;n Cardiovascular</b>
                        </td>
                    </tr>
                    <tr><td colspan="2" style="padding-left:10px"><b>En los siguientes casos NO es necesario la UTILIZACION DE LA TABLA DE PREDICCION DE RCVG</b></td></tr>
                    <tr><td colspan="2" style="padding-left:10px">MENOR DE 39 A&NtildeOS - Evaluar presencia de factores de riesgo y estilos de vida</td></tr>
                    <td><table class="bordes">
                            <tr><td colspan="2" style="padding-top:5px;padding-left:10px"><input type="checkbox" name="acv"<?php if (strtoupper($acv) == 1)
                                echo "checked"; ?>  <?= $desabil ?>/> Accidente Cerebrovascular (ACV)
                                    &nbsp;</td></tr>
                            <tr><td colspan="2" style="padding-left:10px"><input type="checkbox" name="vas_per"<?php if (strtoupper($vas_per) == 1)
                                echo "checked"; ?>  <?= $desabil ?>/> Vasculopat&iacute;a perif&eacute;rica
                                    &nbsp;</td></tr>
                            <tr><td colspan="2" style="padding-left:10px"><input type="checkbox" name="car_isq"<?php if (strtoupper($car_isq) == 1)
                                echo "checked"; ?>  <?= $desabil ?>/> Cardiopat&iacute;a isqu&eacute;mica
                                    &nbsp;</td></tr>
                            <tr><td colspan="2" style="padding-left:10px"><input type="checkbox" name="col310"<?php if (strtoupper($col310) == 1)
                                echo "checked"; ?>  <?= $desabil ?>/> Colesterol total >= 310 mg/dl
                                    &nbsp;</td></tr>
                            <tr><td colspan="2" style="padding-left:10px"><input type="checkbox" name="col_ldl"<?php if (strtoupper($col_ldl) == 1)
                                echo "checked"; ?>  <?= $desabil ?>/> Colesterol LDL >= 230 mg/dl
                                    &nbsp;</td></tr>
                            <tr><td colspan="2" style="padding-left:10px"><input type="checkbox" name="ct_hdl"<?php if (strtoupper($ct_hdl) == 1)
                                echo "checked"; ?>  <?= $desabil ?>/> Relaci&oacute;n CT/HDL > 8
                                    &nbsp;</td></tr>
                            <tr><td colspan="2" style="padding-left:10px"><input type="checkbox" name="pres_art"<?php if (strtoupper($pres_art) == 1)
                                echo "checked"; ?>  <?= $desabil ?>/> Cifras de Presi&oacute;n Arterial permanente elevadas > 160-170 nm Hg de sist&oacute;lica y 100-105 nm Hg de diast&oacute;lica.
                                    &nbsp;</td></tr>
                            <tr><td colspan="2" style="padding-left:10px"><input type="checkbox" name="dmt2"<?php if (strtoupper($dmt2) == 1)
                                echo "checked"; ?>  <?= $desabil ?>/> DMT tipo 2 con nefropat&iacute;a manifiesta, deterioro o insuficiencia de la funci&oacute;n renal.
                                    &nbsp;</td></tr>
                            <tr><td colspan="2" style="padding-left:10px"><input type="checkbox" name="insu_renal"<?php if (strtoupper($insu_renal) == 1)
                                echo "checked"; ?>  <?= $desabil ?>/> Con insuficiencia renal o deterioro de la funci&oacute;n renal
                                    &nbsp;</td></tr>
                            <tr><td colspan="2" style="padding-left:10px"><input type="checkbox" name="dmt_menor"<?php if (strtoupper($dmt_menor) == 1)
                                echo "checked"; ?>  <?= $desabil ?>/> Menor de 39 a&ntilde;os con DMT tipo 2 
                                    &nbsp;</td></tr>
                            <tr><td colspan="2" style="padding-left:10px"><input type="checkbox" name="hta_menor"<?php if (strtoupper($hta_menor) == 1)
                                echo "checked"; ?>  <?= $desabil ?>/> Menor de 39 a&ntilde;os con HTA que requiere tratamiento farmacol&oacute;gico 
                                    &nbsp;</td></tr>
                            <tr><td style="padding-top:5px;padding-left:10px"><b>Quedan excluidas Diabetes gestacional e Hipertensi&oacute;n del embarazo</b></td></tr>
                        </table></td>
                </table>

                <!--********************
                    Comienzo del 2body del formulario
                    ************************-->  

                <tr>
                    <td>                       
                        <table width=95% align="center" class="bordes" style="margin-top:5px">
                            <tr>
                                <td colspan="2">
                            <tr>
                                <td align="center" id='mo' colspan="2"><b>Clasificaci&oacute;n con utilizaci&oacute;n de tabla de predicci&oacute;n RCVG</b></td>
                            </tr>
                            <tr>
                                <td align="left" style="padding-left:10px">
                                    <div style="display:inline;">
                                        <b title="Sin DMT">Sin DMT</b> 
                                        <input type="radio" value="0" name="dmt" <?php if (strtoupper($dmt) == "0")
                                echo "checked"; ?> title="Sin DMT"  <?= $desabil ?>/>
                                        &nbsp;
                                        <b title="DMT 1">DMT1</b> 
                                        <input type="radio" value="1" name="dmt" <?php if (strtoupper($dmt) == "1")
                                echo "checked"; ?> title="DMT 1"  <?= $desabil ?>/>
                                        &nbsp;
                                        <b title="DMT 2">DMT2</b>
                                        <input style="margin-right:5px" type="radio" value="2" name="dmt" <?php if (strtoupper($dmt) == "2")
                                echo "checked"; ?>  title="DMT 2" <?= $desabil ?>/>
                                        <b title="Hipertension Arterial">HTA</b>
                                        <input type="checkbox" name="hta" <?php if (strtoupper($hta) == 1)
                                echo "checked"; ?>  title="Hipertension Arterial"  <?= $desabil ?>/>
                                        <b title="Control de la Presion Arterial Sistolica">TA Sist</b>
                                        <input type="text" value="<?= $ta_sist ?>" name="ta_sist" size="3" style="font-size:9px;" maxlength="3" title="Control de la Presion Arterial Sistolica" <?= $desabil ?>/>
                                        &nbsp;
                                        <b title="Control de la Presion Arterial Diastolica">TA Diast</b>
                                        <input type="text" value="<?= $ta_diast ?>" name="ta_diast" size="3" style="font-size:9px;" maxlength="3" title="Control de la Presion Arterial Diastolica"<?= $desabil ?>/>
                                        &nbsp;
                                        <b>Tabaquismo</b>
                                        <input type="checkbox" name="tabaquismo"<?php if (strtoupper($tabaquismo) == 1)
                                echo "checked"; ?>  <?= $desabil ?>/>
                                        &nbsp;
                                        <b title="Control del colesterol Total">Col. Tot.</b>
                                        <input type="text" value="<?= $col_tot ?>" name="col_tot" size="3" style="font-size:9px;" maxlength="3" title="Control del colesterol Total" <?= $desabil ?>/>mg/dl
                                        &nbsp;
                                    </div>
                            <tr><td><table class="bordes">
                                        <tr><td align="left" style="padding-left:10px"><input type="checkbox" name="meno_prema"<?php if (strtoupper($meno_prema) == 1)
                                echo "checked"; ?>  <?= $desabil ?>/> Menopausia prematura ( < 35 a&ntilde;os )
                                                &nbsp;</td></tr>
                                        <tr><td align="left" style="padding-left:10px"><input type="checkbox" name="antihiper"<?php if (strtoupper($antihiper) == 1)
                                echo "checked"; ?>  <?= $desabil ?>/> Pacientes en tratamiento antihipertensivo
                                                &nbsp;</td></tr>
                                        <tr><td align="left" style="padding-left:10px"><input type="checkbox" name="obesi"<?php if (strtoupper($obesi) == 1)
                                echo "checked"; ?>  <?= $desabil ?>/> Obesidad ( IMC >= 30 )
                                                &nbsp;</td></tr>
                                        <tr><td align="left" style="padding-left:10px"><input type="checkbox" name="acv_prema"<?php if (strtoupper($acv_prema) == 1)
                                            echo "checked"; ?>  <?= $desabil ?>/> Antecedentes familiares de cardiopat&iacute;a isqu&eacute;mica o de ACV prematuro ( Hombre < 55 a&ntilde;os; mujer < 65 a&ntilde;os )
                                                &nbsp;</td></tr>
                                        <tr><td align="left" style="padding-left:10px"><input type="checkbox" name="trigli"<?php if (strtoupper($trigli) == 1)
                                echo "checked"; ?>  <?= $desabil ?>/> Concentraci&oacute;n elevada de triglic&eacute;ridos ( > 180 mg/dl)
                                                &nbsp;</td></tr>
                                        <tr><td align="left" style="padding-left:10px"><input type="checkbox" name="hdl_col"<?php if (strtoupper($hdl_col) == 1)
                                echo "checked"; ?>  <?= $desabil ?>/> Concentraci&oacute;n baja de colesterol HDL (Hombres < 40 mg/dl; Mujeres < 50 mg/dl)
                                                &nbsp;</td></tr>
                                        <tr><td align="left" style="padding-left:10px"><input type="checkbox" name="hiperglu"<?php if (strtoupper($hiperglu) == 1)
                                echo "checked"; ?>  <?= $desabil ?>/> Hiperglucemia en ayunas o intolerancia a la glucosa
                                                &nbsp;</td></tr>
                                        <tr><td align="left" style="padding-left:10px"><input type="checkbox" name="microalbu"<?php if (strtoupper($microalbu) == 1)
                                echo "checked"; ?>  <?= $desabil ?>/> Microalbuminuria
                                                &nbsp;</td></tr>
                                    </table></td></tr>
                </tr>
            </table>
        </td>
        </tr>

        <!--********************
          Comienzo del footer del formulario
          ************************-->  

        <tr>
            <td><table width=95% align="center" class="bordes" style="margin-top:5px">
                    <tr><td><div style="background-color:#A2A2A2"><b>Riesgo Cardiovascular Global</b>                                
                                <input style="margin-left:10px" type="radio" value="bajo" name="riesgo_global" <?php if (strtoupper($riesgo_global) == "BAJO")                                    
                                echo "checked"; ?> title="Riesgo Bajo"  <?= $desabil ?>/> Bajo < 10%
                                <input style="margin-left:10px" type="radio" value="mode" name="riesgo_global" <?php if (strtoupper($riesgo_global) == "MODERAD")
                                echo "checked"; ?> title="Riesgo Moderado"  <?= $desabil ?>/> Moderado 10% a < 20% 
                                <input style="margin-left:10px" type="radio" value="alto" name="riesgo_global" <?php if (strtoupper($riesgo_global) == "ALTO")
                                echo "checked"; ?> title="Riesgo Alto"  <?= $desabil ?>/> Alto 20% a < 30% 
                                <input style="margin-left:10px" type="radio" value="malto" name="riesgo_global" <?php if (strtoupper($riesgo_global) == "MUYALTO")
                                echo "checked"; ?> title="Riesgo Muy Alto"  <?= $desabil ?>/> Muy Alto > 30% 
                            </div>
                        </td>
                    </tr>
                    <tr><td style="padding-top:5px;padding-left:15px" align="left"><? if($fecha_clasificacion=='') $fecha_clasificacion = date("d/m/Y"); ?>
                            Fecha de clasificaci&oacute;n<input type='text' id='fecha_clasificacion' name='fecha_clasificacion' value='<?= Fecha($fecha_clasificacion); ?>' size=15 onKeyUp="mascara(this,'/',patron,true);" onblur="esFechaValida(this);" <?= $desabil ?>/>
        <?=link_calendario("fecha_clasificacion"); ?>
                            &nbsp;&nbsp; </td></tr>
                </table></td>
        </tr>

        <tr>
            <td colspan="2">
                <table width="100%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?= $bgcolor_out ?>' class="bordes">
                    <tr>
                        <td align="center" id='ma'>
                            <button style="font-size:9px;" onclick="window.open('busca_medico.php','Buscar','dependent:yes,width=900,height=700,top=1,left=60,scrollbars=yes');" <?= $desabil ?>>Buscar</button>
                            <b>Datos del Medico</b> 
                        </td>
                    </tr>
                    <tr>
                        <td align="left" style="padding-left:10px">
                            <input type="hidden" size="30" value="<?= $id_medico ?>" name="id_medico" maxlength="50" readonly>
                            <b>Apellido:</b>         	
                            <input type="text" size="30" value="<?= $apellido_medico ?>" name="apellido_medico" maxlength="50" readonly>          
                            &nbsp;
                            <b>Nombre:</b>     
                            <input type="text" size="30" value="<?= $nombre_medico ?>" name="nombre_medico" maxlength="50" readonly>
                            &nbsp;
                            <b>Doc. Medico:</b>         
                            <input type="text" size="16" value="<?= $dni_medico ?>" name="dni_medico" maxlength="12" readonly> 
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

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
                           title="Editar datos de la Planilla" <?= $desabiledi ?>/>
                </td>
                <td>
                    <input type='submit' name='guardar' value='Guardar Planilla' onclick="return control_nuevos()"
                           title="Guardar datos de la Planilla" <?= $desabil ?>/>
                </td>
            </tr>
        </table>           
        <br>

        </form>

            <?
if(($accion2=="Se actualizo la Planilla")||($accion2=="Se guardo la Planilla Clasificacion Nro. " . $nro_clasificacion ." y la Prestacion: " . $id_prestacion)){
    sleep(2);
    echo('<script>window.close();</script>');
    } ?> 

        <?= fin_pagina(); // aca termino ?>







        <!-- cambios al header del formulario, pendientes hasta preguntar a Betina-->

        <!--table width=95% align="center" class="bordes" >
                 <tr>	           
                   <td align="center" colspan="2">
                    <b> N�mero del Dato: <font size="+1" color="Red"><?= ($id_planilla) ? $id_planilla : "Nuevo Dato" ?></font> </b>           <label></label>
                           </td>
                 </tr>
                 <tr>	           
                   <td align="center" colspan="2">
                     <b><font size="2" color="Red">Nota: Los valores numericos se ingresan SIN separadores de miles, y con "." como separador DECIMAL</font> </b>           
                           </td>
                 </tr>
                 
        <!-- Encabezado del formulario -->   
               <!-- <tr>
               <td colspan="2" style="padding-left:10px">
                 <b>Clave Beneficiario:</b><?= $clave ?><input type="hidden" value="<?= $clave ?>" name="clave" />
                       </td>
        </tr> 
        <tr>
            <td style="padding-left:10px"> <b>Fecha de Nacimiento:</b>	<?= $fecha_nac ?><input type="hidden" value="<?= $fecha_nac ?>" name="fecha_nac" />
                                &nbsp;&nbsp;</td>
               <td style="padding-left:10px">
                               <b>Efector:</b> <?
        $sql = "select * from facturacion.smiefectores where cuie='$cuie'";
        $res_efectores = sql($sql) or fin_pagina();
        echo $res_efectores->fields['cuie'] . '-' . $res_efectores->fields['nombreefector'];
        ?><input type="hidden" value="<?= $cuie ?>" name="cuie" />
                       </td>
                       <td style="padding-left:10px">
                       
                               <b>Fecha de Emp.:</b><?= $fechaempadronamiento ?>
                       </td>
        </tr>
        <tr>
            <td colspan="2" style="padding-left:10px"><b>Tipo de Doc.:</b> <?= $tipo_doc ?><input type="hidden" value="<?= $tipo_doc ?>" name="tipo_doc" />
                
                <b>Clase de Doc.:</b><? if ($clase_doc == 'P')
            echo "Propio";
        if ($clase_doc == 'A')
            echo "Ajeno" ?><input type="hidden" value="<?= $clase_doc ?>" name="clase_doc" />
                       &nbsp;&nbsp;
                <b>Nro. de Doc.:</b> <?= $num_doc ?><input type="hidden" value="<?= $num_doc ?>" name="num_doc" />
                         &nbsp;&nbsp;
                <b>Sexo:</b> <? if ($sexo == 'F')
            echo "Femenino";
        if ($sexo == 'M')
            echo "Masculino"; ?></td>
        </tr>
        </table>-->

        <!-- Fin Encabezado del formulario-->

        <!-- Comienzo del 1er body del formulario>

        <table width=95% align="center" class="bordes" style="margin-top:10px">
            <tr>	           
           <td align="left" colspan="2" style="padding-bottom:5px; padding-left:15px">
             <b><font size="1" color="black">En caso de cambio de domicilio aclarar datos obligatorios para actualizar el padron</font> </b>           
                   </td>
         </tr>
                 <tr>
                <td colspan="2" style="padding-left:10px">
                    <b>Telefono:</b>	<?= $telefono ?>
                                 &nbsp;&nbsp;
                    <b>Provincia:</b><?= $provincia_nac ?>
                                 &nbsp;&nbsp;
                    <b>Departamento:</b> <?= $departamento ?>
                    <b> Municipio: </b><?= $municipio ?>
                        &nbsp;&nbsp;
                        
                    </td>
                  <!--b>Apellido:</b><?= $apellido ?><input type="hidden" value="<?= $apellido ?>" name="apellido" />
                          &nbsp;&nbsp;
                           <b>Nombre:</b><?= $nombre ?><input type="hidden" value="<?= $nombre ?>" name="nombre" />
                            &nbsp;&nbsp;>			           	 
                   
         </tr> 
          <tr>
                        <td colspan="2" style="padding-left:10px">
                                
                  <b> Localidad: </b><?= $localidad ?>
                         &nbsp;&nbsp;
                        <b> Calle-Ruta: </b><?= $calle ?>
                        &nbsp;&nbsp;
                        <b> N�/KM: </b><?= $numero_calle ?>
                        <b title="Zona">Urbano</b> 
                    <input type="radio" value="1" name="zona" <?php if (strtoupper($zona) == "1")
            echo "checked"; ?> title="Urbano"  <?= $desabil ?>/>    
                    <b title="Zona">Rural</b> 
                    <input type="radio" value="2" name="zona" <?php if (strtoupper($zona) == "2")
            echo "checked"; ?> title="Rural"  <?= $desabil ?>/>    
                        <b> Barrio: </b><?= $barrio ?>
                         &nbsp;&nbsp;	
                                <!b>Edad-A�os:</b><?= substr($fechaempadronamiento, 6, 10) - substr($fecha_nac, 6, 10) ?><input type="hidden" value="<?= substr($fechaempadronamiento, 6, 10) - substr($fecha_nac, 6, 10) ?>" name="nino_edad" />
                                 &nbsp;&nbsp;>
                                
                                 
                        </td>                  
          </tr>     
          <!tr>
                        <td colspan="2" style="padding-left:10px">
                                                          
                                <b> Datos Cobertura: </b><?= $os ?>  <?= $cual_os ?>	
                                &nbsp;&nbsp;
                                
                                
                                        
                        </td>                  
          </tr>  
                  <tr>
                        <td colspan="2" style="padding-left:10px">					  
                                
                        
                        
                        </td>                  
          </tr> 
                  <tr>
                        <td colspan="2" style="padding-left:10px">
                        
                        <b> Mza.: </b><?= $manzana ?>
                        &nbsp;&nbsp;
                        <b> Piso: </b><?= $piso ?>
                         &nbsp;&nbsp;
                        <b> Casa-Depto: </b><?= $dpto ?>	
                        </td>                  
          </tr>      
      </table-->   

        <!-- Parte de farmacia desactivado en el nuevo formulario-->

        <!--td colspan="2">
                                <table width="100%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?= $bgcolor_out ?>' class="bordes">
                <tr>
                        <td align="center" id='ma'><b>Medicaci&oacute;n en miligramos por d&iacute;a</b> </td>
                </tr>
                <tr>
                        <td align="left" style="padding-left:10px">
                                          <b>Enalapril</b> 					
                                                <input type="text" value="<?= $enalapril_mg ?>" name="enalapril_mg" size="3" style="font-size:9px;" maxlength="4" onblur="if(this.value<2.5 || this.value>40){ this.value='0';}" <?= $desabil ?>/>mg
                                                &nbsp;
                                                <b>Furosemida</b> 					
                                                <input type="text" value="<?= $furosemida_mg ?>" name="furosemida_mg" size="3" style="font-size:9px;" maxlength="4" onblur="if(this.value<20 || this.value>80){ this.value='0';}" <?= $desabil ?>/>mg
                                                &nbsp;
                                                <b title="Glibenclamida">Glibenclam</b> 					
                                                <input type="text" value="<?= $glibenclam_mg ?>" name="glibenclam_mg" size="3" style="font-size:9px;" maxlength="4" title="Glibenclamida" onblur="if(this.value<2.5 || this.value>20){ this.value='0';}" <?= $desabil ?>/>mg
                                                &nbsp;
                                                <b title="Simvastatina">Simvastat</b> 					
                                                <input type="text" value="<?= $simvastat_mg ?>" name="simvastat_mg" size="3" style="font-size:9px;" maxlength="4" title="Simvastatina" onblur="if(this.value<10 || this.value>80){ this.value='0';}" <?= $desabil ?>/>mg
                                                &nbsp;
                                                <b>Otras Drogas</b> 
                                                <input type="text" value="<?= $otras_drogas ?>" name="otras_drogas" size="16" style="font-size:9px;" maxlength="60" <?= $desabil ?>/>
                                                <input type="text" value="<?= $otras_drogas_mg ?>" name="otras_drogas_mg" size="3" style="font-size:9px;" maxlength="4" <?= $desabil ?>/>mg
                        </td>
                </tr>
                                <tr>
                        <td align="left" style="padding-left:10px">
                                          <b>Atenolol</b> 					
                                                <input type="text" value="<?= $atenolol_mg ?>" name="atenolol_mg" size="3" style="font-size:9px;" maxlength="4"  onblur="if(this.value<25 || this.value>100){ this.value='0';}" <?= $desabil ?>/>mg
                                                &nbsp;
                                                <b title="Hidroclorotizida"> Hidroclorot</b> 					
                                                <input type="text" value="<?= $hidroclorot_mg ?>" name="hidroclorot_mg" size="3" style="font-size:9px;" maxlength="4" title="Hidroclorotizida" onblur="if(this.value<12.5 || this.value>50){ this.value='0';}" <?= $desabil ?>/>mg
                                                &nbsp;
                                                <b >Metformina</b> 					
                                                <input type="text" value="<?= $metformina_mg ?>" name="metformina_mg" size="3" style="font-size:9px;" maxlength="4" onblur="if(this.value<500 || this.value>2000){ this.value='0';}" <?= $desabil ?>/>mg
                                                &nbsp;
                                                <b>Insulina</b> 					
                                                si<input type="radio" value="1" name="insulina" <?php if (strtoupper($insulina) == "1")
            echo "checked"; ?>  <?= $desabil ?>/>
                                                no<input type="radio" value="0" name="insulina" <?php if (strtoupper($insulina) == "0")
            echo "checked"; ?>  <?= $desabil ?>/>
                                                &nbsp;
                                                <b title="Aspirina">ASS</b> 					
                                                <input type="text" value="<?= $ass_mg ?>" name="ass_mg" size="3" style="font-size:9px;" maxlength="4" title="Aspirina" onblur="if(this.value<75 || this.value>325){ this.value='0';}" <?= $desabil ?>/>mg
                                                &nbsp;
                                                <input type="text" value="<?= $otras_drogas2 ?>" name="otras_drogas2" size="16" style="font-size:9px;" maxlength="60" <?= $desabil ?>/>
                                                <input type="text" value="<?= $otras_drogas2_mg ?>" name="otras_drogas2_mg" size="3" style="font-size:9px;" maxlength="4" <?= $desabil ?>/>mg
                        </td>
                </tr>
                        </table>
                </td>
      </tr>
          <tr>-->

       