<?php


use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    protected $table = 'tk_product_prices';

    protected $fillable = ['status' ,'price' , 'product_id'];

    protected $casts = [
        'status' => 'boolean'
    ];
}
