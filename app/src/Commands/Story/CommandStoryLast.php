<?php

declare(strict_types = 1);

namespace Teftely\Commands\Story;

use Teftely\Commands\Command;
use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Models\Story\Chapter;

class CommandStoryLast extends Command
{
    public function run(Config $vkConfig, Database $database): void
    {
        $chapter = Chapter::findLast($database);
        if (null === $chapter) {
            $message = 'История не найдена';
        } else {
            $message = "История #{$chapter->getId()} от: {$chapter->getAuthor()}\n";
            $message .= 'Статус: ' . ($chapter->isPublished() ? '&#9989;' : '&#128721;') . "\n\n";
            $message .= $chapter->getChapter();
        }

        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = $message;
    }
}
