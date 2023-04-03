<?php


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = 'tk_products';

    protected $fillable = ['category_id' , 'link' , 'product_woocommerce_id' , 'info_json'];

    protected $casts = [
        'info_json' => 'json'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(SiteCategory::class , 'category_id');
    }

    public function dataAttributes (): HasMany
    {
        return $this->hasMany(ProductAttribute::class , 'product_id');
    }
}
