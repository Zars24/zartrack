<?php

namespace App\Http\Controllers;

use App\Models\Quote;

class PublicController extends Controller
{
    public function home()
    {
        return view('public.home');
    }

    public function about()
    {
        return view('public.about');
    }

    public function services()
    {
        return view('public.services');
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function storeQuote(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'service_type' => ['required', 'in:trucking,freight,warehousing,logistics'],
            'origin' => ['required', 'string', 'max:150'],
            'destination' => ['required', 'string', 'max:150'],
            'cargo_details' => ['nullable', 'string', 'max:1000'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        Quote::create($data + ['status' => 'new']);

        \App\Models\User::whereIn('role', ['super_admin', 'ops_admin'])
            ->where('is_active', true)
            ->get()
            ->each(fn ($u) => $u->notify(new \App\Notifications\OperationalAlert(
                'Request quote baru',
                $data['name'].' · '.$data['origin'].' → '.$data['destination'],
                route('admin.quotes.index'),
            )));

        return back()->with('success', 'Terima kasih. Tim kami akan menghubungi Anda.');
    }

    public function track(\Illuminate\Http\Request $request, ?string $ref = null)
    {
        $ref = $ref ?: $request->query('ref');
        $container = null;

        if ($ref) {
            $container = \App\Models\Container::with(['order.customer', 'timelines', 'latestLocation', 'documents'])
                ->where('tracking_ref', strtoupper(trim($ref)))
                ->first();
        }

        return view('public.tracking', compact('container', 'ref'));
    }

    public function trackData(string $ref)
    {
        $container = \App\Models\Container::with(['timelines', 'latestLocation'])
            ->where('tracking_ref', strtoupper(trim($ref)))
            ->firstOrFail();

        abort_unless($container->status === 'in_transit' || $container->status === 'pickup', 404);

        return response()->json([
            'status' => $container->status,
            'location' => $container->latestLocation,
            'timeline' => $container->timelines->take(10),
        ]);
    }
}
