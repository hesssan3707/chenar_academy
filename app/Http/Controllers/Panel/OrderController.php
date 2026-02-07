<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        return view('panel.stub', ['title' => 'سفارش‌های من']);
    }

    public function show(int $order): View
    {
        return view('panel.stub', ['title' => 'جزئیات سفارش']);
    }
}
