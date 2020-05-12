<?php

declare(strict_types = 1);

namespace Teftely\Commands\Story;

use Teftely\Commands\Command;
use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Models\Story\Chapter;
use Teftely\Models\User;

class CommandStory extends Command
{
    public const DESCRIPTIONS = [
        self::COMMAND_STORY . ' <ID>' => 'вспомнить историю по ID',
        self::COMMAND_STORY_ADD . ' <TEXT>' => 'добавить свою историю',
        self::COMMAND_STORY_DEL . ' <ID>' => 'удалить свою историю',
        self::COMMAND_STORY_UP . ' <ID>' => 'проголосовать ЗА историю',
        self::COMMAND_STORY_DOWN . ' <ID>' => 'проголосовать ПРОТИВ истории',
        self::COMMAND_STORY_LAST => 'последняя история',
    ];

    public function run(Config $vkConfig, Database $database): void
    {
        $user = User::findOrCreate($database, $this->payload->getFromId());

        $chapterId = $this->payload->getPayload();
        if (is_numeric($chapterId)) {
            $chapter = Chapter::findOne($database, $chapterId);
            if (null === $chapter) {
                $message = "История #{$chapterId} не найдена";
            } else {
                $message = "История #{$chapter->getId()} от: {$chapter->getAuthor()}\n";
                $message .= 'Статус: ' . ($chapter->isPublished() ? '&#9989;' : '&#128721;') . "\n\n";
                $message .= $chapter->getChapter();
            }
        } else {
            $message = "Доступные команды:\n\n";
            foreach (self::DESCRIPTIONS as $command => $description) {
                $message .= $command . ' - ' . $description . "\n";
            }

            $message .= "\nПравила создания историй:\n";
            $message .= "1. Одновременно может быть создана только одна история.\n";
            $message .= "2. Пока вакантная история не получит 2 голоса ЗА или 2 голоса ПРОТИВ в сумме, новую нельзя будет создать.\n";
            $message .= "3. Вы не можете проголосовать на своей истории.\n";
            $message .= "4. Вы не можете проголосовать два раза за одну историю.\n";
            $message .= "4. Все опубликованные истории находятся по адресу http://teftely.bemyslavedarlin.ru/.\n";
        }

        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = $message;
    }
}
