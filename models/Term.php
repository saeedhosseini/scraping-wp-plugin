<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Term extends Model
{
    protected $table = 'wp_terms';

    const CREATED_AT = null;
    const UPDATED_AT = null;


    public function termTaxonomy(): BelongsTo
    {
        return $this->belongsTo(TermTaxonomy::class , 'term_id' , 'term_id');
    }
}
