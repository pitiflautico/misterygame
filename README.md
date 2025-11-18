# Sistema Narrativo Universal V4.0

## 🎭 Plataforma Universal para Juegos Narrativos Interactivos

Una plataforma completa para crear, gestionar y jugar experiencias narrativas interactivas con inteligencia artificial, que incluye:

- **Engine Universal** de juegos narrativos
- **Game Creator con IA** (OpenAI/Claude)
- **Sistema de Administración** completo
- **Módulos Multimedia** (audio, video, imágenes, PDFs, fake webs)
- **Generador Automático** de landing pages
- **Sistema de Monetización** y licencias
- **Gestión de Sesiones** en tiempo real

---

## 📋 Tabla de Contenidos

- [Características](#características)
- [Tipos de Juegos](#tipos-de-juegos)
- [Arquitectura](#arquitectura)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [API Endpoints](#api-endpoints)
- [Servicios](#servicios)
- [Base de Datos](#base-de-datos)
- [Seguridad](#seguridad)
- [Desarrollo](#desarrollo)

---

## ✨ Características

### Game Engine
- ✅ Sistema de escenas dinámicas
- ✅ Gestión de estados y flags
- ✅ Narrativa ramificada con condicionales
- ✅ Roles secretos para jugadores
- ✅ Sistema de pistas y descubrimientos
- ✅ Inventario y notas por jugador
- ✅ Temporizadores y eventos cronometrados

### Módulos Multimedia
- 🎵 **Audio**: TTS con efectos (susurro, eco, reverb)
- 🖼️ **Imágenes**: Generación con DALL-E/MidJourney
- 🎬 **Video**: Videos cortos atmosféricos
- 📄 **PDF**: Documentos, cartas, informes forenses
- 🌐 **Fake Web**: Sitios web ficticios completos
- 📰 **Fake News**: Noticias falsas realistas
- 🔔 **Push Notifications**: Notificaciones contextuales

### Game Creator con IA
- 🤖 Generación completa de juegos con prompts
- 🔄 Regeneración selectiva de partes
- ✅ Validación automática de coherencia
- 🎨 Creación de landing pages automáticas
- 📊 Análisis y reparación de inconsistencias

### Monetización
- 💳 Compra individual de juegos
- 🎫 Tickets de host (grupo completo)
- 📅 Suscripciones mensuales/anuales
- 📦 Packs de juegos
- 💰 Integración con Stripe/Apple/Google Pay

---

## 🎮 Tipos de Juegos

| Tipo | Descripción |
|------|-------------|
| **Murder Mystery** | Investigaciones de asesinatos con múltiples sospechosos |
| **Mystery** | Misterios generales y enigmas |
| **Liminal** | Experiencias en espacios liminales |
| **Investigation** | Investigaciones detalladas |
| **Horror** | Terror psicológico suave |
| **Escape** | Escape rooms narrativos |
| **Interactive Movie** | Películas interactivas |
| **Guided Adventure** | Aventuras guiadas |

---

## 🏗️ Arquitectura

### Backend
```
Laravel 10
├── Game Engine (Escenas, Estados, Flags)
├── AI Services (OpenAI/Claude)
├── Multimedia Services (TTS, Image Gen, PDF)
├── Session Management
├── License System
└── API REST
```

### Base de Datos
```
MySQL/PostgreSQL
├── users
├── games
├── game_versions
├── game_assets
├── sessions
├── session_players
├── session_events
├── licenses
├── purchase_logs
└── admin_logs
```

### Cache y Colas
```
Redis
├── Session State (cache)
├── Job Queue (multimedia generation)
└── Real-time data
```

### Storage
```
AWS S3
├── Audio files
├── Images
├── Videos
└── PDFs
```

---

## 🚀 Instalación

### Requisitos
- PHP 8.1+
- Composer
- MySQL 8.0+ o PostgreSQL 13+
- Redis
- Node.js 18+

### Pasos

1. **Clonar el repositorio**
```bash
git clone https://github.com/tu-usuario/narrative-game-platform.git
cd narrative-game-platform
```

2. **Instalar dependencias**
```bash
composer install
npm install
```

3. **Configurar entorno**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurar base de datos**
Editar `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=narrative_games
DB_USERNAME=root
DB_PASSWORD=
```

5. **Ejecutar migraciones**
```bash
php artisan migrate
```

6. **Configurar servicios externos**
Añadir en `.env`:
```env
# OpenAI
OPENAI_API_KEY=sk-...

# Claude (Anthropic)
ANTHROPIC_API_KEY=sk-ant-...

# AWS S3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_BUCKET=...

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

7. **Iniciar servidor**
```bash
php artisan serve
```

---

## ⚙️ Configuración

### Servicios de IA

**OpenAI (GPT-4)**
```env
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4-turbo-preview
```

**Anthropic (Claude)**
```env
ANTHROPIC_API_KEY=sk-ant-...
ANTHROPIC_MODEL=claude-3-sonnet-20240229
```

### TTS (Text-to-Speech)
```env
TTS_PROVIDER=openai
TTS_VOICE=alloy
```

### Generación de Imágenes
```env
IMAGE_PROVIDER=openai
IMAGE_MODEL=dall-e-3
```

### Seguridad
```env
SECURITY_ENABLE_ACTION_VALIDATION=true
SECURITY_ENABLE_CONTENT_MODERATION=true
SECURITY_BLOCK_REAL_AUTHORITIES=true
```

---

## 📡 API Endpoints

### Autenticación
```http
POST /api/register
POST /api/login
POST /api/logout
GET  /api/user
```

### Juegos
```http
GET    /api/games                    # Listar catálogo
GET    /api/games/featured           # Juegos destacados
GET    /api/games/popular            # Juegos populares
GET    /api/games/{id}               # Detalle de juego
GET    /api/games/{id}/landing       # Landing page
GET    /api/games/recommended        # Recomendados (auth)
```

### Sesiones
```http
POST   /api/session/create           # Crear sesión
POST   /api/session/join             # Unirse con código
GET    /api/session/my-sessions      # Mis sesiones activas
POST   /api/session/{id}/start       # Iniciar juego
GET    /api/session/{id}/state       # Estado actual
POST   /api/session/{id}/action      # Realizar acción
POST   /api/session/{id}/abandon     # Abandonar sesión
```

### Admin
```http
GET    /api/admin/games                      # Listar todos los juegos
POST   /api/admin/games/create-with-ai       # Crear juego con IA
PUT    /api/admin/games/{id}                 # Actualizar juego
POST   /api/admin/games/{id}/publish         # Publicar juego
POST   /api/admin/games/{id}/regenerate      # Regenerar parte
GET    /api/admin/games/{id}/statistics      # Estadísticas
GET    /api/admin/sessions                   # Sesiones activas
GET    /api/admin/users                      # Usuarios
GET    /api/admin/statistics                 # Stats globales
```

---

## 🛠️ Servicios

### GameEngine
Gestiona la ejecución del juego:
```php
use App\Services\GameEngine\GameEngine;

$engine = new GameEngine($session);
$engine->initialize();
$scene = $engine->getCurrentScene();
$result = $engine->processAction($player, $actionId);
```

### GameCreatorService
Genera juegos con IA:
```php
use App\Services\AI\GameCreatorService;

$creator = new GameCreatorService();
$game = $creator->generateGame(
    "Crea un misterio de asesinato en una mansión victoriana",
    ['game_type' => 'murder', 'players' => ['min' => 4, 'max' => 8]]
);
```

### MultimediaService
Procesa recursos multimedia:
```php
use App\Services\Multimedia\MultimediaService;

$service = new MultimediaService();
$service->processGameAssets($game, $modules);
```

### SessionService
Gestiona sesiones de juego:
```php
use App\Services\Session\SessionService;

$sessionService = new SessionService();
$session = $sessionService->createSession($user, $game);
$sessionService->startSession($session, $user);
```

### LicenseService
Gestiona licencias y acceso:
```php
use App\Services\License\LicenseService;

$licenseService = new LicenseService();
$hasAccess = $licenseService->hasAccess($user, $game);
$license = $licenseService->purchaseGame($user, $game);
```

---

## 💾 Base de Datos

### Tablas Principales

**users**
- Usuarios de la plataforma
- Roles: user, admin, creator
- Premium status

**games**
- Juegos publicados
- Metadata, precio, dificultad
- JSON con estructura completa

**game_assets**
- Recursos multimedia
- Audio, imágenes, videos, PDFs
- URLs de S3

**sessions**
- Sesiones de juego activas
- Estado actual, flags, pistas
- Resultados finales

**licenses**
- Licencias de acceso
- Tipos: individual, host_ticket, subscription
- Límites de uso

---

## 🔒 Seguridad

### Validaciones Implementadas

✅ **Contenido Seguro**
- Sin referencias a autoridades reales
- Sin acciones peligrosas
- Sin datos personales sensibles
- Todo contenido es claramente ficticio

✅ **Protección de Datos**
- Autenticación con Sanctum
- Middleware de admin
- Rate limiting
- Validación de entrada

✅ **Moderación**
- Keywords peligrosas bloqueadas
- Logs de contenido sospechoso
- Revisión manual opcional

---

## 👨‍💻 Desarrollo

### Estructura de Archivos
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   └── Admin/
│   └── Middleware/
├── Models/
├── Services/
│   ├── GameEngine/
│   ├── AI/
│   ├── Multimedia/
│   ├── Session/
│   └── License/
database/
├── migrations/
├── seeders/
└── factories/
config/
├── platform.php
├── security.php
└── services.php
```

### Testing
```bash
# Ejecutar tests
php artisan test

# Tests específicos
php artisan test --filter GameEngineTest
```

### Queue Workers
```bash
# Procesar multimedia
php artisan queue:work --queue=multimedia

# Procesar IA
php artisan queue:work --queue=ai-generation
```

---

## 📊 Ejemplo de Juego Generado

```json
{
  "title": "Asesinato en la Mansión Blackwood",
  "game_type": "murder",
  "scenes": [
    {
      "scene_id": "scene_01",
      "is_start": true,
      "type": "message",
      "content": "Una tormenta azota la mansión Blackwood...",
      "actions": [
        {
          "id": "investigate_library",
          "label": "Investigar la biblioteca",
          "next_scene": "scene_02",
          "flags": {"library_visited": true}
        }
      ]
    }
  ],
  "roles": [
    {
      "role_id": "detective",
      "name": "Detective",
      "secret_information": "Sabes que el mayordomo miente..."
    }
  ],
  "modules": [
    {
      "module": "audio",
      "id": "thunder",
      "tts_text": "Un trueno retumba en la distancia...",
      "effects": "reverb"
    }
  ]
}
```

---

## 🤝 Contribuir

1. Fork el proyecto
2. Crea una rama (`git checkout -b feature/AmazingFeature`)
3. Commit cambios (`git commit -m 'Add AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

---

## 📝 Licencia

MIT License - ver `LICENSE` para más detalles

---

## 🙏 Créditos

**Desarrollado con:**
- Laravel 10
- OpenAI GPT-4
- Anthropic Claude
- AWS S3
- Redis
- MySQL

**Inspirado en:**
- Murder Mystery Games
- Interactive Fiction
- Escape Rooms
- Narrative Adventures

---

## 📧 Contacto

Para soporte o consultas: [tu-email@example.com](mailto:tu-email@example.com)

**Documentation**: [https://docs.narrativegame.com](https://docs.narrativegame.com)

---

## 🗺️ Roadmap

### V4.1 (Q1 2025)
- [ ] Editor visual de escenas
- [ ] Sistema de achievements
- [ ] Replay de sesiones
- [ ] Modo espectador

### V4.2 (Q2 2025)
- [ ] Multiplayer en tiempo real
- [ ] Voice chat integrado
- [ ] Personalización de avatares
- [ ] Social features

### V5.0 (Q3 2025)
- [ ] VR Support
- [ ] AR Clues
- [ ] Procedural generation
- [ ] Community marketplace

---

**Built with ❤️ for storytellers and mystery lovers**
