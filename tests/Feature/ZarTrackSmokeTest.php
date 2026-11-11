<?php

namespace Tests\Feature;

use App\Models\Container;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZarTrackSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_public_pages_render(): void
    {
        foreach (['/', '/about', '/services', '/contact', '/track', '/login'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_public_tracking_and_quote(): void
    {
        $container = Container::where('status', 'in_transit')->firstOrFail();
        $this->get('/track/'.$container->tracking_ref)->assertOk()->assertSee($container->container_number);

        $this->post('/quote', [
            'name' => 'Test', 'email' => 'a@b.co', 'phone' => '0812',
            'service_type' => 'trucking', 'origin' => 'A', 'destination' => 'B',
        ])->assertRedirect();
        $this->assertDatabaseCount('quotes', 2);
    }

    public function test_role_guards(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->actingAs($this->user('driver'))->get('/admin')->assertForbidden();
        $this->actingAs($this->user('customer'))->get('/driver')->assertForbidden();
        $this->actingAs($this->user('ops_admin'))->get('/admin/users')->assertForbidden();
    }

    public function test_admin_pages_render_for_super_admin(): void
    {
        $admin = $this->user('super_admin');

        $urls = [
            '/admin', '/admin/customers', '/admin/customers/create',
            '/admin/drivers', '/admin/drivers/create',
            '/admin/trucks', '/admin/trucks/create',
            '/admin/orders', '/admin/orders/create',
            '/admin/tracking', '/admin/documents', '/admin/documents/create',
            '/admin/invoices', '/admin/invoices/create',
            '/admin/reports/finance', '/admin/reports/operations',
            '/admin/quotes', '/admin/users', '/admin/users/create',
            '/admin/activity-logs', '/notifications', '/profile',
        ];

        foreach ($urls as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }

        $order = Order::firstOrFail();
        $container = Container::firstOrFail();
        $invoice = Invoice::firstOrFail();

        $this->actingAs($admin)->get('/admin/orders/'.$order->id)->assertOk();
        $this->actingAs($admin)->get('/admin/tracking/'.$container->id)->assertOk();
        $this->actingAs($admin)->get('/admin/invoices/'.$invoice->id)->assertOk();
        $this->actingAs($admin)->get('/admin/invoices/'.$invoice->id.'/pdf')->assertOk();
    }

    public function test_full_shipment_flow_and_payment(): void
    {
        $admin = $this->user('ops_admin');
        $customer = Customer::firstOrFail();
        $driver = Driver::firstOrFail();
        $truck = Truck::firstOrFail();

        $this->actingAs($admin)->post('/admin/orders', [
            'customer_id' => $customer->id,
            'service_type' => 'trucking',
            'origin' => 'Tanjung Priok',
            'destination' => 'Cikarang',
            'total_price' => 5000000,
        ])->assertRedirect();

        $order = Order::latest('id')->firstOrFail();

        $this->actingAs($admin)->post("/admin/orders/{$order->id}/containers", [
            'container_number' => 'TEST1234567', 'size' => '20ft', 'weight' => 12000,
        ])->assertRedirect();

        $container = Container::latest('id')->firstOrFail();

        $this->actingAs($admin)->post("/admin/containers/{$container->id}/assign", [
            'driver_id' => $driver->id, 'truck_id' => $truck->id,
        ])->assertRedirect();

        $container->refresh();
        $this->assertSame('assigned', $container->status);
        $this->assertSame('on_duty', $driver->fresh()->status);

        $this->actingAs($admin)->post("/admin/containers/{$container->id}/transition", [
            'status' => 'pickup',
        ])->assertRedirect();
        $this->assertNotNull($container->fresh()->pickup_at);

        $this->actingAs($admin)->post("/admin/containers/{$container->id}/transition", [
            'status' => 'in_transit',
        ])->assertRedirect();
        $this->assertSame('in_transit', $order->fresh()->status);

        // invalid transition rejected
        $this->actingAs($admin)->post("/admin/containers/{$container->id}/transition", ['status' => 'pending'])
            ->assertSessionHasErrors('status');

        $this->actingAs($admin)->post("/admin/containers/{$container->id}/transition", ['status' => 'delivered'])->assertRedirect();
        $this->assertSame('completed', $driver->fresh()->status === 'available' ? 'completed' : 'pending');
        $this->assertSame('available', $driver->fresh()->status);

        // driver GPS only while travelling
        $driverUser = $driver->user;
        $this->actingAs($driverUser)->postJson("/driver/tasks/{$container->id}/ping", ['lat' => -6.1, 'lng' => 106.8])
            ->assertStatus(422);

        // finance payment
        $finance = $this->user('finance');
        $this->actingAs($finance)->post('/admin/invoices', [
            'order_id' => $order->id, 'issue_date' => now()->toDateString(),
            'due_date' => now()->addWeek()->toDateString(), 'tax' => 0, 'total' => 5000000,
        ])->assertRedirect();

        $invoice = Invoice::latest('id')->firstOrFail();

        $this->actingAs($finance)->post("/admin/invoices/{$invoice->id}/payments", [
            'amount' => 5000000, 'method' => 'transfer', 'paid_at' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertEquals(0, $invoice->fresh()->balance);
    }

    public function test_customer_portal_isolation(): void
    {
        $customerUser = $this->user('customer');
        $ownOrder = Order::where('customer_id', $customerUser->customer_id)->firstOrFail();
        $otherOrder = Order::where('customer_id', '!=', $customerUser->customer_id)->firstOrFail();

        $this->actingAs($customerUser)->get('/customer')->assertOk();
        $this->actingAs($customerUser)->get('/customer/orders/'.$ownOrder->id)->assertOk();
        $this->actingAs($customerUser)->get('/customer/orders/'.$otherOrder->id)->assertForbidden();
        $this->actingAs($customerUser)->get('/customer/invoices')->assertOk();
        $this->actingAs($customerUser)->get('/customer/documents')->assertOk();
    }

    public function test_driver_cannot_open_other_driver_task(): void
    {
        $container = Container::whereNotNull('driver_id')->firstOrFail();
        $other = Driver::where('id', '!=', $container->driver_id)->firstOrFail();

        $this->actingAs($other->user)->get('/driver/tasks/'.$container->id)->assertForbidden();
        $this->actingAs($container->driver->user)->get('/driver/tasks/'.$container->id)->assertOk();
    }

    public function test_each_role_lands_on_its_own_home(): void
    {
        $expected = [
            'super_admin' => '/admin',
            'ops_admin' => '/admin',
            'doc_admin' => '/admin',
            'finance' => '/admin',
            'driver' => '/driver',
            'customer' => '/customer',
        ];

        foreach ($expected as $role => $home) {
            $this->actingAs($this->user($role))->get($home)->assertOk();
        }

        // doc_admin gets read-only invoice access, no create
        $doc = $this->user('doc_admin');
        $this->actingAs($doc)->get('/admin/invoices')->assertOk();
        $this->actingAs($doc)->get('/admin/invoices/create')->assertForbidden();

        // finance has no order write access
        $this->actingAs($this->user('finance'))->get('/admin/orders')->assertForbidden();
        $this->actingAs($this->user('finance'))->get('/admin/reports/finance')->assertOk();
    }

    public function test_notifications_created_on_status_change(): void
    {
        $customerUser = $this->user('customer');
        $container = Container::whereHas('order', fn ($q) => $q->where('customer_id', $customerUser->customer_id))
            ->firstOrFail();
        $ops = $this->user('ops_admin');

        $next = config('zartrack.transitions.'.$container->status)[0] ?? null;
        $this->assertNotNull($next);

        $this->actingAs($ops)->post("/admin/containers/{$container->id}/transition", ['status' => $next])
            ->assertRedirect();

        $this->assertGreaterThan(0, $customerUser->notifications()->count());
        $this->actingAs($customerUser)->get('/notifications')->assertOk();
    }

    private function user(string $role): User
    {
        return User::where('role', $role)->firstOrFail();
    }
}
