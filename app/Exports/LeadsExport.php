<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class LeadsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $leads;

    public function __construct($leads)
    {
        $this->leads = $leads;
    }

    public function collection()
    {
        return $this->leads;
    }

    public function headings(): array
    {
        return [
            'RefNo#',
            'Status',
            'Sub Status',
            'Stage',
            'Last Update',
            'Enquiry Date',
            'Added On',
            'Name',
            'Email',
            'Phone',
            'Listing',
            'Budget',
            'Campaign',
            'Lead Agent',
            'Assigned On',
            'Accepted On',
            'Source',
            'Sub Source',
            'Created By',
            'Updated By',
        ];
    }

    public function map($lead): array
    {
        return [
            $lead->refno,
            $lead->status ? $lead->status->name : null,
            $lead->sub_status ? $lead->sub_status->name : null,
            $lead->lead_stage,
            $lead->updated_at != null ? $lead->updated_at->format('F j, Y') : null,

            //$lead->enquiry_date != null || $lead->enquiry_date != '' ? $lead->enquiry_date->format('F j, Y') : null,
            $lead->enquiry_date,
            $lead->created_at != null ? $lead->created_at->format('F j, Y') : null,
            $lead->contact ? $lead->contact->name : null,
            $lead->contact ? $lead->contact->email : null,
            $lead->contact ? $lead->contact->phone : null,
            $lead->property ? $lead->property->external_refno : null,
            $lead->lead_details ? $lead->lead_details->budget : null,
            $lead->campaign ? $lead->campaign->name : null,
            $lead->lead_agent ? $lead->lead_agent->name : null,
            //$lead->assigned_date != null ? $lead->assigned_date->format('F j, Y') : null,
            $lead->assigned_date,
            //$lead->accepted_date != null ? $lead->accepted_date->format('F j, Y') : null,
            $lead->accepted_date,
            $lead->source ? $lead->source->name : null,
            $lead->sub_source ? $lead->sub_source->name : null,
            $lead->created_by_user ? $lead->created_by_user->name : '',
            $lead->updated_by_user ? $lead->updated_by_user->name : '',
        ];
    }
}
