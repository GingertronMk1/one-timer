<x-app-layout>
    <form action="{{ route('store') }}" method="POST" class="flex flex-col gap-4">
        @csrf
        <label for="content">
            <span class="flex flex-row items-center">
                <span class="text-2xl mr-auto">Content</span>
                @error('content')
                    <span class="text-red-500">{{ $message }}</span>
                @enderror
            </span>
            <textarea
                name="content"
                id="content"
                placeholder="Write your expiring secret here"
                rows="10"
                class="w-full border-2 border-black rounded-md p-2"
            ></textarea>
        </label>
        <div class="flex flex-row gap-4 items-center">
            <label for="expires_in">
            <span>Expires in:</span>
                <input
                    type="number"
                    name="expires_in"
                    id="expires_in"
                    min="0"
                    step="1"
                    value="1"
                    class="border-2 border-black rounded-md p-2"
                >
            </label>
            <select
                class="border-2 border-black rounded-md p-2"
                name="expires_unit"
                id="expires_unit"
            >
                @foreach(\App\TimeUnit::cases() as $case)
                    <option
                        value="{{ $case->value }}"
                        {{ $case->value === \App\TimeUnit::HOURS->value ? 'selected' : '' }}
                    >
                        {{ ucfirst($case->value) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-row gap-2 items-center">
            <span>Viewable</span>
            <input
                type="number"
                name="remaining_views"
                id="remaining_views"
                min="0"
                step="1"
                class="border-2 border-black rounded-md p-2 w-16"
                value="1"
            >
            <span>times</span>
        </div>
        <x-app-button type="submit">
            Create
        </x-app-button>
    </form>

</x-app-layout>
