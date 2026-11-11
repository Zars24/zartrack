<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::with('order.customer')
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->q, fn ($q, $v) => $q->where('invoice_number', 'like', "%$v%")->orWhereHas('order.customer', fn ($c) => $c->where('name', 'like', "%$v%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        return view('admin.invoices.form', [
            'invoice' => new Invoice(),
            'orders' => Order::with('customer')->latest()->take(200)->get(),
            'selectedOrder' => $request->order_id ? Order::find($request->order_id) : null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['invoice_number'] = 'INV-'.now()->format('ymd').'-'.strtoupper(Str::random(4));
        $data['subtotal'] = $data['total'] - $data['tax'];

        $invoice = Invoice::create($data);
        \App\Models\ActivityLog::record('invoice.create', $invoice, "Invoice {$invoice->invoice_number} dibuat");

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Invoice dibuat.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['order.customer', 'order.containers', 'payments.recorder']);

        return view('admin.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        return view('admin.invoices.form', [
            'invoice' => $invoice,
            'orders' => Order::with('customer')->latest()->take(200)->get(),
            'selectedOrder' => $invoice->order,
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $this->validated($request);
        $data['subtotal'] = $data['total'] - $data['tax'];
        $invoice->update($data);

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Invoice diperbarui.');
    }

    public function destroy(Invoice $invoice)
    {
        abort_if($invoice->payments()->exists(), 422, 'Invoice memiliki pembayaran.');
        $invoice->delete();

        return redirect()->route('admin.invoices.index')->with('success', 'Invoice dihapus.');
    }

    public function storePayment(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.max(0.01, $invoice->balance)],
            'method' => ['required', 'in:transfer,cash,cheque'],
            'reference' => ['nullable', 'string', 'max:100'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $payment = $invoice->payments()->create($data + ['recorded_by' => auth()->id()]);
        \App\Models\ActivityLog::record('payment.create', $payment, "Pembayaran {$payment->amount} untuk {$invoice->invoice_number}");

        return back()->with('success', 'Pembayaran dicatat.');
    }

    public function pdf(Invoice $invoice)
    {
        $user = auth()->user();
        $allowed = $user->isAdmin()
            || ($user->role === 'customer' && $invoice->order->customer_id === $user->customer_id);
        abort_unless($allowed, 403);

        $invoice->load(['order.customer', 'order.containers', 'payments']);

        return Pdf::loadView('pdf.invoice', compact('invoice'))
            ->setPaper('a4')
            ->download("{$invoice->invoice_number}.pdf");
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'tax' => ['nullable', 'numeric', 'min:0', 'lte:total'],
            'total' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        return $data + ['tax' => 0];
    }
}