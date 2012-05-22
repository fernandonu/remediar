<?php
//ob_start();
require_once ("../../config.php");
require_once ("../../lib/funciones_misiones.php");
?>
<body bgcolor="silver"></body>
<script language="JavaScript">
  function cualzona(f)
  {
    var zon;
    //zon=document.forms.f_inmunizacion.zona.value;
    zon=f.zona.value;
    location.href="informe_parametros.php?zona="+zon;
  }
  
  function cambiarCombo(f){
    ////////habilita desabilita
    if(f.filter.value == 'vacuna'){
      f.efector.disabled = '';
      f.lugar.disabled = 'false';
    }
    if(f.filter.value == 'terreno'){
      f.efector.disabled = 'false';
      f.lugar.disabled = '';
    }
    if(f.filter.value == 'todos'){
      f.efector.disabled = '';
      f.lugar.disabled = '';
    }
  }
</script>

<form action="informe_parametros.php" method="post" name="f_inmunizacion" >

  <table style="text-align:center;width:100%;padding:10px;" ><tr>
      <td><b>Zona:</b>&nbsp;


        <?php
        $SLef = "SELECT idzona, nombre FROM uad.zonas"; // WHERE idzona = $zona";
        $buss = sql($SLef, "Error en zona") or excepcion("Error en zona");
        ?>
        <SELECT NAME="zona" style="font-size:13px;" onChange="cualzona(this.form)" >
          <OPTION VALUE="0" <?
        if ($_GET['zona'] == 0) {
          echo'selected';
        }
        ?>>Todas</OPTION>  
                  <?php
                  if ($buss->RecordCount() > 0) {
                    $buss->MoveFirst();
                    while (!$buss->EOF) {
                      if ($_GET['zona'] == $buss->fields["idzona"]) {
                        $selected = 'selected';
                      } if ($_GET['zona'] != $buss->fields["idzona"]) {
                        $selected = '';
                      }
                      $nombre = $buss->fields["nombre"];
                      ?>
              <OPTION VALUE="<?php echo $buss->fields["idzona"]; ?>"
                      <?php echo $selected; ?> >
                        <?php echo $nombre; ?>
              </OPTION>
              <?php
              $buss->MoveNext();
            }
          }
          ?>

          <table style="text-align:center;width:80%;padding:10px;" ><tr>
              <td><b>Efector:</b>&nbsp; 

                <?php
                ///////////////// ////////////efector//////////////////////////////
                if ($_GET['zona'] != 0) {
                  $consulefe = ' where id_zona_sani=' . $_GET['zona'];
                }
                $SLe = "SELECT id_efe_conv,  nombre FROM nacer.efe_conv $consulefe";
                $bus = sql($SLe, "Error en efector") or excepcion("Error en efector");
                ?>  

                <SELECT NAME="efector" style="font-size:13" >
                  <Option Value="0" <? if ($_GET['efector'] == 0)
                  echo'selected'; ?>>- - - - - - - - - - - -  </option>

                  <?php
                  if ($bus->RecordCount() > 0) {
                    $bus->MoveFirst();
                    while (!$bus->EOF) {
                      if ($_GET['efector'] == $bus->fields["id_efe_conv"]) {
                        $selected = 'selected';
                      } if ($_GET['efector'] != $bus->fields["id_efe_conv"]) {
                        $selected = '';
                      }

                      $nombre = $bus->fields["nombre"];
                      ?>


                      <OPTION VALUE="<?php echo $bus->fields["id_efe_conv"]; ?>"
                              <?php echo $selected; ?> >
                                <?php echo $nombre; ?>
                      </OPTION>     

                      <?php
                      $bus->MoveNext();
                    }
                  }
                  ?>
                </select>
              </td>
            </tr>



            <tr>
            <table style="text-align:center;width:80%;padding:10px;" ><tr>
                <td><b>En:</b>&nbsp; 
                  <input type=hidden name=form_busqueda value=1>
                  <select name='filter' onChange="cambiarCombo(this.form);">&nbsp;
                    <option value='todos' selected>Todos
                    <option value='terreno'>Terreno
                    <option value='vacunas'>Vacunatorio


                      <?php
                      ///////////////// ////////////terreno //////////////////////////////

                  
                      if ($_GET['terreno'] != 0) {
                        $consult = ' where terreno=' . $_GET['terreno'];
                      }
                      $SLe = "SELECT terreno,  nombre FROM inmunizacion.benefinmunizacion $consult";
                      $bus = sql($SLe, "Error en seleccion") or excepcion("Error en seleccion");
                      ?>  

                    <SELECT NAME="en" style="font-size:13" >
                      <Option Value="0" <? if ($_GET['en'] == 0)
                        echo'selected'; ?>> </option>


                      <?php
                      if ($bus->RecordCount() > 0) {
                        $bus->MoveFirst();
                        while (!$bus->EOF) {
                          if ($_GET['en'] == $bus->fields["terreno"]) {
                            $selected = 'selected';
                          } if ($_GET['en'] != $bus->fields["terreno"]) {
                            $selected = '';
                          }

                          $terreno = $bus->fields["terreno"];
                          ?>


                          <OPTION VALUE="<?php echo $bus->fields["terreno"]; ?>"
                                  <?php echo $selected; ?> >
                                    <?php echo $terreno; ?>
                          </OPTION>     

                          <?php
                          $bus->MoveNext();
                        }
                      }
                      ?>
                    </select>
                </td>
              </tr>
              <tr>




              <table style="text-align:center;width:80%;padding:10px;" ><tr>
                  <td><b>Lugar:</b>&nbsp; 

                    <?php
