<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curriculum Vitae</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            width: 90%;
            max-width: 900px;
            margin: 40px auto;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #0056b3;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 32px;
        }

        .header p {
            margin: 5px 0;
            font-size: 18px;
        }

        .main {
            display: flex;
            flex-wrap: wrap;
            padding: 20px;
        }

        .left-column {
            width: 35%;
            padding-right: 20px;
        }

        .right-column {
            width: 65%;
        }

        .section {
            margin-bottom: 20px;
        }

        h2 {
            font-size: 20px;
            color: #333;
            margin-bottom: 10px;
            border-bottom: 2px solid #0056b3;
            padding-bottom: 5px;
        }

        p, li {
            font-size: 14px;
            line-height: 1.6;
            color: #555;
        }

        ul {
            padding-left: 20px;
        }

        .contact-info {
            background-color: #f7f7f7;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .contact-info p {
            margin: 5px 0;
        }

        .skills li {
            margin-bottom: 5px;
        }

        .skills span {
            font-weight: bold;
            color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>NOMBRE APELLIDO</h1>
        <p>Puesto o profesión (Ejemplo: Desarrollador Web)</p>
    </div>

    <div class="main">
        <!-- Columna Izquierda -->
        <div class="left-column">
            <!-- Información de Contacto -->
            <div class="contact-info section">
                <h2>Contacto</h2>
                <p>Email: email@email.com</p>
                <p>Teléfono: 000-000-000</p>
                <p>Dirección: Calle- Número-Ciudad</p>
            </div>

            <!-- Habilidades -->
            <div class="skills section">
                <h2>Habilidades</h2>
                <ul>
                    <li><span>Desarrollo Web:</span> HTML, CSS, JavaScript, PHP, Laravel</li>
                    <li><span>Diseño Gráfico:</span> Photoshop, Illustrator</li>
                    <li><span>Idiomas:</span> Inglés (Avanzado), Francés (Intermedio)</li>
                </ul>
            </div>
        </div>

        <!-- Columna Derecha -->
        <div class="right-column">
            <!-- Experiencia -->
            <div class="section">
                <h2>Experiencia</h2>
                <p><strong>2019 - Presente | Empresa XYZ</strong></p>
                <p>Descripción del puesto y responsabilidades clave. Ejemplo: Desarrollo de aplicaciones web personalizadas para clientes internacionales.</p>

                <p><strong>2017 - 2019 | Empresa ABC</strong></p>
                <p>Descripción del puesto y responsabilidades clave. Ejemplo: Mantenimiento de plataformas web y mejora de experiencia de usuario en sitios de comercio electrónico.</p>
            </div>

            <!-- Formación Académica -->
            <div class="section">
                <h2>Formación Académica</h2>
                <p><strong>2013 - 2017 | Universidad Ejemplo</strong></p>
                <p>Licenciatura en Ingeniería en Sistemas</p>

                <p><strong>2011 - 2013 | Instituto Ejemplo</strong></p>
                <p>Técnico en Programación</p>
            </div>

            <!-- Proyectos -->
            <div class="section">
                <h2>Proyectos</h2>
                <p><strong>Sistema de Gestión de Inventarios</strong></p>
                <p>Descripción del proyecto. Ejemplo: Desarrollo de un sistema de gestión de inventarios en Laravel que facilita el control de stock y la generación de reportes.</p>

                <p><strong>Aplicación de Tareas</strong></p>
                <p>Descripción del proyecto. Ejemplo: Aplicación móvil desarrollada en React Native para la gestión de tareas y recordatorios.</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
