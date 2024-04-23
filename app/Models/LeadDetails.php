<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadDetails extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'lead_id',
        'kitchen',
        'pets',
        'schools',
        'budget',
        'seen',
        'move_in',
        'cheque',
        'furnish',
        'upgraded',
        'landscape',
        'bathroom',
        'bedroom',
        'suite_bathroom',
        'current_home',
        'work_place',
        'new_to_dubai',
        'view',
        'single_row',
        'vastu',
        'parking',
        'floor',
        'bua',
        'plot_size',
        'balcony',
        'study',
        'maid_room',
        'white_goods',
        'community',
        'subcommunity',
        'property',
        'type',
        'budget_from',
        'budget_to',
        'cashfinance',
        'whentopurchase',
        'finance',
        'finance_with',
        'money_here',
        'viewed_other',
        'offered_anything',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // public function lead()
    // {
    //     return $this->morphTo();
    // }

}
