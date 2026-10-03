# IAm-fit — Backend

API para la app IAm-fit (Android). Laravel 12 + PostgreSQL 17, todo en Docker.

## Levantar

```bash
cp .env.example .env          # y pega GROQ_API_KEY (ver abajo)
docker compose build
docker compose up -d
./iamfit artisan key:generate
./iamfit artisan migrate --seed
```

- API:      http://localhost:8001
- Adminer:  http://localhost:8084  (server `pgsql`, user/pass `iamfit` / `secret`)

El contenedor corre nginx + php-fpm + `queue:listen` (supervisor). Para tests:

```bash
docker compose exec pgsql psql -U iamfit -d iamfit -c "CREATE DATABASE iamfit_test OWNER iamfit;"
./iamfit artisan test
```

### Wrapper `./iamfit`

`./iamfit artisan …`, `./iamfit composer …`, `./iamfit tinker`, `./iamfit test`,
`./iamfit pint app`, `./iamfit psql`, `./iamfit shell`.

## Panel de administración (Filament)

- URL: **http://localhost:8001/** (login con sesión web, separado de los
  tokens Sanctum de la app Android).
- El acceso se controla con `users.is_admin` y la interfaz `FilamentUser` del
  modelo `User`. Un usuario sin `is_admin` recibe `403`.
- Para dar o quitar acceso:

  ```bash
  ./iamfit artisan iamfit:make-admin correo@ejemplo.com
  ./iamfit artisan iamfit:make-admin correo@ejemplo.com --revoke
  ```

  En local, el usuario demo (`demo@iamfit.local` / `password`) se siembra como
  administrador.

Recursos disponibles:

| Recurso | Qué permite |
| --- | --- |
| Alimentos | Curar el catálogo, crear/editar y **verificar** los estimados por IA (badge con pendientes) |
| Porciones | Gestionar las porciones legibles de cada alimento (relation manager) |
| Búsquedas IA | Log de enriquecimientos (`ai_food_lookups`), solo lectura |
| Ejercicios / Músculos | Catálogo de entrenamiento |
| Usuarios | `is_admin`, datos y contraseña (no puedes borrarte a ti mismo) |

Estilo: Filament usa **Tailwind CSS v4** internamente, así que no hay un cambio de
framework. Se personaliza con `->colors()`, `->brandName()` y demás opciones del
panel en `app/Providers/Filament/AdminPanelProvider.php`; para reglas de Tailwind
propias se crea un *custom theme* de Filament. Ahora mismo no hay theme a medida.

## IA

`App\Contracts\AiChatProvider` → `App\Services\Ai\GroqChatProvider`
(Groq, `openai/gpt-oss-20b`). El bind vive en `AppServiceProvider`. Cambiar de
proveedor = una clase nueva + cambiar el bind.

```
GROQ_API_KEY=          # https://console.groq.com/keys
GROQ_MODEL=openai/gpt-oss-20b
USDA_FDC_API_KEY=      # opcional, https://fdc.nal.usda.gov/api-key-signup.html
```

Sin `GROQ_API_KEY` el backend arranca igual; sólo se desactivan las features de IA.

## Dominio

### Nutrición
- **BMR / TDEE / objetivo calórico**: `EnergyCalculator` (Mifflin-St Jeor). Cálculo
  determinista, **sin IA**.
- **Alimentos**: `Food` guarda macros **por 100 g**; `FoodPortion` las porciones
  legibles. `FoodLogEntry` guarda un *snapshot* de kcal/macros al registrar.
- **Enriquecimiento**: si un alimento no está en la BD, `FoodResolver` encola
  `ResolveFoodLookup`, que prueba fuentes en orden (`config/nutrition.php`):
  1. Open Food Facts (buscador search-a-licious)
  2. USDA FoodData Central (si hay key)
  3. IA (`AiNutritionSource`) — estimación, se guarda `verified_at = null`
  El `AiFoodLookup` registra cada intento.

### Entrenamiento
- Catálogo `Muscle` + `Exercise` (primario/secundarios, equipo, mecánica).
- `Routine` → `RoutineDay` → `RoutineExercise` (la arma el usuario, sin IA).
- `WorkoutSession` → `SetLog` (series reales; alimenta el consejo de IA).
- `TrainingAdviceService`: la IA comenta cargas de arranque, progresión y volumen
  a partir del perfil + historial reciente. No arma la rutina.

## Endpoints (`/api`)

| Método | Ruta | |
|---|---|---|
| GET | `/health` | `{"status":"ok"}` (sin auth, sin envelope) |
| POST | `/register`, `/login` | `{data:{user, token}}` (Sanctum) |
| POST | `/forgot-password`, `/reset-password` | recuperación de contraseña |
| POST | `/logout` · GET `/me` | |
| GET/PUT | `/profile` | perfil (sexo, nacimiento, estatura, actividad, objetivo, `timezone`) |
| GET/POST | `/weight` · DELETE `/weight/{id}` | historial de peso (`?days=` para filtrar) |
| GET | `/energy` | BMR, TDEE, `target_kcal`, macros |
| GET | `/progress/streak?date=&timezone=` | racha de 7 días (comida o entreno) |
| GET | `/foods/search?q=` | `found` (200) o `pending` (202 + `lookup_id`) |
| GET | `/foods/lookups/{id}` | estado del enriquecimiento |
| GET | `/foods/{id}` | |
| GET/POST | `/diary?date=&timezone=` · DELETE `/diary/{id}` | diario + totales + objetivo |
| GET | `/muscles` · `/exercises?muscle=&group=&equipment=&q=` | catálogo |
| — | `/routines` (apiResource) | CRUD, árbol anidado `days[].exercises[]` |
| POST | `/routines/{id}/advice` | consejo de carga de la IA (`throttle:ai`) |
| POST | `/diagnostics/ai` | ping al proveedor de IA (`throttle:ai`) |

### Contrato de respuesta

- **Éxito**: envelope `{ "data": ... }` en todos los endpoints de datos. La única
  excepción es `GET /health`, que responde plano para monitoreo.
- **Error**: `{ "message": string, "errors"?: { campo: [mensajes] } }`. Los errores
  `422` de validación siempre traen `errors`; `401`, `403`, `404` y `429` traen solo
  `message` en español.
- **Día local**: los endpoints con fecha (`/diary`, `/weight`,
  `/progress/streak`) aceptan `?timezone=America/Mexico_City`. Si no se envía, se usa
  la `timezone` del perfil y, en su defecto, `APP_USER_TIMEZONE` (default `UTC`).
  Los instantes se guardan en `timestamptz` (UTC).

Rate limits: `throttle:auth` (10/min por IP), `throttle:ai` (6/min global — Groq
limita por organización).
