<?php

define("NRO_FACTURA_MISIONES", "(case when facturacion.factura.nro_fact_offline <> ''
  then cast(facturacion.factura.nro_fact_offline as text)
  else cast(facturacion.factura.id_factura as text) end) as numero_factura, ");

function nro_factura_misiones() {
    $func_nroFactura = false;
    $queryfunciones = "SELECT accion,nombre
			FROM sistema.funciones
      where 
        habilitado='s' 
        and nombre='Nro Factura' 
        and pagina='facturacion'";
    $res_fun = sql($queryfunciones) or fin_pagina();
    if ($res_fun->recordCount() > 0)
        $func_nroFactura = true;
    return $func_nroFactura;
}

function fn_dato_convenio($cuie) {
    $queryfunciones = "SELECT case when t1.padre is not null or t1.padre<>'' then t2.fecha_comp_ges else t1.fecha_comp_ges end as fecha_comp_ges
						,case when t1.padre is not null or t1.padre<>'' then t2.fecha_fin_comp_ges else t1.fecha_fin_comp_ges end as fecha_fin_comp_ges
					FROM nacer.efe_conv as t1
					left join nacer.efe_conv as t2 on  t2.cuie=t1.padre
					where t1.cuie='$cuie' and case when t1.padre is not null or t1.padre<>'' then t2.fecha_comp_ges else t1.fecha_comp_ges end  is not null
							and case when t1.padre is not null or t1.padre<>'' then t2.fecha_fin_comp_ges else t1.fecha_fin_comp_ges end is not null";
    $res_fun = sql($queryfunciones) or fin_pagina();
    $valor['fecha_comp_ges'] = $res_fun->fields['fecha_comp_ges'];
    $valor['fecha_fin_comp_ges'] = $res_fun->fields['fecha_fin_comp_ges'];
    // $x=fopen("archivos2.txt","w");   $as=$cuie.'**'.$res_fun->fields['fecha_comp_ges'].'**'.$res_fun->fields['fecha_fin_comp_ges'];   if($x)    {     fwrite($x,$as);    }
    return $valor;
}

function limite_trazadora() {
    /* Para saber en que cuatrimestre estamos */
    $actualm = date('m');
    $actualy = date('Y');
    $ano = date('Y');
    $desdem1 = '01';
    $hastam1 = '04';
    $desdem2 = '05';
    $hastam2 = '08';
    $desdem3 = '10';
    $hastam3 = '12';
    if (($actualm >= $desdem1 && $actualm <= $hastam1) && $actualy == $ano) {
        $cuatrimestre = 1;
        $cuatrimestrem = 3;
    }
    if (($actualm >= $desdem2 && $actualm <= $hastam2) && $actualy == $ano) {
        $cuatrimestre = 2;
        $cuatrimestrem = $cuatrimestre - 1;
    }
    if (($actualm >= $desdem3 && $actualm <= $hastam3) && $actualy == $ano) {
        $cuatrimestre = 3;
        $cuatrimestrem = $cuatrimestre - 1;
    }
    if ($cuatrimestre > 2) {
        $cuatrimestrem = $cuatrimestre - 1;
    }

    $query2 = "SELECT desde
					FROM facturacion.cuatrimestres
					 where cuatrimestre='$cuatrimestre'";
    $res_sql2 = sql($query2) or excepcion("Error al buscar cuatrimestre");
    $valor['desde'] = $ano . '-' . $res_sql2->fields['desde'];

    $query2 = "SELECT limite
					FROM cuatrimestres
					 where cuatrimestre='$cuatrimestrem'";
    $res_sql2 = sql($query2) or excepcion("Error al buscar cuatrimestre");
    $valor['limite'] = $ano . '-' . $res_sql2->fields['limite'];


    return $valor;
}

function controlado_si_no($id_factura) {
    $valor = '';
    $queryfunciones = "SELECT upper(ctrl) as ctrl,trim(nro_fact_offline) as nro_fact_offline
			FROM facturacion.factura
      where factura.id_factura=$id_factura";
    $res_fun = sql($queryfunciones) or excepcion("Error al buscar factura controlada o no");
    if ($res_fun->fields['nro_fact_offline'] == null || $res_fun->fields['ctrl'] ==
            'S') {
        $valor = 'disabled';
    }
    return $valor;
}

function provincia_uso() {
    $queryfunciones = "SELECT upper(accion)as accion,nombre
			FROM sistema.funciones
      where habilitado='s'   and nombre='Provincia'";
    $res_fun = sql($queryfunciones) or fin_pagina();
    if ($res_fun->recordCount() > 0)
        $prov_uso = $res_fun->fields['accion'];
    return $prov_uso;
}

function obtenerComprobante(&$l, &$var) {
    $comprobante["cuie"] = $l[1];
    $comprobante["id_factura"] = 1; //obtener el id luego de guardar la factura
    $comprobante["nombre_medico"] = $l[55];
    $comprobante["fecha_comprobante"] = date("d/m/Y", strtotime(str_replace('/', '-', $l[13])));
    $comprobante["clave_beneficiario"] = $l[5];
    $comprobante["id_smiafiliado"] = obtenerIdSmiafiliado($l[5], $l[6], $l[7], $l[8], $var, $l);
    $comprobante["fecha_carga"] = date("d/m/Y");
    $comprobante["periodo"] = $l[2];
    $comprobante["id_servicio"] = 1;
    $comprobante["activo"] = 'S';
    return $comprobante;
}

function obtenerPrestacion(&$l, &$var) {
    list($id_nomenclador, $precio_prestacion, $id_anexo) =
            obtenerDatosPorNomenclador($l[12], $l, $var);
    $prestacion["id_comprobante"] = $var['id_comprobante'];
    $prestacion["id_nomenclador"] = $id_nomenclador;
    $prestacion["cantidad"] = 1;
    $prestacion["precio_prestacion"] = $precio_prestacion;
    $prestacion["id_anexo"] = $id_anexo;
    $prestacion["peso"] = 0;
    $prestacion["tension_arterial"] = 0;
    $prestacion["prestacionid"] = $l[4];
    return $prestacion;
}

function obtenerEmbarazada(&$l, &$var) {
    if ($l[18] == null) {
        $l[18] = '';
        $var['error'] = 'si';
        $var['descripcion_error'] .= ',fecha1control';
    }
    if ($l[19] == null) {
        $l[19] = '';
        $var['error'] = 'si';
        $var['descripcion_error'] .= ',SemanaGestacion1control';
    }
    if ($l[28] == null) {
        $l[28] = '';
        $var['error'] = 'si';
        $var['descripcion_error'] .= ',fpp';
    }
    if ($l[29] == null) {
        $l[29] = '';
        $var['error'] = 'si';
        $var['descripcion_error'] .= ',fum';
    }
    if ($l[20] == null)
        $l[20] = '';
    if ($l[21] == null)
        $l[21] = '';
    if ($l[22] == null)
        $l[22] = '';
    if ($l[23] == null)
        $l[23] = '';
    if ($l[24] == null)
        $l[24] = '';
    if ($l[25] == null)
        $l[25] = '';
    if ($l[26] == null)
        $l[26] = '';
    if ($l[27] == null || $l[27] == '')
        $l[27] = 0;
    if ($l[51] == null)
        $l[51] = '';
    $embarazada["cuie"] = $l[1];
    $embarazada["clave"] = $l[5];
    $embarazada["tipo_doc"] = $l[7];
    $embarazada["num_doc"] = $l[8];
    $embarazada["apellido"] = $l[9];
    $embarazada["nombre"] = $l[10];
    $embarazada["fecha_control"] = $l[13];
    $embarazada["sem_gestacion"] = $l[19];
    $embarazada["fum"] = $l[29];
    $embarazada["fpp"] = $l[28];
    $embarazada["fpcp"] = $l[13]; //revisar
    //$embarazada["observaciones"] = $l[];
    $embarazada["fecha_carga"] = date("d/m/Y");
    $embarazada["usuario"] = $_ses_user['id'];
    $embarazada["antitetanica"] = $l[51];
    $embarazada["vdrl"] = $l[23] != '' ? 'S' : 'N';
    $embarazada["estado_nutricional"] = $l[20];
    $embarazada["antitetanica_primera_dosis"] = $l[21];
    $embarazada["antitetanica_segunda_dosis"] = $l[22];
    $embarazada["hiv"] = $l[24] != '' ? 'S' : 'N';
    $embarazada["eco"] = $l[25];
    $embarazada["fecha_obito"] = $l[26];
    $embarazada["nro_control_actual"] = $l[27];
    $embarazada["tension_arterial_maxima"] = $l[59];
    $embarazada["tension_arterial_minima"] = $l[60];
    $embarazada["altura_uterina"] = $l[61];
    $embarazada["peso_embarazada"] = $l[64];
    $embarazada["vdrl_fecha"] = $l[23];
    $embarazada["hiv_fecha"] = $l[24];
    $embarazada["municipio"] = $l[66];
    $embarazada["discapacitado"] = $l[69];
    $embarazada["fecha_nacimiento"] = $l[11];
    $embarazada["fecha_nacimiento"] = $l[4];
    return $embarazada;
}

function obtenerParto(&$l, &$var) {
    if ($var['error'] == 'si')
        $var['ojo'] = 'si';
    if ($l[41] == null || $l[41] == '') {
        $apgar = 0;
        $var['error'] = 'si';
        $var['descripcion_error'] .= ',apgar';
    }
    /* si desconocido null o distinto de N y S; debita */
    if ($l[47] == null || ($l[47] != 'N' && $l[47] != 'S')) {
        $desconocido = '';
        $var['error'] = 'si';
        $var['descripcion_error'] .= ',OBBconocido';
    } else {
        $desconocido = $l[47];
        /* si desconocido no y fecha obito no null paga */
        if ($l[41] == '0' && $desconocido == 'N') {
            if ($var['ojo'] != 'si')
                $var['error'] = 'no';
            $var['descripcion_error'] = str_replace(',apgar', '', $var['descripcion_error']);
        }
        /* si desconocido si y fecha obito null paga */
        if ($l[41] == '0' && $desconocido == 'S' && $l[44] != null) {
            if ($var['ojo'] != 'si')
                $var['error'] = 'no';
            $var['descripcion_error'] = str_replace(',apgar', '', $var['descripcion_error']);
        }
    }
    /* FIN _si desconocido null o distinto de N y S; debita */
    if ($l[40] == null || $l[40] == '0') {
        $pesoalnacer = '';
        $var['error'] = 'si';
        $var['descripcion_error'] .= ',pesoalnacer';
    }
    if ($l[42] == null) {
        $vdrl = '';
        $var['error'] = 'si';
        $var['descripcion_error'] .= ',vdrl';
    }
    if ($l[43] == null) {
        $antitetanica = '';
        $var['error'] = 'si';
        $var['descripcion_error'] .= ',antitetanica';
    }
    if (!($l[43] == 'S' || $l[43] == 'N')) {
        $var['error'] = 'si';
        $var['descripcion_error'] .= ',antitetanica';
    }
    if ($l[46] == null)
        $consejeria = '';
    if ($l[44] == null)
        $obitohijo = '';
    if ($l[45] == null)
        $obitomadre = '';
    $parto["cuie"] = $l[2];
    $parto["clave"] = $l[5];
    $parto["tipo_doc"] = $l[7];
    $parto["num_doc"] = $l[8];
    $parto["apellido"] = $l[9];
    $parto["nombre"] = $l[10];
    $parto["fecha_parto"] = $l[13];
    $parto["apgar"] = $l[41];
    $parto["peso"] = $l[40];
    $parto["vdrl"] = $l[42];
    $parto["antitetanica"] = $l[43];
    $parto["fecha_conserjeria"] = $l[46];
    $parto["observaciones"] = $l[58];
    $parto["fecha_carga"] = date("d/m/Y");
    $parto["usuario"] = $_ses_user['id'];
    $parto["obito_bebe"] = $l[44];
    $parto["obito_madre"] = $l[45];
    $parto["id_prestacion"] = $l[4];
    $parto["obb_desconocido"] = $l[47];
    $parto["talla_rn"] = $l[62];
    $parto["perimcef_rn"] = $l[63];
    $parto["fecha_nacimiento"] = $l[11];
    $parto["discapacitado"] = $l[69];
    $parto["municipio"] = $l[66];
    return $parto;
}

function obtenerNinio($l, &$error, &$descripcion_error) {
    if ($l[30] == null) {
        $peso = '';
        $error = 'si';
        $descripcion_error .= ',peso';
    }
    if ($l[31] == null && $l[12] != 'HAM 00') {
        $percpesoedad = '';
        $error = 'si';
        $descripcion_error .= ',percpesoedad';
    }
    if ($l[33] == null && $l[12] != 'HAM 00') {
        $perctallaedad = '';
        $error = 'si';
        $descripcion_error .= ',perctallaedad';
    }
    if (edad_relativa($l[11], $l[13]) >= 1) {
        /* mayor de 1 año */
        if ($l[36] == null && $l[12] != 'HAM 00') {
            $percpesotalla = '';
            $error = 'si';
            $descripcion_error .= ',percpesotalla';
        }
    }
    if ($l[32] == null) {
        $talla = '';
        $error = 'si';
        $descripcion_error .= ',talla';
    }
    if (edad_relativa($l[11], $l[13]) < 1) {
        /* menor de 1 año */
        if ($l[34] == null && $l[12] != 'HAM 00') {
            $perimcef = '';
            $error = 'si';
            $descripcion_error .= ',perimcef';
        }
        if ($l[35] == null && $l[12] != 'HAM 00') {
            $percperimcefedad = '';
            $error = 'si';
            $descripcion_error .= ',percperimcefedad';
        }
    }
    if ($l[38] == null)
        $fechaobito = '';
    if ($l[39] == null)
        $nrocontrol = '';

    $ninios["cuie"] = $l[2];
    $ninios["clave"] = $l[5];
    $ninios["clase_doc"] = $l[6];
    $ninios["tipo_doc"] = $l[7];
    $ninios["num_doc"] = $l[8];
    $ninios["apellido"] = $l[9];
    $ninios["nombre"] = $l[10];
    $ninios["fecha_nac"] = $l[11];
    $ninios["fecha_control"] = $l[13];
    $ninios["peso"] = $l[30];
    $ninios["talla"] = $l[32];
    $ninios["percen_peso_edad"] = $l[31];
    $ninios["percen_talla_edad"] = $l[33];
    $ninios["perim_cefalico"] = $l[34];
    $ninios["percen_perim_cefali_edad"] = $l[35];
    $ninios["imc"] = $l[68];
    //$ninios["percen_imc_edad"] = $l[];
    $ninios["percen_peso_talla"] = $l[36];
    $ninios["triple_viral"] = $l[37];
    $ninios["nino_edad"] = date('d/m/Y', strtotime(date('Y/m/d')) - strtotime($l[11]));
    $ninios["observaciones"] = $l[58];
    $ninios["fecha_carga"] = date("d/m/Y");
    $ninios["usuario"] = $_ses_user['id'];
    $ninios["fecha_obito"] = $l[38];
    $ninios["ncontrolanual"] = $l[39];
    $ninios["id_prestacion"] = $l[4];
    $ninios["sexo"] = $l[65];
    $ninios["municipio"] = $l[66];
    $ninios["percentilo_imc"] = $l[67];
    $ninios["discapacitado"] = $l[69];
    $ninios["cod_aldea"] = $l[70];
    $ninios["descr_aldea"] = $l[71];
    $ninios["calle"] = $l[72];
    $ninios["num_calle"] = $l[73];
    $ninios["barrio"] = $l[74];
    $ninios["cod_nomenclador"] = $l[12];
    return $ninios;
}

