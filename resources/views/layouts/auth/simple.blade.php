<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>

    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">

        <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">

            <div class="flex w-full max-w-sm flex-col gap-6">

                {{-- CashTrack Secure Brand --}}
                <a
                    href="{{ route('home') }}"
                    class="flex flex-col items-center text-center"
                    wire:navigate
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-zinc-900 text-lg font-bold text-white shadow-sm dark:bg-white dark:text-zinc-900">
                        CT
                    </div>

                    <span class="mt-3 text-lg font-bold tracking-tight text-zinc-900 dark:text-white">
                        CashTrack Secure
                    </span>
                </a>

                {{-- Authentication Content --}}
                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>

            </div>

        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts

    </body>
</html>