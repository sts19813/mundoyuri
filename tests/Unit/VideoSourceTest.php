<?php

namespace Tests\Unit;

use App\Support\VideoSource;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class VideoSourceTest extends TestCase
{
    #[DataProvider('dailymotionUrls')]
    public function test_it_normalizes_dailymotion_urls_for_embedding(string $url): void
    {
        $this->assertSame(
            'https://geo.dailymotion.com/player.html?video=kMoruavr3wFz7EHkyr0',
            VideoSource::normalizeUrl('dailymotion', $url)
        );
    }

    public static function dailymotionUrls(): array
    {
        return [
            'short URL' => ['https://dai.ly/kMoruavr3wFz7EHkyr0'],
            'public URL' => ['https://www.dailymotion.com/video/kMoruavr3wFz7EHkyr0'],
            'public URL with title' => ['https://www.dailymotion.com/video/kMoruavr3wFz7EHkyr0_demo-title'],
            'legacy embed URL' => ['https://www.dailymotion.com/embed/video/kMoruavr3wFz7EHkyr0'],
            'current player URL' => ['https://geo.dailymotion.com/player.html?video=kMoruavr3wFz7EHkyr0'],
            'iframe' => ['<iframe src="https://www.dailymotion.com/embed/video/kMoruavr3wFz7EHkyr0"></iframe>'],
        ];
    }

    public function test_it_rejects_a_non_dailymotion_host(): void
    {
        $this->assertNull(
            VideoSource::normalizeUrl('dailymotion', 'https://example.com/video/kMoruavr3wFz7EHkyr0')
        );
    }
}
