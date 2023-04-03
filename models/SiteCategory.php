<?php


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteCategory extends Model
{
    protected $table = 'tk_site_categories';

    protected $fillable = ['site_id', 'woo_category_id', 'link_list_pattern', 'link_product_regx', 'product_json_reg'];

    const UPDATED_AT = null;

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class ,'category_id');
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(CategoryAttribute::class ,'site_cat_id');
    }
}
