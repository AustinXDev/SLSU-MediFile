<?php

namespace App\Controllers\PatientControllers;

use App\Services\PatientServices\PatientService;

class PatientController
{
    public function __construct(
        private PatientService $service
    ) {
    }


    public function getAll(array $params = []): array
    {
        return $this->service->getAll($params);
    }

    public function upsert(
        array $data
    ): array {

        return $this->service->upsert($data);

    }

    public function delete(
        array $data
    ) {

        $patientId = (int) ($data['patientId'] ?? 0);

        return $this->service->delete($patientId);

    }


    public function deleteDentalService(
        int $serviceId
    ): void {

        $this->service->deleteDentalService($serviceId);

    }

}
