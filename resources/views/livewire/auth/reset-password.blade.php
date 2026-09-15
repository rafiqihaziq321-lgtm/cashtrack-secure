<x-layouts::auth :title="__('Tetapkan semula kata laluan')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Tetapkan semula kata laluan')" :description="__('Masukkan kata laluan baharu anda di bawah')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Email login -->
            <flux:input
                name="email"
                value="{{ request('email') }}"
                :label="__('Email')"
                type="email"
                required
                autocomplete="email"
            />

            <!-- Kata laluan -->
            <flux:input
                name="password"
                :label="__('Kata laluan')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Kata laluan')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirm Kata laluan -->
            <flux:input
                name="password_confirmation"
                :label="__('Ulang kata laluan')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Ulang kata laluan')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="reset-password-button">
                    {{ __('Tetapkan semula kata laluan') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>
