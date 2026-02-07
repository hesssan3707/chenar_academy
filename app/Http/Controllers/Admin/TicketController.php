<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(): View
    {
        return view('admin.stub', ['title' => 'تیکت‌ها']);
    }

    public function show(int $ticket): View
    {
        return view('admin.stub', ['title' => 'نمایش تیکت']);
    }

    public function update(Request $request, int $ticket): RedirectResponse
    {
        return redirect()->route('admin.tickets.show', $ticket);
    }
}