function obtenerIdSmiafiliado($clave, $clase, $tipo, $dni, &$var, &$l) {
    //$var['metez'] = 's';
    $sql = "SELECT * FROM nacer.smiafiliados WHERE clavebeneficiario = '$clave'";
    $result = sql($sql, "No se encuentra smiafiliado con clave: $clave", 0);
    if ($result->RowCount() > 0) {
        //verificar clase, tipo y dni
        //var_dump($result);
        $result->movefirst();
        if (trim($result->fields['aficlasedoc']) == $clase && $result->fields['afitipodoc'] ==
                $tipo && $result->fields['afidni'] == $dni) {
            //si está todo bien:
            return $result->fields['id_smiafiliados'];
        } else {
            //generar error y tratarlo
            //return "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";
            if ($l[0] == "T" || $l[0] == "L" && ($l[3] == 1 || $l[3] == 2 || $l[3] == 3 || $l[3] ==
                    14)) {
                return 0;
            } else {
                $var['error'] = 'si';
                $var['descripcion_error'] .= ', clave no corresponde con datos';
                return 0;
            }
        }
    } else {
        if ($clase == 'A') {
            $sql = "SELECT id_smiafiliados FROM nacer.smiafiliados WHERE trim(aficlasedoc) = '$clase' AND afitipodoc = '$tipo' AND manrodocumento = '$dni'";
        } else {
            $sql = "SELECT id_smiafiliados FROM nacer.smiafiliados WHERE afitipodoc = '$tipo' AND trim(aficlasedoc) = '$clase' AND afidni = '$dni'";
        }
        $result = sql($sql, "No se encuentra smiafiliado con clase: $clase, tipo: $tipo y dni: $dni", 0);
        if ($result->RowCount() > 0) {
            $var['error'] = 'si';
            $var['descripcion_error'] .= ', dni no coincide con clave';
            return $result->fields['id_smiafiliados'];
        } else {
            //generar error y tratarlo
            //return "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";
            $var['error'] = 'si';
            $var['descripcion_error'] .= ', no encuentra smiafiliado';
            //$var['metez'] = 'n';
            return 0;
        }
    }
}

function obtenerIdVigencia(&$l) {
    $hoy = Fecha_db($l[13]);
    $sql = "SELECT id_nomenclador_detalle FROM facturacion.nomenclador_detalle WHERE fecha_desde <= '$hoy' AND fecha_hasta >= '$hoy'";
    $result = sql($sql, "", 0);
    if ($result->RowCount() > 0) {
        return $result->fields['id_nomenclador_detalle'];
    } else {
        return false;
    }
}

function obtenerDatosPorNomenclador($cod, &$l, &$var) {
    //  //obtener nro de orden
    //  $precio = 0;
    //  $cod_orig = $cod;
    //  $cod = explode('-', $cod);
    //  $nro_orden = intval($cod[1]);
    //  //obtener cod nomenclador
    //  $cod_nom = $cod[0];
    //  //$cod_nom = str_replace(' ', '', $cod[0]);
    //  //buscar id en facturacion.nomenclador
    //  $id_vigencia = obtenerIdVigencia();
    //  //$id_vigencia = 2; //obtenerIdVigencia();
    //  $sql_n = "SELECT id_nomenclador, precio FROM facturacion.nomenclador WHERE codigo = '$cod_orig' AND id_nomenclador_detalle = $id_vigencia";
    //  $result_n = sql($sql_n, "No se encuentra nomenclador con cod: $cod_nom", 0);
    //  if ($result_n->RowCount() > 0) {
    //    $id_nomenclador = $result_n->fields['id_nomenclador'];
    //  } else {
    //    //si no encuentra, separo el cod en partes y armo de nuevo
    //    $cod1 = substr($cod_nom, 0, 3);
    //    $cod2 = intval(substr($cod_nom, 3));
    //    $ceros = $cod2 < 10 ? '0' : '';
    //    $cod_nom2 = $cod1 . ' ' . $ceros . $cod2;
    //    $sql_n = "SELECT id_nomenclador, precio FROM facturacion.nomenclador WHERE codigo = '$cod_nom2' AND id_nomenclador_detalle = $id_vigencia";
    //    $result_n = sql($sql_n, "No se encuentra nomenclador con cod: $cod_nom2", 0);
    //    if ($result_n->RowCount() > 0) {
    //      $id_nomenclador = $result_n->fields['id_nomenclador'];
    //    } else {
    //      $var['error_datos'] = 'si';
    //      $var['mjs_error_datos'] .= ',CodNomenclador';
    //      //$l[12] = 'M 999';
    //      //echo 'xxxxxxxxxxxxxxxxxxxxxxxx   ' . $cod_nom2 . '   xxxxxxxxxxxxxxxxxxxxxxxx';
    //      $id_nomenclador = -1;
    //      //return array(-1, -1, -1);
    //    }
    //  }
    //obtener nro de orden
    $precio = 0;

    $cod_a = explode('-', $cod);
    $nro_orden = intval($cod_a[1]);
    $codigo = $cod_a[0];

    $codigo1 = $codigo;
    //$cod_orig = $cod;
    //  //obtener cod nomenclador
    //  $cod_nom = $cod[0];
    //  $codigo[] = $cod_nom;
    $codigo2 = str_replace(' ', '', $codigo);
    //$cod_nom = str_replace(' ', '', $cod[0]);
    //buscar id en facturacion.nomenclador
    $codigo3 = substr($codigo, 0, 6) . substr($codigo, 7, 1);
    $codigo4 = substr($codigo, 0, 3) . ' ' . substr($codigo, 3);

    $id_vigencia = obtenerIdVigencia($l);
    //$id_vigencia = 2; //obtenerIdVigencia();
    //$sql_n = "SELECT id_nomenclador, precio FROM facturacion.nomenclador WHERE codigo IN('$codigo', '$codigo1', '$codigo2', '$codigo3', '$codigo4') AND id_nomenclador_detalle = $id_vigencia";
    $sql_n = "SELECT id_nomenclador, precio FROM facturacion.nomenclador WHERE replace(codigo,' ','') = '$codigo2' AND id_nomenclador_detalle = $id_vigencia";
    $result_n = sql($sql_n, "No se encuentra nomenclador con cod: $codigo1", 0);
    if ($result_n->RowCount() > 0) {
        $id_nomenclador = $result_n->fields['id_nomenclador'];
    } else {
        $sql_n = "SELECT id_nomenclador, precio FROM facturacion.nomenclador WHERE replace(codigo,' ','') = '$codigo2'";
        $result_n = sql($sql_n, "No se encuentra nomenclador con cod: $codigo1", 0);
        if ($result_n->RowCount() > 0) {
            excepcion('Rechazado por Código de Nomenclador no vigente en la línea ' . $var['row']);
        }
        $var['error_datos'] = 'si';
        $var['mjs_error_datos'] .= ',CodNomenclador';
        $l[12] = 'M 999';
        excepcion('Rechazado por Código de Nomenclador no existente en la línea ' . $var['row'] .
                " ($cod)");
        //echo 'xxxxxxxxxxxxxxxxxxxxxxxx   ' . $cod_nom2 . '   xxxxxxxxxxxxxxxxxxxxxxxx';
        $id_nomenclador = -1;
        //return array(-1, -1, -1);
    }

    //buscar anexo
    if ($nro_orden > 0) {
        if ($id_nomenclador) {
            $sql_a = "SELECT id_anexo, precio FROM anexo WHERE id_nomenclador = $id_nomenclador AND id_nomenclador_detalle = $id_vigencia AND numero = $nro_orden";
            $result_a = sql($sql_a, "", 0);
            if ($result_a->RowCount() > 0) {
                $precio = $result_a->fields['precio'];
                $id_anexo = $result_a->fields['id_anexo'];
            } else {
                $id_anexo = -1;
                $precio = $result_n->fields['precio'];
            }
        } else {
            $id_anexo = -1;
            $precio = -1;
        }
    } else {
        $id_anexo = -1;
        $precio = $result_n->fields['precio'];
    }
    //devolver resultados
    //    $precio = 6574674676746;
    if ($precio == -1 || $precio == '')
        excepcion('No se encuentra nomenclador: ' . $l[12] . ' en línea: ' . $var['row']);
    return array($id_nomenclador, $precio, $id_anexo);
}

function edad_relativa($fecha_nacimiento, $fecha_consulta) {
    list($dia_nacimiento, $mes_nacimiento, $anio_nacimiento) = explode("/", $fecha_nacimiento);
    list($dia_consulta, $mes_consulta, $anio_consulta) = explode("/", $fecha_consulta);
    $anio_dif = $anio_consulta - $anio_nacimiento;
    $mes_dif = $mes_consulta - $mes_nacimiento;
    $dia_dif = $dia_consulta - $dia_nacimiento;
    if (($dia_dif < 0 && $mes_dif == 0) || $mes_dif < 0)
        $anio_dif--;
    return $anio_dif;
}

function limpiar($registro) {
    foreach ($registro as $clave => $valor) {
        $valor = str_replace("''", "", $valor);
        $valor = str_replace("'", "", $valor);
        $valor = str_replace("!", "", $valor);
        $valor = str_replace("///", "", $valor);
        $valor = str_replace("//", "", $valor);
        $valor = str_replace("}", "", $valor);
        $valor = str_replace("{", "", $valor);
        $valor = str_replace("?:..", "", $valor);
        $valor = str_replace("?", "", $valor);
        $valor = str_replace('"', "", $valor);
        $valor = str_replace("#", "", $valor);
        $valor = str_replace("=", "", $valor);
        $valor = str_replace("~", "", $valor);
        $valor = str_replace("%", "", $valor);
        $valor = str_replace("(", "", $valor);
        $valor = str_replace(")", "", $valor);
        $registro[$clave] = $valor;
    }
    return $registro;
}

function verificarValidezFechaPrestacion(&$l, &$var) {

    //$pr = $fp_pacomp - $l[11]; //$fcomp['pr'];
    //$nc = $fprest_limite - $fp_pacomp;
    //$new_txt = '01/08/2009' - $fp_pacomp;
    $fprest_limite = str_replace('/', '-', $var['fprest_limite']);
    $fp_pacomp = str_replace('/', '-', $l[13]);
    $pr = floor(abs(strtotime($fp_pacomp) - strtotime($l[11])) / 86400);
    $nc = floor(abs(strtotime($fprest_limite) - strtotime($fp_pacomp)) / 86400);
    $var['new_txt'] = floor(abs(strtotime('01-08-2009') - strtotime($fp_pacomp)) /
            86400);

    $var['nacimerr'] = 'no';
    if ($nc < 0) {
        if ($l[3] == 1 || $l[12] == 'NPE 41' || $l[12] == 'RPE 93') {
            $var['descripcion_error'] .= ',fnac no antes ctrl';
            $var['error'] = 'si';
        }
        $var['nacimerr'] = 'si';
        $var['error_datos'] = 'si';
        $var['mjs_error_datos'] .= ',FechaNac>FechaPrestacion';
    }
    $var['fuera_prest'] = 'no';
    if ($pr < 0) {
        $var['descripcion_error'] .= ',fecha prest';
        $var['error'] = 'si';
        $var['fuera_prest'] = 'si';
        $var['error_datos'] = 'si';
        $var['mjs_error_datos'] .=
                ',No corresponde fecha de prestacion para el periodo liquidado';
    }
}

