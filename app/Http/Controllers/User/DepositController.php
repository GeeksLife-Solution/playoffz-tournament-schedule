<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Fund;
use App\Models\Gateway;
use Illuminate\Http\Request;
use App\Traits\PaymentValidationCheck;
use Illuminate\Support\Facades\Auth;

class DepositController extends Controller
{

    use PaymentValidationCheck;

    public function supportedCurrency(Request $request)
    {
        $gateway = Gateway::where('id', $request->gateway)->first();
        $pmCurrency =  $gateway->receivable_currencies[0]->name ?? $gateway->receivable_currencies[0]->currency;
        $isCrypto = $gateway->id < 1000 && checkTo($gateway->currencies, $pmCurrency) == 1;

        return response([
            'success' => true,
            'data' => $gateway->supported_currency,
            'currencyType' => $isCrypto ? 0 : 1,
        ]);
    }

    public function checkAmount(Request $request)
    {
        if ($request->ajax()) {
            $amount = $request->amount;
            $selectedCurrency = $request->selected_currency;
            $selectGateway = $request->select_gateway;
            $selectedCryptoCurrency = $request->selectedCryptoCurrency;
            $data = $this->checkAmountValidate($amount, $selectedCurrency, $selectGateway, $selectedCryptoCurrency);
            return response()->json($data);
        }
        return response()->json(['error' => 'Invalid request'], 400);
    }

    public function checkAmountValidate($amount, $selectedCurrency, $selectGateway, $selectedCryptoCurrency = null)
    {
        return $this->validationCheck($amount, $selectGateway, $selectedCurrency, $selectedCryptoCurrency);
    }


    public function paymentRequest(Request $request)
    {
        $amount = $request->amount;
        $gateway = $request->gateway_id;
        $currency = $request->supported_currency;
        $cryptoCurrency= $request->supported_crypto_currency;

        try {
            $checkAmountValidate = $this->validationCheck($amount, $gateway, $currency, $cryptoCurrency);

            if (!$checkAmountValidate['status']) {
                return back()->with('error', $checkAmountValidate['message']);
            }

            $deposit = Deposit::create([
                'user_id' => Auth::user()->id,
                'payment_method_id' => $checkAmountValidate['gateway_id'],
                'payment_method_currency' => $checkAmountValidate['currency'],
                'amount' => $amount,
                'percentage_charge' => $checkAmountValidate['percentage_charge'],
                'fixed_charge' => $checkAmountValidate['fixed_charge'],
                'payable_amount' => $checkAmountValidate['payable_amount'],
                'payable_amount_in_base_currency' => $checkAmountValidate['payable_amount_baseCurrency'],
                'charge_base_currency' => $checkAmountValidate['charge_baseCurrency'],
                'status' => 0,
            ]);

            return redirect(route('payment.process', $deposit->trx_id));

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

}
