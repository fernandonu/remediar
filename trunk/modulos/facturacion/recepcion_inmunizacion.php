<?php
require_once ("../../config.php");
echo $html_header;
$directorio_base = trim(substr(ROOT_DIR, strrpos(ROOT_DIR, chr(92)) + 1, strlen
  (ROOT_DIR)));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title></title>
    <script type="text/javascript">
	function guardar(){
		var fecha_entra=document.all.fecha_entrada.value;
		if(fecha_entra.replace(/^\s+|\s+$/g,"")==""){ 
			alert('Debe completar el campo Fecha Entrada');
			//fecha_entra.focus();
			return false;
		}
		
		var cod_or=document.all.cod_org.value;
		if(cod_or.replace(/^\s+|\s+$/g,"")==""){ 
			alert('Debe completar el campo Cod. Org.');
			//cod_or.focus();
			return false;
		}
		
		var no_correlativ=document.all.no_correlativo.value;
		if(no_correlativ.replace(/^\s+|\s+$/g,"")==""){ 
			alert('Debe completar el campo Nro. Correlativo');
			//no_correlativ.focus();
			return false;
		}
		
		var ano_expe=document.all.ano_exp.value;
		if(ano_expe.replace(/^\s+|\s+$/g,"")==""){ 
			alert('Debe completar el campo Año Expte.');
			//ano_expe.focus();
			return false;
		}
		
		var cuerp=document.all.cuerpo.value;
		if(cuerp.replace(/^\s+|\s+$/g,"")==""){ 
			alert('Debe completar el campo Cuerpo');
			//cuerp.focus();
			return false;
		}
	}
//<![CDATA[
      var nav4 = window.Event ? true : false;
      function acceptNum(evt){ 
        var key = nav4 ? evt.which : evt.keyCode; 
        return (key <= 13 || (key >= 48 && key <= 57));
      }
    //]]>
	//Validar Fechas
function esFechaValida(fecha){
    if (fecha != undefined && fecha.value != "" ){
        if (!/^\d{2}\/\d{2}\/\d{4}$/.test(fecha.value)){
            alert("formato de fecha no válido (dd/mm/aaaa)");
			fecha.focus();
			fecha.value = ""
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
			fecha.focus();
			fecha.value = ""
            return false;
    }
 
        if (dia>numDias || dia==0){
            alert("Fecha introducida errónea");
			fecha.focus();
			fecha.value = ""
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
/*<![CDATA[*/
    input.c3 {width:350px}
      div.c2 {text-align: center}
      div.c1 {font-weight: bold; text-align: left}
    /*]]>*/
    </style>
    <script type="text/javascript" src="/&lt;?phpecho $directorio_base?&gt;/lib/jquery-1.5.1.js">
</script>
    <style type="text/css">
/*<![CDATA[*/
    td.c1 {padding: 5px;font-size: 14px;}
    /*]]>*/
    </style>
</head>

<body>
    <br />
    <br />

    <form name='recepcion_nuevo_txt' action='recepcion_inmunizacion_txt.php' method="post" enctype='multipart/form-data' id="recepcion_nuevo_txt">
        <table width="469" border="0" align="center" cellpadding="0" cellspacing="0">
<!--            <tr>
                <td id="mo" align="center" class="c1">Expediente (Cod. de Org. - N&ordm; Correlativo - A&ntilde;o - Cuerpo)</td>
            </tr>

            <tr>
                <td align="center"><br />
                <br />
                <input type="text" name="cod_org" size="4" maxlength="5" onkeypress="return acceptNum(event)" value="<?php echo $cod; ?>" /> - <input type="text" name="no_correlativo" size="4" maxlength="5" onkeypress="return acceptNum(event)" value="<?php echo $correl; ?>" /> - <input type="text" name="ano_exp" size="2" maxlength="2" onkeypress="return acceptNum(event)" value="<?php echo $ano_exp1; ?>" /> - <input type="text" name="cuerpo" size="3" maxlength="3" onkeypress="return acceptNum(event)" value="<?php echo $cuerp; ?>" />
                <br />
                <br /></td>
            </tr>

            <tr>
                <td id="mo" align="center" class="c1">Fecha de Entrada</td>
            </tr>

            <tr>
                <td align="center"><br />
                <br />
                <input type="text" name="fecha_entrada" id="fecha_entrada" size="15" maxlength="10" onKeyUp="mascara(this,'/',patron,true);" onblur="esFechaValida(this);"/>&nbsp;&nbsp;&nbsp;<?=
                link_calendario('fecha_entrada');
                ?>

                <br />
                <br /></td>
            </tr>
-->
            <tr>
                <td id="mo" align="center" class="c1">Archivo</td>
            </tr>

            <tr>
                <td align="center"><br />
                <br />
                <input type="file" name="archivo" class="c3" />
                <br />
                <br /></td>
            </tr>

            <tr>
                <td align="center" id="mo" class="c1"><input type="submit" name="enviar" value="Enviar" onclick="return guardar()"/></td>
            </tr>
        </table>
    </form><?
    fin_pagina();
    ?>
    </body>
</html>


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
