<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    protected function user(): User
    {
        return Auth::guard('web')->user();
    }
}
