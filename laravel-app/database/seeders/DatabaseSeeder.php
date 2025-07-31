<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 管理者ユーザーを作成
        User::factory()->create([
            'name' => '管理者',
            'email' => 'admin@library.local',
            'role' => 1, // 1:管理者
            'password' => bcrypt('password'), // 開発環境用の簡単なパスワード
        ]);

        // 一般ユーザーを作成
        User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'user@library.local',
            'role' => 2, // 2:一般ユーザー
            'password' => bcrypt('password'),
        ]);

        // 追加の一般ユーザー（テスト用）
        User::factory(5)->create([
            'role' => 2, // 2:一般ユーザー
        ]);

        // 書籍データを作成
        $this->call(BookSeeder::class);
    }
}
