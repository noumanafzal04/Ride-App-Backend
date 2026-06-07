<?php

namespace App\Traits;

trait ResourceBinding
{

    /**
     * Generic fields for models
     */
    public const BILLING_CYCLE_FIELDS = ['id', 'name', 'status'];
    public const CUSTOMER_TYPES_FIELDS = ['id', 'name', 'status'];
    public const CUSTOMER_TYPES_COMPANY_FIELDS = ['id', 'company_id', 'name', 'is_default', 'status'];

    public const RATE_TIERS_FIELDS = ['id', 'usage', 'charge_rate', 'additional_charge', 'status'];
    public const RATE_TIERS_FIELDS_SELECT = [
        ...self::RATE_TIERS_FIELDS,
        'company_rate_id',
    ];

    public const USERDEFINED_FIELDS = ['id', 'field_label', 'field_placeholder', 'field_value', 'status'];
    public const USERDEFINED_FIELDS_SELECT = [
        ...self::USERDEFINED_FIELDS,
        'meter_id',
    ];

    // inventory
    public const INVENTORY_ITEM_UPCS_FIELDS = ['id', 'company_inventory_item_id', 'upc'];
    public const INVENTORY_CATEGORY_FIELDS = ['id', 'name'];
    public const INVENTORY_WAREHOUSES_FIELDS = ['id', 'company_inventory_item_id', 'company_warehouse_id', 'quantity', 'primary_location', 'secondary_location'];

    public const KIT_ITEMS_FIELDS = ['id', 'name', 'identification_no', 'last_cost', 'status'];

    // inventory item fields for "inventory item warehouses" model
    public const INVENTORY_ITEM_FIELDS = ['company_inventory_items.id', 'company_inventory_items.name', 'company_inventory_items.identification_no', 'company_inventory_items.last_cost', 'company_inventory_items.status'];
    public const INVENTORY_KIT_FIELDS = ['id', 'name', 'identification_no', 'company_warehouse_id', 'status'];


    // company warehouse
    public const COMPANY_WAREHOUSES_FIELDS = ['id', 'name', 'status'];
    public const KIT_WAREHOUSES_FIELDS = ['id', 'name', 'identification_no', 'status'];
    // company warehouse fields for "inventory item warehouses" model
    public const COMPANY_WAREHOUSES_FIELDS_FOR_IIW = ['id', 'name', 'status'];
    // public const INVENTORY_WAREHOUSES_FIELDS_SELECT = [
    //     ...self::INVENTORY_WAREHOUSES_FIELDS,
    //     'meter_id',
    // ];

    // transaction type
    public const TRANSACTION_TYPE_FIELDS = ['id', 'name', 'code', 'status'];

    // form formats
    public const FORM_FORMATS_FIELDS = ['id', 'status'];

    // work order code items
    public const WORK_ORDER_CODE_ITEMS_FIELDS = ['id', 'company_work_order_code_id', 'company_inventory_item_id', 'inventory_item_warehouse_id', 'quantity'];
    public const WORK_ORDER_CODE_KITS_FIELDS = ['id', 'company_work_order_code_id', 'company_inventory_kit_id', 'quantity'];

    // banks city,state and bank mailing address city, state
    public const STATE_FIELDS = ['id', 'name', 'country_code'];
    public const CITY_FIELDS = ['id', 'name', 'country_code'];

    /**
     * Company Customers
     */

    // customer mailing
    public const CUSTOMER_MAILING = ['id', 'house_number', 'street_name', 'state_id', 'city_id', 'zipcode', 'usps_post_net', 'is_default'];
    public const CUSTOMER_MAILING_UPDATE = ['house_number', 'street_name', 'state_id', 'city_id', 'zipcode', 'usps_post_net', 'is_default'];
    // customer contacts
    public const CUSTOMER_CONTACTS = ['id', 'relation_type', 'first_name', 'last_name', 'email', 'cell_phone_number', 'address'];
    public const CUSTOMER_CONTACTS_UPDATE = ['relation_type', 'first_name', 'last_name', 'email', 'cell_phone_number', 'address'];


    /**
     * Bindings
     */
    private function serviceBinding()
    {
        if (
            $this->relationLoaded('companyService') &&
            $this->companyService->relationLoaded('service')
        ) {
            $this->service = $this->companyService->service;
        }
    }

    private function serviceBindingReturnCompanyService()
    {
        if (
            $this->relationLoaded('companyService') &&
            $this->companyService->relationLoaded('service')
        ) {
            $this->cmpService = $this->companyService;
        }
    }
}