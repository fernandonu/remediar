SELECT clave_beneficiario as ClaveBeneficiario, apellido_benef as Apellido, nombre_benef as Nombre, sexo, fecha_nacimiento_benef as FechaNacimiento,
       tipo_documento as TipoDocumento, clase_documento_benef as ClaseDocumento, numero_doc as NroDocumento, calle, numero_calle, municipio, departamento, 
       cuie_ea as cuie, '' as fecha_empadronamiento, '' as score, '' as cuie_remediar, '' as preg8, '' as preg9 
from uad.beneficiarios
WHERE NOT EXISTS(SELECT clavebeneficiario FROM uad.remediar_x_beneficiario WHERE clavebeneficiario = clave_beneficiario)
      AND NOT EXISTS(SELECT tipo_doc, documento FROM puco.puco WHERE tipo_doc = tipo_documento AND documento = CAST(numero_doc AS INT))
      AND '2011-12-31' - DATE(beneficiarios.fecha_nacimiento_benef)>= 2190
      AND '2011-12-31' - DATE(beneficiarios.fecha_nacimiento_benef)<= 6935
      AND beneficiarios.fallecido = 'n' 
