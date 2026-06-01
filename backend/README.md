# ProgresoFit — Backend API

API REST para la plataforma de gestión de gimnasios, rutinas y usuarios. Desarrollada en Node.js + Express con MySQL y Docker.

---

## Cómo levantar el proyecto

### Pre-requisitos
- **Docker Desktop** instalado y corriendo.

### Pasos

1. **Levantar los contenedores** (desde la raíz del proyecto, no desde `/backend`)
   ```bash
   docker compose up -d --build
   ```
   La primera vez tarda unos minutos (descarga imágenes de Node, PHP y MySQL).

2. **Verificar que esté funcionando**
   ```bash
   curl http://localhost:3000/
   # {"message":"API ProgresoFit funcionando"}
   ```
   Si no responde: `docker compose logs backend`

3. **Restaurar la base de datos base** (tablas `usuarios` y `gimnasios`)
   ```bash
   tail -n +2 backend/backup_progresofit.sql | \
     docker exec -i progresofit-mysql bash -c "mysql -uroot -proot_password progresofit"
   ```
   > El `tail -n +2` saltea la primera línea del dump que contiene un warning de mysqldump que no es SQL válido.

4. **Ejecutar la migración** (crea las 6 tablas nuevas y columnas en `gimnasios`)
   ```bash
   cat backend/database/migrations/create_tables.sql | \
     docker exec -i progresofit-mysql bash -c "mysql -uroot -proot_password progresofit"
   ```

5. **Verificar las tablas**
   ```bash
   docker exec progresofit-mysql bash -c \
     "mysql -uroot -proot_password progresofit -e 'SHOW TABLES;' 2>/dev/null"
   ```
   Deben aparecer 9 tablas: `alumno_rutinas`, `ejercicios`, `entrenadores`, `entrenamiento_ejercicios`, `entrenamientos`, `gimnasios`, `rutinas`, `suscripciones`, `usuarios`.

### Credenciales de Docker

| Servicio | URL | Usuario | Password |
|----------|-----|---------|----------|
| Backend API | `http://localhost:3000` | — | — |
| Frontend PHP | `http://localhost:8000` | — | — |
| MySQL | `localhost:3306` | `progresofit_user` | `progresofit_pass` |
| MySQL (root) | `localhost:3306` | `root` | `root_password` |
| Base de datos | `progresofit` | — | — |

---

## Autenticación (JWT)

La API usa **JSON Web Tokens**. El flujo es:

1. Hacés login → el backend devuelve un `token`.
2. En cada request protegida lo enviás en el header:
   ```
   Authorization: Bearer <TOKEN>
   ```

Los roles posibles son `ADMIN`, `ENTRENADOR` y `ALUMNO`. Cada endpoint indica qué roles tienen acceso.

---

## Arquitectura del código

Cada feature sigue el mismo patrón de 4 capas:

```
Request HTTP
    ↓
Route       → define la URL + qué middlewares aplica
    ↓
Controller  → parsea req, llama al service, devuelve res (sin lógica de negocio)
    ↓
Service     → lógica de negocio (quién puede hacer qué, validaciones)
    ↓
Repository  → SQL puro contra la base de datos
```

Si querés cambiar una regla de negocio → tocás el Service.
Si querés optimizar una query → tocás el Repository.
El Controller nunca escribe SQL. El Repository nunca verifica roles.

---

## Endpoints

### Auth — `/auth`

#### `POST /auth/register`
Registra un usuario. Útil para crear el primer ADMIN en desarrollo.

- **Protegido**: NO
- **Body**: `{ "email", "password", "rol" }` — `rol` es opcional, por defecto `ALUMNO`
- **Respuesta 201**: `{ "message", "user": { id, email, rol } }`
- **Errores**: `400` email ya registrado, email o password faltante

#### `POST /auth/login`
Devuelve un token JWT.

- **Protegido**: NO
- **Body**: `{ "email", "password" }`
- **Respuesta 200**: `{ "token", "user": { id, email, rol } }`
- **Errores**: `401` credenciales inválidas

#### `POST /auth/logout`
El cliente es responsable de descartar el token. Este endpoint solo confirma.

- **Protegido**: SÍ
- **Respuesta 200**: `{ "message" }`

---

### Usuarios — `/usuarios`

#### `POST /usuarios/alumno`
Registra un nuevo alumno. Endpoint público para el formulario de registro.

