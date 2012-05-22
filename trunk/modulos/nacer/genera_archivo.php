<?php

require_once("../../config.php");

if ($_POST['generar']){
	$sql_tmp="select * from public.dosep";
	$result1=sql($sql_tmp);
	$filename = '200805.txt';	

	  	if (!$handle = fopen($filename, 'a')) {
        	 echo "No se Puede abrir ($filename)";
         	exit;
    	}

    	$result1->movefirst();
    	while (!$result1->EOF) {
    		$contenido="DNI";
    		$contenido.=str_repeat('0',8-strlen($result1->fields['num'])).$result1->fields['num'];
    		$contenido.=$result1->fields['ape']." ";
    		$contenido.=$result1->fields['nom'];
    		$contenido1=$contenido;
    		$contenido.=str_repeat(' ',61-strlen($contenido1));
    		$contenido.=substr($result1->fields['sexo'],0,1);
    		$contenido.="3225515700";
    		$contenido.="    SL";    		
    		if ($result1->fields['plan']=="PLG") $contenido.="T";
    		else $contenido.="A";
    		$contenido.="\n";
    		if (fwrite($handle, $contenido) === FALSE) {
        		echo "No se Puede escribir  ($filename)";
        		exit;
    		}
    		$result1->MoveNext();
    	}
    	echo "El Archivo ($filename) se genero con exito";
    
    	fclose($handle);
	
}

if ($_POST['importapuco']){
	echo '<font color="Red">Procesando...Aguarde por favor!!</font>';
	$filename1 = 'C:\\'.$_POST['cual_puco'].'_ok.txt';
	//if(!file_exists($filename1)){
			$sql_tmp="truncate puco.puco";
			$result1=sql($sql_tmp);
			echo "Se elimino datos de la tabla puco <br>";
			
			 $sql_tmp="DROP INDEX puco.documento_puco_puco";
			 $result1=sql($sql_tmp);
			 "No se pudo eliminar documento_puco_puco.<br>";
			 $sql_tmp="DROP INDEX puco.nombre_puco";
			 $result1=sql($sql_tmp);
			echo "No se pudo eliminar tipo_nrodocumento.<br>";
			 $sql_tmp="DROP INDEX puco.tipo_numero_puco";
			 $result1=sql($sql_tmp);
			echo "No se pudo eliminar tipo_nrodocumento.<br>";
			
			$filename = 'pucos/'.$_POST['cual_puco'].'.txt';//'puco.txt';	
		
				if (!$handle = fopen($filename, 'r')) {
					 echo "No se Puede abrir ($filename)";
					exit;
				}
			
			//$filename1 = 'C:\\'.$_POST['cual_puco'].'_ok.txt';
			if(file_exists($filename1)){
				unlink($filename1);
			 }
				if (!$handle1 = fopen($filename1, 'w+')) {
					 echo "No se Puede abrir ($filename1)";
					exit;
				}
				
				while (!feof($handle)) {
				$buffer = fgets($handle, 61);
				$a=substr($buffer,3,8);
				$b=substr($buffer,0,3);
				$c=substr($buffer,15,6);
				$d=substr($buffer,22,40);       
				
			   $contenido="";
			   $contenido.=trim($b);
			   $contenido.=chr(9);
			   $contenido.=ereg_replace('[^ A-Za-z0-9_-]','',trim($d));
			   $contenido.=chr(9);
			   $contenido.=ereg_replace('[^ A-Za-z0-9_-]','',trim($c));
			   $contenido.=chr(9);
			   $contenido.=trim($a);     
			   $contenido.="\n";
					if (fwrite($handle1, $contenido) === FALSE) {
						echo "No se Puede escribir  ($filename1)";
						exit;
					}
				  
			   }
			
				echo "Se Genero $filename1";
				fclose($handle);
				fclose($handle1);
				/*CORRE SCRIPT DE GUARDADO DE PUCO*/	
				echo "<SCRIPT Language='Javascript'> location.href='puco_ini.php?puco_cual=".$_POST['cual_puco']."'</SCRIPT>"; 
				
	//}
}

