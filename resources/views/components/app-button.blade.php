@props(['as' => 'button'])
@php($classes = 'px-4 py-2 rounded-sm bg-blue-500 text-center')
@if($as === 'a')
    <a class="{{ $classes }}" {{ $attributes }}>
        {{ $slot }}
    </a>
@else
    <button class="{{ $classes }}" {{ $attributes }}>
        {{ $slot }}
    </button>
@endif
