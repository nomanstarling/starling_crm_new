<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class TowersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $towers;

    public function __construct($towers)
    {
        $this->towers = $towers;
    }

    public function collection()
    {
        return $this->towers;
    }

    public function headings(): array
    {
        return [
            'Country',
            'City',
            'Community',
            'Sub-Community',
            'Tower',
            'Sale',
            'Rent',
            'Off-Plan',
            'Archived',
            'Created Date',
            'Last Update',
        ];
    }

    public function map($tower): array
    {
        return [
            $tower->country->name,
            $tower->city->name,
            $tower->community->name,
            $tower->sub_community->name,
            $tower->name,
            $tower->sales_listing_count ?? 0,
            $tower->rent_listing_count ?? 0,
            $tower->rent_listing_count ?? 0,
            $tower->archive_listing_count ?? 0,
            $tower->created_at->format('F j, Y'),
            $tower->updated_at->format('F j, Y'),
        ];
    }
}
