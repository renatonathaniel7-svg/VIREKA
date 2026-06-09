{{-- ============================================================ --}}
{{-- UPDATE: resources/views/expenses/show.blade.php             --}}
{{-- Tambahkan section ini di bawah detail transaksi utama.      --}}
{{-- Sesuaikan layout dengan struktur existing view kamu.        --}}
{{-- ============================================================ --}}

{{--
    Asumsi: variabel $expense tersedia di view ini.
    $expense->verified_status dapat bernilai: draft|pending|verified|flagged|unverified
--}}

{{-- ── Section: Status Verifikasi ─────────────────────────────────────── --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mt-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Status Verifikasi</h2>
        @include('verifications._status_badge', ['status' => $expense->verified_status ?? 'draft'])
    </div>

    @php
        $verifiedStatus = $expense->verified_status ?? 'draft';

        // Ambil record verifikasi terkait (jika ada)
        $latestVerification = \App\Models\Verification::where('reference_type', 'expense')
            ->where('reference_id', $expense->id)
            ->where('user_id', auth()->id())
            ->latest()
            ->first();
    @endphp

    {{-- STATUS: DRAFT — Belum pernah upload screenshot --}}
    @if($verifiedStatus === 'draft')
    <div class="flex items-start gap-3">
        <div class="flex-1">
            <p class="text-sm text-gray-600">
                Transaksi ini belum diverifikasi. Upload screenshot mutasi atau saldo rekening
                untuk memasukkannya ke <strong>verified balance</strong> dan
                memperhitungkannya dalam Financial Health Score.
            </p>
            <p class="text-xs text-gray-400 mt-1">
                ℹ Tanpa verifikasi, transaksi hanya masuk ke shadow balance.
            </p>
        </div>
        <a href="{{ route('verifications.create', ['expense', $expense->id]) }}"
           class="flex-shrink-0 inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700
                  text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Upload Bukti
        </a>
    </div>

    {{-- STATUS: PENDING — Screenshot sudah diupload, sedang/menunggu proses --}}
    @elseif($verifiedStatus === 'pending')
    <div class="flex items-center gap-3">
        <svg class="w-5 h-5 text-blue-500 animate-spin flex-shrink-0"
             fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 0v4a8 8 0 00-8 8H4z"></path>
        </svg>
        <p class="text-sm text-blue-700">
            Screenshot sudah diupload. Verifikasi sedang diproses atau menunggu review manual.
        </p>
    </div>
    @if($latestVerification)
    <div class="mt-3">
        <a href="{{ route('verifications.show', $latestVerification->id) }}"
           class="text-sm text-blue-600 hover:text-blue-800 underline">
            Lihat Status Detail →
        </a>
    </div>
    @endif

    {{-- STATUS: VERIFIED — Transaksi terverifikasi --}}
    @elseif($verifiedStatus === 'verified')
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                  clip-rule="evenodd"/>
        </svg>
        <div class="flex-1">
            <p class="text-sm text-green-700 font-medium">
                Transaksi berhasil diverifikasi dan sudah masuk ke verified balance.
            </p>
            @if($latestVerification?->ai_confidence)
            <p class="text-xs text-green-600 mt-1">
                Confidence AI: {{ round($latestVerification->ai_confidence * 100) }}%
            </p>
            @endif
        </div>
        @if($latestVerification)
        <a href="{{ route('verifications.show', $latestVerification->id) }}"
           class="flex-shrink-0 text-sm text-green-700 hover:text-green-900 underline font-medium">
            Lihat Detail
        </a>
        @endif
    </div>

    {{-- STATUS: FLAGGED — Ada discrepancy, perlu review --}}
    @elseif($verifiedStatus === 'flagged')
    <div class="space-y-3">
        <div class="flex items-start gap-3 bg-amber-50 rounded-lg p-3">
            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                      d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                      clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="text-sm text-amber-800 font-medium">Terdapat perbedaan antara screenshot dan data input.</p>
                @if($latestVerification?->flag_reason)
                <p class="text-xs text-amber-700 mt-1">{{ $latestVerification->flag_reason }}</p>
                @endif
            </div>
        </div>
        <div class="flex gap-2">
            @if($latestVerification)
            <a href="{{ route('verifications.show', $latestVerification->id) }}"
               class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-gray-800
                      border border-gray-300 px-3 py-1.5 rounded-lg transition-colors">
                Lihat Detail
            </a>
            <form action="{{ route('verifications.retry', $latestVerification->id) }}" method="POST">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1 text-sm text-amber-700 hover:text-amber-900
                               border border-amber-300 bg-amber-50 px-3 py-1.5 rounded-lg transition-colors">
                    Coba Lagi
                </button>
            </form>
            @endif
            <a href="{{ route('verifications.create', ['expense', $expense->id]) }}"
               class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-800
                      border border-indigo-300 bg-indigo-50 px-3 py-1.5 rounded-lg transition-colors">
                Upload Screenshot Baru
            </a>
        </div>
    </div>

    {{-- STATUS: UNVERIFIED — Transaksi tunai / shadow balance --}}
    @elseif($verifiedStatus === 'unverified')
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="flex-1">
            <p class="text-sm text-gray-600">
                <span class="font-medium">Transaksi tunai.</span>
                Masuk ke shadow balance sebagai estimasi. Tidak mempengaruhi
                verified balance dan Financial Health Score.
            </p>
        </div>
    </div>

    @endif
</div>
{{-- ── End Section: Status Verifikasi ─────────────────────────────────── --}}
