<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class ListingsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $listings;

    public function __construct($listings)
    {
        $this->listings = $listings;
    }

    public function collection()
    {
        return $this->listings;
    }

    public function headings(): array
    {
        return [
            'RefNo#',
            'External Ref No#',
            'Status',
            'Property For',
            'Category',
            'Property Type',
            'Unit No',
            'City',
            'Community',
            'Sub Community',
            'Tower',
            'Beds',
            'Baths',
            'Price',
            'BUA',
            'Rera Permit',
            'Furnished',
            
            'Listing Agent',
            'Marketing Agent',
            'Created By',
            'Updated By',
            'Added On',
            'Updated On',
            'Published On',
            'Project Status',
            'Plot Area',
            'Occupancy',
            'Cheques',
            'Developer',
            'Property Title',
            'Description',
            'Owner',
            'Email',
            'Phone',
        ];
    }

    public function map($listing): array
    {
        $description = str_replace(["\r\n", "\r", "\n"], "\n", strip_tags($listing->desc));
        return [
            $listing->refno,
            $listing->external_refno,
            $listing->status ? $listing->status->name : null,
            $listing->property_for,
            $listing->category ? $listing->category->name : null,
            $listing->prop_type ? $listing->prop_type->name : null,
            $listing->unit_no,
            $listing->city ? $listing->city->name : null,
            $listing->community ? $listing->community->name : null,
            $listing->sub_community ? $listing->sub_community->name : null,
            $listing->tower ? $listing->tower->name : null,
            $listing->beds,
            $listing->baths,
            $listing->price,
            $listing->bua,

            $listing->rera_permit,
            $listing->furnished,
            
            $listing->listing_agent ? $listing->listing_agent->name : null,
            $listing->marketing_agent ? $listing->marketing_agent->name : null,
            $listing->created_by_user ? $listing->created_by_user->name : '',
            $listing->updated_by_user ? $listing->updated_by_user->name : '',

            $listing->created_at ? $listing->created_at->format('F j, Y') : null,
            $listing->updated_at ? $listing->updated_at->format('F j, Y') : null,
            // $listing->published_at ? $listing->published_at->format('F j, Y') : null,
            $listing->published_at ? (new Carbon($listing->published_at))->format('F j, Y') : null,

            $listing->project_status ? $listing->project_status->name : null,
            $listing->plot_area,
            $listing->occupancy ? $listing->occupancy->name : null,
            $listing->cheques,
            $listing->developer ? $listing->developer->name : null,
            $listing->title,
            $description,
            $listing->owner ? $listing->owner->name : null,
            $listing->owner ? $listing->owner->email : null,
            $listing->owner ? $listing->owner->phone : null,
            
        ];
    }
}
