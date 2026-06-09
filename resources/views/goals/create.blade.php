@extends('layouts.app')

@section('content')

<div class="max-w-xl mx-auto">

    <div class="flex items-center gap-2 text-sm text-slate-500 mt-4 mb-6">
        <a href="{{ route('goals.index') }}"
           class="hover:text-indigo-600 transition-colors">
            Goals
        </a>

        <svg xmlns="http://www.w3.org/2000/svg"
             viewBox="0 0 24 24"
             fill="none"
             stroke="currentColor"
             stroke-width="2"
             stroke-linecap="round"
             stroke-linejoin="round"
             class="w-4 h-4">
            <path d="m9 18 6-6-6-6"/>
        </svg>

        <span class="text-slate-800 font-medium">
            Tambah Baru
        </span>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl p-6">

        <h2 class="text-lg font-bold text-slate-900 mb-5">
            🎯 Buat Goal Baru
        </h2>

        <form action="{{ route('goals.store') }}" method="POST">
    @csrf

    <div class="mb-4">
        <label class="block mb-2 font-medium">
            Nama Goal
        </label>

        <input
            type="text"
            name="name"
            class="w-full border rounded px-3 py-2"
            placeholder="Contoh: Axioo Hype 7"
            required>
    </div>

    <div class="mb-4">
        <label class="block mb-2 font-medium">
            Target Dana
        </label>

        <input
            type="number"
            name="target_amount"
            class="w-full border rounded px-3 py-2"
            placeholder="Contoh: 9000000"
            required>
    </div>

    <div class="mb-4">
        <label class="block mb-2 font-medium">
            Target Tanggal
        </label>

        <input
            type="date"
            name="target_date"
            class="w-full border rounded px-3 py-2">
    </div>

    <div class="mb-6">
        <label class="block mb-2 font-medium">
            Deskripsi
        </label>

        <textarea
            name="description"
            rows="4"
            class="w-full border rounded px-3 py-2"
            placeholder="Contoh: Laptop untuk kuliah, coding, dan pengerjaan TA"></textarea>

        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">

    <p class="text-sm text-blue-700">

        🎯 Goal membantu kamu menyisihkan dana secara bertahap
        untuk kebutuhan besar seperti laptop, smartphone,
        kendaraan, dana darurat, atau liburan impian.

    </p>

</div>
    </div>

<div class="flex justify-end gap-3">

    <a
        href="{{ route('goals.index') }}"
        class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">
        Batal
    </a>

    <button
        type="submit"
        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">
        Simpan Goal
    </button>

</div>
@endsection