<?php

declare(strict_types=1);

namespace Src\Shared\Application\Bus;

use Closure;
use Illuminate\Support\Facades\DB;

class UseDatabaseTransactions
{
    public function handle(mixed $command, Closure $next): void
    {
        DB::transaction(function () use ($command, $next) {
            return $next($command);
        });
    }
}
