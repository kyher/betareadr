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

Artisan::command('admin:create {email}', function (string $email) {
    $email = Str::lower($email);
    $name = text('Name', required: true);
    $password = password('Password', required: true);
    $confirmation = password('Confirm password', required: true);

    $attributes = Validator::make([
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'password_confirmation' => $confirmation,
    ], [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', Rule::unique(Administrator::class)],
        'password' => ['required', 'string', Password::default(), 'confirmed'],
    ])->validate();

    Administrator::create($attributes);
    $this->info("Administrator {$email} created.");
})->purpose('Create an administrator');

Artisan::command('admin:reset-password {email}', function (string $email) {
    $administrator = Administrator::where('email', Str::lower($email))->firstOrFail();
    $password = password('Password', required: true);
    $confirmation = password('Confirm password', required: true);

    Validator::make([
        'password' => $password,
        'password_confirmation' => $confirmation,
    ], [
        'password' => ['required', 'string', Password::default(), 'confirmed'],
    ])->validate();

    $administrator->update(['password' => $password]);
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
