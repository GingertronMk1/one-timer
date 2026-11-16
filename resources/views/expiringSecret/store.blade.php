@props(['expiringSecret'])
<x-app-layout>
    {{ route('show', ['expiringSecret' => $expiringSecret->id]) }}
    <a href="{{ route('create') }}">Make another</a>
</x-app-layout>
