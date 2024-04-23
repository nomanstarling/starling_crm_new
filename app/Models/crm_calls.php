<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class crm_calls extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'port',
        'start_date',
        'answer_date',
        'direction',
        'source',
        'ip',
        'destination',
        'hang_side',
        'reason',
        'duration',
        'codec',
        'rtp_send',
        'rtp_recv',
        'loss_rate',
        'BCCH',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
