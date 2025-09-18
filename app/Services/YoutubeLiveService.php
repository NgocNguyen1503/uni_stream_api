<?php

namespace App\Services;

// For version php 8.2.x and GoogleClient ^2.0
use Google_Client;
use Google_Service_YouTube;
use Google_Service_YouTube_LiveBroadcast;
use Google_Service_YouTube_LiveStream;
// For version php 8.1.x and GoogleClient ~2.0
use Google\Service\YouTube\LiveBroadcast;
use Google\Service\YouTube\LiveStream;
use Google\Service\YouTube;
use Google\Client;

class YoutubeLiveService
{
    // Init google client.
    private $client;
    // Define youtube scope.
    const YOUTUBE_SCOPE = [
        'https://www.googleapis.com/auth/youtube',
        'https://www.googleapis.com/auth/youtube.force-ssl'
    ];

    /**
     * Construction method.
     * Init google client.
     */
    public function __construct()
    {
        $client = new Client();
        $client->setClientId(config('services.google_services.google_client_id'));
        $client->setClientSecret(config('services.google_services.google_client_secret'));
        $client->setRedirectUri(config('services.google_services.redirect_url'));
        $client->addScope(self::YOUTUBE_SCOPE);
        $this->client = $client;
    }

    /**
     * Method redirect to authentication of google.
     *
     * @return string Google authentication link.
     */
    public function redirectAuthGoogle()
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Method render token.
     *
     * @param string $code from callback url.
     * @param int $streamerId from database.
     * @return array token and streamer_id.
     */
    public function renderToken($code = null, $streamerId)
    {
        // Render access token with auth code from callback url.
        $token = $this->client->fetchAccessTokenWithAuthCode($code);
        // Return token and save to database.
        return [
            'streamer_id' => $streamerId,
            'google_live_token' => $token   // Need to save token to DB for corresponding streamer to create live session.
        ];
    }

    /**
     * Method create a live.
     *
     * @param string $title from database.
     * @param string $token get from Google callback URL.
     * @return array watch_url, stream_url, stream_key.
     */
    public function createLiveStream($title, $token)
    {
        // Check token has expire.
        $this->client->setAccessToken($token);
        if ($this->client->isAccessTokenExpired()) {
            return 'Token expired';
        }
        // Init youtube service.
        $youtube = new YouTube($this->client);
        // 1. Create broadcast.
        $broadcast = new LiveBroadcast([
            'snippet' => [
                'title' => $title,
                'scheduledStartTime' => date(DATE_RFC3339, strtotime('+5 minutes'))
            ],
            'status' => [
                'privacyStatus' => 'public' // Default public live stream.
            ],
            'kind' => 'youtube#liveBroadcast'
        ]);
        $broadcast = $youtube->liveBroadcasts->insert('snippet,status', $broadcast);
        // 2. Create stream.
        $stream = new LiveStream([
            'snippet' => [
                'title' => $title
            ],
            'cdn' => [
                'resolution' => '480p',
                'frameRate' => '30fps',
                'ingestionType' => 'rtmp'
            ],
            'kind' => 'youtube#liveStream'
        ]);
        $stream = $youtube->liveStreams->insert('snippet,cdn', $stream);
        // 3. Assign stream to broadcast.
        $youtube->liveBroadcasts->bind($broadcast['id'], 'id,contentDetails', [
            'streamId' => $stream['id']
        ]);
        $videoId = $broadcast['id'];
        // Return information save to database.
        return [
            'video_id' => $videoId,                                              // Basic info using check live URL.
            'watch_url' => 'https://www.youtube.com/watch?v=' . $videoId,         // Using for FE to display live session.
            'embed_url' => 'https://www.youtube.com/embed/' . $videoId,           // Using for FE to display live session.
            'stream_url' => $stream['cdn']['ingestionInfo']['ingestionAddress'],   // Use for OBS to support live sessions.
            'stream_key' => $stream['cdn']['ingestionInfo']['streamName'],         // Use for OBS to support live sessions.
        ];
    }
}