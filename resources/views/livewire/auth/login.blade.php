<x-layouts::auth :title="__('CashTrack Secure - Login')">

    <div class="flex flex-col gap-6">

        <x-auth-header
            :title="__('Welcome Back')"
            :description="__('Enter your account credentials to continue')"
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
                :label="__('Email Address')"
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
                        {{ __('Forgot password?') }}
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
                {{ __('Login to CashTrack Secure') }}
            </flux:button>

        </form>

        <div class="text-center">
            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                Authorized users only
            </p>
        </div>

    </div>

</x-layouts::auth>