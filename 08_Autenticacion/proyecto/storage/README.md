# Almacenamiento de archivos PDF

`pdfs/` queda fuera de `public/`. Nunca use una URL directa a este directorio.
Los archivos se leen por su identificador interno después de consultar MySQL.
Al restablecer MySQL también se debe volver a copiar los PDF de demostración desde `archivos_prueba/` si fueron borrados.
