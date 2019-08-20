<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Passport\Client;

class SetupClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tortuga:setup {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Init set up of new client';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $clientName = $this->argument('name');
        $slug       = Str::slug($clientName);
        $name       = Str::ucfirst($clientName);
        $email      = "$slug@mail.tatrgel.cz";

        $user = User::where('email', '=', $email)->first();

        // create user
        if (!$user) {
            $password = Str::random(32);

            // create user
            $user = User::create([
                'email'    => $email,
                'name'     => $name,
                'password' => Hash::make($password),
            ]);

            $this->info("Created user name: $name, email: $email, password: $password");
            $this->info('Jot the password down, you will not have a chance to get it again.');
        } else {
            $this->info("User $name with email $email already exists - using that.");
        }

        $this->info('Using user ID: ' . $user->id);

        // create oauth client
        $client = Client::where('user_id', '=', $user->id)->first();
        if (!$client) {
            $this->call('passport:client', [
                '--password' => true,
            ]);

            $client = Client::where('user_id', '=', $user->id)->first();
            $this->info("Created Client with user_id " . $client->user_id);
        } else {
            $this->info("Found an OAuth Client with user_id " . $client->user_id);
        }

        $this->info('All set.');
    }
}
