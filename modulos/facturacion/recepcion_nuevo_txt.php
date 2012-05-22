<?php
ob_start();
require_once ("../../config.php");
require_once ("../../lib/funciones_misiones.php");
//echo diferencia_dias_m('12/11/2008','15/11/2008');
//exit;

?>
<html>
  <head>
  	<link rel='icon' href='http://192.168.1.251/nacer/favicon.ico'>
	  <link REL='SHORTCUT ICON' HREF='http://192.168.1.251/nacer/favicon.ico'>
	   <link rel=stylesheet type='text/css' href='/nacer/lib/estilos.css'>
  </head>
  <body background="/nacer/imagenes/fondo.gif" bgcolor="#B7CEC4" >
    <br /><br /><br />
    <table width="469" border="0" align="center" cellpadding="0" cellspacing="0">
      <tr>
        <td id="mo" align="center" style="padding: 5px;font-size: 16px;">
          Resultados de la recepción
        </td>
      </tr>
      <tr>      
<?php
$var = array();

$var['c_i'] = 0; //informados
$var['c_pr'] = 0; //prestaciones
$var['c_e'] = 0; //embarazadas
$var['c_p'] = 0; //partos
$var['c_n'] = 0; //niños
$var['c_m'] = 0; //muertes
$var['c_e_tmp'] = 0; //embarazadas temporal
$var['c_p_tmp'] = 0; //partos temporal
$var['c_n_tmp'] = 0; //niños temporal
$var['c_m_tmp'] = 0; //muertes temporal

$error_types = array(1 =>
  'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
  'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
  'The uploaded file was only partially uploaded.', 'No file was uploaded.', 6 =>
  'Missing a temporary folder.', 'Failed to write file to disk.',
  'A PHP extension stopped the file upload.');

$error_types = array(1 => 'El archivo excede el tamaño máximo permitido.',
  'El archivo excede el tamaño máximo permitido.',
  'El archivo fue subido parcialmente.', 'No se ha subido ningun archivo.', 6 =>
  'No se encuentra la carpeta temporal.', 'Fallo al escribir el archivo a disco.',
  'Una extensión de PHP ha detenido la carga del archivo.');

$mes_nombre = array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
  "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

