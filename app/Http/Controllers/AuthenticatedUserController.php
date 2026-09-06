<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

abstract class AuthenticatedUserController extends Controller
{
    protected function user(): User
    {
        return Auth::guard(User::AUTH_GUARD)->user();
    }
}
