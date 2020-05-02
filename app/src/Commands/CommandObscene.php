<?php

declare(strict_types = 1);

namespace Teftely\Commands;

use Teftely\Components\Config;
use Teftely\Components\Database;
use Wkhooy\ObsceneCensorRus;

class CommandObscene extends Command
{
    private const MESSAGES = [
        'Не матерись, сука!',
        'Мне показалось, или кто-то пукнул в чате?',
        "Бан!... \nа бл* я же не админ",
        'С@си цацу, рот поганый!',
        'Сидели культурно и тут такое...',
        'Чай остывает, пока ты матюгаешься',
        'Ссаными тряпками из чата грязноротиков!',
        'Не матюгнешься - по попке не получишь...',
        "Утро вечера...\nкак там это слово, а...\nХ#ЕВЕЕ",
        'Захожу в чат, а вы снова о своем',
    ];

    public function run(Config $vkConfig, Database $database): void
    {
        $rnd = random_int(0, 9);
        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = self::MESSAGES[$rnd];
    }

    public static function check(string $text): bool
    {
        return false === ObsceneCensorRus::isAllowed($text)
            || false !== stripos(mb_strtolower($text), 'цаца')
            || false !== stripos(mb_strtolower($text), 'цацы');
    }
}
