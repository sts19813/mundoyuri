<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class CommunityBadgeCatalogSeeder extends Seeder
{
    /**
     * Synchronize only the definitions in the approved community badge catalog.
     * This never creates badge_user assignments.
     */
    public function run(): void
    {
        foreach ($this->definitions() as $definition) {
            $slug = $definition['slug'];
            unset($definition['slug']);

            Badge::query()->updateOrCreate(['slug' => $slug], $definition);
        }
    }

    /** @return list<array{name: string, slug: string, description: string, icon: string, type: string, priority: int, color: string, is_active: bool}> */
    private function definitions(): array
    {
        return [
            $this->badge('Miembro Histórico', 'miembro-historico', '🌸', 'legacy', 'Usuario recuperado del Mundo Yuri antiguo.', 100, '#f43f8e'),
            $this->badge('Pionera 2007', 'pionera-2007', '🕰️', 'legacy', 'Miembro registrado en 2007.', 95, '#c084fc'),
            $this->badge('Generación 2008', 'generacion-2008', '💿', 'legacy', 'Miembro registrado en 2008.', 90, '#60a5fa'),
            $this->badge('Fundadora', 'fundadora', '👑', 'special', 'Fundadora original de Mundo Yuri.', 100, '#f59e0b'),
            $this->badge('Leyenda de Mundo Yuri', 'leyenda-de-mundo-yuri', '🏛️', 'special', 'Reconocimiento excepcional y manual.', 95, '#d4a574'),
            $this->badge('Veterana', 'veterana', '🔥', 'special', 'Miembro con muchísimos años dentro de la comunidad.', 90, '#fb7185'),
            $this->badge('Alma de Mundo Yuri', 'alma-de-mundo-yuri', '🌙', 'special', 'Reconocimiento personal por impacto extraordinario.', 90, '#a78bfa'),
            $this->badge('Corazón de la Comunidad', 'corazon-de-la-comunidad', '💗', 'special', 'Persona muy querida o con un aporte comunitario especial.', 85, '#f472b6'),
            $this->badge('Staff', 'staff', '🛡️', 'staff', 'Miembro del equipo.', 90, '#38bdf8'),
            $this->badge('Moderación', 'moderacion', '⚔️', 'staff', 'Moderadora o moderador de la comunidad.', 90, '#34d399'),
            $this->badge('Administración', 'administracion', '🔑', 'staff', 'Administrador del portal.', 95, '#f59e0b'),
            $this->badge('Desarrollador', 'desarrollador', '💻', 'staff', 'Desarrollo de Mundo Yuri.', 85, '#60a5fa'),
            $this->badge('Diseño', 'diseno', '🎨', 'staff', 'Diseño gráfico o UI del sitio.', 85, '#f472b6'),
            $this->badge('Traductor', 'traductor', '✍️', 'contribution', 'Traducciones para la comunidad.', 70, '#c084fc'),
            $this->badge('Editor', 'editor', '📖', 'contribution', 'Correcciones o aportes editoriales.', 70, '#a78bfa'),
            $this->badge('Colaborador', 'colaborador', '📰', 'contribution', 'Colaboraciones recurrentes.', 65, '#38bdf8'),
            $this->badge('Cazador de recuerdos', 'cazador-de-recuerdos', '📸', 'contribution', 'Recuperó material histórico del antiguo Mundo Yuri.', 80, '#f59e0b'),
            $this->badge('Archivista', 'archivista', '🗃️', 'contribution', 'Aportó información o documentos históricos.', 75, '#d4a574'),
            $this->badge('Bug Hunter', 'bug-hunter', '🧩', 'development', 'Reportó errores importantes.', 70, '#fb7185'),
            $this->badge('Gran idea', 'gran-idea', '💡', 'contribution', 'Propuso una sugerencia que terminó implementándose.', 70, '#facc15'),
            $this->badge('Primeros pasos', 'primeros-pasos', '🌱', 'activity', 'Primera publicación en la comunidad.', 20, '#4ade80'),
            $this->badge('Primera conversación', 'primera-conversacion', '💬', 'activity', 'Primer mensaje o respuesta.', 20, '#60a5fa'),
            $this->badge('Autora de temas', 'autora-de-temas', '📝', 'forum', 'Creó su primer tema.', 30, '#c084fc'),
            $this->badge('Tema en llamas', 'tema-en-llamas', '🔥', 'forum', 'Creó un tema que alcanzó mucha actividad.', 50, '#fb7185'),
            $this->badge('Centenar', 'centenar', '💯', 'forum', 'Alcanzó 100 publicaciones modernas.', 40, '#60a5fa'),
            $this->badge('Conversadora', 'conversadora', '💬', 'forum', 'Alcanzó 250 publicaciones.', 50, '#38bdf8'),
            $this->badge('Cronista', 'cronista', '📚', 'forum', 'Alcanzó 500 publicaciones.', 60, '#a78bfa'),
            $this->badge('Mil historias', 'mil-historias', '🌌', 'forum', 'Alcanzó 1,000 publicaciones.', 70, '#6366f1'),
            $this->badge('Curiosa', 'curiosa', '❓', 'questions', 'Publicó su primera pregunta.', 30, '#f472b6'),
            $this->badge('Tengo la respuesta', 'tengo-la-respuesta', '💡', 'questions', 'Obtuvo su primera respuesta aceptada.', 40, '#facc15'),
            $this->badge('Yuri Scholar', 'yuri-scholar', '🧠', 'questions', 'Consiguió varias respuestas aceptadas.', 60, '#a78bfa'),
            $this->badge('Sensei', 'sensei', '🎓', 'questions', 'Acumuló muchas respuestas útiles o aceptadas.', 70, '#60a5fa'),
            $this->badge('Primer amor', 'primer-amor', '❤️', 'social', 'Marcó su primer favorito.', 20, '#f43f8e'),
            $this->badge('Coleccionista GL', 'coleccionista-gl', '💞', 'catalog', 'Agregó varias series a favoritos.', 45, '#ec4899'),
            $this->badge('Yuri Lover', 'yuri-lover', '🌸', 'catalog', 'Tiene alta interacción con obras yuri.', 55, '#f472b6'),
            $this->badge('Maratonista', 'maratonista', '🎬', 'catalog', 'Tiene mucha actividad relacionada con episodios o series.', 50, '#f97316'),
            $this->badge('Exploradora GL', 'exploradora-gl', '🔍', 'catalog', 'Descubrió o interactuó con muchas obras distintas.', 50, '#38bdf8'),
            $this->badge('Buen ojo', 'buen-ojo', '⭐', 'community', 'Su contenido publicado recibió varias reacciones.', 50, '#facc15'),
            $this->badge('Querida por la comunidad', 'querida-por-la-comunidad', '💖', 'social', 'Recibió muchas reacciones positivas.', 60, '#f472b6'),
            $this->badge('Conectada', 'conectada', '🤝', 'social', 'Sigue o es seguida por varios usuarios.', 35, '#34d399'),
            $this->badge('Mariposa social', 'mariposa-social', '🦋', 'social', 'Tiene alta interacción sana con distintos miembros.', 55, '#818cf8'),
            $this->badge('Primer aniversario', 'primer-aniversario', '🎂', 'seniority', 'Cumplió 1 año desde su registro moderno.', 35, '#f59e0b'),
            $this->badge('Tres primaveras', 'tres-primaveras', '🌸', 'seniority', 'Cumplió 3 años desde su registro moderno.', 45, '#f472b6'),
            $this->badge('Cinco primaveras', 'cinco-primaveras', '🌺', 'seniority', 'Cumplió 5 años desde su registro moderno.', 55, '#ec4899'),
            $this->badge('Una década juntas', 'una-decada-juntas', '🌳', 'seniority', 'Cumplió 10 años desde su registro moderno.', 70, '#34d399'),
            $this->badge('Halloween Yuri', 'halloween-yuri', '🎃', 'event', 'Participó en un evento Halloween.', 25, '#f97316'),
            $this->badge('Navidad Yuri', 'navidad-yuri', '🎄', 'event', 'Participó en un evento navideño.', 25, '#4ade80'),
            $this->badge('Mes del Orgullo', 'mes-del-orgullo', '🌈', 'event', 'Participó en una actividad del Pride.', 30, '#a78bfa'),
            $this->badge('Aniversario Mundo Yuri', 'aniversario-mundo-yuri', '🎉', 'event', 'Estuvo presente en el aniversario del portal.', 35, '#f472b6'),
            $this->badge('Nueva generación', 'nueva-generacion', '🚀', 'special', 'Formó parte del primer grupo importante del relanzamiento moderno.', 65, '#60a5fa'),
            $this->badge('Beta Tester', 'beta-tester', '🧪', 'special', 'Participó probando una función antes de su lanzamiento.', 60, '#34d399'),
            $this->badge('Yo estuve ahí', 'yo-estuve-ahi', '🐛', 'fun', 'Sobrevivió a un bug o evento memorable del sitio.', 30, '#f97316'),
            $this->badge('Fantasma', 'fantasma', '👻', 'fun', 'Usuario antiguo que vuelve tras mucho tiempo.', 25, '#a78bfa'),
            $this->badge('Nocturna', 'nocturna', '🌙', 'fun', 'Tiene mucha actividad nocturna.', 25, '#6366f1'),
            $this->badge('Madrugadora', 'madrugadora', '☀️', 'fun', 'Tiene mucha actividad temprano.', 25, '#facc15'),
            $this->badge('Hora del té', 'hora-del-te', '🍵', 'fun', 'Tiene participación constante y tranquila.', 25, '#34d399'),
            $this->badge('Nyan Yuri', 'nyan-yuri', '🐱', 'secret', 'Easter egg o condición oculta.', 80, '#f472b6'),
            $this->badge('Insignia secreta', 'insignia-secreta', '💎', 'secret', 'Condición que no se revela.', 80, '#c084fc'),
        ];
    }

    /** @return array{name: string, slug: string, description: string, icon: string, type: string, priority: int, color: string, is_active: bool} */
    private function badge(string $name, string $slug, string $icon, string $type, string $description, int $priority, string $color): array
    {
        return compact('name', 'slug', 'description', 'icon', 'type', 'priority', 'color') + ['is_active' => true];
    }
}
