<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ManualController extends Controller
{
    public function index()
    {
        return Inertia::render('Owner/Manual/Index');
    }
}
