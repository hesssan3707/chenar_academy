<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(): View
    {
        return view('panel.stub', ['title' => 'تیکت‌های من']);
    }

    public function create(): View
    {
        return view('panel.stub', ['title' => 'ایجاد تیکت']);
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('panel.tickets.index');
    }

    public function show(int $ticket): View
    {
        return view('panel.stub', ['title' => 'نمایش تیکت']);
    }

    public function edit(int $ticket): View
    {
        return view('panel.stub', ['title' => 'ویرایش تیکت']);
    }

    public function update(Request $request, int $ticket): RedirectResponse
    {
        return redirect()->route('panel.tickets.show', $ticket);
    }

    public function destroy(int $ticket): RedirectResponse
    {
        return redirect()->route('panel.tickets.index');
    }
}
