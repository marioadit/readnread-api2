<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

class PruneExpiredTokens extends Command
{
    protected $signature = 'auth:prune-expired-tokens';
    protected $description = 'Delete expired sanctum tokens';

    public function handle()
    {
        $expiredTokens = PersonalAccessToken::where('expires_at', '<', now('Asia/Jakarta'))->delete();
        $this->info('Expired tokens have been pruned.');
    }
} 