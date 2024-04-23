<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\countries;
use App\Models\cities;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Retrieve all countries from the database
        $countries = countries::all();

        // Loop through each country
        // foreach ($countries as $country) {
        //     // Fetch cities for the current country from the Geonames API
        //     $response = Http::get("http://api.geonames.org/searchJSON", [
        //         //'name_equals' => $country->name,
        //         'country' => $country->code,
        //         'maxRows' => 5000,
        //         'username' => 'mnafullstack',
        //     ]);

        //     // Extract cities from the API response
        //     $citiesData = $response->json()['geonames'] ?? [];

        //     // Insert cities into the 'cities' table with the corresponding 'country_id'
        //     foreach ($citiesData as $cityData) {
        //         cities::create([
        //             'name' => $cityData['name'],
        //             'country_id' => $country->id,
        //         ]);
        //         $this->command->info("City Name {$cityData['name']}");
        //     }

        //     // Output progress information
        //     $this->command->info("Cities inserted for {$country->name}");
        // }

        $response = Http::get("http://api.geonames.org/searchJSON", [
            //'name_equals' => $country->name,
            'country' => 'ae',
            'maxRows' => 5000,
            'username' => 'mnafullstack',
        ]);

        // Extract cities from the API response
        $citiesData = $response->json()['geonames'] ?? [];

        foreach ($citiesData as $cityData) {
            // City::create([
            //     'name' => $cityData['toponymName'],
            //     'country_id' => $country->id,
            //     'latitude' => $cityData['lat'],
            //     'longitude' => $cityData['lng'],
            //     // Add other city attributes as needed
            // ]);

            $this->command->info($cityData['toponymName']);
        }

        // Insert cities into the 'cities' table with the corresponding 'country_id'
        
        // foreach ($citiesData as $cityData) {
        //     cities::create([
        //         'name' => $cityData['name'],
        //         'country_id' => $country->id,
        //     ]);
        //     $this->command->info("City Name {$cityData['name']}");
        // }

        // Output progress information
        //$this->command->info("Cities inserted for {$country->name}");

        $this->command->info('Cities seeding completed.');
    }
}
