<?php
namespace App\Support\Mappers;

class RequestToModelMap
{
    public const COMPANY_MAILING_ADDRESS = [
        'mailing_state_id' => 'state_id',
        'mailing_city_id'  => 'city_id',
        'mailing_zipcode' => 'zipcode',
    ];    
}
