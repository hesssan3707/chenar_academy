<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class FakePaymentGatewayController extends Controller
{
    /**
     * Show the fake payment gateway simulation page
     */
    public function show(Request $request): View
    {
        $amount = $request->query('amount');
        $authority = $request->query('authority');
        $callback = $request->query('callback');

        abort_if(!$amount || !$authority || !$callback, 400, 'Missing required payment parameters.');

        return view('commerce.checkout.fake-gateway', [
            'amount' => $amount,
            'authority' => $authority,
            'callback' => urldecode($callback),
        ]);
    }

    /**
     * Process the simulated payment result
     */
    public function process(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'authority' => ['required', 'string'],
            'callback' => ['required', 'url'],
            'status' => ['required', 'string', 'in:OK,NOK'],
        ]);

        $callbackUrl = $validated['callback'];
        
        // Append Authority and Status query parameters
        $separator = str_contains($callbackUrl, '?') ? '&' : '?';
        $redirectUrl = $callbackUrl . $separator . http_build_query([
            'Authority' => $validated['authority'],
            'Status' => $validated['status'],
        ]);

        return redirect()->away($redirectUrl);
    }
}
