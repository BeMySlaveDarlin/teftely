<?php

declare(strict_types = 1);

namespace Teftely\Commands;

use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Models\User;

class CommandFortune extends Command
{
    private const ANSWERS = [
        'Бесспорно',
        'Предрешено',
        'Никаких сомнений',
        'Определённо да',
        'Можно уверенно сказать да',
        'Мне кажется — «да»',
        'Вероятнее всего',
        'Хорошие перспективы',
        'Знаки говорят — «да»',
        'Да',
        'Пока не ясно, попробуй снова',
        'Спроси позже',
        'Лучше не рассказывать',
        'Сейчас нельзя предсказать',
        'Сконцентрируйся и спроси опять',
        'Даже не думай',
        'Мой ответ — «нет»',
        'По моим данным — «нет»',
        'Перспективы не очень хорошие',
        'Весьма сомнительно',
        'Ты цаца, вот мой ответ',
        //Нестандартные
        'Думаю нет',
        'Точно нет',
        'Без сомнений',
        'Мало шансов',
        'Есть шансы',
        'Точно нет',
        'Есть сомнения',
        'Возможно',
        'Непонятно',
    ];

    public function run(Config $vkConfig, Database $database): void
    {
        $user = User::findOrCreate($database, $this->payload->getFromId());
        $rnd = random_int(0, count(self::ANSWERS));
        $message = "{$user->getName()}, " . self::ANSWERS[$rnd];

        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = $message;
    }

    public static function check(string $message): bool
    {
        $isBotCall = false !== strpos($message, '@teftely_secret');
        $isQuestion = false !== strpos($message, '?');

        return $isBotCall && $isQuestion;
    }
}
