<?php

/**
 * Created by jcuna.
 * Date: 9/21/18
 * Time: 5:25 PM
 */

declare(strict_types=1);

namespace Jcuna\ApiKeys\Cli;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Jcuna\ApiKeys\Models\ApiKey;

class ApiCommands extends Command
{
    /**
     * @var array
     */
    private $allowActions = ['new', 'ls', 'expire'];

    /**
     * @var string
     */
    protected $signature = 'api:client {action} {--all}';

    /**
     * @var array
     */
    private $clientData = [
        'app_name' => 'Please enter a name for this client.',
        'origin_url' => 'Enter the origin url for this client',
        'access_map' => '(Optional) Enter a comma delimited list of target API URLs',
        'expires_at' => '(Optional) Enter an expiration date like (yyyy-mm-dd) or another valid date/time string'
    ];

    /**
     * @var array
     */
    private $requiredFields = ['key', 'app_name', 'origin_url'];

    public function handle(): void
    {
        $action = $this->argument('action');

        if (! in_array($action, $this->allowActions)) {
            $this->error(
                "{$action} not allowed, valid options are: " . implode(', ', $this->allowActions)
            );
            exit(1);
        }

        switch ($action) {
            case $this->allowActions[0]:
                $this->newClient();
                break;
            case $this->allowActions[1]:
                $this->listClients();
                break;
            default:
                $this->expireClient();
        }
    }

    private function newClient(): void
    {
        foreach ($this->clientData as $field => $message) {
            $this->enterFieldData($field, $message);
        }

        $api = new ApiKey($this->clientData);
        $api->save();

        $this->info($api->toJson(JSON_PRETTY_PRINT));
    }

    /**
     * @param string $field
     * @param string $message
     */
    private function enterFieldData(string $field, string $message): void
    {
        $this->clientData[$field] = $this->ask($message);

        if (in_array($field, $this->requiredFields) && is_null($this->clientData[$field])) {
            call_user_func([$this, __FUNCTION__], ...[$field, $message]);
        }
    }

    private function listClients(): void
    {
        if ($this->option('all')) {
            $clients = ApiKey::all();
        } else {
            $clients = ApiKey::where('expires_at', '>', DB::raw('NOW()'))->get();
        }

        $this->info(
            $clients->toJson(JSON_PRETTY_PRINT)
        );
    }

    private function expireClient(): void
    {
        $input = $this->ask('Enter either the key or the app name of the client you wish to expire');

        $column = 'app_name';
        if (ctype_xdigit($input) && strlen($input) === 64) {
            $column = 'key';
        }

        ApiKey::where($column, $input)->update(
            ['expires_at' => DB::raw('NOW()')]
        );

        $this->info('Boom!');
    }
}