- **Protegido**: NO
- **Body**: `{ "nombre", "email", "password" }` — `nombre` es opcional
- **Respuesta 201**: `{ "message", "user": { id, nombre, email, rol } }`

#### `POST /usuarios/entrenador`
Crea un entrenador. Solo ADMIN.

- **Protegido**: SÍ + **Rol ADMIN**
- **Body**: `{ "nombre", "email", "password" }`
- **Respuesta 201**: `{ "message", "user": { id, nombre, email, rol } }`

#### `GET /usuarios/:id`
Devuelve el perfil de un usuario.

- **Protegido**: SÍ (cualquier rol)
- **Respuesta 200**: `{ id, nombre, email, rol, created_at }`

#### `PUT /usuarios/:id`
Edita el perfil. Un usuario solo puede editar el suyo propio; un ADMIN puede editar cualquiera.

- **Protegido**: SÍ
- **Body** (todos opcionales, solo se actualizan los enviados): `{ "nombre", "email", "password" }`
- **Respuesta 200**: `{ "message", "user": { id, nombre, email, rol, created_at } }`
- **Errores**: `403` intentando editar el perfil de otro usuario

---

### Gimnasios — `/gimnasios`

#### `POST /gimnasios/registrar`
Registro público de un gimnasio. No requiere autenticación.

- **Protegido**: NO
- **Body**: `{ "nombre", "direccion", "horarios", "telefono", "email", "descripcion", "servicios" }`
- **Respuesta 201**: `{ "message", "gimnasio": { id, nombre, ...campos } }`
- **Errores**: `400` nombre faltante

#### `GET /gimnasios`
Lista todos los gimnasios activos.

- **Protegido**: SÍ (cualquier rol)
- **Query params**: `?search=Central` (opcional)
- **Respuesta 200**: `[ { id, nombre, direccion, horarios, activo } ]`

#### `GET /gimnasios/:id`
Devuelve un gimnasio por ID.

- **Protegido**: SÍ (cualquier rol)
- **Respuesta 200**: `{ id, nombre, direccion, horarios, activo }`
- **Errores**: `404` no encontrado

#### `POST /gimnasios`
Crea un gimnasio. Solo ADMIN.

- **Protegido**: SÍ + **Rol ADMIN**
- **Body**: `{ "nombre", "direccion", "horarios" }`
- **Respuesta 201**: `{ id, nombre, direccion, horarios, activo }`

#### `PUT /gimnasios/:id`
Actualiza un gimnasio. Solo ADMIN.

- **Protegido**: SÍ + **Rol ADMIN**
- **Body**: `{ "nombre", "direccion", "horarios" }`

#### `DELETE /gimnasios/:id`
Baja lógica (marca `activo = FALSE`). Solo ADMIN.

- **Protegido**: SÍ + **Rol ADMIN**
- **Respuesta 200**: `{ "message" }`

---

### Rutinas — `/rutinas`

#### `GET /rutinas`
Lista rutinas. El resultado varía según el rol del usuario autenticado:

- **ALUMNO** → ve únicamente las rutinas que le fueron asignadas
- **ENTRENADOR / ADMIN** → ve todas las rutinas

- **Protegido**: SÍ (cualquier rol)
- **Respuesta 200**:
  ```json
  [
    {
      "id": 1,
      "titulo": "Rutina Fuerza Total",
      "descripcion": "...",
      "objetivo": "Hipertrofia",
      "created_at": "2026-05-31T22:27:30.000Z",
      "entrenador_nombre": "Carlos Entrenador"
    }
  ]
  ```

#### `GET /rutinas/:id`
Devuelve el detalle completo de una rutina con sus días de entrenamiento y los ejercicios de cada día.

- **Protegido**: SÍ (cualquier rol)
- **Respuesta 200**:
  ```json
  {
    "id": 1,
    "titulo": "Rutina Fuerza Total",
    "descripcion": "...",
    "objetivo": "Hipertrofia",
    "entrenador_nombre": "Carlos Entrenador",
    "dias": [
      {
        "id": 1,
        "nombre_dia": "Lunes",
        "grupo_muscular": "Pecho y Tríceps",
        "duracion_minutos": 60,
        "orden": 1,
        "ejercicios": [
          {
            "id": 1,
            "nombre": "Press de banca",
            "musculos": "Chest",
            "equipamiento": "Barbell",
            "dificultad": "media",
            "series_repeticiones": "4x8",
            "orden": 1
          }
        ]
      }
    ]
  }
  ```
