<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // サンプル書籍データを作成
        $books = [
            [
                'title' => 'Laravel実践入門',
                'author' => '山田太郎',
                'isbn' => '978-4-12-345678-9',
                'publisher' => '技術評論社',
                'published_date' => '2024-01-15',
                'description' => 'Laravelフレームワークの基礎から実践的な使い方まで詳しく解説した入門書です。',
                'thumbnail_url' => 'https://via.placeholder.com/300x400/4F46E5/FFFFFF?text=Laravel%E5%AE%9F%E8%B7%B5%E5%85%A5%E9%96%80',
            ],
            [
                'title' => 'PHP8完全ガイド',
                'author' => '佐藤花子',
                'isbn' => '978-4-87-654321-0',
                'publisher' => 'オライリー・ジャパン',
                'published_date' => '2023-12-01',
                'description' => 'PHP8の新機能と改善点を網羅的に解説。実際のコード例とともに学べます。',
                'thumbnail_url' => 'https://via.placeholder.com/300x400/059669/FFFFFF?text=PHP8%E5%AE%8C%E5%85%A8%E3%82%AC%E3%82%A4%E3%83%89',
            ],
            [
                'title' => 'Docker実践ガイド',
                'author' => '田中一郎',
                'isbn' => '978-4-56-789012-3',
                'publisher' => '翔泳社',
                'published_date' => '2024-03-10',
                'description' => 'Dockerの基本概念から実際の運用まで、実践的な内容を豊富な例とともに解説。',
                'thumbnail_url' => 'https://via.placeholder.com/300x400/0EA5E9/FFFFFF?text=Docker%E5%AE%9F%E8%B7%B5%E3%82%AC%E3%82%A4%E3%83%89',
            ],
            [
                'title' => 'JavaScript モダン開発',
                'author' => '鈴木次郎',
                'isbn' => '978-4-98-765432-1',
                'publisher' => 'インプレス',
                'published_date' => '2024-02-20',
                'description' => 'ES2024の最新機能からReact、Vue.jsまで、モダンなJavaScript開発手法を学べます。',
                'thumbnail_url' => 'https://via.placeholder.com/300x400/F59E0B/FFFFFF?text=JavaScript%E3%83%A2%E3%83%80%E3%83%B3%E9%96%8B%E7%99%BA',
            ],
            [
                'title' => 'データベース設計の基礎',
                'author' => '高橋美咲',
                'isbn' => '978-4-11-223344-5',
                'publisher' => '日経BP',
                'published_date' => '2023-11-15',
                'description' => 'リレーショナルデータベースの設計原則から正規化、パフォーマンス最適化まで詳解。',
                'thumbnail_url' => 'https://via.placeholder.com/300x400/DC2626/FFFFFF?text=%E3%83%87%E3%83%BC%E3%82%BF%E3%83%99%E3%83%BC%E3%82%B9%E8%A8%AD%E8%A8%88',
            ],
            [
                'title' => 'Git & GitHub実践入門',
                'author' => '伊藤健太',
                'isbn' => '978-4-22-334455-6',
                'publisher' => 'SBクリエイティブ',
                'published_date' => '2024-01-30',
                'description' => 'バージョン管理システムGitとGitHubの使い方を実際のプロジェクトを通して学習。',
                'thumbnail_url' => 'https://via.placeholder.com/300x400/7C3AED/FFFFFF?text=Git%26GitHub%E5%AE%9F%E8%B7%B5%E5%85%A5%E9%96%80',
            ],
            [
                'title' => 'Webセキュリティ入門',
                'author' => '渡辺真理',
                'isbn' => '978-4-33-445566-7',
                'publisher' => 'マイナビ出版',
                'published_date' => '2023-10-05',
                'description' => 'Webアプリケーションのセキュリティ脅威と対策を実例とともに詳しく解説。',
                'thumbnail_url' => 'https://via.placeholder.com/300x400/EF4444/FFFFFF?text=Web%E3%82%BB%E3%82%AD%E3%83%A5%E3%83%AA%E3%83%86%E3%82%A3%E5%85%A5%E9%96%80',
            ],
            [
                'title' => 'アジャイル開発実践ガイド',
                'author' => '中村雅彦',
                'isbn' => '978-4-44-556677-8',
                'publisher' => '日本実業出版社',
                'published_date' => '2024-04-12',
                'description' => 'スクラムやカンバンなどのアジャイル手法を実際のチーム運営に活かす方法を解説。',
                'thumbnail_url' => 'https://via.placeholder.com/300x400/10B981/FFFFFF?text=%E3%82%A2%E3%82%B8%E3%83%A3%E3%82%A4%E3%83%AB%E9%96%8B%E7%99%BA',
            ],
        ];

        foreach ($books as $bookData) {
            Book::create($bookData);
        }

        // ランダムな書籍データも追加（テスト用）
        Book::factory(10)->create();
    }
}