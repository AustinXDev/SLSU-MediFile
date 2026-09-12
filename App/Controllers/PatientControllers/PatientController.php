<?php

namespace App\Controllers\PatientControllers;

use App\Services\PatientServices\PatientService;

class PatientController
{
    public function __construct(
        private PatientService $service
    ) {
    }


    public function getAll(): array
    {

        return $this->service->getAll();

    }

    public function upsert(
        array $data
    ): array {

        return $this->service->upsert($data);

    }

}
