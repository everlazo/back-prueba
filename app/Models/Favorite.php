<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    use HasFactory;
    /**
     * Campos asignables en masa
 
     */
    protected $fillable = [
        'user_id',
        'external_id',
        'name',
        'image',
        'description',
    ];

    /**
     * Relación: un favorito pertenece a un usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
