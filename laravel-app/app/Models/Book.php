<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    // この行はモデル名とテーブル名が一致しているので、Laravelが自動で認識してくれるので書かなくても良い
    protected $table = 'books';
    // カラムを増やしたかったら以下に追記する
    protected $fillable = [
        'title',
        'author',
        'isbn',
    ];
}
