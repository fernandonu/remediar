<?

require_once ("../../config.php");

require_once ("../../lib/funciones_misiones.php");

extract($_GET, EXTR_SKIP);
if ($parametros) extract($parametros, EXTR_OVERWRITE);

echo ' PROCESANDO CONTROL......ESTA OPERACION PUEDE TARDAR VARIOS MINUTOS';
echo ' AGUARDE POR FAVOR!!!';  

try {

  sql("BEGIN");

  $queryfunciones = "SELECT COUNT(*) AS cant
		     FROM facturacion.efec_nom
                     WHERE UPPER(efec_nom.cuie)=UPPER('$cuie')";
  
  $res_fun = sql($queryfunciones) or fin_pagina();
  if ($res_fun->fields['cant'] > 0) {
      
    //busca todas las prestaciones habilitadas para el efector   
    $cod_permiso = "(";
    $sql = "SELECT UPPER(codigo) AS codigo
            FROM facturacion.efec_nom
	    WHERE UPPER(efec_nom.cuie)=UPPER('$cuie')";
    
    $res_sql = sql($sql) or fin_pagina();
    while (!$res_sql->EOF) {
      $cod_permiso .= "'" . $res_sql->fields['codigo'] . "',";
      $res_sql->movenext();
    }
    $cod_permiso .= ")";
    $cod_permiso = str_replace(",)", ")", $cod_permiso);
    
    //controla que si hay prestaciones facturadas que no estan habilitadas al efector
    //FALTA LO MISMO PERO PARA EL NUEVO NOMENCLADOR
    $sql3 = " SELECT  prestacion.id_nomenclador,precio,smiafiliados.afidni,smiafiliados.afiapellido,smiafiliados.afinombre,codigo
	      FROM facturacion.factura
              INNER JOIN facturacion.comprobante ON factura.id_factura=comprobante.id_factura
              INNER JOIN facturacion.prestacion ON comprobante.id_comprobante=prestacion.id_comprobante
              INNER JOIN nacer.smiafiliados ON smiafiliados.id_smiafiliados=comprobante.id_smiafiliados
              INNER JOIN facturacion.nomenclador ON nomenclador.id_nomenclador=prestacion.id_nomenclador
              WHERE factura.id_factura=$id_factura";
    $sql3 .= " AND NOT EXISTS(SELECT id_debito FROM facturacion.debito WHERE id_factura=$id_factura AND documento_deb=smiafiliados.afidni AND codigo_deb=codigo)";
    $sql3 .= " AND codigo NOT IN " . $cod_permiso;
    
 
    
    $res_sql3 = sql($sql3) or fin_pagina();
    while (!$res_sql3->EOF) {
      //genera debito por prestacion no habilitada al efector  
      $SQLbenef = "INSERT INTO facturacion.debito 
                          (id_factura,id_nomenclador,cantidad,id_motivo_d,monto,documento_deb,apellido_deb,nombre_deb, codigo_deb,observaciones_deb,mensaje_baja)
		   VALUES (" . $id_factura . "," . $res_sql3->fields['id_nomenclador'] .
                           ", 1, 61, " . $res_sql3->fields['precio'] . ", '" . $res_sql3->fields['afidni'] .
                           "', '" . $res_sql3->fields['afiapellido'] . "', '" . $res_sql3->fields['afinombre'] .
                           "', '" . $res_sql3->fields['codigo'] . "', '', 'No puede Facturar este código')";

      sql($SQLbenef, "Error al insertar débito", 0) or excepcion("Error al insertar débito");
      $res_sql3->movenext();
    }
  }
  
  //SELECCIONA LOS DATOS DE LA FACTURA QUE NO TENGAN DEBITOS
  //FALTA LO MISMO PERO QUE INCLUYA AL NOMENCLADOR NUEVO
  $sql3 = " SELECT prestacion.id_nomenclador,precio,smiafiliados.afidni,
                   smiafiliados.afiapellido,smiafiliados.afinombre,codigo,
                   CASE WHEN UPPER(SUBSTR(nombrearchivo,16,1))='R' AND informados.cuie IS NULL THEN 1 ELSE 0 END noinformado,
	           CASE WHEN DATE(DATE_PART('year',fecha_entrada)||'-'||(DATE_PART('month', fecha_entrada))||
                   '-'||01)-(30*6)>comprobante.fecha_comprobante THEN 1 ELSE 0 END supera6antiguedad,
		   informados.cuie,UPPER(SUBSTR(nombrearchivo,16,1)),fecha_entrada,
                   comprobante.fecha_comprobante,((comprobante.fecha_comprobante-smiafiliados.afifechanac)/30) AS diasvida
            FROM facturacion.factura
            INNER JOIN facturacion.comprobante ON factura.id_factura=comprobante.id_factura
            INNER JOIN facturacion.prestacion ON comprobante.id_comprobante=prestacion.id_comprobante
            INNER JOIN nacer.smiafiliados ON smiafiliados.id_smiafiliados=comprobante.id_smiafiliados
            INNER JOIN facturacion.nomenclador ON nomenclador.id_nomenclador=prestacion.id_nomenclador
            INNER JOIN facturacion.recepcion ON recepcion.idrecepcion=factura.recepcion_id
            LEFT JOIN facturacion.informados ON informados.cuie=factura.cuie and informados.idprestacion=comprobante.idprestacion 
                                                AND informados.fechaactual=comprobante.fecha_comprobante and informados.idrecepcion<>factura.recepcion_id
            WHERE factura.id_factura=$id_factura";
  $sql3 .= " AND NOT EXISTS(SELECT id_debito FROM facturacion.debito WHERE id_factura=$id_factura AND documento_deb=smiafiliados.afidni AND UPPER(codigo_deb)=UPPER(codigo))";

  /* Se puede refacturar???
    and exists(SELECT a.apellido
    FROM [20benefprestacion]a
    inner join [20benefrecepcionipos]b on a.idbenefrecepcion=b.idbenefrecepcion
    WHERE  a.nrodoc=@nrodoc and a.fechaactual=@fechaactual and a.codnomenclador=@codnomenclador
    and idrecepcion<>@idrecepcion)
   */
  $res_sql3 = sql($sql3) or fin_pagina();
  while (!$res_sql3->EOF) {
    $cod_deb = 0;
    //
    if ($res_sql3->fields['noinformado'] == 1) {
      $cod_deb = 64;
      $mjs = 'Prestacion no informada oportunamente';
    }
    //LA PRESTACION TIENE MAS DE 6 MESES DE REALIZADA
    if ($res_sql3->fields['supera6antiguedad'] == 1) {
      $cod_deb = 66;
      $mjs = 'Prestacion posee mas de 6 meses de antiguedad';
    }
    
    //CONTROLA SI TIENE REGISTRO DE VACUNAS CUANDO ES CONSULTA NIÑOS ENTRE 1 Y 6 AÑOS
    if (($res_sql3->fields['codigo'] == 'npe 32' || $res_sql3->fields['codigo'] ==
      'npe 33') && $res_sql3->fields['diasvida'] > 390) {
      $querympe1 = "SELECT * FROM trazadoras.nino_new 
		    WHERE num_doc=" . $res_sql3->fields['afidni'] .
                          " AND triple_viral<>'1899-12-31' AND triple_viral IS NOT NULL";
      $res_funmpe1 = sql($querympe1) or fin_pagina();
      $querympe2 = "SELECT * FROM trazadoras.trz_antisarampionosa 
		    WHERE numero_documento=" . $res_sql3->fields['afidni'] .
                        " AND fecha_vacunacion<>'1899-12-31' AND fecha_vacunacion IS NOT NULL";
      $res_funmpe2 = sql($querympe2) or fin_pagina();

      if ($res_funmpe1->recordcount() == 0 && $res_funmpe2->recordcount() == 0) {
        $cod_deb = 69;
        $mjs = 'Niño mayor de 1 año sin vacuna';
      }
    }
    
    //REGISTRA EL DEBITO 
    if ($cod_deb != 0) {
      $SQLbenef = "INSERT INTO facturacion.debito 
                        (id_factura, id_nomenclador, cantidad, id_motivo_d, monto, documento_deb, apellido_deb, nombre_deb, codigo_deb, observaciones_deb, mensaje_baja)
		   VALUES (" . $id_factura . "," . $res_sql3->fields['id_nomenclador'] .
                        ", 1, " . $cod_deb . ", " . $res_sql3->fields['precio'] . ", '" . $res_sql3->
                        fields['afidni'] . "', '" . $res_sql3->fields['afiapellido'] . "', '" . $res_sql3->
                        fields['afinombre'] . "', '" . $res_sql3->fields['codigo'] . "', '', '$mjs')";
      /////////////////////////////////////////error/////////////////////////////////
      sql($SQLbenef, "Error al insertar débito", 0) or excepcion("Error al insertar débito");
    }
    $res_sql3->movenext();
  }
  //
  //echo ' <SCRIPT Language="Javascript">
  //		location.href="controles_2.php?id_factura=' . $id_factura . '"
  //				</SCRIPT>';
?>
<?

//require_once ("../../config.php");
//
//require_once("../../lib/funciones_misiones.php");
//
//extract($_POST, EXTR_SKIP);
//if ($parametros)
//  extract($parametros, EXTR_OVERWRITE);
//
//echo ' PROCESANDO CONTROL......ESTA OPERACION PUEDE TARDAR VARIOS MINUTOS....AGUARDE2!!!';

/* 1_INICIO DE LOS CONTROL */

$sql3 = " SELECT  prestacion.id_nomenclador,precio,smiafiliados.afidni,smiafiliados.afiapellido,smiafiliados.afinombre 
            ,nomenclador.codigo,comprobante.id_smiafiliados,factura.cuie,comprobante.clavebeneficiario,prestacion.id_prestacion
            ,validacion_prestacion_mns.control,validacion_prestacion_mns.periodicidad,validacion_prestacion_mns.maxefector,validacion_prestacion_mns.maxprovincial
            ,validacion_prestacion_mns.tipope,validacion_prestacion_mns.tipoef,validacion_prestacion_mns.tipopr
            ,comprobante.fecha_comprobante, DATE_PART('month',comprobante.fecha_comprobante) AS mes,DATE_PART('year',comprobante.fecha_comprobante) AS ano
            --,convert(nvarchar,fechacontrolprenatal,103) AS primerctrl
	FROM facturacion.factura
	INNER JOIN facturacion.comprobante ON factura.id_factura=comprobante.id_factura
	INNER JOIN facturacion.prestacion ON comprobante.id_comprobante=prestacion.id_comprobante
	INNER JOIN nacer.smiafiliados ON smiafiliados.id_smiafiliados=comprobante.id_smiafiliados
	INNER JOIN facturacion.nomenclador ON nomenclador.id_nomenclador=prestacion.id_nomenclador
	LEFT JOIN facturacion.validacion_prestacion_mns ON trim(nomenclador.codigo)=trim(validacion_prestacion_mns.codnomenclador)
	WHERE factura.id_factura=$id_factura";
$sql3 .= " AND NOT EXISTS(select id_debito from facturacion.debito where id_factura=$id_factura and documento_deb=smiafiliados.afidni and codigo_deb=codigo)";
$sql3 .= " validacion_prestacion_mns.control in ('provincial','efector')";
/* 	 $controlcual=  "SELECT a.idBenefPrestacion,a.claveBeneficiario,a.tipoDoc,a.nroDoc,a.apellido,a.nombre,a.codNomenclador,
  CONVERT ( varchar ( 10 ), a.fechaActual ,103 ) as fechaActual,b.cuieEfector,a.idbenefrecepcion,x.control,x.periodicidad,
  x.maxefector,x.maxprovincial,tipope,tipoef,tipopr,month(a.fechaActual) as mes,year(a.fechaActual) as ano,
  convert(nvarchar,fechacontrolprenatal,103) as primerctrl
  FROM [20BEnefPrestacion]a INNER JOIN [20EfectoresInforme] b ON a.idEfectorInforme=b.idEfectorInforme
  INNER JOIN [20Nomencladores] x on x.codnomenclador=a.codnomenclador
  left JOIN trzembarazadas r ON a.idbenefrecepcion=r.idbenefrecepcion
  WHERE ((idCaratula='$idc') and (x.control='provincial' or  x.control='efector') and (a.debitoFinan='0' and a.debitoMedi='0'))
  group by a.idBenefPrestacion,a.claveBeneficiario,a.tipoDoc,a.nroDoc,a.apellido,a.nombre,a.codNomenclador,
  CONVERT ( varchar ( 10 ), a.fechaActual ,103),b.cuieEfector,a.idbenefrecepcion,x.control,x.periodicidad,x.maxefector,
  x.maxprovincial,tipope,tipoef,tipopr,datepart(month,a.fechaActual),datepart(year,a.fechaActual),convert(nvarchar,fechacontrolprenatal,103)"; */

$res_sql3 = sql($sql3) or fin_pagina();
while (!$res_sql3->EOF) {
  $consulta1 = "";
  $consulta2 = "";
  $consulta3 = "";
  $mjs_provef = "";
  $cuantos = 0;
  $idBenefPrestacion = $res_sql3->fields['id_prestacion'];
  $idbenefdebito = $idBenefPrestacion;
  $claveBeneficiario = trim($res_sql3->fields['clavebeneficiario']);
  $id_smiafiliados = $res_sql3->fields['id_smiafiliados'];
  $nroDoc = $res_sql3->fields['afidni'];
  $codigo = $res_sql3->fields['codigo'];
  $id_nomenclador = $res_sql3->fields['id_nomenclador'];
  $precio = $res_sql3->fields['precio'];
  $apellido = $res_sql3->fields['afiapellido'];
  $nombre = $res_sql3->fields['afinombre'];
  $codNomenclador_benef = trim($res_sql3->fields['codigo']);
  $fechaActual = $res_sql3->fields['fecha_comprobante'];
  $cuieEfector = $res_sql3->fields['cuie'];
  $control = trim($res_sql3->fields['control']);
  $periodo = trim($res_sql3->fields['periodicidad']);
  $maxefector = trim($res_sql3->fields['maxefector']);
  $maxprovincial = trim($res_sql3->fields['maxprovincial']);
  $tipope = trim($res_sql3->fields['tipope']);
  $tipoef = trim($res_sql3->fields['tipoef']);
  $tipopr = trim($res_sql3->fields['tipopr']);
  $mes = trim($res_sql3->fields['mes']);
  $ano = trim($res_sql3->fields['ano']);
  //$primerctrl=$res_sql3->fields['primerctrl'];
  $debitar = 'n';

  if ($control == 'efector') {
    $q = 'E';
    $consulta1 = "factura.cuie='$cuieEfector' and ";
  }
  if ($control == 'provincial') {
    $q = 'P';
  }

  $maximus = 0;

  if (substr($tipope, 1, 1) != 'c' && $tipope != 'v') {
    $contar = 1;
    $prov = " SELECT  prestacion.id_nomenclador,  comprobante.id_smiafiliados,comprobante.fecha_comprobante,prestacion.id_prestacion
					,factura.nro_fact_offline,factura.id_factura
					FROM facturacion.factura
					inner join facturacion.comprobante on factura.id_factura=comprobante.id_factura
					inner join facturacion.prestacion on comprobante.id_comprobante=prestacion.id_comprobante
					where $consulta1 comprobante.id_smiafiliados=$id_smiafiliados";
    $prov .= " and not exists(select id_debito from facturacion.debito where id_factura=factura.id_factura and documento_deb='$nroDoc' and codigo_deb='$codigo')";
    $prov .= " and prestacion.id_nomenclador='$id_nomenclador'";
    $prov .= " and prestacion.id_prestacion<>$idBenefPrestacion";
    $prov .= " and comprobante.fecha_comprobante>DATE '$fechaActual' - ($periodo*30) and comprobante.fecha_comprobante<DATE '$fechaActual' + ($periodo*30)";

    /* 	$prov=  "SELECT  c.codexpediente,CONVERT ( varchar ( 10 ), a.fechaActual ,103 ) as fechaActual,a.idBenefPrestacion
      ,nrocuerpoexp
      FROM [20BenefPrestacion]a INNER JOIN [20EfectoresInforme] b ON a.idEfectorInforme=b.idEfectorInforme
      INNER JOIN [20CaratulaInforme] c ON b.idCaratula=c.idCaratula
      WHERE $consulta1 ((a.tipoDoc='$tipoDoc' and a.nroDoc='$nroDoc' and a.apellido='$apellido' and a.nombre='$nombre')
      $clave ) and a.idBenefPrestacion<>'$idBenefPrestacion' and
      (a.codNomenclador='$codNomenclador_benef' and a.debitoFinan='0' and a.debitoMedi='0') and
      (a.fechaActual>DATEADD (Month,-$periodo,'$fechaActual') and a.fechaActual<DATEADD (Month,$periodo,'$fechaActual'))
      group by c.codexpediente,CONVERT ( varchar ( 10 ), a.fechaActual ,103 ),a.idBenefPrestacion,nrocuerpoexp"; */

    $res_prov = sql($prov) or fin_pagina();
    while (!$res_prov->EOF) {
      $nro_fact_offline = trim($res_prov->fields['nro_fact_offline']);
      $fechaActual_comp = $res_prov->fields['fecha_comprobante'];
      $idBenefPrestacion_comp = $res_prov->fields['id_prestacion'];
      $contar++;

      $res_prov->movenext();
    }

    if ($contar > 1) {
      $mjs_provef = 'Prestacion no cumple con la periodicidad del control';
      $debitar == 's';
    }

    if ($debitar == 's') {

      $SQLbenef = "insert into facturacion.debito (id_factura, id_nomenclador, cantidad, id_motivo_d, monto, documento_deb, apellido_deb, nombre_deb, codigo_deb, observaciones_deb, mensaje_baja)
				 values (" . $id_factura . "," . $id_nomenclador . ", 1, 65, " . $precio .
        ", '" . $nroDoc . "', '" . $apellido . "', '" . $nombre . "', '" . $codigo .
        "', '', '$mjs_provef')";
      /////////////////////////////////////////error/////////////////////////////////
      sql($SQLbenef, "Error al insertar débito", 0) or excepcion("Error al insertar débito");
    }
  }
  $res_sql3->movenext();
}
/* 1_ FIN DE CONTROL */

$SQLfin = "update facturacion.factura set ctrl='S' where factura.id_factura=$id_factura";
/////////////////////////////////////////error/////////////////////////////////
sql($SQLfin, "Error al marcar control", 0) or excepcion("Error al marcar control");

sql("COMMIT");
} catch (exception $e) {
sql("ROLLBACK", "Error en rollback", 0);
echo "Error: " . $e->getMessage() . "<br /><br /><br />";
echo ' <SCRIPT Language="Javascript">
		alert("Fallo al realizar los Controles Automáticos");
		 window.opener.location.reload();
		 window.close();
		</SCRIPT>';
}

echo ' <SCRIPT Language="Javascript">
		alert("Fin de Controles Automáticos");
		 window.opener.location.reload();
		 window.close();
		</SCRIPT>';
?>