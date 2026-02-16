<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'copy_number',
        'publisher',
        'published_date',
        'description',
        'thumbnail_url'
    ];

    protected $casts = [
        'published_date' => 'date',
    ];

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

    /**
     * 出版日を日本語形式でフォーマットして取得する。
     */
    public function getFormattedPublishedDateAttribute(): string
    {
        return $this->published_date ? $this->published_date->format('Y年m月d日') : '不明';
    }

    /**
     * 表紙画像のURLを取得する（Google Books APIまたはデフォルト画像）。
     */
    public function getThumbnailImageAttribute(): string
    {
        return $this->thumbnail_url ?: '/images/no-book-cover.png';
    }

    /**
     * 出版社を日本語形式でフォーマットして取得する。
     */
    public function getFormattedPublisherAttribute(): string
    {
        return $this->publisher ?: '不明';
    }

    /**
     * 説明文を日本語形式でフォーマットして取得する（長い場合は省略）。
     */
    public function getFormattedDescriptionAttribute(): string
    {
        if (!$this->description) {
            return '説明なし';
        }
        
        return mb_strlen($this->description) > 100 
            ? mb_substr($this->description, 0, 100) . '...' 
            : $this->description;
    }

    /**
     * 著者名を日本語形式でフォーマットして取得する。
     */
    public function getFormattedAuthorAttribute(): string
    {
        return $this->author ?: '不明';
    }

    /**
     * 冊番号付きの表示タイトルを取得する（2冊以上ある場合のみ表示）。
     */
    public function getDisplayTitleAttribute(): string
    {
        if ($this->copy_number > 1) {
            return "{$this->title} [冊{$this->copy_number}]";
        }
        return $this->title;
    }
}