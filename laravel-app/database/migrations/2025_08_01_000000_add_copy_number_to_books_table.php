<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->unsignedInteger('copy_number')->default(1)->after('isbn');
        });

        // ISBNの単独ユニーク制約を削除し、ISBN+copy_numberの複合ユニークに変更
        Schema::table('books', function (Blueprint $table) {
            $table->dropUnique(['isbn']);
            $table->unique(['isbn', 'copy_number']);
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropUnique(['isbn', 'copy_number']);
            $table->unique('isbn');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('copy_number');
        });
    }
};
