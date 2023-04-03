<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttribute extends Model
{
    protected $table = 'tk_product_attributes';

    protected $fillable = ['product_id' , 'attr_id' , 'attr_value'];

    public function attributeName(): BelongsTo
    {
        return $this->belongsTo(CategoryAttribute::class , 'attr_id');
    }
}
