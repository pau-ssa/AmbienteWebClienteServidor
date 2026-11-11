<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Biblioteca Virtual Fidélitas | SC-502</title>
    <link rel="stylesheet" href="launcher.css">
</head>

<body>
    <header>
        <div class="cabecera">
            <p>UNIVERSIDAD FIDÉLITAS · SC-502</p>
            <h1>Biblioteca Virtual — Laboratorio progresivo</h1>
            <p class="subtitulo">Ocho aplicaciones autónomas para estudiar PHP, MySQL/MariaDB,
                Stored Procedures, MVC, carga de PDF, visor y autenticación.</p>
        </div>
    </header>
    <main>
        <h2>Seleccionar un prototipo</h2>
        <p>Abra los proyectos desde Apache (XAMPP). Visual Studio Code se usa para editar
            archivos y comparar los cambios.</p>
        <section class="grid" aria-label="Versiones de la biblioteca">
            <article class="tarjeta">
                <div class="numero">PROTOTIPO 01</div>
                <h3>Biblioteca inicial con arreglos</h3>
                <p>PHP, datos en memoria y catálogo público.</p>
                <a class="boton" href="01_Biblioteca_Basica_PHP/proyecto/public/index.php">Abrir P01</a>
                <p><a href="01_Biblioteca_Basica_PHP/proyecto/public/diagnostico.php">Comprobar entorno P01</a></p>
            </article>
            <article class="tarjeta">
                <div class="numero">PROTOTIPO 02</div>
                <h3>Conexión y consultas MySQL</h3>
                <p>PDO, tablas y procedimientos de consulta.</p>
                <a class="boton" href="02_Conexion_MySQL/proyecto/public/index.php">Abrir P02</a>
                <p><a href="02_Conexion_MySQL/proyecto/public/diagnostico.php">Comprobar entorno P02</a></p>
            </article>
            <article class="tarjeta">
                <div class="numero">PROTOTIPO 03</div>
                <h3>CRUD de libros</h3>
                <p>Altas, edición, eliminación y validación.</p>
                <a class="boton" href="03_CRUD_Libros/proyecto/public/index.php">Abrir P03</a>
                <p><a href="03_CRUD_Libros/proyecto/public/diagnostico.php">Comprobar entorno P03</a></p>
            </article>
            <article class="tarjeta">
                <div class="numero">PROTOTIPO 04</div>
                <h3>Arquitectura MVC</h3>
                <p>Separación de rutas, modelos, vistas y controladores.</p>
                <a class="boton" href="04_Arquitectura_MVC/proyecto/public/index.php">Abrir P04</a>
                <p><a href="04_Arquitectura_MVC/proyecto/public/diagnostico.php">Comprobar entorno P04</a></p>
            </article>
            <article class="tarjeta">
                <div class="numero">PROTOTIPO 05</div>
                <h3>Categorías y búsqueda</h3>
                <p>CRUD de categorías, filtros y relaciones.</p>
                <a class="boton" href="05_Categorias_y_Busqueda/proyecto/public/index.php">Abrir P05</a>
                <p><a href="05_Categorias_y_Busqueda/proyecto/public/diagnostico.php">Comprobar entorno P05</a></p>
            </article>
            <article class="tarjeta">
                <div class="numero">PROTOTIPO 06</div>
                <h3>Carga de PDF</h3>
                <p>Subida, reemplazo, descarga y almacenamiento protegido.</p>
                <a class="boton" href="06_Carga_de_PDF/proyecto/public/index.php">Abrir P06</a>
                <p><a href="06_Carga_de_PDF/proyecto/public/diagnostico.php">Comprobar entorno P06</a></p>
            </article>
            <article class="tarjeta">
                <div class="numero">PROTOTIPO 07</div>
                <h3>Visor integrado</h3>
                <p>Lectura en navegador y entrega con rangos HTTP.</p>
                <a class="boton" href="07_Visor_PDF/proyecto/public/index.php">Abrir P07</a>
                <p><a href="07_Visor_PDF/proyecto/public/diagnostico.php">Comprobar entorno P07</a></p>
            </article>
            <article class="tarjeta">
                <div class="numero">PROTOTIPO 08</div>
                <h3>Login y autorización</h3>
                <p>Sesiones y control del área administrativa.</p>
                <a class="boton" href="08_Autenticacion/proyecto/public/index.php">Abrir P08</a>
                <p><a href="08_Autenticacion/proyecto/public/diagnostico.php">Comprobar entorno P08</a></p>
            </article>
        </section>
        <div class="nota"><strong>Instalación:</strong> inicie Apache y MySQL desde XAMPP;
            importe el archivo <code>database/00_INSTALAR_TODO.sql</code> de cada prototipo P02–P08
            mediante phpMyAdmin. P01 no requiere base de datos. El enlace de diagnóstico permite
            confirmar extensiones, conexión y permisos.</div>
        <h2>Documentación</h2>
        <p>Lea los archivos Markdown desde Visual Studio Code y la guía formal de Word incluida
            con el paquete. Los proyectos P03–P07 tienen operaciones administrativas sin autenticación
            porque esa funcionalidad se enseña en P08: use el laboratorio solo en localhost.</p>
    </main>
    <footer>
        <div class="cabecera">SC-502 · Ambiente Web Cliente/Servidor · Material docente de laboratorio</div>
    </footer>
</body>

</html>