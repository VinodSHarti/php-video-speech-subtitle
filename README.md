# PHP Video Speech Subtitle

Convert video speech to text and automatically burn subtitles.

## Installation

composer require vinodharti/php-video-speech-subtitle

## Usage

use Vinod\VideoSpeech\VideoSpeech;

VideoSpeech::process(
    'input.mp4',
    'output.mp4',
    'YOUR_API_KEY'
);

## Requirements

- PHP 8.0+
- FFmpeg installed