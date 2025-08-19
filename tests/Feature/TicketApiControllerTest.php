<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Plate;
use App\Models\Ticket;
use App\Models\ParkingSession;
use App\Models\ParkingRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class TicketApiControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $attendant;
    protected User $admin;
    protected ParkingRate $parkingRate;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->attendant = User::factory()->create();
        $this->attendant->assignRole('attendant');

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        // Create parking rate
        $this->parkingRate = ParkingRate::factory()->create([
            'rate_amount' => 50.00,
            'time_unit' => 'hour',
            'grace_period_minutes' => 15,
        ]);
    }

    /** @test */
    public function it_returns_auto_generated_tickets_for_authenticated_attendant()
    {
        // Create plates
        $plate1 = Plate::factory()->create(['number' => 'ABC123']);
        $plate2 = Plate::factory()->create(['number' => 'XYZ789']);

        // Create parking sessions (which will auto-generate tickets via ParkingSessionObserver)
        $session1 = ParkingSession::factory()->create([
            'plate_number' => $plate1->number,
            'created_by' => $this->attendant->id,
            'parking_rate_id' => $this->parkingRate->id,
            'start_time' => now()->subHours(2),
        ]);

        $session2 = ParkingSession::factory()->create([
            'plate_number' => $plate2->number,
            'created_by' => $this->attendant->id,
            'parking_rate_id' => $this->parkingRate->id,
            'start_time' => now()->subHour(),
        ]);

        // Create another session by different user (should not appear in results)
        $session3 = ParkingSession::factory()->create([
            'plate_number' => 'OTHER123',
            'created_by' => $this->admin->id,
            'parking_rate_id' => $this->parkingRate->id,
            'start_time' => now()->subMinutes(30),
        ]);

        Sanctum::actingAs($this->attendant);

        $response = $this->getJson('/api/tickets');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'ticket_number',
                        'plate_number',
                        'time_in',
                        'formatted_time_in',
                        'rate',
                        'formatted_rate',
                        'currency',
                        'parking_slot',
                        'is_printed',
                        'barcode',
                        'qr_data',
                        'created_at',
                        'updated_at',
                        'parking_session' => [
                            'id',
                            'start_time',
                            'end_time',
                            'duration_minutes',
                            'amount_paid',
                            'is_active',
                        ],
                        'creator' => [
                            'id',
                            'name',
                            'email',
                        ],
                        'parking_rate' => [
                            'id',
                            'name',
                            'rate_amount',
                            'formatted_rate',
                            'currency',
                            'time_unit',
                            'grace_period_minutes',
                        ],
                    ]
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'from',
                    'to',
                    'has_more_pages',
                ],
                'filters_applied',
                'currency_info' => [
                    'code',
                    'symbol',
                    'name',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Tickets retrieved successfully',
                'currency_info' => [
                    'code' => 'PHP',
                    'symbol' => '₱',
                    'name' => 'Philippine Peso',
                ],
            ]);

        // Verify only tickets from the authenticated attendant are returned
        $tickets = $response->json('data');
        $this->assertCount(2, $tickets);

        $plateNumbers = collect($tickets)->pluck('plate_number')->toArray();
        $this->assertContains('ABC123', $plateNumbers);
        $this->assertContains('XYZ789', $plateNumbers);
        $this->assertNotContains('OTHER123', $plateNumbers);
    }

    /** @test */
    public function it_filters_tickets_by_plate_number()
    {
        $plate1 = Plate::factory()->create(['number' => 'ABC123']);
        $plate2 = Plate::factory()->create(['number' => 'XYZ789']);

        ParkingSession::factory()->create([
            'plate_number' => $plate1->number,
            'created_by' => $this->attendant->id,
            'parking_rate_id' => $this->parkingRate->id,
        ]);

        ParkingSession::factory()->create([
            'plate_number' => $plate2->number,
            'created_by' => $this->attendant->id,
            'parking_rate_id' => $this->parkingRate->id,
        ]);

        Sanctum::actingAs($this->attendant);

        $response = $this->getJson('/api/tickets?plate_number=ABC');

        $response->assertStatus(200);

        $tickets = $response->json('data');
        $this->assertCount(1, $tickets);
        $this->assertEquals('ABC123', $tickets[0]['plate_number']);
    }

    /** @test */
    public function it_filters_tickets_by_print_status()
    {
        $plate = Plate::factory()->create(['number' => 'ABC123']);

        $session = ParkingSession::factory()->create([
            'plate_number' => $plate->number,
            'created_by' => $this->attendant->id,
            'parking_rate_id' => $this->parkingRate->id,
        ]);

        // Mark one ticket as printed
        $ticket = $session->ticket;
        $ticket->markAsPrinted();

        Sanctum::actingAs($this->attendant);

        // Test printed filter
        $response = $this->getJson('/api/tickets?print_status=printed');
        $response->assertStatus(200);
        $tickets = $response->json('data');
        $this->assertCount(1, $tickets);
        $this->assertTrue($tickets[0]['is_printed']);

        // Test unprinted filter
        $response = $this->getJson('/api/tickets?print_status=unprinted');
        $response->assertStatus(200);
        $tickets = $response->json('data');
        $this->assertCount(0, $tickets);
    }

    /** @test */
    public function it_filters_tickets_by_date_range()
    {
        $plate = Plate::factory()->create(['number' => 'ABC123']);

        // Create session from yesterday
        ParkingSession::factory()->create([
            'plate_number' => $plate->number,
            'created_by' => $this->attendant->id,
            'parking_rate_id' => $this->parkingRate->id,
            'created_at' => now()->subDay(),
        ]);

        // Create session from today
        ParkingSession::factory()->create([
            'plate_number' => $plate->number,
            'created_by' => $this->attendant->id,
            'parking_rate_id' => $this->parkingRate->id,
            'created_at' => now(),
        ]);

        Sanctum::actingAs($this->attendant);

        $response = $this->getJson('/api/tickets?date_from=' . now()->format('Y-m-d'));
        $response->assertStatus(200);

        $tickets = $response->json('data');
        $this->assertCount(1, $tickets);
    }

    /** @test */
    public function it_sorts_tickets_correctly()
    {
        $plate = Plate::factory()->create(['number' => 'ABC123']);

        // Create sessions with different creation times
        $session1 = ParkingSession::factory()->create([
            'plate_number' => $plate->number,
            'created_by' => $this->attendant->id,
            'parking_rate_id' => $this->parkingRate->id,
            'created_at' => now()->subHour(),
        ]);

        $session2 = ParkingSession::factory()->create([
            'plate_number' => $plate->number,
            'created_by' => $this->attendant->id,
            'parking_rate_id' => $this->parkingRate->id,
            'created_at' => now(),
        ]);

        Sanctum::actingAs($this->attendant);

        // Test ascending sort
        $response = $this->getJson('/api/tickets?sort_direction=asc');
        $response->assertStatus(200);

        $tickets = $response->json('data');
        $this->assertCount(2, $tickets);
        $this->assertTrue(
            strtotime($tickets[0]['created_at']) <= strtotime($tickets[1]['created_at'])
        );
    }

    /** @test */
    public function it_returns_ticket_statistics()
    {
        $plate = Plate::factory()->create(['number' => 'ABC123']);

        // Create multiple sessions
        ParkingSession::factory()->count(5)->create([
            'plate_number' => $plate->number,
            'created_by' => $this->attendant->id,
            'parking_rate_id' => $this->parkingRate->id,
        ]);

        Sanctum::actingAs($this->attendant);

        $response = $this->getJson('/api/tickets/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'total_tickets',
                    'printed_tickets',
                    'unprinted_tickets',
                    'today_tickets',
                    'this_month_tickets',
                    'this_week_tickets',
                    'total_revenue' => [
                        'amount',
                        'formatted',
                        'currency',
                    ],
                    'average_rate' => [
                        'amount',
                        'formatted',
                        'currency',
                    ],
                ],
                'currency_info',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Ticket statistics retrieved successfully',
                'data' => [
                    'total_tickets' => 5,
                    'unprinted_tickets' => 5,
                    'printed_tickets' => 0,
                    'currency_info' => [
                        'code' => 'PHP',
                        'symbol' => '₱',
                        'name' => 'Philippine Peso',
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_returns_specific_ticket_details()
    {
        $plate = Plate::factory()->create(['number' => 'ABC123']);

        $session = ParkingSession::factory()->create([
            'plate_number' => $plate->number,
            'created_by' => $this->attendant->id,
            'parking_rate_id' => $this->parkingRate->id,
        ]);

        Sanctum::actingAs($this->attendant);

        $ticket = $session->ticket;

        $response = $this->getJson("/api/tickets/{$ticket->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'ticket_number',
                    'plate_number',
                    'time_in',
                    'formatted_time_in',
                    'rate',
                    'formatted_rate',
                    'currency',
                    'parking_slot',
                    'is_printed',
                    'barcode',
                    'qr_data',
                    'created_at',
                    'updated_at',
                    'parking_session',
                    'creator',
                    'parking_rate',
                ],
                'currency_info',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Ticket retrieved successfully',
                'data' => [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'plate_number' => $plate->number,
                    'currency' => 'PHP',
                ],
            ]);
    }

    /** @test */
    public function it_prevents_access_to_other_users_tickets()
    {
        $plate = Plate::factory()->create(['number' => 'ABC123']);

        $session = ParkingSession::factory()->create([
            'plate_number' => $plate->number,
            'created_by' => $this->admin->id, // Created by admin, not attendant
            'parking_rate_id' => $this->parkingRate->id,
        ]);

        Sanctum::actingAs($this->attendant);

        $ticket = $session->ticket;

        $response = $this->getJson("/api/tickets/{$ticket->id}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Ticket not found',
            ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/tickets');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_requires_attendant_role()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/tickets');

        $response->assertStatus(403);
    }

    /** @test */
    public function it_validates_request_parameters()
    {
        Sanctum::actingAs($this->attendant);

        $response = $this->getJson('/api/tickets?print_status=invalid&per_page=1000');

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'print_status',
                    'per_page',
                ],
            ]);
    }

    /** @test */
    public function it_handles_pagination_correctly()
    {
        $plate = Plate::factory()->create(['number' => 'ABC123']);

        // Create 25 sessions (which will create 25 tickets)
        ParkingSession::factory()->count(25)->create([
            'plate_number' => $plate->number,
            'created_by' => $this->attendant->id,
            'parking_rate_id' => $this->parkingRate->id,
        ]);

        Sanctum::actingAs($this->attendant);

        $response = $this->getJson('/api/tickets?per_page=10');

        $response->assertStatus(200);

        $meta = $response->json('meta');
        $this->assertEquals(10, $meta['per_page']);
        $this->assertEquals(25, $meta['total']);
        $this->assertEquals(3, $meta['last_page']); // 25 items / 10 per page = 3 pages
        $this->assertTrue($meta['has_more_pages']);
    }
}
