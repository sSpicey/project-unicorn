<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    private $balance;
    
    protected $fillable = [
        'id',
        'balance'
    ];

    public function deposit($amount){
        $this->$balance += $amount;
    }

    public function withdraw($amount){
        $this->$balance -= $amount;
    }
}