function prepararDatosComprobante(&$l, &$var) {
    if ($l[5] == null) {
        $claveBeneficiario = 'vacio';
    } else {
        $claveBeneficiario = $l[5];
    }
    if ($l[6] == null) {
        $l[6] = '';
        $var['error_datos'] = 'si';
        $var['mjs_error_datos'] .= ',Clase Doc.';
        if ($l[3] == 1) {
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',claseDoc';
        }
    } else {
        $var['error'] = 'no';
    }
    if ($l[7] == null) {
        $l[7] = '';
        $var['error'] = 'si';
        $var['descripcion_error'] .= ',tipoDoc';
    } else {
        $tipoDoc = $l[7];
        if ($tipoDoc == 'EXT') {
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',tipoDoc';
        }
        if ($tipoDoc == 'CI' || $tipoDoc == 'ci') {
            $l[7] = 'C20';
        }
    }
    $l[8] = intval($l[8]);
    if ($l[8] == '' || $l[8] == null) {
        $l[8] = 0;
        $var['error'] = 'si';
        $var['error_datos'] = 'si';
        $var['mjs_error_datos'] .= ',NroDoc.';
    }

    $permitidos = "0123456789";
    for ($i = 0; $i < strlen($l[8]); $i++) {
        if (strpos($permitidos, substr($l[8], $i, 1)) === false) {
            $var['error'] = 'si';
            $l[8] = 0;
            $var['error_datos'] = 'si';
            $var['mjs_error_datos'] .= ',NroDoc.';
        }
    }
    $var['metez'] = 's';
    if ($l[8] == 0 && $claveBeneficiario == 'vacio') {
        $var['descripcion_error'] .= ',no clave_dni';
        $var['error'] = 'si';
        $var['error_datos'] = 'si';
        $var['mjs_error_datos'] .= ',NroDoc y ClaveBeneficiario';
        $var['metez'] = 'n';
    }
    if ($l[9] == null) {
        $l[9] = '';
    } else {
        $l[9] = str_replace("'", "", $l[9]);
    }
    if ($l[10] == null) {
        $l[10] = '';
    } else {
        $l[10] = str_replace("'", "", $l[10]);
    }
    if ($l[11] == null) {
        $l[11] = '';
    }
    ////////////////////////////////////////////////////////////////////////////////

    list($dia, $mes, $ano) = explode('/', $l[13]);
    $var['prestacion'] = $ano . $mes . $dia;
    list($dia1, $mes1, $ano1) = explode('/', $var['fechaNac']);
    $var['nacimiento'] = $ano1 . $mes1 . $dia1;
    $var['menor'] = $var['prestacion'] - $var['nacimiento'];

    if ($l[16] == null)
        $l[16] = '';

    if ($l[17] == null)
        $l[17] = '';

    if ($l[18] == null || $l[3] == 2 || $l[3] == 3)
        $l[18] = '';

    /* si formato nuevo */
    if ($l[3] == 14) {
        if ($l[48] == null) {
            $l[48] = '';
            $var['e_defuncion'] = 'si';
            $var['descripcion_error'] .= ',fdefuncion';
            $var['error'] = 'si';
        }
        if ($l[49] == null)
            $l[49] = '';
        if ($l[50] == null) {
            $l[50] = '';
            $var['e_caso'] = 'si';
            $var['descripcion_error'] .= ',caso';
            $var['error'] = 'si';
        }
    }
    /* fin_ si formato nuevo */
    $var['perimcef_rn'] = 0;
    $var['talla_rn'] = 0;
    $var['au'] = 0;
    $var['tamin'] = 0;
    $var['tamax'] = 0;
    $var['peso_mem02'] = 0;
    //  if ($var['new_txt'] <= 0) {
    if ($l[3] == 2) {
        if ($l[59] == null || $l[59] == '')
            $l[59] = 0; //$descripcion_error.=',tamax'; $error='si';
        if ($l[60] == null || $l[60] == '')
            $l[60] = 0; //$descripcion_error.=',tamin'; $error='si';
        if ($l[61] == null || $l[61] == '')
            $l[61] = 0; //if($l[12]=='MEM 02' || $l[12]=='MER 08'){ $descripcion_error.=',au'; $error='si';}
        if ($l[64] == null || $l[64] == '')
            $l[64] = 0; //if($l[12]=='MEM 02'){$descripcion_error.=',peso_mem02'; $error='si';}
    }
    //    if ($l[3] == 3) {
    //    if ($l[62] == null || $l[62] == '')
    //      $l[62] = 0; //$descripcion_error.=',talla_rn'; $error='si';
    //    if ($l[63] == null || $l[63] == '')
    //      $l[63] = 0; //$descripcion_error.=',perimcef_rn'; $error='si';
    //  }
    //}
    if ($l[62] == null || $l[62] == '')
        $l[62] = 0; //$descripcion_error.=',talla_rn'; $error='si';
    if ($l[63] == null || $l[63] == '')
        $l[63] = 0; //$descripcion_error.=',perimcef_rn'; $error='si';
    if ($l[65] == null)
        $l[65] = '';
    /* $error_datos='si'; $mjs_error_datos.=',Sexo'; */
    if ($l[66] == null || $l[66] == '')
        $l[66] = 0;
    /* $error_datos='si'; $mjs_error_datos.=',Municipio'; */
    if ($l[3] == 1) {
        if ($l[67] == null)
            $l[67] = '';
        if (strlen($l[67]) > 1) {
            $var['descripcion_error'] .= ',percentilo_imc';
            $var['error'] = 'si';
            //excepcion('Rechazado por valor erróneo de percentilo_imc');
        }
        /* if ($menor >=10000){//mayor de 1 año
          $descripcion_error.=',percentilo_imc'; $error='si'; } */
        if ($l[68] == null || $l[68] == '')
            $l[68] = 0;
        /* if ($menor >=10000){//mayor de 1 año
          $descripcion_error.=',imc'; $error='si'; } */
    }
    if ($l[53] == null || $l[53] == '')
        $l[53] = 0;
    if ($l[69] == null)
        $l[69] = '';
    /* $error_datos='si'; $mjs_error_datos.=',discapacitado'; */
    if ($l[70] == null)
        $l[70] = '';
    /* $error_datos='si'; $mjs_error_datos.=',discapacitado'; */
    if ($l[71] == null)
        $l[71] = '';
    /* $error_datos='si'; $mjs_error_datos.=',discapacitado'; */
    if ($l[72] == null)
        $l[72] = '';
    /* $error_datos='si'; $mjs_error_datos.=',discapacitado'; */
    if ($l[73] == null)
        $l[73] = '';
    /* $error_datos='si'; $mjs_error_datos.=',discapacitado'; */
    if ($l[74] == null)
        $l[74] = '';
    /* $error_datos='si'; $mjs_error_datos.=',discapacitado'; */
}

function procesarLineaLiquidacion(&$l, &$var) {
    //$caratula_id = "exec Recepcion_CargaExpediente '$codExpediente','$nroCuerpoExp','$nroCaratula','$vig_estim','$mes_vig','$ano_vig','$fechafactura','$ddjj_sip','$fecha_mesaentrada'";
    /* $x=fopen("archivo.txt","w");   $as='**'.$caratula_id;   if($x)    {     fwrite($x,$as);    } */
    //  $resultado = mssql_query($caratula_id);
    //  if ($resultado == false) {
    //    mssql_query($SQLerror);
    //    mssql_close($conexion);
    //    fclose($handle);
    /*    echo "<SCRIPT Language='Javascript'>
      //				alert('Error.Vuelva a intentarlo');
      //				errorsimple();
      //			 </SCRIPT>"; */
    //  }
    //  if (mssql_num_rows($resultado) > 0) {
    //    while ($f1 = mssql_fetch_array($resultado)) {
    //      $idCaratula = $f1['idCaratula'];
    //    }
    //  }

    if ($l[52] == null)
        $l[52] = '';
    if ($l[54] == null)
        $l[54] = '';
    if ($l[55] == null)
        $l[55] = '';
    if ($l[56] == null)
        $l[56] = '';
    if ($l[57] == null)
        $l[57] = '';
    if ($l[58] == null)
        $l[58] = '';
    if ($l[66] == null || $l[66] == '')
        $l[66] = 0;
    ////////////verifica repetidod
    $query = "select fc.cuie as cuie, ff.recepcion_id as idrecepcion from facturacion.factura ff inner join facturacion.comprobante fc on (ff.id_factura = fc.id_factura) inner join facturacion.prestacion fp on (fc.id_comprobante = fp.id_comprobante) where fc.cuie='$l[1]' and ff.periodo='$l[2]' and prestacionid='$l[4]'";
    //sql($query, "Error al desvincular el comprobante") or fin_pagina();
    $resulx = sql($query, "Error al buscar comprobante repetido", 0) or excepcion("Error al buscar comprobante repetido");
    //  $control = "select cuie,idrecepcion
    //				from [20BenefRecepcionIpos]
    //				where cuie='$data[1]' and anoMes='$data[2]' and idPrestacion='$data[4]'";
    //  $resulx = mssql_query($control);
    //  if ($resulx == false) {
    //    echo $data[1] . '*' . $data[2] . '*' . $data[4];
    //    mssql_query($SQLerror);
    //    mssql_close($conexion);
    //    fclose($handle);
    /*    echo "<SCRIPT Language='Javascript'>
      //				alert('Error.Vuelva a intentarlo');
      //				errorsimple();
      //				</SCRIPT>"; */
    //  }
    if ($resulx->RecordCount() > 0) {
        $resulx->MoveFirst();
        while (!$resulx->EOF) {
            //while ($rfilas = mssql_fetch_array($resulx)) {
            $var['existe_id'] = 'si';
            $var['idrecepcion_idb'] = $resulx->fields['idrecepcion'];
            if ($var['idrecepcion_idb'] != $var['recepcion_id']) {
                $var['mjs_id'] = 'idprestacion ya existente en el sistema';
            }
            if ($var['idrecepcion_idb'] == $var['recepcion_id']) {
                $var['mjs_id'] = 'idprestacion ya existente en el archivo';
            }
            //$query = "select cuie	from facturacion.rechazados where cuie='$l[1]' and anoMes='$l[2]' and idPrestacion='$l[4]'";
            //      $resul_re = sql($query, "Error al buscar rechazado") or fin_pagina();
            //      if ($resul_re->RecordCount() > 0) {
            //        $var['existe_id'] = 'no';
            //      }
            $resulx->MoveNext();
        }
    }

    /* pregunta se es taller */
    $var['idtaller'] = '0';
    if ($l[12] == 'CMI 65' || $l[12] == 'CMI 66' || $l[12] == 'CMI 67' || $l[12] ==
            'RCM 107' || $l[12] == 'RCM 108' || $l[12] == 'RCM 109') {
        if ($l[15] == null || $l[15] == '') {
            $l['idtaller'] = 0;
            $var['error_datos'] = 'si';
            $var['mjs_error_datos'] .= ',IdTaller';
        } else {
            $var['idtaller'] = $l[15];
        }
    }
    /* pregunta si es vacunacion */
    $var['vacuna'] = 'si';
    $var['idvacuna'] = 0;
    if ($l[12] == 'NPE 41' || $l[12] == 'NPE 42' || $l[12] == 'RPE 93' || $l[12] ==
            'RPE 94' || $l[12] == 'NNE 31' || $l[12] == 'MPU 23') {
        if ($l[14] == null || $l[14] == '') {
            $var['error_datos'] = 'si';
            $var['mjs_error_datos'] .= ',Id. Vacuna Invalido';
            $l[14] == 0;
        } else {
            $var['idvacuna'] = $l[14];
            if ($var['existe_id'] == 'no') {
                $id_nomenclador = obtenerIdNomenclador($l[12], $l, $var);
                //$tbl_trazadora = obtenerTablaTrazadora($l[3]);
                $id_smiafiliado = obtenerIdSmiafiliado($l[5], $l[6], $l[7], $l[8], $var, $l);
                $query = "select fp.id_nomenclador from facturacion.prestacion fp inner join facturacion.comprobante fc on(fp.id_comprobante = fc.id_comprobante) where ((fp.id_nomenclador='$id_nomenclador' and fc.idvacuna='$l[14]' and fc.fecha_comprobante='" .
                        Fecha_db($l[13]) . "') and ((fc.clavebeneficiario='$l[5]') or (fc.id_smiafiliados = $id_smiafiliado)))";
                $resC = sql($query, "Error al buscar vacuna", 0) or excepcion("Error al buscar vacuna");
                //$resC = mssql_query($contro4);
                if ($resC->RecordCount() > 0) {
                    //while ($r = mssql_fetch_array($resC)) {
                    $var['error_datos'] = 'si';
                    $var['mjs_error_datos'] .= ',Vacuna duplicada';
                    //}
                }
            }
        }
    }
    /* pregunta se es traslado */
    if ($l[12] == 'TMI 69' || $l[12] == 'TMI 70' || $l[12] == 'TMI 71' || $l[12] ==
            'RTM 111' || $l[12] == 'RTM 112' || $l[12] == 'RTM 113') {
        if ($l[15] == null)
            $l['idtaller'] = 0;
    }

    /* Si corresponde auditoria */
    //  $auditoriaxontrol = "exec Recepcion_CargaAuditoria '$data[1]','$data[2]','$idCaratula','$idRecepcion','$codExpediente','$nroCuerpoExp','$nroCaratula','$user'";
    //  $reauti = mssql_query($auditoriaxontrol);
    //  if ($reauti == false) {
    //    mssql_query($SQLerror);
    //    mssql_close($conexion);
    //    fclose($handle);
    /*    echo "<SCRIPT Language='Javascript'>
      //						alert('Error.Vuelva a intentarlo');
      //						errorsimple();
      //					</SCRIPT>"; */
    //  }
}

function existeIdTrazadora(&$l, &$var) {
    $var['ya_esta'] = 'no';
    if ($l[3] == 1)
        $idr = "SELECT tn.clave FROM facturacion.prestacion fp INNER JOIN trazadoras.nino_new tn ON (fp.id_prestacion = tn.id_prestacion) WHERE tn.cuie='$l[1]' AND fp.prestacionid=$l[4] AND fp.id_nomenclador='" .
                $var['id_nomenclador'] . "'";

    if ($l[3] == 2)
        $idr = "SELECT tn.clave FROM facturacion.prestacion fp INNER JOIN trazadoras.embarazadas tn ON (fp.id_prestacion = tn.id_prestacion) WHERE tn.cuie='$l[1]' AND fp.prestacionid=$l[4] AND fp.id_nomenclador='" .
                $var['id_nomenclador'] . "'";

    if ($l[3] == 3)
        $idr = "SELECT tn.clave FROM facturacion.prestacion fp INNER JOIN trazadoras.partos tn ON (fp.id_prestacion = tn.id_prestacion) WHERE tn.cuie='$l[1]' AND fp.prestacionid=$l[4] AND fp.id_nomenclador='" .
                $var['id_nomenclador'] . "'";

    if ($l[3] == 14)
        $idr = "SELECT tn.cuie FROM facturacion.prestacion fp INNER JOIN trazadoras.mu tn ON (fp.id_prestacion = tn.id_prestacion) WHERE tn.cuie='$l[1]' AND fp.prestacionid=$l[4] AND fp.id_nomenclador='" .
                $var['id_nomenclador'] . "'";

    $result_idr = sql($idr, "Error al consultar existencia de trazadora $idr", 0) or
            excepcion("Error al consultar existencia de trazadora");

    if ($result_idr->recordCount > 0) {
        //    $result_idr->movefirst();
        //    while (!$result_idr->EOF) {
        //$var['clavebeneficiario'] = $result_idr->fields['clavebeneficiario'];
        //$var['eliminadosi'] = $result_idr->fields['eliminado'];
        $var['ya_esta'] = 'si';
        //      $result_idr->MoveNext();
        //    }
    }
}

