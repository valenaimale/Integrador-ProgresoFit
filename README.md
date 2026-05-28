# ProgresoFit

Plataforma web de gestión de gimnasios y rutinas de entrenamiento.

## Stack tecnológico

| Capa | Tecnología |
|------|------------|
| Frontend | PHP 8.3 (MVC propio) |
| Backend API | Node.js + Express |
| Base de datos | MySQL 8.0 |
| Infraestructura | Docker + Docker Compose |

---

## Requisito previo

Tener instalado **[Docker Desktop](https://www.docker.com/products/docker-desktop/)** y que esté corriendo.

---

## Instalación y puesta en marcha

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio>
cd Integrador-php
```

### 2. Levantar todo con un solo comando

Desde la carpeta `Integrador-php/`:

```bash
docker compose up --build
```

La **primera vez** tarda unos minutos mientras descarga las imágenes de Node.js, PHP y MySQL.

Esto levanta automáticamente:

| Servicio | URL / Puerto |
|----------|-------------|
| Frontend PHP | http://localhost:8000 |
| Backend API | http://localhost:3000 |
| MySQL | localhost:3306 |

### 3. Importar los datos de ejemplo (primera vez)

Una vez que los contenedores estén corriendo, importá el backup con el gimnasio y usuarios de prueba:

```bash
tail -n +2 backend/backup_progresofit.sql | docker exec -i progresofit-mysql mysql -u progresofit_user -pprogresofit_pass progresofit
```

> **Nota:** El `tail -n +2` omite la primera línea del archivo (que puede causar errores). El warning `Using a password on the command line...` es normal, no es un error.

> **Persistencia:** Los datos se guardan en un volumen Docker y **sobreviven** a `docker compose down`. Solo necesitás volver a importar el backup si hacés `docker compose down -v` (que borra los volúmenes).

### 4. Abrir la aplicación

Abrí **http://localhost:8000** en el navegador.

---

## Uso de la aplicación

### Registro e inicio de sesión

1. Hacé clic en **Registro** para crear una cuenta nueva.
2. Completá el formulario con nombre, email y contraseña.
3. Iniciá sesión con tus credenciales.

### Crear un usuario ADMIN (para gestionar gimnasios)

El primer ADMIN se crea desde la terminal porque el rol ADMIN no está disponible en el formulario público:

```bash
curl -X POST http://localhost:3000/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@progresofit.com","password":"admin123","rol":"ADMIN"}'
```

Con ese usuario podés crear, editar y dar de baja gimnasios desde la API (ver documentación del backend en `ProgresoFitBackend/README.md`).

---

## Detener la aplicación

```bash
# Detener los contenedores (los datos se conservan)
docker compose down

# Detener y borrar todos los datos (reset completo)
docker compose down -v
```

---

## Correr sin Docker (desarrollo local)

Si preferís no usar Docker, necesitás PHP 8.1+, Composer, Node.js y MySQL instalados localmente.

**Backend:**
```bash
cd backend
# Editar .env: cambiar DB_HOST=mysql → DB_HOST=localhost
npm install
npm start
# API disponible en http://localhost:3000
```

**Frontend:**
```bash
# Desde la raíz del proyecto
composer install
php -S localhost:8000 -t public/
# Sitio disponible en http://localhost:8000
```

---

## Estructura del proyecto

```
Integrador-php/
├── backend/                   # Backend Node.js + Express
│   ├── src/server.js          # Entrada de la API
│   ├── controllers/           # Lógica de endpoints
│   ├── services/              # Reglas de negocio
│   ├── repositories/          # Consultas SQL
│   ├── middleware/            # Autenticación JWT
│   ├── database/              # Conexión MySQL
│   ├── backup_progresofit.sql # Datos de ejemplo
│   └── Dockerfile             # Imagen Docker del backend
├── public/
│   ├── index.php              # Punto de entrada HTTP
│   └── assets/css/            # Hojas de estilo
├── src/
│   ├── app/
│   │   ├── controllers/       # Controladores MVC
│   │   ├── services/
│   │   │   └── ApiClient.php  # Cliente HTTP → backend
│   │   └── views/             # Vistas PHP
│   └── core/
│       ├── Router.php         # Enrutador
│       └── Request.php        # Requests HTTP
├── Dockerfile                 # Imagen Docker del frontend PHP
├── docker-compose.yml         # Orquestación de todos los servicios
└── README.md                  # Este archivo
```

---

## Variables de entorno

El `docker-compose.yml` ya incluye todos los valores necesarios. Si corrés sin Docker, creá un archivo `.env` en `Integrador-php/` con:

```env
API_BASE_URL=http://localhost:3000
```

---

## Solución de problemas

**Los contenedores no levantan:**
```bash
docker compose logs
```

**El frontend no conecta con el backend:**
```bash
docker compose logs frontend
docker compose logs backend
```

**Reiniciar desde cero:**
```bash
docker compose down -v
docker compose up --build
```

**El backup falla al importarse:**  
Asegurate de que MySQL esté `healthy` antes de importar:
```bash
docker compose ps   # la columna STATUS del servicio mysql debe decir "healthy"
```
