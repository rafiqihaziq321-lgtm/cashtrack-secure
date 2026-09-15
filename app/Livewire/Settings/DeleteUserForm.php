<?php

namespace App\Livewire\Settings;

use App\Concerns\PasswordValidationRules;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class DeleteUserForm extends Component
{
    use PasswordValidationRules;

    public string $password = '';

    /**
     * Preserve account references used by the audit history.
     */
    public function deleteUser(): void
    {
        $this->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        throw ValidationException::withMessages([
            'account' => 'Akaun dikekalkan untuk sejarah audit. Owner boleh menyahaktif akaun Cashier.',
        ]);
    }
}