///////////////// ////////////lugar//////////////////////////////
                    if ($_GET['zona'] != 0) {
                      $consulefe = ' where id_zona_sani=' . $_GET['zona'];
                    }
                    $SLe = "SELECT id_efe_conv,  nombre FROM nacer.efe_conv $consulefe";
                    $bus = sql($SLe, "Error en efector") or excepcion("Error en efector");
                    ?>  

                    <SELECT NAME="lugar" style="font-size:13" >
                      <Option Value="0" <? if ($_GET['efector'] == 0)
                      echo'selected'; ?>>- - - - - - - - - - - -  </option>

                      <?php
                      if ($bus->RecordCount() > 0) {
                        $bus->MoveFirst();
                        while (!$bus->EOF) {
                          if ($_GET['efector'] == $bus->fields["id_efe_conv"]) {
                            $selected = 'selected';
                          } if ($_GET['efector'] != $bus->fields["id_efe_conv"]) {
                            $selected = '';
                          }

                          $nombre = $bus->fields["nombre"];
                          ?>


                          <OPTION VALUE="<?php echo $bus->fields["id_efe_conv"]; ?>"
                                  <?php echo $selected; ?> >
                                    <?php echo $nombre; ?>
                          </OPTION>     

                          <?php
                          $bus->MoveNext();
                        }
                      }
                      ?>
                    </select>
                  </td>
                </tr>




                <tr>
                  <td><input type="submit" name="enviar" value="Enviar" /></td>
                </tr>
              </table>


              </form>


              <?php if ($_POST['enviar'] == "Enviar") { ?>

                <?php //print_r($_POST);     ?>

                <table style="text-align:center;width:100%;padding:10px;" ><tr>
                    <td>&nbsp;






                      <?php
                      $idmunicipio = $_POST['municipio'];
                      $idzona = $_POST['zona'];
                      $fechadesde = $_POST ['fecha_desde'];
                      $fechahasta = $_POST ['fecha_hasta'];
                      $idefe = $_POST ['efector'];
                      $idterre = $_POST['terreno'];
                      $idvacu = $_POST['vacuna'];
                      $consulta = "idrecepcion='$idrecepcion'";


                      if ($idzona == 0) {
                        $consulta = 'true'; //nada
                      } else {
                        if ($idmunicipio == 0) {
                          $consulta = "id_zona_sani= $idzona"; //solo zona
                        } else {
                          $consulta = "municipio= $idmunicipio"; //municipio
                        }
                      }

                      ////////////////si se selecciona solo municipio////////////////////////

                      if ($idmunicipio != 0) {
                        $consulta = "municipio= $idmunicipio";
                      } else {
                        if ($idzona == 0) {
                          $consulta = 'true';
                        } else {
                          $consulta = "id_zona_sani= $idzona";
                        }
                      }
                      if ($idzona != 0) {
                        $SQLef = "SELECT nombre FROM uad.zonas where idzona=$idzona";
                        $e_busq = sql($SQLef, "Error en nombre de archivo") or excepcion("Error en nombre de archivo");

                        if ($e_busq->RecordCount() > 0) {
                          $zona = $e_busq->fields["idzona"];
                          $nombre = $e_busq->fields["nombre"];
                        } else {
                          echo 'No se encuentra archivo!';
                        }
                      } else {
                        $nombre = 'Todas';
                      }

                      echo '<b>Segun rendición de Zona: </b>' . $nombre . ' - ';

////////////////////////zona/////////////////////////////////////////////             

                      if ($idefe != 0) {

                        $Sll = "SELECT  id_efe_conv, nombre FROM nacer.efe_conv 
                    where id_efe_conv=$idefe";

                        $e_busq = sql($Sll, "Error en efector") or excepcion("Error en efector");
                        if ($e_busq->RecordCount() > 0) {
                          $nombre = $e_busq->fields["$idefe"];
                        } else {
                          echo 'No se encuentra efector';
                        }
                      } else {
                        $nombre = 'Todos';
                      }

                      echo '<b>Segun rendición de Efector: </b>' . $nombre . ' - ';

                      /* ++++++++++++++++++++++++++++ busqueda de zona, muestra municipio ++++++++++++++++++++++++++++++++++ */
                      if ($idefe != 0) {

                        $Sll = "SELECT  id_efe_conv, nombre FROM nacer.efe_conv 
                    where id_efe_conv=$idefe";

                        $e_busq = sql($Sll, "Error en lugar") or excepcion("Error en lugar");
                        if ($e_busq->RecordCount() > 0) {
                          $nombre = $e_busq->fields["$nombre"];
                        } else {
                          echo 'No se encuentra lugar';
                        }
                      } else {
                        $nombre = 'Todos';
                      }

                      echo '<b>Segun rendición de Lugar: </b>' . $nombre . ' - ';

                      /* ++++++++++++++++++++++++++++ fin busqueda++++++++++++++++++++++++++++++++++ */

////////////////vacuna, grupo, cantidad/////////////////////////////                     



                      $consu227 = "SELECT a.idvacuna,vacuna FROM inmunizacion.benefinmunizacion a 
inner join inmunizacion.vacunas b on a.idvacuna=b.idvacuna 
           inner join uad.municipios c on a.municipio=idmuni_provincial 
           inner join  nacer.efe_conv d on a.cuie= d.cuie  where  $consulta group by a.idvacuna,vacuna order by a.idvacuna";

                      $busc = sql($consu227, "Error fecha 1 ") or excepcion("Error en fecha ");

                      if ($busc->RecordCount() > 0) {
                        $busc->moveFirst();
                        while (!$busc->EOF) {

                          $vacuna = $busc->fields["idvacuna"];
                          $nombre_vacuna = $busc->fields["vacuna"];

                          echo'<table align="center"  cellpadding="0" cellspacing="0" border="0" width="50%">
 <tr ><td><b>' . $nombre_vacuna . '</b></td> </tr>
	</table>';
                          echo '<table align="center"  cellpadding="0" cellspacing="0" border="1" width="40%">';

                          $consu = "SELECT count(*) as cantidad,a.idrangovacuna,a.idvacuna 
        FROM inmunizacion.benefinmunizacion a
	inner join inmunizacion.vacunas b on a.idvacuna=b.idvacuna
        inner join uad.municipios c on a.municipio=idmuni_provincial
       inner join  nacer.efe_conv d on a.cuie= d.cuie 
	where  ( $consulta ) and a.idvacuna=$vacuna		
	group by a.idrangovacuna,a.idvacuna";

                          $bus = sql($consu, "Error fecha 2 ") or excepcion("Error en fecha ");
                          if ($bus->RecordCount() > 0) {
                            $bus->moveFirst();
                            while (!$bus->EOF) {

                              $cantidad = $bus->fields["cantidad"];
                              $idrangovacuna = $bus->fields["idrangovacuna"];
                              $consu2 = "SELECT descripcion FROM inmunizacion.vacunasrangos where idrangosvacunas ='$idrangovacuna'";
                              $bus2 = sql($consu2, "Error en vacuna") or excepcion("Error en vacuna");

                              echo '<tr align="center" width="80%"><td>' . $bus2->fields["descripcion"] . '</td><td width="20%">' . $cantidad . '</td></tr>';
                              $bus->moveNext();
                            }
                          }
                          echo "</table>";
                          $busc->moveNext();
                        }
                      }
                    }
                    fin_pagina();
                    ?>