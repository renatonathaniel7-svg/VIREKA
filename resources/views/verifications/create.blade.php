{{-- resources/views/verifications/create.blade.php --}}
{{-- Form upload screenshot untuk verifikasi expense atau income --}}

@extends('layouts.app')

@section('title', 'Verifikasi Transaksi')

@push('styles')
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4" x-data="verificationForm()">

    <div class="max-w-2xl mx-auto">

        {{-- ── Header ─────────────────────────────────────────────────── --}}
        <div class="mb-6">
            <a href="{{ $type === 'expense' ? route('expenses.show', $transaction->id) : route('income.show', $transaction->id) }}"
               class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-3">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke Detail Transaksi
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Verifikasi Transaksi</h1>
            <p class="text-gray-500 text-sm mt-1">Upload bukti screenshot mutasi atau saldo rekening</p>
        </div>

        {{-- ── Info Box Kuning (toleransi & batasan sistem) ───────────── --}}
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-amber-800 text-sm font-medium">Tentang Sistem Verifikasi</p>
                    <p class="text-amber-700 text-sm mt-1">
                        Sistem akan membandingkan screenshot dengan data yang kamu input.
                        <strong>Toleransi perbedaan nominal: 5%</strong> (untuk pembulatan dan potongan pajak).
                    </p>
                    <p class="text-amber-600 text-xs mt-1">
                        ⚠ Sistem ini berfungsi sebagai pengecekan akurasi, bukan pencegahan kecurangan penuh.
                        Screenshot bisa dimanipulasi — akurasi OCR bervariasi antar bank.
                    </p>
                </div>
            </div>
        </div>

        {{-- ── Detail Transaksi yang Akan Diverifikasi ─────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Detail Transaksi</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-400">Tipe</p>
                    <p class="text-sm font-medium text-gray-900">
                        {{ $type === 'expense' ? 'Pengeluaran' : 'Pendapatan' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Status Saat Ini</p>
                    @include('verifications._status_badge', ['status' => $transaction->verified_status ?? 'draft'])
                </div>
                <div>
                    <p class="text-xs text-gray-400">Nominal</p>
                    <p class="text-lg font-bold text-gray-900">
                        Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Tanggal</p>
                    <p class="text-sm font-medium text-gray-900">
                        {{ \Carbon\Carbon::parse($transaction->date)->isoFormat('D MMMM YYYY') }}
                    </p>
                </div>
                @if($type === 'expense' && isset($transaction->category))
                <div>
                    <p class="text-xs text-gray-400">Kategori</p>
                    <p class="text-sm font-medium text-gray-900">{{ $transaction->category->name ?? '-' }}</p>
                </div>
                @endif
                @if(isset($transaction->description) && $transaction->description)
                <div class="col-span-2">
                    <p class="text-xs text-gray-400">Deskripsi</p>
                    <p class="text-sm text-gray-700">{{ $transaction->description }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- ── Error Messages ──────────────────────────────────────────── --}}
        @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <p class="text-red-700 text-sm font-medium">Terdapat kesalahan:</p>
            <ul class="mt-1 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li class="text-red-600 text-sm">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- ── Form Upload ─────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-4">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Upload Bukti Transaksi</h2>

            <form action="{{ route('verifications.store', [$type, $transaction->id]) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  @submit="handleSubmit">
                @csrf

                {{-- FilePond drop area --}}
                <div class="mb-5">
                    <label for="screenshot" class="block text-sm font-medium text-gray-700 mb-2">
                        Screenshot Mutasi / Saldo Rekening
                    </label>

                    {{-- Input file yang akan di-enhance oleh FilePond --}}
                    <input type="file"
                           id="screenshot"
                           name="screenshot"
                           accept=".jpg,.jpeg,.png,.pdf"
                           class="filepond"
                           data-max-file-size="5MB"
                           data-label-idle='<span class="text-gray-400">
                               <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                               </svg>
                               <span class="font-medium text-indigo-600">Klik untuk pilih file</span>
                               atau drag &amp; drop di sini
                           </span>
                           <span class="block text-xs text-gray-400 mt-1">JPG, PNG, atau PDF — Maks. 5MB</span>'>

                    <p class="text-xs text-gray-400 mt-2">
                        💡 Tips: Pastikan screenshot menunjukkan nominal, tanggal, dan nama bank dengan jelas.
                    </p>
                </div>

                {{-- Loading state (Alpine.js) --}}
                <div x-show="isLoading" x-cloak
                     class="flex items-center justify-center py-4 mb-4 bg-indigo-50 rounded-lg">
                    <svg class="animate-spin h-5 w-5 text-indigo-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 0v4a8 8 0 00-8 8H4z"></path>
                    </svg>
                    <span class="text-indigo-700 text-sm font-medium">Sedang menganalisis screenshot...</span>
                </div>

                {{-- Submit button --}}
                <button type="submit"
                        :disabled="isLoading"
                        :class="isLoading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-indigo-700'"
                        class="w-full bg-indigo-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2">
                    <svg x-show="!isLoading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Mulai Verifikasi</span>
                </button>
            </form>
        </div>

        {{-- ── Link Cash (tidak ada bukti) ──────────────────────────────── --}}
        <div class="text-center">
            <p class="text-sm text-gray-400 mb-2">Tidak memiliki bukti digital?</p>
            <form action="{{ route('verifications.cash', [$type, $transaction->id]) }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                        onclick="return confirm('Tandai sebagai transaksi tunai? Transaksi ini akan masuk ke shadow balance dan tidak mempengaruhi Financial Health Score.')"
                        class="text-sm text-gray-500 hover:text-gray-700 underline">
                    Tandai sebagai Transaksi Tunai (tidak ada bukti)
                </button>
            </form>
        </div>

    </div>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    <script>
        // ── Inisialisasi FilePond ──────────────────────────────────────────
        FilePond.registerPlugin(FilePondPluginImagePreview);

        const inputElement = document.querySelector('#screenshot');
        FilePond.create(inputElement, {
            storeAsFile: true,
            allowMultiple: false,
            maxFileSize: '5MB',
            acceptedFileTypes: ['image/jpeg', 'image/png', 'application/pdf'],
            labelIdle: `
                <span class="text-gray-400">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="font-medium text-indigo-600">Klik untuk pilih file</span> atau drag & drop
                </span>
                <span class="block text-xs text-gray-400 mt-1">JPG, PNG, atau PDF — Maks. 5MB</span>
            `,
        });

        // ── Alpine.js component ───────────────────────────────────────────
        function verificationForm() {
            return {
                isLoading: false,
                handleSubmit() {
                    this.isLoading = true;
                    // Biarkan form submit secara normal setelah loading state aktif
                }
            }
        }
    </script>
@endpush
