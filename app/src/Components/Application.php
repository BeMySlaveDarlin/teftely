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
            $this->logger->log(Logger::INFO, $throwable->getMessage(), $throwable->getTrace());

            return 'OK';
        }
    }

    public function schedule(): void
    {
        do {
            try {
                $time = date('H:i:00', strtotime('+3 hours'));
                print "Sending events for time $time" . PHP_EOL;

                $peersEvents = Event::getListActive($this->database, $time);
                $foundPE = count($peersEvents);
                print "Found {$foundPE} events" . PHP_EOL;

                foreach ($peersEvents as $eventId => $event) {
                    try {
                        print "Sending event #$eventId" . PHP_EOL;
                        $params = [
                            'peer_id' => $event->getPeerId(),
                            'message' => $event->getMessage(),
                        ];
                        if ($event->getAttachment()) {
                            $params['attachment'] = $event->getAttachment();
                        }
                        $this->logger->log(Logger::INFO, 'sent event', $params);
                        $this->response->send($this->config->get(Config::VK_CONFIG), Message::METHOD_SEND, $params);
                    } catch (\Throwable $throwable) {
                        $this->logger->log(Logger::INFO, $throwable->getMessage(), $throwable->getTrace());
                    }
                }
                print 'All events sent' . PHP_EOL;
            } catch (\Throwable $throwable) {
                $this->logger->log(Logger::INFO, $throwable->getMessage(), $throwable->getTrace());
            }

            sleep(60);
        } while (true);
    }

    public function sendMessage(array $argv): void
    {
        try {
            /** @var Message $message */
            $message = EventManager::unpack($argv[1]);
            $message->send($this->config->get(Config::VK_CONFIG), $this->database, $this->response);
        } catch (\Throwable $throwable) {
            $this->logger->log(Logger::INFO, $throwable->getMessage(), $throwable->getTrace());
        }
    }
}
