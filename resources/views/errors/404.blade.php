{{-- errors/404.blade.php --}}
@extends('layouts.app')

@section('title', '404 — Halaman Tidak Ditemukan')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center px-4">
    <div class="max-w-md w-full text-center">
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-amber-100 dark:bg-amber-900/30 rounded-3xl mb-6">
                <svg class="w-12 h-12 text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-6xl font-black text-gray-200 dark:text-gray-700 mb-2">404</h1>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Halaman Tidak Ditemukan</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                Halaman yang Anda cari tidak ada atau sudah dipindahkan.
                Mungkin URL yang dimasukkan salah, atau halaman telah dihapus.
            </p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Kembali ke Dashboard
            </a>
            <a href="javascript:history.back()"
               class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Halaman Sebelumnya
            </a>
        </div>
    </div>
</div>
@endsection
