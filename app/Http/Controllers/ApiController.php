<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function getAuthorizationApi(Request $request) {
        return "hola";
        $headers = [
            'x-inbenta-key' => env('API_KEY'),
            'Content-Type' => 'application/json'
            ];
        $body = [
            'secret' =>  env('API_SECRET')
        ];
        $response = $req->post('https://api.inbenta.io/v1/auth', $headers, $body);
        $response = json_decode($response);
        $accessToken = $response->accessToken;
        $expiration = $response->expiration;


        $headers = [
            'x-inbenta-key' => env('API_KEY'),
            'Authorization' => 'Bearer '.$accessToken
        ];
        $response = $req->get('https://api.inbenta.io/v1/apis', $headers);
        $response = json_decode($response);
        $chatbotApiUrl = $response->apis->chatbot;

        return $chatbotApiUrl;
    }
}
