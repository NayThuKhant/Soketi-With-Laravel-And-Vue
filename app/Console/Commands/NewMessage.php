<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\NewMessage as NewMessageEvent;

class NewMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'new:message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        broadcast(new NewMessageEvent(\Illuminate\Support\Str::random(), "Anonymous user just sent a message from the server"));
    }
}
