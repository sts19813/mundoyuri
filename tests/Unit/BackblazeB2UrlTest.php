<?php

namespace Tests\Unit;

use App\Support\BackblazeB2Url;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BackblazeB2UrlTest extends TestCase
{
    #[DataProvider('backblazeUrls')]
    public function test_it_parses_supported_backblaze_urls(string $url, string $bucket, string $file): void
    {
        $this->assertSame(compact('bucket', 'file'), BackblazeB2Url::parse($url));
    }

    public static function backblazeUrls(): array
    {
        return [
            'friendly URL' => [
                'https://f005.backblazeb2.com/file/mundoyuri/series/Fulfill.S01E01.mp4',
                'mundoyuri',
                'series/Fulfill.S01E01.mp4',
            ],
            'virtual-hosted S3 URL' => [
                'https://mundoyuri.s3.us-east-005.backblazeb2.com/Fulfill.S01E01.mp4',
                'mundoyuri',
                'Fulfill.S01E01.mp4',
            ],
            'path-style S3 URL' => [
                'https://s3.us-east-005.backblazeb2.com/mundoyuri/Fulfill%20S01E01.mp4',
                'mundoyuri',
                'Fulfill S01E01.mp4',
            ],
        ];
    }

    public function test_it_rejects_non_backblaze_urls(): void
    {
        $this->assertNull(BackblazeB2Url::parse('https://example.com/file/mundoyuri/video.mp4'));
    }

    public function test_normalization_removes_query_tokens(): void
    {
        $this->assertSame(
            'https://f005.backblazeb2.com/file/mundoyuri/video.mp4',
            BackblazeB2Url::normalize('https://f005.backblazeb2.com/file/mundoyuri/video.mp4?Authorization=secret')
        );
    }
}
