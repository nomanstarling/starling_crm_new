<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class OwnersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $owners;

    public function __construct($owners)
    {
        $this->owners = $owners;
    }

    public function collection()
    {
        return $this->owners;
    }

    public function headings(): array
    {
        return [
            'RefNo#',
            'Name',
            'Phone',
            'WhatsApp',
            'Email',
            'DOB',
            'Company',
            'Designation',
            'Religion',
            'Source',
            'Sub Source',
            'Country',
            'City',
            'Address',
            'Created By',
            'Updated By',
            'Created Date',
            'Updated Date',
        ];
    }

    public function map($owner): array
    {
        return [
            $owner->refno,
            $owner->title.' '.$owner->name,
            $owner->phone,
            $owner->whatsapp,
            $owner->email,
            $owner->dob,
            $owner->company,
            $owner->designation,
            $owner->religion,
            $owner->source ? $owner->source->name : '',
            $owner->sub_source ? $owner->sub_source->name : '',
            $owner->country,
            $owner->city,
            $owner->address,
            $owner->created_by_user ? $owner->created_by_user->name : '',
            $owner->updated_by_user ? $owner->updated_by_user->name : '',
            $owner->created_at->format('F j, Y'),
            $owner->updated_at->format('F j, Y'),
        ];
    }
}

