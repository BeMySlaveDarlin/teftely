<?php

declare(strict_types = 1);

namespace Teftely\Components;

use Teftely\Actions\AbstractAction;
use Teftely\Actions\Index;

class Application
{
    public const ACTION_NAMESPACE = 'Teftely\\Actions\\';

    private Config $config;
    private Request $request;
    private Response $response;

    public function __construct(string $configPath)
    {
        $this->config = require $configPath;
        $this->request = new Request();
        $this->response = new Response();
    }

    public function handle(): void
    {
        try {
            $uri = $this->request->getRequest('_url');
            $actionClass = Index::class;
            if (!empty($uri)) {
                $actionString = self::ACTION_NAMESPACE . ucfirst(trim($uri, '/ '));
                if (class_exists($actionString)) {
                    $actionClass = $actionString;
                }
            }

            /** @var AbstractAction $action */
            $action = new $actionClass($this->config, $this->request, $this->response);
            $action->run();
        } catch (\Throwable $throwable) {
            $this->response->setContent(null);
        }

        $this->response->send();
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
