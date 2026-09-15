<x-layouts::auth :title="__('Pemulihan kata laluan')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Pemulihan kata laluan')" :description="__('Masukkan email akaun untuk memohon pautan pemulihan')" />

        <p class="text-sm text-zinc-500">Dalam demonstrasi tempatan, mailer log hanya merekod pautan dan tidak menghantar email sebenar. Hubungi pemasang untuk menggunakan pautan tersebut. Untuk hosting, SMTP perlu dikonfigurasi dan diuji.</p><!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email login -->
            <flux:input
                name="email"
                :label="__('Email login')"
                type="email"
                required
                autofocus
                placeholder="email@example.com"
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="email-password-reset-link-button">
                {{ __('Mohon pautan pemulihan') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
            <span>{{ __('Kembali ke') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('log masuk') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
