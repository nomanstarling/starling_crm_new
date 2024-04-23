<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class SubCommunitiesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $sub_communities;

    public function __construct($sub_communities)
    {
        $this->sub_communities = $sub_communities;
    }

    public function collection()
    {
        return $this->sub_communities;
    }

    public function headings(): array
    {
        return [
            'Country',
            'City',
            'Community',
            'Sub-Community',
            'Sale',
            'Rent',
            'Off-Plan',
            'Archived',
            'Created Date',
            'Last Update',
        ];
    }

    public function map($sub_community): array
    {
        return [
            $sub_community->country->name,
            $sub_community->city->name,
            $sub_community->community->name,
            $sub_community->name,
            $sub_community->sales_listing_count ?? 0,
            $sub_community->rent_listing_count ?? 0,
            $sub_community->rent_listing_count ?? 0,
            $sub_community->archive_listing_count ?? 0,
            $sub_community->created_at->format('F j, Y'),
            $sub_community->updated_at->format('F j, Y'),
        ];
    }
}
