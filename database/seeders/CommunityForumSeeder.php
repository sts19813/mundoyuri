<?php

namespace Database\Seeders;

use App\Models\Forum;
use App\Models\ForumCategory;
use Illuminate\Database\Seeder;

class CommunityForumSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->structure() as $categoryData) {
            $forums = $categoryData['forums'];
            unset($categoryData['forums']);

            $category = ForumCategory::query()->firstOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData,
            );

            foreach ($forums as $forumData) {
                Forum::query()->firstOrCreate(
                    ['slug' => $forumData['slug']],
                    ['forum_category_id' => $category->id, ...$forumData],
                );
            }
        }
    }

    /** @return array<int, array{name: string, slug: string, sort_order: int, is_active: bool, forums: array<int, array{name: string, slug: string, description: string, sort_order: int, is_active: bool, is_locked: bool}>}> */
    private function structure(): array
    {
        return [
            [
                'name' => 'Bienvenidas',
                'slug' => 'bienvenidas',
                'sort_order' => 10,
                'is_active' => true,
                'forums' => [
                    ['name' => 'Presentaciones', 'slug' => 'presentaciones', 'description' => 'Preséntate y conoce a las demás personas de la comunidad.', 'sort_order' => 10, 'is_active' => true, 'is_locked' => false],
                ],
            ],
            [
                'name' => "Girls' Love",
                'slug' => 'girls-love',
                'sort_order' => 20,
                'is_active' => true,
                'forums' => [
                    ['name' => 'Yuri', 'slug' => 'yuri', 'description' => 'Anime, manga, series y todo lo relacionado con Girls\' Love.', 'sort_order' => 10, 'is_active' => true, 'is_locked' => false],
                    ['name' => 'Recomendaciones', 'slug' => 'recomendaciones', 'description' => 'Busca o comparte recomendaciones de anime, manga y series GL.', 'sort_order' => 20, 'is_active' => true, 'is_locked' => false],
                    ['name' => 'Busco una obra', 'slug' => 'busco-una-obra', 'description' => '¿No recuerdas el nombre de un anime, manga o serie? Pregunta aquí.', 'sort_order' => 30, 'is_active' => true, 'is_locked' => false],
                ],
            ],
            [
                'name' => 'Comunidad',
                'slug' => 'comunidad',
                'sort_order' => 30,
                'is_active' => true,
                'forums' => [
                    ['name' => 'General', 'slug' => 'general', 'description' => 'Conversaciones generales de la comunidad.', 'sort_order' => 10, 'is_active' => true, 'is_locked' => false],
                    ['name' => 'Música', 'slug' => 'musica', 'description' => 'Música, artistas, openings, endings y temas relacionados.', 'sort_order' => 20, 'is_active' => true, 'is_locked' => false],
                    ['name' => 'Juegos', 'slug' => 'juegos', 'description' => 'Videojuegos y juegos relacionados con la comunidad.', 'sort_order' => 30, 'is_active' => true, 'is_locked' => false],
                ],
            ],
            [
                'name' => 'Creatividad',
                'slug' => 'creatividad',
                'sort_order' => 40,
                'is_active' => true,
                'forums' => [
                    ['name' => 'Fanart', 'slug' => 'fanart', 'description' => 'Comparte ilustraciones y trabajos creativos.', 'sort_order' => 10, 'is_active' => true, 'is_locked' => false],
                    ['name' => 'Fanfics y textos', 'slug' => 'fanfics-y-textos', 'description' => 'Historias, fanfics y escritos de la comunidad.', 'sort_order' => 20, 'is_active' => true, 'is_locked' => false],
                ],
            ],
            [
                'name' => 'Mundo Yuri',
                'slug' => 'mundo-yuri',
                'sort_order' => 50,
                'is_active' => true,
                'forums' => [
                    ['name' => 'Dudas y sugerencias', 'slug' => 'dudas-y-sugerencias', 'description' => 'Preguntas, sugerencias y comentarios sobre Mundo Yuri.', 'sort_order' => 10, 'is_active' => true, 'is_locked' => false],
                ],
            ],
        ];
    }
}
