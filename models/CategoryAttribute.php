<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryAttribute extends Model
{
    protected $table = 'tk_site_category_product_attributes';

    protected $fillable = ['site_cat_id','attr_name','attr_regx','property' , 'is_required'];

    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $casts = [
        'attr_regx_replace' => 'json',
        'is_required' => 'boolean'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(SiteCategory::class , 'site_cat_id');
    }
}
