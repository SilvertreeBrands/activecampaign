<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model;

interface SyncInterface
{
    /**
     * Iterator callback
     *
     * @param array $args
     *
     * @return void
     */
    public function iteratorCallback(array $args): void;

    /**
     * Sync
     *
     * @param mixed $model
     *
     * @return mixed
     */
    public function sync(mixed $model): mixed;
}
