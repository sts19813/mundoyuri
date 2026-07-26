<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class AssistantSetting extends Model
{
    protected $fillable = [
        'enabled',
        'initial_state',
        'remember_user_state',
        'initial_delay_seconds',
        'message_interval_seconds',
        'bubble_duration_seconds',
        'peek_duration_seconds',
        'messages',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'remember_user_state' => 'boolean',
            'initial_delay_seconds' => 'integer',
            'message_interval_seconds' => 'integer',
            'bubble_duration_seconds' => 'integer',
            'peek_duration_seconds' => 'integer',
            'messages' => 'array',
        ];
    }

    public static function current(): self
    {
        if (! Schema::hasTable('assistant_settings')) {
            return (new static(static::defaults()))->forceFill(['id' => 1]);
        }

        return static::query()->firstOrNew([
            'id' => 1,
        ], static::defaults());
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'enabled' => true,
            'initial_state' => 'minimized',
            'remember_user_state' => true,
            'initial_delay_seconds' => 20,
            'message_interval_seconds' => 20,
            'bubble_duration_seconds' => 7,
            'peek_duration_seconds' => 7,
            'messages' => static::defaultMessages(),
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function defaultMessages(): array
    {
        return [
            [
                'text' => '¡Hola! Soy tu asistente Miyu.',
                'peek' => '¡Hola! Soy tu asistente Miyu.',
                'audience' => 'all',
                'action_type' => 'none',
                'label' => '',
                'url' => '',
            ],
            [
                'text' => '¿Encontraste un problema en la página? Ayúdanos a mejorar.',
                'peek' => '¿Encontraste un problema en la página? Ayúdanos a mejorar.',
                'audience' => 'all',
                'action_type' => 'report',
                'label' => 'Reportar un problema',
                'url' => '',
            ],
            [
                'text' => '¿Hay alguna serie o película que te encantaría ver aquí? Coméntanos para subirla.',
                'peek' => '¿Hay alguna serie o película que te gustaría ver aquí?',
                'audience' => 'all',
                'action_type' => 'request',
                'label' => 'Coméntanos cuál',
                'url' => '',
            ],
            [
                'text' => 'Crea una cuenta gratis para preparar tu lista de favoritas y descubrir las próximas novedades.',
                'peek' => 'Crea una cuenta gratis para guardar tus favoritas y descubrir novedades.',
                'audience' => 'guest',
                'action_type' => 'register',
                'label' => 'Crear cuenta gratis',
                'url' => '',
            ],
            [
                'text' => '¿Quieres mandarnos un mensaje? Puedes hacerlo sin salir de esta página.',
                'peek' => '¿Quieres mandarnos un mensaje?',
                'audience' => 'all',
                'action_type' => 'message',
                'label' => 'Escribir mensaje',
                'url' => '',
            ],
            [
                'text' => '¿No sabes qué ver? Explora nuestras series y películas disponibles.',
                'peek' => '¿Buscas algo nuevo para ver?',
                'audience' => 'all',
                'action_type' => 'catalog',
                'label' => 'Explorar el catálogo',
                'url' => '',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function messagesFor(?User $user): array
    {
        return collect($this->messages ?: static::defaultMessages())
            ->filter(function (array $message) use ($user): bool {
                return match ($message['audience'] ?? 'all') {
                    'guest' => $user === null,
                    'authenticated' => $user !== null,
                    default => true,
                };
            })
            ->map(function (array $message): array {
                $actionType = $message['action_type'] ?? 'none';
                $clientMessage = [
                    'text' => $message['text'],
                    'peek' => filled($message['peek'] ?? null) ? $message['peek'] : $message['text'],
                ];

                if (in_array($actionType, ['report', 'request', 'message'], true)) {
                    $clientMessage['label'] = $message['label'] ?: $this->defaultLabel($actionType);
                    $clientMessage['formType'] = $actionType;
                } elseif ($actionType !== 'none') {
                    $clientMessage['label'] = $message['label'] ?: $this->defaultLabel($actionType);
                    $clientMessage['url'] = match ($actionType) {
                        'register' => route('register'),
                        'catalog' => route('catalog.series.index'),
                        'external' => $message['url'] ?? '',
                        default => '',
                    };
                }

                return $clientMessage;
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, int|string|bool>
     */
    public function clientConfig(): array
    {
        return [
            'initialState' => $this->initial_state,
            'rememberUserState' => $this->remember_user_state,
            'initialDelayMs' => $this->initial_delay_seconds * 1000,
            'messageIntervalMs' => $this->message_interval_seconds * 1000,
            'bubbleDurationMs' => $this->bubble_duration_seconds * 1000,
            'peekDurationMs' => $this->peek_duration_seconds * 1000,
        ];
    }

    private function defaultLabel(string $actionType): string
    {
        return match ($actionType) {
            'report' => 'Reportar un problema',
            'request' => 'Coméntanos cuál',
            'message' => 'Escribir mensaje',
            'register' => 'Crear cuenta gratis',
            'catalog' => 'Explorar el catálogo',
            'external' => 'Más información',
            default => '',
        };
    }
}
