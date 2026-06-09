<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ExpensePolicy
 *
 * Memastikan user HANYA bisa akses data miliknya sendiri.
 * Ini adalah lapisan keamanan penting — tanpa ini, user bisa akses
 * data orang lain dengan mengganti ID di URL.
 *
 * Daftarkan di AuthServiceProvider:
 *   $policies = [Expense::class => ExpensePolicy::class];
 *
 * Penggunaan di controller:
 *   $this->authorize('view', $expense);
 *   $this->authorize('update', $expense);
 *
 * Atau di blade:
 *   @can('delete', $expense) ... @endcan
 */
class ExpensePolicy
{
    use HandlesAuthorization;

    /**
     * User bisa lihat list expense → hanya miliknya (difilter via scopeForUser).
     * Policy ini tidak perlu dicek untuk index karena sudah dihandle di query.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * User hanya bisa lihat expense miliknya.
     */
    public function view(User $user, Expense $expense): bool
    {
        return $user->id === $expense->user_id;
    }

    /**
     * User yang sudah login bisa create expense baru.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * User hanya bisa update expense miliknya.
     * Tambahan: expense yang sudah 'verified' tidak bisa diedit
     * (integritas data — verified = data sudah dikonfirmasi AI).
     */
    public function update(User $user, Expense $expense): bool
    {
        if ($user->id !== $expense->user_id) {
            return false;
        }

        // Expense yang sudah verified tidak bisa diedit
        // Alasan: mengubah data yang sudah diverifikasi akan merusak audit trail
        return $expense->verified_status !== 'verified';
    }

    /**
     * User hanya bisa hapus expense miliknya.
     * Hanya expense dengan status draft/unverified yang bisa dihapus.
     */
    public function delete(User $user, Expense $expense): bool
    {
        if ($user->id !== $expense->user_id) {
            return false;
        }

        // Tidak bisa hapus yang sudah verified
        return in_array($expense->verified_status, ['draft', 'unverified', 'flagged']);
    }

    /**
     * Restore: tidak digunakan (soft delete tidak diimplementasi).
     */
    public function restore(User $user, Expense $expense): bool
    {
        return false;
    }

    /**
     * Force delete: tidak diizinkan untuk user biasa.
     */
    public function forceDelete(User $user, Expense $expense): bool
    {
        return false;
    }
}
