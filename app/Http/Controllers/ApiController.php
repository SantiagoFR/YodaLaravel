<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function getAuthorizationApi() {
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
        if (session('expiration') < strtotime('now')) $this->getAuthorizationApi();
        if (!session()->has('sessionToken') || !session()->has('sessionId')) $this->initConversation();

        if(str_contains($request->text, 'force')){
            $headers = [
                'Content-Type' => 'application/json'
            ];
            $response = Http::withHeaders($headers)
            ->post("https://inbenta-graphql-swapi-prod.herokuapp.com/api", [
                'query' => "{allFilms(first: 10) {films{title}}}"
            ]);
            $arrResponse = $response->json();
            $message =  'Here is a list of Star Wars Films: <br>';
            foreach($arrResponse['data']['allFilms']['films'] as $character){
                $message = $message . $character['title'] . '<br>';
            }               
    
            return [
                'type' => 'list',
                'message' => $message
            ];
        }
        

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
            return [ 
                'type' => 'error',
                'messages' => 'Error: "conversation/message" endpoint response is empty'
            ];
        }
        if (isset($arrResponse['answers'][0]['flags'][0]) && $arrResponse['answers'][0]['flags'][0] === "no-results") {  
            if(session('noResultsNumber') !== 2) session(['noResultsNumber' => session('noResultsNumber') + 1]);   
            else {
                session(['noResultsNumber' => 0]);
                $headers = [
                    'Content-Type' => 'application/json'
                ];
                $response = Http::withHeaders($headers)
                ->post("https://inbenta-graphql-swapi-prod.herokuapp.com/api", [
                    'query' => "{allPeople(first: 10) { people { name } } }"
                ]);
                $arrResponse = $response->json();
                $message =  'Here is a list of Star Wars Characters: <br>';
                foreach($arrResponse['data']['allPeople']['people'] as $character){
                    $message = $message . $character['name'] . '<br>';
                }               
        
                return [
                    'type' => 'list',
                    'message' => $message
                ];
            } 
        }
        return [
            'type' => 'message',
            'message' => $response['answers'][0]
        ];
    }

    public function getHistory ()
    {        
        if (!session()->has('expiration') || session('expiration') < strtotime('now')) {
            $this->getAuthorizationApi();
            $this->initConversation();
        }
        if (!session()->has('sessionToken') || !session()->has('sessionId')) $this->initConversation();

        $headers = [
            'x-inbenta-key' => env('API_KEY'),
            'Authorization' => session('authKey'),
            'x-inbenta-session' => session('sessionToken')
        ];
        $response = Http::withHeaders($headers)
        ->get(session('chatbotApiUrl') . "/v1/conversation/history");
        $arrResponse = $response->json();


        if (empty($arrResponse)) {
            return 'error';
        }
        return $arrResponse;
    }
}
