<?php

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{

    protected $table = 'tk_currencies';
    protected $fillable = ['origin', 'exchange', 'rate', 'status', 'calculate_rial','crawl_url' , 'crawl_regex'];
}
