<x-head/>
{{ print_r($errors->all(), true) }}
<form action="{{ route('store') }}" method="POST" class="flex flex-col gap-4">
    @csrf
    <textarea
        name="content"
        placeholder="Write your expiring secret here"
        rows="10"
        class="w-full border-2 border-black rounded-md p-2"
    ></textarea>
    <div class="flex flex-row gap-4 items-center">
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
    <button type="submit" class="px-4 py-2 rounded-sm bg-blue-800 text-white cursor-pointer">
        Create
    </button>
</form>
