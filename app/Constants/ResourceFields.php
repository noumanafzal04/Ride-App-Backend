<?php

namespace App\Constants;

class ResourceFields
{
    public const CITY_FIELDS = ['id', 'name', 'lat', 'lon'];
    public const DRIVER_PROFILE_FIELDS = [
        'id',
        'user_id',
        'cnic_number',
        'cnic_front_image',
        'cnic_back_image',
        'license_number',
        'license_front_image',
        'license_back_image',
    ];

    public const USER_PROFILE_FIELDS = [
        'id',       // ← missing
        'user_id',  // ← missing — FK to match hasOne
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
    public const VEHICLE_FIELDS = [
        'id',
        'user_id',
        'model_id',
        'vehicle_image_path',
        'manufacture_year',
        'color',
        'registration_number',
        'seating_capacity',
        'luggage_capacity',
        'has_air_conditioner',
        'status',
    ];

    public const VEHICLE_MODEL_FIELDS = ['id', 'make_id', 'name', 'status'];
    public const VEHICLE_MAKE_FIELDS = ['id', 'name', 'status'];

    public const DRIVER_LIST_FIELDS = ['id', 'user_id', 'cnic_number', 'license_number', 'verification_status', 'is_online'];
    public const VEHICLE_MAKE_LIST_FIELDS = ['id', 'name', 'status'];
    public const VEHICLE_MODEL_LIST_FIELDS = ['id', 'make_id', 'name', 'seating_capacity', 'status'];

    //post ride


    public const RIDE_POST_DRIVER_FIELDS = ['id', 'first_name', 'last_name', 'phone_number'];
    public const RIDE_POST_VEHICLE_FIELDS = ['id', 'user_id', 'model_id', 'vehicle_image_path', 'color', 'seating_capacity'];

    public const RIDE_POST_LIST_FIELDS = [
        'id',
        'driver_id',
        'from_city_id',
        'to_city_id',
        'departure_at',
        'available_seats',
        'price_per_seat',
        'luggage_allowed',
        'post_type',
        //        'status',
        'from_address',
        'from_latitude',
        'from_longitude',
        'to_address',
        'to_latitude',
        'to_longitude',
        'notes'
    ];


    // WRITE constants — no id, no FK
    public const USER_PROFILE_CREATE_FIELDS = [
        'profile_image',
        'dob',
        'gender',
        'city',
        'address',
        'bio',
    ];

    public const DRIVER_PROFILE_CREATE_FIELDS = [
        'cnic_number',
        'cnic_front_image',
        'cnic_back_image',
        'license_number',
        'license_front_image',
        'license_back_image',
    ];
}
