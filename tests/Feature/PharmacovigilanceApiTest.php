<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Medication;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PharmacovigilanceApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        try {
            $this->artisan('migrate:fresh')->run();
        } catch (\Throwable $exception) {
            $this->markTestSkipped('Database not available for feature tests: ' . $exception->getMessage());
        }
    }

    public function test_login_endpoint_returns_jwt_token(): void
    {
        User::query()->create([
            'username' => 'pv_admin',
            'email' => 'pv_admin@example.com',
            'full_name' => 'PV Admin',
            'password' => Hash::make('123456'),
            'user_status_id' => 1,
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'pv_admin',
            'password' => '123456',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.token_type', 'bearer')
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
                'data' => ['access_token', 'expires_in', 'user'],
            ]);
    }

    public function test_protected_orders_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/orders?lot=951357')->assertStatus(401);
    }

    public function test_protected_orders_endpoint_requires_admin_role(): void
    {
        $roleClient = Role::findOrCreate('client', 'api');

        $user = User::query()->create([
            'username' => 'non_admin_user',
            'email' => 'nonadmin@example.com',
            'full_name' => 'Non Admin User',
            'password' => Hash::make('123456'),
            'user_status_id' => 1,
        ]);
        $user->assignRole($roleClient);

        $loginResponse = $this->postJson('/api/login', [
            'username' => 'non_admin_user',
            'password' => '123456',
        ]);

        $token = $loginResponse->json('data.access_token');

        $this->getJson('/api/orders?lot=951357', [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(403);
    }

    public function test_alert_send_prevents_duplicates_for_same_order_and_lot(): void
    {
        [$token, $order] = $this->buildAuthenticatedOrderContext();

        $headers = ['Authorization' => "Bearer {$token}"];

        $firstSend = $this->postJson('/api/alerts/send', [
            'order_id' => $order->id,
            'lot_number' => '951357',
        ], $headers);

        $firstSend->assertOk()->assertJsonPath('data.status', 'sent');

        $secondSend = $this->postJson('/api/alerts/send', [
            'order_id' => $order->id,
            'lot_number' => '951357',
        ], $headers);

        $secondSend->assertStatus(409)
            ->assertJsonPath('status', false)
            ->assertJsonPath('errors.alert.status', 'skipped_duplicate');
    }

    public function test_bulk_alert_send_returns_summary_counts(): void
    {
        [$token, $order] = $this->buildAuthenticatedOrderContext();

        $customerTwo = Customer::query()->create([
            'name' => 'Second Customer',
            'email' => 'second.customer@example.com',
            'phone' => '+56977777777',
        ]);

        $orderTwo = Order::query()->create([
            'customer_id' => $customerTwo->id,
            'purchase_date' => now()->subDays(1),
        ]);

        $medicationTwo = Medication::query()->create([
            'name' => 'Loratadina 10mg',
            'lot_number' => '951357',
            'description' => 'Antihistaminico',
        ]);

        OrderItem::query()->create([
            'order_id' => $orderTwo->id,
            'medication_id' => $medicationTwo->id,
            'quantity' => 1,
        ]);

        $headers = ['Authorization' => "Bearer {$token}"];

        $response = $this->postJson('/api/alerts/send-bulk', [
            'order_ids' => [$order->id, $orderTwo->id],
            'lot_number' => '951357',
        ], $headers);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.total', 2)
            ->assertJsonPath('data.summary.sent', 2)
            ->assertJsonPath('data.summary.failed', 0);
    }

    public function test_orders_csv_export_endpoint_returns_csv_file(): void
    {
        [$token] = $this->buildAuthenticatedOrderContext();

        $response = $this->get('/api/orders/export/csv?lot=951357', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    private function buildAuthenticatedOrderContext(): array
    {
        $roleAdmin = Role::findOrCreate('admin', 'api');

        $user = User::query()->create([
            'username' => 'pv_user',
            'email' => 'pv_user@example.com',
            'full_name' => 'PV User',
            'password' => Hash::make('123456'),
            'user_status_id' => 1,
        ]);
        $user->assignRole($roleAdmin);

        $customer = Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'test.customer@example.com',
            'phone' => '+56966666666',
        ]);

        $order = Order::query()->create([
            'customer_id' => $customer->id,
            'purchase_date' => now()->subDays(3),
        ]);

        $medication = Medication::query()->create([
            'name' => 'Ibuprofeno 400mg',
            'lot_number' => '951357',
            'description' => 'Antiinflamatorio',
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'medication_id' => $medication->id,
            'quantity' => 2,
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'username' => $user->username,
            'password' => '123456',
        ]);

        $token = $loginResponse->json('data.access_token');

        return [$token, $order];
    }
}
