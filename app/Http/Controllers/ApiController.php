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
        //TODO store expiration and retrieve new token on expiration
        $expiration = $response->expiration;
        dump($expiration);
        session(['authKey' =>'Bearer ' . $response->accessToken]);

        $headers = [
            'x-inbenta-key' => env('API_KEY'),
            'Authorization' =>  session('authKey')
        ];

        $response = Http::withHeaders($headers)->get('https://api.inbenta.io/v1/apis');
        $response = json_decode($response);   

        session(['chatbotApiUrl' => $response->apis->chatbot]);
        //TODO this will not be needed with renovation on expiration
        $this->initConversation();
        //TODO return different response depending of API response
        return $response;
    }
    // TODO: Conversation configuration on payload
    public function initConversation() {
        $headers = [
            'x-inbenta-key' => env('API_KEY'),
            'Authorization' => session('authKey')
        ];
        $response = Http::withHeaders($headers)->post(session('chatbotApiUrl') . "/v1/conversation");
        $response = json_decode($response);
        session(['sessionToken' => 'Bearer ' . $response->sessionToken]);
        session(['sessionId' => $response->sessionId]);
    }

    public function talk(Request $request) {

        if(!session()->has('sessionToken') || !session()->has('sessionId')) $this->initConversation();

        $headers = [
            'x-inbenta-key' => env('API_KEY'),
            'Authorization' => session('authKey'),
            'x-inbenta-session' => session('sessionToken')
        ];
        $response = Http::withHeaders($headers)
        ->post(session('chatbotApiUrl') . "/v1/conversation/message", [
            'message' => $request->text
        ]);
        $response = json_decode($response);

        return $response->answers;
    }
}
