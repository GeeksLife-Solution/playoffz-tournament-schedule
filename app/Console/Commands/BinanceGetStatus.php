<?php

namespace App\Console\Commands;

use App\Models\Payout;
use App\Models\Transaction;
use Facades\App\Services\BasicService;
use App\Traits\Notify;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BinanceGetStatus extends Command
{
    use Notify;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payout-status:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron for Binance Status';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $methodObj = 'App\\Services\\Payout\\binance\\Card';
        $data = $methodObj::getStatus();
        if ($data) {
            $apiResponses = collect($data);
            $binaceIds = $apiResponses->pluck('id');
            $payouts = Payout::whereIn('response_id', $binaceIds)->where('status', 1)->get();
            foreach ($payouts as $payout) {
                foreach ($apiResponses as $apiResponse) {
                    if ($payout->response_id == $apiResponse->id) {
                        $status = $apiResponse->status;
                        if ($status == 6) {
                            BasicService::preparePayoutComplete($payout);

                        } elseif ($status == 1 || $status == 3 || $status == 5) {
                            BasicService::preparePayoutFail($payout);
                        }
                        break;
                    }
                }
            }
        }
        return 0;
    }


}