- **Errores**: `404` rutina no encontrada

#### `POST /rutinas`
Crea una nueva rutina. El `entrenador_id` se toma del JWT automáticamente.

- **Protegido**: SÍ + **Rol ENTRENADOR o ADMIN**
- **Body**: `{ "titulo", "descripcion", "objetivo" }` — solo `titulo` es obligatorio
- **Respuesta 201**: `{ id, titulo, descripcion, objetivo, entrenador_id }`

#### `PUT /rutinas/:id`
Edita una rutina. Un ENTRENADOR solo puede editar las suyas propias.

- **Protegido**: SÍ + **Rol ENTRENADOR o ADMIN**
- **Body**: `{ "titulo", "descripcion", "objetivo" }`
- **Errores**: `403` intentando editar una rutina ajena

#### `DELETE /rutinas/:id`
Elimina una rutina (y en cascada sus días y ejercicios). Un ENTRENADOR solo puede eliminar las suyas.

- **Protegido**: SÍ + **Rol ENTRENADOR o ADMIN**
- **Errores**: `403` intentando eliminar una rutina ajena

#### `POST /rutinas/:id/dias`
Agrega un día de entrenamiento a la rutina.

- **Protegido**: SÍ + **Rol ENTRENADOR o ADMIN**
- **Body**: `{ "nombre_dia", "grupo_muscular", "duracion_minutos", "orden" }` — solo `nombre_dia` es obligatorio
- **Respuesta 201**: `{ id, rutina_id, nombre_dia, grupo_muscular, duracion_minutos, orden }`

#### `DELETE /rutinas/:rutinaId/dias/:diaId`
Elimina un día de entrenamiento (y en cascada sus ejercicios).

- **Protegido**: SÍ + **Rol ENTRENADOR o ADMIN**
- **Errores**: `400` si el día no pertenece a esa rutina

#### `POST /rutinas/:rutinaId/dias/:diaId/ejercicios`
Agrega un ejercicio a un día. Recibe el ID del ejercicio en la API externa (`api_ejercicio_id`). Si el ejercicio no está en la base de datos local, lo descarga y guarda automáticamente (lazy-cache).

- **Protegido**: SÍ + **Rol ENTRENADOR o ADMIN**
- **Body**: `{ "api_ejercicio_id", "series_repeticiones", "orden" }`
- **Respuesta 201**: `{ id, entrenamiento_id, ejercicio_id, series_repeticiones, orden }`

#### `DELETE /rutinas/:rutinaId/dias/:diaId/ejercicios/:pivotId`
Quita un ejercicio de un día.

- **Protegido**: SÍ + **Rol ENTRENADOR o ADMIN**

#### `POST /rutinas/:id/asignar`
Asigna una rutina a un alumno.

- **Protegido**: SÍ + **Rol ENTRENADOR o ADMIN**
- **Body**: `{ "alumno_id": 5 }`
- **Respuesta 201**: `{ "message": "Rutina asignada exitosamente" }`
- **Errores**: `400` si ya estaba asignada

#### `DELETE /rutinas/:rutinaId/asignar/:alumnoId`
Quita la asignación de una rutina a un alumno.

- **Protegido**: SÍ + **Rol ENTRENADOR o ADMIN**

---

### Ejercicios — `/ejercicios`

Los ejercicios se obtienen en tiempo real desde la API pública de **wger.de** (gratuita, sin API key). El backend actúa como proxy y transforma la respuesta al formato de la aplicación.

#### `GET /ejercicios`
Lista ejercicios del catálogo externo con paginación.

- **Protegido**: SÍ (cualquier rol)
- **Query params**: `?limit=20&offset=0`
- **Respuesta 200**:
  ```json
  {
    "total": 849,
    "ejercicios": [
      {
        "api_id": 1962,
        "nombre": "Step Jack",
        "descripcion": "...",
        "musculos": "Quads",
        "equipamiento": "none (bodyweight exercise)",
        "dificultad": "media",
        "video_url": null,
        "categoria": "Cardio"
      }
    ]
  }
  ```

#### `GET /ejercicios/:id`
Devuelve el detalle de un ejercicio por su `api_id` (el ID de wger).

