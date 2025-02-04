<?php

namespace App\Services;


use App\Models\Deposit;
use App\Models\Payout;
use App\Models\ReferralBonus;
use App\Models\Transaction;
use App\Traits\Notify;
use GPBMetadata\Google\Api\Auth;

class BasicService
{
    use Notify;

    public function setEnv($value)
    {
        $envPath = base_path('.env');
        $env = file($envPath);
        foreach ($env as $env_key => $env_value) {
            $entry = explode("=", $env_value, 2);
            $env[$env_key] = array_key_exists($entry[0], $value) ? $entry[0] . "=" . $value[$entry[0]] . "\n" : $env_value;
        }
        $fp = fopen($envPath, 'w');
        fwrite($fp, implode($env));
        fclose($fp);
    }

    public function preparePaymentUpgradation($deposit)
    {
        try {
            if ($deposit->status == 0 || $deposit->status == 2) {
                $basic = basicControl();

                $deposit->status = 1;
                $deposit->save();

                if ($deposit->user) {
                    $user = $deposit->user;
                    $user->balance += $deposit->payable_amount_in_base_currency;
                    $user->save();

                    $remark = 'Payment Via ' . $deposit->gateway->name ?? 'Unknown';
                    $this->makeTransaction($user, $deposit->payable_amount_in_base_currency, $deposit->charge_base_currency, '+',
                        $deposit->trx_id, $remark, $deposit->id, Deposit::class);

                    if ($basic->deposit_commission == 1) {
                        $this->setBonus($user, $deposit->payable_amount_in_base_currency, 'deposit');
                    }


                    $params = [
                        'gateway_name' => $deposit->gateway->name ?? 'Unknown',
                        'amount' => getAmount($deposit->amount),
                        'currency' => $deposit->payment_method_currency,
                        'transaction' => $deposit->trx_id,
                    ];

                    $action = [
                        "link" => "#",
                        "icon" => "fa fa-money-bill-alt text-white"
                    ];

                    $this->sendMailSms($user, 'PAYMENT_COMPLETE', $params, $action);
                    $this->userPushNotification($user, 'PAYMENT_COMPLETE', $params, $action);
                    $this->userFirebasePushNotification($user, 'PAYMENT_COMPLETE', $params, $action);

                    $actionAdmin = [
                        "name" => optional($deposit->user)->firstname . ' ' . optional($deposit->user)->lastname,
                        "image" => getFile(optional($deposit->user)->image_driver, optional($deposit->user)->image),
                        "link" => "#",
                        "icon" => "fas fa-ticket-alt text-white"
                    ];

                    $this->adminMail('PAYMENT_COMPLETE_ADMIN', $params, $actionAdmin);
                    $this->adminPushNotification('PAYMENT_COMPLETE_ADMIN', $params, $actionAdmin);
                    $this->adminFirebasePushNotification('PAYMENT_COMPLETE_ADMIN', $params);
                }

                return true;
            }
        } catch (\Exception $e) {
        }
    }

    public function preparePayoutComplete($payout): void
    {
        if ($payout->status == 1) {
            $payout->status = 2;
            $payout->save();
            $this->userPayoutNotify($payout);
        }
    }

