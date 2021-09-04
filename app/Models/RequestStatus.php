<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

/**
 * Class RequestStatus
 *
 * @package App\Models
 */
class RequestStatus extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'requests_status';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'color_code',
        'description',
    ];

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class, 'status_id', 'id');
    }
}
