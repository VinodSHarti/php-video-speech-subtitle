<?php

namespace Vinod\VideoSpeech;

use GuzzleHttp\Client;
use Exception;

class VideoSpeech
{
    public static function process(
        string $videoPath,
        string $outputPath,
        string $apiKey
    ): void {

        if (!file_exists($videoPath)) {
            throw new Exception("Video file not found.");
        }

        $tempAudio = sys_get_temp_dir() . '/audio.wav';
        $tempSrt   = sys_get_temp_dir() . '/subtitle.srt';

        // 1️⃣ Extract audio
        self::extractAudio($videoPath, $tempAudio);

        // 2️⃣ Convert speech to text
        $text = self::transcribe($tempAudio, $apiKey);

        // 3️⃣ Generate subtitle file
        self::generateSubtitle($text, $tempSrt);

        // 4️⃣ Burn subtitle into video
        self::burnSubtitle($videoPath, $tempSrt, $outputPath);
    }

    private static function extractAudio(string $video, string $audio): void
    {
        exec("ffmpeg -y -i \"$video\" -vn -acodec pcm_s16le -ar 44100 -ac 2 \"$audio\"");
    }

    private static function transcribe(string $audioPath, string $apiKey): string
    {
        $client = new Client();

        $response = $client->post(
            "https://api.openai.com/v1/audio/transcriptions",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey
                ],
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($audioPath, 'r')
                    ],
                    [
                        'name' => 'model',
                        'contents' => 'whisper-1'
                    ]
                ]
            ]
        );

        $data = json_decode($response->getBody(), true);

        return $data['text'] ?? '';
    }

    private static function generateSubtitle(string $text, string $srtPath): void
    {
        $content = "1\n00:00:00,000 --> 00:10:00,000\n$text\n";
        file_put_contents($srtPath, $content);
    }

    private static function burnSubtitle(string $video, string $srt, string $output): void
    {
        exec("ffmpeg -y -i \"$video\" -vf subtitles=\"$srt\" \"$output\"");
    }
}