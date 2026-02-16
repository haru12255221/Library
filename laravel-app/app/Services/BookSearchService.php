<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BookSearchService
{
    private const OPENBD_API_URL = 'https://api.openbd.jp/v1/get';
    private const NDL_API_URL = 'https://ndlsearch.ndl.go.jp/api/opensearch';
    private const TIMEOUT_SECONDS = 10;

    /**
     * ISBNから書籍情報を取得する（openBD → NDL の順で検索）
     */
    public function fetchByIsbn(string $isbn): ?array
    {
        $cleanIsbn = $this->cleanIsbn($isbn);

        // 1段目: openBD
        $result = $this->fetchFromOpenBD($cleanIsbn);
        if ($result) {
            return $result;
        }

        // 2段目: 国立国会図書館サーチ
        $result = $this->fetchFromNDL($cleanIsbn);
        if ($result) {
            return $result;
        }

        Log::info("BookSearch: No book found for ISBN {$isbn}");
        return null;
    }

    /**
     * openBD APIから書籍情報を取得する
     */
    private function fetchFromOpenBD(string $isbn): ?array
    {
        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->get(self::OPENBD_API_URL, ['isbn' => $isbn]);

            if (!$response->successful()) {
                Log::warning("openBD API: HTTP error for ISBN {$isbn}");
                return null;
            }

            $data = $response->json();

            if (empty($data) || $data[0] === null) {
                return null;
            }

            $summary = $data[0]['summary'] ?? [];
            $hanmoto = $data[0]['hanmoto'] ?? [];

            $title = $summary['title'] ?? null;
            if (!$title) {
                return null;
            }

            return [
                'title' => $title,
                'authors' => $summary['author'] ?? '著者不明',
                'publisher' => $summary['publisher'] ?? null,
                'published_date' => $this->parsePublishedDate($hanmoto['dateshuppan'] ?? ($summary['pubdate'] ?? null)),
                'description' => $data[0]['onix']['CollateralDetail']['TextContent'][0]['Text'] ?? null,
                'thumbnail_url' => $summary['cover'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error("openBD API Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 国立国会図書館サーチAPIから書籍情報を取得する
     */
    private function fetchFromNDL(string $isbn): ?array
    {
        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->get(self::NDL_API_URL, ['isbn' => $isbn]);

            if (!$response->successful()) {
                Log::warning("NDL API: HTTP error for ISBN {$isbn}");
                return null;
            }

            $xml = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NONET);
            if ($xml === false) {
                Log::warning("NDL API: Failed to parse XML for ISBN {$isbn}");
                return null;
            }

            $items = $xml->channel->item ?? null;
            if (!$items || count($items) === 0) {
                return null;
            }

            $item = $items[0];
            $dc = $item->children('dc', true);

            $title = (string) ($dc->title ?? $item->title ?? null);
            if (!$title) {
                return null;
            }

            $author = (string) ($dc->creator ?? $item->author ?? '著者不明');
            $publisher = (string) ($dc->publisher ?? '');

            return [
                'title' => $title,
                'authors' => $author,
                'publisher' => $publisher ?: null,
                'published_date' => $this->parsePublishedDate((string) ($dc->date ?? null)),
                'description' => (string) ($item->description ?? null) ?: null,
                'thumbnail_url' => null,
            ];
        } catch (\Exception $e) {
            Log::error("NDL API Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * ISBNをクリーンアップする（ハイフンを削除）
     */
    private function cleanIsbn(string $isbn): string
    {
        return preg_replace('/[^0-9X]/', '', $isbn);
    }

    /**
     * 出版日をパースしてY-m-d形式にする
     */
    private function parsePublishedDate(?string $publishedDate): ?string
    {
        if (!$publishedDate) {
            return null;
        }

        try {
            if (preg_match('/^(\d{4})(-\d{2})?(-\d{2})?/', $publishedDate, $matches)) {
                $year = $matches[1];
                $month = isset($matches[2]) ? substr($matches[2], 1) : '01';
                $day = isset($matches[3]) ? substr($matches[3], 1) : '01';

                return "{$year}-{$month}-{$day}";
            }
        } catch (\Exception $e) {
            Log::warning("Failed to parse published date: {$publishedDate}");
        }

        return null;
    }
}
