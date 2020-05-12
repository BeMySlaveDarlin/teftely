<?php

declare(strict_types = 1);

namespace Teftely\Commands\Story;

use Teftely\Commands\Command;
use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Models\Story\Chapter;
use Teftely\Models\User;

class CommandStoryDel extends Command
{
    public function run(Config $vkConfig, Database $database): void
    {
        $user = User::findOrCreate($database, $this->payload->getFromId());

        $chapterId = $this->payload->getPayload();
        $message = 'ID истории должен быть корректным';
        if (is_numeric($chapterId)) {
            $chapter = Chapter::findOne($database, $chapterId);
            if (null === $chapter) {
                $message = 'История не найдена';
            } elseif ($chapter->delete($user->getFromId())) {
                $message = "История #{$chapter->getId()} удалена";
            } else {
                $message = 'Вы не можете удалить эту историю';
            }
        }

        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = $message;
    }
}
