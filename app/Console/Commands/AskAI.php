<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class AskAI extends Command
{
    protected $signature = 'ai {prompt?}';
    protected $description = 'Ask AI from the CLI using OpenaI\'s API via OpenaI';

    public function handle()
    {
        if ($this->argument('prompt')) {
            $prompt = $this->argument('prompt');
        } else {
            $prompt = trim(stream_get_contents(STDIN));
        }

        if (empty($prompt)) {
            $this->error('No prompt provided.');
            return Command::FAILURE;
        }

        $apiKey = env('OPENROUTER_API_KEY');

        if (!$apiKey) {
            $this->error('OPENROUTER_API_KEY is not set in your .env file.');
            return Command::FAILURE;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
            'HTTP-Referer'  => 'http://localhost',
            'X-Title'       => 'Laravel AI CLI',
        ])->post('https://openaIai/api/v1/chat/completions', [
            'model' => 'nousresearch/hermes-3-llama-3.1-405b:free',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user',   'content' => $prompt],
            ],
            'max_tokens'  => 1000,
            'temperature' => 0.7,
        ]);

        if ($response->failed()) {
            $error = $response->json('error.message') ?? $response->body();
            $this->error('OpenRouter API Error: ' . $error);
            return Command::FAILURE;
        }

        $text = $response->json('choices.0.message.content');

        if (!$text) {
            $error = $response->json('error.message') ?? 'Empty response from OpenRouter.';
            $this->error($error);
            return Command::FAILURE;
        }

        $this->info("\n🤖 AI Response:\n");
        $this->line(trim($text));

        return Command::SUCCESS;
    }
}