- **Protegido**: SÍ (cualquier rol)
- **Respuesta 200**: mismo formato que un ítem del listado
- **Errores**: `404` ejercicio no encontrado en la API externa

---

### Entrenadores — `/entrenadores`

Los entrenadores son usuarios con `rol = 'ENTRENADOR'`. Esta sección maneja su perfil extendido (especialidad, descripción, horario, gimnasio).

#### `GET /entrenadores`
Lista todos los usuarios con rol ENTRENADOR junto con su perfil y gimnasio.

- **Protegido**: SÍ (cualquier rol)
- **Respuesta 200**:
  ```json
  [
    {
      "id": 3,
      "nombre": "Carlos Entrenador",
      "email": "carlos@test.com",
      "perfil_id": 1,
      "especialidad": "Fuerza y Acondicionamiento",
      "descripcion": "...",
      "horario": "Lunes a Viernes 7:00-13:00",
      "gimnasio_id": 1,
      "gimnasio_nombre": "Gimnasio Central"
    }
  ]
  ```

#### `GET /entrenadores/:id`
Devuelve el perfil de un entrenador por su `usuario_id`.

- **Protegido**: SÍ (cualquier rol)
- **Respuesta 200**: mismo formato que un ítem del listado
- **Errores**: `404` usuario no encontrado o no es entrenador

#### `PUT /entrenadores/:id`
Crea o actualiza el perfil de un entrenador (upsert). Un ENTRENADOR solo puede editar su propio perfil. Un ADMIN puede editar cualquiera.

- **Protegido**: SÍ + **Rol ENTRENADOR o ADMIN**
- **Body**: `{ "especialidad", "descripcion", "horario", "gimnasio_id" }` — todos opcionales
- **Respuesta 200**: perfil completo actualizado
- **Errores**: `403` intentando editar el perfil de otro entrenador

---

### Suscripciones — `/suscripciones`

#### `GET /suscripciones/planes`
Lista los planes disponibles. No requiere rol específico.

- **Protegido**: SÍ (cualquier rol)
- **Respuesta 200**:
  ```json
  [
    { "nombre": "basico",  "precio": 2999, "duracion_dias": 30 },
    { "nombre": "premium", "precio": 4999, "duracion_dias": 30 },
    { "nombre": "elite",   "precio": 7999, "duracion_dias": 30 }
  ]
  ```

#### `GET /suscripciones/mi-suscripcion`
Devuelve la suscripción activa del alumno autenticado, o `null` si no tiene ninguna.

- **Protegido**: SÍ + **Rol ALUMNO**
- **Respuesta 200**:
  ```json
  {
    "id": 1,
    "usuario_id": 2,
    "plan": "premium",
    "precio": "4999.00",
    "estado": "activa",
    "fecha_inicio": "2026-05-31T00:00:00.000Z",
    "fecha_fin": "2026-06-30T00:00:00.000Z",
    "created_at": "..."
  }
  ```

#### `POST /suscripciones`
Suscribe al alumno autenticado a un plan. Si ya tenía una suscripción activa, la cancela automáticamente antes de crear la nueva.

- **Protegido**: SÍ + **Rol ALUMNO**
- **Body**: `{ "plan": "premium" }`
- **Respuesta 201**: objeto de suscripción creada
- **Errores**: `400` plan inválido (opciones: `basico`, `premium`, `elite`)

---

## Códigos de respuesta

| Código | Cuándo ocurre |
|--------|---------------|
| `200` | OK |
| `201` | Recurso creado exitosamente |
| `400` | Datos inválidos o faltantes |
| `401` | Token no proporcionado |
| `403` | Token inválido/expirado, o rol sin permiso |
| `404` | Recurso no encontrado |
| `500` | Error interno del servidor |

---

## Guía de integración con el frontend PHP

El frontend PHP en `http://localhost:8000` se comunica con esta API usando `ApiClient.php` (curl). A continuación se detalla exactamente qué tiene que hacer cada controller PHP para activar cada feature.

### Paso previo — agregar `put()` al ApiClient

`ApiClient` solo tiene `get()` y `post()`. Necesitás agregar este método para editar perfiles:

```php
// src/app/services/ApiClient.php
public function put(string $path, array $data, ?string $token = null): array
{
    return $this->request('PUT', $path, $data, $token);
}
```

