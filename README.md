# PropuestaLaboral.io

Trabajo de Desarrollo Web Empresarial (DWE)

---

## 📋 Tabla de Contenidos

- [Descripción](#descripción)  
- [Tecnologías](#tecnologías)  
- [Instalación](#instalación)  
- [Uso](#uso)  
- [Estructura del proyecto](#estructura-del-proyecto)  
- [Branching / Flujo de trabajo](#branching--flujo-de-trabajo)  
- [Autores](#autores)  

---

## 🧐 Descripción

PropuestaLaboral.io es una aplicación web para gestionar propuestas laborales. Permite:

- Registro de usuarios  
- Login / Logout  
- Crear, editar, eliminar propuestas  
- Marcar propuestas como favoritas  
- Ver las propuestas existentes  

Este proyecto es parte de la materia **Desarrollo Web Empresarial (DWE)**.

---

## 🛠 Tecnologías

El proyecto está construido con:

| Tecnología | Uso |
|------------|-----|
| **PHP** | Lógica del backend, conexión con base de datos |
| **MySQL** (o base de datos SQL, importando `trabajo.sql`) | Almacenamiento de datos de usuarios y propuestas |
| **CSS** | Estilos de la interfaz |
| HTML / Formularios | Interfaz de usuario básica |

---

## 🚀 Instalación

1. Cloná este repositorio:  
   ```bash
   git clone https://github.com/romagorda/propuestalaboral.io.git

## ⚙️ Uso

Registro: los usuarios deben registrarse primero (archivo registro.php).

Login: iniciar sesión para poder usar las funciones que requieran autenticación (login.php).

Crear / Editar / Eliminar propuestas: funcionalidades disponibles una vez logueado.

Favoritos: marcar propuestas como favoritas usando favorito.php.

## 📂 Estructura del proyecto

Aquí la estructura de carpetas y archivos principales:

/
├── css/                  # Archivos de estilos
├── conexion.php          # Archivo de conexión a la base de datos
├── crear.php             # Para crear nuevas propuestas
├── editar.php            # Para editar propuestas existentes
├── eliminar.php          # Para eliminar propuestas
├── favorito.php          # Para marcar como favorito
├── index.php             # Página principal / listado de propuestas
├── login.php             # Inicio de sesión
├── logout.php            # Cerrar sesión
├── registro.php          # Registro de usuario
└── trabajo.sql           # Script SQL para crear la base de datos

## 🌱 Branching / Flujo de trabajo

Para colaborar sin conflictos, seguimos este flujo:

main es la rama estable que contiene la versión funcional del proyecto.

Cada quien crea su propia rama para trabajar en su parte, con nombre claro, por ejemplo:

git checkout -b joaco-parteX


Trabajar, hacer commits frecuentes:

git add .
git commit -m "Descripción clara de lo que hice"


Pushear tu rama al remoto:

git push origin joaco-parteX


Crear un Pull Request para que se revise y se fusione a main cuando esté listo.

## 🧑‍🤝‍🧑 Autores

Roman Quiroga

Joaquin Marek

Camila Gonzalez
