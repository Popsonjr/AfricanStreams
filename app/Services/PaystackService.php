<?php
namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    protected $client;
    protected $secretKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->secretKey = env('PAYSTACK_SECRET_KEY');
        $this->baseUrl = env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co');
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function createPlan(String $name, int $amount, String $interval = 'monthly') {
        try {
            $response = $this->client->post('/plan', [
                'json' => [
                    'name' => $name,
                    'amount' => $amount * 100, // convert to kobo
                    'interval' => $interval,
                ]
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Paystack create plan failed', ['error' => $e->getMessage()]);
            throw new Exception('Failed to create plan: ' . $e->getMessage());
        }
    }

    public function getPlan(String $planCode) {
        try {
            $response = $this->client->get("/plan/{$planCode}");
            return json_decode($response->getBody(), true);
        }  catch (RequestException $e) {
            Log::error('Paystack get plan failed', ['error' => $e->getMessage()]);
            throw new Exception('Failed to get plan: ' . $e->getMessage());
        }
    }

    public function createSubscription(string $customerCode, string $planCode) {
        try {
            $response = $this->client->post('/subscription', [
                'json' => [
                    'customer' => $customerCode,
                    'plan' => $planCode,
                ],
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Paystack create subscription failed', ['error' => $e->getMessage()]);
            throw new \Exception('Failed to create subscription: ' . $e->getMessage());
        }
    }

    public function initializeTransaction(string $email, int $amount, string $reference, string $callbackUrl): array
    {
        try {
            $response = $this->client->post('/transaction/initialize', [
                'json' => [
                    'email' => $email,
                    'amount' => $amount * 100, // Convert to kobo
                    'reference' => $reference,
                    'callback_url' => $callbackUrl,
                    'currency' => 'NGN',
                ],
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Paystack initialize transaction failed', ['error' => $e->getMessage()]);
            throw new \Exception('Failed to initialize transaction: ' . $e->getMessage());
        }
    }

    public function verifyTransaction(string $reference): array
    {
        try {
            $response = $this->client->get("/transaction/verify/{$reference}");
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Paystack verify transaction failed', ['error' => $e->getMessage()]);
            throw new \Exception('Failed to verify transaction: ' . $e->getMessage());
        }
    }
}