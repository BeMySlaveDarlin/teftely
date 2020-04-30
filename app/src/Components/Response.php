<?php

declare(strict_types = 1);

namespace Teftely\Components;

class Response
{
    public function send(Config $vkConfig, string $method, array $params): string
    {
        $params['access_token'] = $vkConfig->get('access_token');
        $params['v'] = $vkConfig->get('api_version');
        $params['random_id'] = $this->getRandomId();
        if (isset($params['peer_id']) && null === $params['peer_id']) {
            throw new \RuntimeException("[$method] peer_id is not defined");
        }

        $query = http_build_query($params);
        $url = $vkConfig->get('api_endpoint') . $method . '?' . $query;

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

    private function getRandomId(): string
    {
        $base = time();
        $randPercent = random_int(1000, 9999);

        return $base . $randPercent;
    }
}
