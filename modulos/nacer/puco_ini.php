<? require_once("../../config.php");
/*CORRE SCRIPT DE GUARDADO DE PUCO*/	
				$filename1 = 'C:\\'.$puco_cual.'_ok.txt';
				$filename1x = 'C:\\puco_ok.txt';
				unlink($filename1x);
				copy($filename1,$filename1x);
				  if(file_exists($filename1x)){
					$filename2 = 'log_puco.txt';
					$control = fopen($filename2,"w+");   
					if($control == false){   
					  die("No se ha podido crear el archivo.");   
					  fclose($control);
					}else{ 	
						exec('sube_puco.bat');
						fclose($control);
						echo "<SCRIPT Language='Javascript'> location.href='puco_proc.php?incr=0&puco_cual=".$puco_cual."'</SCRIPT>"; 
					}
				  }else{
					echo "<SCRIPT Language='Javascript'> 
							alert('Debe primer Importar PUCO');
							location.href='genera_archivo.php';
							</SCRIPT>"; 
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
<?  echo fin_pagina();// aca termino  ?>