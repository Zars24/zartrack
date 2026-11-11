<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Container;
use App\Models\Document;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = Document::with(['order', 'container', 'uploader'])
            ->when($request->type, fn ($q, $v) => $q->where('type', $v))
            ->when($request->q, fn ($q, $v) => $q->where('title', 'like', "%$v%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.documents.index', compact('documents'));
    }

    public function create()
    {
        return view('admin.documents.form', [
            'orders' => Order::with('customer')->latest()->take(200)->get(),
            'containers' => Container::with('order')->latest()->take(200)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('documents', 'public');
        }

        $data['uploaded_by'] = auth()->id();
        $document = Document::create($data);
        \App\Models\ActivityLog::record('document.create', $document, "Dokumen {$document->title} diunggah");

        return redirect()->route('admin.documents.index')->with('success', 'Dokumen diunggah.');
    }

    public function destroy(Document $document)
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Dokumen dihapus.');
    }

    public function download(Document $document)
    {
        $user = auth()->user();
        $allowed = $user->isAdmin()
            || ($user->role === 'customer' && $document->order?->customer_id === $user->customer_id);
        abort_unless($allowed, 403);

        abort_unless(Storage::disk('public')->exists($document->file_path), 404);

        return Storage::disk('public')->download($document->file_path, $document->title.'.'.pathinfo($document->file_path, PATHINFO_EXTENSION));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'order_id' => ['nullable', 'exists:orders,id'],
            'container_id' => ['nullable', 'exists:containers,id'],
            'type' => ['required', 'in:'.implode(',', array_keys(config('zartrack.document_types')))],
            'title' => ['required', 'string', 'max:150'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);
    }
}
