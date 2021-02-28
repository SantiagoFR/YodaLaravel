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
        // Auth endpoint to get access token 
        $response = Http::withHeaders($headers)->post('https://api.inbenta.io/v1/auth', $body);
        $arrResponse = $response->json();
        //TODO store expiration and retrieve new token on expiration
        if (empty($arrResponse)) {
            return 'Error: Auth endpoint response is empty';
        }
        session(['expiration' => $arrResponse['expiration'] ]);
        session(['authKey' =>'Bearer ' . $response['accessToken']]);

        $headers = [
            'x-inbenta-key' => env('API_KEY'),
            'Authorization' =>  session('authKey')
        ];

        // Api endpoint to get chatbot API url 
        $response = Http::withHeaders($headers)->get('https://api.inbenta.io/v1/apis');
        $arrResponse = $response->json();
        if (empty($arrResponse)) {
            return 'Error: Apis endpoint response is empty';
        }
        session(['chatbotApiUrl' => $arrResponse['apis']['chatbot']]);
        //TODO this will not be needed with renovation on expiration
        $this->initConversation();

        //TODO return different response depending of API response
        return "Auth and Apis calls was succesfull";
    }
    // TODO: Conversation configuration on payload
    public function initConversation() {
        $headers = [
            'x-inbenta-key' => env('API_KEY'),
            'Authorization' => session('authKey')
        ];
        // ChatBot '/conversation' endpoint
        $response = Http::withHeaders($headers)->post(session('chatbotApiUrl') . "/v1/conversation");
        $arrResponse = $response->json();
        if (empty($arrResponse)) {
            return 'Error: InitConversation endpoint response is empty';
        }
        session(['sessionToken' => 'Bearer ' . $arrResponse['sessionToken']]);
        session(['sessionId' => $arrResponse['sessionId']]);
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
        $arrResponse = $response->json();

        if (empty($arrResponse)) {
            return 'Error: "conversation/message" endpoint response is empty';
        }
        return $response['answers'];
    }
}
