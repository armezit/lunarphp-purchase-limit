<?php

namespace Armezit\Lunar\PurchaseLimit\Commands;

use Illuminate\Console\Command;

class ListPurchaseLimits extends Command
{
    public $signature = 'lunar:purchase-limit:list';

    public $description = 'List all defined purchase limits';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
