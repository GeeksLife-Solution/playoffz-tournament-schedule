<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TournamentController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\MatchController;
use App\Http\Controllers\Admin\MatchOptionController;
use App\Http\Controllers\Admin\ManageResultController;
use App\Http\Controllers\Admin\ManageBetController;

$basicControl = basicControl();
Route::group(['prefix' => $basicControl->admin_prefix ?? 'admin', 'as' => 'admin.'], function () {
    Route::middleware(['auth:admin', 'permission', 'demo'])->group(function () {

        //Manage Category
        Route::controller(CategoryController::class)->group(function () {
            Route::get('category/list', 'listCategory')->name('listCategory');
            Route::get('category/list/search', 'listCategorySearch')->name('listCategorySearch');
            Route::post('category/store', 'storeCategory')->name('storeCategory');
            Route::post('category/update/{id}', 'updateCategory')->name('updateCategory');
            Route::delete('category/delete/{id}', 'deleteCategory')->name('deleteCategory');

            Route::post('category/multiple-status/change', 'multiStatusChange')->name('categoryMultiStatusChange');
        });

        //Manage Tournament
        Route::controller(TournamentController::class)->group(function () {
            Route::get('tournament/list', 'listTournament')->name('listTournament');
            Route::get('tournament/list/search', 'listTournamentSearch')->name('listTournamentSearch');
            Route::post('tournament/store', 'storeTournament')->name('storeTournament');
            Route::post('tournament/update/{id}', 'updateTournament')->name('updateTournament');
            Route::delete('tournament/delete/{id}', 'deleteTournament')->name('deleteTournament');

            Route::post('tournament/multiple-status/change', 'multiStatusChange')->name('tournamentMultiStatusChange');
        });

        //Manage Team
        Route::controller(TeamController::class)->group(function () {
            Route::get('team/list', 'listTeam')->name('listTeam');
            Route::get('team/list/search', 'listTeamSearch')->name('listTeamSearch');
            Route::post('team/store', 'storeTeam')->name('storeTeam');
            Route::post('team/update/{id}', 'updateTeam')->name('updateTeam');
            Route::delete('team/delete/{id}', 'deleteTeam')->name('deleteTeam');

            Route::post('team/multiple-status/change', 'multiStatusChange')->name('teamMultiStatusChange');
        });

        //Manage Match
        Route::controller(MatchController::class)->group(function () {
            Route::get('match/list', 'listMatch')->name('listMatch');
            Route::get('match/list/search', 'listMatchSearch')->name('listMatchSearch');
            Route::post('match/store', 'storeMatch')->name('storeMatch');
            Route::post('match/update/{id}', 'updateMatch')->name('updateMatch');
            Route::delete('match/delete/{id}', 'deleteMatch')->name('deleteMatch');

            Route::post('match/multiple-status/change', 'multiStatusChange')->name('matchMultiStatusChange');
            Route::get('match/locker/{id}', 'matchLocker')->name('match.locker');
            Route::post('ajax-match/list', 'ajaxListMatch')->name('ajax.listMatch');

            //Manage Match Question
            Route::get('match/question/{id}', 'infoMatch')->name('infoMatch');
            Route::get('match/question/add/{match_id}', 'addQuestion')->name('addQuestion');
            Route::post('match/question/save', 'storeQuestion')->name('storeQuestion');
            Route::post('match/question/update', 'updateQuestion')->name('updateQuestion');
            Route::delete('match/question/delete/{id}', 'deleteQuestion')->name('deleteQuestion');
            Route::post('match/question/active', 'activeQsMultiple')->name('question-active');
            Route::post('match/question/deactive', 'deActiveQsMultiple')->name('question-deactive');
            Route::post('match/question/close', 'closeQsMultiple')->name('question-close');
        });

        //Match Option
        Route::controller(MatchOptionController::class)->group(function () {
            Route::get('optionList/{question_id?}', 'optionList')->name('optionList');
            Route::post('optionAdd', 'optionAdd')->name('optionAdd');
            Route::post('optionUpdate', 'optionUpdate')->name('optionUpdate');
            Route::post('optionDelete', 'optionDelete')->name('optionDelete');
            Route::post('question/locker', 'questionLocker')->name('question.locker');
        });

        //Match Option
        Route::controller(ManageResultController::class)->group(function () {
            Route::get('/result/history/pending', 'resultList')->name('resultList.pending');
            Route::get('/result/history/complete', 'resultList')->name('resultList.complete');
            Route::get('/result/history/search', 'resultSearch')->name('resultSearch');
            Route::post('/result/winner', 'makeWinner')->name('makeWinner');
            Route::get('/result/winner/{question_id}', 'resultWinner')->name('resultWinner');
            Route::get('/bet/user/{question_id}', 'betUser')->name('betUser');
            Route::post('/bet/refundQuestion/{id}', 'refundQuestion')->name('refundQuestion');
        });

        //Manage Bet
        Route::controller(ManageBetController::class)->group(function () {
            Route::get('/bet-history', 'betList')->name('historyBet');
            Route::get('/bet-history/search', 'betSearch')->name('searchBet');
            Route::post('/bet/refund', 'betRefund')->name('refundBet');
        });
    });
});
