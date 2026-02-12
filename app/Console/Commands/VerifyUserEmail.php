<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class VerifyUserEmail extends Command
{
    protected $signature = 'user:verify-email {email}';
    protected $description = 'Manually verify a user email';

    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found!");
            return 1;
        }

        if ($user->hasVerifiedEmail()) {
            $this->info("Email already verified for {$email}");
            return 0;
        }

        $user->markEmailAsVerified();
        
        $this->info("Email verified successfully for {$email}");
        return 0;
    }
}