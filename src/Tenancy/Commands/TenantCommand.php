<?php

namespace Hyn\Tenancy\Commands;

use Hyn\Tenancy\Traits\TenantDatabaseCommandTrait;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

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
        {--timeout=600 : Timeout for each tenant process }
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
        if (!$websites->count()) {
            return;
        }

        $this->output->progressStart(count($websites));
        foreach ($websites as $website) {
            $process = new Process('php artisan ' . $this->argument('tenantcommand') . ' ' . $this->option('arguments') . ' ' . $this->option('options'),
                                   null,
                                   ['TENANT' => $website->id],
                                    null,
                                    $this->option('timeout'));
            $process->run();
            if (!$process->isSuccessful()) {
                $this->comment($process->getExitCodeText());
                continue;
            }
            $this->comment($process->getOutput());

            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
    }
}
