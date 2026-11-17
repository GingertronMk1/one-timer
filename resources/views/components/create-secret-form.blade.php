<form action="{{ route('store') }}" method="POST" class="flex flex-col gap-4" data-expiry-form>
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
                min="1"
                step="1"
                value="{{ old('expires_in', 1) }}"
                class="rounded-md border-2 border-black p-2"
            />
        </label>
        <select class="rounded-md border-2 border-black p-2" name="expires_unit" id="expires_unit">
            @foreach (\App\TimeUnit::cases() as $case)
                <option
                    value="{{ $case->value }}"
                    {{ $case->value === old('expires_unit', \App\TimeUnit::HOURS->value) ? 'selected' : '' }}
                >
                    {{ ucfirst($case->value) }}
                </option>
            @endforeach
        </select>
    </div>
    <p data-expiry-preview aria-live="polite"></p>
    <label for="recipients">
        <span class="flex flex-row items-center">
            <span class="mr-auto text-2xl">Email recipients (optional)</span>
            @error('recipients')
                <span class="text-red-500">{{ $message }}</span>
            @enderror
        </span>
        <span class="block">Enter one email address per line. Each recipient will receive their own link.</span>
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
