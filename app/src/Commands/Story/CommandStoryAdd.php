<?php

declare(strict_types = 1);

namespace Teftely\Commands\Story;

use Teftely\Commands\Command;
use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Components\Helper;
use Teftely\Models\Story\Chapter;
use Teftely\Models\User;

class CommandStoryAdd extends Command
{
    public const MIN_LENGTH = 100;
    public const MAX_LENGTH = 350;

    public function run(Config $vkConfig, Database $database): void
    {
        $user = User::findOrCreate($database, $this->payload->getFromId());

        $message = 'Не введен текст истории';
        if (!empty($this->payload->getPayload())) {
            $text = $this->payload->getPayload();
            if (is_string($text)) {
                $lastChapter = Chapter::findLast($database);
                if (null !== $lastChapter && false === $lastChapter->isPublished()) {
                    $message = "Есть неактивная история #{$lastChapter->getId()}, необходимо проголосовать";
                } else {
                    $length = mb_strlen($text);
                    if (self::MIN_LENGTH > $length || self::MAX_LENGTH < $length) {
                        $message = 'Текст должен быть длиннее ' . self::MIN_LENGTH;
                        $message .= ' и короче ' . self::MAX_LENGTH;
                    } else {
                        $requiredVotes = CommandStoryVoteUp::VOTES_REQUIRED;
                        $chapter = Chapter::createOne($database, [
                            'from_id' => $user->getFromId(),
                            'chapter' => $text,
                        ]);
                        $message = "История #{$chapter->getId()} создана.\n";
                        $plural = Helper::plural($requiredVotes, ['голос', 'голоса', 'голосов']);
                        $message .= "Требуется минимум {$requiredVotes} {$plural} для публикации.\n";
                        $message .= '/story_up <ID> или /story_down <ID> для голосования';
                    }
                }
            }
        }

        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = $message;
    }
}
