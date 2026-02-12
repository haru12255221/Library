<?php

namespace App\Console\Commands;

use App\Models\Loan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckOverdueLoans extends Command
{
    protected $signature = 'app:check-overdue-loans';

    protected $description = '期限を過ぎた貸出中の書籍を延滞ステータスに更新する';

    public function handle(): int
    {
        $count = Loan::where('status', Loan::STATUS_BORROWED)
            ->where('due_date', '<', now()->startOfDay())
            ->update(['status' => Loan::STATUS_OVERDUE]);

        $message = "延滞チェック完了: {$count}件を延滞ステータスに更新しました";
        $this->info($message);
        Log::info($message);

        return self::SUCCESS;
    }
}
