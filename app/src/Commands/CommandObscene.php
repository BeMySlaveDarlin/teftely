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
        "Утро вечера...\nкак там это слово, а...\nХ%ЕВЕЕ",
        'Захожу в чат, а вы снова о своем',
        'Пельмешек прикрой, бесстыдница!',
        'Запихни свою цацу себе в цацу через цацу!',
    ];

    public function run(Config $vkConfig, Database $database): void
    {
        $rnd = random_int(0, count(self::MESSAGES));
        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = self::MESSAGES[$rnd];
    }

    public static function check(string $text): bool
    {
        ObsceneCensorRus::$exceptions[] = 'бубля';
        $isObscene = false === ObsceneCensorRus::isAllowed($text);
        if ($isObscene) {
            $isObscene = false === (bool) random_int(0, 10);
        }
        $isCaca = false !== stripos(mb_strtolower($text), 'цац');

        return $isObscene || $isCaca;
    }
}
