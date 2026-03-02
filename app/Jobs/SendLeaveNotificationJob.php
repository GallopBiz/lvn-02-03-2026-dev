<?php

namespace App\Jobs;

use App\Mail\LeaveAppliedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
class SendLeaveNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $leaveDetails;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($leaveDetails)
    {
        $this->leaveDetails = $leaveDetails;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->leaveDetails['employee_email'])
        ->send(new LeaveAppliedNotification($this->leaveDetails));
    }
}
