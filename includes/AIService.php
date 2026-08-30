<?php
/**
 * AIService — OpenAI-compatible API client for IT Bot
 *
 * Sends chat completion requests to an OpenAI-compatible endpoint (OpenAI,
 * Grocq, OpenRouter, or any self-hosted compatible service) and returns the
 * generated text.  When the API key is empty or the request fails, every
 * method degrades gracefully so the app keeps working with its existing
 * database-driven responses.
 *
 * Usage:
 *   $ai = new AIService();
 *   if ($ai->isAvailable()) {
 *       $reply = $ai->chat($messages);  // array of ['role'=>'user'|'assistant', 'content'=>'...']
 *   }
 */
class AIService {
    private string $apiKey;
    private string $baseUrl;
    private string $model;
    private int $timeout;

    public function __construct() {
        $this->apiKey  = (string) (defined('OPENAI_API_KEY')  ? OPENAI_API_KEY  : '');
        $this->baseUrl = (string) (defined('OPENAI_BASE_URL') ? rtrim(OPENAI_BASE_URL, '/') : 'https://api.openai.com/v1');
        $this->model   = (string) (defined('OPENAI_MODEL')    ? OPENAI_MODEL    : 'gpt-4o-mini');
        $this->timeout = (int)   (defined('AI_TIMEOUT')       ? AI_TIMEOUT       : 30);
    }

    /**
     * Is the service configured (non-empty API key)?
     */
    public function isAvailable(): bool {
        return $this->apiKey !== '';
    }

    /**
     * Send a chat-completion request and return the assistant's text reply,
     * or false on any failure (caller falls back to DB-driven response).
     *
     * @param array $messages  Array of ['role' => 'system'|'user'|'assistant', 'content' => '...']
     * @param float $temperature
     * @param int   $maxTokens
     * @return string|false
     */
    public function chat(array $messages, float $temperature = 0.7, int $maxTokens = 1024) {
        if (!$this->isAvailable()) {
            return false;
        }

        $payload = [
            'model'       => $this->model,
            'messages'    => $messages,
            'temperature' => $temperature,
            'max_tokens'  => $maxTokens,
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ];

        $ch = curl_init($this->baseUrl . '/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $rawResponse = curl_exec($ch);
        $httpCode     = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);
        curl_close($ch);

        if ($rawResponse === false) {
            error_log('AIService curl error: ' . $curlError);
            return false;
        }

        $data = json_decode($rawResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('AIService JSON decode error: ' . json_last_error_msg());
            return false;
        }

        if ($httpCode !== 200) {
            $errMsg = $data['error']['message'] ?? $rawResponse;
            error_log('AI API error (HTTP ' . $httpCode . '): ' . $errMsg);
            return false;
        }

        return $data['choices'][0]['message']['content'] ?? false;
    }

    /**
     * Build a system-prompt string for the IT support bot.
     */
    public function getSystemPrompt(string $botName): string {
        return sprintf(
            "You are %s, a friendly and knowledgeable IT support assistant. "
            . "Your job is to help users troubleshoot IT problems — hardware, "
            . "software, network, printer, and CCTV issues. "
            . "You are backed by a company knowledge base of error codes, "
            . "troubleshooting procedures, and device references. "
            . "Always respond in clear, conversational English. "
            . "Format code/commands in backticks, use bullet lists for steps, "
            . "and bold important terms. Keep responses practical and actionable. "
            . "If you are unsure, ask a clarifying question.",
            $botName
        );
    }
}
