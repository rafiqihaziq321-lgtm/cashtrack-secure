<x-layouts::auth :title="__('CashTrack Secure - Login')">

    <div class="flex flex-col gap-6">

        <x-auth-header
            :title="__('Log masuk')"
            :description="__('Gunakan email dan kata laluan akaun anda')"
        />

        {{-- Session Status --}}
        <x-auth-session-status
            class="text-center"
            :status="session('status')"
        />

        <x-passkey-verify />

        <form
            method="POST"
            action="{{ route('login.store') }}"
            class="flex flex-col gap-6"
        >
            @csrf

            {{-- Email --}}
            <flux:input
                name="email"
                :label="__('Email login')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            {{-- Kata laluan --}}
            <div class="relative">

                <flux:input
                    name="password"
                    :label="__('Kata laluan')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Kata laluan')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link
                        class="absolute top-0 text-sm end-0"
                        :href="route('password.request')"
                        wire:navigate
                    >
                        {{ __('Lupa kata laluan?') }}
                    </flux:link>
                @endif

            </div>

            {{-- Remember Me --}}
            <flux:checkbox
                name="remember"
                :label="__('Ingat saya')"
                :checked="old('remember')"
            />

            {{-- Login --}}
            <flux:button
                variant="primary"
                type="submit"
                class="w-full"
                data-test="login-button"
            >
                {{ __('Log masuk') }}
            </flux:button>

        </form>

        <div class="text-center">
            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                Untuk pengguna yang diberi akses
            </p>
        </div>

    </div>

</x-layouts::auth>