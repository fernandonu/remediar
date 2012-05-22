C: 
chdir "C:\Archivos de programa\PostgreSQL\9.0\bin\" 
psql -d nacer -U projekt -w -c "SET CLIENT_ENCODING TO LATIN1; copy puco.puco FROM 'C:\\puco_ok.txt' null AS ''"; 
D: 
chdir "C:\sistemas\nacer\modulos\nacer\" 
echo 0 > log_puco.txt