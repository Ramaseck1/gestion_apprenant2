<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendGradeReports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */ protected $promotion;

    public function __construct(Promotion $promotion)
    {
        $this->promotion = $promotion;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
