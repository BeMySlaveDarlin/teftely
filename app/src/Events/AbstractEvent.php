<?php

declare(strict_types = 1);

namespace Teftely\Events;

use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Components\Message;

class AbstractEvent
{
    protected Config $config;
    protected Database $db;

    protected ?object $eventData = null;
    protected ?object $message = null;

    public function setData(?object $eventData = null): void
    {
        $this->eventData = $eventData;

        $secret = $this->eventData->secret ?? null;
        if ($this->config->get('secret') === $secret) {
            $message = $this->eventData->object->message ?? null;
            if (null !== $message) {
                $this->message = new Message($message, $this->db, $this->config);
                $this->message->resolve();
            }
        }
    }

    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    public function setDatabase(Database $database): void
    {
        $this->db = $database;
    }

    public function sendMessage(): void
    {
        return;
    }

    protected function send(string $method, array $params): string
    {
        $params['access_token'] = $this->config->get('access_token');
        $params['v'] = $this->config->get('api_version');
        $params['random_id'] = $this->getRandomId();
        if (isset($params['peer_id']) && null === $params['peer_id']) {
            throw new \RuntimeException("[$method] peer_id is not defined");
        }

        $query = http_build_query($params);
        $url = $this->config->get('api_endpoint') . $method . '?' . $query;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($curl);

        $error = curl_error($curl);
        if ($error) {
            throw new \RuntimeException("Failed [$method] request: $error");
        }

        curl_close($curl);
        $response = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!$response || !isset($response['response'])) {
            throw new \RuntimeException("Invalid response for [$method] request: $json");
        }

        return $json;
    }

    public function getResponseData(): ?string
    {
        return 'OK';
    }

    private function getRandomId(): string
    {
        $base = time();
        $randPercent = random_int(1000, 9999);

        return $base . $randPercent;
    }
}
