<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('book_id');
            $table->index('status');
            $table->index('due_date');
            $table->index('returned_at');
            $table->index(['book_id', 'status']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->index('title');
            $table->index('author');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['book_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['returned_at']);
            $table->dropIndex(['book_id', 'status']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex(['title']);
            $table->dropIndex(['author']);
            $table->dropIndex(['created_at']);
        });
    }
};
