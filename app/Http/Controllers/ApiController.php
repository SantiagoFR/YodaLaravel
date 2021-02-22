<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function getAuthorizationApi(Request $request) {
        $headers = [
            'x-inbenta-key' => env('API_KEY'),
            'Content-Type' => 'application/json'
            ];
        $body = [
            'secret' =>  env('API_SECRET')
        ];
        $response = Http::withHeaders($headers)->post('https://api.inbenta.io/v1/auth', $body);
        $response = json_decode($response);
        $accessToken = $response->accessToken;
        $expiration = $response->expiration;


        $headers = [
            'x-inbenta-key' => env('API_KEY'),
            'Authorization' => 'Bearer '.$accessToken
        ];
        $response = Http::withHeaders($headers)->get('https://api.inbenta.io/v1/apis');
        $response = json_decode($response);
        $chatbotApiUrl = $response->apis->chatbot;

        return $chatbotApiUrl;
    }
}
