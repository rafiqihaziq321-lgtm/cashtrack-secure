<x-layouts::auth :title="__('CashTrack Secure - Login')">

    <div class="flex flex-col gap-6">

        <x-auth-header
            :title="__('Log In')"
            :description="__('Enter your email and password')"
        />

        {{-- Session Status --}}
        <x-auth-session-status
            class="text-center"
            :status="session('status')"
        />

        <form
            method="POST"
            action="{{ route('login.store') }}"
            class="flex flex-col gap-6"
        >
            @csrf

            {{-- Email --}}
            <flux:input
                name="email"
                :label="__('Email')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            {{-- Password --}}
            <div class="relative">

                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link
                        class="absolute top-0 text-sm end-0"
                        :href="route('password.request')"
                        wire:navigate
                    >
                        {{ __('Forgot Password?') }}
                    </flux:link>
                @endif

            </div>

            {{-- Remember Me --}}
            <flux:checkbox
                name="remember"
                :label="__('Remember me')"
                :checked="old('remember')"
            />

            {{-- Login --}}
            <flux:button
                variant="primary"
                type="submit"
                class="w-full"
                data-test="login-button"
            >
                {{ __('Log In') }}
            </flux:button>

        </form>

                </form>

        {{-- Passkey Login --}}
        <div class="relative flex items-center justify-center">
            <div class="absolute inset-x-0 top-1/2 border-t border-zinc-200 dark:border-zinc-700"></div>

            <span class="relative bg-white px-3 text-xs text-zinc-500 dark:bg-zinc-900 dark:text-zinc-400">
                {{ __('or') }}
            </span>
        </div>

        <x-passkey-verify />

        <div class="text-center">
            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                Sign in to manage your sales and stock.
            </p>
        </div>

    </div>

</x-layouts::auth>
