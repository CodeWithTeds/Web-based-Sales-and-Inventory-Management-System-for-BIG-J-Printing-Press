<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Services\AddressService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AddressForm extends Component
{
    public $regions = [];
    public $provinces = [];
    public $cities = [];
    public $barangays = [];
    
    public $region_code = '';
    public $province_code = '';
    public $city_code = '';
    public $barangay_code = '';
    public $exact_address = '';
    public $latitude = null;
    public $longitude = null;
    
    protected $addressService;
    
    protected $rules = [
        'region_code' => 'required',
        'province_code' => 'required',
        'city_code' => 'required',
        'barangay_code' => 'required',
        'exact_address' => 'nullable|string|max:255',
    ];
    
    public function boot(AddressService $addressService)
    {
        $this->addressService = $addressService;
    }
    
    public function mount()
    {
        $this->regions = $this->addressService->getRegions();
        
        $address = $this->addressService->getUserAddress();
        
        if ($address) {
            $this->region_code = $address->region_code;
            $this->province_code = $address->province_code;
            $this->city_code = $address->city_code;
            $this->barangay_code = $address->barangay_code;
            $this->exact_address = $address->exact_address;
            $this->latitude = $address->latitude;
            $this->longitude = $address->longitude;
            
            $this->loadProvinces();
            $this->loadCities();
            $this->loadBarangays();
        }
    }
    
    public function updatedRegionCode()
    {
        $this->province_code = '';
        $this->city_code = '';
        $this->barangay_code = '';
        $this->provinces = [];
        $this->cities = [];
        $this->barangays = [];
        
        $this->loadProvinces();
    }
    
    public function updatedProvinceCode()
    {
        $this->city_code = '';
        $this->barangay_code = '';
        $this->cities = [];
        $this->barangays = [];
        
        $this->loadCities();
    }
    
    public function updatedCityCode()
    {
        $this->barangay_code = '';
        $this->barangays = [];
        
        $this->loadBarangays();
    }
    
    public function updatedBarangayCode()
    {
        $this->updateCoordinates();
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'region_code') {
            $this->updatedRegionCode();
        } elseif ($propertyName === 'province_code') {
            $this->updatedProvinceCode();
        } elseif ($propertyName === 'city_code') {
            $this->updatedCityCode();
        } elseif ($propertyName === 'barangay_code') {
            $this->updateCoordinates();
        }
    }
    
    public function updateCoordinates()
    {
        if ($this->region_code && $this->province_code && $this->city_code && $this->barangay_code) {
            // Resolve human-readable names from codes
            $regionName = collect($this->regions)->firstWhere('code', $this->region_code)['name'] ?? '';
            $provinceName = collect($this->provinces)->firstWhere('code', $this->province_code)['name'] ?? '';
            $cityName = collect($this->cities)->firstWhere('code', $this->city_code)['name'] ?? '';
            $barangayName = collect($this->barangays)->firstWhere('code', $this->barangay_code)['name'] ?? '';
        
            // Try precise geocoding using OpenStreetMap Nominatim
            $didGeocode = $this->geocodeAndSetCoordinates($regionName, $provinceName, $cityName, $barangayName);
        
            if (!$didGeocode) {
                // Fallback: coarse coordinates per region (no synthetic diagonal offsets)
                switch ($this->region_code) {
                    case 'NCR':
                        $this->latitude = 14.5995; $this->longitude = 120.9842; break;
                    case '01':
                        $this->latitude = 17.5707; $this->longitude = 120.3865; break;
                    case '02':
                        $this->latitude = 16.9754; $this->longitude = 121.8107; break;
                    case '03':
                        $this->latitude = 15.4827; $this->longitude = 120.7120; break;
                    case '04':
                    case '04A': // CALABARZON sometimes encoded as 04A
                        $this->latitude = 14.1008; $this->longitude = 121.0794; break;
                    default:
                        $this->latitude = 12.8797; $this->longitude = 121.7740; // Center of PH
                }
            }
        }
    }

    private function geocodeAndSetCoordinates(string $regionName, string $provinceName, string $cityName, string $barangayName): bool
    {
        $queries = array_filter([
            trim("$barangayName, $cityName, $provinceName, Philippines"),
            trim("$cityName, $provinceName, Philippines"),
            trim("$provinceName, Philippines"),
            trim("$regionName, Philippines"),
        ]);
    
        foreach ($queries as $q) {
            try {
                $resp = Http::withHeaders([
                    'User-Agent' => (config('app.name') ?: 'IMS') . ' (+http://localhost)',
                    'Accept-Language' => 'en',
                ])->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $q,
                    'format' => 'jsonv2',
                    'limit' => 1,
                ]);
    
                if ($resp->ok()) {
                    $data = $resp->json();
                    if (is_array($data) && !empty($data)) {
                        $lat = isset($data[0]['lat']) ? (float) $data[0]['lat'] : null;
                        $lon = isset($data[0]['lon']) ? (float) $data[0]['lon'] : null;
                        if ($lat !== null && $lon !== null) {
                            $this->latitude = round($lat, 6);
                            $this->longitude = round($lon, 6);
                            return true;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Silently ignore and try the next query or fallback
            }
        }
    
        return false;
    }
    
    public function loadProvinces()
    {
        if ($this->region_code) {
            $this->provinces = $this->addressService->getProvinces($this->region_code);
        }
    }
    
    public function loadCities()
    {
        if ($this->province_code) {
            $this->cities = $this->addressService->getCities($this->province_code);
        }
    }
    
    public function loadBarangays()
    {
        if ($this->city_code) {
            $this->barangays = $this->addressService->getBarangays($this->city_code);
        }
    }
    
    public function save()
    {
        $this->validate();
        
        $data = [
            'region_code' => $this->region_code,
            'province_code' => $this->province_code,
            'city_code' => $this->city_code,
            'barangay_code' => $this->barangay_code,
            'exact_address' => $this->exact_address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
        
        $this->addressService->saveUserAddress($data);
        
        session()->flash('status', 'address-updated');
    }
    
    public function render()
    {
        return view('livewire.settings.address-form');
    }
}