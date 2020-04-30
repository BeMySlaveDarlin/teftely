<?php

declare(strict_types = 1);

namespace Teftely\Components;

class Application
{
    private Config $config;
    private Request $request;
    private Response $response;
    private Database $database;
    private Logger $logger;

    public function __construct(string $configPath)
    {
        $this->config = require $configPath;
        $this->request = new Request();
        $this->response = new Response();
        $this->database = new Database($this->config->get(Config::DB_CONFIG));
        $this->logger = new Logger();
    }

    public function handle(): void
    {
        try {
            $eventManager = $this->request->event($this->config->get('secret'));
            $eventHandler = $eventManager->getEvent();

            $eventHandler->setDatabase($this->database);
            $eventHandler->setConfig($this->config->get(Config::VK_CONFIG));
            $eventHandler->setData($eventManager->getData());

            $this->response->setEventHandler($eventHandler);
            $response = $this->response->send();
        } catch (\Throwable $throwable) {
            $this->logger->log(Logger::INFO, $throwable->getMessage());
            $response = 'OK';
        }

        echo $response;
    }

    public function queue(): void
    {
    }
}
