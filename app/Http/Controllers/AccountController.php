<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Account;

class AccountController extends Controller
{

    public function reset()
    {
        Account::truncate();
        return response('OK', 200);
    }

    public function show(Request $request)
    {
        $account_id = intval($request->input('account_id'));
        $account_exists = DB::table('accounts')->where('id', $account_id)->exists();

        if (!$account_exists) { 
            return response(0, 404);
        }

        $account = Account::find($account_id);

        return response($account->balance, 200);
    }

    public function store(Request $request)
    {
        switch ($request->type) {
            case 'deposit':
                return $this->deposit($request);
                break;
            case 'withdraw':
                return $this->withdraw($request);
                break;
            case 'transfer':
                return $this->transfer($request);
                break;
        }
    }

    private function deposit(Request $request){
        $amount = $request->input('amount');
        $destination = $request->input('destination');
        $account_exists = DB::table('accounts')->where('id', $destination)->exists();

        if (!$account_exists) {
            $account = Account::create(['id' => $destination, 'balance' => $amount]);

            $response = ['destination' => ['id' => $destination, 'balance' => $amount]];

            return response($response, 201);
        }
            $account = Account::find($destination);

            $account->balance += $amount;
            $account->save();

            $response = ['destination' => ['id' => $destination, 'balance' => $account->balance]];
            return response($response, 201);
    }

    private function withdraw(Request $request){
        $amount = $request->input('amount');
        $origin = $request->input('origin');
        $account_exists = DB::table('accounts')->where('id', $origin)->exists();

        if (!$account_exists) {
            return response(0, 404);
        }
            $origin = $request->input('origin');
            $account = Account::find($origin);

            $account->balance -= $amount;
            $account->save();

            $response = ['origin' => ['id' => $origin, 'balance' => $account->balance]];
            return response($response, 201);
    }

    private function transfer(Request $request){
        $amount = $request->input('amount');
        $origin = $request->input('origin');
        $destination = $request->input('destination');
        $account_exists = DB::table('accounts')->where('id', $origin)->exists();

        if (!$account_exists) {
            return response(0, 404);
        }

        $origin_acc = Account::find($origin);

        $dest_exists = DB::table('accounts')->where('id', $destination)->exists();

        if ($dest_exists) {
            $dest_acc = Account::find($destination);

            $origin_acc->balance -= $amount;
            $dest_acc->balance += $amount;

            $origin_acc->save();
            $dest_acc->save();
        }
        else {
            $dest_acc = Account::create(['id' => $destination, 'balance' => $amount]);

            $origin_acc->balance -= $amount;
            
            $origin_acc->save();
        }

        $response = ['origin' => ['id' => strval($origin_acc->id), 'balance' => $origin_acc->balance], 
                     'destination' => ['id' => strval($dest_acc->id), 'balance' => $dest_acc->balance]];

        return response($response, 201);
    }
}
