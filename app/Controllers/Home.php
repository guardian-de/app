<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
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

