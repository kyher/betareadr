<?php

use App\Models\Administrator;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$promptForPassword = function (): string {
    $attributes = Validator::make([
        'password' => password('Password', required: true),
        'password_confirmation' => password('Confirm password', required: true),
    ], [
        'password' => ['required', 'string', Password::default(), 'confirmed'],
    ])->validate();

    return $attributes['password'];
};

Artisan::command('admin:create {email}', function (string $email) use ($promptForPassword) {
    $email = Str::lower($email);
    $name = text('Name', required: true);

    $attributes = Validator::make([
        'name' => $name,
        'email' => $email,
    ], [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', Rule::unique(Administrator::class)],
    ])->validate();

    Administrator::create([...$attributes, 'password' => $promptForPassword()]);
    $this->info("Administrator {$email} created.");
})->purpose('Create an administrator');

Artisan::command('admin:reset-password {email}', function (string $email) use ($promptForPassword) {
    $administrator = Administrator::where('email', Str::lower($email))->firstOrFail();
    $administrator->update(['password' => $promptForPassword()]);
    $this->info("Administrator {$administrator->email} password reset.");
})->purpose('Reset an administrator password');

Artisan::command('admin:delete {email}', function (string $email) {
    $administrator = Administrator::where('email', Str::lower($email))->firstOrFail();

    if (! confirm("Delete administrator {$administrator->email}?")) {
        return;
    }

    $administrator->delete();
    $this->info("Administrator {$administrator->email} deleted.");
})->purpose('Delete an administrator');
