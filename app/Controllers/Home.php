<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        if (session()->get('isLoggedIn')) {
            $role = session()->get('user_role');
            if ($role === 'admin') {
                return redirect()->to('/admin/contracts');
            } elseif ($role === 'operator') {
                return redirect()->to('/admin/contracts');
            } else {
                return redirect()->to('/dashboard');
            }
        }
        return view('welcome_message');
    }

    public function privacy(): string
    {
        return view('privacy');
    }

    public function terms(): string
    {
        return view('terms');
    }
}

