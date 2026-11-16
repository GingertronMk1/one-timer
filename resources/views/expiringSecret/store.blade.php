@props(['expiringSecret'])
<x-app-layout>
    <input type="text" readonly value="{{ route('show', ['expiringSecret' => $expiringSecret->id]) }}" />
    <x-app-button as="a" href="{{ route('create') }}"> Make another </x-app-button>
</x-app-layout>
