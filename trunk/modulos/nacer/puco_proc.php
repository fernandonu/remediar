<? require_once("../../config.php");
//$incr=99;
if($incr<1){ 
	$filas=file('log_puco.txt'); 
	
	$i=0; 
	$numero_fila=0; 
	while($filas[$i]!=NULL){ 
	$row= $filas;
	$i++; 
	$numero_fila++; 
	}
	if($row==''){
		$incr++; 
		echo "<SCRIPT Language='Javascript'> location.href='puco_proc.php?incr=".$incr."&puco_cual=".$puco_cual."'</SCRIPT>"; 
	}elseif($row!=''){
		$fecha=date("Y-m-d H:m:s");
	    $usuario=$_ses_user['id'];
	    $usuario = substr($usuario,0,9);
		$a_reemplazar = array("-", "_", "PUCO"); 
		$puco_cual2=str_replace($a_reemplazar,'',strtoupper($puco_cual));
		$sql_tmp="INSERT INTO puco.registro_puco
        			( fecha,usuario,puco)
        			VALUES
        			('$fecha',upper('$usuario'),'$puco_cual2')";
		$result1=sql($sql_tmp);  
		 
		 $sql_tmp="UPDATE sistema.funciones set accion='$puco_cual2' where upper(nombre)='PUCO'";
		$result1=sql($sql_tmp); 
		
			 
				 $sql_tmp="CREATE INDEX documento_puco_puco ON puco.puco USING btree (documento)";
				 $result1=sql($sql_tmp);
				echo "No se pudo crear documento_puco_puco.<br>";
				 $sql_tmp="CREATE INDEX nombre_puco ON puco.puco USING btree (nombre)";
				 $result1=sql($sql_tmp);
				 "No se pudo crear documento_puco_puco.<br>";
				$sql_tmp="CREATE INDEX tipo_numero_puco ON puco.puco USING btree (tipo_doc, documento)";
				$result1=sql($sql_tmp);
				echo "No se pudo crear documento_puco_puco.<br>";
				
				echo "<SCRIPT Language='Javascript'> 
				alert('Puco procesado exitosamente!!');
				location.href='genera_archivo.php';
			 </SCRIPT>"; 
		}
		echo $incr.'<br>';

}
echo $html_header;
?>
<table border="0" align="center" height="100">
<tr>
	<td>
		<font color="Red">Procesando...Aguarde por favor!!</font>
	</td>
</tr>
</table>
<? sleep(120); echo fin_pagina();// aca termino  ?>
