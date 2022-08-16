<?php

namespace Armezit\GetCandy\PurchaseLimit\Commands;

use Illuminate\Console\Command;

class ListPurchaseLimits extends Command
{
    public $signature = 'getcandy:purchase-limit:list';

    public $description = 'List all defined purchase limits';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
