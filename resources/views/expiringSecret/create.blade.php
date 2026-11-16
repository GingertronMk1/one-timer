<x-app-layout>
    <form action="{{ route('store') }}" method="POST" class="flex flex-col gap-4">
        @csrf
        <label for="content">
            <span class="flex flex-row items-center">
                <span class="mr-auto text-2xl">Content</span>
                @error('content')
                    <span class="text-red-500">{{ $message }}</span>
                @enderror
            </span>
            <textarea
                name="content"
                id="content"
                placeholder="Write your expiring secret here"
                rows="10"
                class="w-full rounded-md border-2 border-black p-2"
            >{{ old('content') }}</textarea>
        </label>
        <div class="flex flex-row items-center gap-4">
            <label for="expires_in">
                <span>Expires in:</span>
                <input
                    type="number"
                    name="expires_in"
                    id="expires_in"
                    min="0"
                    step="1"
                    value="1"
                    class="rounded-md border-2 border-black p-2"
                />
            </label>
            <select class="rounded-md border-2 border-black p-2" name="expires_unit" id="expires_unit">
                @foreach (\App\TimeUnit::cases() as $case)
                    <option
                        value="{{ $case->value }}"
                        {{ $case->value === \App\TimeUnit::HOURS->value ? 'selected' : '' }}
                    >
                        {{ ucfirst($case->value) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-row items-center gap-2">
            <span>Viewable</span>
            <input
                type="number"
                name="remaining_views"
                id="remaining_views"
                min="0"
                step="1"
                class="w-16 rounded-md border-2 border-black p-2"
                value="1"
            />
            <span>times</span>
        </div>
        <label for="recipients">
            <span class="flex flex-row items-center">
                <span class="mr-auto text-2xl">Email recipients (optional)</span>
                @error('recipients')
                    <span class="text-red-500">{{ $message }}</span>
                @enderror
            </span>
            <span class="block"
                >Enter one email address per line. Each recipient will receive a link to the secret and share its view
                limit.</span>
            <textarea
                name="recipients"
                id="recipients"
                rows="4"
                placeholder="person@example.com"
                class="w-full rounded-md border-2 border-black p-2"
            >{{ old('recipients') }}</textarea>
        </label>
        <x-app-button type="submit"> Create </x-app-button>
    </form>
</x-app-layout>