function existeTrazadorarecepcion(&$l, &$var, $limite_trz) {

    if ($l[3] == 1)
        $idr = "SELECT nino_new.clave 
			FROM trazadoras.nino_new 
			inner join facturacion.recepcion on recepcion.idrecepcion=nino_new.id_recepcion
			WHERE recepcion.fecha_rec< date '" . $limite_trz['limite'] . "'
				and nino_new.cuie='$l[1]' 
				AND	(
						(nino_new.num_doc='$l[8]' and  nino_new.tipo_doc='$l[7]' and nino_new.apellido='$l[9]') or
		 				(nino_new.clave='$l[5]' and nino_new.apellido='$l[9]')
					)";

    if ($l[3] == 2)
        $idr = "SELECT embarazadas.clave 
			FROM trazadoras.embarazadas 
			inner join facturacion.recepcion on recepcion.idrecepcion=embarazadas.id_recepcion
			WHERE recepcion.fecha_rec< date '" . $limite_trz['limite'] . "'
				and embarazadas.cuie='$l[1]' 
				AND	(
						(embarazadas.num_doc='$l[8]' and embarazadas.tipo_doc='$l[7]' and embarazadas.apellido='$l[9]') or
		 				(embarazadas.clave='$l[5]' and embarazadas.apellido='$l[9]')
					)";


    if ($l[3] == 3)
        $idr = "SELECT partos.clave 
			FROM trazadoras.partos 
			inner join facturacion.recepcion on recepcion.idrecepcion=partos.id_recepcion
			WHERE recepcion.fecha_rec< date '" . $limite_trz['limite'] . "'
				and partos.cuie='$l[1]' 
				AND	(
						(partos.num_doc='$l[8]' and partos.tipo_doc='$l[7]' and partos.apellido='$l[9]') or
		 				(partos.clave='$l[5]' and partos.apellido='$l[9]')
					)";



    $result_idr = sql($idr, "Error al consultar existencia de trazadora $idr", 0) or
            excepcion("Error al consultar existencia de trazadora");

    if ($result_idr->recordCount == 0) {
        $e_sql = "select id_debito from facturacion.debito where id_factura=" . $var['id_factura'] . " and documento_deb='" . $l[8] . "' and codigo_deb='" . $l[12] . "'";
        $e_busqueda = sql($e_sql, "Error al consultar debito") or excepcion("Error al consultar debito", 0);
        if ($e_busqueda->RecordCount() == 0) {
            list($id_nomenclador, $precio_prestacion, $id_anexo) =
                    obtenerDatosPorNomenclador($l[12], $l, $var);
            $SQLbenef = "insert into facturacion.debito (id_factura, id_nomenclador, cantidad, id_motivo_d, monto, documento_deb, apellido_deb, nombre_deb, codigo_deb, observaciones_deb, mensaje_baja) values (" .
                    $var['id_factura'] . ", " . $var['id_nomenclador'] . ", 1, 67, " . $precio_prestacion . ", '" . $l[8] . "', '" . $l[9] . "', '" . $l[10] .
                    "', '" . $l[12] . "', '" . $l[58] . "', 'Trazadoras presentada fuera de termino')";
            /////////////////////////////////////////error/////////////////////////////////
            sql($SQLbenef, "Error al insertar débito", 0) or excepcion("Error al insertar débito");
        }
    }
}

