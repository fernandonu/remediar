<?php
require_once("../../config.php");

variables_form_busqueda("efectores_unif");

$fecha_hoy = date("Y-m-d H:i:s");
$fecha_hoy = fecha($fecha_hoy);

$orden = array(
    "default" => "1",
    "1" => "cuie",
    "2" => "nombreefector",
    "3" => "cuidad",
    "9" => "nombre_dpto"
);

$filtro = array(
    "cuie" => "CUIE",
    "smiefectores.nombreefector" => "Nombre"
);


$sql_tmp = "select * from facturacion.smiefectores inner join uad.departamentos on departamento=id_departamento";

echo $html_header;
?>
<form name=form1 action="efectores_unif.php" method=POST>
    <table cellspacing=2 cellpadding=2 border=0 width=100% align=center>
        <tr>

            <td align=center>
                <? list($sql, $total_muletos, $link_pagina, $up) = form_busqueda($sql_tmp, $orden, $filtro, $link_tmp, $where_tmp, "buscar"); ?>
                &nbsp;&nbsp;<input type=submit name="buscar" value='Buscar'>
                &nbsp;&nbsp;
                <? $link = encode_link("efectores_unif_excel.php", array("cmd" => $cmd)); ?>
                <img src="../../imagenes/excel.gif" style='cursor:pointer;'  onclick="window.open('<?= $link ?>')">
                &nbsp;&nbsp;
                <b><a href=mail.txt target="_blank">Mail con Descripcion</a></b>
                &nbsp;&nbsp;
                <b><a href=mail_solos.txt target="_blank">Mail</a></b>
            </td>
        </tr>
    </table>

    <? $result = sql($sql) or die; ?>

    <table border=0 width=100% cellspacing=2 cellpadding=2 bgcolor='<?= $bgcolor3 ?>' align=center>
        <tr>
            <td colspan=15 align=left id=ma style="padding-top: 10px">Listado de Efectores</td>
        </tr>
        <tr>
            <td colspan=15 align=left id=ma>
                <table width=100%>
                    <tr id=ma>
                        <td width=30% align=left><b>Total:</b> <?= $total_muletos ?></td>       
                        <td width=40% align=right><?= $link_pagina ?></td>
                    </tr>
                </table>
            </td>
        </tr>


        <tr>
            <td align=right id=mo><a id=mo href='<?= encode_link("efectores_unif.php", array("sort" => "1", "up" => $up)) ?>'>CUIE</a></td>      	
            <td align=right id=mo><a id=mo href='<?= encode_link("efectores_unif.php", array("sort" => "2", "up" => $up)) ?>'>Nombre</a></td>
            <td align=right id=mo><a id=mo href='<?= encode_link("efectores_unif.php", array("sort" => "3", "up" => $up)) ?>'>Cuidad</a></td> 
            <td align=right id=mo><a id=mo href='<?= encode_link("efectores_unif.php", array("sort" => "9", "up" => $up)) ?>'>Departamento</a></td>            
            <td align=right id=mo><a id=mo href='<?= encode_link("efectores_unif.php", array("sort" => "6", "up" => $up)) ?>'>Telefono</a></td>       
        </tr>
        <?
        while (!$result->EOF) {
            $ref = encode_link("efectores_unif_admin.php", array("cuie" => $result->fields['cuie']));
            $onclick_elegir = "location.href='$ref'";
            ?>

            <tr <?= atrib_tr() ?>>        
                <td align="center" onclick="<?= $onclick_elegir ?>"><?= $result->fields['cuie'] ?></td>
                <td onclick="<?= $onclick_elegir ?>"><?= trim($result->fields['nombreefector']) ?></td>
                <td onclick="<?= $onclick_elegir ?>"><?= $result->fields['ciudad'] ?></td>
                <td onclick="<?=$onclick_elegir?>"><?=$result->fields['nombre']?></td>
                <td onclick="<?= $onclick_elegir ?>"><?= $result->fields['tel'] ?></td>    
            </tr>
            <?
            $result->MoveNext();
        }

        if (permisos_check('inicio', 'mail_masivos')) {
            ?>    
            <tr>
                <td colspan=15 align=left id=ma>
                    <table width=100%>
                        <tr id=ma>
                            <td width=30% align=left>
                                <b>Envia Mail a TODOS los CAPS con Convenio, de todos los NIÑOS con Percentilos de Peso Bajo:</b>
                                <input type="submit" value="Enviar Mail" name="mail_percentilo_nino_bajo">
                            </td>       
                        </tr>	
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan=15 align=left id=ma>
                    <table width=100%>
                        <tr id=ma>
                            <td width=30% align=left>
                                <b>Envia Mail a TODOS los CAPS con Convenio, de Mujeres con embarazo Adolecente:</b>
                                <input type="submit" value="Enviar Mail" name="mail_embarazo_adol">
                            </td>       
                        </tr>	
                    </table>
                </td>
            </tr>
<? } ?>
    </table>
</form>
</body>
</html>
<?
echo fin_pagina(); // aca termino ?>