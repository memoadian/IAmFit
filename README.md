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

## IA

Portada de `inmuebles`: `App\Contracts\AiChatProvider` → `App\Services\Ai\GroqChatProvider`
(Groq, `openai/gpt-oss-20b`). El bind vive en `AppServiceProvider`. Cambiar de
proveedor = una clase nueva + cambiar el bind.

```
GROQ_API_KEY=          # https://console.groq.com/keys (o reusa la de inmuebles)
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
| POST | `/register`, `/login` | devuelven `{ user, token }` (Sanctum) |
| POST | `/logout` · GET `/me` | |
| GET/PUT | `/profile` | perfil (sexo, nacimiento, estatura, actividad, objetivo) |
| GET/POST | `/weight` · DELETE `/weight/{id}` | historial de peso |
| GET | `/energy` | BMR, TDEE, `target_kcal`, macros |
| GET | `/foods/search?q=` | `found` (200) o `pending` (202 + `lookup_id`) |
| GET | `/foods/lookups/{id}` | estado del enriquecimiento |
| GET | `/foods/{id}` | |
| GET/POST | `/diary?date=` · DELETE `/diary/{id}` | diario + totales + objetivo |
| GET | `/muscles` · `/exercises?muscle=&group=&equipment=&q=` | catálogo |
| — | `/routines` (apiResource) | CRUD, árbol anidado `days[].exercises[]` |
| POST | `/routines/{id}/advice` | consejo de carga de la IA (`throttle:ai`) |

Rate limits: `throttle:auth` (10/min por IP), `throttle:ai` (6/min global — Groq
limita por organización).
