<?php
  require_once ("../../config.php");
  require_once ("../../lib/funciones_misiones.php");

  ob_start();

  $error_types = array(1 =>
      'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
      'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified 
        in the HTML form.',
      'The uploaded file was only partially uploaded.', 'No file was uploaded.', 6 =>
      'Missing a temporary folder.', 'Failed to write file to disk.',
      'A PHP extension stopped the file upload.');

  $error_types = array(1 => 'El archivo excede el tama�o m�ximo permitido.',
      'El archivo excede el tama�o m�ximo permitido.',
      'El archivo fue subido parcialmente.', 'No se ha subido ningun archivo.', 6 =>
      'No se encuentra la carpeta temporal.', 'Fallo al escribir el archivo a disco.',
      'Una extensi�n de PHP ha detenido la carga del archivo.');

  $mes_nombre = array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
      "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

  try {

    sql("BEGIN");

    $cmd = $_POST["enviar"];
    if ($cmd == "Enviar") {
      $tamanio = $_FILES["archivo"]["size"];
      if ($tamanio == 0)
        excepcion('El tama�o del archivo es nulo');

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
            //$error = "�Posible ataque de carga de archivos!";
            excepcion("�Posible ataque de carga de archivos!");
          }
      }
    }

    $nombre_archivo = explode('.', $_FILES["archivo"]["name"]);
    $nombre_archivo = $nombre_archivo[0];

    $longitud = strlen($nombre_archivo);
    if ($longitud == '10' && $nombre_archivo[0] == 'V') {
      $e_sql = "SELECT * FROM inmunizacion.recepciontxt WHERE nombrearchivo = '$nombre_archivo'";
      $e_busqueda = sql($e_sql, "Error al buscar archivo ya cargado") or excepcion("Error al buscar archivo ya cargado");
      if ($e_busqueda->RecordCount() > 0) {
        excepcion('El archivo ya est� cargado en el sistema.');
      } else {
        $fecha_rec = date("Y-m-d h:i");
        $usuario = $_ses_user['id'];
        $e_sql = "INSERT INTO inmunizacion.recepciontxt (nombrearchivo, fecha, usuario) VALUES ('$nombre_archivo', '$fecha_rec', '$usuario') RETURNING idrecepcion";
        $result_recepcion = sql($e_sql, "Error al insertar archivo.", 0) or excepcion('Error al insertar archivo.');
        if ($result_recepcion->RecordCount() > 0) {
          $result_recepcion->movefirst();
          $idRecepcion = $result_recepcion->fields['idrecepcion'];
        } else {
          excepcion('No se encuentra el archivo.');
        }
      }
    } else {
      excepcion('Formato de nombre incorrecto.');
    }
    $file = fopen(UPLOADS_DIR . "/archivos/" . $_FILES["archivo"]["name"], 'r');
    while ($datos = fgetcsv($file, 10000, "\t\t")) {
      $lineas[] = explode(';', $datos[0]);
    }
    $lineas = limpiar($lineas);
    //print_r($l);
    $iii = 0;
    $errores = 0;
    foreach ($lineas as $data) {
      $num = count($data);
      if ($num == 21 or $num == 22) {
        $error_dni = 'no';
        $nrodni = 'si';
        $vacuna = 'si';
        $con_cuie = 'si';
        $t_fechav = 'si';
        $existe_id = 'no';
        $grupo = 'si';

        if ($data[0] == null) {
          $cuie = '';
          $con_cuie = 'no';
        } else {
          $cuie = $data[0];
        }
        if ($data[2] == null) {
          $claveBenef = 'vacio';
        } else {
          $claveBenef = $data[2];
        }
        if ($data[3] == null) {
          $cDoc = '';
        } else {
          $cDoc = $data[3];
        }
        if ($data[4] == null) {
          $tDoc = '';
        } else {
          $tDoc = $data[4];
        }
        if ($data[5] == '' && ($tDoc != 'S/D' && $tDoc != 'No Tiene')) {
          $nroDoc = '';
          $nrodni = 'no';
        }

        if ($data[5] != '' && $data[5] != null) {
          $nroDoc = $data[5];
          $nrodni = 'si';

          $permitidos = "0123456789";
          for ($i = 0; $i < strlen($nroDoc); $i++) {
            if (strpos($permitidos, substr($nroDoc, $i, 1)) === false) {
              $nroDoc = '';
              $error_dni = 'si';
            }
          }
        }

        if ($data[6] == null) {
          $apellido = '';
        } else {
          $apellido = $data[6];
        }
        if ($data[7] == null) {
          $nombre = '';
        } else {
          $nombre = $data[7];
        }
        if ($data[8] == null) {
          $fNac = '';
        } else {
          $fNac = $data[8];
        }
        if ($data[9] == null) {
          $fvacuna = '';
          $t_fechav = 'no';
        } else {
          $fvacuna = $data[9];
        }
        if ($data[10] == null) {
          $idvacuna = -1;
          $vacuna = 'no';
        } else {
          $idvacuna = $data[10];
        }
        if ($data[11] == null) {
          $domicilio = '';
        } else {
          $domicilio = $data[11];
        }
        if ($data[12] == null) {
          $departamento = 0;
        } else {
          $departamento = $data[12];
        }
        if ($data[13] == null) {
          $municipio = '';
        } else {
          $municipio = $data[13];
        }
        if ($data[14] == null) {
          $sexo = '';
        }
        $sexo_tiene = 'no';
        if ($data[14] == 'M' || $data[14] == 'F') {
          $sexo = $data[14];
          $sexo_tiene = 'si';
        } $existe_id = 'no';

        $control = "select idprestacion
                  from inmunizacion.benefinmunizacion
                  where cuie='$cuie'  and idprestacion=$data[1] ";
        $e_busqueda = sql($control, "Error al consultar prestacion existente") or excepcion("Error al consultar prestacion existente");
        if ($e_busqueda->RecordCount() > 0) {
          //excepcion('La prestacion ya est� cargada en el sistema.');
          $existe_id = 'si';
        }
        if ($data[15] == null) {
          $originaria = '';
        }
        $originaria_tiene = 'no';
        if ($data[15] == 'N' || $data[15] == 'S') {
          $originaria = $data[15];
          $originaria_tiene = 'si';
        }
        if ($data[16] == null) {
          $donde = '';
        }
        $donde_tiene = 'no';
        if ($data[16] == 'V' || $data[16] == 'T') {
          $donde = $data[16];
          $donde_tiene = 'si';
        }

        if ($data[17] == null) {
          $terreno = '';
        } else {
          $terreno = $data[17];
        }
        if ($data[18] == null) {
          $iddepartamento = 0;
        } else {
          $iddepartamento = $data[18];
        }
        if ($data[19] == null) {
          $idmunicipio = 0;
        } else {
          $idmunicipio = $data[19];
        }
        if ($data[20] == null) {
          $idgrupovacuna = -1;
          $grupo = 'no';
        } else {
          $idgrupovacuna = $data[20];
          $grupo = 'si';
        }
      }

      if ($error_dni == 'no' && $nrodni == 'si' && $vacuna == 'si' && $con_cuie == 'si' && $t_fechav == 'si' && $existe_id == 'no' && $sexo_tiene == 'si' && $originaria_tiene == 'si' && $donde_tiene == 'si' && $grupo == 'si') {
        $cuenta_procesado++;
        /* 	$SQLB="BEGIN TRANSACTION";
          mssql_query($SQLB); */
        $SQL = "insert into inmunizacion.benefinmunizacion_tmp (idrecepcion,cuie,idprestacion,
        clavebeneficiario,clasedoc,tipodoc,nrodoc,apellido,nombre,fechaNac,
        fechavacunacion,idvacuna,domicilio,departamento,municipio,sexo,
        originaria,donde,terreno,iddepartamento,idmunicipio,idrangovacuna)
	values
('$idRecepcion','$cuie','$data[1]','$claveBenef','$cDoc','$tDoc','$nroDoc','$apellido','$nombre','".Fecha_db($fNac)."','".Fecha_db($fvacuna)."',$idvacuna,'$domicilio','$departamento','$municipio','$sexo','$originaria','$donde','$terreno',$iddepartamento,$idmunicipio,$idgrupovacuna)";

        $result_recepcion = sql($SQL, "Error al recepcionar informe.", 0) or excepcion('Error al insertar archivo.');
      }
      $mensaje = '';
      if ($nrodni == 'no') {
        $mensaje = ', No posee numero de Documento';
      }
      if ($error_dni=='si') {
        $mensaje .= ', Numero de Documento invalido';
      }
      if ($vacuna == 'no') {
        $mensaje .= ', No posee identificacion de la vacuna aplicada';
      }
      if ($con_cuie == 'no') {
        $mensaje .= ', No posee el codigo de efector';
      }
      if ($t_fechav == 'no') {
        $mensaje .= ', No posee fecha de vacunacion';
      }
      if ($existe_id == 'si') {
        $mensaje .= ', Ya existe la identificacion de la prestacion';
      }
      if ($sexo_tiene == 'no') {
        $mensaje .= ', No esta definido el sexo';
      }
      if ($originaria_tiene == 'no') {
        $mensaje .= ', No esta definida la poblacion';
      }
      if ($donde_tiene == 'no') {
        $mensaje .= ', No esta definido sector de vacunacion';
      }
      if ($grupo == 'no') {
        $mensaje .= ', No esta definido el grupo de de vacuna';
      }
      if ($mensaje != '') {
        $consul = "insert into inmunizacion.benefinmunizacion_tmp
     (idrecepcion,cuie,idprestacion,clavebeneficiario,clasedoc,tipodoc,
        nrodoc,apellido,nombre,fechanac,fechavacunacion,idvacuna,fila,mensaje,
        domicilio,departamento,municipio,sexo,originaria,donde,terreno,
        iddepartamento,idmunicipio,idrangovacuna) values('$idRecepcion','$cuie','$data[1]','$claveBenef','$cDoc','$tDoc','$nroDoc','$apellido','$nombre','".Fecha_db($fNac)."','".Fecha_db($fvacuna)."',$idvacuna,'$i','$mensaje', '$domicilio','$departamento','$municipio','$sexo','$originaria','$donde','$terreno',$iddepartamento,$idmunicipio,$idgrupovacuna)";

        sql($consul, "Error al insertar inmunizacion con error", 0) or excepcion('Error al insertar inmunizacion');
        $errores++;
      }
      $iii++;
    }
    sql("COMMIT");
    
    echo "Se han recepcionado $iii registros, de los cuales $errores poseen errores";
    
  } catch (Exception $e) {
    sql("ROLLBACK", "Error en rollback", 0);
    echo "Error: " . $e->getMessage() . "<br /><br /><br />";
  }
  ?>
<?php
echo fin_pagina(); // aca termino
?>
<?php
ob_end_flush();
?>