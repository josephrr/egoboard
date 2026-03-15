# Egoboard

Egoboard es una aplicacion web construida con Laravel para crear muros de notas compartidos por sala. La idea es simple: un docente o facilitador crea una sala, comparte un enlace y los participantes pueden dejar notas con su nombre y su mensaje en un tablero visual.

Este proyecto esta pensado para actividades educativas, sesiones de descubrimiento, levantamiento de problemas, lluvia de ideas y dinamicas de clase.

## Licencia

Este proyecto se publica para uso libre bajo licencia MIT. Puedes usarlo, modificarlo, adaptarlo y publicarlo respetando los terminos de la licencia incluidos en [LICENSE](./LICENSE).

## Creditos

- Autor y creador: Joseph Rodriguez
- Marca y desarrollo: [Egobytes](https://egobytes.com)

Si reutilizas este proyecto, mantener una referencia visible a Joseph Rodriguez y Egobytes es una buena practica y una forma justa de reconocer el trabajo original.

## Stack

- PHP 8.2+
- Laravel 12
- Blade
- Tailwind CSS 4
- Vite
- SQLite o MySQL

## Funcionalidades actuales

- Crear salas publicas por enlace
- Publicar notas con nombre y mensaje
- Organizar notas dentro de cada sala
- Interfaz visual tipo board
- Modal para crear nuevas notas
- Guardado persistente en base de datos

## Casos de uso

- Muro de dolores
- Lluvia de ideas
- Recoleccion de feedback
- Descubrimiento de oportunidades
- Actividades participativas en clase
- Retrospectivas de equipos

## Estructura general

La app esta organizada como un proyecto Laravel convencional:

- `app/Http/Controllers` contiene la logica HTTP
- `app/Models` contiene los modelos Eloquent
- `database/migrations` contiene la estructura de la base de datos
- `resources/views` contiene las vistas Blade
- `resources/css` y `resources/js` contienen los assets del frontend
- `routes/web.php` define las rutas web

## Instalacion local

### 1. Clonar el repositorio

```bash
git clone https://github.com/josephrr/egoboard.git
cd egoboard
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Crear archivo de entorno

```bash
cp .env.example .env
```

En Windows PowerShell puedes usar:

```powershell
Copy-Item .env.example .env
```

### 4. Generar clave de aplicacion

```bash
php artisan key:generate
```

### 5. Configurar base de datos

Puedes usar SQLite o MySQL.

#### Opcion A: SQLite

1. Crea el archivo:

```bash
touch database/database.sqlite
```

2. Ajusta tu `.env`:

```env
DB_CONNECTION=sqlite
```

Si usas una version de Laravel que requiere ruta explicita, agrega:

```env
DB_DATABASE=database/database.sqlite
```

#### Opcion B: MySQL

Ajusta estas variables en `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=egoboard
DB_USERNAME=root
DB_PASSWORD=
```

### 6. Ejecutar migraciones

```bash
php artisan migrate
```

### 7. Instalar dependencias frontend

```bash
npm install
```

### 8. Ejecutar en desarrollo

Puedes levantar Laravel y Vite por separado:

```bash
php artisan serve
npm run dev
```

O usar el script de Composer:

```bash
composer run dev
```

## Build para produccion

Para compilar los assets:

```bash
npm run build
```

Luego asegúrate de tener:

- `APP_ENV=production`
- `APP_DEBUG=false`
- una base de datos configurada
- permisos correctos en `storage/` y `bootstrap/cache/`

Opcionalmente puedes optimizar Laravel:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Guia rapida de implementacion

Si quieres adaptar Egoboard para tu propia institucion o marca, esta es una ruta recomendada:

### 1. Personaliza identidad visual

Edita estos archivos:

- `resources/views/components/layouts/app.blade.php`
- `resources/css/app.css`
- `resources/views/rooms/index.blade.php`
- `resources/views/rooms/show.blade.php`

Aqui puedes cambiar:

- nombre del proyecto
- tipografias
- colores
- footer
- textos del flujo

### 2. Ajusta reglas de negocio

La logica base esta en:

- `app/Http/Controllers/RoomController.php`
- `app/Http/Controllers/NoteController.php`
- `app/Models/Room.php`
- `app/Models/Note.php`

Desde ahi puedes modificar:

- validacion del nombre
- longitud de las notas
- orden del tablero
- comportamiento de las salas

### 3. Extiende base de datos

Para nuevas funciones, crea migraciones. Algunas ideas:

- categorias por nota
- reacciones o votos
- moderacion
- cierre de sala
- exportacion CSV
- sala privada para administracion

### 4. Publica en hosting

Puedes desplegarlo en:

- un VPS con Nginx o Apache
- cPanel con soporte PHP
- Laravel Forge
- Ploi
- Railway
- Render

Pasos generales:

1. Subir codigo
2. Configurar `.env`
3. Ejecutar `composer install --no-dev`
4. Ejecutar `php artisan migrate --force`
5. Ejecutar `npm install && npm run build`
6. Apuntar el document root a `public/`

## Flujo de uso

### Para docentes o facilitadores

1. Entra a la pagina principal
2. Crea una sala con nombre y descripcion
3. Copia el enlace generado
4. Comparte el enlace con tus estudiantes o participantes

### Para estudiantes o participantes

1. Abren el enlace de la sala
2. Escriben su nombre
3. Dejan su nota
4. Ven el board actualizado

## Pruebas

Para ejecutar pruebas:

```bash
php artisan test
```

## Recomendaciones antes de publicar en GitHub

- Verifica que `.env` no este versionado
- Usa un nombre y descripcion claros en el repo
- Agrega capturas de pantalla en una carpeta como `docs/` o `screenshots/`
- Completa el enlace real del repositorio en este README
- Mantén la licencia visible

## Ideas para siguientes versiones

- Vista privada para docente
- QR para compartir salas
- Exportar resultados
- Reacciones por nota
- Anonimato opcional
- Filtros por categoria
- Moderacion de contenido
- Salas con fecha de cierre

## Soporte y atribucion

Si este proyecto te resulta util y quieres usarlo como base para tu institucion, taller o producto, puedes mantener el credito original:

`Creado por Joseph Rodriguez - Egobytes`

Sitio oficial:

- [https://egobytes.com](https://egobytes.com)
