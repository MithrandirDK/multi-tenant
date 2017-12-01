<?php

namespace Hyn\Tenancy\Traits;

use Hyn\Tenancy\Contracts\WebsiteRepositoryContract;
use Symfony\Component\Console\Input\InputOption;

trait TenantDatabaseCommandTrait
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getWebsitesFromOption()
    {
        $repository = app(WebsiteRepositoryContract::class);

        if ($this->option('tenant') == 'all') {
            return $repository->queryBuilder()->where('type', '!=', 5)->get();
        } else {
            $ids = explode(',', $this->option('tenant'));
            $websiteIds = [];
            foreach ($ids as $id) {
                if (is_numeric($id)) {
                    $websiteIds[] = $id;
                }
                elseif (strpos($id, '-')) {
                    list($start, $end) = explode('-', $id);
                    for ($i = $start; $i < $end; $i++) {
                        $websiteIds[] = $i;
                    }
                }
            }
            return $repository
                ->queryBuilder()
                ->whereIn('id', $websiteIds)
                ->get();
        }
    }

    /**
     * @return array
     */
    protected function getTenantOption()
    {
        return [['tenant', null, InputOption::VALUE_OPTIONAL, 'The tenant(s) to apply on; use {all|5,8|1-10}']];
    }
}
