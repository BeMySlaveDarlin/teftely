<?php

declare(strict_types = 1);

namespace Teftely\Commands\Events;

use Teftely\Commands\Command;
use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Models\Event;
use Teftely\Models\Peer;
use Teftely\Models\User;

class CommandSubscribe extends Command
{
    public function run(Config $vkConfig, Database $database): void
    {
        $user = User::findOrCreate($database, $this->payload->getFromId());
        $peer = Peer::findOrCreate($database, $this->payload->getPeerId());
        $events = Event::findList($database);
        $peersEvents = Event::findListActive($database, null, $this->payload->getPeerId());

        if ($user->isModerator()) {
            $eventId = (int) $this->payload->getPayload();
            /** @var Event $event */
            $event = $events[$eventId];
            $peerId = $peer->getPeerId();
            if (!isset($peersEvents[$eventId])) {
                $event->enable($peerId);
            }
            $message = "Событие #$eventId включено";
        } else {
            $message = 'Только админ или модер может переключать события в чате';
        }

        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = $message;
    }
}
