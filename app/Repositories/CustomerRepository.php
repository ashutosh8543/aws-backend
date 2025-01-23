<?php

namespace App\Repositories;

use App\Models\Customer;

/**
 * Class CustomerRepository
 */
class CustomerRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'email',
        'phone',
        'country',
        'dob',
        'city',
        'address',
        'postal_code',
        'image',
        'latitude',
        'longitude',
        'created_at',
        'unique_code',
        'chanel_id',
    ];

    /**
     * Return searchable fields
     */
    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Customer::class;
    }
}