if ($_POST['generarsmiafiliados']){
	$sql_tmp="delete from nacer.smiafiliados";
	$result1=sql($sql_tmp);
	echo "Se elimino datos de la tabla smiafiliados <br>";
	$filename = 'smiafiliados.csv';	

	  	if (!$handle = fopen($filename, 'r')) {
        	 echo "No se Puede abrir ($filename)";
         	exit;
    	}
		
    	$cont=0;
    	while (!feof($handle)) {
        $buffer = fgets($handle, 8192);
        $buffer=ereg_replace(chr(9),null,$buffer);
        $buffer=ereg_replace("'",null,$buffer);
        $buffer=explode('"',$buffer);
        //print_r($buffer);
        list($a,$b,$c,$d,$f,$g,$h,$i,$j,$k,$l,$m,$n,$o,$p,$q,$r,$s,$t,$u,$v,$w,$x,$y,$z,$ab,$ac,$ad,$ae,$af,$ag,$ah,$ai,
        	 $aj,$ak,$al,$am,$an,$ao,$ap,$aq,$ar,$as,$at,$au,$av,$aw,$ax)=$buffer;
        $b= ereg_replace(',000000',null,$b);
        $w= ereg_replace(',000000',null,$w);
        $y= Fecha_db(ereg_replace(' 12:00 a.m.',null,$y)); if ($y=='') $y='1980-01-01';
        $ah= ereg_replace(',000000',null,$ah); 
        $al= Fecha_db(ereg_replace(' 12:00 a.m.',null,$al));if ($al=='') $al='1980-01-01';
        $an= Fecha_db(ereg_replace(' 12:00 a.m.',null,$an));if ($an=='') $an='1980-01-01';
		$ax= Fecha_db(ereg_replace(' 12:00 a.m.',null,$ax));if ($ax=='') $ax='1980-01-01';		
		$sql_tmp="INSERT INTO nacer.smiafiliados
        			(id_smiafiliados,clavebeneficiario,afiapellido,afinombre,afitipodoc,aficlasedoc,afidni,afisexo,afidomdepartamento,
  						afidomlocalidad,afitipocategoria,afifechanac,activo,cuieefectorasignado,cuielugaratencionhabitual,
  						motivobaja,mensajebaja,fechainscripcion,fechacarga,usuariocarga,manrodocumento,maapellido,manombre,fechadiagnosticoembarazo)
        			VALUES
        			($b,'$d','$g','$i','$k','$m','$o','$q','$s','$u',$w,'$y','$ab','$ad','$af',$ah,'$aj','$al',
        			'$an','$ap','$ar','$at','$av','$ax')";
		sql($sql_tmp);        
        $cont++;
    	}
		 	
    	echo "Se exportaron $cont Registros";
    	fclose($handle);
	
}

if ($_POST['generarsmiafiliadosaux']){
	$sql_tmp="delete from nacer.smiafiliadosaux";
	$result1=sql($sql_tmp);
	echo "Se elimino datos de la tabla smiafiliadosaux <br>";
	$filename = 'smiafiliadosaux.csv';	

	  	if (!$handle = fopen($filename, 'r')) {
        	 echo "No se Puede abrir ($filename)";
         	exit;
    	}
		
    	$cont=0;
    	while (!feof($handle)) {
        $buffer = fgets($handle, 8192);
        $buffer=ereg_replace(chr(9),null,$buffer);
        $buffer=ereg_replace("'",null,$buffer);
        $buffer=explode('"',$buffer);
        //print_r($buffer);
        list($a,$b,$c,$d)=$buffer;
        $d= ereg_replace(',000000',null,$d);
        $sql_tmp="INSERT INTO nacer.smiafiliadosaux
        			(clavebeneficiario,id_procesoingresoafiliados)
        			VALUES
        			('$b',$d)";
		sql($sql_tmp);        
        $cont++;
    	}
		 	
    	echo "Se exportaron $cont Registros";
    	fclose($handle);
	
}

if ($_POST['generarsmiefectores']){
	$sql_tmp="delete from facturacion.smiefectores";
	$result1=sql($sql_tmp);
	echo "Se elimino datos de la tabla smiefectores <br>";
	$filename = 'smiefectores.csv';	

	  	if (!$handle = fopen($filename, 'r')) {
        	 echo "No se Puede abrir ($filename)";
         	exit;
    	}
		
    	$cont=0;
    	while (!feof($handle)) {
        $buffer = fgets($handle, 8192);
        $buffer=ereg_replace(chr(9),null,$buffer);
        $buffer=ereg_replace("'",null,$buffer);
        $buffer=explode('"',$buffer);
        //print_r($buffer);
        list($a,$b,$c,$d,$f,$g,$h,$i,$j,$k)=$buffer;
        $sql_tmp="INSERT INTO facturacion.smiefectores
        			(cuie,tipoefector,nombreefector,direccion,localidadmunicipiopartido)
        			VALUES
        			('$b','$d','$g','$i','$k')";
		sql($sql_tmp);        
        $cont++;
    	}
		 	
    	echo "Se exportaron $cont Registros";
    	fclose($handle);
	
}

