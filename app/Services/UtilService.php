<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\Statuses;
use App\Models\Leads;
use App\Models\owners;
use App\Models\contacts;
use App\Models\Listings;
use Spatie\Valuestore\Valuestore;

class UtilService
{
    public function __construct()
    {
        $this->settings = Valuestore::make(config('settings.path'));
        $this->shortName = $this->settings->get('short_name');
    }

    public function valid_leads()
    {
        $query = Statuses::where('type', 'Leads')->where('lead_type', 'Active')->pluck('id');
        return $query;
    }

    public function get_next_refkey_lead(){
        //$latestOwner = owners::latest()->first();
        $latestLead = Leads::withTrashed()
            ->select('refno')
            ->orderByRaw("CAST(SUBSTRING_INDEX(refno, '-', -1) AS SIGNED) DESC")
            ->first();

        if ($latestLead) {
            // Extract the numeric part of the existing refno
            $latestRefNo = $latestLead->refno;
            
            $numericPart = (int)preg_replace('/[^0-9]/', '', $latestRefNo);
            
            // Increment the numeric part
            $nextNumericPart = $numericPart + 1;
            // Generate the new refno
            $newRefNo = $this->shortName . '-L-' . str_pad($nextNumericPart, 3, '0', STR_PAD_LEFT);
        } else {
            // If there are no existing records, start from 001
            $newRefNo = $this->shortName . '-L-001';
        }
        return $newRefNo;
    }

    public function get_next_refkey_contact(){
        //$latestOwner = owners::latest()->first();
        $latestContact = contacts::withTrashed()
            ->select('refno')
            ->orderByRaw("CAST(SUBSTRING_INDEX(refno, '-', -1) AS SIGNED) DESC")
            ->first();

        if ($latestContact) {
            // Extract the numeric part of the existing refno
            $latestRefNo = $latestContact->refno;
            
            $numericPart = (int)preg_replace('/[^0-9]/', '', $latestRefNo);
            
            // Increment the numeric part
            $nextNumericPart = $numericPart + 1;
            // Generate the new refno
            $newRefNo = $this->shortName . '-C-' . str_pad($nextNumericPart, 3, '0', STR_PAD_LEFT);
        } else {
            // If there are no existing records, start from 001
            $newRefNo = $this->shortName . '-C-001';
        }
        return $newRefNo;
    }

    public function get_next_refkey_owner(){
        //$latestOwner = owners::latest()->first();
        $latestOwner = owners::withTrashed()
            ->select('refno')
            ->orderByRaw("CAST(SUBSTRING_INDEX(refno, '-', -1) AS SIGNED) DESC")
            ->first();

        if ($latestOwner) {
            // Extract the numeric part of the existing refno
            $latestRefNo = $latestOwner->refno;
            
            $numericPart = (int)preg_replace('/[^0-9]/', '', $latestRefNo);
            
            // Increment the numeric part
            $nextNumericPart = $numericPart + 1;
            // Generate the new refno
            $newRefNo = $this->shortName . '-O-' . str_pad($nextNumericPart, 3, '0', STR_PAD_LEFT);
        } else {
            // If there are no existing records, start from 001
            $newRefNo = $this->shortName . '-O-001';
        }
        return $newRefNo;
    }

    public function get_next_refkey_listing($type){
        $latestListing = Listings::withTrashed()
            ->select('refno')
            ->orderByRaw("CAST(SUBSTRING_INDEX(refno, '-', -1) AS SIGNED) DESC")
            ->first();

        if ($latestListing) {
            $latestRefNo = $latestListing->refno;
            $numericPart = (int)preg_replace('/[^0-9]/', '', $latestRefNo);
            $nextNumericPart = $numericPart + 1;
            $newRefNo = $this->shortName . '-'.$type.'-' . str_pad($nextNumericPart, 3, '0', STR_PAD_LEFT);
        } else {
            $newRefNo = $this->shortName . '-'.$type.'-001';
        }
        return $newRefNo;
    }
}
