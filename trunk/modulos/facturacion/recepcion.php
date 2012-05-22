<?php
session_start();
if ($_SESSION['user'] == '' or $_SESSION['user'] == null) {
  echo '<script language="JavaScript"> 
alert("Su session ha expirado.\nVuelva a iniciar el sistema");
if (self.parent.frames.length != 0)
self.parent.location="../cierra_sesion.php";
</script> ';
}

$codExpediente = trim($codExpediente);
$archivo = 'txt/' . $nombreArchivo;
$vig_estim = substr($nombreArchivo, 15, 1);
$ano_vig = substr($nombreArchivo, 9, 4);
$mes_vig = substr($nombreArchivo, 13, 2);

echo '<SCRIPT Language="Javascript">
		function errorsimple()
		{
			if (screen.width + "x" +screen.height == "1024x768") 
			window.location="menu_1024x768.php?ini1=si&expediente=' . $codExpediente .
  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '";
			if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&expediente=' . $codExpediente . '&cuerp=' .
  $nroCuerpoExp . '&cara=' . $nroCaratula . '";
		}
	  </SCRIPT>';

if ($mes_vig < 1 || $mes_vig > 12) {
  echo '<SCRIPT Language="Javascript">
		alert("Nombre del txt incorrecto");
 				 if (screen.width + "x" +screen.height == "1024x768") 
		window.location="menu_1024x768.php?mjs=";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 				window.location="menu.php?mjs=";
				
				  </SCRIPT>';
} else {
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
  $fprest_limite = '10/' . $mes_vig1 . '/' . $ano_vig1;

  function compara_fechas($fecha1, $fecha2) {
    list($dia1, $mes1, $año1) = split("/", $fecha1);
    $f1 = $año1 . $dia1 . $mes1;
    list($dia2, $mes2, $año2) = split("/", $fecha2);
    $f2 = $año2 . $dia2 . $mes2;
    $dif = $f1 - $f2;
    return ($dif);
  }

  echo 'PROCESANDO ORDEN...ESTA OPERACION PUEDE TARDAR VARIOS MINUTOS....ESPERE.... ';
  ////////////RECEPCION DE IPOS

  $q = gethostbyaddr($_SERVER['REMOTE_ADDR']);
  $user = $_SESSION['user'];
  $cuenta_procesado = 0;
  $cuenta_error = 0;
  $errort = 0;
  $errorl = 0;
  $ni = 0;
  $em = 0;
  $pa = 0;
  $contra = 0;
  $mu = 0;
  $SQLerror = "ROLLBACK";
  include ("../conex.php");
  $errorsql = mssql_query("BEGIN TRANSACTION");

  if (!$errorsql) {
    echo '<SCRIPT Language="Javascript">
				alert("ERROR...NO SE EJECUTO BEGIN.");
				errorsimple();
			 </SCRIPT>';
  }

  $SQLarchivo = "exec Recepcion_CargaArchivoRegistro '$nombreArchivo','$user','$q'";
  $result1 = mssql_query($SQLarchivo);
  if ($result1 == false) {
    mssql_query($SQLerror);
    mssql_close($conexion);
    echo "<SCRIPT Language='Javascript'>
				alert('Error recepcion.Vuelva a intentarlo');
				errorsimple();
				</SCRIPT>";
  }
  if (mssql_num_rows($result1) > 0) {
    while ($row1 = mssql_fetch_array($result1)) {
      $idRecepcion = $row1["idRecepcion"];
    }
  }

  //////////FIN DEL REGISTRO DE LAS RECEPCIONES
  ///////////////////////////////////////////////////////*dos facturas*////////////////////////////////////////////
  $row = 1;
  $menos = 0;
  $menosl = 0;
  $handle = fopen("$archivo", "r");

  //while (($data = fgetcsv($handle, 2000,";",'"')) !== FALSE)
  while (($data = fgetcsv($handle, 3000, ";")) !== false) {
    //$x=fopen("archivo.txt","w");   $as='**'.$data[4];   if($x)    {     fwrite($x,$as);    }

    $num = count($data);
    $idata = 0;
    while ($idata <= $num) {
      $data[$idata] = str_replace("''", "", $data[$idata]);
      $data[$idata] = str_replace("'", "", $data[$idata]);
      $data[$idata] = str_replace("!", "", $data[$idata]);
      $data[$idata] = str_replace("///", "", $data[$idata]);
      $data[$idata] = str_replace("//", "", $data[$idata]);
      $data[$idata] = str_replace("}", "", $data[$idata]);
      $data[$idata] = str_replace("{", "", $data[$idata]);
      $data[$idata] = str_replace("?:..", "", $data[$idata]);
      $data[$idata] = str_replace("?", "", $data[$idata]);
      $data[$idata] = str_replace('"', "", $data[$idata]);
      $data[$idata] = str_replace("#", "", $data[$idata]);
      $data[$idata] = str_replace("=", "", $data[$idata]);
      $data[$idata] = str_replace("~", "", $data[$idata]);
      $data[$idata] = str_replace("%", "", $data[$idata]);
      $data[$idata] = str_replace("(", "", $data[$idata]);
      $data[$idata] = str_replace(")", "", $data[$idata]);
      //$data[$idata]=str_replace('"', "", $data[$idata]);
      $idata++;
    }
    ///////////////////////////SI ES MUERTES//////////////////////////////////
    if ($data[0] == "M" && $row == 1) {
      $fechafactura = $data[5];
      $ddjj_sip = $data[6];
      if ($vig_estim == 'V') {
        $SQLmurt = "exec Recepcion_CargaMuertes '$data[1]','$mes_vig','$ano_vig','$data[3]','$data[4]'";
        $errorsqlmurt = mssql_query($SQLmurt);
        if (!$errorsqlmurt) {
          mssql_query($SQLerror);
          mssql_close($conexion);
          fclose($handle);
          echo '<SCRIPT Language="Javascript">
					if (screen.width + "x" +screen.height == "1024x768") 
					window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
            '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=208";
					if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
					window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
            '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=208";
					</SCRIPT>';
        }

      }
    }
    /*FIN DE SI ES MUERTES*/

    if (($num == 75) && $row != 1) {
      $descripcion_error = '';
      $eliminado = 'N';
      $ya_esta = 'no';
      $ya_estaTMP = 'no';
      $error = 'no';
      $idvacuna = '';
      $error_datos = 'no';
      $mjs_error_datos = '';
      $idtaller = '';
      $km = '';
      $e_defuncion = 'no';
      $e_caso = 'no';
      $ojo = 'no';
      $existe_id = 'no';
      $idbenefrecepcion = 0;
      //$cn2=substr($data[12], 3);
      //$cn1=substr($data[12], 0,3);
      //$cn1mas2=$cn1.' '.$cn2;
      ///compara codigo de nomenclador
      $COD = "SELECT rtrim(codNomenclador)codNomenclador
		FROM [20Nomencladores]
		where codNomenclador='$data[12]' or replace(codnomenclador,' ','')='$data[12]'";
      $CODN = mssql_query($COD);
      if ($CODN == false) {
        mssql_query($SQLerror);
        mssql_close($conexion);
        fclose($handle);
        echo "<SCRIPT Language='Javascript'>
				alert('Error.Vuelva a intentarlo');
				errorsimple();
				</SCRIPT>";
      }
      if (mssql_num_rows($CODN) > 0) {
        while ($fila = mssql_fetch_array($CODN)) {
          $data[12] = $fila['codNomenclador'];
        }
      } else {
        $error_datos = 'si';
        $mjs_error_datos .= ',CodNomenclador';
        $data[12] = 'M 999';
      }
      mssql_free_result($CODN);

      $COEF = $data[1];
      $IPRES = $data[4];
      $COEF = trim($COEF);
      $IPRES = trim($IPRES);

      if ($data[5] == null) {
        $claveBeneficiario = 'vacio';
      } else {
        $claveBeneficiario = $data[5];
      }
      if ($data[6] == null) {
        $claseDoc = '';
        $error_datos = 'si';
        $mjs_error_datos .= ',Clase Doc.';
        if ($data[3] == 1) {
          $error = 'si';
          $descripcion_error .= ',claseDoc';
        }
      } else {
        $claseDoc = $data[6];
        $error = 'no';
      }
      if ($data[7] == null) {
        $tipoDoc = '';
        $error = 'si';
        $descripcion_error .= ',tipoDoc';
      } else {
        $tipoDoc = $data[7];
        if ($tipoDoc == 'EXT') {
          $error = 'si';
          $descripcion_error .= ',tipoDoc';
        }
        if ($tipoDoc == 'CI' || $tipoDoc == 'ci') {
          $tipoDoc = 'C20';
        }
      }
      if ($data[8] == '') {
        $nroDoc = '';
        $error = 'si';
        $error_datos = 'si';
        $mjs_error_datos .= ',NroDoc.';
      } else {
        $nroDoc = $data[8];
      }

      $permitidos = "0123456789";
      for ($i = 0; $i < strlen($data[8]); $i++) {
        if (strpos($permitidos, substr($data[8], $i, 1)) === false) {
          $error = 'si';
          $nroDoc = '';
          $error_datos = 'si';
          $mjs_error_datos .= ',NroDoc.';
        }
      }
      $metez = 's';
      if ($nroDoc == '' && $claveBeneficiario == 'vacio') {
        $descripcion_error .= ',no clave_dni';
        $error = 'si';
        $error_datos = 'si';
        $mjs_error_datos .= ',NroDoc y ClaveBeneficiario';
        $metez = 'n';
      }
      if ($data[9] == null) {
        $apellido = '';
      } else {
        $apellido = str_replace("'", "", $data[9]);
      }
      if ($data[10] == null) {
        $nombre = '';
      } else {
        $nombre = str_replace("'", "", $data[10]);
      }
      if ($data[11] == null) {
        $fechaNac = '';
      } else {
        $fechaNac = $data[11];
      }
      $fp_pacomp = $data[13];
      $comp = "SELECT datediff(day,'$fechaNac','$fp_pacomp') nc,datediff(day,'$fp_pacomp','$fprest_limite') pr,datediff(day,'$fp_pacomp','01/08/2009') new_txt";
      $rescomp = mssql_query($comp);
      if ($rescomp == false) {
        mssql_query($SQLerror);
        mssql_close($conexion);
        fclose($handle);
        echo "<SCRIPT Language='Javascript'>
				alert('Error.Vuelva a intentarlo');
				errorsimple();
				</SCRIPT>";
      }
      if (mssql_num_rows($rescomp) > 0) {
        while ($fcomp = mssql_fetch_array($rescomp)) {
          $pr = $fcomp['pr'];
          $nc = $fcomp['nc'];
          $new_txt = $fcomp['new_txt'];
        }
      }

      $nacimerr = 'no';
      if ($nc < 0) {
        if ($data[3] == 1 || $data[12] == 'NPE 41' || $data[12] == 'RPE 93') {
          $descripcion_error .= ',fnac no antes ctrl';
          $error = 'si';
        }
        $nacimerr = 'si';
        $error_datos = 'si';
        $mjs_error_datos .= ',FechaNac>FechaPrestacion';
      }
      $fuera_prest = 'no';
      if ($pr < 0) {
        $descripcion_error .= ',fecha prest';
        $error = 'si';
        $fuera_prest = 'si';
        $error_datos = 'si';
        $mjs_error_datos .= ',No corresponde fecha de prestacion para el periodo liquidado';
      }

      list($dia, $mes, $ano) = explode('/', $data[13]);
      $prestacion = $ano . $mes . $dia;
      list($dia1, $mes1, $ano1) = explode('/', $fechaNac);
      $nacimiento = $ano1 . $mes1 . $dia1;
      $menor = $prestacion - $nacimiento;

      if ($data[16] == null) {
        $origen = '';
      } else {
        $origen = $data[16];
      }
      if ($data[17] == null) {
        $destino = '';
      } else {
        $destino = $data[17];
      }
      if ($data[18] == null || $data[3] == 2 || $data[3] == 3) {
        $clavemadre = '';
      } else {
        $clavemadre = $data[18];
      }
      /*si formato nuevo*/
      if ($data[3] == 14) {
        if ($data[48] == null) {
          $fdefuncion = '';
          $e_defuncion = 'si';
          $descripcion_error .= ',fdefuncion';
          $error = 'si';
        } else {
          $fdefuncion = $data[48];
        }
        if ($data[49] == null) {
          $fppmuerte = '';
        } else {
          $fppmuerte = $data[49];
        }
        if ($data[50] == null) {
          $caso = '';
          $e_caso = 'si';
          $descripcion_error .= ',caso';
          $error = 'si';
        } else {
          $caso = $data[50];
        }
      }
      /*fin_ si formato nuevo*/
      $perimcef_rn = 0;
      $talla_rn = 0;
      $au = 0;
      $tamin = 0;
      $tamax = 0;
      $peso_mem02 = 0;
      if ($new_txt <= 0) {
        if ($data[3] == 2) {
          if ($data[59] == null || $data[59] == 0) {
            $tamax = 0; //$descripcion_error.=',tamax'; $error='si';
          } else {
            $tamax = $data[59];
          }

          if ($data[60] == null || $data[60] == 0) {
            $tamin = 0; //$descripcion_error.=',tamin'; $error='si';
          } else {
            $tamin = $data[60];
          }

          if ($data[61] == null || $data[61] == 0) {
            $au = 0; //if($data[12]=='MEM 02' || $data[12]=='MER 08'){ $descripcion_error.=',au'; $error='si';}
          } else {
            $au = $data[61];
          }

          if ($data[64] == null || $data[64] == 0) {
            $peso_mem02 = 0; //if($data[12]=='MEM 02'){$descripcion_error.=',peso_mem02'; $error='si';}
          } else {
            $peso_mem02 = $data[64];
          }
        }
        if ($data[3] == 3) {
          if ($data[62] == null || $data[62] == 0) {
            $talla_rn = 0; //$descripcion_error.=',talla_rn'; $error='si';
          } else {
            $talla_rn = $data[62];
          }

          if ($data[63] == null || $data[63] == 0) {
            $perimcef_rn = 0; //$descripcion_error.=',perimcef_rn'; $error='si';
          } else {
            $perimcef_rn = $data[63];
          }
        }
      }
      if ($data[65] == null) {
        $sexo = '';
        /*$error_datos='si'; $mjs_error_datos.=',Sexo';*/
      } else {
        $sexo = $data[65];
      }
      if ($data[66] == null) {
        $municipio = '';
        /*$error_datos='si'; $mjs_error_datos.=',Municipio';*/
      } else {
        $municipio = $data[66];
      }
      if ($data[3] == 1) {
        if ($data[67] == null) {
          $percentilo_imc = '';
          /* if ($menor >=10000){//mayor de 1 año
          $descripcion_error.=',percentilo_imc'; $error='si'; }*/
        } else {
          $percentilo_imc = $data[67];
        }
        if ($data[68] == null) {
          $imc = 0;
          /* if ($menor >=10000){//mayor de 1 año
          $descripcion_error.=',imc'; $error='si'; }*/
        } else {
          $imc = $data[68];
        }
      }
      if ($data[53] == null) {
        $semgesta = '';
      } else {
        $semgesta = $data[53];
      }
      if ($data[69] == null) {
        $discapacitado = '';
        /*$error_datos='si'; $mjs_error_datos.=',discapacitado';*/
        if ($data[3] == 1 || $data[3] == 2 || $data[3] == 3 || $data[12] == 'NPE 41') {
          /*$descripcion_error.=',discapacitado'; $error='si'; */
        }
      } else {
        $discapacitado = $data[69];
      }

      if ($data[70] == null) {
        $cod_aldea = '';
        /*$error_datos='si'; $mjs_error_datos.=',discapacitado';*/
      } else {
        $cod_aldea = $data[70];
      }

      if ($data[71] == null) {
        $descrip_aldea = '';
        /*$error_datos='si'; $mjs_error_datos.=',discapacitado';*/
      } else {
        $descrip_aldea = $data[71];
      }

      if ($data[72] == null) {
        $calle = '';
        /*$error_datos='si'; $mjs_error_datos.=',discapacitado';*/
      } else {
        $calle = $data[72];
      }

      if ($data[73] == null) {
        $num_calle = '';
        /*$error_datos='si'; $mjs_error_datos.=',discapacitado';*/
      } else {
        $num_calle = $data[73];
      }

      if ($data[74] == null) {
        $barrio = '';
        /*$error_datos='si'; $mjs_error_datos.=',discapacitado';*/
      } else {
        $barrio = $data[74];
      }
      ///////////////////////////SI ES LIQUIDACION Y TRAZADORAS//////////////////////////////////

      if ($data[0] == "L") {

        $caratula_id = "exec Recepcion_CargaExpediente '$codExpediente','$nroCuerpoExp','$nroCaratula','$vig_estim','$mes_vig','$ano_vig','$fechafactura','$ddjj_sip','$fecha_mesaentrada'";
        /*$x=fopen("archivo.txt","w");   $as='**'.$caratula_id;   if($x)    {     fwrite($x,$as);    }*/
        $resultado = mssql_query($caratula_id);
        if ($resultado == false) {
          mssql_query($SQLerror);
          mssql_close($conexion);
          fclose($handle);
          echo "<SCRIPT Language='Javascript'>
				alert('Error.Vuelva a intentarlo');
				errorsimple();
			 </SCRIPT>";
        }
        if (mssql_num_rows($resultado) > 0) {
          while ($f1 = mssql_fetch_array($resultado)) {
            $idCaratula = $f1['idCaratula'];
          }
        }

        if ($data[52] == null) {
          $hc = '';
        } else {
          $hc = $data[52];
        }

        if ($data[54] == null) {
          $ultimoctrl = '';
        } else {
          $ultimoctrl = $data[54];
        }
        if ($data[55] == null) {
          $medicoguardia = '';
        } else {
          $medicoguardia = $data[55];
        }
        if ($data[56] == null) {
          $coddiag = '';
        } else {
          $coddiag = $data[56];
        }
        if ($data[57] == null) {
          $diag = '';
        } else {
          $diag = $data[57];
        }
        if ($data[58] == null) {
          $obs = '';
        } else {
          $obs = $data[58];
        }

        ////////////verifica repetidod
        $control = "select cuie,idrecepcion
				from [20BenefRecepcionIpos]
				where cuie='$data[1]' and anoMes='$data[2]' and idPrestacion='$data[4]'";
        $resulx = mssql_query($control);
        if ($resulx == false) {
          echo $data[1] . '*' . $data[2] . '*' . $data[4];
          mssql_query($SQLerror);
          mssql_close($conexion);
          fclose($handle);
          echo "<SCRIPT Language='Javascript'>
				alert('Error.Vuelva a intentarlo');
				errorsimple();
				</SCRIPT>";
        }
        if (mssql_num_rows($resulx) > 0) {
          while ($rfilas = mssql_fetch_array($resulx)) {
            $existe_id = 'si';
            $idrecepcion_idb = $rfilas['idrecepcion'];
            if ($idrecepcion_idb != $idRecepcion) {
              $mjs_id = 'idprestacion ya existente en el sistema';
            }
            if ($idrecepcion_idb == $idRecepcion) {
              $mjs_id = 'idprestacion ya existente en el archivo';
            }
            $existe_re = "select cuie
				from [20rechazados]
				where cuie='$data[1]' and anoMes='$data[2]' and idPrestacion='$data[4]'";
            $resul_re = mssql_query($existe_re);
            if ($resul_re == false) {
              mssql_query($SQLerror);
              mssql_close($conexion);
              fclose($handle);
              echo "<SCRIPT Language='Javascript'>
				alert('Error.Vuelva a intentarlo');
				errorsimple();
				</SCRIPT>";
            }
            if (mssql_num_rows($resul_re) > 0) {
              while ($rech = mssql_fetch_array($resul_re)) {
                $existe_id = 'no';
              }
            }
            mssql_free_result($resul_re);
          }
        }
        mssql_free_result($resulx);

        /*pregunta se es taller */
        $idtaller = '0';
        if ($data[12] == 'CMI 65' || $data[12] == 'CMI 66' || $data[12] == 'CMI 67' || $data[12] ==
          'RCM 107' || $data[12] == 'RCM 108' || $data[12] == 'RCM 109') {
          if ($data[15] == null) {
            $idtaller = '';
            $error_datos = 'si';
            $mjs_error_datos .= ',IdTaller';
          } else {
            $idtaller = $data[15];
          }
        }
        /*pregunta si es vacunacion*/
        $vacuna = 'si';
        $idvacuna = '';
        if ($data[12] == 'NPE 41' || $data[12] == 'NPE 42' || $data[12] == 'RPE 93' || $data[12] ==
          'RPE 94' || $data[12] == 'NNE 31' || $data[12] == 'MPU 23') {
          if ($data[14] == null || $data[14] == '') {
            $error_datos = 'si';
            $mjs_error_datos .= ',Id. Vacuna Invalido';
          } else {
            $idvacuna = $data[14];
            if ($existe_id == 'no') {
              $contro4 = "select codNomenclador		from [20BenefRecepcionIpos]
								where ((codNomenclador='$data[12]' and idvacuna='$idvacuna' and fechaprestacion='$data[13]') and 
								((claveBeneficiario='$claveBeneficiario') or (claseDoc='$claseDoc' and nroDoc='$nroDoc')))";
              $resC = mssql_query($contro4);
              if ($resC == false) {
                mssql_query($SQLerror);
                mssql_close($conexion);
                fclose($handle);
                echo "<SCRIPT Language='Javascript'>
									alert('Error.Vuelva a intentarlo');
									errorsimple();
									</SCRIPT>";
              }
              if (mssql_num_rows($resC) > 0) {
                while ($r = mssql_fetch_array($resC)) {
                  $error_datos = 'si';
                  $mjs_error_datos .= ',Vacuna duplicada';
                }
              }
              mssql_free_result($resC);
            }
          }
        }
        /*pregunta se es traslado*/
        if ($data[12] == 'TMI 69' || $data[12] == 'TMI 70' || $data[12] == 'TMI 71' || $data[12] ==
          'RTM 111' || $data[12] == 'RTM 112' || $data[12] == 'RTM 113') {
          if ($data[15] == null) {
            $km = 0;
          } else {
            $km = $data[15];
          }
        }

        /* Si corresponde auditoria*/
        $auditoriaxontrol = "exec Recepcion_CargaAuditoria '$data[1]','$data[2]','$idCaratula','$idRecepcion','$codExpediente','$nroCuerpoExp','$nroCaratula','$user'";
        $reauti = mssql_query($auditoriaxontrol);
        if ($reauti == false) {
          mssql_query($SQLerror);
          mssql_close($conexion);
          fclose($handle);
          echo "<SCRIPT Language='Javascript'>
						alert('Error.Vuelva a intentarlo');
						errorsimple();
					</SCRIPT>";
        }

        /*graba beneficiarios*/
        if ($existe_id == 'no' && $error_datos == 'no') {
          $cuenta_procesado++;

          $SQL = "insert into [20BenefRecepcionIpos] (idRecepcion,cuie,anomes,tipoInforme,idPrestacion,claveBeneficiario,claseDoc,tipoDoc,nroDoc,apellido,nombre,fechaNac,codNomenclador,fechaPrestacion,procesado,idCaratula,idvacuna,idtaller,km,origen,destino,hc,semgesta,ultimoctrl,medicoguardia,coddiag,diag,obs,clavemadre,sexo,municipio,discapacitado,calle, num_calle, barrio)
	values ('$idRecepcion','$data[1]','$data[2]','$data[3]','$data[4]','$claveBeneficiario','$claseDoc','$tipoDoc','$nroDoc','$apellido','$nombre','$fechaNac','$data[12]','$data[13]','N','$idCaratula','$idvacuna','$idtaller','$km','$origen','$destino','$hc','$semgesta','$ultimoctrl','$medicoguardia','$coddiag','$diag','$obs','$clavemadre','$sexo','$municipio','$discapacitado','$calle', '$num_calle', '$barrio')";

          $errorsql = mssql_query($SQL);
          if (!$errorsql) {
            /*$x=fopen("archivo.txt","w");   $as='**'.$SQL;   if($x)    {     fwrite($x,$as);    }*/
            mssql_query($SQLerror);
            mssql_close($conexion);
            fclose($handle);
            echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
              '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=2&linea=' . $row . '";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
              '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=2&linea=' . $row . '";
					</SCRIPT>';
          }

        }

        /////////ERRORES///////
        if ($existe_id == 'si') {
          $cuenta_procesado++;
          $SQL3 = "insert into [20BenefRecepcionIpos] (idRecepcion,cuie,anomes,tipoInforme,idPrestacion,claveBeneficiario,claseDoc,tipoDoc,nroDoc,apellido,nombre,fechaNac,codNomenclador,fechaPrestacion,procesado,idCaratula,mensaje,fila,idvacuna,idtaller,km,origen,destino,hc,semgesta,ultimoctrl,medicoguardia,coddiag,diag,obs,clavemadre,sexo,municipio,discapacitado,calle, num_calle, barrio)
	values ('$idRecepcion','$data[1]','$data[2]','$data[3]','$data[4]','$claveBeneficiario','$claseDoc','$tipoDoc','$nroDoc','$apellido','$nombre','$fechaNac','$data[12]','$data[13]','N','$idCaratula','$mjs_id','$row','$idvacuna','$idtaller','$km','$origen','$destino','$hc','$semgesta','$ultimoctrl','$medicoguardia','$coddiag','$diag','$obs','$clavemadre','$sexo','$municipio','$discapacitado','$calle', '$num_calle', '$barrio')";
          $errorsql = mssql_query($SQL3);
          if (!$errorsql) {
            mssql_query($SQLerror);
            mssql_close($conexion);
            fclose($handle);
            echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
              '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=6";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
              '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=6";
					</SCRIPT>';
          }
          $error_datos = 'no';
        }
        /*ERROR GENERAL*/
        if ($error_datos == 'si') {
          $cuenta_procesado++;
          $SQL20 = "insert into [20BenefRecepcionIpos] (idRecepcion,cuie,anomes,tipoInforme,idPrestacion,claveBeneficiario,claseDoc,tipoDoc,nroDoc,apellido,nombre,fechaNac,codNomenclador,fechaPrestacion,procesado,idCaratula,mensaje,fila,idvacuna,idtaller,km,origen,destino,hc,semgesta,ultimoctrl,medicoguardia,coddiag,diag,obs,clavemadre,sexo,municipio,discapacitado,calle, num_calle, barrio)
	values ('$idRecepcion','$data[1]','$data[2]','$data[3]','$data[4]','$claveBeneficiario','$claseDoc','$tipoDoc','$nroDoc','$apellido','$nombre','$fechaNac','$data[12]','$data[13]','N','$idCaratula','$mjs_error_datos','$row','$idvacuna','$idtaller','$km','$origen','$destino','$hc','$semgesta','$ultimoctrl','$medicoguardia','$coddiag','$diag','$obs','$clavemadre','$sexo','$municipio','$discapacitado','$calle', '$num_calle', '$barrio')";

          $errorsql = mssql_query($SQL20);
          if (!$errorsql) {
            mssql_query($SQLerror);
            mssql_close($conexion);
            fclose($handle);
            echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
              '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=8";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
              '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=8";
					</SCRIPT>';
          }
          $existe_id = 'no';
        }

        $idr = "select max(idbenefrecepcion) as idbenefrecepcion from [20benefrecepcionipos]";
        $result_idr = mssql_query($idr);
        if ($result_idr == false) {
          mssql_query($SQLerror);
          mssql_close($conexion);
          fclose($handle);
          echo "<SCRIPT Language='Javascript'>
				alert('Error.Vuelva a intentarlo');
				errorsimple();
				</SCRIPT>";
        }
        if (mssql_num_rows($result_idr) > 0) {
          while ($f_idr = mssql_fetch_array($result_idr)) {
            $idbenefrecepcion = $f_idr['idbenefrecepcion'];
          }
        }
        mssql_free_result($result_idr);
        ////////////////////////////////////////////////finaliza carga de dos facturas////////////////////////////////////////
      }

      ///////////////////////////SI ES SOLO TRAZADORAS//////////////////////////////////
      if ($data[0] == "T" || $data[0] == "L" && ($data[3] == 1 || $data[3] == 2 || $data[3] ==
        3 || $data[3] == 14 || $data[12] == 'NPE 41')) {
        $menos++;

        $contra++;

        $titulo = "exec Recepcion_CargaEncabezadoTrz '$data[1]','$data[3]','$idRecepcion'";
        $r_titulo = mssql_query($titulo);
        if ($r_titulo == false) {
          mssql_query($SQLerror);
          mssql_close($conexion);
          fclose($handle);
          echo "<SCRIPT Language='Javascript'>
						alert('Error.Vuelva a intentarlo');
						errorsimple();
						</SCRIPT>";
        }
        if (mssql_num_rows($r_titulo) > 0) {
          while ($f_titulo = mssql_fetch_array($r_titulo)) {
            $idtrazadora = $f_titulo['idtrazadora'];
          }
        }
        if (($fuera_prest == 'si' || $nacimerr == 'si') && $data[0] == "L") {
          $Dbenefrece = "UPDATE [20benefrecepcionipos] SET fila='$row',mensaje='Error de datos de Trazadoras'
			 where idprestacion='$data[4]' and cuie='$data[1]' and anomes='$data[2]'";
          $errorsql = mssql_query($Dbenefrece);

          if (!$errorsql) {
            echo '<SCRIPT Language="Javascript">
				  alert("no se debito recepcion");
					</SCRIPT>';
          }
        }

        if ($claseDoc == 'P' || $claseDoc == 'p') {
          $claseDoc = 'R';
        }
        if ($claseDoc == 'A' || $claseDoc == 'a') {
          $claseDoc = 'M';
        }

        ///////////////////////////CONTROL DE SI EXISTE ID PRESTACION//////////////////////////////////
        if ($data[3] == 1) {
          $idr1 = "select idprestacion,clavebeneficiario,eliminado from trzninios 
		where codigoefector='$COEF' and idprestacion='$IPRES' and codnomenclador='$data[12]'";
          $result_idr1 = mssql_query($idr1);
          if ($result_idr1 == false) {
            mssql_query($SQLerror);
            mssql_close($conexion);
            fclose($handle);
            echo "<SCRIPT Language='Javascript'>
				alert('Error.Vuelva a intentarlo');
				errorsimple();
				</SCRIPT>";
          }
          if (mssql_num_rows($result_idr1) > 0) {
            while ($f_idr1 = mssql_fetch_array($result_idr1)) {
              $clavebeneficiario = $f_idr1['clavebeneficiario'];
              $eliminadosi = $f_idr1['eliminado'];
              $ya_esta = 'si';
            }
          }
          mssql_free_result($result_idr1);
        }
        if ($data[3] == 2) {
          $idr2 = "select idprestacion,clavebeneficiario,eliminado  from trzembarazadas 
		where codigoefector='$COEF' and idprestacion='$IPRES'";
          $result_idr2 = mssql_query($idr2);
          if ($result_idr2 == false) {
            mssql_query($SQLerror);
            mssql_close($conexion);
            fclose($handle);
            echo "<SCRIPT Language='Javascript'>
				alert('Error.Vuelva a intentarlo');
				errorsimple();
				</SCRIPT>";
          }
          if (mssql_num_rows($result_idr2) > 0) {
            while ($f_idr2 = mssql_fetch_array($result_idr2)) {
              $clavebeneficiario = $f_idr2['clavebeneficiario'];
              $eliminadosi = $f_idr2['eliminado'];
              $ya_esta = 'si';
            }
          }
          mssql_free_result($result_idr2);
        }
        if ($data[3] == 3) {
          $idr3 = "select idprestacion,clavebeneficiario,eliminado  from trzpartos 
		where codigoefector='$COEF' and idprestacion='$IPRES'";
          $result_idr3 = mssql_query($idr3);
          if ($result_idr3 == false) {
            mssql_query($SQLerror);
            mssql_close($conexion);
            fclose($handle);
            echo "<SCRIPT Language='Javascript'>
				alert('Error.Vuelva a intentarlo');
				errorsimple();
				</SCRIPT>";
          }
          if (mssql_num_rows($result_idr3) > 0) {
            while ($f_idr3 = mssql_fetch_array($result_idr3)) {
              $clavebeneficiario = $f_idr3['clavebeneficiario'];
              $eliminadosi = $f_idr3['eliminado'];
              $ya_esta = 'si';
            }
          }
          mssql_free_result($result_idr3);
        }
        if ($data[3] == 14) {
          $idr1 = "select idprestacion,clavebeneficiario,eliminado from trzmuertes 
		where codigoefector='$COEF' and idprestacion='$IPRES'";
          $result_idr1 = mssql_query($idr1);
          if ($result_idr1 == false) {
            mssql_query($SQLerror);
            mssql_close($conexion);
            fclose($handle);
            echo "<SCRIPT Language='Javascript'>
				alert('Error.Vuelva a intentarlo');
				errorsimple();
				</SCRIPT>";
          }
          if (mssql_num_rows($result_idr1) > 0) {
            while ($f_idr1 = mssql_fetch_array($result_idr1)) {
              $clavebeneficiario = $f_idr1['clavebeneficiario'];
              $eliminadosi = $f_idr1['eliminado'];
              $ya_esta = 'si';
            }
          }
          mssql_free_result($result_idr1);
        }
        ///////////////////////////ACTUALIZA DATOS//////////////////////////////////
        if ($ya_esta == 'si') {

          if ($data[3] == 1) {
            /*NIÑOS*/

            if ($data[30] == null) {
              $peso = '';
              $error = 'si';
              $descripcion_error .= ',peso';
            } else {
              $peso = $data[30];
            }
            if ($data[31] == null && $data[12] != 'HAM 00') {
              $percpesoedad = '';
              $error = 'si';
              $descripcion_error .= ',percpesoedad';
            } else {
              $percpesoedad = $data[31];
            }
            if ($data[33] == null && $data[12] != 'HAM 00') {
              $perctallaedad = '';
              $error = 'si';
              $descripcion_error .= ',perctallaedad';
            } else {
              $perctallaedad = $data[33];
            }
            if ($menor >= 10000) {
              /*mayor de 1 año*/
              if ($data[36] == null && $data[12] != 'HAM 00') {
                $percpesotalla = '';
                $error = 'si';
                $descripcion_error .= ',percpesotalla';
              } else {
                $percpesotalla = $data[36];
              }
            } else {
              $percpesotalla = $data[36];
            }
            if ($data[32] == null) {
              $talla = '';
              $error = 'si';
              $descripcion_error .= ',talla';
            } else {
              $talla = $data[32];
            }
            if ($menor < 10000) {
              /*menor de 1 año*/
              if ($data[34] == null && $data[12] != 'HAM 00') {
                $perimcef = '';
                $error = 'si';
                $descripcion_error .= ',perimcef';
              } else {
                $perimcef = $data[34];
              }
              if ($data[35] == null && $data[12] != 'HAM 00') {
                $percperimcefedad = '';
                $error = 'si';
                $descripcion_error .= ',percperimcefedad';
              } else {
                $percperimcefedad = $data[35];
              }
            } else {
              $percperimcefedad = $data[35];
              $perimcef = $data[34];
            }

            if ($data[37] == null) {
              $antisarampionosa = null;
            } else {
              $antisarampionosa = $data[37];
            }
            if ($data[38] == null) {
              $fechaobito = '';
            } else {
              $fechaobito = $data[38];
            }
            if ($data[39] == null) {
              $nrocontrol = '';
            } else {
              $nrocontrol = $data[39];
            }
            /*graba beneficiarios*/
            if ($error == 'no') {

              $SQLnU = "UPDATE TrzNinios SET ClaveBeneficiario='$claveBeneficiario',ClaseDocumento='$claseDoc',TipoDocumento='$tipoDoc',
	NumeroDocumento='$nroDoc',Apellido='$apellido',Nombre='$nombre',FechaNacimiento='$fechaNac',FechaControl='$data[13]'
,Peso='$peso',Talla='$talla',PerimetroCefalico='$perimcef',PercentiloPesoEdad='$percpesoedad',
PercentiloTallaEdad='$perctallaedad',PercentiloPerimCefalicoEdad='$percperimcefedad',PercentiloPesoTalla='$percpesotalla'
,FechaVacunacion='$antisarampionosa',fechaobito='$fechaobito',ncontrolanual='$nrocontrol',percentilo_imc='$percentilo_imc',imc='$imc',
idbenefrecepcion='$idbenefrecepcion',eliminado='$eliminado',discapacitado='$discapacitado',cod_aldea='$cod_aldea',descrip_aldea='$descrip_aldea'
,calle='$calle', num_calle='$num_calle', barrio='$barrio',municipio='$municipio'
 where idprestacion='$data[4]' and codigoefector='$data[1]' and codnomenclador='$data[12]'";

              $errorsql = mssql_query($SQLnU);
              if (!$errorsql) {
                /*	$x=fopen("archivo.txt","w");   $as='**'.$SQLnU;   if($x)    {     fwrite($x,$as);    }*/
                mssql_query($SQLerror);
                mssql_close($conexion);
                fclose($handle);
                echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=28";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=28";
					</SCRIPT>';
              }

            }
          }
          if ($data[3] == 2) {
            /*EMBARAZADAS*/
            if ($data[18] == null) {
              $fecha1control = '';
              $error = 'si';
              $descripcion_error .= ',fecha1control';
            } else {
              $fecha1control = $data[18];
            }
            if ($data[19] == null) {
              $SemanaGestacion1control = '';
              $error = 'si';
              $descripcion_error .= ',SemanaGestacion1control';
            } else {
              $SemanaGestacion1control = $data[19];
            }
            if ($data[28] == null) {
              $fpp = '';
              $error = 'si';
              $descripcion_error .= ',fpp';
            } else {
              $fpp = $data[28];
            }
            if ($data[29] == null) {
              $fum = '';
              $error = 'si';
              $descripcion_error .= ',fum';
            } else {
              $fum = $data[29];
            }
            if ($data[20] == null) {
              $estadonutricional = '';
            } else {
              $estadonutricional = $data[20];
            }
            if ($data[21] == null) {
              $antitetanica1dosis = '';
            } else {
              $antitetanica1dosis = $data[21];
            }
            if ($data[22] == null) {
              $antitetanica2dosis = '';
            } else {
              $antitetanica2dosis = $data[22];
            }
            if ($data[23] == null) {
              $vdrl = '';
            } else {
              $vdrl = $data[23];
            }
            if ($data[24] == null) {
              $hiv = '';
            } else {
              $hiv = $data[24];
            }
            if ($data[25] == null) {
              $eco = '';
            } else {
              $eco = $data[25];
            }
            if ($data[26] == null) {
              $fechaobito = '';
            } else {
              $fechaobito = $data[26];
            }
            if ($data[27] == null) {
              $nrocontrolactual = '';
            } else {
              $nrocontrolactual = $data[27];
            }
            if ($data[51] == null) {
              $attvigente = '';
            } else {
              $attvigente = $data[51];
            }
            /*graba beneficiarios*/
            if ($error == 'no') {

              $SQLeU = "UPDATE TrzEmbarazadas SET ClaveBeneficiario='$claveBeneficiario',TipoDocumento='$tipoDoc',NumeroDocumento='$nroDoc'
,Apellido='$apellido',Nombre='$nombre',FechaControl='$data[13]',FechaMenstruacion='$fum',FechaParto='$fpp',
FechaControlPrenatal='$fecha1control',semanagestacion1control='$SemanaGestacion1control',estadonutricional='$estadonutricional'
,antitetanica1dosis='$antitetanica1dosis',antitetanica2dosis='$antitetanica2dosis',vdrl='$vdrl',hiv='$hiv',eco='$eco',
fechaobito='$fechaobito',ncontrolactual='$nrocontrolactual',idbenefrecepcion='$idbenefrecepcion',eliminado='$eliminado',attvigente='$attvigente'
,tamax='$tamax',tamin='$tamin',au='$au',peso_mem02='$peso_mem02',FechaNacimiento='$fechaNac',discapacitado='$discapacitado',municipio='$municipio'
where idprestacion='$data[4]' and codigoefector='$data[1]'";

              $errorsql = mssql_query($SQLeU);
              if (!$errorsql) {
                /*		$x=fopen("archivo.txt","w");
                $as='**'.$SQLeU;
                
                if($x)
                {
                fwrite($x,$as);
                }*/
                mssql_query($SQLerror);
                mssql_close($conexion);
                fclose($handle);
                echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=29";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=29";
					</SCRIPT>';
              }

            }
          }
          if ($data[3] == 3) {
            /*PARTOS*/
            if ($error == 'si') {
              $ojo = 'si';
            }
            if ($data[41] == null) {
              $apgar = '';
              $error = 'si';
              $descripcion_error .= ',apgar';
            } else {
              $apgar = $data[41];
            }

            //*si desconocido null o distinto de N y S; debita*/
            if ($data[47] == null || ($data[47] != 'N' && $data[47] != 'S')) {
              $desconocido = '';
              $error = 'si';
              $descripcion_error .= ',OBBconocido';
            } else {
              $desconocido = $data[47];

              /*si desconocido no y fecha obito no null paga*/
              if ($apgar == '0' && $desconocido == 'N') {
                if ($ojo != 'si') {
                  $error = 'no';
                }
                $descripcion_error = str_replace(',apgar', '', $descripcion_error);
              }
              /*si desconocido si y fecha obito null paga*/
              if ($apgar == '0' && $desconocido == 'S' && $data[44] != null) {
                if ($ojo != 'si') {
                  $error = 'no';
                }
                $descripcion_error = str_replace(',apgar', '', $descripcion_error);
              }
            }
            /*FIN _si desconocido null o distinto de N y S; debita*/

            if ($data[40] == null || $data[40] == '0') {
              $pesoalnacer = '';
              $error = 'si';
              $descripcion_error .= ',pesoalnacer';
            } else {
              $pesoalnacer = $data[40];
            }

            if ($data[42] == null) {
              $vdrl = '';
              $error = 'si';
              $descripcion_error .= ',vdrl';
            } else {
              $vdrl = $data[42];
            }
            if ($data[43] == null) {
              $antitetanica = '';
              $error = 'si';
              $descripcion_error .= ',antitetanica';
            } else {
              $antitetanica = $data[43];
            }
            if ($antitetanica == 'S' || $antitetanica == 'N') {
            } else {
              $error = 'si';
              $descripcion_error .= ',antitetanica';
            }
            if ($data[46] == null) {
              $consejeria = '';
            } else {
              $consejeria = $data[46];
            }
            if ($data[44] == null) {
              $obitohijo = '';
            } else {
              $obitohijo = $data[44];
            }
            if ($data[45] == null) {
              $obitomadre = '';
            } else {
              $obitomadre = $data[45];
            }

            /*graba beneficiarios*/
            if ($error == 'no') {

              $SQLpU = "UPDATE TrzPartos SET ClaveBeneficiario='$claveBeneficiario',TipoDocumento='$tipoDoc',NumeroDocumento='$nroDoc',
Apellido='$apellido',Nombre='$nombre',FechaParto='$data[13]',APGAR5='$apgar',Peso='$pesoalnacer',Antitetanica='$antitetanica',
FechaConsejeria='$consejeria',VDRL='$vdrl',obitobebe='$obitohijo',obitomadre='$obitomadre',FechaNacimiento='$fechaNac',discapacitado='$discapacitado'
,idbenefrecepcion='$idbenefrecepcion',eliminado='$eliminado',obbdesconocido='$data[47]',talla_rn='$talla_rn',perimcef_rn='$perimcef_rn',municipio='$municipio'
 where idprestacion='$data[4]' and codigoefector='$data[1]'";

              $errorsql = mssql_query($SQLpU);
              if (!$errorsql) {
                mssql_query($SQLerror);
                mssql_close($conexion);
                /*$x=fopen("archivo.txt","w");   $as='**'.$SQLn;   if($x)    {     fwrite($x,$as);    }*/
                fclose($handle);
                echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=30";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=30";
					</SCRIPT>';
              }

            }
          }
          if ($data[3] == 14) {
            /*Muertes*/
            /*graba beneficiarios*/
            if ($error == 'no') {

              $SQLmU = "UPDATE Trzmuertes SET ClaveBeneficiario='$claveBeneficiario',ClaseDocumento='$claseDoc',TipoDocumento='$tipoDoc'
	,	NumeroDocumento='$nroDoc',Apellido='$apellido',Nombre='$nombre',FechaNacimiento='$fechaNac',comitelocal='$data[13]'
,caso='$caso',fechadefuncion='$fdefuncion',fppmuerte='$fppmuerte',idbenefrecepcion='$idbenefrecepcion',eliminado='$eliminado'
,municipio='$municipio'
 where idprestacion='$data[4]' and codigoefector='$data[1]'";

              $errorsql = mssql_query($SQLmU);
              if (!$errorsql) {
                mssql_query($SQLerror);
                mssql_close($conexion);
                /*$x=fopen("archivo.txt","w");   $as='**'.$SQLmU;   if($x)    {     fwrite($x,$as);    }*/
                fclose($handle);
                echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=30x1";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=30x1";
					</SCRIPT>';
              }
            }
          }

        } ///////////////////////////FIN ACTUALIZA DATOS//////////////////////////////////

        if ($ya_esta == 'no') {
          if ($data[12] == 'NPE 41' || $data[12] == 'RPE 93') {
            /*ANTISARAMPIONOSA*/

            /*graba beneficiarios*/

            $SQLnkl = "insert into Trzantisarampionosa
	 (CodigoEfector,ClaveBeneficiario,ClaseDocumento,TipoDocumento,NumeroDocumento,Apellido,Nombre,
FechaNacimiento,FechaControl,FechaVacunacion,idtrazadora,idprestacion,sexo,municipio,discapacitado)
values
('$data[1]','$claveBeneficiario','$claseDoc','$tipoDoc','$nroDoc','$apellido','$nombre','$fechaNac','$data[13]','$data[13]','$idtrazadora','$data[4]','$sexo','$municipio','$discapacitado')";

            $errorsql = mssql_query($SQLnkl);
            if (!$errorsql) {
              mssql_query($SQLerror);
              mssql_close($conexion);
              fclose($handle);
              echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=31";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=31";
					</SCRIPT>';
            }
          }
          if ($data[3] == 1) {
            /*NIÑOS*/

            if ($data[30] == null) {
              $peso = '';
              $error = 'si';
              $descripcion_error .= ',peso';
            } else {
              $peso = $data[30];
            }
            if ($data[31] == null && $data[12] != 'HAM 00') {
              $percpesoedad = '';
              $error = 'si';
              $descripcion_error .= ',percpesoedad';
            } else {
              $percpesoedad = $data[31];
            }
            if ($data[33] == null && $data[12] != 'HAM 00') {
              $perctallaedad = '';
              $error = 'si';
              $descripcion_error .= ',perctallaedad';
            } else {
              $perctallaedad = $data[33];
            }
            if ($menor >= 10000) {
              /*mayor de 1 año*/
              if ($data[36] == null && $data[12] != 'HAM 00') {
                $percpesotalla = '';
                $error = 'si';
                $descripcion_error .= ',percpesotalla';
              } else {
                $percpesotalla = $data[36];
              }
            } else {
              $percpesotalla = $data[36];
            }
            if ($data[32] == null) {
              $talla = '';
              $error = 'si';
              $descripcion_error .= ',talla';
            } else {
              $talla = $data[32];
            }
            if ($menor < 10000) {
              /*menor de 1 año*/
              if ($data[34] == null && $data[12] != 'HAM 00') {
                $perimcef = '';
                $error = 'si';
                $descripcion_error .= ',perimcef';
              } else {
                $perimcef = $data[34];
              }
              if ($data[35] == null && $data[12] != 'HAM 00') {
                $percperimcefedad = '';
                $error = 'si';
                $descripcion_error .= ',percperimcefedad';
              } else {
                $percperimcefedad = $data[35];
              }
            } else {
              $percperimcefedad = $data[35];
              $perimcef = $data[34];
            }

            if ($data[37] == null) {
              $antisarampionosa = null;
            } else {
              $antisarampionosa = $data[37];
            }
            if ($data[38] == null) {
              $fechaobito = '';
            } else {
              $fechaobito = $data[38];
            }
            if ($data[39] == null) {
              $nrocontrol = '';
            } else {
              $nrocontrol = $data[39];
            }
            /*graba beneficiarios*/
            if ($error == 'no') {

              $SQLn = "insert into TrzNinios (CodigoEfector,ClaveBeneficiario,ClaseDocumento,TipoDocumento,NumeroDocumento,Apellido,Nombre,
FechaNacimiento,FechaControl,Peso,Talla,PerimetroCefalico,PercentiloPesoEdad,PercentiloTallaEdad,PercentiloPerimCefalicoEdad,
PercentiloPesoTalla,FechaVacunacion,fechaobito,ncontrolanual,idtrazadora,idprestacion,eliminado,idbenefrecepcion,sexo,municipio,percentilo_imc,imc,discapacitado
,cod_aldea,descrip_aldea,calle, num_calle, barrio,codnomenclador)
values ('$data[1]','$claveBeneficiario','$claseDoc','$tipoDoc','$nroDoc','$apellido','$nombre','$fechaNac','$data[13]','$peso'
,'$talla','$perimcef','$percpesoedad','$perctallaedad','$percperimcefedad','$percpesotalla','$antisarampionosa','$fechaobito',
'$nrocontrol','$idtrazadora','$data[4]','N','$idbenefrecepcion','$sexo','$municipio','$percentilo_imc','$imc','$discapacitado'
,'$cod_aldea','$descrip_aldea','$calle', '$num_calle', '$barrio','$data[12]')";

              $errorsql = mssql_query($SQLn);
              if (!$errorsql) {
                /*$x=fopen("archivo.txt","w");   $as='**'.$SQLn;   if($x)    {     fwrite($x,$as);    }*/
                mssql_query($SQLerror);
                mssql_close($conexion);
                fclose($handle);
                echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=32";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=32";
					</SCRIPT>';
              }
              $SQLnD = "DELETE FROM TrzNiniostmp  where idprestacion='$data[4]' and codigoefector='$data[1]' and codnomenclador='$data[12]'";

              $errorsql = mssql_query($SQLnD);
              if (!$errorsql) {
                mssql_query($SQLerror);
                mssql_close($conexion);
                fclose($handle);
                echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=33";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=33";
					</SCRIPT>';
              }

            }
          }
          if ($data[3] == 2) {
            /*EMBARAZADAS*/
            if ($data[18] == null) {
              $fecha1control = '';
              $error = 'si';
              $descripcion_error .= ',fecha1control';
            } else {
              $fecha1control = $data[18];
            }
            if ($data[19] == null) {
              $SemanaGestacion1control = '';
              $error = 'si';
              $descripcion_error .= ',SemanaGestacion1control';
            } else {
              $SemanaGestacion1control = $data[19];
            }
            if ($data[28] == null) {
              $fpp = '';
              $error = 'si';
              $descripcion_error .= ',fpp';
            } else {
              $fpp = $data[28];
            }
            if ($data[29] == null) {
              $fum = '';
              $error = 'si';
              $descripcion_error .= ',fum';
            } else {
              $fum = $data[29];
            }
            if ($data[20] == null) {
              $estadonutricional = '';
            } else {
              $estadonutricional = $data[20];
            }
            if ($data[21] == null) {
              $antitetanica1dosis = '';
            } else {
              $antitetanica1dosis = $data[21];
            }
            if ($data[22] == null) {
              $antitetanica2dosis = '';
            } else {
              $antitetanica2dosis = $data[22];
            }
            if ($data[23] == null) {
              $vdrl = '';
            } else {
              $vdrl = $data[23];
            }
            if ($data[24] == null) {
              $hiv = '';
            } else {
              $hiv = $data[24];
            }
            if ($data[25] == null) {
              $eco = '';
            } else {
              $eco = $data[25];
            }
            if ($data[26] == null) {
              $fechaobito = '';
            } else {
              $fechaobito = $data[26];
            }
            if ($data[27] == null) {
              $nrocontrolactual = '';
            } else {
              $nrocontrolactual = $data[27];
            }
            if ($data[51] == null) {
              $attvigente = '';
            } else {
              $attvigente = $data[51];
            }
            /*graba beneficiarios*/
            if ($error == 'no') {

              $SQLe = "insert into TrzEmbarazadas (CodigoEfector,ClaveBeneficiario,TipoDocumento,NumeroDocumento,Apellido,Nombre,FechaControl,
SemanaGestacion,FechaMenstruacion,FechaParto,FechaControlPrenatal,semanagestacion1control,estadonutricional
,antitetanica1dosis,antitetanica2dosis,vdrl,hiv,eco,fechaobito,ncontrolactual,idtrazadora,idprestacion,eliminado,idbenefrecepcion,attvigente,
tamax,tamin,au,peso_mem02,FechaNacimiento,discapacitado,municipio)
values ('$data[1]','$claveBeneficiario','$tipoDoc','$nroDoc','$apellido','$nombre','$data[13]','','$fum','$fpp','$fecha1control',
	'$SemanaGestacion1control','$estadonutricional','$antitetanica1dosis','$antitetanica2dosis','$vdrl','$hiv','$eco','$fechaobito'
	,'$nrocontrolactual','$idtrazadora','$data[4]','N','$idbenefrecepcion','$attvigente','$tamax','$tamin','$au','$peso_mem02','$fechaNac','$discapacitado','$municipio')";

              $errorsql = mssql_query($SQLe);
              if (!$errorsql) {
                mssql_query($SQLerror);
                mssql_close($conexion);
                fclose($handle);
                echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=34";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=34";
					</SCRIPT>';
              }
              $SQLeD = "DELETE FROM TrzEmbarazadastmp  where idprestacion='$data[4]' and codigoefector='$data[1]'";

              $errorsql = mssql_query($SQLeD);
              if (!$errorsql) {
                mssql_query($SQLerror);
                mssql_close($conexion);
                fclose($handle);
                echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=35";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=35";
					</SCRIPT>';
              }

            }
          }
          if ($data[3] == 3) {
            /*PARTOS*/
            if ($error == 'si') {
              $ojo = 'si';
            }
            if ($data[41] == null) {
              $apgar = '';
              $error = 'si';
              $descripcion_error .= ',apgar';
            } else {
              $apgar = $data[41];
            }

            /*si desconocido null o distinto de N y S; debita*/
            if ($data[47] == null || ($data[47] != 'N' && $data[47] != 'S')) {
              $desconocido = '';
              $error = 'si';
              $descripcion_error .= ',OBBconocido';
            } else {
              $desconocido = $data[47];

              /*si desconocido no y fecha obito no null paga*/
              if ($apgar == '0' && $desconocido == 'N') {
                if ($ojo != 'si') {
                  $error = 'no';
                }
                $descripcion_error = str_replace(',apgar', '', $descripcion_error);
              }
              /*si desconocido si y fecha obito null paga*/
              if ($apgar == '0' && $desconocido == 'S' && $data[44] != null) {
                if ($ojo != 'si') {
                  $error = 'no';
                }
                $descripcion_error = str_replace(',apgar', '', $descripcion_error);
              }
            }
            /*FIN _si desconocido null o distinto de N y S; debita*/

            if ($data[40] == null || $data[40] == '0') {
              $pesoalnacer = '';
              $error = 'si';
              $descripcion_error .= ',pesoalnacer';
            } else {
              $pesoalnacer = $data[40];
            }

            if ($data[42] == null) {
              $vdrl = '';
              $error = 'si';
              $descripcion_error .= ',vdrl';
            } else {
              $vdrl = $data[42];
            }
            if ($data[43] == null) {
              $antitetanica = '';
              $error = 'si';
              $descripcion_error .= ',antitetanica';
            } else {
              $antitetanica = $data[43];
            }
            if ($antitetanica == 'S' || $antitetanica == 'N') {
            } else {
              $error = 'si';
              $descripcion_error .= ',antitetanica';
            }
            if ($data[46] == null) {
              $consejeria = '';
            } else {
              $consejeria = $data[46];
            }
            if ($data[44] == null) {
              $obitohijo = '';
            } else {
              $obitohijo = $data[44];
            }
            if ($data[45] == null) {
              $obitomadre = '';
            } else {
              $obitomadre = $data[45];
            }

            /*graba beneficiarios*/
            if ($error == 'no') {

              $SQLp = "insert into TrzPartos (CodigoEfector,ClaveBeneficiario,TipoDocumento,NumeroDocumento,Apellido,Nombre,
	FechaParto,APGAR5,Peso,Antitetanica,FechaConsejeria,VDRL,obitobebe,obitomadre,idtrazadora,idprestacion,eliminado,idbenefrecepcion,ObbDesconocido
	,talla_rn,perimcef_rn,FechaNacimiento,discapacitado,municipio)
	values ('$data[1]','$claveBeneficiario','$tipoDoc','$nroDoc','$apellido','$nombre','$data[13]','$apgar',
	'$pesoalnacer','$antitetanica','$consejeria','$vdrl','$obitohijo','$obitomadre','$idtrazadora','$data[4]','N','$idbenefrecepcion','$desconocido'
	,'$talla_rn','$perimcef_rn','$fechaNac','$discapacitado','$municipio')";

              $errorsql = mssql_query($SQLp);
              if (!$errorsql) {
                mssql_query($SQLerror);
                mssql_close($conexion);
                fclose($handle);
                echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=36";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=36";
					</SCRIPT>';
              }

              $SQLpD = "DELETE FROM TrzPartostmp  where idprestacion='$data[4]' and codigoefector='$data[1]'";

              $errorsql = mssql_query($SQLpD);
              if (!$errorsql) {
                mssql_query($SQLerror);
                mssql_close($conexion);
                fclose($handle);
                echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=37";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=37";
					</SCRIPT>';
              }

            }
          }
          if ($data[3] == 14) {
            /*MUERTES*/
            if ($error == 'no') {
              $SQLm = "insert into Trzmuertes (CodigoEfector,ClaveBeneficiario,ClaseDocumento,TipoDocumento,NumeroDocumento,Apellido,Nombre,
FechaNacimiento,comitelocal,caso,fechadefuncion,fppmuerte,idtrazadora,idbenefrecepcion,idprestacion,sexo,municipio) values ('$data[1]','$claveBeneficiario','$claseDoc','$tipoDoc','$nroDoc','$apellido','$nombre','$fechaNac','$data[13]'
,'$caso','$fdefuncion','$fppmuerte','$idtrazadora','$idbenefrecepcion','$data[4]','$sexo','$municipio')";

              $errorsql = mssql_query($SQLm);
              if (!$errorsql) {
                mssql_query($SQLerror);
                mssql_close($conexion);
                fclose($handle);
                echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=37x1";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=37x1";
					</SCRIPT>';
              }

              $SQLmD = "DELETE FROM Trzmuertestmp  where idprestacion='$data[4]' and codigoefector='$data[1]'";

              $errorsql = mssql_query($SQLmD);
              if (!$errorsql) {
                mssql_query($SQLerror);
                mssql_close($conexion);
                fclose($handle);
                echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=37x2";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=37x2";
					</SCRIPT>';
              }
            }
          }
        }
        $ya_estaTMP = 'no';
        ////////////////////////////CONTROL ID PRESTACION EN TEMPORAL
        if ($data[3] == 1) {
          $idr11 = "select idprestacion,clavebeneficiario from trzniniosTMP 
		where codigoefector='$COEF' and idprestacion='$IPRES' and codnomenclador='$data[12]'";
          $result_idr11 = mssql_query($idr11);
          if ($result_idr11 == false) {
            mssql_query($SQLerror);
            mssql_close($conexion);
            fclose($handle);
            echo "<SCRIPT Language='Javascript'>
				alert('Error.Vuelva a intentarlo');
				errorsimple();
				</SCRIPT>";
          }
          if (mssql_num_rows($result_idr11) > 0) {
            while ($f_idr11 = mssql_fetch_array($result_idr11)) {
              $clavebeneficiarioTMP = $f_idr11['clavebeneficiario'];
              $ya_estaTMP = 'si';
            }
          }
          mssql_free_result($result_idr11);
        }
        if ($data[3] == 2) {
          $idr21 = "select idprestacion,clavebeneficiario  from trzembarazadasTMP
		where codigoefector='$COEF' and idprestacion='$IPRES'";
          $result_idr21 = mssql_query($idr21);
          if ($result_idr21 == false) {
            mssql_query($SQLerror);
            mssql_close($conexion);
            fclose($handle);
            echo "<SCRIPT Language='Javascript'>
				alert('Error.Vuelva a intentarlo');
				errorsimple();
				</SCRIPT>";
          }
          if (mssql_num_rows($result_idr21) > 0) {
            while ($f_idr21 = mssql_fetch_array($result_idr21)) {
              $clavebeneficiarioTMP = $f_idr21['clavebeneficiario'];
              $ya_estaTMP = 'si';
            }
          }
          mssql_free_result($result_idr21);
        }
        if ($data[3] == 3) {
          $idr31 = "select idprestacion,clavebeneficiario  from trzpartosTMP
		where codigoefector='$COEF' and idprestacion='$IPRES'";
          $result_idr31 = mssql_query($idr31);
          if ($result_idr31 == false) {
            mssql_query($SQLerror);
            mssql_close($conexion);
            fclose($handle);
            echo "<SCRIPT Language='Javascript'>
				alert('Error.Vuelva a intentarlo');
				errorsimple();
				</SCRIPT>";
          }
          if (mssql_num_rows($result_idr31) > 0) {
            while ($f_idr31 = mssql_fetch_array($result_idr31)) {
              $clavebeneficiarioTMP = $f_idr31['clavebeneficiario'];
              $ya_estaTMP = 'si';
            }
          }
          mssql_free_result($result_idr31);
        }
        if ($data[3] == 14) {
          $idr11 = "select idprestacion,clavebeneficiario from trzmuertesTMP 
		where codigoefector='$COEF' and idprestacion='$IPRES'";
          $result_idr11 = mssql_query($idr11);
          if ($result_idr11 == false) {
            mssql_query($SQLerror);
            mssql_close($conexion);
            fclose($handle);
            echo "<SCRIPT Language='Javascript'>
				alert('Error.Vuelva a intentarlo');
				errorsimple();
				</SCRIPT>";
          }
          if (mssql_num_rows($result_idr11) > 0) {
            while ($f_idr11 = mssql_fetch_array($result_idr11)) {
              $clavebeneficiarioTMP = $f_idr11['clavebeneficiario'];
              $ya_estaTMP = 'si';
            }
          }
          mssql_free_result($result_idr11);
        }
        ///////////////////////////////SI TIENE ERROR///////////////////////////////
        if ($error == 'si') {
          $cuenta_error++;
          $errort++;
          $descripcion_error .= '-' . $row;
          if ($ya_estaTMP == 'si') {
            if ($data[3] == 1) {
              /*NIÑOS*/
              $ni++;
              $SQLnU = "UPDATE TrzNiniostmp SET ClaveBeneficiario='$claveBeneficiario',ClaseDocumento='$claseDoc',TipoDocumento='$tipoDoc',
	NumeroDocumento='$nroDoc',Apellido='$apellido',Nombre='$nombre',FechaNacimiento='$fechaNac',FechaControl='$data[13]'
,Peso='$peso',Talla='$talla',PerimetroCefalico='$perimcef',PercentiloPesoEdad='$percpesoedad',
PercentiloTallaEdad='$perctallaedad',PercentiloPerimCefalicoEdad='$percperimcefedad',PercentiloPesoTalla='$percpesotalla'
,FechaVacunacion='$antisarampionosa',fechaobito='$fechaobito',ncontrolanual='$nrocontrol',percentilo_imc='$percentilo_imc',imc='$imc',
idbenefrecepcion='$idbenefrecepcion',mjs='$descripcion_error',discapacitado='$discapacitado',cod_aldea='$cod_aldea',descrip_aldea='$descrip_aldea'
,calle='$calle', num_calle='$num_calle', barrio='$barrio',municipio='$municipio'
 where idprestacion='$data[4]' and codigoefector='$data[1]' and codnomenclador='$data[12]'";

              $errorsql = mssql_query($SQLnU);
              if (!$errorsql) {
                mssql_query($SQLerror);
                mssql_close($conexion);
                fclose($handle);
                echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=38";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=38";
					</SCRIPT>';
              }

            }
            if ($data[3] == 2) {
              /*EMBARAZADAS*/
              $em++;
              /*graba beneficiarios*/
              $SQLeU = "UPDATE TrzEmbarazadastmp SET ClaveBeneficiario='$claveBeneficiario',TipoDocumento='$tipoDoc',NumeroDocumento='$nroDoc'
,Apellido='$apellido',Nombre='$nombre',FechaControl='$data[13]',FechaMenstruacion='$fum',FechaParto='$fpp',
FechaControlPrenatal='$fecha1control',semanagestacion1control='$SemanaGestacion1control',estadonutricional='$estadonutricional'
,antitetanica1dosis='$antitetanica1dosis',antitetanica2dosis='$antitetanica2dosis',vdrl='$vdrl',hiv='$hiv',eco='$eco',
fechaobito='$fechaobito',ncontrolactual='$nrocontrolactual',idbenefrecepcion='$idbenefrecepcion',mjs='$descripcion_error',tamax='$tamax',tamin='$tamin',au='$au'
,peso_mem02='$peso_mem02',FechaNacimiento='$fechaNac',discapacitado='$discapacitado',municipio='$municipio'
where idprestacion='$data[4]' and codigoefector='$data[1]'";

              $errorsql = mssql_query($SQLeU);
              if (!$errorsql) {
                mssql_query($SQLerror);
                mssql_close($conexion);
                fclose($handle);
                echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=39";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=39";
					</SCRIPT>';
              }
            }
            if ($data[3] == 3) {
              /*PARTOS*/
              $pa++;
              /*graba beneficiarios*/
              $SQLpU = "UPDATE TrzPartostmp SET ClaveBeneficiario='$claveBeneficiario',TipoDocumento='$tipoDoc',NumeroDocumento='$nroDoc',
Apellido='$apellido',Nombre='$nombre',FechaParto='$data[13]',APGAR5='$apgar',Peso='$pesoalnacer',Antitetanica='$antitetanica',
FechaConsejeria='$consejeria',VDRL='$vdrl',obitobebe='$obitohijo',obitomadre='$obitomadre',mjs='$descripcion_error',discapacitado='$discapacitado'
,idbenefrecepcion='$idbenefrecepcion',ObbDesconocido='$desconocido',talla_rn='$talla_rn',perimcef_rn='$perimcef_rn',FechaNacimiento='$fechaNac'
,municipio='$municipio'
 where idprestacion='$data[4]' and codigoefector='$data[1]'";

              $errorsql = mssql_query($SQLpU);
              if (!$errorsql) {
                mssql_query($SQLerror);
                mssql_close($conexion);
                fclose($handle);
                echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=40";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=40";
					</SCRIPT>';
              }
            }
            if ($data[3] == 14) {
              /*Muertes*/
              $mu++;
              /*graba beneficiarios*/

              $SQLmU = "UPDATE Trzmuertestmp SET ClaveBeneficiario='$claveBeneficiario',ClaseDocumento='$claseDoc',TipoDocumento='$tipoDoc',
	NumeroDocumento='$nroDoc',Apellido='$apellido',Nombre='$nombre',FechaNacimiento='$fechaNac',comitelocal='$data[13]'
,caso='$caso',fechadefuncion='$fdefuncion',fppmuerte='$fppmuerte',idbenefrecepcion='$idbenefrecepcion',mjs='$descripcion_error'
,municipio='$municipio'
 where idprestacion='$data[4]' and codigoefector='$data[1]'";

              $errorsql = mssql_query($SQLmU);
              if (!$errorsql) {
                mssql_query($SQLerror);
                mssql_close($conexion);
                fclose($handle);
                echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=40x1";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=40x1";
					</SCRIPT>';
              }

            }
          }
          if ($ya_estaTMP == 'no') {

            if ($data[3] == 1) {
              /*NIÑOS*/
              $ni++;
              /*graba beneficiarios*/

              $SQLn = "insert into TrzNiniostmp (CodigoEfector,ClaveBeneficiario,ClaseDocumento,TipoDocumento,NumeroDocumento,Apellido,Nombre,
FechaNacimiento,FechaControl,Peso,Talla,PerimetroCefalico,PercentiloPesoEdad,PercentiloTallaEdad,PercentiloPerimCefalicoEdad,
PercentiloPesoTalla,FechaVacunacion,fechaobito,ncontrolanual,idtrazadora,mjs,idprestacion,idbenefrecepcion,sexo,municipio,percentilo_imc,imc,discapacitado
,cod_aldea,descrip_aldea,calle, num_calle, barrio,codnomenclador)
values ('$data[1]','$claveBeneficiario','$claseDoc','$tipoDoc','$nroDoc','$apellido','$nombre','$fechaNac','$data[13]','$peso'
,'$talla','$perimcef','$percpesoedad','$perctallaedad','$percperimcefedad','$percpesotalla','$antisarampionosa','$fechaobito',
'$nrocontrol','$idtrazadora','$descripcion_error','$data[4]','$idbenefrecepcion','$sexo','$municipio','$percentilo_imc','$imc','$discapacitado'
,'$cod_aldea','$descrip_aldea','$calle', '$num_calle', '$barrio','$data[12]')";

              $errorsql = mssql_query($SQLn);
              if (!$errorsql) {
                $x = fopen("archivo.txt", "w");
                $as = '**' . $SQLn;
                if ($x) {
                  fwrite($x, $as);
                }
                mssql_query($SQLerror);
                mssql_close($conexion);
                fclose($handle);
                echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=41";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=41";
					</SCRIPT>';
              }
            }
            if ($data[3] == 2) {
              /*EMBARAZADAS*/
              $em++;
              /*graba beneficiarios*/

              $SQLe = "insert into TrzEmbarazadastmp (CodigoEfector,ClaveBeneficiario,TipoDocumento,NumeroDocumento,Apellido,Nombre,FechaControl,
SemanaGestacion,FechaMenstruacion,FechaParto,FechaControlPrenatal,semanagestacion1control,estadonutricional
,antitetanica1dosis,antitetanica2dosis,vdrl,hiv,eco,fechaobito,ncontrolactual,idtrazadora,mjs,idprestacion,idbenefrecepcion,
tamax,tamin,au,peso_mem02,FechaNacimiento,discapacitado,municipio)
values ('$data[1]','$claveBeneficiario','$tipoDoc','$nroDoc','$apellido','$nombre','$data[13]','','$fum','$fpp','$fecha1control',
	'$SemanaGestacion1control','$estadonutricional','$antitetanica1dosis','$antitetanica2dosis','$vdrl','$hiv','$eco','$fechaobito'
	,'$nrocontrolactual','$idtrazadora','$descripcion_error','$data[4]','$idbenefrecepcion','$tamax','$tamin','$au','$peso_mem02','$fechaNac','$discapacitado'
	,'$municipio')";

              $errorsql = mssql_query($SQLe);
              if (!$errorsql) {
                mssql_query($SQLerror);
                mssql_close($conexion);
                fclose($handle);
                echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=42";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=42";
					</SCRIPT>';
              }
            }
            if ($data[3] == 3) {
              /*PARTOS*/
              $pa++;
              /*graba beneficiarios*/

              $SQLp = "insert into TrzPartostmp (CodigoEfector,ClaveBeneficiario,TipoDocumento,NumeroDocumento,Apellido,Nombre,
	FechaParto,APGAR5,Peso,Antitetanica,FechaConsejeria,VDRL,obitobebe,obitomadre,idtrazadora,mjs,idprestacion,idbenefrecepcion,ObbDesconocido
	,talla_rn,perimcef_rn,FechaNacimiento,discapacitado,municipio)
	values ('$data[1]','$claveBeneficiario','$tipoDoc','$nroDoc','$apellido','$nombre','$data[13]','$apgar',
	
'$pesoalnacer','$antitetanica','$consejeria','$vdrl','$obitohijo','$obitomadre','$idtrazadora','$descripcion_error','$data[4]','$idbenefrecepcion','$desconocido'
,'$talla_rn','$perimcef_rn','$fechaNac','$discapacitado','$municipio')";

              $errorsql = mssql_query($SQLp);
              if (!$errorsql) {
                mssql_query($SQLerror);
                mssql_close($conexion);
                fclose($handle);
                echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=43";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=43";
					</SCRIPT>';
              }
            }
            if ($data[3] == 14) {
              /*Muertes*/
              $mu++;

              $SQLmU = "insert into Trzmuertestmp (CodigoEfector,ClaveBeneficiario,ClaseDocumento,TipoDocumento,NumeroDocumento,Apellido,Nombre,
FechaNacimiento,comitelocal,caso,fechadefuncion,fppmuerte,idtrazadora,mjs,idbenefrecepcion,idprestacion,sexo,municipio) values ('$data[1]','$claveBeneficiario','$claseDoc','$tipoDoc','$nroDoc','$apellido','$nombre','$fechaNac','$data[13]'
,'$caso','$fdefuncion','$fppmuerte','$idtrazadora','$descripcion_error','$idbenefrecepcion','$data[4]','$sexo','$municipio')";

              $errorsql = mssql_query($SQLmU);
              if (!$errorsql) {
                mssql_query($SQLerror);
                mssql_close($conexion);
                fclose($handle);
                echo '<SCRIPT Language="Javascript">
				 if (screen.width + "x" +screen.height == "1024x768") 
	window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=43x1";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 			window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
                  '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=43x1";
					</SCRIPT>';
              }

            }

          } //FIN YA NO ESTA
        } //////////fin de error//////////////////////////

      } else {
        $menosl++;
      }
      /*FIN DE SI ES TRAZADORAS*/

      ///////////////////////////SI ES INFORMADO//////////////////////////////////
      if ($data[0] == "I" || $data[0] == "T") {
        if ($metez == 's') {
          $SQL = "insert into [20BenefInformados] (idRecepcion,cuie,idPrestacion,claveBeneficiario,codNomenclador,tipoDoc,nroDoc,nombre,apellido,fechaNac,fechaactual,idvacuna,idtaller,km,origen,destino,clavemadre,sexo,municipio,semgesta,discapacitado,clasedoc)
		values ('$idRecepcion','$data[1]','$data[4]','$claveBeneficiario','$data[12]','$tipoDoc','$nroDoc','$nombre','$apellido','$fechaNac','$data[13]','$idvacuna','$idtaller','$km','$origen','$destino','$clavemadre','$sexo','$municipio','$semgesta','$discapacitado','$claseDoc')";

          $errorsql = mssql_query($SQL);
          if (!$errorsql) {
            mssql_query($SQLerror);
            mssql_close($conexion);
            fclose($handle);
            echo '<SCRIPT Language="Javascript">
					 if (screen.width + "x" +screen.height == "1024x768") 
		window.location="menu_1024x768.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
              '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=200";
	
			if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
				window.location="menu.php?ini1=si&mjs=eerroorr&expediente=' . $codExpediente .
              '&cuerp=' . $nroCuerpoExp . '&cara=' . $nroCaratula . '&a=200";
						</SCRIPT>';
          }
        }
      }
      /*FIN DE SI ES INFORMADO*/
    }

    if ($data[0] != "M" && $row == 1) {

      mssql_query($SQLerror);
      mssql_close($conexion);
      fclose($handle);
      echo '<SCRIPT Language="Javascript">
					alert("Error de archivo.Falta primer linea");
				 errorsimple();
					</SCRIPT>';
    }

    if (($num != 75) && $row != 1) {

      mssql_query($SQLerror);
      mssql_close($conexion);
      fclose($handle);
      echo '<SCRIPT Language="Javascript">
					alert("Error de archivo(campos' . $row . ')");
				 errorsimple();
					</SCRIPT>';
    }
    $row++;
  }

  //////el log final
  $archivoe = 'log/log.txt';

  $hora = date("d/m/Y H:i");
  $row1 = $row - $menos;
  $row1 = $row1 - 1;
  if (file_exists($archivoe)) {
    $p = fopen("$archivoe", "a");

    $a = '**' . $hora . ': Se han recepcionado "' . $cuenta_procesado .
      '" registros de ' . $row1 . ' correspondientes al archivo "' . $nombreArchivo .
      '" y fueron asignados al Expediente nº: "' . $codExpediente . '", Cuerpo nº: "' .
      $nroCuerpoExp . '" y Caratula : "' . $nroCaratula . '"
  ';
  } else {
    $p = fopen("$archivo", "w");
    $a = '**' . $hora . ': Se han recepcionado "' . $cuenta_procesado .
      '" registros de ' . $row1 . ' correspondientes al archivo "' . $nombreArchivo .
      '" y fueron asignados al Expediente nº: "' . $codExpediente . '", Cuerpo nº: "' .
      $nroCuerpoExp . '" y Caratula : "' . $nroCaratula . '".
   ';
  }
  if ($p) {
    fwrite($p, $a);
    // fputs
  }

  fclose($p);

  $arch = 'log/trazadoras.txt';

  $hora = date("d/m/Y H:i");
  $row2 = $row - $menosl;
  $row2 = $row2 - 1;
  ////si el archivo existe
  if (file_exists($arch)) {
    $x = fopen("$arch", "a");

    $as = '**' . $hora . ':El archivo "' . $nombreArchivo . '" tenia "' . $cuenta_error .
      '" registros erroneos de ' . $row2 . '.L=' . $errorl . ' y T=' . $errort .
      ' errores.Correspondientes a niños ' . $ni . ', correspondientes a embarazadas ' .
      $em . ', correspondientes a partos ' . $pa . 'y correspondientes a muertes ' . $mu .
      '.
   ';
    $as .= '--------------------------------------------Fin Trazadora--------------------------------------
	 ';

  } else {
    /////si no existe archivo
    $x = fopen("$arch", "w");
    $as = '**' . $hora . ':El archivo "' . $nombreArchivo . '" tenia "' . $cuenta_error .
      '" registros erroneos de ' . $row2 . '.L=' . $errorl . ' y T=' . $errort .
      ' errores.Correspondientes a niños ' . $ni . ', correspondientes a embarazadas ' .
      $em . ', correspondientes a partos ' . $pa . ' y correspondientes a muertes ' .
      $mu . '.
   ';
    $as .= '--------------------------------------------Fin Trazadora--------------------------------------
	 ';

  }
  if ($x) {
    fwrite($x, $as);
  }

  fclose($x);
  fclose($handle);

  $SQLC = "COMMIT";
  $errorsql = mssql_query($SQLC);

  if (!$errorsql) {
    mssql_query($SQLerror);
    mssql_close($conexion);
    echo "<SCRIPT Language='Javascript'>
							alert('ERROR...NO SE EJECUTO commit.');
						</SCRIPT>";
  }
  mssql_close($conexion);
  echo '<SCRIPT Language="Javascript">
		window.open("recupera_trazadoras.php","trazadoras","toolbar=no,directories=no,menubar=no,status=no,width=5, height=5");
				  </SCRIPT>';
  echo '<SCRIPT Language="Javascript">
 				 if (screen.width + "x" +screen.height == "1024x768") 
		window.location="menu_1024x768.php?mjs=recepcionado";

		if (screen.width + "x" +screen.height == "800x600" || screen.width + "x" +screen.height != "1024x768")
 				window.location="menu.php?mjs=recepcionado";		
				  </SCRIPT>';
}
?>