---

### Feature 1 — Rutinas (listado y detalle)

**`Router.php`** — reemplazá las rutas estáticas:
```php
// Borrá las 4 rutas de rutina1/2/3/4 y reemplazá por:
$this->register('GET@/rutinas', 'RutinasController@listar');
$this->register('GET@/rutina',  'RutinaIndController@mostrar'); // una sola, con ?id=
```

**`RutinasController.php`**:
```php
use PAW\app\services\ApiClient;

public function listar() {
    $this->api = new ApiClient();
    $response  = $this->api->get('/rutinas', $_SESSION['jwt'] ?? null);
    $rutinas   = $response['ok'] ? $response['data'] : [];
    require $this->viewsDir . 'rutinas.view.php';
    // Vista recibe: $rutinas → array con id, titulo, descripcion, objetivo, entrenador_nombre
    // Cada card linkea a: /rutina?id=X
}
```

**`RutinaIndController.php`**:
```php
public function mostrar() {
    $this->api = new ApiClient();
    $id        = $_GET['id'] ?? null;
    $response  = $this->api->get("/rutinas/{$id}", $_SESSION['jwt'] ?? null);
    $rutina    = $response['ok'] ? $response['data'] : null;
    require $this->viewsDir . 'rutina_ind.view.php';
    // Vista recibe: $rutina con estructura anidada:
    // $rutina['titulo'], $rutina['objetivo']
    // $rutina['dias'][] → nombre_dia, grupo_muscular, duracion_minutos
    //   $dia['ejercicios'][] → nombre, musculos, series_repeticiones
}
```

---

### Feature 2 — Ejercicios (catálogo y detalle)

**`Router.php`**:
```php
$this->register('GET@/ejercicios', 'EjercicioController@listar');  // catálogo
$this->register('GET@/ejercicio',  'EjercicioController@mostrar'); // detalle
```

**`EjercicioController.php`**:
```php
public function listar() {
    $this->api = new ApiClient();
    $offset    = (int)($_GET['offset'] ?? 0);
    $response  = $this->api->get("/ejercicios?limit=20&offset={$offset}", $_SESSION['jwt'] ?? null);
    $resultado  = $response['ok'] ? $response['data'] : ['total' => 0, 'ejercicios' => []];
    $ejercicios = $resultado['ejercicios'];
    $total      = $resultado['total'];
    require $this->viewsDir . 'ejercicios.view.php';
    // Cada card linkea a: /ejercicio?id=X (donde X es el api_id)
}

public function mostrar() {
    $this->api  = new ApiClient();
    $apiId      = $_GET['id'] ?? null;
    $response   = $this->api->get("/ejercicios/{$apiId}", $_SESSION['jwt'] ?? null);
    $ejercicio  = $response['ok'] ? $response['data'] : null;
    require $this->viewsDir . 'ejercicio.view.php';
    // Vista recibe: $ejercicio con nombre, descripcion, musculos, equipamiento, dificultad
}
```

---

### Feature 3 — Perfil de usuario (ver y editar)

El `GET` ya funciona. Solo necesitás agregar el `POST` para guardar cambios.

**`Router.php`**:
```php
$this->register('GET@/perfil-user',  'PerfilUserController@mostrar');
$this->register('POST@/perfil-user', 'PerfilUserController@actualizar'); // nuevo
```

**`PerfilUserController.php`** — agregá el método `actualizar()`:
```php
public function actualizar() {
    if (empty($_SESSION['user'])) { header('Location: /inicio-sesion'); exit; }

    $userId = $_SESSION['user']['id'];
    // array_filter descarta los campos vacíos para no pisarlos en la API
    $data = array_filter([
        'nombre'   => $_POST['nombre']   ?? '',
        'email'    => $_POST['email']    ?? '',
        'password' => $_POST['password'] ?? '',
    ]);

    $response = $this->api->put("/usuarios/{$userId}", $data, $_SESSION['jwt']);

    if ($response['ok']) {
        $_SESSION['user'] = array_merge($_SESSION['user'], $data);
        header('Location: /perfil-user');
        exit;
    }

    $error    = $response['data']['error'] ?? 'Error al actualizar';
    $userData = $_SESSION['user'];
    require $this->viewsDir . 'perfilUser.view.php';
}
```

La vista necesita un `<form method="POST" action="/perfil-user">` con campos `nombre`, `email` y `password`.

