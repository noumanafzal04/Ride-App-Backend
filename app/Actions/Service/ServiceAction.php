<?php

namespace App\Actions\Service;

use App\Repositories\Service\ServiceCategoryRepository;

class ServiceAction
{
    public function __construct(
        protected ServiceCategoryRepository $categories,
    ) {}

    /** Active service categories for browsing + provider registration. */
    public function categories()
    {
        return $this->categories->allActive();
    }
}
