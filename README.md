# customreports

# Documentación Técnica

# Plugin Moodle: **Student Reports (local_customreports)**

![Moodle](https://img.shields.io/badge/Moodle-4.4+-F98012?style=for-the-badge\&logo=moodle\&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge\&logo=php\&logoColor=white)
![Versión](https://img.shields.io/badge/Versión-v2.0-success?style=for-the-badge)
![Estado](https://img.shields.io/badge/Estado-Producción-brightgreen?style=for-the-badge)

---

#  Descripción

**Student Reports** es un plugin local para Moodle diseñado para proporcionar reportes detallados del comportamiento académico de los estudiantes dentro de un curso.

Permite consultar de manera rápida información sobre:

*  Estudiantes matriculados
*  Fecha de inscripción
*  Accesos por actividad
*  Último acceso
*  Calificaciones
*  Estadísticas generales
*  Exportación de reportes

Su objetivo es facilitar el seguimiento del progreso de los estudiantes y apoyar la toma de decisiones por parte de administradores y coordinadores académicos.

---

#  Características

##  Gestión de Cursos

* Selección dinámica de cursos
* Búsqueda rápida por nombre
* Compatible con todos los cursos de Moodle

---

##  Gestión de Estudiantes

Visualización de:

* Fotografía
* Nombre completo
* Correo electrónico
* Fecha de inscripción

Incluye búsqueda por:

* Nombre
* Apellido
* Correo electrónico

---

##  Seguimiento de Actividades

Para cada actividad del curso se muestra:

* Número de accesos
* Fecha del último acceso
* Calificación obtenida
* Estado de acceso

Compatible con actividades como:

* Quiz
* Assignment
* Resource
* Page
* URL
* Forum
* Book
* Folder

---

##  Estadísticas

El sistema genera indicadores automáticos como:

| Indicador             | Descripción                       |
| --------------------- | --------------------------------- |
| Total estudiantes     | Cantidad de usuarios inscritos    |
| Estudiantes activos   | Usuarios con actividad registrada |
| Estudiantes inactivos | Usuarios sin registros de acceso  |
| Total actividades     | Recursos y actividades del curso  |

---

##  Filtros Disponibles

El reporte permite filtrar por:

* Curso
* Estudiante
* Fecha inicial
* Fecha final
* Estado de acceso

---

##  Exportación

El reporte puede exportarse en:

* CSV
* Microsoft Excel (.xlsx)

Los archivos incluyen:

* Información del curso
* Estadísticas
* Información de estudiantes
* Accesos por actividad
* Último acceso
* Calificaciones

---

# Arquitectura

```text
local/customreports/
│
├── access.php
├── version.php
├── settings.php
├── index.php
├── export.php
│
├── lang/
│   ├── en/
│   └── es/
│
└── db/
```

---

#  Tecnologías

* Moodle Local Plugin API
* PHP
* SQL
* HTML5
* CSS3
* JavaScript
* Moodle Excel Library
* Moodle CSV Library

---

#  Seguridad

El plugin implementa:

✔ Validación de parámetros

✔ Control de permisos

✔ Consultas SQL parametrizadas

✔ Escape de datos

✔ Acceso restringido mediante capacidades de Moodle

---

#  Compatibilidad

| Plataforma | Compatible                    |
| ---------- | ----------------------------- |
| Moodle 4.3 | ✅                             |
| Moodle 4.4 | ✅                             |
| Moodle 5.x | ✅ (requiere validación final) |

---

#  Flujo del Sistema

```text
Administrador

        │

        ▼

Selecciona Curso

        │

        ▼

Aplica Filtros

        │

        ▼

Consulta Base de Datos

        │

        ▼

Procesamiento

        │

        ▼

Visualización

        │

        ├────────► Exportar CSV

        └────────► Exportar Excel
```

---

#  Información Mostrada

Cada estudiante contiene:

* Nombre
* Email
* Fecha de inscripción

Para cada actividad:

* Cantidad de accesos
* Última fecha de acceso
* Calificación

---

#  Casos de Uso

## Administrador

Consultar el avance de un curso completo.

---

## Coordinador Académico

Detectar estudiantes con poca actividad.

---

## Docente

Revisar el acceso a recursos específicos.

---

## Recursos Humanos

Monitorear capacitaciones corporativas.

---

#  Beneficios

✅ Centraliza la información académica

✅ Reduce tiempos de consulta

✅ Facilita auditorías

✅ Mejora el seguimiento de estudiantes

✅ Permite exportar evidencia

✅ Compatible con cursos corporativos y educativos

---

#  Funcionamiento

1. Seleccionar un curso.
2. Aplicar filtros.
3. Consultar estudiantes.
4. Visualizar estadísticas.
5. Revisar actividades.
6. Exportar resultados.

---

#  Requisitos

| Requisito     | Versión                      |
| ------------- | ---------------------------- |
| Moodle        | 4.3 o superior               |
| PHP           | 8.1 o superior               |
| Base de datos | MySQL / MariaDB / PostgreSQL |
| Permisos      | Manager o Administrador      |

---

#  Versionado

| Versión | Descripción                                |
| ------- | ------------------------------------------ |
| 1.0     | Primera versión                            |
| 2.0     | Mejoras en interfaz, filtros y exportación |

---

#  Mejoras Futuras

* Dashboard con gráficas.
* Indicadores de progreso.
* Exportación PDF.
* Programación automática de reportes.
* Envío por correo electrónico.
* Integración con Finalización de Cursos.
* Filtros por Cohortes y Grupos.
* Paginación para cursos masivos.
* Optimización de consultas SQL.
* Compatibilidad completa con Moodle 5.x.

---

#  Autor

**Felipe Ochoa**

Desarrollador Moodle | WordPress | PHP

Especialista en desarrollo de plugins, automatización de procesos y plataformas LMS.

---

# 📄 Licencia

Este proyecto ha sido desarrollado para su implementación en plataformas Moodle y puede ser adaptado según las necesidades de cada organización.
