<?php

use App\Http\Controllers\Auth\LoginController as UserLoginController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\DepositController;
use App\Http\Controllers\User\PayoutController;
use App\Http\Controllers\ManualRecaptchaController;
use App\Http\Controllers\khaltiPaymentController;
use App\Http\Controllers\GameFetchController;
use App\Models\GameCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InAppNotificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\User\SupportTicketController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\User\VerificationController;
use App\Http\Controllers\User\KycVerificationController;
use App\Http\Controllers\User\BetHistoryController;
use App\Http\Controllers\TwoFaSecurityController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

$basicControl = basicControl();
Route::get('language/{locale?}', function ($locale) {
    $language = \App\Models\Language::where('short_name', $locale)->first();
    if (!$language) $locale = 'en';
    session()->put('lang', $locale);
    session()->put('rtl', $language ? $language->rtl : 0);
    return back();
})->name('language');

Route::group(['middleware' => ['guest']], function () {
    Route::get('guest/register/{sponsor?}', [RegisterController::class, 'sponsor'])->name('register.sponsor');
});

Route::post('/loginModal', [LoginController::class, 'loginModal'])->name('loginModal');

Route::get('/themeMode/{themeType?}', function ($themeType = 'true') {
    session()->put('dark-mode', $themeType);
    return $themeType;
})->name('themeMode');

Route::get('/', function () {
    $data['gameCategories'] = GameCategory::with(['activeTournament'])->withCount('gameActiveMatch')
        ->whereStatus(1)->orderBy('game_active_match_count', 'desc')->get();
    return view(template() . 'home', $data);
})->name('home');

Route::get('clear', function () {
    Illuminate\Support\Facades\Artisan::call('optimize:clear');
    if (url()->previous() !== url()->current()) {
        return back()->with('success', 'Cache Clear Successfully');
    }
    return redirect('/')->with('success', 'Cache Clear Successfully');
})->name('clear');

