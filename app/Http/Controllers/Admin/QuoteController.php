<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $quotes = Quote::query()
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.quotes.index', compact('quotes'));
    }

    public function update(Request $request, Quote $quote)
    {
        $data = $request->validate(['status' => ['required', 'in:new,contacted,quoted,won,lost']]);
        $quote->update($data);

        return back()->with('success', 'Status quote diperbarui.');
    }
}