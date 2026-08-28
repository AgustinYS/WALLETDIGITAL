GESTOR DE GASTOS PERSONALES - PHP + MYSQL

REQUISITOS
- XAMPP
- Navegador

INSTALACION
1. Copia la carpeta gestor_gastos_simple dentro de C:\xampp\htdocs\
2. Abre XAMPP e inicia Apache y MySQL.
3. Entra a http://localhost/phpmyadmin
4. Ve a Importar y selecciona database.sql.
5. Abre http://localhost/gestor_gastos_simple/
6. Crea una cuenta.
7. Define tu presupuesto mensual.
8. Agrega ingresos y gastos.

BASE DE DATOS
- usuarios
- categorias
- movimientos

ARCHIVOS PRINCIPALES
- config.php: conexión MySQL y sesión
- login.php: inicio de sesión
- registro.php: registro de usuarios
- dashboard.php: resumen mensual
- movimientos.php: agregar/eliminar movimientos
- presupuesto.php: presupuesto mensual
- logout.php: cerrar sesión
- database.sql: creación de base de datos
- assets/style.css: diseño

CONFIGURACION MYSQL
Por defecto usa:
Host: localhost
Base de datos: gestor_gastos
Usuario: root
Contraseña: vacía

Si tu MySQL tiene otra contraseña, modifica config.php.
