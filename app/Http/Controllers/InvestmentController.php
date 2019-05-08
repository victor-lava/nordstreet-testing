<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Payment;
use App\Project;
use App\Investment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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

             // dump($missmatch);
        }

        foreach ($investments as $investment) {
          if(!$investment['match']) {
            $missmatch[] = $investment;
          }
        }

       if(count($missmatch) > 0) {$missmatches[] = $missmatch;}


        // $missmatch = [];
        // foreach ($investments as $investment) {
        //   if(!$investment['match']) {
        //     $missmatch[] = $investment;
        //   }
        // }
        // foreach ($project->investments as $investment) {
        //   $paymentSum = 0;
        //
        //   $payments = PaymentPaysera::where('project_id', $project->id)
        //                               ->where('user_id', $investment->user_id)
        //                               ->where('status', 'reserved')->get();
        //
        //   foreach ($payments as $payment) {
        //     $paymentSum += $payment->investment;
        //     $investments[$investment->user_id]['payments'][] = $payment->investment;
        //   }
        //
        //   $investments[$investment->user_id]['amount'] = $investment->amount;
        //   $investments[$investment->user_id]['match'] = ($paymentSum === $investment->amount) ? true : false;
        //
        //   var_dump($investments);
        // }

      }

      dump($missmatches);

      // dd($investments);


    }

}
