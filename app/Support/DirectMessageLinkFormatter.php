<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

class DirectMessageLinkFormatter
{
    private const URL_PATTERN = '~https?://[^\s<]+~iu';

    public static function html(?string $body): HtmlString
    {
        $body ??= '';
        $parts = preg_split('~(https?://[^\\s<]+)~iu', $body, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($parts === false) {
            return new HtmlString(e($body));
        }

        $html = '';
        foreach ($parts as $index => $part) {
            if ($index % 2 === 0) {
                $html .= e($part);

                continue;
            }

            $url = rtrim($part, '.,!?;:');
            $suffix = substr($part, strlen($url));

            if (filter_var($url, FILTER_VALIDATE_URL) && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)) {
                $html .= '<a class="messenger-inline-link" href="'.e($url).'" target="_blank" rel="noopener noreferrer nofollow">'.e($url).'</a>'.e($suffix);
            } else {
                $html .= e($part);
            }
        }

        return new HtmlString($html);
    }

    /**
     * @return array<int, string>
     */
    public static function urls(?string $body): array
    {
        preg_match_all(self::URL_PATTERN, $body ?? '', $matches);

        return collect($matches[0] ?? [])
            ->map(fn (string $url) => rtrim($url, '.,!?;:'))
            ->filter(fn (string $url) => filter_var($url, FILTER_VALIDATE_URL)
                && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true))
            ->unique()
            ->values()
            ->all();
    }
}
