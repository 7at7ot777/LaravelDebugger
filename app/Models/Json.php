<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Json extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'json',
    ];
    protected $appends = ['decoded_json'];
    protected $hidden = ['json'];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'json' => 'array',
        ];
    }

    public function getDecodedJsonAttribute()
    {
        return json_decode($this->json, true);
    }


    public function debug(): MorphOne
    {
        return $this->morphOne(Debug::class, 'debug');
    }
}