try {

  sql("BEGIN");

  $cmd = $_POST["enviar"];
  if ($cmd == "Enviar") {
    $tamanio = $_FILES["archivo"]["size"];
    if ($tamanio == 0)
      excepcion('El tamaño del archivo es nulo');

    if (!$_FILES["archivo"])
      excepcion("Debe seleccionar un archivo.");

    //$error .= "Debe seleccionar un archivo.<br>";
    if ($_FILES["archivo"]["error"] != 0)
      echo $error .= $error_types[$_FILES['archivo']['error']];
    if (!$error) {
      //if (is_file(UPLOADS_DIR . "/archivos/" . $_FILES["archivo"]["name"]))
      //        excepcion("El Archivo ya existe.");
      //$error = "El Archivo ya existe.";
      if (!$error)
        if (!move_uploaded_file($_FILES['archivo']['tmp_name'], UPLOADS_DIR .
          "/archivos/" . $_FILES["archivo"]["name"])) {
          //$error = "¡Posible ataque de carga de archivos!";
          excepcion("¡Posible ataque de carga de archivos!");
        }
    }
  }
  if (!$error) {

    if (!validarFormularioRecepcion($_POST, $var))
      excepcion($var['error_formulario']);

    $nombre_archivo = explode('.', $_FILES["archivo"]["name"]);
    //var_dump($nombre_archivo);
    $nombre_archivo = $nombre_archivo[0];
    //var_dump($nombre_archivo);

    $e_cod_org = $_POST["cod_org"];
    $e_no_correlativo = $_POST["no_correlativo"];
    $e_ano_exp = $_POST["ano_exp"];
    $e_cuerpo = $_POST["cuerpo"];
    $e_sql = "SELECT * FROM facturacion.recepcion WHERE nombrearchivo = '$nombre_archivo'";
    $e_busqueda = sql($e_sql, "Error al buscar archivo ya cargado") or excepcion("Error al buscar archivo ya cargado",
      0);
    if ($e_busqueda->RecordCount() > 0) {
      excepcion('El archivo ya está cargado en el sistema.');
    } else {
      $fecha_rec = $fecha_carga = date("Y-m-d");
      $e_sql = "INSERT INTO facturacion.recepcion (nombrearchivo, cod_org, no_correlativo, ano_exp, cuerpo,fecha_rec) VALUES ('$nombre_archivo', $cod_org, $no_correlativo, $ano_exp, $cuerpo,'$fecha_rec') RETURNING idrecepcion";
      $result_recepcion = sql($e_sql, "Error al insertar archivo.", 0) or excepcion('Error al insertar archivo.');
      //$e_sql = "SELECT idrecepcion FROM facturacion.recepcion WHERE nombrearchivo = '$nombre_archivo' AND cod_org = $cod_org AND no_correlativo = $no_correlativo AND ano_exp = $ano_exp AND cuerpo = $cuerpo";
      //$result_recepcion = sql($e_sql, "Error al consultar archivo.") or excepcion('Error al consultar archivo.');
      //var_dump($result_recepcion);
      if ($result_recepcion->RecordCount() > 0) {
        $result_recepcion->movefirst();
        $var['recepcion_id'] = $result_recepcion->fields['idrecepcion'];
      } else {
        excepcion('No se encuentra el archivo.');
      }

    }

  }
  if (!$error) {

    $file = fopen(UPLOADS_DIR . "/archivos/" . $_FILES["archivo"]["name"], 'r');
    while ($datos = fgetcsv($file, 10000, "\t\t")) {
      $lineas[] = explode(';', $datos[0]);
    }
    if ($lineas[0][0] != "M" && substr($nombre_archivo, 0, 1) != 'H') {
      excepcion('Formato de archivo incorrecto');
    }

    if (substr($nombre_archivo, 0, 1) != 'H') {
      //obtener datos nombre de archivo
      if (strlen($nombre_archivo) == 29) {
        $datos_nombre_archivo['cuie'] = substr($nombre_archivo, 0, 6);
        $datos_nombre_archivo['nro_correlativo'] = substr($nombre_archivo, 6, 3);
        $datos_nombre_archivo['periodo_liquidado'] = substr($nombre_archivo, 9, 6);
        $datos_nombre_archivo['periodo_liquidado_mes'] = substr($nombre_archivo, 13, 2);
        $datos_nombre_archivo['periodo_liquidado_anho'] = substr($nombre_archivo, 9, 4);
        $datos_nombre_archivo['tipo_facturacion'] = substr($nombre_archivo, 15, 1);
        $datos_nombre_archivo['vigencia_controlador'] = substr($nombre_archivo, 16, 2);
        $datos_nombre_archivo['nro_factura'] = substr($nombre_archivo, 18, 9);
        $datos_nombre_archivo['nro_vigencia_txt'] = substr($nombre_archivo, 27, 2);

        //      print_r($datos_nombre_archivo); //debug
        //insertarArchivo($nombre_archivo);
      } else {
        throw new Exception("Formato de nombre de archivo incorrecto.");
      }

      if ($datos_nombre_archivo['nro_vigencia_txt'] < 1 || $datos_nombre_archivo['nro_vigencia_txt'] >
        12)
        excepcion('Número de vigencia incorrecta en el nombre del archivo');

      //obtener primera linea
      //$file = fopen(UPLOADS_DIR . "/archivos/" . $_FILES["archivo"]["name"], 'r');
      //$primera_linea = fgetcsv($file, 3000);
      //$primera_linea = explode(';', $primera_linea[0]);
      $primera_linea = $lineas[0];
      $primera_linea[5] = str_replace("/", "-", $primera_linea[5]);
      //print_r($primera_linea); //debug
      //obtener datos para factura
      $factura = obtenerFactura($primera_linea, $mes_nombre);
      $factura["nro_fact_offline"] = substr($datos_nombre_archivo['cuie'], 1, 5) . $datos_nombre_archivo['nro_factura'];
      existeFactura($factura["nro_fact_offline"]);
      $factura["recepcion_id"] = $var['recepcion_id'];
      $factura["fecha_entrada"] = $_POST['fecha_entrada'];
      $var['id_factura'] = insertarFactura($factura);
      //print_r($factura); //debug

      if ($lineas[0][0] == "M" && substr($nombre_archivo, 0, 1) != 'H') {
        //comienza recepcion muerte
        //$SQLmurt = "exec Recepcion_CargaMuertes '$data[1]','$mes_vig','$ano_vig','$data[3]','$data[4]'";
        $sql = "insert into facturacion.muertes (cuie, mes, ano, cantidadt, cantidadok)
values ('" . $lineas[0][1] . "', '" . $datos_nombre_archivo['periodo_liquidado_mes'] .
          "', '" . $datos_nombre_archivo['periodo_liquidado_anho'] . "', " . $lineas[0][3] .
          ", " . $lineas[0][4] . ")";
        sql($sql, "Error al insertar muertes", 0) or excepcion("Error al insertar muertes");
      }

      //Calcular límite fecha de prestación

      //    $mes_vig = $datos_nombre_archivo['periodo_liquidado_mes'];
      //    $ano_vig = $datos_nombre_archivo['periodo_liquidado_anho'];
      $var['fprest_limite'] = calcular_limite_fecha_prestacion($datos_nombre_archivo['periodo_liquidado_mes'],
        $datos_nombre_archivo['periodo_liquidado_anho']);
      //    $fcierre = '01/' . $mes_vig . '/' . $ano_vig;
      //    if ($mes_vig == '12') {
      //      $mes_vig1 = '01';
      //      $ano_vig1 = $ano_vig + 1;
      //    }
      //    if ($mes_vig != '12') {
      //      $mes_vig1 = $mes_vig + 1;
      //      $ano_vig1 = $ano_vig;
      //      if ($mes_vig1 < 10) {
      //        $mes_vig1 = '0' . $mes_vig1;
      //      }
      //    }
      //    $var['fprest_limite'] = '10/' . $mes_vig1 . '/' . $ano_vig1;
      $dato_convenio = fn_dato_convenio($lineas[0][1]);
      //var_dump($lineas[0]);
      unset($lineas[0]);
      $i = 2;

    } else {
      $i = 1;
    }
    $var['q'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $var['user'] = $_SESSION['user'];
    $var['cuenta_procesado'] = 0;
    $var['cuenta_error'] = 0;
    $var['errort'] = 0;
    $var['errorl'] = 0;
    $var['ni'] = 0;
    $var['em'] = 0;
    $var['pa'] = 0;
    $var['contra'] = 0;
    $var['mu'] = 0;
    $var['SQLerror'] = "ROLLBACK";

    if (count($lineas) > 0) {
      $limite_trz = limite_trazadora();

      foreach ($lineas as $l) {

        if ((count($l) != 75 && $i != 1 && substr($nombre_archivo, 0, 1) != 'H') || (substr
          ($nombre_archivo, 0, 1) == 'H' && count($l) != 75)) {
          echo count($l) . " - " . $i . " - " . substr($nombre_archivo, 0, 1);
          print_r($l);
          excepcion('Formato de archivo incorrecto'); //error
        }

        $var['row'] = $i;
        $var['descripcion_error'] = '';
        $var['eliminado'] = 'N';
        $var['ya_esta'] = 'no';
        $var['ya_estaTMP'] = 'no';
        $var['error'] = 'no';
        $var['idvacuna'] = 0;
        $var['error_datos'] = 'no';
        $var['mjs_error_datos'] = '';
        $var['idtaller'] = '';
        $var['km'] = '';
        $var['e_defuncion'] = 'no';
        $var['e_caso'] = 'no';
        $var['ojo'] = 'no';
        $var['existe_id'] = 'no';
        $var['idbenefrecepcion'] = 0;
        //$var['id_motivo'] = 0;
        $var['motivo_debito'] = 18;

        $l = limpiar($l);

        prepararDatosComprobante($l, $var);

        if ($l[0] == 'L') {
          verificarValidezFechaPrestacion($l, $var);
          procesarLineaLiquidacion($l, $var);
          $var['id_nomenclador'] = obtenerIdNomenclador($l[12], $l, $var);
          determinarDebito($l, $var, $dato_convenio);

          $comprobante = obtenerComprobante($l, $var);
          $comprobante["id_factura"] = $var['id_factura'];
          $comprobante["idvacuna"] = $var['idvacuna'];
          $var['id_comprobante'] = insertarComprobante($l, $var, $comprobante);
          //$c_comprobantes++;
          $prestacion = obtenerPrestacion($l, $var);
          $var['id_prestacion'] = insertarPrestacion($l, $var, $prestacion);
          $var['id_nomenclador'] = $prestacion["id_nomenclador"];

          //debito////////////////////////////////////////////////////////////////////////////7
          //if ($prestacion[$i]["id_nomenclador"] > 0)
          //$c_prestaciones++;
        }
        //if ($l[0] == "T" || $l[0] == "L" && ($l[3] == 1 || $l[3] == 2 || $l[3] == 3 || $l[3] == 14 || $l[12] == 'NPE 41')) {
        if (($l[0] == "T" || $l[0] == "L") && ($l[3] == 1 || $l[3] == 2 || $l[3] == 3 ||
          $l[3] == 14)) {

          $var['id_nomenclador'] = obtenerIdNomenclador($l[12], $l, $var);

          $var['menos']++;

          $var['contra']++;

          if (($var['fuera_prest'] == 'si' || $var['nacimerr'] == 'si') && $data[0] == "L") {
            $Dbenefrece = "UPDATE [20benefrecepcionipos] SET fila='$row', mensaje='Error de datos de Trazadoras' where idprestacion='$l[4]' and cuie='$l[1]' and anomes='$l[2]'";
            sql($Dbenefrece, "Error al actualizar", 0) or excepcion('Error al actualizar');
          }

          if ($l[6] == 'P' || $l[6] == 'p') {
            $l[6] = 'R';
          }
          if ($l[6] == 'A' || $l[6] == 'a') {
            $l[6] = 'M';
          }

          existeIdTrazadora($l, $var);

          if ($var['ya_esta'] == 'si') {
            actualizarTrazadora($l, $var);
          }
          if ($var['ya_esta'] == 'no') {
            insertarTrazadora($l, $var);
          }
          if ($var['error'] == 'si') {
            $var['ya_esta'] = 'no';
            $var['cuenta_error']++;
            //$var['error']++;
            $var['descripcion_error'] .= '-' . $i;
            existeIdTrazadoraTMP($l, $var);
            if ($var['ya_estaTMP'] == 'si') {
              actualizarTrazadoraTMP($l, $var);
            } else {
              insertarTrazadoraTMP($l, $var);
            }
          }
          if (Fecha_db($l[13]) < $limite_trz['desde'] && $fecha_rec > $limite_trz['limite'] &&
            $l[0] == "L") {
            existeTrazadorarecepcion($l, $var, $limite_trz);
          }
        }

        ///////////////////////////SI ES INFORMADO//////////////////////////////////
        if (($l[0] == "I" || $l[0] == "T") && $var['metez'] == 's') {
          insertarInformado($l, $var);
          $var['c_i']++;
        }
        /*FIN DE SI ES INFORMADO*/

        /* calculo de monto */
        if (isset($var['id_factura'])) {
          $query_1 = "SELECT sum 
			(facturacion.prestacion.cantidad*facturacion.prestacion.precio_prestacion) as total
			FROM
			  facturacion.factura
			  INNER JOIN facturacion.comprobante ON (facturacion.factura.id_factura = facturacion.comprobante.id_factura)
			  INNER JOIN facturacion.prestacion ON (facturacion.comprobante.id_comprobante = facturacion.prestacion.id_comprobante)
			  INNER JOIN facturacion.nomenclador ON (facturacion.prestacion.id_nomenclador = facturacion.nomenclador.id_nomenclador)
			  INNER JOIN nacer.smiafiliados ON (facturacion.comprobante.id_smiafiliados = nacer.smiafiliados.id_smiafiliados)
			  INNER JOIN facturacion.smiefectores ON (facturacion.comprobante.cuie = facturacion.smiefectores.cuie)
			  where factura.id_factura=" . $var['id_factura'];
          $monto_prefactura_1 = sql($query_1) or excepcion('Error al calcular el total liquidado');
          $monto_prefactura_1 = $monto_prefactura_1->fields['total'];
          ($monto_prefactura_1 == '') ? $monto_prefactura_1 = 0 : $monto_prefactura_1 = $monto_prefactura_1;

          $query = "update facturacion.factura set 
   					monto_prefactura='$monto_prefactura_1'
   					where id_factura=" . $var['id_factura'];
          sql($query, 'Error al calcular el total liquidado', 1) or excepcion('Error al calcular el total liquidado');
        } else {
          $monto_prefactura_1 = 0;
        }
        $i++;
        if ($error == 'si')
          echo $var['descripcion_error'] . '<br />';
      }
    }
  }
  sql("COMMIT");
?>
  <td align="center" style="padding: 20px;font-size: 14px;background-color: white;">
    <table border="1">
	<tr align="center">
      <td width="300"><b>Descripcion</b></td><td colspan="2"><b>Detalles</b></td>
      </tr>
      <tr>
        <td width="300"><b>Monto Total Liquidado</b></td><td colspan="2"><?=
  number_format($monto_prefactura_1,2,'.','');
?></td>
      </tr>
      
      <tr>
      <td width="300"><b>Prestaciones Liquidadas</b></td><td colspan="2"><?=
  $var['c_pr']
?></td>
      </tr>
	  <tr>
      <td><b>Informados</b></td><td colspan="2" ><?=
  $var['c_i']
?></td>
      </tr> 
	  <tr align="center">
      <td>&nbsp;</td><td ><b>Aceptadas</b></td><td ><b>Rechazadas</b></td>
      </tr>
      <tr>
      <td><b>Embarazadas</b></td><td><?=
  $var['c_e']
?></td><td><?=
  $var['c_e_tmp']
?></td>
      </tr>
      <tr>
      <td><b>Partos</b></td><td><?=
  $var['c_p']
?></td><td><?=
  $var['c_p_tmp']
?></td>
      </tr>
      <tr>
      <td><b>Niños</b></td><td><?=
  $var['c_n']
?></td><td><?=
  $var['c_n_tmp']
?></td>
      </tr>
      <tr>
      <td><b>Muertes</b></td><td><?=
  $var['c_m']
?></td><td><?=
  $var['c_m_tmp']
?></td>
      </tr>                       
    </table>
  <?php
  //  echo "Prestaciones: " . $var['c_pr'] . "<br />";
  //  echo "Embarazadas: " . $var['c_e'] . " / " . $var['c_e_tmp'] . "<br />";
  //  echo "Partos: " . $var['c_p'] . " / " . $var['c_p_tmp'] . "<br />";
  //  echo "Niños: " . $var['c_n'] . " / " . $var['c_n_tmp'] . "<br />";
  //  echo "Muertes: " . $var['c_m'] . " / " . $var['c_m_tmp'] . "<br />";
  //  echo "Informados: " . $var['c_i'] . "<br />";

?>
  </td>
  <?php
}
catch (exception $e) {
  //  echo "d";
  sql("ROLLBACK", "Error en rollback", 0);
?>
  <td align="center" style="padding: 20px;font-size: 14px;background-color: white;color: red;font-weight: bold;">
<?php
  if (isset($i))
    echo "Error en la línea $i<br />";
  echo "Error: " . $e->getMessage() . "<br /><br /><br />";
?>
    <a href="recepcion_txt.php">&laquo; Volver atrás</a>
  </td>
<?php
}
;
?>
      </tr>
    </table>
<?php
echo fin_pagina(); // aca termino

?>
  </body>
</html>
<?php
ob_end_flush();
?>