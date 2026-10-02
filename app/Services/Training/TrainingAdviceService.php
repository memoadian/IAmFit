<?php

namespace App\Services\Training;

use App\Contracts\AiChatProvider;
use App\Exceptions\AiException;
use App\Models\AiTrainingAdvice;
use App\Models\Routine;
use App\Models\SetLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Consejo de carga para una rutina. La IA NO arma la rutina (eso lo hace el
 * usuario por músculo); sólo comenta, a partir del perfil del usuario y de su
 * historial reciente de series, qué cargas y progresión son razonables y dónde
 * hay riesgo de sobre-entrenar.
 */
class TrainingAdviceService
{
    public function __construct(private readonly AiChatProvider $ai) {}

    public function forRoutine(User $user, Routine $routine): AiTrainingAdvice
    {
        $context = $this->buildContext($user, $routine);

        try {
            $result = $this->ai->complete(
                $this->systemPrompt(),
                json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            );
        } catch (AiException $e) {
            Log::warning('training_advice.failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            throw $e;
        }

        return AiTrainingAdvice::create([
            'user_id' => $user->id,
            'routine_id' => $routine->id,
            'context' => $context,
            'advice' => trim($result->content),
            'provider' => $this->ai->name(),
            'prompt_tokens' => $result->promptTokens,
            'completion_tokens' => $result->completionTokens,
        ]);
    }

    /** @return array<string, mixed> */
    private function buildContext(User $user, Routine $routine): array
    {
        $profile = $user->profile;
        $weightKg = $user->latestWeightKg();

        $routine->load('days.exercises.exercise.primaryMuscle');

        return [
            'perfil' => $profile ? [
                'sexo' => $profile->sex->value,
                'edad' => $profile->ageYears(),
                'estatura_cm' => $profile->height_cm,
                'peso_kg' => $weightKg,
                'nivel_actividad' => $profile->activity_level->value,
                'objetivo' => $profile->goal->value,
            ] : null,
            'rutina' => [
                'nombre' => $routine->name,
                'dias_por_semana' => $routine->days_per_week,
                'dias' => $routine->days->map(fn ($day) => [
                    'etiqueta' => $day->label,
                    'ejercicios' => $day->exercises->map(fn ($re) => [
                        'ejercicio' => $re->exercise->name,
                        'musculo' => $re->exercise->primaryMuscle->name ?? null,
                        'series' => $re->target_sets,
                        'reps' => "{$re->target_reps_min}-{$re->target_reps_max}",
                        'rpe_objetivo' => $re->target_rpe,
                    ])->all(),
                ])->all(),
            ],
            'historial_reciente' => $this->recentPerformance($user),
        ];
    }

    /**
     * Mejores series por ejercicio en las últimas 8 semanas (para estimar cargas).
     *
     * @return array<int, array<string, mixed>>
     */
    private function recentPerformance(User $user): array
    {
        return SetLog::query()
            ->whereHas('session', fn ($q) => $q->where('user_id', $user->id)
                ->where('performed_at', '>=', now()->subWeeks(8)))
            ->where('is_warmup', false)
            ->with('exercise:id,name')
            ->get()
            ->groupBy('exercise_id')
            ->map(function ($sets) {
                $top = $sets->sortByDesc('weight_kg')->first();

                return [
                    'ejercicio' => $top->exercise->name ?? "#{$top->exercise_id}",
                    'mejor_serie' => "{$top->weight_kg} kg x {$top->reps}",
                    'rpe' => $top->rpe,
                    '1rm_estimado_kg' => $top->estimatedOneRepMax(),
                    'series_registradas' => $sets->count(),
                ];
            })
            ->values()
            ->all();
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
        Eres un entrenador de fuerza. Recibes un JSON con el perfil del usuario,
        su rutina (armada por él) y su historial reciente de series.

        Devuelve un consejo BREVE en español (máx. 200 palabras), en prosa, que cubra:
        - Cargas de arranque sugeridas para los ejercicios principales (usa el
          historial y el 1RM estimado; si no hay historial, da rangos por peso corporal).
        - Progresión semanal realista (ej. +2.5 kg en compuestos si se cumplió el RPE).
        - Señales de sobre-entrenamiento o volumen excesivo en la rutina, si las hay.

        No inventes lesiones ni des diagnósticos médicos. No repitas el JSON.
        PROMPT;
    }
}
