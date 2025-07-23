<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'author', 'isbn'];

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * 現在貸出中の貸出情報を取得するリレーション。
     * 返却日がnullのものが対象。
     */
    public function currentLoan()
    {
        return $this->hasOne(Loan::class)->whereNull('returned_at');
    }

    /**
     * 本が利用可能かどうかを判定する。
     */
    public function isAvailable(): bool
    {
        return $this->currentLoan === null;
    }

    /**
     * ログイン中のユーザーがこの本を借りているかどうかを判定する。
     */
    public function isBorrowedByMe(): bool
    {
        if ($this->isAvailable() || !auth()->check()) {
            return false;
        }

        return $this->currentLoan->user_id === auth()->id();
    }
}