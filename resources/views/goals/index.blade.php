@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">

    <h1 class="text-2xl font-bold mb-6">
        🎯 Goals & Wishlist
    </h1>

    <div class="flex justify-between items-center mb-4">

    <h2 class="font-bold text-lg">
        Daftar Goal
    </h2>

    <a
        href="{{ route('goals.create') }}"
        class="bg-indigo-600 text-white px-4 py-2 rounded">

        + Goal Baru

    </a>

</div>

@forelse($goals as $goal)

@php
    $progress = $goal->target_amount > 0
        ? min(100, round(($goal->collected_amount / $goal->target_amount) * 100))
        : 0;
@endphp

<div class="bg-white border rounded-xl p-5 mb-4">

    <div class="flex justify-between items-start">

        <div>
            <h3 class="font-bold text-lg">
                🎯 {{ $goal->name }}
            </h3>

            @if($goal->description)
                <p class="text-sm text-gray-500 mt-1">
                    {{ $goal->description }}
                </p>
            @endif
        </div>

        <span class="font-bold text-blue-600">
            {{ $progress }}%
        </span>

    </div>

    {{-- Progress --}}
    <div class="mt-4">
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div
                class="bg-blue-600 h-3 rounded-full"
                style="width: {{ $progress }}%">
            </div>
        </div>
    </div>

    {{-- Progress Nominal --}}
    <div class="mt-3 text-sm text-gray-600">
        Rp {{ number_format($goal->collected_amount,0,',','.') }}
        /
        Rp {{ number_format($goal->target_amount,0,',','.') }}
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-3 gap-4 mt-5">

        <div>
            <p class="text-xs text-gray-500">
                Terkumpul
            </p>

            <p class="font-bold text-green-600">
                Rp {{ number_format($goal->collected_amount,0,',','.') }}
            </p>
        </div>

        <div>
            <p class="text-xs text-gray-500">
                Target
            </p>

            <p class="font-bold">
                Rp {{ number_format($goal->target_amount,0,',','.') }}
            </p>
        </div>

        <div>
            <p class="text-xs text-gray-500">
                Sisa
            </p>

            <p class="font-bold text-orange-600">
                Rp {{ number_format(
                    $goal->target_amount - $goal->collected_amount,
                    0, ',', '.'
                ) }}
            </p>
        </div>

    </div>

    {{-- Target Date --}}
    @if($goal->target_date)
        <div class="mt-4 text-sm text-gray-500">
            📅
            {{ \Carbon\Carbon::parse($goal->target_date)->translatedFormat('d F Y') }}
        </div>
    @endif

    {{-- Action --}}
    <div class="flex gap-2 mt-5">

        <a
            href="{{ route('goals.contribute', $goal->id) }}"
            class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm">
            Tambah Dana
        </a>

        <form
            action="{{ route('goals.destroy', $goal->id) }}"
            method="POST">

            @csrf
            @method('DELETE')

            <button
                class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-sm">
                Hapus
            </button>

        </form>

    </div>

</div>

</div>

@empty

<p class="text-gray-500">
    Belum ada goal.
</p>

@endforelse

    </div>

</div>
@endsection