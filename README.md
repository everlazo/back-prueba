# Laravel JWT API - Prueba Técnica Backend

## 🔥 **API Completa con JWT Authentication**

Esta es una aplicación backend desarrollada en Laravel 12 que implementa un sistema de autenticación con JWT y un CRUD completo de favoritos.

> ✨ **Proyecto optimizado y limpio - Solo lo esencial**

## 🚀 Características Implementadas

### ✅ Requerimientos Principales
- **Laravel 12** - Última versión
- **Autenticación completa** con JWT (JSON Web Tokens)
- **CRUD de favoritos** con validaciones
- **Base de datos MySQL** con migraciones y seeders
- **Service Layer** y **Repository Pattern**
- **Validaciones robustas** con Form Requests
- **Respuestas JSON estructuradas**

### 🎯 Funcionalidades Bonus
- ✅ **reCAPTCHA v3** - Validación en registro y login
- ✅ **Recuperación de contraseña** - Por correo electrónico
- ✅ **Control de sesión con JWT** - Tokens con expiración configurable
- ✅ **Middleware single.session (token_version)** para asegurar una sola sesión activa

## 📋 Endpoints Disponibles

### 🔐 Autenticación
| Método | Endpoint | Descripción | Autenticado |
|---------|----------|-------------|------------|
| POST | `/api/auth/register` | Registrar usuario | No |
| POST | `/api/auth/login` | Iniciar sesión | No |
| POST | `/api/auth/logout` | Cerrar sesión | Sí |
| POST | `/api/auth/forgot-password` | Solicitar recuperación | No |
| POST | `/api/auth/reset-password` | Resetear contraseña | No |
| GET | `/api/user` | Datos del usuario actual | Sí |
| POST | `/api/auth/refresh` | Refrescar token JWT | Sí |

### ⭐ Favoritos
| Método | Endpoint | Descripción | Autenticado |
|---------|----------|-------------|------------|
| GET | `/api/favorites` | Listar favoritos (paginado) | Sí |
| POST | `/api/favorites` | Crear favorito (solo external_id; name/image/description se obtienen de PokéAPI) | Sí |
| GET | `/api/favorites/{key}` | Ver favorito específico por id interno o por external_id (id/nombre de PokéAPI) | Sí |
| DELETE | `/api/favorites/{id}` | Eliminar favorito | Sí |

Nota: Los datos de favoritos son inmutables (provenientes de PokéAPI). La operación UPDATE no está permitida.

### 🏥 Utilidades
| Método | Endpoint | Descripción | Autenticado |
|---------|----------|-------------|------------|
| GET | `/api/health` | Health check de la API | No |

## 🛠️ Instalación y Configuración

### Prerrequisitos
- PHP 8.2+
- MySQL 5.7+
- Composer
- WAMP/XAMPP (o similar)

### Tecnologías Utilizadas
- **Laravel 12** - Framework PHP
- **JWT Auth (tymon/jwt-auth)** - Autenticación con JSON Web Tokens
- **MySQL** - Base de datos
- **Eloquent ORM** - Mapeo objeto-relacional

### Pasos de Instalación

1. **Clonar o acceder al proyecto**
   ```bash
   cd C:\\wamp64\\www\\back-prueba
   ```

2. **Instalar dependencias**
   ```bash
   composer install
   ```

3. **Configurar base de datos**
   - El archivo `.env` ya está configurado para MySQL
   - Base de datos: `back_prueba`
   - Usuario: `root` (sin contraseña)
   - Nota WAMP: valida el puerto de MySQL. En WAMP es común usar `3308`. Si aplica, ajusta `DB_PORT=3308` en `.env`.

4. **Ejecutar migraciones y seeders**
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Iniciar servidor**
   ```bash
   php artisan serve
   ```

La API estará disponible en: `http://127.0.0.1:8000`

## 📁 Estructura del Proyecto

```
app/
├── Http/
│   ├── Controllers/API/
│   │   ├── AuthController.php      # Autenticación
│   │   └── FavoriteController.php  # CRUD Favoritos
│   ├── Requests/
│   │   ├── Auth/                   # Validaciones Auth
│   │   └── Favorite/               # Validaciones Favoritos
│   └── Middleware/
│       ├── JWTMiddleware.php       # Autenticación JWT
│       └── EnsureSingleSession.php # Sesión única (token_version)
├── Models/
│   ├── User.php                    # Usuario con JWT (JWTSubject)
│   └── Favorite.php                # Favoritos
├── Services/
│   ├── AuthService.php             # Lógica de autenticación
│   ├── FavoriteService.php         # Lógica de favoritos
│   └── RecaptchaService.php        # Validación reCAPTCHA
├── Repositories/
│   └── FavoriteRepository.php      # Persistencia favoritos
└── Rules/
    └── RecaptchaRule.php           # Regla reCAPTCHA

database/
├── migrations/                     # Esquemas de BD
├── factories/
│   └── FavoriteFactory.php        # Factory para testing
└── seeders/
    └── FavoriteSeeder.php          # Datos de prueba
```

## 🧪 Testing con Postman

### Importar Colección JWT
1. Abrir Postman
2. Importar el archivo `postman_collection_jwt.json`
3. La colección JWT incluye:
   - **Authentication JWT**: Login, logout, refresh token
   - **Favorites CRUD (JWT Protected)**: Operaciones con autenticación JWT
   - **JWT Error Testing**: Pruebas de tokens inválidos/expirados
   - **Health Check**: Verificación de API

