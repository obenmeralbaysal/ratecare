<?php

namespace App\Models;

/**
 * Currency Model
 */
class Currency extends BaseModel
{
    protected $table = 'currencies';
    protected $fillable = [
        'name', 'code', 'symbol', 'exchange_rate', 'is_active', 'rate_updated_at'
    ];
    
    /**
     * Get active currencies
     */
    public function getActive()
    {
        return $this->where('is_active', 1)->orderBy('code', 'ASC');
    }
    
    /**
     * Find currency by code
     */
    public function findByCode($code)
    {
        return $this->whereFirst('code', strtoupper($code));
    }
    
    /**
     * Update exchange rate
     */
    public function updateRate($currencyId, $rate)
    {
        return $this->update($currencyId, [
            'exchange_rate' => $rate,
            'rate_updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Convert amount between currencies
     */
    public function convert($amount, $fromCurrency, $toCurrency)
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }
        
        $from = $this->findByCode($fromCurrency);
        $to = $this->findByCode($toCurrency);
        
        if (!$from || !$to) {
            return $amount; // Return original if currencies not found
        }
        
        // Convert to USD first, then to target currency
        $usdAmount = $amount / $from['exchange_rate'];
        $convertedAmount = $usdAmount * $to['exchange_rate'];
        
        return round($convertedAmount, 2);
    }
    
    /**
     * Format amount with currency symbol
     */
    public function formatAmount($amount, $currencyCode)
    {
        $currency = $this->findByCode($currencyCode);
        
        if (!$currency) {
            return number_format($amount, 2);
        }
        
        return $currency['symbol'] . number_format($amount, 2);
    }
    
    /**
     * Get currency dropdown list
     */
    public function getDropdownList()
    {
        $currencies = $this->getActive();
        $list = [];
        
        foreach ($currencies as $currency) {
            $list[$currency['code']] = $currency['code'] . ' - ' . $currency['name'];
        }
        
        return $list;
    }
    
    /**
     * Get exchange rates for all active currencies
     */
    public function getExchangeRates()
    {
        $currencies = $this->getActive();
        $rates = [];
        
        foreach ($currencies as $currency) {
            $rates[$currency['code']] = [
                'rate' => $currency['exchange_rate'],
                'symbol' => $currency['symbol'],
                'updated_at' => $currency['rate_updated_at']
            ];
        }
        
        return $rates;
    }
    
    /**
     * Update all exchange rates (placeholder for API integration)
     */
    public function updateAllRates($rates)
    {
        foreach ($rates as $code => $rate) {
            $currency = $this->findByCode($code);
            
            if ($currency) {
                $this->updateRate($currency['id'], $rate);
            }
        }
    }
    
    /**
     * Get hotels using this currency
     */
    public function hotels()
    {
        return $this->hasMany('App\Models\Hotel', 'currency', 'code');
    }
    
    /**
     * Get rates in this currency
     */
    public function rates()
    {
        return $this->hasMany('App\Models\Rate', 'currency', 'code');
    }
    
    /**
     * Activate currency
     */
    public function activate($currencyId)
    {
        return $this->update($currencyId, ['is_active' => 1]);
    }
    
    /**
     * Deactivate currency
     */
    public function deactivate($currencyId)
    {
        return $this->update($currencyId, ['is_active' => 0]);
    }
}
