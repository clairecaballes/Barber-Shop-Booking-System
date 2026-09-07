<?php

use App\Models\BusinessSetting;

/**
 * Format an integer centavo amount as a currency string (e.g. ₱150.00).
 */
function money(int $centavos): string
{
    $currency = BusinessSetting::get('currency', '₱');

    return $currency.number_format($centavos / 100, 2);
}
