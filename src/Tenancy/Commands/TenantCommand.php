<?php

namespace Hyn\Tenancy\Commands;

use Hyn\Tenancy\Traits\TenantDatabaseCommandTrait;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\DB;

class TenantCommand extends Command
{
    use TenantDatabaseCommandTrait;

    /**
     * @var string
     */
    protected $signature = 'multi-tenant:run {tenantcommand}
        {--tenant= : The tenant(s) to apply on; use {all|5,8}}
        {--arguments= : Arguments for the delegated command} 
        {--options= : Options to pass on to the delegated command}
    ';

    /**
     * @var string
     */
    protected $description = 'Run another artisan command in a tenant configuration';

    /**
     * Delegate command to tenants.
     */
    public function handle()
    {
        $websites = $this->getWebsitesFromOption();

        $newArgv = array('artisan', $this->argument('tenantcommand'));
        if ($arguments = $this->option('arguments')) {
            $newArgv = array_merge($newArgv, explode(' ', trim($arguments)));
        }
        if ($options = $this->option('options')) {
            $newArgv = array_merge($newArgv, explode(' ', trim($options)));
        }

        $this->output->progressStart(count($websites));
        $tenantApp = require base_path('bootstrap') . '/app.php';
        foreach ($websites as $website) {
            putenv('TENANT=' . $website->id);
            $kernel = $tenantApp->make(Kernel::class);

            $status = $kernel->handle(
              $input = new ArgvInput($newArgv),
              new ConsoleOutput
            );
            $kernel->terminate($input, $status);
            $this->output->progressAdvance();
            DB::disconnect('tenant');
            DB::disconnect('hyn');
        }
        $this->output->progressFinish();
    }
}