Route::get('maintenance-mode', function () {
    if (!basicControl()->is_maintenance_mode) {
        return redirect(route('page'));
    }
    $data['maintenanceMode'] = \App\Models\MaintenanceMode::first();
    return view(template() . 'maintenance', $data);
})->name('maintenance');

Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('forget-password', [ForgotPasswordController::class, 'submitForgetPassword'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset')->middleware('guest');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.reset.update');

Route::get('instruction/page', function () {
    return view('instruction-page');
})->name('instructionPage');

Route::group(['middleware' => ['maintenanceMode']], function () use ($basicControl) {
    Route::group(['middleware' => ['guest']], function () {
        Route::get('/login', [UserLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [UserLoginController::class, 'login'])->name('login.submit');
    });

    Route::group(['middleware' => ['auth'], 'prefix' => 'user', 'as' => 'user.'], function () {

        Route::get('check', [VerificationController::class, 'check'])->name('check');
        Route::get('resend_code', [VerificationController::class, 'resendCode'])->name('resendCode');
        Route::post('mail-verify', [VerificationController::class, 'mailVerify'])->name('mailVerify');
        Route::post('sms-verify', [VerificationController::class, 'smsVerify'])->name('smsVerify');
        Route::post('twoFA-Verify', [VerificationController::class, 'twoFAverify'])->name('twoFA-Verify');

        Route::middleware('userCheck')->group(function () {

            Route::middleware('kyc')->group(function () {

                //Bet History
                Route::get('/bet-history', [BetHistoryController::class, 'betList'])->name('betHistory');
                Route::post('/betSlip', [HomeController::class, 'betSlip'])->name('betSlip');

                Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard');
                Route::post('save-token', [HomeController::class, 'saveToken'])->name('save.token');
                Route::get('add-fund', [HomeController::class, 'addFund'])->name('add.fund');
                Route::get('funds', [HomeController::class, 'fund'])->name('fund.index');

                Route::get('transaction-list', [HomeController::class, 'transaction'])->name('transaction');

                /* ===== Manage Two Step ===== */
                Route::get('two-step-security', [TwoFaSecurityController::class, 'twoStepSecurity'])->name('twostep.security');
                Route::post('twoStep-enable', [TwoFaSecurityController::class, 'twoStepEnable'])->name('twoStepEnable');
                Route::post('twoStep-disable', [TwoFaSecurityController::class, 'twoStepDisable'])->name('twoStepDisable');
                Route::post('twoStep/re-generate', [TwoFaSecurityController::class, 'twoStepRegenerate'])->name('twoStepRegenerate');

                /* PAYOUT REQUEST BY USER */

                Route::get('payout-list', [PayoutController::class, 'index'])->name('payout.index');

                Route::get('payout', [PayoutController::class, 'payout'])->name('payout');
                Route::get('payout-supported-currency', [PayoutController::class, 'payoutSupportedCurrency'])->name('payout.supported.currency');
                Route::get('payout-check-amount', [PayoutController::class, 'checkAmount'])->name('payout.checkAmount');
                Route::post('request-payout', [PayoutController::class, 'payoutRequest'])->name('payout.request');

                Route::match(['get', 'post'], 'confirm-payout/{trx_id}', [PayoutController::class, 'confirmPayout'])->name('payout.confirm');
                Route::post('confirm-payout/flutterwave/{trx_id}', [PayoutController::class, 'flutterwavePayout'])->name('payout.flutterwave');
                Route::post('confirm-payout/paystack/{trx_id}', [PayoutController::class, 'paystackPayout'])->name('payout.paystack');
                Route::post('payout-bank-form', [PayoutController::class, 'getBankForm'])->name('payout.getBankForm');
                Route::post('payout-bank-list', [PayoutController::class, 'getBankList'])->name('payout.getBankList');

                /* ===== Push Notification ===== */
                Route::get('push-notification-show', [InAppNotificationController::class, 'show'])->name('push.notification.show');
                Route::get('push.notification.readAll', [InAppNotificationController::class, 'readAll'])->name('push.notification.readAll');
                Route::get('push-notification-readAt/{id}', [InAppNotificationController::class, 'readAt'])->name('push.notification.readAt');

                Route::group(['prefix' => 'ticket', 'as' => 'ticket.'], function () {
                    Route::get('/', [SupportTicketController::class, 'index'])->name('list');
                    Route::get('/create', [SupportTicketController::class, 'create'])->name('create');
                    Route::post('/create', [SupportTicketController::class, 'store'])->name('store');
                    Route::get('/view/{ticket}', [SupportTicketController::class, 'ticketView'])->name('view');
                    Route::put('/reply/{ticket}', [SupportTicketController::class, 'reply'])->name('reply');
                    Route::get('/download/{ticket}', [SupportTicketController::class, 'download'])->name('download');
                });

            });


            Route::get('profile', [HomeController::class, 'profile'])->name('profile');
            Route::post('profile-update', [HomeController::class, 'updateInformation'])->name('updateInformation');
            Route::post('profile-update/image', [HomeController::class, 'updateProfile'])->name('updateProfile');
            Route::any('update/password', [HomeController::class, 'updatePassword'])->name('updatePassword');

            //KYC
            Route::controller(KycVerificationController::class)->group(function () {
                Route::get('kyc/{slug}/{id}', 'kycShow')->name('kyc');
                Route::post('kyc/submit/{id}', 'kycVerificationSubmit')->name('kyc.verification.submit');
                Route::get('verification/center', 'verificationCenter')->name('verification.center');
            });

            Route::get('/invite-friends', [HomeController::class, 'referral'])->name('referral');
            Route::post('/get-referral-user', [HomeController::class, 'getReferralUser'])->name('myGetDirectReferralUser');
            Route::get('/referral-bonus', [HomeController::class, 'referralBonus'])->name('referral.bonus');
            Route::get('/referral-bonus/search', [HomeController::class, 'referralBonusSearch'])->name('referral.bonus.search');

        });
    });


    Route::get('captcha', [ManualRecaptchaController::class, 'reCaptCha'])->name('captcha');
    Route::post('/channel/subscribe', [FrontendController::class, 'subscribe'])->name('subscribe');
    Route::post('/contact/send', [FrontendController::class, 'contactSend'])->name('contact.send');

    Route::get('/blog/details/{id?}/{title?}', [FrontendController::class, 'blogDetails'])->name('blogDetails');

    /* Manage User Deposit */
    Route::get('supported-currency', [DepositController::class, 'supportedCurrency'])->name('supported.currency');
    Route::post('payment-request', [DepositController::class, 'paymentRequest'])->name('payment.request');
    Route::get('deposit-check-amount', [DepositController::class, 'checkAmount'])->name('deposit.checkAmount');

    Route::get('payment-process/{trx_id}', [PaymentController::class, 'depositConfirm'])->name('payment.process');
    Route::post('addFundConfirm/{trx_id}', [PaymentController::class, 'fromSubmit'])->name('addFund.fromSubmit');
    Route::match(['get', 'post'], 'success', [PaymentController::class, 'success'])->name('success');
    Route::match(['get', 'post'], 'failed', [PaymentController::class, 'failed'])->name('failed');

    Route::post('khalti/payment/verify/{trx}', [\App\Http\Controllers\khaltiPaymentController::class, 'verifyPayment'])->name('khalti.verifyPayment');
    Route::post('khalti/payment/store', [khaltiPaymentController::class, 'storePayment'])->name('khalti.storePayment');


    Route::get('/bet/allSports/{categoryId?}', [GameFetchController::class, 'index'])->name('allSports');
    Route::get('/bet/result', [FrontendController::class, 'betResult'])->name('betResult');
    Route::get('/category/{category_slug}/{category_id}', [FrontendController::class, 'category'])->name('category');
    Route::get('/tournament/{tournament_name}/{tournament_id}', [FrontendController::class, 'tournament'])->name('tournament');
    Route::get('/match/{match_name}/{match_id}', [FrontendController::class, 'match'])->name('match');


    Auth::routes();
    /*= Frontend Manage Controller =*/
    Route::get("/{slug?}", [FrontendController::class, 'page'])->name('page');
});


