<?php

declare(strict_types = 1);

namespace Teftely\Commands;

use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Models\Event;
use Teftely\Models\Peer;

class CommandEvents extends Command
{
    public function run(Config $vkConfig, Database $database): void
    {
        $peer = Peer::findOrCreate($database, $this->payload->getPeerId());
        $peersEvents = Event::findListActive($database, null, $this->payload->getPeerId());

        $command = self::COMMAND_TOGGLE;
        $message = "Бот отключен! События игнорируются! [$command] чтобы включить\n\n";
        if ($peer->isEnabled()) {
            $eventId = $this->payload->getPayload();
            if (!empty($eventId) && is_numeric($eventId)) {
                try {
                    $event = Event::findOne($database, $eventId);
                    if (null === $event) {
                        $message = "Событие #{$eventId} не существует";
                    } else {
                        $message = $event->getFormattedMessage($peersEvents);
                    }

                    $this->params['attachment'] = $event->getAttachment();
                } catch (\Throwable $throwable) {
                    $message = "Событие #$eventId не найдено";
                }
            } else {
                $message = "Список событий:\n\n";
                $events = Event::findList($database);
                foreach ($events as $eventId => $event) {
                    $message .= $event->getFormattedMessage($peersEvents);
                }
            }
        }

        $this->params['message'] = $message;
        $this->params['peer_id'] = $this->payload->getPeerId();
    }
}
