<?php

declare(strict_types = 1);

namespace Teftely\Commands\Story;

use Teftely\Commands\Command;
use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Components\Helper;
use Teftely\Models\Story\Chapter;
use Teftely\Models\User;

class CommandStoryVoteUp extends Command
{
    public const VOTES_REQUIRED = 2;

    public function run(Config $vkConfig, Database $database): void
    {
        $this->vote($database, true);
    }

    protected function vote(Database $database, bool $vote = true): void
    {
        $user = User::findOrCreate($database, $this->payload->getFromId());

        $chapterId = $this->payload->getPayload();
        $message = 'ID истории должен быть корректным';
        if (is_numeric($chapterId)) {
            $chapter = Chapter::findOne($database, $chapterId);
            if (null === $chapter) {
                $message = 'История не найдена';
            } else {
                if ($chapter->isVoted($user->getFromId())) {
                    $message = 'Вы уже голосовали за эту историю';
                } elseif ($chapter->getFromId() === $user->getFromId()) {
                    $message = 'Нельзя голосовать за свою историю';
                } elseif ($chapter->isPublished()) {
                    $message = 'История уже опубликована';
                } elseif ($chapter->vote($user->getFromId(), $vote)) {
                    $message = "Голос успешно засчитан.\n";
                    $votes = (int) $chapter->getVotes();
                    if ($votes === self::VOTES_REQUIRED) {
                        $chapter->publish();
                        $message .= 'Ура! История опубликована!';
                    } elseif ($votes === -self::VOTES_REQUIRED) {
                        $chapter->delete($chapter->getFromId());
                        $message .= 'Жаль, история была удалена из-за низкого рейтинга';
                    } else {
                        $requiredVotesUp = abs(self::VOTES_REQUIRED - $votes);
                        $requiredVotesDown = self::VOTES_REQUIRED + $votes;
                        $message .= "Рейтинг истории: {$votes}. Требуется:\n";

                        $plural = Helper::plural($requiredVotesUp, ['лайк', 'лайка', 'лайков']);
                        $message .= "{$requiredVotesUp} {$plural}, чтобы опубликовать\n";

                        $plural = Helper::plural($requiredVotesDown, ['дизлайк', 'дизлайка', 'дизлайков']);
                        $message .= "{$requiredVotesDown} {$plural}, чтобы удалить\n";
                    }
                }
            }
        }

        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = $message;
    }
}
