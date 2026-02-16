<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withCount(['loans' => function ($query) {
            $query->whereNull('returned_at');
        }])->orderBy('created_at', 'desc')->get();

        return view('admin.users.index', compact('users'));
    }

    public function toggleRole(User $user)
    {
        // 自分自身の権限変更は不可
        if ($user->id === auth()->id()) {
            return back()->with('error', '自分自身の権限は変更できません');
        }

        $user->update([
            'role' => $user->isAdmin() ? User::ROLE_USER : User::ROLE_ADMIN,
        ]);

        $newRole = $user->isAdmin() ? '管理者' : '一般ユーザー';
        Log::info("User role changed: {$user->name} → {$newRole} (ID: {$user->id})");
        AuditLog::log('user_role_changed', $user, "{$user->name} → {$newRole}");

        return back()->with('success', "「{$user->name}」の権限を{$newRole}に変更しました");
    }
}
