<?php

namespace App\POS\Listeners;

use App\Events\InfraSheetUploaded;
use App\POS\Facades\POS;
use Carbon\Carbon;

class CounterpointEventSubscriber
{
    /**
     * Handle a newly uploaded InfraSheet.
     *
     * @param InfraSheetUploaded $event
     */
    public function handleInfraSheetUploaded(InfraSheetUploaded $event)
    {
        POS::initializeInfraSale($event->infrasheet);

        // Initialize in cp database
        // Parse sales
        // Pre-process sales
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @return array
     */
    public function subscribe()
    {
        if (config('pos.driver') == 'counterpoint') {
            return [
                InfraSheetUploaded::class => [
                    [CounterpointEventListener::class, 'handleInfraSheetUploaded']
                ],
            ];
        }

        return [];
    }
}