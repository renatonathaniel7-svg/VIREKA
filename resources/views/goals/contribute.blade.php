@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">

    <div class="mb-6">
        <a href="{{ route('goals.index') }}"
           class="text-blue-600 hover:underline">
            ← Kembali ke Goals
        </a>
    </div>

    <div class="bg-white rounded-xl shadow p-6">

        <h1 class="text-2xl font-bold mb-4">
            🎯 {{ $goal->name }}
        </h1>

        <div class="mb-6">

            <p class="text-gray-600">
                Target Dana
            </p>

            <p class="font-bold text-lg">
                Rp {{ number_format($goal->target_amount,0,',','.') }}
            </p>

        </div>
        
        <div class="mb-6">

            <p class="text-gray-600">
                Saldo Tersedia
            </p>

            <p class="font-bold text-blue-600 text-lg">
                Rp {{ number_format($balance,0,',','.') }}
            </p>

        </div>
        
        <div class="mb-6">

            <p class="text-gray-600">
                Dana Terkumpul
            </p>

            <p class="font-bold text-lg text-green-600">
                Rp {{ number_format($goal->collected_amount,0,',','.') }}
            </p>

        </div>

        <form
            action="{{ route('goals.contribute.store', $goal->id) }}"
            method="POST">

            @csrf

            <div class="mb-4">

                <label class="block mb-2 font-medium">
                    Nominal Setoran
                </label>

                <input
                    type="number"
                    name="amount"
                    min="1000"
                    required
                    class="w-full border rounded px-3 py-2"
                    placeholder="Contoh: 500000">

            </div>

            <div class="mb-6">

                <label class="block mb-2 font-medium">
                    Catatan
                </label>

                <textarea
                    name="note"
                    rows="3"
                    class="w-full border rounded px-3 py-2"
                    placeholder="Tabungan bulan Juni"></textarea>

            </div>

            <button
                type="submit"
                class="bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700">

                Simpan Dana

            </button>

        </form>

    </div>

</div>
@endsection