### Variables de Entorno JWT
La colección JWT maneja automáticamente:
- `base_url`: http://127.0.0.1:8000
- `jwt_token`: Se guarda automáticamente al hacer login
- `favorite_id`: Se guarda al crear favoritos
- **Expiración**: Los tokens JWT expiran en 1 hora (3600 segundos)

### Usuario de Prueba
```json
{
    "email": "everlazocastilo@gmail.com",
    "password": "password"
}
```

### Mini-flujo: Token revocado (una sola sesión activa)
1. Ejecuta "Login User (JWT)".
2. Ejecuta nuevamente "Login User (JWT)": se incrementa token_version y la colección guarda el token anterior en `old_jwt_token`.
3. Ejecuta "Revoked JWT Token (Old token after new login)": debe devolver 401 con `error: token_revoked`.

### Reset Password (manual)
1. Ejecuta "Forgot Password" con el email.
2. Busca el token de reseteo en `storage/logs/laravel.log`.
3. Ejecuta "Reset Password" con:
```json
{
  "token": "{{reset_token}}",
  "email": "everlazocastilo@gmail.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

## 🔧 Configuración Adicional

### reCAPTCHA (Opcional)
Para habilitar reCAPTCHA, agregar en `.env`:
```env
RECAPTCHA_SITE_KEY=tu_site_key
RECAPTCHA_SECRET_KEY=tu_secret_key
```

### Correo Electrónico
Para recuperación de contraseña, configurar en `.env` (ejemplo usando Gmail con App Password):
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=evernavitlazocastillo@gmail.com
MAIL_PASSWORD=<<APP_PASSWORD_GMAIL>>   # contraseña de aplicación (no tu password normal)
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=evernavitlazocastillo@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```
Notas:
- El remitente está configurado como `evernavitlazocastillo@gmail.com`.
- El usuario de pruebas para recibir correos es `everlazocastilo@gmail.com`.
- Para construir el enlace de reseteo en entornos solo-API, se usa `config('app.frontend_url')` (o `APP_URL` si no existe). Puedes definir `FRONTEND_URL` en `.env` si tienes un frontend diferente.

## 📊 Características Técnicas

### Validaciones Implementadas
- **Registro**: Nombre, email único, contraseña confirmada
- **Login**: Email y contraseña requeridos
- **Favoritos**: External ID único por usuario, imagen URL válida
- **reCAPTCHA**: Validación con score mínimo configurable

### Seguridad JWT
- **Tokens JWT** para autenticación stateless
- **Expiración configurable** (por defecto 1 hora)
- **Claims personalizados** incluidos en el token (incluye `tv` = token_version)
- **Refresh tokens** para renovar sesión
- **Middleware jwt.auth** para validar JWT
- **Middleware single.session** para forzar una sola sesión activa por usuario (invalidación de tokens previos en login)
- **Validaciones robustas** en todos los endpoints
- **Hash de contraseñas** con bcrypt

### Reglas de Favoritos (PokéAPI)
- Crear favorito: solo se envía `external_id` (id o nombre de PokéAPI); el backend enriquece `name`, `image`, `description`.
- Consultar favorito: por `id` interno o por `external_id` (id/nombre de PokéAPI).
- Actualizar favorito: NO permitido (datos inmutables provenientes de PokéAPI).

### Base de Datos (Limpia y Optimizada)
- **users**: Usuario de prueba (everlazocastilo@gmail.com)
- **favorites**: 8 favoritos Pokémon de ejemplo
- **password_reset_tokens**: Para recuperación de contraseña
- **migrations**: Control de versiones de BD
- **sessions**: Presente por defecto en el esqueleto (no requerida por JWT, pero mantenida)

## 🎯 Uso de la API

### Flujo de Autenticación
1. **Registro/Login** → Obtener token
2. **Usar token** en header: `Authorization: Bearer {token}`
3. **CRUD de favoritos** con token válido
4. **Logout** → Invalidar token

### Ejemplo de Request - Crear Favorito
```bash
POST /api/favorites
Authorization: Bearer 1|token_example
Content-Type: application/json

{
    "external_id": "pokemon_25",
    "name": "Pikachu",
    "image": "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/25.png",
    "description": "Pikachu es un Pokémon de tipo eléctrico muy querido."
}
```

### Respuesta Típica
```json
{
    "message": "Favorite created",
    "data": {
        "id": 1,
        "user_id": 1,
        "external_id": "pokemon_25",
        "name": "Pikachu",
        "image": "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/25.png",
        "description": "Pikachu es un Pokémon de tipo eléctrico muy querido.",
        "created_at": "2025-10-08T02:30:00.000000Z",
        "updated_at": "2025-10-08T02:30:00.000000Z"
    }
}
```

## 🐛 Manejo de Errores

### Códigos de Estado
- `200` - Éxito
- `201` - Recurso creado
- `401` - No autenticado
- `403` - No autorizado
- `404` - Recurso no encontrado
- `422` - Errores de validación
- `500` - Error interno del servidor

### Formato de Errores
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

## 📈 Logs y Debugging

Los logs se encuentran en `storage/logs/laravel.log` e incluyen:
- Errores de reCAPTCHA
- Intentos de autenticación fallidos
- Errores de validación
- Excepciones del sistema

---

**Desarrollado con ❤️ usando Laravel 12, JWT y buenas prácticas**
