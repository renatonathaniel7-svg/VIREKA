<?php
if (! function_exists('rupiah')) {
    function rupiah(int|float $amount): string
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }
}

if (! function_exists('rupiah_short')) {
    function rupiah_short(int|float $amount): string
    {
        if ($amount >= 1_000_000_000) {
            return 'Rp ' . number_format($amount / 1_000_000_000, 1, ',', '.') . ' M';
        }

        if ($amount >= 1_000_000) {
            return 'Rp ' . number_format($amount / 1_000_000, 1, ',', '.') . ' Jt';
        }

        return rupiah($amount);
    }
}