if ($_POST['generarsmiprocesoafiliados']){
	$sql_tmp="delete from nacer.smiprocesoafiliados";
	$result1=sql($sql_tmp);
	echo "Se elimino datos de la tabla smiprocesoafiliados <br>";
	$filename = 'smiprocesoafiliados.csv';	

	  	if (!$handle = fopen($filename, 'r')) {
        	 echo "No se Puede abrir ($filename)";
         	exit;
    	}
		
    	$cont=0;
    	while (!feof($handle)) {
        $buffer = fgets($handle, 8192);
        $buffer=ereg_replace(chr(9),null,$buffer);
        $buffer=ereg_replace("'",null,$buffer);
        $buffer=explode('"',$buffer);
        //print_r($buffer);
        list($a,$b,$c,$d,$f,$g)=$buffer;
        $b= ereg_replace(',000000',null,$b);
        
        $sql_tmp="INSERT INTO nacer.smiprocesoafiliados
        			( id_procafiliado,periodo,codigocialtadatos)
        			VALUES
        			($b,'$d','$g')";
		sql($sql_tmp);        
        $cont++;
    	}
		 	
    	echo "Se exportaron $cont Registros";
    	fclose($handle);
	
}
/*
if ($_POST['sube_puco']){
  $filename1 = 'C:\\puco_ok.txt';
  if(file_exists($filename1)){
  	$filename2 = 'log_puco.txt';
  	$control = fopen($filename2,"w+");   
	if($control == false){   
	  die("No se ha podido crear el archivo.");   
	}else{ 	
		exec('sube_puco.bat');
		echo "<SCRIPT Language='Javascript'> location.href='puco.php?incr=0'</SCRIPT>"; 
  	}
  }else{
	echo '<font color="Red">Debe primer "Importar PUCO"</font>';
  }
}
*/
echo $html_header;
?>
<script>
function control(){
	if(document.all.cual_puco.value=="-1"){
	alert("Debe seleccionar 1 PUCO");
	document.all.cual_puco.focus();
	 return false;
	}
}
</script>
<form name=form1 action="genera_archivo.php" method=POST>
<table width="80%" class="bordes" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?=$bgcolor_out?>' class="bordes">
<tr><td>
  <table width=100% align="center" class="bordes">
  
	<tr id="mo" align="center">
    <td colspan="2" align="center">
    	<font size=+1><b>Importar Archivo</b></font>      	      
    </td>
   </tr>
   		<tr>	           
           <td align="center" colspan="2" id="ma">
            <b> Obra social </b>
           </td>
         </tr>
     <tr>
      <td align="right">		
	    <input type=submit name="generar" value='Genera Archivo OSP' style="width=250px">
	  </td>
	  <td align="left">		
	    <font color="Red">Debe tener preparada con los datos correspondiente la tabla "public.dosep".</font>
	  </td>
     </tr> 
     <tr>	           
           <td align="center" colspan="2" id="ma">
            <b> Importacion Archivos Sistema</b>
           </td>
         </tr>
     <tr>
      <td align="right">		
	  
		 <select name="cual_puco" >
		 <option value="-1">Elija 1 opcion</option>
		 <? $path="pucos/"; 
	  	 $directorio=dir($path); 
		 $cont=1;
			while ($archivo = $directorio->read()){ 
				if ($cont >= 3){
					$extension=explode('.',$archivo);
					$nro = count($extension)-1;
					//echo '*'.$extension[0];
					if( strtoupper($extension[$nro])== 'TXT'){
						echo '<option value="'.$extension[0].'">'.$extension[0].'</option>';
					}
				}
							$cont++;
			} 
	  ?>
		 </select>
	    <input type=submit name="importapuco" value='Importar PUCO' style="width=250px" onclick="return control();">
	  </td>
	   <td align="left">		
	    <font color="Red">Debe copiar archivo puco.txt a la carpeta "sistema\modulos\nacer"</font>
	  </td>
     </tr><tr>
      <td align="right">		
	    <input type=submit name="generarsmiafiliados" value='Importar Smiafiliados' style="width=250px">
	  </td>
	   <td align="left">		
	    <font color="Red">Debe copiar archivo smiafiliados.csv a la carpeta "sistema\modulos\nacer"</font>
	  </td>
     </tr>
     <tr>
      <td align="right">		
	    <input type=submit name="generarsmiafiliadosaux" value='Importar Smiafiliadosaux' style="width=250px">
	  </td>
	 <td align="left">		
	    <font color="Red">Debe copiar archivo smiafiliadosaux.csv a la carpeta "sistema\modulos\nacer"</font>
	  </td>
     </tr>
     <tr>
      <td align="right">		
	    <input type=submit name="generarsmiefectores" value='Importar Smiefectores' style="width=250px">
	  </td>
	  <td align="left">		
	    <font color="Red">Debe copiar archivo smiefectores.csv a la carpeta "sistema\modulos\nacer"</font>
	  </td>
     </tr>
     <tr>
      <td align="right">	
	    <input type=submit name="generarsmiprocesoafiliados" value='Importar Smiprocesoafiliados' style="width=250px">
	  </td>
	  <td align="left">		
	    <font color="Red">Debe copiar archivo smiprocesoafiliados.csv a la carpeta "sistema\modulos\nacer"</font>
	  </td>
     </tr>
	      <tr>
      <td align="right">	
	    <input type=submit name="sube_puco" value='Procesar PUCO' style="width=250px">
	  </td>
	  <td align="left">		
	    <font color="Red">Debe primer "Importar PUCO"</font>
	  </td>
     </tr>
     </table>
     </td>
     </tr>
</table>

</form>
</body>
</html>
<?echo fin_pagina();// aca termino ?>