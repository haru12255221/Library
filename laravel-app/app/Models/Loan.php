<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = [
        'user_id',
        'book_id', 
        'borrowed_at',
        'due_date',
        'returned_at',
        'status'
    ];

    // 2. 日付として扱うカラム
    protected $casts = [
        'borrowed_at' => 'datetime',
        'due_date' => 'date',
        'returned_at' => 'datetime',
    ];

    const STATUS_BORROWED = 1;  // 貸出中
    const STATUS_RETURNED = 2;  // 返却済み
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class)->withTrashed();
    }

    // 5. 便利メソッド
    public function isBorrowed()
    {
        return $this->status === self::STATUS_BORROWED;
    }

    public function isReturned()
    {
        return $this->status === self::STATUS_RETURNED;
    }

}
