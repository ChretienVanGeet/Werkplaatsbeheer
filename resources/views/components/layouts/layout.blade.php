<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-950 relative">
        @include('components.layouts.sidebar')

        {{ $slot }}

        @livewireScripts
        @vite(['resources/js/app.js'])
        @fluxScripts

        @persist('toast')
            <flux:toast position="bottom right" />
        @endpersist
    </body>
</html>
