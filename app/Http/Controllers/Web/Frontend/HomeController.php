<?php

namespace App\Http\Controllers\Web\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the welcome page.
     */
    public function index(): View
    {
        return view('frontend.welcome');
    }
}
