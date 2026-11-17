@props(['expiringSecret'])
<x-app-layout>
    @if ($recipientCount > 0)
        <p>Secret sent to {{ $recipientCount }} {{ $recipientCount === 1 ? 'user' : 'users' }}</p>
    @else
        <input type="text" readonly value="{{ route('show', ['expiringSecret' => $expiringSecret->id]) }}" />
    @endif
    <x-app-button as="a" href="{{ route('create') }}"> Make another </x-app-button>
</x-app-layout>
