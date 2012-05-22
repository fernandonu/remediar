<?php

require_once("../../config.php");
$archivoDevuelto = $_FILES['archivodevuelto']['name'];

//$uploaddir = './tmp/';
//$uploadfile = $uploaddir . basename($_FILES['archivodevuelto']['name']);

$fecha_actual = date("Y-m-d");
$encontro=false;
//recorre el archivo buscando los id de beneficiario y genera la consulta update
/*if(!move_uploaded_file($_FILES['archivodevuelto']['tmp_name'], $uploadfile)){
    echo "No se pudo abrir ($archivoDevuelto)";
    exit;
}*/
if (!$archivoenvariable = file_get_contents($_FILES['archivodevuelto']['tmp_name'])) { //abro el archivo y lo pongo en una variable String
    echo "No se pudo abrir ($archivoDevuelto)";
    exit;
} else {
    $fecha_generacion = date("Y-m-d H:m:s");
    $beneficiarios = explode('"D"', $archivoenvariable);
    foreach ($beneficiarios as $beneficiario) {
        $substring=stristr($beneficiario, 'DNI');
        if($substring!==false){
            $length=strpos($substring, ';"');
            $doc_beneficiarios = substr($beneficiario, 7, $length-5);
            $consulta = "select id_clasificacion from trazadoras.clasificacion_remediar where num_doc='" . $doc_beneficiarios . "'";
            $result = sql($consulta, "Error al procesar el archivo enviado") or die;
        if ($result->fields['id_clasificacion'] != '') {
            //el beneficiario esta clasificadon en la tabla vieja
            $tabladelupdate = "trazadoras.clasificacion_remediar";
        } else {
            //esta en la tabla nueva
            $consulta = "select id_clasificacion from trazadoras.clasificacion_remediar2 where num_doc='" . $doc_beneficiarios . "'";
            $result = sql($consulta, "Error al procesar el archivo enviado") or die;
            $tabladelupdate = "trazadoras.clasificacion_remediar2";
        }
        $consulta2 = "update ". $tabladelupdate ." set enviado='n', fecha_modif='$fecha_actual' where id_clasificacion=";
        $consulta2.=$result->fields['id_clasificacion'];
        sql($consulta2, "Error al procesar el archivo enviado") or fin_pagina();
        $encontro=true;
    }
    }if($encontro){
        echo "<br>El Archivo ($archivoDevuelto) se proceso con exito";
    }else{
        echo "<br>No hay nada para procesar";
    }
}
?>