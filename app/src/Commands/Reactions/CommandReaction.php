<?php

declare(strict_types = 1);

namespace Teftely\Commands\Reactions;

use Teftely\Commands\Command;
use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Models\Peer;
use Teftely\Models\Reaction;
use Teftely\Models\User;

class CommandReaction extends Command
{
    public function run(Config $vkConfig, Database $database): void
    {
        $user = User::findOrCreate($database, $this->payload->getFromId());
        $peer = Peer::findOrCreate($database, $this->payload->getPeerId());
        $reaction = Reaction::findOne($database, $this->payload->getPayload());
        if (null !== $reaction && $reaction->isEnabled() && $peer->isEnabled()) {
            $message = $reaction->getMessage($user);
            if (null !== $message) {
                $this->params['message'] = $message;
            }
            if (null !== $reaction->getAttachment()) {
                $this->params['attachment'] = $reaction->getAttachment();
            }
        }

        $this->params['peer_id'] = $this->payload->getPeerId();
    }
}