---

### Feature 4 — Perfil de entrenador

**`Router.php`**:
```php
$this->register('GET@/entrenadores',       'EntrenadoresController@listar');
$this->register('GET@/perfil-entrenador',  'PerfilEntrenadorController@mostrar');
$this->register('POST@/perfil-entrenador', 'PerfilEntrenadorController@actualizar');
```

**`PerfilEntrenadorController.php`** — ahora hace llamadas reales:
```php
public function mostrar() {
    $this->api  = new ApiClient();
    // Si viene ?id=X lo usa; si no, usa el usuario logueado
    $id         = $_GET['id'] ?? ($_SESSION['user']['id'] ?? null);
    $response   = $this->api->get("/entrenadores/{$id}", $_SESSION['jwt'] ?? null);
    $entrenador = $response['ok'] ? $response['data'] : null;
    require $this->viewsDir . 'perfilEntrenador.view.php';
    // Vista recibe: $entrenador con nombre, especialidad, descripcion, horario, gimnasio_nombre
}

public function actualizar() {
    $userId = $_SESSION['user']['id'];
    $data   = [
        'especialidad' => $_POST['especialidad'] ?? '',
        'descripcion'  => $_POST['descripcion']  ?? '',
        'horario'      => $_POST['horario']       ?? '',
        'gimnasio_id'  => $_POST['gimnasio_id']  ?? null,
    ];
    $this->api->put("/entrenadores/{$userId}", $data, $_SESSION['jwt']);
    header('Location: /perfil-entrenador');
    exit;
}
```

**Nuevo `EntrenadoresController.php`** para el listado público:
```php
public function listar() {
    $this->api    = new ApiClient();
    $response     = $this->api->get('/entrenadores', $_SESSION['jwt'] ?? null);
    $entrenadores = $response['ok'] ? $response['data'] : [];
    require $this->viewsDir . 'entrenadores.view.php';
}
```

---

### Feature 5 — Suscripciones

**`Router.php`**:
```php
$this->register('GET@/pagos-suscripcion',  'PagosuscripcionController@mostrar');
$this->register('POST@/pagos-suscripcion', 'PagosuscripcionController@suscribirse');
```

**`PagosuscripcionController.php`**:
```php
public function mostrar() {
    $this->api   = new ApiClient();
    $token       = $_SESSION['jwt'] ?? null;
    $planesResp  = $this->api->get('/suscripciones/planes', $token);
    $suscripResp = $this->api->get('/suscripciones/mi-suscripcion', $token);
    $planes      = $planesResp['ok']  ? $planesResp['data']  : [];
    $suscripcion = $suscripResp['ok'] ? $suscripResp['data'] : null;
    require $this->viewsDir . 'pagosuscripcion.view.php';
    // Vista recibe:
    // $planes → [{ nombre, precio, duracion_dias }]
    // $suscripcion → { plan, precio, estado, fecha_inicio, fecha_fin } o null
}

public function suscribirse() {
    $this->api = new ApiClient();
    $plan      = $_POST['plan'] ?? '';
    $response  = $this->api->post('/suscripciones', ['plan' => $plan], $_SESSION['jwt']);
    header('Location: /pagos-suscripcion');
    exit;
}
```

La vista muestra los planes disponibles con un botón por cada uno. Cada botón es un `<form method="POST" action="/pagos-suscripcion">` con un `<input type="hidden" name="plan" value="basico">` (o premium/elite).

---

### Feature 6 — Asignación de rutinas a alumnos

Esta acción vive dentro del panel del entrenador (Feature 7).

**`Router.php`**:
```php
$this->register('POST@/asignar-rutina', 'PanelEntrenadorController@asignarRutina');
```

**`PanelEntrenadorController.php`** — método `asignarRutina()`:
```php
public function asignarRutina() {
    $this->api = new ApiClient();
    $rutinaId  = $_POST['rutina_id'] ?? null;
    $alumnoId  = $_POST['alumno_id'] ?? null;
    $this->api->post("/rutinas/{$rutinaId}/asignar", ['alumno_id' => $alumnoId], $_SESSION['jwt']);
    header('Location: /panel-entrenador');
    exit;
}
```

La vista del panel tiene un formulario con un `<select>` de alumnos y otro de rutinas.

---

