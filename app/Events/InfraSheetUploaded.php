<?php

namespace App\Events;

use App\InfraSheet;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InfraSheetUploaded
{
    use Dispatchable, SerializesModels;

    /**
     * The infrasheet that was uploaded.
     *
     * @var InfraSheet
     */
    public $infrasheet;

    /**
     * Create a new event instance.
     *
     * @param InfraSheet $infrasheet
     */
    public function __construct(InfraSheet $infrasheet)
    {
        $this->infrasheet = $infrasheet;
    }
}
