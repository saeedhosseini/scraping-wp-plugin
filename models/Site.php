<?php


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $table = 'tk_source_sites';

    protected $fillable = ['base_url'];

    public $timestamps = false;

    public function categories(): HasMany
    {
        return $this->hasMany(SiteCategory::class ,'site_id');
    }
}
