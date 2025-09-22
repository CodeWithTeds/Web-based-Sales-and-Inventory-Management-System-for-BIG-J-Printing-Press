@if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isStaff()))
<x-layouts.app.sidebar-admin :title="$title ?? null">
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar-admin>
@else
<x-layouts.app.sidebar-client :title="$title ?? null">
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar-client>
@endif