    public function preparePayoutFail($payout)
    {
        if ($payout->status == 1) {
            $payout->status = 3;
            $payout->save();

            updateBalance($payout->user_id, $payout->net_amount_in_base_currency, 1);

            $this->makeTransaction($payout->user, $payout->net_amount_in_base_currency, 0, '+',
                $payout->trx_id, 'Payout Amount Refunded', $payout->id, Payout::class);

            $receivedUser = $payout->user;
            $params = [
                'sender' => $receivedUser->name,
                'amount' => getAmount($payout->amount),
                'currency' => $payout->payout_currency_code,
                'transaction' => $payout->trx_id,
            ];

            $action = [
                "link" => "#",
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $firebaseAction = "#";
            $this->sendMailSms($receivedUser, 'PAYOUT_CANCEL', $params);
            $this->userPushNotification($receivedUser, 'PAYOUT_CANCEL', $params, $action);
            $this->userFirebasePushNotification($receivedUser, 'PAYOUT_CANCEL', $params, $firebaseAction);
        }
    }

    public function userPayoutNotify($payout):void
    {
        try {
            $msg = [
                'username' => optional($payout->user)->username,
                'amount' => getAmount($payout->amount),
                'currency' => $payout->payout_currency_code,
                'gateway' => optional($payout->method)->name,
                'transaction' => $payout->trx_id,
            ];
            $action = [
                "link" => '#',
                "icon" => "fas fa-money-bill-alt text-white"
            ];
            $fireBaseAction = "#";
            $this->userPushNotification($payout->user, 'PAYOUT_APPROVED', $msg, $action);
            $this->userFirebasePushNotification('PAYOUT_APPROVED', $msg, $fireBaseAction);
            $this->sendMailSms($payout->user, 'PAYOUT_APPROVED', [
                'gateway_name' => optional($payout->method)->name,
                'amount' => currencyPosition($payout->amount),
                'charge' => currencyPosition($payout->charge),
                'transaction' => $payout->trx_id,
                'feedback' => $payout->note,
            ]);
        }catch (\Exception $e){

        }
    }

    public function cryptoQR($wallet, $amount, $crypto = null)
    {
        $varb = $wallet . "?amount=" . $amount;
        return "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=$varb&choe=UTF-8";
    }

    public function makeTransaction($user, $amount, $charge, $trxType, $trxId, $remark, $transactionalId, $transactionalType)
    {
        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->amount = $amount;
        $transaction->charge = $charge;
        $transaction->trx_type = $trxType;
        $transaction->trx_id = $trxId;
        $transaction->remarks = $remark;
        $transaction->transactional_id = $transactionalId;
        $transaction->transactional_type = $transactionalType;
        $transaction->save();
    }

    public function setBonus($user, $amount, $commissionType = '')
    {

        $basic = basicControl();
        $userId = $user->id;
        $i = 1;
        $level = \App\Models\Referral::where('commission_type', $commissionType)->count();
        while ($userId != "" || $userId != "0" || $i < $level) {
            $me = \App\Models\User::with('referral')->find($userId);
            $refer = $me->referral;
            if (!$refer) {
                break;
            }
            $commission = \App\Models\Referral::where('commission_type', $commissionType)->where('level', $i)->first();
            if (!$commission) {
                break;
            }
            $com = ($amount * $commission->percent) / 100;
            $new_bal = getAmount($refer->balance + $com);
            $refer->balance = $new_bal;
            $refer->save();

            $trx = strRandom();

            $remarks = ' level ' . $i . ' Referral bonus From ' . $user->username;


            $bonus = new \App\Models\ReferralBonus();
            $bonus->from_user_id = $refer->id;
            $bonus->to_user_id = $user->id;
            $bonus->level = $i;
            $bonus->amount = getAmount($com);
            $bonus->main_balance = $new_bal;
            $bonus->transaction = $trx;
            $bonus->type = $commissionType;
            $bonus->remarks = $remarks;
            $bonus->save();

            $this->makeTransaction($refer, $com, 0, '+', $trx, $remarks, $bonus->id, ReferralBonus::class);


            $this->sendMailSms($refer, 'REFERRAL_BONUS', [
                'transaction_id' => $trx,
                'amount' => currencyPosition($com),
                'bonus_from' => $user->username,
                'level' => $i
            ]);


            $msg = [
                'transaction_id' => $trx,
                'amount' => currencyPosition($com),
                'bonus_from' => $user->username,
                'level' => $i
            ];
            $action = [
                "link" => route('user.referral.bonus'),
                "icon" => "fa fa-money-bill-alt"
            ];
            $this->userPushNotification($refer, 'REFERRAL_BONUS', $msg, $action);

            $userId = $refer->id;
            $i++;
        }
        return 0;

    }

}
