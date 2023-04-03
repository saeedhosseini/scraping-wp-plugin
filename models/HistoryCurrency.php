<?php


use Illuminate\Database\Eloquent\Model;

class HistoryCurrency extends Model
{
    protected $table = 'tk_history_currencies';

    protected $fillable = ['origin' , 'exchange' , 'rate'];
}
