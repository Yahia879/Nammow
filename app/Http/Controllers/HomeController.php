<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            return redirect('/super-admin/dashboard');
        }

        if ($user->hasRole('client')) {
            return redirect('/client/dashboard');
        }

        if ($user->hasRole('company')) {
            return redirect('/company/dashboard');
        }

        if ($user->hasRole('employee')) {
            return redirect('/employee/dashboard');
        }

        // Default fallback if roles aren't assigned yet or for existing roles
        return redirect('/dashboard');
    }
}
