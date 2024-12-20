<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use \Illuminate\Support\Facades\Artisan;

class AuthTest extends TestCase
{
    use DatabaseTransactions;
    public function testObtenerTokenDeAcceso()
    {
        if (!\Laravel\Passport\Client::where('password_client', 1)->exists()) {
            Artisan::call('passport:client', ['--password' => true, '--no-interaction' => true]);
        }
        $client = \Laravel\Passport\Client::where('password_client', 1)->first();

        $userData = [
            'email' => 'test@test.com',
            'password' => bcrypt('12345678'),
            'active' => true,
            'email_verified_at' => now(),
        ];

        $userTest = \App\Models\User::create($userData);

        $this->assertNotNull($userTest);

        $response = $this->post('api/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $userTest->email,
            'password' => '12345678',
            'scope' => '',
        ], [
            'Accept' => 'application/json',
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'expires_in',
            'token_type',
            'refresh_token',
        ]);
    }

    public function testTokenDeAccesoValido()
    {
        if (!\Laravel\Passport\Client::where('password_client', 1)->exists()) {
            Artisan::call('passport:client', ['--password' => true, '--no-interaction' => true]);
        }
        $client = \Laravel\Passport\Client::where('password_client', 1)->first();

        $userData = [
            'email' => 'test@test.com',
            'password' => bcrypt('12345678'),
            'active' => true,
            'email_verified_at' => now(),
        ];

        $userTest = \App\Models\User::create($userData);

        $tokenResponse = $this->post('api/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $userTest->email,
            'password' => '12345678',
            'scope' => '',
        ]);

        $token = json_decode($tokenResponse->getContent())->access_token;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
            'Accept' => 'application/json',
        ])->post('api/oauth/verify');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'valid',
            'user' => $userTest->id,
        ]);
    }

    public function testTokenDeAccesoInvalido()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer invalid_token",
            'Accept' => 'application/json',
        ])->post('api/oauth/verify');

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 'invalid',
            'user' => NULL,
        ]);
    }

    public function testTokenRefresh()
    {
        if (!\Laravel\Passport\Client::where('password_client', 1)->exists()) {
            Artisan::call('passport:client', ['--password' => true, '--no-interaction' => true]);
        }
        $client = \Laravel\Passport\Client::where('password_client', 1)->first();

        $userData = [
            'email' => 'test@test.com',
            'password' => bcrypt('12345678'),
            'active' => true,
            'email_verified_at' => now(),
        ];

        $userTest = \App\Models\User::create($userData);

        $tokenResponse = $this->post('api/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $userTest->email,
            'password' => '12345678',
            'scope' => '',
        ]);

        $refresh_token = json_decode($tokenResponse->getContent())->refresh_token;

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->post('api/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token,
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => '',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'expires_in',
            'token_type',
            'refresh_token',
        ]);
    }
    public function testTokenRefreshInvalido()
    {
        if (!\Laravel\Passport\Client::where('password_client', 1)->exists()) {
            Artisan::call('passport:client', ['--password' => true, '--no-interaction' => true]);
        }
        $client = \Laravel\Passport\Client::where('password_client', 1)->first();

        $userData = [
            'email' => 'test@test.com',
            'password' => bcrypt('12345678'),
            'active' => true,
            'email_verified_at' => now(),
        ];

        $userTest = \App\Models\User::create($userData);

        $tokenResponse = $this->post('api/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $userTest->email,
            'password' => '12345678',
            'scope' => '',
        ]);

        $refresh_token = json_decode($tokenResponse->getContent())->refresh_token;

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->post('api/oauth/token', [
            'grant_type' => "refresh_token",
            'refresh_token' => str_replace('a', 'x', $refresh_token),
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => '',
        ]);

        $response->assertStatus(401);
        $response->assertJsonFragment([
            'error_description' => 'The refresh token is invalid.',
            'hint' => 'Cannot decrypt the refresh token',
            'message' => 'The refresh token is invalid.',
        ]);
    }

    public function testTokenRevoke()
    {
        if (!\Laravel\Passport\Client::where('password_client', 1)->exists()) {
            Artisan::call('passport:client', ['--password' => true, '--no-interaction' => true]);
        }
        $client = \Laravel\Passport\Client::where('password_client', 1)->first();

        $userData = [
            'email' => 'test@test.com',
            'password' => bcrypt('12345678'),
            'active' => true,
            'email_verified_at' => now(),
        ];

        $userTest = \App\Models\User::create($userData);

        $responseToken = $this->post('api/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $userTest->email,
            'password' => '12345678',
            'scope' => '',
        ], [
            'Accept' => 'application/json',
        ]);

        $token = json_decode($responseToken->getContent())->access_token;

        $responseValid = $this->withHeaders([
            'Authorization' => "Bearer $token",
            'Accept' => 'application/json',
        ])->post('api/oauth/verify');

        $responseValid->assertStatus(200);
        $responseValid->assertJson([
            'status' => 'valid',
            'user' => $userTest->id,
        ]);

        $tokens = $userTest->tokens->all();
        foreach ($tokens as $tokenp) {

            $tokenId = $tokenp["id"];
            $responseRevoke = $this->withHeaders([
                'Authorization' => "Bearer $token",
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                ])->delete("api/oauth/tokens/$tokenId");
                
            $responseRevoke->assertStatus(204);
        }
    }

    public function testTokenRevokeInvalido()
    {
        if (!\Laravel\Passport\Client::where('password_client', 1)->exists()) {
            Artisan::call('passport:client', ['--password' => true, '--no-interaction' => true]);
        }
        $client = \Laravel\Passport\Client::where('password_client', 1)->first();

        $userData = [
            'email' => 'test@test.com',
            'password' => bcrypt('12345678'),
            'active' => true,
            'email_verified_at' => now(),
        ];

        $userTest = \App\Models\User::create($userData);

        $responseToken = $this->post('api/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $userTest->email,
            'password' => '12345678',
            'scope' => '',
        ], [
            'Accept' => 'application/json',
        ]);

        $token = json_decode($responseToken->getContent())->access_token;

        $responseRevoke = $this->withHeaders([
            'Authorization' => "Bearer $token",
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            ])->delete("api/oauth/tokens/invalid_token");
            
        $responseRevoke->assertStatus(404);
    }

    public function testTokenRevokeWithoutPermision()
    {
        if (!\Laravel\Passport\Client::where('password_client', 1)->exists()) {
            Artisan::call('passport:client', ['--password' => true, '--no-interaction' => true]);
        }
        $client = \Laravel\Passport\Client::where('password_client', 1)->first();

        $userData = [
            'email' => 'test@test.com',
            'password' => bcrypt('12345678'),
            'active' => true,
            'email_verified_at' => now(),
        ];

        $userTest = \App\Models\User::create($userData);

        $this->post('api/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $userTest->email,
            'password' => '12345678',
            'scope' => '',
        ], [
            'Accept' => 'application/json',
        ]);


        $tokens = $userTest->tokens->all();
        foreach ($tokens as $tokenp) {

            $tokenId = $tokenp["id"];
            $responseRevoke = $this->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                ])->delete("api/oauth/tokens/$tokenId");
                
            $responseRevoke->assertStatus(401);
        }
    }
}
