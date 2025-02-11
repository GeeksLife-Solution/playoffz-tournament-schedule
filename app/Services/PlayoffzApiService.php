<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Client\RequestException;

class PlayoffzApiService
{
    protected $baseUrl;
    protected $defaultCredentials;

    public function __construct()
    {
        $this->baseUrl = 'https://app.playoffz.in/orag_api/';
        $this->defaultCredentials = [
            'email' => 'laks@gmail.com',
            'password' => '123456',
            'type' => 'Orgnizer'
        ];
    }

    public function loginOrganizer()
    {
        if (Session::has('organizer')) {
            return ['message' => 'Already logged in', 'organizer' => Session::get('organizer')];
        }

        $response = $this->post('u_login_user.php', $this->defaultCredentials);

        if (isset($response['ResponseCode']) && $response['ResponseCode'] == '200') {
            Session::put('organizer', $response['OragnizerLogin']);
            Session::put('currency', $response['currency']);
        }

        return $response;
    }

    public function getCategories()
    {
        $organizer = $this->ensureLoggedIn();
        if (!$organizer) {
            return ['error' => 'Login failed'];
        }
        $response = $this->post('list_category.php', ['orag_id' => $organizer['id']]);
        if (isset($response['Categorydata']) && is_array($response['Categorydata'])) {
            return $response['Categorydata'];
        }
        return ['error' => 'Failed to fetch category data'];
    }

    public function getEvents()
    {
        $organizer = $this->ensureLoggedIn();
        if (!$organizer) {
            return ['error' => 'Login failed'];
        }
        $response = $this->post('list_event_fixtures.php', ['orag_id' => $organizer['id']]);
        if (isset($response['Eventdata']) && is_array($response['Eventdata'])) {
            return $response['Eventdata'];
        }

        return ['error' => 'Failed to fetch event data'];
    }

    public function getEventParticipants($eventId)
    {
        $organizer = $this->ensureLoggedIn();
        if (!$organizer) {
            return ['error' => 'Login failed'];
        }
        $response = $this->post('event_participant.php', [
            'orag_id' => $organizer['id'],
            'event_id' => $eventId
        ]);
        if (isset($response['participants']) && is_array($response['participants'])) {
            return $response['participants'];
        }
        return ['error' => 'Failed to fetch participants'];
    }


    private function ensureLoggedIn()
    {
        if (!Session::has('organizer')) {
            $this->loginOrganizer();
        }
        return Session::get('organizer', null);
    }

    private function post($endpoint, $data)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . $endpoint, $data);

            return $response->successful() ? $response->json() : ['error' => 'API request failed', 'status' => $response->status()];
        } catch (RequestException $e) {
            return ['error' => $e->getMessage()];
        }
    }

}
