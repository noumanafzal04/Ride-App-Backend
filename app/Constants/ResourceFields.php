<?php

namespace App\Constants;

class ResourceFields
{
    public const  CITIES_LIST_FIELDS = ['id', 'name', 'status'];
    public const DRIVER_PROFILE_FIELDS = [
        'cnic_number',
        'cnic_front_image',
        'cnic_back_image',
        'license_number',
        'license_front_image',
        'license_back_image',
    ];

    public const USER_PROFILE_FIELDS = [
        'profile_image',
        'dob',
        'gender',
        'city',
        'address',
        'bio',
    ];

    public const VEHICLE_CREATE_FIELDS = [
        'model_id',
        'vehicle_image_path',
        'manufacture_year',
        'color',
        'registration_number',
        'seating_capacity',
        'luggage_capacity',
        'has_air_conditioner',
    ];

    public const VEHICLE_MODEL_FIELDS = ['id', 'make_id', 'name', 'status'];
    public const VEHICLE_MAKE_FIELDS = ['id', 'name', 'status'];

    public const DRIVER_LIST_FIELDS = ['id', 'user_id', 'cnic_number', 'license_number', 'verification_status', 'is_online'];
    public const VEHICLE_MAKE_LIST_FIELDS = ['id', 'name', 'status'];
    public const VEHICLE_MODEL_LIST_FIELDS = ['id', 'make_id', 'name', 'seating_capacity', 'status'];
}
