<?php

declare(strict_types = 1);

namespace Teftely\Actions;

use Teftely\Messages\Message;

class Index extends AbstractAction
{
    public function run(): void
    {
        $this->response->setContent(new Message([
            'Nothing to do',
        ]));
    }
}
