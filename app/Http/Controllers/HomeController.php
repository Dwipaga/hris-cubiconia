<?php
// app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Show the application home page.
     */
    public function index(): View
    {
        $data = [
            'title' => 'Home Page'
        ];
        
        return view('home', compact('data'));
    }
}


