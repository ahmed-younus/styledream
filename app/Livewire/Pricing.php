<?php

namespace App\Livewire;

use App\Services\CurrencyService;
use App\Services\PricingService;
use Livewire\Component;

class Pricing extends Component
{
    public string $currency = 'usd';

    public function mount()
    {
        $this->currency = CurrencyService::getUserCurrency();
    }

    public function setCurrency(string $currency)
    {
        if (array_key_exists($currency, CurrencyService::CURRENCIES)) {
            $this->currency = $currency;
            CurrencyService::setUserCurrency($currency);
        }
    }

    public function render()
    {
        return view('livewire.pricing', [
            'creditPacks' => PricingService::getCreditPacksForCurrency($this->currency),
            'plans' => PricingService::getPlansForCurrency($this->currency),
            'currency' => $this->currency,
            'currencySymbol' => CurrencyService::getSymbol($this->currency),
            'currencies' => CurrencyService::getSupportedCurrencies(),
        ])->layout('layouts.app', ['title' => 'Pricing']);
    }
}
