<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'A Market' }}</title>
        {{-- <link rel="stylesheet" href="{{ asset('build/assets/app-CGkTTEI-.css') }}"> <!-- Perbarui jalur CSS -->
        <script src="{{ asset('build/assets/app-CLDX0f-R.js') }}" defer></script> <!-- Perbarui jalur JavaScript --> --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="bg-slate-200 dark:bg-slate-700">
        @livewire('partials.navbar')
        <main>
            {{ $slot }}
        </main>
        @livewire('partials.footer')
        @livewireScripts
        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <x-livewire-alert::scripts />
    </body>
</html>
