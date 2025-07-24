<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    protected $model = Book::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'author' => $this->faker->name(),
            'isbn' => $this->faker->isbn13(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * 日本語の書籍データを生成
     */
    public function japanese(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $this->faker->randomElement([
                'プログラミング入門',
                'データベース設計',
                'Web開発の基礎',
                'Laravel実践ガイド',
                'PHP完全マスター',
                'システム設計の原則',
                'アルゴリズムとデータ構造',
                'ネットワーク技術',
                'セキュリティ対策',
                'クラウド活用術'
            ]),
            'author' => $this->faker->randomElement([
                '山田太郎',
                '佐藤花子',
                '田中一郎',
                '鈴木美咲',
                '高橋健太',
                '渡辺由美',
                '伊藤正雄',
                '中村あかり',
                '小林大輔',
                '加藤真理'
            ]),
        ]);
    }

    /**
     * 技術書のデータを生成
     */
    public function technical(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $this->faker->randomElement([
                'Clean Code',
                'Design Patterns',
                'Refactoring',
                'The Pragmatic Programmer',
                'Code Complete',
                'Structure and Interpretation of Computer Programs',
                'Introduction to Algorithms',
                'Computer Networks',
                'Operating System Concepts',
                'Database System Concepts'
            ]),
            'author' => $this->faker->randomElement([
                'Robert C. Martin',
                'Gang of Four',
                'Martin Fowler',
                'Andrew Hunt',
                'Steve McConnell',
                'Harold Abelson',
                'Thomas H. Cormen',
                'Andrew S. Tanenbaum',
                'Abraham Silberschatz',
                'Ramez Elmasri'
            ]),
        ]);
    }

    /**
     * 特定のISBNを持つ書籍
     */
    public function withIsbn(string $isbn): static
    {
        return $this->state(fn (array $attributes) => [
            'isbn' => $isbn,
        ]);
    }
}