function actualizarTrazadora(&$l, &$var) {

    if ($l[3] == 1) {
        /* NIÑOS */
        if ($l[30] == null || $l[30] == '') {
            $l[30] = 0;
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',peso';
        }
        if ($l[31] == null && $l[12] != 'HAM 00') {
            $l[31] = '';
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',percpesoedad';
        }
        if ($l[33] == null && $l[12] != 'HAM 00') {
            $l[33] = '';
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',perctallaedad';
        }
        if (edad_relativa($l[11], $l[13]) >= 1) {
            /* mayor de 1 año */
            if ($l[36] == null && $l[12] != 'HAM 00') {
                $l[36] = '';
                $var['error'] = 'si';
                $var['descripcion_error'] .= ',percpesotalla';
            }
        }
        if ($l[32] == null || $l[32] == '') {
            $l[32] = 0;
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',talla';
        }
        if (edad_relativa($l[11], $l[13]) < 1) {
            /* menor de 1 año */
            if (($l[34] == null || $l[31] == '') && $l[12] != 'HAM 00') {
                $l[34] = 0;
                $var['error'] = 'si';
                $var['descripcion_error'] .= ',perimcef';
            }
            if ($l[35] == null && $l[12] != 'HAM 00') {
                $l[35] = '';
                $var['error'] = 'si';
                $var['descripcion_error'] .= ',percperimcefedad';
            }
        }
        if ($l[37] == null)
            $l[37] = null;
        if ($l[38] == null)
            $l[38] = '';
        if ($l[39] == null || $l[39] == '')
            $l[39] = '';
        if ($l[34] == null || $l[34] == '')
            $l[34] = 0;
        /* graba beneficiarios */

        $ninios["nino_edad"] = date('d/m/Y', strtotime(date('d-m-Y')) - strtotime($l[11]));
        $ninios["fecha_carga"] = date("d/m/Y");
        $ninios["usuario"] = $_ses_user['id'];

        if ($var['error'] == 'no') {

            $SQLnU = "UPDATE trazadoras.nino_new SET cuie = '" . $l[2] . "', clave = '" . $l[5] .
                    "', clase_doc = '" . $l[6] . "', tipo_doc = '" . $l[7] . "', num_doc = " . $l[8] .
                    ", apellido = '" . $l[9] . "', nombre = '" . $l[10] . "', fecha_nac = '" .
                    Fecha_db($l[11], '1899-12-31') . "', fecha_control = '" . Fecha_db($l[13], '1899-12-31') . "', peso = " . $l[30] . ", talla = " . $l[32] .
                    ", percen_peso_edad = '" . $l[31] . "', percen_talla_edad = '" . $l[33] .
                    "', perim_cefalico = " . $l[34] . ", percen_perim_cefali_edad = '" . $l[35] .
                    "', imc = '" . $l[68] . "', percen_peso_talla = '" . $l[36] .
                    "', triple_viral = '" . Fecha_db($l[37], '1899-12-31') . "', nino_edad = " . $ninios["nino_edad"] .
                    ", observaciones = '" . $l[58] . "', fecha_carga = '" . Fecha_db($ninios["fecha_carga"], '1899-12-31') . "', usuario = '" . $ninios["usuario"] . "', fecha_obito = '" .
                    Fecha_db($l[38], '1899-12-31') . "', ncontrolanual = " . $l[39] .
                    ", id_prestacion = " . $l[4] . ", sexo = '" . $l[65] . "', municipio = " . $l[66] .
                    ", percentilo_imc = '" . $l[67] . "', discapacitado = '" . $l[69] .
                    "', cod_aldea = '" . $l[70] . "', descr_aldea = '" . $l[71] . "', calle = '" . $l[72] .
                    "', num_calle = '" . $l[73] . "', barrio = '" . $l[74] .
                    "', cod_nomenclador = '" . $l[12] . "', id_recepcion = " . $var['recepcion_id'] .
                    " WHERE id_prestacion = " . $l[4] . " and cuie = '" . $l[1] .
                    "' and cod_nomenclador = '" . $l[12] . "'";
            sql($SQLnU, "Error al actualizar trazadora", 0) or excepcion("Error al actualizar trazadora");
            $var['c_n']++;
        }
    }
    if ($l[3] == 2) {
        /* EMBARAZADAS */
        if ($l[18] == null) {
            $l[18] = '';
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',fecha1control';
        }
        if ($l[19] == null || $l[19] == '') {
            $l[19] = 0;
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',SemanaGestacion1control';
        }
        if ($l[28] == null) {
            $l[28] = '';
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',fpp';
        }
        if ($l[29] == null) {
            $l[29] = '';
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',fum';
        }
        if ($l[20] == null)
            $l[20] = '';
        if ($l[21] == null)
            $l[21] = '';
        if ($l[22] == null)
            $l[22] = '';
        if ($l[23] == null)
            $l[23] = '';
        if ($l[24] == null)
            $l[24] = '';
        if ($l[25] == null)
            $l[25] = '';
        if ($l[26] == null)
            $l[26] = '';
        if ($l[27] == null || $l[27] == '')
            $l[27] = 0;
        if ($l[51] == null)
            $l[51] = '';
        $embarazada["fecha_carga"] = date("d/m/Y");
        $embarazada["usuario"] = $_ses_user['id'];

        /* graba beneficiarios */
        if ($var['error'] == 'no') {

            $SQLeU = "UPDATE trazadoras.embarazadas SET cuie = '" . $l[1] . "', clave = '" .
                    $l[5] . "', tipo_doc = '" . $l[7] . "', num_doc = " . $l[8] . ", apellido = '" .
                    $l[9] . "', nombre = '" . $l[10] . "', fecha_control = '" . Fecha_db($l[13], '1899-12-31') . "', sem_gestacion = " . $l[19] . ", fum = '" . Fecha_db($l[29], '1899-12-31') . "', fpp = '" . Fecha_db($l[28], '1899-12-31') . "', fpcp = '" .
                    Fecha_db($l[13], '1899-12-31') . "', fecha_carga = '" . Fecha_db($embarazada["fecha_carga"], '1899-12-31') . "', usuario = '" . $embarazada["usuario"] .
                    "', antitetanica = '" . $l[51] . "', vdrl = '" . Fecha_db($l[23], '1899-12-31') .
                    "', estado_nutricional = '" . $l[20] . "', antitetanica_primera_dosis = '" .
                    Fecha_db($l[21], '1899-12-31') . "', antitetanica_segunda_dosis = '" . Fecha_db($l[22], '1899-12-31') . "', hiv = '" . Fecha_db($l[24], '1899-12-31') . "', eco = '" .
                    Fecha_db($l[25], '1899-12-31') . "', fecha_obito = '" . Fecha_db($l[26], '1899-12-31') . "', nro_control_actual = " . $l[27] .
                    ", tension_arterial_maxima = " . $l[59] . ", tension_arterial_minima = " . $l[60] .
                    ", altura_uterina = " . $l[61] . ", peso_embarazada = " . $l[64] .
                    ", vdrl_fecha = '" . Fecha_db($l[23], '1899-12-31') . "', hiv_fecha = '" .
                    Fecha_db($l[24], '1899-12-31') . "', municipio = " . $l[66] .
                    ", discapacitado = '" . $l[69] . "', fecha_nacimiento = '" . Fecha_db($l[11], '1899-12-31') . "', id_prestacion = " . $l[4] . ", id_recepcion = " . $var['recepcion_id'] .
                    " WHERE id_prestacion = " . $l[4] . " and cuie = '" . $l[1] . "'";

            sql($SQLeU, "Error al actualizar trazadora", 0) or excepcion("Error al actualizar trazadora");
            $var['c_e']++;
        }
    }
    if ($l[3] == 3) {
        /* PARTOS */
        if ($var['error'] == 'si') {
            $var['ojo'] = 'si';
        }
        if ($l[41] == null || $l[41] == '') {
            $l[41] = 0;
            $var['error'] = 'si';
            $var['descripcion_error'] .= ', apgar
        ';
        }

        //*si desconocido null o distinto de N y S; debita*/
        if ($l[47] == null || ($l[47] != 'N' && $l[47] != 'S')) {
            $l[47] = '';
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',
        OBBconocido';
        } else {
            /* si desconocido no y fecha obito no null paga */
            if ($l[41] == '0' && $l[47] == 'N') {
                if ($var['ojo'] != 'si') {
                    $var['error'] = 'no';
                }
                $var['descripcion_error'] = str_replace(', apgar', '', $var['
        descripcion_error']);
            }
            /* si desconocido si y fecha obito null paga */
            if ($l[41] == '0' && $l[47] == 'S' && $l[44] != null) {
                if ($var['ojo'] != 'si') {
                    $var['error'] = 'no';
                }
                $var['descripcion_error'] = str_replace(', apgar', '', $var['
        descripcion_error']);
            }
        }
        /* FIN _si desconocido null o distinto de N y S; debita */

        if ($l[40] == null || $l[40] == '0' || $l[40] == '') {
            $l[40] = -1;
            $var['error'] = 'si';
            $var['descripcion_error'] .= ', pesoalnacer';
        }
        if ($l[42] == null) {
            $l[42] = '';
            $var['error'] = 'si';
            $var['descripcion_error'] .= ', vdrl';
        }
        if ($l[43] == null) {
            $l[43] = '';
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',
        antitetanica';
        }
        if ($l[43] == 'S' || $l[43] == 'N') {
            
        } else {
            $var['error'] = 'si';
            $var['descripcion_error'] .= ', antitetanica';
        }
        if ($l[46] == null)
            $l[46] = '';
        if ($l[44] == null)
            $l[44] = '';
        if ($l[45] == null)
            $l[45] = '';
        $parto["fecha_carga"] = date("d/m/Y");
        $parto["usuario"] = $_ses_user['id'];

        /* graba beneficiarios */
        if ($var['error'] == 'no') {

            $SQLpU = "UPDATE trazadoras.partos SET cuie = '" . $l[2] . "', clave = '" . $l[5] .
                    "', tipo_doc = '" . $l[7] . "', num_doc = " . $l[8] . ", apellido = '" . $l[9] .
                    "', nombre = '" . $l[10] . "', fecha_parto = '" . Fecha_db($l[13], '1899-12-31') .
                    "', apgar = " . $l[41] . ", peso = " . $l[40] . ", vdrl = '" . $l[42] .
                    "', antitetanica = '" . $l[43] . "', fecha_conserjeria = '" . Fecha_db($l[46], '1899-12-31') . "', observaciones = '" . $l[58] . "', fecha_carga = '" .
                    Fecha_db($parto["fecha_carga"], '1899-12-31') . "', usuario = '" . $parto["usuario"] .
                    "', obito_bebe = '" . Fecha_db($l[44]) . "', obito_madre = '" . Fecha_db($l[45], '1899-12-31') . "', id_prestacion = " . $l[4] . ", obb_desconocido = '" . $l[47] .
                    "', talla_rn = " . $l[62] . ", perimcef_rn = " . $l[63] .
                    ", fecha_nacimiento = '" . Fecha_db($l[11], '1899-12-31') .
                    "', discapacitado = '" . $l[69] . "', municipio = " . $l[66] .
                    " , id_recepcion = " . $var['recepcion_id'] . " WHERE id_prestacion = " . $l[4] .
                    " AND cuie = '" . $l[1] . "'";
            sql($SQLpU, "Error al actualizar trazadora", 0) or excepcion("Error al actualizar trazadora");
            $var['c_p']++;
        }
    }
    if ($l[3] == 14) {
        /* Muertes */
        /* graba beneficiarios */
        if ($var['error'] == 'no') {

            $SQLmU = "UPDATE trazadoras.mu SET cuie = '" . $l[1] . "', tipo_doc = '" . $l[7] .
                    "', num_doc = " . $l[8] . ", apellido = '" . $l[9] . "', nombre = '" . $l[10] .
                    "', fecha_defuncion = '" . Fecha_db($l[48], '1899-12-31') .
                    "', fecha_auditoria = '" . Fecha_db($l[13], '1899-12-31') .
                    "', fecha_par_int = '" . Fecha_db($l[13], '1899-12-31') . "', fecha_nac = '" .
                    Fecha_db($l[11], '1899-12-31') . "', observaciones = '" . $l[58] .
                    "', fecha_carga = '" . Fecha_db(date("d/m/Y")) . "', usuario = '" . $_ses_user['id'] .
                    "', clase_doc = '" . $l[6] . "', comitelocal = '" . Fecha_db($l[13], '1899-12-31') . "', caso = '" . $l[50] . "', fppmuerte = '" . Fecha_db($l[49], '1899-12-31') . "', id_prestacion = " . $l[4] . ", eliminado = '" . $var['eliminado'] .
                    "', municipio = " . $l[66] . ", clavebeneficiario = '" . $l[5] .
                    "' , id_recepcion = " . $var['recepcion_id'] . " where id_prestacion = '$l[4]' and cuie = '$l[1]'";
            $errorsql = sql($SQLmU, "Error al actualizar trazadora", 0) or excepcion("Error al actualizar trazadora");
            $var['c_m']++;
            ;
        }
    }
}

function insertarTrazadora(&$l, &$var) {
    if ($l[12] == 'NPE 41' || $l[12] == 'RPE 93') {
        $SQLnkl = "INSERT INTO trz_antisarampionosa (codigo_efector, clave_beneficiario, clase_documento, tipo_documento, numero_documento, apellido, nombre, fecha_nacimiento, fecha_control, fecha_vacunacion, prestacion_id, idprestacion, sexo, municipio, discapacitado, id_recepcion) values ('$l[1]','$l[5]','$l[6]','$l[7]','$l[8]','$l[9]','$l[10]','" .
                Fecha_db($l[11], '1899-12-31') . "','" . Fecha_db($l[13], '1899-12-31') . "','" .
                Fecha_db($l[13], '1899-12-31') . "'," . $var['id_prestacion'] . ",'$l[4]','$l[65]',$l[66],'$l[69]', " .
                $var['recepcion_id'] . ")";

        sql($SQLnkl, "Error al insertar trazadora", 0) or excepcion("Error al insertar trazadora");
    }
    if ($l[3] == 1) {
        /* NIÑOS */
        if ($l[30] == null || $l[30] == '') {
            $l[30] = 0;
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',peso';
        }
        if ($l[31] == null && $l[12] != 'HAM 00') {
            $l[31] = '';
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',percpesoedad';
        }
        if ($l[33] == null && $l[12] != 'HAM 00') {
            $l[33] = '';
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',perctallaedad';
        }
        if (edad_relativa($l[11], $l[13]) >= 1) {
            /* mayor de 1 año */
            if ($l[36] == null && $l[12] != 'HAM 00') {
                $l[36] = '';
                $var['error'] = 'si';
                $var['descripcion_error'] .= ',percpesotalla';
            }
        }
        if ($l[32] == null || $l[32] == '') {
            $l[32] = 0;
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',talla';
        }
        if (edad_relativa($l[11], $l[13]) < 1) {
            /* menor de 1 año */
            if ($l[34] == null && $l[12] != 'HAM 00') {
                $l[34] = 0;
                $var['error'] = 'si';
                $var['descripcion_error'] .= ',perimcef';
            }
            if ($l[35] == null && $l[12] != 'HAM 00') {
                $l[35] = '';
                $var['error'] = 'si';
                $var['descripcion_error'] .= ',percperimcefedad';
            }
        }
        if ($l[37] == null)
            $l[37] = '';
        if ($l[38] == null)
            $l[38] = '';
        if ($l[39] == null || $l[39] == '')
            $l[39] = 0;
        if ($l[34] == null || $l[34] == '')
            $l[34] = 0;
        $ninios["nino_edad"] = date('d/m/Y', strtotime(date('d-m-Y')) - strtotime($l[11]));
        $ninios["fecha_carga"] = date("d/m/Y");
        $ninios["usuario"] = $_ses_user['id'];

        /* graba beneficiarios */
        if ($var['error'] == 'no') {

            $SQLn = "INSERT INTO trazadoras.nino_new (cuie, clave, clase_doc, tipo_doc, num_doc, apellido, nombre, fecha_nac, fecha_control, peso, talla, percen_peso_edad, percen_talla_edad, perim_cefalico, percen_perim_cefali_edad, imc, percen_peso_talla, triple_viral, nino_edad, observaciones, fecha_carga, usuario, fecha_obito, ncontrolanual, id_prestacion, sexo, municipio, percentilo_imc, discapacitado, cod_aldea, descr_aldea, calle, num_calle, barrio, cod_nomenclador, id_recepcion) VALUES ('" .
                    $l[2] . "','" . $l[5] . "','" . $l[6] . "','" . $l[7] . "'," . $l[8] . ",'" . $l[9] .
                    "','" . $l[10] . "','" . Fecha_db($l[11], '1899-12-31') . "','" . Fecha_db($l[13], '1899-12-31') . "'," . $l[30] . "," . $l[32] . ",'" . $l[31] . "','" . $l[33] .
                    "'," . $l[34] . ",'" . $l[35] . "','" . $l[68] . "','" . $l[36] . "','" .
                    Fecha_db($l[37], '1899-12-31') . "'," . $ninios["nino_edad"] . ",'" . $l[58] .
                    "','" . Fecha_db($ninios["fecha_carga"], '1899-12-31') . "','" . $ninios["usuario"] .
                    "','" . Fecha_db($l[38], '1899-12-31') . "'," . $l[39] . "," . $l[4] . ",'" . $l[65] .
                    "'," . $l[66] . ",'" . $l[67] . "','" . $l[69] . "','" . $l[70] . "','" . $l[71] .
                    "','" . $l[72] . "','" . $l[73] . "','" . $l[74] . "','" . $l[12] . "', " . $var['recepcion_id'] .
                    ")";

            sql($SQLn, "Error al insertar trazadora", 0) or excepcion("Error al insertar trazadora");
            $var['c_n']++;

            $SQLnD = "DELETE FROM trazadoras.nino_tmp WHERE id_prestacion = $l[4] AND cuie = '$data[1]'";

            sql($SQLnD, "Error al eliminar trazadora temporal", 0) or excepcion("Error al eliminar trazadora temporal");
        }
    }
    if ($l[3] == 2) {
        /* EMBARAZADAS */
        if ($l[18] == null) {
            $l[18] = '';
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',fecha1control';
        }
        if ($l[19] == null || $l[19] == '') {
            $l[19] = 0;
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',SemanaGestacion1control';
        }
        if ($l[28] == null) {
            $l[28] = '';
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',fpp';
        }
        if ($l[29] == null) {
            $l[29] = '';
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',fum';
        }
        if ($l[20] == null)
            $l[20] = '';
        if ($l[21] == null)
            $l[21] = '';
        if ($l[22] == null)
            $l[22] = '';
        if ($l[23] == null)
            $l[23] = '';
        if ($l[24] == null)
            $l[24] = '';
        if ($l[25] == null)
            $l[25] = '';
        if ($l[26] == null)
            $l[26] = '';
        if ($l[27] == null || $l[27] == '')
            $l[27] = 0;
        if ($l[51] == null)
            $l[51] = '';
        $embarazada["fecha_carga"] = date("d/m/Y");
        $embarazada["usuario"] = $_ses_user['id'];

        /* graba beneficiarios */
        if ($var['error'] == 'no') {

            $SQLe = "INSERT INTO trazadoras.embarazadas (cuie, clave, tipo_doc, num_doc, apellido, nombre, fecha_control, sem_gestacion, fum, fpp, fpcp, fecha_carga, usuario, antitetanica, vdrl, estado_nutricional, antitetanica_primera_dosis, antitetanica_segunda_dosis, hiv, eco, fecha_obito, nro_control_actual, tension_arterial_maxima, tension_arterial_minima, altura_uterina, peso_embarazada, vdrl_fecha, hiv_fecha, municipio, discapacitado, fecha_nacimiento, id_prestacion, id_recepcion) VALUES ('" .
                    $l[1] . "','" . $l[5] . "','" . $l[7] . "'," . $l[8] . ",'" . $l[9] . "','" . $l[10] .
                    "','" . Fecha_db($l[13], '1899-12-31') . "'," . $l[19] . ",'" . Fecha_db($l[29], '1899-12-31') . "','" . Fecha_db($l[28], '1899-12-31') . "','" . Fecha_db($l[13], '1899-12-31') . "','" . Fecha_db($embarazada["fecha_carga"], '1899-12-31') .
                    "','" . $embarazada["usuario"] . "','" . $l[51] . "','" . Fecha_db($l[23], '1899-12-31') . "','" . $l[20] . "','" . Fecha_db($l[21], '1899-12-31') . "','" .
                    Fecha_db($l[22], '1899-12-31') . "','" . Fecha_db($l[24], '1899-12-31') . "','" .
                    Fecha_db($l[25], '1899-12-31') . "','" . Fecha_db($l[26], '1899-12-31') . "'," .
                    $l[27] . "," . $l[59] . "," . $l[60] . "," . $l[61] . "," . $l[64] . ",'" .
                    Fecha_db($l[23], '1899-12-31') . "','" . Fecha_db($l[24], '1899-12-31') . "'," .
                    $l[66] . ",'" . $l[69] . "','" . Fecha_db($l[11], '1899-12-31') . "'," . $l[4] .
                    ", " . $var['recepcion_id'] . ")";

            sql($SQLe, "Error al insertar trazadora", 0) or excepcion("Error al insertar trazadora");
            $var['c_e']++;

            $SQLeD = "DELETE FROM trazadoras.embarazadas_tmp where id_prestacion = $l[4] and cuie = '$l[1]'";

            sql($SQLeD, "Error al eliminar trazadora temporal", 0) or excepcion("Error al eliminar trazadora temporal");
        }
    }
    if ($l[3] == 3) {
        /* PARTOS */
        if ($var['error'] == 'si') {
            $var['ojo'] = 'si';
        }
        if ($l[41] == null || $l[41] == '') {
            $l[41] = 0;
            $var['error'] = 'si';
            $var['descripcion_error'] .= ', apgar
        ';
        }
        //*si desconocido null o distinto de N y S; debita*/
        if ($l[47] == null || ($l[47] != 'N' && $l[47] != 'S')) {
            $l[47] = '';
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',
        OBBconocido';
        } else {
            /* si desconocido no y fecha obito no null paga */
            if ($l[41] == '0' && $l[47] == 'N') {
                if ($var['ojo'] != 'si') {
                    $var['error'] = 'no';
                }
                $var['descripcion_error'] = str_replace(', apgar', '', $var['
        descripcion_error']);
            }
            /* si desconocido si y fecha obito null paga */
            if ($l[41] == '0' && $l[47] == 'S' && $l[44] != null) {
                if ($var['ojo'] != 'si') {
                    $var['error'] = 'no';
                }
                $var['descripcion_error'] = str_replace(', apgar', '', $var['
        descripcion_error']);
            }
        }
        /* FIN _si desconocido null o distinto de N y S; debita */

        if ($l[40] == null || $l[40] == '0' || $l[40] == '') {
            $l[40] = -1;
            $var['error'] = 'si';
            $var['descripcion_error'] .= ', pesoalnacer
        ';
        }
        if ($l[42] == null) {
            $l[42] = '';
            $var['error'] = 'si';
            $var['descripcion_error'] .= ', vdrl';
        }
        if ($l[43] == null) {
            $l[43] = '';
            $var['error'] = 'si';
            $var['descripcion_error'] .= ',
        antitetanica';
        }
        if ($l[43] == 'S' || $l[43] == 'N') {
            
        } else {
            $var['error'] = 'si';
            $var['descripcion_error'] .= ', antitetanica';
        }
        if ($l[46] == null)
            $l[46] = '';
        if ($l[44] == null)
            $l[44] = '
        ';
        if ($l[45] == null)
            $l[45] = '';
        $parto["fecha_carga"] = date("d/m/Y");
        $parto["usuario"] = $_ses_user['id'];

        /* graba beneficiarios */
        if ($var['error'] == 'no') {

            $SQLp = "INSERT INTO trazadoras.partos (cuie, clave, tipo_doc, num_doc, apellido, nombre, fecha_parto, apgar, peso, vdrl, antitetanica, fecha_conserjeria, observaciones, fecha_carga, usuario, obito_bebe, obito_madre, id_prestacion, obb_desconocido, talla_rn, perimcef_rn, fecha_nacimiento, discapacitado, municipio, id_recepcion)	VALUES ('" .
                    $l[2] . "','" . $l[5] . "','" . $l[7] . "'," . $l[8] . ",'" . $l[9] . "','" . $l[10] .
                    "','" . Fecha_db($l[13], '1899-12-31') . "'," . $l[41] . "," . $l[40] . ",'" . $l[42] .
                    "','" . $l[43] . "','" . Fecha_db($l[46], '1899-12-31') . "','" . $l[58] . "','" .
                    Fecha_db($parto["fecha_carga"], '1899-12-31') . "','" . $parto["usuario"] .
                    "','" . Fecha_db($l[44], '1899-12-31') . "','" . Fecha_db($l[45], '1899-12-31') .
                    "'," . $l[4] . ",'" . $l[47] . "'," . $l[62] . "," . $l[63] . ",'" . Fecha_db($l[11], '1899-12-31') . "','" . $l[69] . "'," . $l[66] . ", " . $var['recepcion_id'] .
                    ")";

            sql($SQLp, "Error al insertar trazadora", 0) or excepcion("Error al insertar trazadora");
            $var['c_p']++;

            $SQLpD = "DELETE FROM trazadoras.partos_tmp where id_prestacion='$l[4]' AND cuie='$l[1]'";

            sql($SQLpD, "Error al eliminar trazadora temporal", 0) or excepcion("Error al eliminar trazadora temporal");
        }
    }
    if ($l[3] == 14) {
        /* MUERTES */
        if ($var['error'] == 'no') {
            $SQLm = "insert into trazadoras.mu (cuie, tipo_doc, num_doc, apellido, nombre, fecha_defuncion, fecha_auditoria, fecha_par_int, fecha_nac, observaciones, fecha_carga, usuario, clase_doc, comitelocal, caso, fppmuerte, id_prestacion, municipio, clavebeneficiario, id_recepcion) values ('" .
                    $l[1] . "', '" . $l[7] . "', " . $l[8] . ", '" . $l[9] . "', '" . $l[10] .
                    "', '" . Fecha_db($l[48], '1899-12-31') . "', '" . Fecha_db($l[13], '1899-12-31') .
                    "', '" . Fecha_db($l[13], '1899-12-31') . "', '" . Fecha_db($l[11], '1899-12-31') .
                    "', '" . $l[58] . "', '" . Fecha_db(date("d/m/Y")) . "', '" . $_ses_user['id'] .
                    "', '" . $l[6] . "', '" . Fecha_db($l[13], '1899-12-31') . "', '" . $l[50] .
                    "', '" . Fecha_db($l[49], '1899-12-31') . "', " . $l[4] . ", " . $l[66] . ",'" .
                    $l[5] . "', " . $var['recepcion_id'] . ")";

            sql($SQLm, "Error al insertar trazadora", 0) or excepcion("Error al insertar trazadora");
            $var['c_m']++;

            $SQLmD = "DELETE FROM trazadoras.mu_tmp  where id_prestacion = '$l[4]' and cuie = '$l[1]'";

            sql($SQLmD, "Error al eliminar trazadora temporal", 0) or excepcion("Error al eliminar trazadora temporal");

            /*
              cuie text NOT NULL,
              tipo_doc text,
              num_doc numeric(30,6),
              apellido text,
              nombre text,
              fecha_defuncion date,
              fecha_auditoria date,
              fecha_par_int date,
              fecha_nac date,
              observaciones text,
              fecha_carga timestamp without time zone,
              usuario text,
              clase_doc text,
              comitelocal timestamp without time zone,
              caso character varying,
              fppmuerte timestamp without time zone,
              id_prestacion bigint,
              eliminado character(1),
              municipio integer,
              clavebeneficiario character(16),
             */
        }
    }
}

function insertarPrestacion(&$l, &$var, $p) {
    $sql_p = "INSERT INTO facturacion.prestacion (id_comprobante, id_nomenclador, cantidad, precio_prestacion, id_anexo, peso, tension_arterial, prestacionid) VALUES (" .
            $p["id_comprobante"] . ", " . $p["id_nomenclador"] . ", " . $p["cantidad"] .
            ", " . $p["precio_prestacion"] . ", " . $p["id_anexo"] . ", " . $p["peso"] .
            ", '" . $p["tension_arterial"] . "', " . $p["prestacionid"] .
            " ) RETURNING id_prestacion";
    $result = sql($sql_p, "Error al insertar prestación", 0) or excepcion("Error al insertar prestación");
    $var['c_pr']++;
    return $result->fields['id_prestacion'];
}

function existeIdTrazadoraTMP(&$l, &$var) {
    //  if ($l[3] == 1)
    //    $idr = "select idprestacion,clavebeneficiario,eliminado from trzniniosTMP where codigoefector='$COEF' and idprestacion='$IPRES' and codnomenclador='$data[12]'";
    //
  //  if ($l[3] == 2)
    //    $idr = "select idprestacion,clavebeneficiario,eliminado  from trzembarazadasTMP where codigoefector='$COEF' and idprestacion='$IPRES'";
    //
  //  if ($l[3] == 3)
    //    $idr = "select idprestacion,clavebeneficiario,eliminado  from trzpartosTMP where codigoefector='$COEF' and idprestacion='$IPRES'";
    //
  //  if ($l[3] == 14)
    //    $idr = "select idprestacion,clavebeneficiario,eliminado from trzmuertesTMP where codigoefector='$COEF' and idprestacion='$IPRES'";
    //
  //  $result_idr = sql($idr,
    //    "Error al consultar existencia de id prestacion en temporales") or fin_pagina();
    //
  //  if ($result_idr->recordCount > 0) {
    //    $result_idr->movefirst();
    //    while (!$result_idr->EOF) {
    //      $var['clavebeneficiarioTMP'] = $result_idr->fields['clavebeneficiario'];
    //      $var['ya_estaTMP'] = 'si';
    //      $result_idr->MoveNext();
    //    }
    //  }
    $var['ya_estaTMP'] = 'no';
    if ($l[3] == 1)
        $idr = "SELECT tn.clave FROM facturacion.prestacion fp INNER JOIN trazadoras.nino_tmp tn ON (fp.id_prestacion = tn.id_prestacion) WHERE tn.cuie='$l[1]' AND fp.prestacionid=$l[4] AND fp.id_nomenclador='" .
                $var['id_nomenclador'] . "'";

    if ($l[3] == 2)
        $idr = "SELECT tn.clave FROM facturacion.prestacion fp INNER JOIN trazadoras.embarazadas_tmp tn ON (fp.id_prestacion = tn.id_prestacion) WHERE tn.cuie='$l[1]' AND fp.prestacionid=$l[4] AND fp.id_nomenclador='" .
                $var['id_nomenclador'] . "'";

    if ($l[3] == 3)
        $idr = "SELECT tn.clave FROM facturacion.prestacion fp INNER JOIN trazadoras.partos_tmp tn ON (fp.id_prestacion = tn.id_prestacion) WHERE tn.cuie='$l[1]' AND fp.prestacionid=$l[4] AND fp.id_nomenclador='" .
                $var['id_nomenclador'] . "'";

    if ($l[3] == 14)
        $idr = "SELECT tn.cuie FROM facturacion.prestacion fp INNER JOIN trazadoras.mu_tmp tn ON (fp.id_prestacion = tn.id_prestacion) WHERE tn.cuie='$l[1]' AND fp.prestacionid=$l[4] AND fp.id_nomenclador='" .
                $var['id_nomenclador'] . "'";

    $result_idr = sql($idr, "Error al consultar existencia de trazadora temporal $idr", 0) or excepcion("Error al consultar existencia de trazadora temporal");

    if ($result_idr->RecordCount() > 0) {
        //    $result_idr->movefirst();
        //    while (!$result_idr->EOF) {
        //$var['clavebeneficiario'] = $result_idr->fields['clavebeneficiario'];
        //$var['eliminadosi'] = $result_idr->fields['eliminado'];
        $var['ya_estaTMP'] = 'si';
        //      $result_idr->MoveNext();
        //    }
    }
}

function actualizarTrazadoraTMP(&$l, &$var) {
    if ($l[3] == 1) {
        /* NIÑOS */
        $var['ni']++;

        $ninios["nino_edad"] = date('d/m/Y', strtotime(date('d-m-Y')) - strtotime($l[11]));
        $ninios["fecha_carga"] = date("d/m/Y");
        $ninios["usuario"] = $_ses_user['id'];

        $SQLnU = "UPDATE trazadoras.nino_tmp SET cuie = '" . $l[2] . "', clave = '" . $l[5] .
                "', clase_doc = '" . $l[6] . "', tipo_doc = '" . $l[7] . "', num_doc = " . $l[8] .
                ", apellido = '" . $l[9] . "', nombre = '" . $l[10] . "', fecha_nac = '" .
                Fecha_db($l[11], '1899-12-31') . "', fecha_control = '" . Fecha_db($l[13], '1899-12-31') . "', peso = " . $l[30] . ", talla = " . $l[32] .
                ", percen_peso_edad = '" . $l[31] . "', percen_talla_edad = '" . $l[33] .
                "', perim_cefalico = " . $l[34] . ", percen_perim_cefali_edad = '" . $l[35] .
                "', imc = '" . $l[68] . "', percen_peso_talla = '" . $l[36] .
                "', triple_viral = '" . Fecha_db($l[37], '1899-12-31') . "', nino_edad = " . $ninios["nino_edad"] .
                ", observaciones = '" . $l[58] . "', fecha_carga = '" . $ninios["fecha_carga"] .
                "', usuario = '" . $ninios["usuario"] . "', fecha_obito = '" . Fecha_db($l[38], '1899-12-31') . "', ncontrolanual = " . $l[39] . ", id_prestacion = " . $l[4] .
                ", sexo = '" . $l[65] . "', municipio = " . $l[66] . ", percentilo_imc = '" . $l[67] .
                "', discapacitado = '" . $l[69] . "', cod_aldea = '" . $l[70] .
                "', descr_aldea = '" . $l[71] . "', calle = '" . $l[72] . "', num_calle = '" . $l[73] .
                "', barrio = '" . $l[74] . "', cod_nomenclador = '" . $l[12] . "', mjs = '" . $var['descripcion_error'] .
                "' , id_recepcion = " . $var['recepcion_id'] . " WHERE id_prestacion = " . $l[4] .
                " and cuie = '" . $l[1] . "' and cod_nomenclador = '" . $l[12] . "'";

        sql($SQLnU, "Error al actualizar trazadora temporal", 0) or excepcion("Error al actualizar trazadora temporal");
        $var['c_n_tmp']++;
    }
    if ($l[3] == 2) {
        /* EMBARAZADAS */
        $var['em']++;
        /* graba beneficiarios */

        $embarazada["fecha_carga"] = date("d/m/Y");
        $embarazada["usuario"] = $_ses_user['id'];

        $SQLeU = "UPDATE trazadoras.embarazadas_tmp SET cuie = '" . $l[1] .
                "', clave = '" . $l[5] . "', tipo_doc = '" . $l[7] . "', num_doc = " . $l[8] .
                ", apellido = '" . $l[9] . "', nombre = '" . $l[10] . "', fecha_control = '" .
                Fecha_db($l[13], '1899-12-31') . "', sem_gestacion = " . $l[19] . ", fum = '" .
                Fecha($l[29], '1899-12-31') . "', fpp = '" . Fecha_db($l[28], '1899-12-31') .
                "', fpcp = '" . Fecha_db($l[13], '1899-12-31') . "', fecha_carga = '" . $embarazada["fecha_carga"] .
                "', usuario = '" . $embarazada["usuario"] . "', antitetanica = '" . $l[51] .
                "', vdrl = '" . $l[23] . "', estado_nutricional = '" . $l[20] .
                "', antitetanica_primera_dosis = '" . Fecha_db($l[21], '1899-12-31') .
                "', antitetanica_segunda_dosis = '" . Fecha_db($l[22], '1899-12-31') .
                "', hiv = '" . $l[24] . "', eco = '" . Fecha_db($l[25], '1899-12-31') .
                "', fecha_obito = '" . Fecha_db($l[26], '1899-12-31') .
                "', nro_control_actual = " . $l[27] . ", tension_arterial_maxima = " . $l[59] .
                ", tension_arterial_minima = " . $l[60] . ", altura_uterina = " . $l[61] .
                ", peso_embarazada = " . $l[64] . ", vdrl_fecha = '" . Fecha_db($l[23], '1899-12-31') . "', hiv_fecha = '" . Fecha_db($l[24], '1899-12-31') .
                "', municipio = " . $l[66] . ", discapacitado = '" . $l[69] .
                "', fecha_nacimiento = '" . Fecha_db($l[11], '1899-12-31') .
                "', id_prestacion = " . $l[4] . ", mjs = '" . $var['descripcion_error'] .
                "' , id_recepcion = " . $var['recepcion_id'] . " WHERE id_prestacion = " . $l[4] .
                " and cuie = '" . $l[1] . "'";

        sql($SQLeU, "Error al actualizar trazadora temporal", 0) or excepcion("Error al actualizar trazadora temporal");
        $var['c_e_tmp']++;
    }

    if ($l[3] == 3) {
        /* PARTOS */
        $var['pa']++;
        /* graba beneficiarios */

        $parto["fecha_carga"] = date("d/m/Y");
        $parto["usuario"] = $_ses_user['id'];

        $SQLpU = "UPDATE trazadoras.partos_tmp SET cuie = '" . $l[2] . "', clave = '" .
                $l[5] . "', tipo_doc = '" . $l[7] . "', num_doc = " . $l[8] . ", apellido = '" .
                $l[9] . "', nombre = '" . $l[10] . "', fecha_parto = '" . Fecha_db($l[13], '1899-12-31') . "', apgar = " . $l[41] . ", peso = " . $l[40] . ", vdrl = '" . $l[42] .
                "', antitetanica = '" . $l[43] . "', fecha_conserjeria = '" . Fecha_db($l[46], '1899-12-31') . "', observaciones = '" . $l[58] . "', fecha_carga = '" .
                Fecha_db($parto["fecha_carga"], '1899-12-31') . "', usuario = '" . $parto["usuario"] .
                "', obito_bebe = '" . Fecha_db($l[44], '1899-12-31') . "', obito_madre = '" .
                Fecha_db($l[45], '1899-12-31') . "', id_prestacion = " . $l[4] .
                ", obb_desconocido = '" . $l[47] . "', talla_rn = " . $l[62] .
                ", perimcef_rn = " . $l[63] . ", fecha_nacimiento = '" . Fecha_db($l[11], '1899-12-31') . "', discapacitado = '" . $l[69] . "', municipio = " . $l[66] .
                ", mjs = '" . $var['descripcion_error'] . "' , id_recepcion = " . $var['recepcion_id'] .
                " WHERE id_prestacion = " . $l[4] . " AND cuie = '" . $l[1] . "'";
        sql($SQLpU, "Error al actualizar trazadora temporal", 0) or excepcion("Error al actualizar trazadora temporal");
        $var['c_p_tmp']++;
    }
    if ($l[3] == 14) {
        /* Muertes */
        $var['mu']++;
        /* graba beneficiarios */
        //$SQLmU = "UPDATE Trzmuertestmp SET ClaveBeneficiario='$claveBeneficiario',ClaseDocumento='$claseDoc',TipoDocumento='$tipoDoc', NumeroDocumento='$nroDoc',Apellido='$apellido',Nombre='$nombre',FechaNacimiento='$fechaNac',comitelocal='$data[13]' ,caso='$caso',fechadefuncion='$fdefuncion',fppmuerte='$fppmuerte',idbenefrecepcion='$idbenefrecepcion',mjs='$descripcion_error' ,municipio='$municipio' where idprestacion='$data[4]' and codigoefector='$data[1]'";
        //$errorsql = sql($SQLmU, "Error al actualizar trazadora en temporales", 0) or fin_pagina();

        $SQLmU = "UPDATE trazadoras.mu_tmp SET cuie = '" . $l[1] . "', tipo_doc = '" . $l[7] .
                "', num_doc = " . $l[8] . ", apellido = '" . $l[9] . "', nombre = '" . $l[10] .
                "', fecha_defuncion = '" . Fecha_db($l[48], '1899-12-31') .
                "', fecha_auditoria = '" . Fecha_db($l[13], '1899-12-31') .
                "', fecha_par_int = '" . Fecha_db($l[13], '1899-12-31') . "', fecha_nac = '" .
                Fecha_db($l[11], '1899-12-31') . "', observaciones = '" . $l[58] .
                "', fecha_carga = '" . Fecha_db(date("d/m/Y")) . "', usuario = '" . $_ses_user['id'] .
                "', clase_doc = '" . $l[6] . "', comitelocal = '" . Fecha_db($l[13], '1899-12-31') . "', caso = '" . $l[50] . "', fppmuerte = '" . Fecha_db($l[49], '1899-12-31') . "', id_prestacion = " . $l[4] . ", mjs = '" . $var['descripcion_error'] .
                "', municipio = " . $l[66] . ", clavebeneficiario = '" . $l[5] .
                "' , id_recepcion = " . $var['recepcion_id'] . " where id_prestacion = '$l[4]' and cuie = '$l[1]'";
        $errorsql = sql($SQLmU, "Error al actualizar trazadora temporal", 0) or
                excepcion("Error al actualizar trazadora temporal");
        $var['c_m_tmp'];
    }
}

function insertarTrazadoraTMP(&$l, &$var) {
    if ($l[3] == 1) {
        /* NIÑOS */
        $var['ni']++;
        /* graba beneficiarios */

        $ninios["nino_edad"] = date('d/m/Y', strtotime(date('d-m-Y')) - strtotime($l[11]));
        $ninios["fecha_carga"] = date("d-m-Y");
        $ninios["usuario"] = $_ses_user['id'];

        $SQLn = "INSERT INTO trazadoras.nino_tmp (cuie, clave, clase_doc, tipo_doc, num_doc, apellido, nombre, fecha_nac, fecha_control, peso, talla, percen_peso_edad, percen_talla_edad, perim_cefalico, percen_perim_cefali_edad, imc, percen_peso_talla, triple_viral, nino_edad, observaciones, fecha_carga, usuario, fecha_obito, ncontrolanual, id_prestacion, sexo, municipio, percentilo_imc, discapacitado, cod_aldea, descr_aldea, calle, num_calle, barrio, cod_nomenclador, mjs, id_recepcion) VALUES ('" .
                $l[2] . "','" . $l[5] . "','" . $l[6] . "','" . $l[7] . "'," . $l[8] . ",'" . $l[9] .
                "','" . $l[10] . "','" . Fecha_db($l[11], '1899-12-31') . "','" . Fecha_db($l[13], '1899-12-31') . "'," . $l[30] . "," . $l[32] . ",'" . $l[31] . "','" . $l[33] .
                "'," . $l[34] . ",'" . $l[35] . "','" . $l[68] . "','" . $l[36] . "','" .
                Fecha_db($l[37], '1899-12-31') . "'," . $ninios["nino_edad"] . ",'" . $l[58] .
                "','" . Fecha_db($ninios["fecha_carga"], '1899-12-31') . "','" . $ninios["usuario"] .
                "','" . Fecha_db($l[38], '1899-12-31') . "'," . $l[39] . "," . $l[4] . ",'" . $l[65] .
                "'," . $l[66] . ",'" . $l[67] . "','" . $l[69] . "','" . $l[70] . "','" . $l[71] .
                "','" . $l[72] . "','" . $l[73] . "','" . $l[74] . "','" . $l[12] . "','" . $var['descripcion_error'] .
                "', " . $var['recepcion_id'] . ")";

        sql($SQLn, "Error al insertar trazadora temporal", 0) or excepcion("Error al insertar trazadora temporal");
        $var['c_n_tmp']++;
    }
    if ($l[3] == 2) {
        /* EMBARAZADAS */
        $var['em']++;
        /* graba beneficiarios */

        $embarazada["fecha_carga"] = date("d/m/Y");
        $embarazada["usuario"] = $_ses_user['id'];

        $SQLe = "INSERT INTO trazadoras.embarazadas_tmp (cuie, clave, tipo_doc, num_doc, apellido, nombre, fecha_control, sem_gestacion, fum, fpp, fpcp, fecha_carga, usuario, antitetanica, vdrl, estado_nutricional, antitetanica_primera_dosis, antitetanica_segunda_dosis, hiv, eco, fecha_obito, nro_control_actual, tension_arterial_maxima, tension_arterial_minima, altura_uterina, peso_embarazada, vdrl_fecha, hiv_fecha, municipio, discapacitado, fecha_nacimiento, id_prestacion, mjs, id_recepcion) VALUES ('" .
                $l[1] . "','" . $l[5] . "','" . $l[7] . "'," . $l[8] . ",'" . $l[9] . "','" . $l[10] .
                "','" . Fecha_db($l[13], '1899-12-31') . "'," . $l[19] . ",'" . Fecha_db($l[29], '1899-12-31') . "','" . Fecha_db($l[28], '1899-12-31') . "','" . Fecha_db($l[13], '1899-12-31') . "','" . Fecha_db($embarazada["fecha_carga"], '1899-12-31') .
                "','" . $embarazada["usuario"] . "','" . $l[51] . "','" . Fecha_db($l[23], '1899-12-31') . "','" . $l[20] . "','" . Fecha_db($l[21], '1899-12-31') . "','" .
                Fecha_db($l[22], '1899-12-31') . "','" . Fecha_db($l[24], '1899-12-31') . "','" .
                Fecha_db($l[25], '1899-12-31') . "','" . Fecha_db($l[26], '1899-12-31') . "'," .
                $l[27] . "," . $l[59] . "," . $l[60] . "," . $l[61] . "," . $l[64] . ",'" .
                Fecha_db($l[23], '1899-12-31') . "','" . Fecha_db($l[24], '1899-12-31') . "'," .
                $l[66] . ",'" . $l[69] . "','" . Fecha_db($l[11], '1899-12-31') . "'," . $l[4] .
                ",'" . $var['descripcion_error'] . "', " . $var['recepcion_id'] . ")";

        sql($SQLe, "Error al insertar trazadora temporal", 0) or excepcion("Error al insertar trazadora temporal");
        $var['c_e_tmp']++;
    }
    if ($l[3] == 3) {
        /* PARTOS */
        $var['pa']++;
        /* graba beneficiarios */

        $parto["fecha_carga"] = date("d/m/Y");
        $parto["usuario"] = $_ses_user['id'];

        $SQLp = "INSERT INTO trazadoras.partos_tmp (cuie, clave, tipo_doc, num_doc, apellido, nombre, fecha_parto, apgar, peso, vdrl, antitetanica, fecha_conserjeria, observaciones, fecha_carga, usuario, obito_bebe, obito_madre, id_prestacion, obb_desconocido, talla_rn, perimcef_rn, fecha_nacimiento, discapacitado, municipio, mjs, id_recepcion)	VALUES ('" .
                $l[2] . "','" . $l[5] . "','" . $l[7] . "'," . $l[8] . ",'" . $l[9] . "','" . $l[10] .
                "','" . Fecha_db($l[13], '1899-12-31') . "'," . $l[41] . "," . $l[40] . ",'" . $l[42] .
                "','" . $l[43] . "','" . Fecha_db($l[46], '1899-12-31') . "','" . $l[58] . "','" .
                Fecha_db($parto["fecha_carga"], '1899-12-31') . "','" . $parto["usuario"] .
                "','" . Fecha_db($l[44], '1899-12-31') . "','" . Fecha_db($l[45], '1899-12-31') .
                "'," . $l[4] . ",'" . $l[47] . "'," . $l[62] . "," . $l[63] . ",'" . Fecha_db($l[11], '1899-12-31') . "','" . $l[69] . "'," . $l[66] . ",'" . $var['descripcion_error'] .
                "', " . $var['recepcion_id'] . ")";

        sql($SQLp, "Error al insertar trazadora temporal", 0) or excepcion("Error al insertar trazadora temporal");
        $var['c_p_tmp']++;
    }
    if ($l[3] == 14) {
        /* Muertes */
        $var['mu']++;

        $SQLm = "insert into trazadoras.mu_tmp (cuie, tipo_doc, num_doc, apellido, nombre, fecha_defuncion, fecha_auditoria, fecha_par_int, fecha_nac, observaciones, fecha_carga, usuario, clase_doc, comitelocal, caso, fppmuerte, id_prestacion, municipio, clavebeneficiario, mjs, id_recepcion) values ('" .
                $l[1] . "', '" . $l[7] . "', " . $l[8] . ", '" . $l[9] . "', '" . $l[10] .
                "', '" . Fecha_db($l[48], '1899-12-31') . "', '" . Fecha_db($l[13], '1899-12-31') .
                "', '" . Fecha_db($l[13], '1899-12-31') . "', '" . Fecha_db($l[11], '1899-12-31') .
                "', '" . $l[58] . "', '" . Fecha_db(date("d/m/Y")) . "', '" . $_ses_user['id'] .
                "', '" . $l[6] . "', '" . Fecha_db($l[13], '1899-12-31') . "', '" . $l[50] .
                "', '" . Fecha_db($l[49], '1899-12-31') . "', " . $l[4] . ", " . $l[66] . ",'" .
                $l[5] . "', '" . $var['descripcion_error'] . "', " . $var['recepcion_id'] . ")";

        sql($SQLm, "Error al insertar trazadora temporal", 0) or excepcion("Error al insertar trazadora temporal");
        $var['c_m_tmp']++;

        //    $SQLmU = "insert into Trzmuertestmp (CodigoEfector,ClaveBeneficiario,ClaseDocumento,TipoDocumento,NumeroDocumento,Apellido,Nombre,
        //FechaNacimiento,comitelocal,caso,fechadefuncion,fppmuerte,idtrazadora,mjs,idbenefrecepcion,idprestacion,sexo,municipio) values ('$data[1]','$claveBeneficiario','$claseDoc','$tipoDoc','$nroDoc','$apellido','$nombre','$fechaNac','$data[13]'
        //,'$caso','$fdefuncion','$fppmuerte','$idtrazadora','$descripcion_error','$idbenefrecepcion','$data[4]','$sexo','$municipio')";
        //
    //    $errorsql = sql($SQLmU, "Error al insertar trazadora en temporales", 0) or
        //      fin_pagina();
    }
}

function obtenerFactura($primera_linea, $mes_nombre) {
    $factura["cuie"] = $primera_linea[1];
    $factura["periodo"] = $primera_linea[2];
    $factura["estado"] = "A";
    $factura["fecha_carga"] = date("d/m/Y");
    $factura["fecha_factura"] = date("d/m/Y", strtotime($primera_linea[5]));
    $factura["mes_fact_d_c"] = $mes_nombre[date("n", strtotime($primera_linea[5]))] .
            " " . date("Y", strtotime($primera_linea[5]));
    $factura["nro_exp"] = $primera_linea[7];
    $factura["online"] = "NO";
    return $factura;
}

function existeFactura($f) {
    $sql = "SELECT nro_fact_offline, nro_exp FROM facturacion.factura WHERE nro_fact_offline = '$f'";
    $result = sql($sql) or excepcion('Error al buscar factura repetida');
    if ($result->RecordCount() > 0) {
        excepcion('El nro de factura: ' . $result->fields['nro_fact_offline'] .
                ' ya existe en el expediente: ' . $result->fields['nro_exp']);
    }
}

function insertarFactura($factura) {
    $f_sql = "INSERT INTO facturacion.factura (cuie, periodo, estado, fecha_carga, fecha_factura, mes_fact_d_c, nro_exp, online, nro_exp_ext, nro_fact_offline, recepcion_id, fecha_entrada) VALUES ('" .
            $factura['cuie'] . "', '" . $factura['periodo'] . "', 'A', '" . Fecha_db($factura["fecha_carga"]) .
            "', '" . Fecha_db($factura["fecha_factura"]) . "', '" . $factura["mes_fact_d_c"] .
            "', '" . $factura["nro_exp"] . "', 'NO', '" . $factura["nro_exp"] . "', '" . $factura["nro_fact_offline"] .
            "', " . $factura["recepcion_id"] . ", '" . Fecha_db($factura["fecha_entrada"]) .
            "' ) RETURNING id_factura";
    $result = sql($f_sql, "", 0) or excepcion('Error al insertar la factura');
    return $result->fields['id_factura'];
}

function excepcion($m) {
    throw new Exception($m);
}

function obtenerIdNomenclador($cod, &$l, &$var) {
    $id_vigencia = obtenerIdVigencia($l);

    $cod_a = explode('-', $cod);
    //$nro_orden = intval($cod_a[1]);
    $codigo = $cod_a[0];
    $codigo1 = $codigo;
    $codigo2 = str_replace(' ', '', $codigo);
    $codigo3 = substr($codigo, 0, 6) . substr($codigo, 7, 1);
    $codigo4 = substr($codigo, 0, 3) . ' ' . substr($codigo, 3);

    //$sql_n = "SELECT id_nomenclador, precio FROM facturacion.nomenclador WHERE codigo IN('$codigo', '$codigo1', '$codigo2', '$codigo3', '$codigo4') AND id_nomenclador_detalle = $id_vigencia"; replace(codnomenclador,' ','')
    $sql_n = "SELECT id_nomenclador, precio, categoria FROM facturacion.nomenclador 
  WHERE replace(codigo,' ','') = '$codigo2' AND id_nomenclador_detalle = $id_vigencia";
    $result_n = sql($sql_n, "No se encuentra nomenclador con cod: $cod_nom", 0);
    if ($result_n->RowCount() > 0) {
        $id_nomenclador = $result_n->fields['id_nomenclador'];
        $var['categoria_nomenclador'] = $result_n->fields['categoria'];
    } else {
        $sql_n = "SELECT id_nomenclador, precio FROM facturacion.nomenclador 
        WHERE replace(codigo,' ','') = '$codigo2'";
        $result_n = sql($sql_n, "No se encuentra nomenclador con cod: $codigo1", 0);
        if ($result_n->RowCount() > 0) {
            excepcion('Rechazado por Código de Nomenclador no vigente en la línea ' . $var['row']);
        }
        $var['error_datos'] = 'si';
        $var['mjs_error_datos'] .= ',CodNomenclador';
        $l[12] = 'M 999';
        excepcion('Rechazado por Código de Nomenclador no existente en la línea ' . $var['row'] .
                "($cod)");
        //echo 'xxxxxxxxxxxxxxxxxxxxxxxx   ' . $cod_nom2 . '   xxxxxxxxxxxxxxxxxxxxxxxx';
        $id_nomenclador = -1;
        //return array(-1, -1, -1);
    }
    return $id_nomenclador;
}

function obtenerTablaTrazadora($tipo_informe) {
    switch ($tipo_informe) {
        case 1:
            return "nino";
            break;
        case 2:
            return "embarazadas";
            break;
        case 3:
            return "partos";
            break;
        case 14:
            return "mu";
            break;
    }
    return false;
}

function insertarComprobante(&$l, &$var, $c) {
    /* graba beneficiarios */
    if ($var['existe_id'] == 'no' && $var['error_datos'] == 'no') {
        $var['cuenta_procesado']++;
        $sql_c = "INSERT INTO facturacion.comprobante (cuie, id_factura, nombre_medico, fecha_comprobante, clavebeneficiario, id_smiafiliados, fecha_carga, periodo, id_servicio, activo, idvacuna, idprestacion) VALUES ('" .
                $c["cuie"] . "', " . $c["id_factura"] . ", '" . $c["nombre_medico"] . "', '" .
                Fecha_db($c["fecha_comprobante"]) . "', '" . $c["clave_beneficiario"] . "', " .
                $c["id_smiafiliado"] . ", '" . Fecha_db($c["fecha_carga"]) . "', '" . $c["periodo"] .
                "', " . $c["id_servicio"] . ", '" . $c["activo"] . "', " . $c["idvacuna"] . ", " .
                $l[4] . " ) RETURNING id_comprobante";

        $result = sql($sql_c, "Error al insertar comprobante", 0) or excepcion("Error al insertar comprobante");
        $var['idbenefrecepcion'] = $result->fields['id_comprobante'];
    }

    /////////ERRORES///////
    if ($var['existe_id'] == 'si') {
        $var['cuenta_procesado']++;

        $sql_c = "INSERT INTO facturacion.comprobante (cuie, id_factura, nombre_medico, fecha_comprobante, clavebeneficiario, id_smiafiliados, fecha_carga, periodo, id_servicio, activo, idvacuna, mensaje, fila) VALUES ('" .
                $c["cuie"] . "', " . $c["id_factura"] . ", '" . $c["nombre_medico"] . "', '" .
                Fecha_db($c["fecha_comprobante"]) . "', '" . Fecha_db($c["fecha_comprobante"]) .
                "', " . $c["id_smiafiliado"] . ", '" . Fecha_db($c["fecha_carga"]) . "', '" . $c["periodo"] .
                "', " . $c["id_servicio"] . ", '" . $c["activo"] . "', " . $c["idvacuna"] .
                ", '" . $var['mjs_id'] . "', '" . $var['row'] . "' ) RETURNING id_comprobante";

        $result = sql($sql_c, "Error al insertar comprobante", 0) or excepcion("Error al insertar comprobante");
        $var['idbenefrecepcion'] = $result->fields['id_comprobante'];
        $var['error_datos'] = 'no';
    }
    /* ERROR GENERAL */
    if ($var['error_datos'] == 'si') {
        $var['cuenta_procesado']++;

        $sql_c = "INSERT INTO facturacion.comprobante (cuie, id_factura, nombre_medico, fecha_comprobante, clavebeneficiario, id_smiafiliados, fecha_carga, periodo, id_servicio, activo, idvacuna, mensaje, fila) VALUES ('" .
                $c["cuie"] . "', " . $c["id_factura"] . ", '" . $c["nombre_medico"] . "', '" .
                Fecha_db($c["fecha_comprobante"]) . "', '" . Fecha_db($c["fecha_comprobante"]) .
                "', " . $c["id_smiafiliado"] . ", '" . Fecha_db($c["fecha_carga"]) . "', '" . $c["periodo"] .
                "', " . $c["id_servicio"] . ", '" . $c["activo"] . "', " . $c["idvacuna"] .
                ", '" . $var['mjs_id'] . "', '" . $var['row'] . "' ) RETURNING id_comprobante";

        $result = sql($sql_c, "Error al insertar comprobante", 0) or excepcion("Error al insertar comprobante");
        $var['idbenefrecepcion'] = $result->fields['id_comprobante'];
        $var['existe_id'] = 'no';
    }

    return $var['idbenefrecepcion'];
}

function determinarDebito(&$l, &$var, &$dato_convenio) {

    /////REALIZA LA COMPARACION CON SMIAFILIADO PARA DETERMINAR LOS DEBITOS CUANDO TIENE CLAVE DE BENEFICIARIO


    $categoria_nomencla = $var['categoria_nomenclador'];
    $claseDoc = $l[6];
    $debitoFinan1 = 0;
    //$x=fopen("archivos.txt","w");   $as='**'.$dato_convenio['fecha_comp_ges'].'**'.$dato_convenio['fecha_fin_comp_ges'].'**'.Fecha_db($l[13]);   if($x)    {     fwrite($x,$as);    }
    if ($dato_convenio['fecha_comp_ges'] < Fecha_db($l[13]) && $dato_convenio['fecha_fin_comp_ges'] > Fecha_db($l[13])) {
        $SQLc = "SELECT cast(fechainscripcion as date), case when  ultimaejecucion is null and Activo='N' then cast(fechainscripcion as date) else cast(ultimaejecucion as date) end as ultimaejecucion, rtrim(clavebeneficiario)clavebeneficiario,afiapellido,afinombre,afidni,mensajebaja,afitipodoc,afifechanac as fechana, aficlasedoc,activo,mensajebaja,motivobaja,afitipocategoria,manrodocumento FROM nacer.smiafiliados c left join nacer.smiprocesobajaautomatica d on c.id_procesobajaautomatica=d.id_procbajaautomatica WHERE (clavebeneficiario = '" .
                $l[5] . "' and afifechanac='" . Fecha_db($l[11]) .
                "' and aficlasedoc='A ') or (clavebeneficiario = '" . $l[5] . "' and afidni='" .
                $l[8] . "' and aficlasedoc='P ')";
        $result = sql($SQLc) or excepcion('Error al consultar smiprocesobajaautomatica');
        if ($result->RecordCount() > 0) {

            $result->MoveFirst();
            while (!$result->EOF) {
                //$edad_dias = $result->fields['edad_dias'];
                $fechainscripcion = $result->fields['fechainscripcion'];
                $ultimaejecucion = $result->fields['ultimaejecucion'];
                $fechaNac1 = $result->fields['fechana'];
                $apellido1 = $result->fields['afiapellido'];
                $nombre1 = $result->fields['afinombre'];
                $tipoDoc1 = $result->fields['afitipodoc'];
                $nroDoc1 = $result->fields['afidni'];
                $clavebeneficiario1 = $result->fields['clavebeneficiario'];
                //list($dia1, $mes1, $ano1) = explode('/', $fechaNac);
                //$nacimiento = $ano1 . $mes1 . $dia1;
                //$menor = $l[13] - $nacimiento;
                $MjsBaja1 = '';
                $menbajacertFinan1 = '';
                $debitoFinan1 = 0;

                if ($categoria_nomencla != '2') {
                    if (($result->fields['afitipocategoria'] == '2' || $result->fields['afitipocategoria'] ==
                            '1') && $categoria_nomencla != '0') {
                        $MjsBaja1 = '';
                        $menbajacertFinan1 =
                                'La categoria del beneficiario no corresponde con la prestacion realizada';
                        $debitoFinan1 = 1;
                        $var['motivo_debito'] = 42;
                    } else {
                        if (($result->fields['afitipocategoria'] == '3' || $result->fields['afitipocategoria'] ==
                                '4') && $categoria_nomencla != '1') {
                            $MjsBaja1 = '';
                            $menbajacertFinan1 =
                                    'La categoria del beneficiario no corresponde con la prestacion realizada';
                            $debitoFinan1 = 1;
                            $var['motivo_debito'] = 42;
                        } else {
                            if (rtrim($result->fields['activo']) == 'N') { ///si esta pasivo
                                $MjsBaja1 = $result->fields['mensajebaja'];
                                $debitoFinan1 = 1;
                                //$var['motivo_debito'] = ;
                                $menbajacertFinan1 = $result->fields['mensajebaja'];
                                list($dia1, $mes1, $ano1) = explode('/', $l[13]);
                                $fechaPrestacion1 = $ano1 . $mes1 . $dia1;
                                list($dia2, $mes2, $ano2) = explode('/', $ultimaejecucion);
                                $ultimaejecucion = $ano2 . $mes2 . $dia2;
                                $totalfecha = $ultimaejecucion - $fechaPrestacion1;
                                if ($totalfecha > 0) {
                                    $MjsBaja1 = '';
                                    $menbajacertFinan1 = '';
                                    $debitoFinan1 = 0;
                                }
                            }
                        }
                    }
                } else {

                    $MjsBaja1 = '';
                    $menbajacertFinan1 = '';
                    $debitoFinan1 = 0;
                    if (rtrim($result->fields['activo']) == 'N') { ///si esta pasivo
                        $MjsBaja1 = $result->fields['mensajebaja'];
                        $debitoFinan1 = 1;
                        //$var['motivo_debito'] = ;
                        $menbajacertFinan1 = $result->fields['mensajebaja'];
                        list($dia1, $mes1, $ano1) = explode('/', $fechaPrestacion);
                        $fechaPrestacion1 = $ano1 . $mes1 . $dia1;
                        list($dia2, $mes2, $ano2) = explode('/', $ultimaejecucion);
                        $ultimaejecucion = $ano2 . $mes2 . $dia2;
                        $totalfecha = $ultimaejecucion - $fechaPrestacion1;
                        if ($totalfecha > 0) {
                            $MjsBaja1 = '';
                            $menbajacertFinan1 = '';
                            $debitoFinan1 = 0;
                        }
                    }
                } ///fin del si esta pasivo
                if (strtoupper($claseDoc) == 'A' && $nroDoc != $result->fields['afidni'] && $nroDoc !=
                        $result->fields['manrodocumento'] && $nroDoc != $result->fields['panrodocumento'] &&
                        $nroDoc != $result->fields['otronrodocumento']) {

                    $MjsBaja1 = '';
                    $menbajacertFinan1 = 'DNI no corresponde con la clave de beneficiario';
                    $var['motivo_debito'] = 50;
                    $debitoFinan1 = 1;
                }
                $result->MoveNext();
            }
            $edad_dias = diferencia_dias_m($l[11], $l[13]);

            if ($edad_dias >= 365 and strtoupper($claseDoc) == 'A') {
                $MjsBaja1 = '';
                $menbajacertFinan1 = 'Niño mayor de 1 año sin documento propio';
                $var['motivo_debito'] = 63;
                $debitoFinan1 = 1;
            }
        } else { /////si la clave de  beneficiario no se encuentra.
            if (strtoupper($claseDoc) == 'A') {
                $MjsBaja1 = '';
                $menbajacertFinan1 = 'Clave de Beneficiario no corresponde con dni';
                $var['motivo_debito'] = 50;
                $debitoFinan1 = 1;
            }
            if (strtoupper($claseDoc) == 'P') {
                $SQL465456 = "SELECT activo FROM nacer.smiafiliados
					WHERE clavebeneficiario = '$clavebeneficiario' or afidni='$nroDoc' ";

                $result125125 = sql($SQL465456) or excepcion('Error al consultar si afiliado es activo');
                if ($result125125->RecordCount() > 0) {

                    $MjsBaja1 = '';
                    $menbajacertFinan1 = 'DNI NO CORRESPONDE CON LA CLAVE DE BENEFICIARIO';
                    $var['motivo_debito'] = 50;
                    $debitoFinan1 = 1;
                } else {
                    $MjsBaja1 = '';
                    $menbajacertFinan1 = 'NO ESTA INSCRIPTO EN PLAN NACER';
                    $var['motivo_debito'] = 18;
                    $debitoFinan1 = 1;
                }
                //mssql_free_result($result125125);
            }
        }
    } else {
        $MjsBaja1 = '';
        $menbajacertFinan1 = 'Efector sin convenio al momento de la prestacion.';
        $var['motivo_debito'] = 68;
        $debitoFinan1 = 1;
    }
    //mssql_free_result($result);

    /* fin trz */

    if ($debitoFinan1 == 1) {
        $e_sql = "select id_debito from facturacion.debito where id_factura=" . $var['id_factura'] . " and documento_deb='" . $l[8] . "' and codigo_deb='" . $l[12] . "'";
        $e_busqueda = sql($e_sql, "Error al consultar debito") or excepcion("Error al consultar debito", 0);
        if ($e_busqueda->RecordCount() == 0) {

            list($id_nomenclador, $precio_prestacion, $id_anexo) =
                    obtenerDatosPorNomenclador($l[12], $l, $var);
            $SQLbenef = "insert into facturacion.debito (id_factura, id_nomenclador, cantidad, 
			id_motivo_d, monto, documento_deb, apellido_deb, nombre_deb, codigo_deb,
			observaciones_deb, mensaje_baja) values (" .
                    $var['id_factura'] . ", " . $var['id_nomenclador'] . ", 1, " . $var['motivo_debito'] .
                    ", " . $precio_prestacion . ", '" . $l[8] . "', '" . $l[9] . "', '" . $l[10] .
                    "', '" . $l[12] . "', '" . $l[58] . "', '$MjsBaja1')";
            /////////////////////////////////////////error/////////////////////////////////
            sql($SQLbenef, "Error al insertar débito", 0) or excepcion("Error al insertar débito");
        }
    }
}

function insertarInformado(&$l, &$var) {

    //$SQL = "insert into facturacion.informados (idRecepcion,cuie,idPrestacion,claveBeneficiario,codNomenclador,tipoDoc,nroDoc,nombre,apellido,fechaNac,fechaactual,idvacuna,idtaller,km,origen,destino,clavemadre,sexo,municipio,semgesta,discapacitado,clasedoc) values ('$idRecepcion','$data[1]','$data[4]','$claveBeneficiario','$data[12]','$tipoDoc','$nroDoc','$nombre','$apellido','$fechaNac','$data[13]','$idvacuna','$idtaller','$km','$origen','$destino','$clavemadre','$sexo','$municipio','$semgesta','$discapacitado','$claseDoc')";

    if ($l[15] == null || $l[15] == '') {
        $l[15] = 0;
    }

    $SQL = "insert into facturacion.informados (idrecepcion, cuie, idprestacion, clavebeneficiario, codnomenclador, tipodoc, nrodoc, nombre, apellido, fechanac, fechaactual,  idvacuna, idtaller, km, origen, destino, clavemadre, sexo, municipio, semgesta, discapacitado, clasedoc) values (" .
            $var['recepcion_id'] . ", '" . $l[1] . "', " . $l[4] . ", '" . $l[5] . "', '" .
            $l[12] . "', '" . $l[7] . "', '" . $l[8] . "', '" . $l[10] . "', '" . $l[9] .
            "', '" . Fecha_db($l[11], '1899-12-31') . "', '" . Fecha_db($l[13], '1899-12-31') .
            "', " . $var['idvacuna'] . ", " . $l[15] . ", " . $l[15] . ", '" . $l[16] .
            "', '" . $l[17] . "', '" . $l[18] . "', '" . $l[65] . "', " . $l[66] . ", " . $l[53] .
            ", '" . $l[69] . "', '" . $l[6] . "')";

    sql($SQL, 'Error al insertar informado', 0) or excepcion('Error al insertar informado');
}

function calcular_limite_fecha_prestacion($mes_vig, $ano_vig) {
    $fcierre = '01/' . $mes_vig . '/' . $ano_vig;
    if ($mes_vig == '12') {
        $mes_vig1 = '01';
        $ano_vig1 = $ano_vig + 1;
    }
    if ($mes_vig != '12') {
        $mes_vig1 = $mes_vig + 1;
        $ano_vig1 = $ano_vig;
        if ($mes_vig1 < 10) {
            $mes_vig1 = '0' . $mes_vig1;
        }
    }
    return '10/' . $mes_vig1 . '/' . $ano_vig1;
}

function validarFormularioRecepcion($post, &$var) {
    //validar codigo organizacion
    if (!es_numero($post["cod_org"])) {
        $var['error_formulario'] = "Código de Organización no válido";
        return false;
    }
    //validar nro correlativo
    if (!es_numero($post["no_correlativo"])) {
        $var['error_formulario'] = "Número correlativo no válido";
        return false;
    }
    //validar año
    if (!es_numero($post["ano_exp"])) {
        $var['error_formulario'] = "Año no válido";
        return false;
    }
    //validar cuerpo
    if (!es_numero($post["cuerpo"])) {
        $var['error_formulario'] = "Cuerpo no válido";
        return false;
    }
    //validar fecha de entrada
    if (!FechaOk($post["fecha_entrada"])) {
        $var['error_formulario'] = "Fecha de entrada no válida";
        return false;
    }
    return true;
}

?>