### Feature 7 — Panel del entrenador (CRUD de rutinas)

**`Router.php`**:
```php
$this->register('GET@/panel-entrenador',   'PanelEntrenadorController@index');
$this->register('POST@/crear-rutina',      'PanelEntrenadorController@crearRutina');
$this->register('POST@/editar-rutina',     'PanelEntrenadorController@editarRutina');
$this->register('POST@/eliminar-rutina',   'PanelEntrenadorController@eliminarRutina');
$this->register('POST@/agregar-dia',       'PanelEntrenadorController@agregarDia');
$this->register('POST@/agregar-ejercicio', 'PanelEntrenadorController@agregarEjercicio');
$this->register('POST@/asignar-rutina',    'PanelEntrenadorController@asignarRutina');
```

**Nuevo `PanelEntrenadorController.php`**:
```php
public function index() {
    $this->api = new ApiClient();
    $token     = $_SESSION['jwt'] ?? null;
    $resp      = $this->api->get('/rutinas', $token);
    $rutinas   = $resp['ok'] ? $resp['data'] : [];
    require $this->viewsDir . 'panelEntrenador.view.php';
}

public function crearRutina() {
    $this->api = new ApiClient();
    $this->api->post('/rutinas', [
        'titulo'      => $_POST['titulo'],
        'descripcion' => $_POST['descripcion'] ?? '',
        'objetivo'    => $_POST['objetivo']    ?? '',
    ], $_SESSION['jwt']);
    header('Location: /panel-entrenador'); exit;
}

public function agregarDia() {
    $this->api = new ApiClient();
    $rutinaId  = $_POST['rutina_id'];
    $this->api->post("/rutinas/{$rutinaId}/dias", [
        'nombre_dia'       => $_POST['nombre_dia'],
        'grupo_muscular'   => $_POST['grupo_muscular']   ?? '',
        'duracion_minutos' => $_POST['duracion_minutos'] ?? null,
        'orden'            => $_POST['orden']            ?? 1,
    ], $_SESSION['jwt']);
    header('Location: /panel-entrenador'); exit;
}

public function agregarEjercicio() {
    $this->api = new ApiClient();
    $rutinaId  = $_POST['rutina_id'];
    $diaId     = $_POST['dia_id'];
    $this->api->post("/rutinas/{$rutinaId}/dias/{$diaId}/ejercicios", [
        'api_ejercicio_id'    => $_POST['api_ejercicio_id'],
        'series_repeticiones' => $_POST['series_repeticiones'] ?? '',
        'orden'               => $_POST['orden'] ?? 1,
    ], $_SESSION['jwt']);
    header('Location: /panel-entrenador'); exit;
}
```

---

### Feature 8 — Registro de gimnasio

**`Router.php`**:
```php
$this->register('GET@/crearcuenta-gym',  'CrearcuentaGymController@crear');
$this->register('POST@/crearcuenta-gym', 'CrearcuentaGymController@registrar'); // nuevo
```

**`CrearcuentaGymController.php`** — agregá el método `registrar()`:
```php
public function registrar() {
    $this->api = new ApiClient();
    $response  = $this->api->post('/gimnasios/registrar', [
        'nombre'      => $_POST['nombre'],
        'direccion'   => $_POST['direccion']   ?? '',
        'horarios'    => $_POST['horarios']    ?? '',
        'telefono'    => $_POST['telefono']    ?? '',
        'email'       => $_POST['email']       ?? '',
        'descripcion' => $_POST['descripcion'] ?? '',
        'servicios'   => $_POST['servicios']   ?? '',
    ]); // sin token — endpoint público

    if ($response['ok']) {
        header('Location: /inicio-sesion');
        exit;
    }

    $error = $response['data']['error'] ?? 'Error al registrar el gimnasio';
    require $this->viewsDir . 'crearcuenta-gym.view.php';
}
```

El formulario `crearcuenta-gym.view.php` solo necesita `method="POST" action="/crearcuenta-gym"` con los campos correctos.

---

### Tabla resumen rápida

