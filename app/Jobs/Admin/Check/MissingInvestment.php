<?php

namespace App\Jobs\Admin\Check;

use App\Payment;
use App\Project;
use App\Investment;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class MissingInvestment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $projects = Project::where('status', 3)->get();
      $missmatches = [];

      foreach ($projects as $project) {

        $investments = [];
        $missmatch = [];
        // $payments = [];
        // dd($project->payments->where('status', 'reserved'));
        foreach ($project->payments->where('status', 'confirmed') as $payment) {
           $userID = $payment->user_id;
           $message = 'OK';

           $investments[$userID]['total_payments'] = 0;
           $investments[$userID]['payments'][] = $payment->investment;

           foreach ($investments[$userID]['payments'] as $insidePayment) {
             $investments[$userID]['total_payments'] += $insidePayment;
           }

           $investment = Investment::where('project_id', $project->id)
                                    ->where('user_id', $payment->user_id)
                                    ->first();

            if(!$investment) { // Investment not found, send log
              // dd('nerasta'.$payment->user_id);
              $match = false;
              $investment = 0;
              $message = 'investment not found';
            } else { // Investment found, check if SUM is OK
              $match = ($investments[$userID]['total_payments'] === $investment->amount) ? true : false;
              $investment = $investment->amount;
              if(!$match) { $message = 'investment missmatch'; }
            }


           $investments[$userID]['investment'] = $investment;
           $investments[$userID]['match'] = $match;
           $investments[$userID]['message'] = $message;
           $investments[$userID]['project_id'] = $project->id;
           $investments[$userID]['user_id'] = $payment->user_id;

        }

        foreach ($investments as $investment) {
          if(!$investment['match']) {
            $missmatch[] = $investment;
          }
        }

       if(count($missmatch) > 0) {$missmatches[] = $missmatch;}


      }
    }
}
