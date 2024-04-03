<?php

namespace App\Console\Commands;

use App\Mail\PriceReceivedEmail;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class Sorteo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sorteo:random {prices_file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign randomly one price to the file to one participant';

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
        $prices_file = $this->argument('prices_file');

        $handle = fopen($prices_file, "r");

        if ($handle === false) {
            return;
        }

        $playersQuery = DB::table('game_player')->get();
        $participants = [];
        foreach ($playersQuery as $player) {
	        $participants[$player->player_id] = uniqid();
        }

        $gamesQuery = DB::table('games')->get();
        foreach ($gamesQuery as $game) {
        	if ($game->approved) {
        	    $participants[$game->owner_id] = uniqid();
	        }
        }

        $participants = array_flip($participants);

        $count = 0;
        $keys = [];
        $new_prices = [];

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

        	if (!count($participants)) {
        		break;
	        }

            if ($count === 0) {
                $keys = $data;
                $count++;
                continue;
            }

            $price = array_combine($keys, $data);

            $participant_key = array_rand($participants);


			$participant = $participants[$participant_key];
            unset($participants[$participant_key]);
            $new_prices[] = [
                'user_id' => (int) $participant,
                'description' => "Gentileza de <strong>" . $price["Colaborador"] . "</strong>: " . $price["Premio"],
                'code' => 'premio' . (int) trim($price['ID']) . uniqid(),
            ];

            $user = User::find($participant);
            Mail::to($user)->send(new PriceReceivedEmail($user));
        }

        fclose($handle);

        DB::table('prices')->insert($new_prices);
    }
}
