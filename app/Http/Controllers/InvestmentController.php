<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Payment;
use App\Project;
use App\Investment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Admin\RunningQueue;
use App\Notifications\Admin\Check\MissingInvestment;

class InvestmentController extends Controller
{
    /**
     * Checks if there is a paysera reserved transaction that doesn't have a created investment.
     * Sometimes after transaction is reserved the system doesnt create an investment for user.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

      Notification::route('slack', env('LOG_SLACK_WEBHOOK_URL'))
                   ->notify(new RunningQueue('MissingInvestment', 0));

      $projects = Project::where('status', 2)->get();
      $missmatches = [];

      foreach ($projects as $project) {

        $investments = [];
        $missmatch = [];
        // $payments = [];
        // dd($project->payments->where('status', 'reserved'));
        foreach ($project->payments->where('status', 'reserved') as $payment) {
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
              $message = 'Investment not found, fix immediately!';
              $investments[$userID]['type'] = 'error';
            } else { // Investment found, check if SUM is OK
              $match = ($investments[$userID]['total_payments'] === $investment->amount) ? true : false;
              $investment = $investment->amount;
              if(!$match) {
                $message = 'Investment missmatch, check if manual payment was done.';
                $investments[$userID]['type'] = 'warning';
               }
            }


           $investments[$userID]['investment'] = $investment;
           $investments[$userID]['match'] = $match;
           $investments[$userID]['message'] = $message;
           $investments[$userID]['project'] = $project;
           $investments[$userID]['user'] = $payment->user;

             // dump($missmatch);
        }

        foreach ($investments as $investment) {

          if(!$investment['match']) {
            $missmatch[] = $investment;
            Notification::route('slack', env('LOG_SLACK_WEBHOOK_URL'))
                         ->notify(new MissingInvestment($investment));


          }
        }
        // break;
       if(count($missmatch) > 0) {$missmatches[] = $missmatch;}


      }

      Notification::route('slack', env('LOG_SLACK_WEBHOOK_URL'))
                   ->notify(new RunningQueue('MissingInvestment', 1));


    }

    // public function logging() {
    //   Notification::route('slack', env('LOG_SLACK_WEBHOOK_URL'))
    //                ->notify(new RunningQueue('MissingInvestment', 1));
    // }

}
