<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Services\AddressService;
use Illuminate\Support\Facades\Auth;

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

    public function updated($propertyName)
    {
        if ($propertyName === 'region_code') {
            $this->updatedRegionCode();
        } elseif ($propertyName === 'province_code') {
            $this->updatedProvinceCode();
        } elseif ($propertyName === 'city_code') {
            $this->updatedCityCode();
        }
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
        ];
        
        $this->addressService->saveUserAddress($data);
        
        session()->flash('status', 'address-updated');
    }
    
    public function render()
    {
        return view('livewire.settings.address-form');
    }
}