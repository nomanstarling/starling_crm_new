<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class ContactsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $contacts;

    public function __construct($contacts)
    {
        $this->contacts = $contacts;
    }

    public function collection()
    {
        return $this->contacts;
    }

    public function headings(): array
    {
        return [
            'RefNo#',
            'Type',
            'Name',
            'Phone',
            //'WhatsApp',
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

    public function map($contact): array
    {
        return [
            $contact->refno,
            $contact->contact_type,
            $contact->title.' '.$contact->name,
            $contact->phone,
            $contact->email,
            $contact->dob,
            $contact->company,
            $contact->designation,
            $contact->religion,
            $contact->source ? $contact->source->name : '',
            $contact->sub_source ? $contact->sub_source->name : '',
            $contact->country,
            $contact->city,
            $contact->address,
            $contact->created_by_user ? $contact->created_by_user->name : '',
            $contact->updated_by_user ? $contact->updated_by_user->name : '',
            $contact->created_at->format('F j, Y'),
            $contact->updated_at->format('F j, Y'),
        ];
    }
}

