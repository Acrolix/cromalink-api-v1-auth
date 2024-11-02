<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    public function testObtenerTokenDeAcceso()
    {   
        $userData = [
            'email' => 'test@test.com',
            'password' => bcrypt('12345678'),
            'active' => true,
            'email_verified_at' => now(),
        ];
        $user = \App\Models\User::create($userData);
        
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('api/oauth/token', [
            'grant_type' => 'password',
            'client_id' => env('PASSPORT_CLIENT_ID'),
            'client_secret' => env('PASSPORT_CLIENT_SECRET'),
            'username' => $user->email,
            'password' => '12345678',
            'scope' => '',
        ]);
        print_r($response->json());
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'expires_in',
            'token_type',
            'refresh_token',
        ]);
    }
}
