<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleBooksService
{
    private const API_BASE_URL = 'https://www.googleapis.com/books/v1/volumes';
    private const TIMEOUT_SECONDS = 10;

    /**
     * ISBNから書籍情報を取得する
     */
    public function fetchByIsbn(string $isbn): ?array
    {
        try {
            $cleanIsbn = $this->cleanIsbn($isbn);
            
            Log::info("Google Books API request: ISBN={$cleanIsbn}");
            
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->get(self::API_BASE_URL, [
                    'q' => "isbn:{$cleanIsbn}",
                    'maxResults' => 1
                ]);

            Log::info("Google Books API response status: " . $response->status());
            
            if ($response->successful()) {
                $data = $response->json();
                Log::info("Google Books API response data: " . json_encode($data));
                
                if (isset($data['items'][0])) {
                    return $this->parseVolumeInfo($data['items'][0]['volumeInfo']);
                }
                
                Log::info("Google Books API: No items found for ISBN {$isbn}");
                return null;
            }

            Log::error("Google Books API HTTP Error: Status {$response->status()}, Body: " . $response->body());
            throw new \Exception("Google Books API returned status {$response->status()}");

        } catch (\Exception $e) {
            Log::error("Google Books API Error: " . $e->getMessage());
            throw $e; // エラーを再スローしてコントローラーで処理
        }
    }

    /**
     * APIレスポンスから必要な書籍情報を抽出する
     */
    private function parseVolumeInfo(array $volumeInfo): array
    {
        return [
            'title' => $volumeInfo['title'] ?? 'タイトル不明',
            'authors' => isset($volumeInfo['authors']) 
                ? implode(', ', $volumeInfo['authors']) 
                : '著者不明',
            'publisher' => $volumeInfo['publisher'] ?? null,
            'published_date' => $this->parsePublishedDate($volumeInfo['publishedDate'] ?? null),
            'description' => $volumeInfo['description'] ?? null,
            'thumbnail_url' => $this->getThumbnailUrl($volumeInfo['imageLinks'] ?? []),
            'page_count' => $volumeInfo['pageCount'] ?? null,
            'language' => $volumeInfo['language'] ?? 'ja',
            'google_books_id' => $volumeInfo['industryIdentifiers'][0]['identifier'] ?? null,
        ];
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

        // Google Books APIは様々な形式で日付を返すため、柔軟に対応
        try {
            // "2024-07-26", "2024-07", "2024" などに対応
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

    /**
     * 表紙画像URLを取得する
     */
    private function getThumbnailUrl(array $imageLinks): ?string
    {
        // 優先順位: thumbnail > smallThumbnail > medium > large
        $priorities = ['thumbnail', 'smallThumbnail', 'medium', 'large'];
        
        foreach ($priorities as $size) {
            if (isset($imageLinks[$size])) {
                // HTTPSに変換
                return str_replace('http://', 'https://', $imageLinks[$size]);
            }
        }

        return null;
    }

    /**
     * タイトルで検索する（ISBNが見つからない場合のフォールバック）
     */
    public function searchByTitle(string $title): array
    {
        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->get(self::API_BASE_URL, [
                    'q' => $title,
                    'maxResults' => 5
                ]);

            if ($response->successful() && isset($response['items'])) {
                $results = [];
                foreach ($response['items'] as $item) {
                    $results[] = $this->parseVolumeInfo($item['volumeInfo']);
                }
                return $results;
            }

            return [];

        } catch (\Exception $e) {
            Log::error("Google Books API Search Error: " . $e->getMessage());
            return [];
        }
    }
}