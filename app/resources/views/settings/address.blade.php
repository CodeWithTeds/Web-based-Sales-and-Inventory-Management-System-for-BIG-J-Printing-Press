<x-app-layout>
    <section class="w-full">
        @include('partials.settings-heading')

        <x-settings.layout :heading="__('Address')" :subheading="__('Set your address using PSGC data')">
            <livewire:settings.address-form />
        </x-settings.layout>
    </section>
</x-app-layout>