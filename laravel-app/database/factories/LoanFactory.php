<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Loan>
 */
class LoanFactory extends Factory
{
    protected $model = Loan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $borrowedAt = $this->faker->dateTimeBetween('-30 days', 'now');
        
        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'borrowed_at' => $borrowedAt,
            'returned_at' => null,
            'created_at' => $borrowedAt,
            'updated_at' => $borrowedAt,
        ];
    }

    /**
     * 返却済みの貸出記録
     */
    public function returned(): static
    {
        return $this->state(function (array $attributes) {
            $borrowedAt = $attributes['borrowed_at'] ?? $this->faker->dateTimeBetween('-30 days', '-1 day');
            $returnedAt = $this->faker->dateTimeBetween($borrowedAt, 'now');
            
            return [
                'borrowed_at' => $borrowedAt,
                'returned_at' => $returnedAt,
                'updated_at' => $returnedAt,
            ];
        });
    }

    /**
     * 現在借りている（未返却）の貸出記録
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'returned_at' => null,
        ]);
    }

    /**
     * 期限切れの貸出記録
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'borrowed_at' => $this->faker->dateTimeBetween('-60 days', '-15 days'),
            'returned_at' => null,
        ]);
    }

    /**
     * 特定のユーザーの貸出記録
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * 特定の書籍の貸出記録
     */
    public function forBook(Book $book): static
    {
        return $this->state(fn (array $attributes) => [
            'book_id' => $book->id,
        ]);
    }
}