<?php

declare(strict_types = 1);

namespace Teftely\Commands;

use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Models\Event;
use Teftely\Models\Peer;
use Teftely\Models\User;

class CommandUnsubscribe extends Command
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
            if (isset($peersEvents[$eventId])) {
                $event->disable($peerId);
            }
            $message = "Событие #$eventId отключено";
        } else {
            $message = 'Только админ или модел может переключать события в чате';
        }

        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = $message;
    }
}
