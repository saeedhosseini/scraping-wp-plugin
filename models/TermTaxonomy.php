<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TermTaxonomy extends Model
{
    protected $table = 'wp_term_taxonomy';

    const CREATED_AT = null;
    const UPDATED_AT = null;

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TermTaxonomy::class, 'parent', 'term_taxonomy_id ');
    }

    public function children(): HasMany
    {
        return $this->hasMany(TermTaxonomy::class, 'parent', 'term_taxonomy_id ');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'term_id', 'term_id');
    }
}
