<?php

declare(strict_types = 1);

namespace Teftely\Components;

use Teftely\Models\Event;
use Teftely\Models\Story\Chapter;
use Throwable;

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

    public function handle(): ?string
    {
        $url = $this->request->get('_url');
        if ($url === '/webhook') {
            try {
                $data = $this->request->getData();
                $this->eventManager->createMessage($data);

                return $this->eventManager->getResponse($data);
            } catch (Throwable $throwable) {
                $this->logger->log(Logger::INFO, $throwable->getMessage(), $throwable->getTrace());

                return 'OK';
            }
        } else {
            $chapters = Chapter::findList($this->database);

            $pages = [];
            $page = 1;
            $pageText = '';
            foreach ($chapters as $chapter) {
                if (mb_strlen($pageText) >= 1000) {
                    $pageText = '';
                    $page++;
                }
                $pageText .= $chapter->getChapter();
                $pages[$page][] = $chapter;
            }

            header('Content-Type: text/html');
            include BASE_PATH . 'template/layout.php';

            return null;
        }
    }

    public function schedule(): void
    {
        do {
            try {
                $time = date('H:i:00', strtotime('+3 hours'));
                $week = (string) date('w', strtotime(date('Y-m-d ') . $time));
                print "Sending events for time $time" . PHP_EOL;

                $peersEvents = Event::findListActive($this->database, $time);
                $foundPE = count($peersEvents);
                print "Found {$foundPE} events" . PHP_EOL;

                foreach ($peersEvents as $eventId => $events) {
                    foreach ($events as $peerId => $event) {
                        try {
                            $eventWeek = $event->getWeek();
                            if (null !== $eventWeek && $week !== $eventWeek) {
                                print "Skipping event #$eventId: wrong weekday" . PHP_EOL;
                                continue;
                            }

                            print "Sending event #$eventId" . PHP_EOL;
                            $params = [
                                'peer_id' => $event->getPeerId(),
                                'message' => $event->getMessage(),
                            ];
                            if ($event->getAttachment()) {
                                $params['attachment'] = $event->getAttachment();
                            }
                            $this->response->send($this->config->get(Config::VK_CONFIG), Message::METHOD_SEND, $params);
                            $this->logger->log(Logger::INFO, 'sent event', $params);
                        } catch (Throwable $throwable) {
                            $this->logger->log(Logger::INFO, $throwable->getMessage(), $throwable->getTrace());
                        }
                    }
                }
                print 'All events sent' . PHP_EOL;
            } catch (Throwable $throwable) {
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
            $response = $message->send($this->config->get(Config::VK_CONFIG), $this->database, $this->response);
            if (!empty($response)) {
                $this->logger->log(Logger::INFO, $response);
            }
        } catch (Throwable $throwable) {
            $this->logger->log(Logger::INFO, $throwable->getMessage(), $throwable->getTrace());
        }
    }
}
