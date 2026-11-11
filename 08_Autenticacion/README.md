# P08 — Autenticación

**Curso SC-502 | Universidad Fidélitas | XAMPP + Visual Studio Code**

**Objetivo:** Controlar el acceso administrativo con roles, sesiones y contraseñas protegidas.

**URL de aplicación:** http://localhost/BibliotecaVirtualFidelitas/08_Autenticacion/proyecto/public/index.php  
**Diagnóstico:** http://localhost/BibliotecaVirtualFidelitas/08_Autenticacion/proyecto/public/diagnostico.php  
**Base de datos:** `biblioteca_p08`

## Puesta en funcionamiento

1. Copiar la carpeta principal a `C:\xampp\htdocs\BibliotecaVirtualFidelitas`.
2. Desde **XAMPP Control Panel**, iniciar Apache y MySQL.
3. Abrir la carpeta principal del proyecto en Visual Studio Code.

4. En el navegador abrir `http://localhost/phpmyadmin/`.
5. Ir a **Importar** desde la pantalla inicial del servidor (no hace falta crear la base a mano).
6. Seleccionar `database/00_INSTALAR_TODO.sql` de **esta** versión.
7. Mantener el formato SQL y codificación UTF-8; pulsar **Importar / Continuar**.
8. Comprobar que aparece la base de datos de la versión; después abrir la pestaña **SQL** y ejecutar
   `database/05_CONSULTAS_VERIFICACION.sql` o sus consultas principales.
9. En VS Code revisar `proyecto/config/database.php`. Los valores de laboratorio son
   `127.0.0.1`, puerto `3306`, usuario `root`, contraseña vacía; si su XAMPP difiere,
   **modificar solamente los datos de conexión** (sin exponer contraseñas en Git).
10. En XAMPP `Config → PHP (php.ini)`: habilitar `file_uploads`, asignar
   `upload_max_filesize = 16M` y `post_max_size = 20M`, guardar y reiniciar Apache.
11. Comprobar que `proyecto/storage/pdfs` contiene los PDF de muestra y es escribible.
12. Para la demostración utilizar el usuario inicial del SQL:
    `admin@biblioteca.local` / `DocenteDemo#2026!` (solo en localhost).

Abrir la URL de diagnóstico **http://localhost/BibliotecaVirtualFidelitas/08_Autenticacion/proyecto/public/diagnostico.php** y comprobar los resultados.
Luego abrir el prototipo **http://localhost/BibliotecaVirtualFidelitas/08_Autenticacion/proyecto/public/index.php**.
No utilizar Live Server ni `php -S`: el servidor es Apache de XAMPP.


## Demostración del docente

Abrir el catálogo sin sesión; luego ingresar con `admin@biblioteca.local` y
`DocenteDemo#2026!`, administrar libros y cerrar sesión. Intentar abrir la administración
sin autenticar; debe volver al ingreso. Revisar sesiones y `Auth.php`.

## Responsabilidad de esta versión

- Mantiene las funcionalidades de los prototipos anteriores pero usa código completo y autónomo.
- Los cambios concretos se describen en `documentacion/03_CAMBIOS_DESDE_ANTERIOR.md`
  y `documentacion/08_DIFF_REAL_DE_CODIGO.patch`.
- El orden de construcción está en `documentacion/07_TALLER_PASO_A_PASO.md`.
- Los casos de prueba están en `documentacion/05_PRUEBAS.md`.
- Apache permite acceder a los recursos de la aplicación dentro de `proyecto/public/`; el resto de **esta carpeta de prototipo** queda protegido por `.htaccess`.

**Seguridad:** las etapas P03–P07 no tienen login. Son ejemplos exclusivamente
para laboratorio local; la autorización se explica en P08.
