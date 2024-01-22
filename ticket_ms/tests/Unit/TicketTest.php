<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;
    public function test_make_ticket_api()
    {
        // test fail request with has small title
        $response = $this->postJson('/api/ticket/store', [
            'token' => '123456789000',
            'title' => 'h1',
            'text' => 'some some some',
        ]);
        $response->assertStatus(422);
        // check error message has title
        $this->assertTrue(array_key_exists('title', $response->json()['data']['errors']));

        // test success request
        $response = $this->postJson('/api/ticket/store', [
            'token' => '123456789000',
            'title' => 'hdd',
            'text' => 'some some some',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('tickets', [
            'title' => 'hdd',
            'user_id' => 1
        ]);
    }
}
