<div class="w-full">
    <form wire:submit.prevent="save" class="my-6 w-full space-y-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Region') }}</label>
                <select wire:model.live="region_code" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                    <option value="">-- {{ __('Select Region') }} --</option>
                    @foreach($regions as $r)
                        <option value="{{ $r['code'] }}">{{ $r['name'] }}</option>
                    @endforeach
                </select>
                @error('region_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Province') }}</label>
                <select wire:model.live="province_code" @disabled(empty($provinces)) wire:key="province-select-{{ $region_code }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                    <option value="">-- {{ __('Select Province') }} --</option>
                    @foreach($provinces as $p)
                        <option value="{{ $p['code'] }}">{{ $p['name'] }}</option>
                    @endforeach
                </select>
                @error('province_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
        
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('City/Municipality') }}</label>
                <select wire:model.live="city_code" @disabled(empty($cities)) wire:key="city-select-{{ $province_code }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                    <option value="">-- {{ __('Select City/Municipality') }} --</option>
                    @foreach($cities as $c)
                        <option value="{{ $c['code'] }}">{{ $c['name'] }}</option>
                    @endforeach
                </select>
                @error('city_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Barangay') }}</label>
                <select wire:model.live="barangay_code" @disabled(empty($barangays)) wire:key="barangay-select-{{ $city_code }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                    <option value="">-- {{ __('Select Barangay') }} --</option>
                    @foreach($barangays as $b)
                        <option value="{{ $b['code'] }}">{{ $b['name'] }}</option>
                    @endforeach
                </select>
                @error('barangay_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Exact Address (Optional)') }}</label>
            <input type="text" wire:model="exact_address" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="{{ __('House No., Street, Subdivision') }}">
            @error('exact_address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-4">
            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="update-address-button">
                    {{ __('Save') }}
                </flux:button>
            </div>

            @if (session('status') === 'address-updated')
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</div>