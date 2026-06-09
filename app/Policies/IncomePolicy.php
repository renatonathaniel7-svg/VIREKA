<?php

namespace App\Policies;

use App\Models\IncomeEntry;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * IncomePolicy
 *
 * Mirror dari ExpensePolicy untuk IncomeEntry model.
 * Memastikan setiap user hanya bisa mengakses data income miliknya.
 *
 * Daftarkan di AuthServiceProvider:
 *   $policies = [IncomeEntry::class => IncomePolicy::class];
 */
class IncomePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Hanya bisa lihat income entry milik sendiri.
     */
    public function view(User $user, IncomeEntry $incomeEntry): bool
    {
        return $user->id === $incomeEntry->user_id;
    }

    /**
     * Semua user yang login bisa buat income entry baru.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Hanya bisa update income entry milik sendiri.
     * Income yang sudah verified tidak bisa diedit (menjaga integritas running balance).
     */
    public function update(User $user, IncomeEntry $incomeEntry): bool
    {
        if ($user->id !== $incomeEntry->user_id) {
            return false;
        }

        return $incomeEntry->verified_status !== 'verified';
    }

    /**
     * Hanya bisa hapus income entry milik sendiri yang belum verified.
     */
    public function delete(User $user, IncomeEntry $incomeEntry): bool
    {
        if ($user->id !== $incomeEntry->user_id) {
            return false;
        }

        return in_array($incomeEntry->verified_status, ['draft', 'unverified', 'flagged']);
    }

    public function restore(User $user, IncomeEntry $incomeEntry): bool
    {
        return false;
    }

    public function forceDelete(User $user, IncomeEntry $incomeEntry): bool
    {
        return false;
    }
}
