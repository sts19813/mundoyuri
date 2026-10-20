<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $descriptions = [
            'primeros-pasos' => 'Envió su primer mensaje de chat o comentario en un capítulo.',
            'primera-conversacion' => 'Inició una conversación por chat.',
            'autora-de-temas' => 'Dejó su primer comentario en un capítulo.',
            'tema-en-llamas' => 'Un comentario suyo en un capítulo encendió la conversación.',
            'centenar' => 'Alcanzó 100 mensajes de chat y comentarios de capítulos.',
            'conversadora' => 'Alcanzó 250 mensajes de chat y comentarios de capítulos.',
            'cronista' => 'Alcanzó 500 mensajes de chat y comentarios de capítulos.',
            'mil-historias' => 'Alcanzó 1,000 mensajes de chat y comentarios de capítulos.',
            'curiosa' => 'Dejó su primer comentario en un capítulo.',
            'tengo-la-respuesta' => 'Recibió su primera respuesta directa en el chat.',
            'yuri-scholar' => 'Sus mensajes iniciaron varias respuestas directas.',
            'sensei' => 'Sus mensajes iniciaron muchas respuestas directas.',
        ];

        foreach ($descriptions as $slug => $description) {
            DB::table('badges')->where('slug', $slug)->update(['description' => $description, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // Copy is intentionally not reverted: it replaced obsolete forum-only wording.
    }
};
