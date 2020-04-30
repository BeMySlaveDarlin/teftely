<?php

declare(strict_types = 1);

namespace Teftely\Components;

use Teftely\Models\Event;

class Application
{
    private Config $config;
    private Database $database;
    private Request $request;
    private Response $response;
    private Logger $logger;
    private EventManager $eventManager;

    public function __construct(Config $config, Database $database)
    {
        $this->config = $config;
        $this->database = $database;

        $this->response = new Response();
        $this->request = new Request();
        $this->logger = new Logger();

        $this->eventManager = new EventManager($config);
    }

    public function handle(): string
    {
        try {
            $data = $this->request->getData();
            $this->eventManager->createMessage($data);

            return $this->eventManager->getResponse($data);
        } catch (\Throwable $throwable) {
            $this->logger->log(Logger::INFO, $throwable->getMessage());

            return 'OK';
        }
    }

    public function schedule(): void
    {
        $time = date('H:i:00', strtotime('+3 hours'));
        print "Sending events for time $time" . PHP_EOL;

        $peersEvents = Event::getListActive($this->database, $time);
        foreach ($peersEvents as $eventId => $event) {
            print "Sending event #$eventId" . PHP_EOL;

            $params = [
                'peer_id' => $event->getPeerId(),
                'message' => $event->getFormattedMessage($peersEvents),
            ];
            if (null !== $event->getAttachment()) {
                $params['attachment'] = $event->getAttachment();
            }
            $this->response->send($this->config->get(Config::VK_CONFIG), Message::METHOD_SEND, $params);
        }
    }

    public function sendMessage(array $argv): void
    {
        try {
            /** @var Message $message */
            $message = EventManager::unpack($argv[1]);
            $message->send($this->config->get(Config::VK_CONFIG), $this->database, $this->response);
        } catch (\Throwable $throwable) {
            $this->logger->log(Logger::INFO, $throwable->getMessage());
        }
    }
}