| Feature | Controller PHP | Método `ApiClient` | Endpoint backend |
|---------|---------------|-------------------|-----------------|
| 1. Rutinas listado | `RutinasController::listar` | `get()` | `GET /rutinas` |
| 1. Rutina detalle | `RutinaIndController::mostrar` | `get()` | `GET /rutinas/{id}` |
| 2. Ejercicios catálogo | `EjercicioController::listar` | `get()` | `GET /ejercicios?limit=20&offset=0` |
| 2. Ejercicio detalle | `EjercicioController::mostrar` | `get()` | `GET /ejercicios/{id}` |
| 3. Ver perfil | `PerfilUserController::mostrar` | `get()` | `GET /usuarios/{id}` ✅ ya funciona |
| 3. Editar perfil | `PerfilUserController::actualizar` | `put()` | `PUT /usuarios/{id}` |
| 4. Listar entrenadores | `EntrenadoresController::listar` | `get()` | `GET /entrenadores` |
| 4. Ver perfil entrenador | `PerfilEntrenadorController::mostrar` | `get()` | `GET /entrenadores/{id}` |
| 4. Editar perfil entrenador | `PerfilEntrenadorController::actualizar` | `put()` | `PUT /entrenadores/{id}` |
| 5. Ver planes | `PagosuscripcionController::mostrar` | `get()` | `GET /suscripciones/planes` |
| 5. Ver suscripción activa | `PagosuscripcionController::mostrar` | `get()` | `GET /suscripciones/mi-suscripcion` |
| 5. Suscribirse | `PagosuscripcionController::suscribirse` | `post()` | `POST /suscripciones` |
| 6. Asignar rutina | `PanelEntrenadorController::asignarRutina` | `post()` | `POST /rutinas/{id}/asignar` |
| 7. Panel entrenador | `PanelEntrenadorController` (varios) | `post()` | `POST /rutinas`, `/dias`, `/ejercicios` |
| 8. Registro gimnasio | `CrearcuentaGymController::registrar` | `post()` | `POST /gimnasios/registrar` |

**Orden recomendado de implementación**: 8 → 3 → 1 → 2 → 4 → 5 → 6 → 7. Empezás por lo más simple (formularios sin roles) y terminás por lo más complejo (panel del entrenador).

---

## Estructura del proyecto

```
backend/
├── src/
│   └── server.js                    # Entry point de Express — registra todas las rutas
├── routes/
│   ├── authRoutes.js                # /auth
│   ├── gimnasioRoutes.js            # /gimnasios
│   ├── usuarioRoutes.js             # /usuarios
│   ├── rutinaRoutes.js              # /rutinas
│   ├── ejercicioRoutes.js           # /ejercicios
│   ├── entrenadorRoutes.js          # /entrenadores
│   └── suscripcionRoutes.js         # /suscripciones
├── controllers/
│   ├── authController.js
│   ├── gimnasioController.js
│   ├── usuarioController.js
│   ├── rutinaController.js
│   ├── ejercicioController.js
│   ├── entrenadorController.js
│   └── suscripcionController.js
├── services/
│   ├── authService.js               # Lógica de registro y login
│   ├── gimnasioService.js
│   ├── usuarioService.js
│   ├── rutinaService.js             # Lógica de rutinas, días y asignaciones
│   ├── ejercicioService.js          # Proxy a wger.de API externa
│   ├── entrenadorService.js
│   └── suscripcionService.js        # Planes hardcodeados + lógica de cancelación
├── repositories/
│   ├── gimnasioRepository.js
│   ├── usuarioRepository.js
│   ├── rutinaRepository.js          # SQL de rutinas, días y pivot exercises
│   ├── ejercicioRepository.js       # Cache local de ejercicios (tabla ejercicios)
│   ├── entrenadorRepository.js      # JOIN usuarios + entrenadores + gimnasios
│   └── suscripcionRepository.js
├── middleware/
│   └── authMiddleware.js            # authenticateToken + checkRole
├── database/
│   ├── connection.js                # Pool de conexiones MySQL (mysql2/promise)
│   └── migrations/
│       └── create_tables.sql        # Crea las 6 tablas nuevas + ALTER TABLE gimnasios
├── docker-compose.yml
├── Dockerfile
└── backup_progresofit.sql           # Dump de tablas base (usuarios + gimnasios)
```

---

## Persistencia de datos

Los datos de MySQL se guardan en un volumen de Docker (`mysql_data`).

- `docker compose stop` → los datos se conservan.
- `docker compose down` → los contenedores se borran pero los datos se conservan.
- `docker compose down -v` → **borra también los datos** (cuidado — hay que volver a correr el backup y la migración).
