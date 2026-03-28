<?php
require 'calendar_config.php';

class GoogleCalendarAPI {
    private $client;
    private $service;
    
    public function __construct() {
        if (!class_exists('Google_Client')) {
            throw new Exception('Google API Client Library not installed. Run: composer require google/apiclient:^2.0');
        }
        
        $this->client = new Google_Client();
        $this->client->setClientId(GOOGLE_CLIENT_ID);
        $this->client->setClientSecret(GOOGLE_CLIENT_SECRET);
        $this->client->setRedirectUri(GOOGLE_REDIRECT_URI);
        $this->client->addScope(GOOGLE_SCOPES);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        
        $this->service = new Google_Service_Calendar($this->client);
    }
    
    public function getAuthUrl() {
        return $this->client->createAuthUrl();
    }
    
    public function authenticate($code) {
        $this->client->authenticate($code);
        return $this->client->getAccessToken();
    }
    
    public function setAccessToken($token) {
        $this->client->setAccessToken($token);
        
        if ($this->client->isAccessTokenExpired()) {
            $this->client->fetchAccessTokenWithRefreshToken();
            return $this->client->getAccessToken();
        }
        
        return $token;
    }
    
    public function createEvent($calendarId, $eventData) {
        $event = new Google_Service_Calendar_Event();
        
        // Basic event details
        $event->setSummary($eventData['title']);
        $event->setDescription($eventData['description'] ?? '');
        
        // Set start and end times
        $start = new Google_Service_Calendar_EventDateTime();
        $start->setDateTime($eventData['start_datetime']);
        $start->setTimeZone('Asia/Jakarta');
        $event->setStart($start);
        
        $end = new Google_Service_Calendar_EventDateTime();
        $end->setDateTime($eventData['end_datetime']);
        $end->setTimeZone('Asia/Jakarta');
        $event->setEnd($end);
        
        // Set location
        if (!empty($eventData['location'])) {
            $event->setLocation($eventData['location']);
        }
        
        // Set color based on shift type
        if (!empty($eventData['color'])) {
            $event->setColorId($this->getColorId($eventData['color']));
        }
        
        // Add attendees if provided
        if (!empty($eventData['attendees'])) {
            $attendees = [];
            foreach ($eventData['attendees'] as $email) {
                $attendee = new Google_Service_Calendar_EventAttendee();
                $attendee->setEmail($email);
                $attendees[] = $attendee;
            }
            $event->setAttendees($attendees);
        }
        
        // Set reminders
        $event->setReminders([
            'useDefault' => false,
            'overrides' => [
                ['method' => 'email', 'minutes' => 24 * 60], // 24 hours before
                ['method' => 'popup', 'minutes' => 30] // 30 minutes before
            ]
        ]);
        
        try {
            $createdEvent = $this->service->events->insert($calendarId, $event);
            return [
                'success' => true,
                'event_id' => $createdEvent->getId(),
                'html_link' => $createdEvent->getHtmlLink()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function updateEvent($calendarId, $eventId, $eventData) {
        try {
            $event = $this->service->events->get($calendarId, $eventId);
            
            // Update event details
            $event->setSummary($eventData['title']);
            $event->setDescription($eventData['description'] ?? '');
            
            if (!empty($eventData['start_datetime'])) {
                $start = new Google_Service_Calendar_EventDateTime();
                $start->setDateTime($eventData['start_datetime']);
                $start->setTimeZone('Asia/Jakarta');
                $event->setStart($start);
            }
            
            if (!empty($eventData['end_datetime'])) {
                $end = new Google_Service_Calendar_EventDateTime();
                $end->setDateTime($eventData['end_datetime']);
                $end->setTimeZone('Asia/Jakarta');
                $event->setEnd($end);
            }
            
            if (!empty($eventData['location'])) {
                $event->setLocation($eventData['location']);
            }
            
            if (!empty($eventData['color'])) {
                $event->setColorId($this->getColorId($eventData['color']));
            }
            
            $updatedEvent = $this->service->events->update($calendarId, $eventId, $event);
            
            return [
                'success' => true,
                'event_id' => $updatedEvent->getId(),
                'html_link' => $updatedEvent->getHtmlLink()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function deleteEvent($calendarId, $eventId) {
        try {
            $this->service->events->delete($calendarId, $eventId);
            return ['success' => true];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function getEvents($calendarId, $timeMin = null, $timeMax = null) {
        $optParams = [];
        
        if ($timeMin) {
            $optParams['timeMin'] = $timeMin;
        }
        
        if ($timeMax) {
            $optParams['timeMax'] = $timeMax;
        }
        
        $optParams['maxResults'] = 100;
        $optParams['orderBy'] = 'startTime';
        $optParams['singleEvents'] = true;
        
        try {
            $results = $this->service->events->listEvents($calendarId, $optParams);
            $events = [];
            
            foreach ($results->getItems() as $event) {
                $events[] = [
                    'id' => $event->getId(),
                    'title' => $event->getSummary(),
                    'description' => $event->getDescription(),
                    'location' => $event->getLocation(),
                    'start' => $event->getStart()->getDateTime(),
                    'end' => $event->getEnd()->getDateTime(),
                    'html_link' => $event->getHtmlLink()
                ];
            }
            
            return [
                'success' => true,
                'events' => $events
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function getColorId($hexColor) {
        // Map hex colors to Google Calendar color IDs
        $colorMap = [
            '#4285F4' => '1', // Blue
            '#EA4335' => '2', // Red  
            '#FBBC04' => '5', // Yellow
            '#34A853' => '3', // Green
            '#9E9E9E' => '8', // Gray
            '#FF6F00' => '7'  // Orange
        ];
        
        return $colorMap[$hexColor] ?? '1';
    }
    
    public function listCalendars() {
        try {
            $calendarList = $this->service->calendarList->listCalendarList();
            $calendars = [];
            
            foreach ($calendarList->getItems() as $calendar) {
                $calendars[] = [
                    'id' => $calendar->getId(),
                    'summary' => $calendar->getSummary(),
                    'description' => $calendar->getDescription(),
                    'primary' => $calendar->getPrimary()
                ];
            }
            
            return [
                'success' => true,
                'calendars' => $calendars
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?>
