<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class CommunitiesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $communities;

    public function __construct($communities)
    {
        $this->communities = $communities;
    }

    public function collection()
    {
        return $this->communities;
    }

    public function headings(): array
    {
        return [
            'Country',
            'City',
            'Community',
            'Sale',
            'Rent',
            'Off-Plan',
            'Archived',
            'Created Date',
            'Last Update',
        ];
    }

    public function map($community): array
    {
        return [
            $community->country->name,
            $community->city->name,
            $community->name,
            $community->sales_listing_count ?? 0,
            $community->rent_listing_count ?? 0,
            $community->rent_listing_count ?? 0,
            $community->archive_listing_count ?? 0,
            $community->created_at->format('F j, Y'),
            $community->updated_at->format('F j, Y'),
        ];
    }
}

