<?php

namespace Quadrubo\EloquentAutosort\Commands;

use Illuminate\Console\Command;

class EloquentAutosortCommand extends Command
{
    public $signature = 'eloquent-autosort';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
