<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Debug extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'line_number',
        'class_name',
        'debug_id',
        'debug_type',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'variable_id' => 'integer',
        ];
    }

    public function debugable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'debug_type', 'debug_id');
    }


}
