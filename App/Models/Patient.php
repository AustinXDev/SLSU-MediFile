<?php

namespace App\Models;

class Patient
{
    private ?string $id = null;
    private ?string $firstname = null;
    private ?string $middlename = null;
    private ?string $surname = null;
    private ?string $dob = null;
    private ?string $gender = null;
    private ?string $civilStatus = null;
    private ?string $religion = null;
    private ?string $nationality = null;
    private ?string $department = null;
    private ?string $position = null;
    private ?string $contact = null;
    private ?string $email = null;
    private ?string $address = null;
    private ?string $emergencyName = null;
    private ?string $emergencyNumber = null;
    private ?string $emergencyAddress = null;
    private string $status = 'Active';


    public static function fromArray(array $data): self
    {
        $patient = new self();

        $patient->id = $data['id'] ?? null;
        $patient->firstname = $data['first_name'] ?? $data['firstName'] ?? null;
        $patient->middlename = $data['middle_name'] ?? $data['middleName'] ?? null;
        $patient->surname = $data['surname'] ?? $data['surName'] ?? null;
        $patient->dob = $data['dob'] ?? $data['birthdate'] ?? null;
        $patient->gender = $data['sex'] ?? null;
        $patient->civilStatus = $data['civil_status'] ?? null;
        $patient->religion = $data['religion'] ?? null;
        $patient->nationality = $data['nationality'] ?? null;
        $patient->department = $data['college_dept'] ?? null;
        $patient->position = $data['job_position_course'] ?? null;
        $patient->contact = $data['tel_no'] ?? $data['contactNumber'] ?? null;
        $patient->address = $data['home_address'] ?? null;
        $patient->emergencyName = $data['ice_guardian_name'] ?? null;
        $patient->emergencyNumber = $data['ice_address'] ?? null;
        $patient->emergencyAddress = $data['ice_tel_no'] ?? null;
        $patient->status = $data['status'] === 1 ? 'Active' : 'Inactive';

        return $patient;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'firstname' => $this->firstname,
            'middlename' => $this->middlename,
            'surname' => $this->surname,
            'fullName' => $this->getFullName(),
            'dob' => $this->dob,
            'age' => $this->getAge(),
            'gender' => $this->gender,
            'civilStatus' => $this->civilStatus,
            'religion' => $this->religion,
            'nationality' => $this->nationality,
            'department' => $this->department,
            'position' => $this->position,
            'contact' => $this->contact,
            'email' => $this->email,
            'address' => $this->address,
            'emergencyName' => $this->emergencyName,
            'emergencyNumber' => $this->emergencyNumber,
            'emergencyAddress' => $this->emergencyAddress,
            'status' => $this->status,
        ];
    }

    // Helper Methods
    public function getFullName(): string
    {
        $name = sprintf('%s %s %s', $this->firstname, $this->middlename, $this->surname);
        return trim(preg_replace('/\s+/', ' ', $name));
    }

    public function getAge(): ?int
    {
        if (!$this->dob) {
            return null;
        }
        $birthDate = new \DateTime($this->dob);
        $today = new \DateTime('today');
        return $birthDate->diff($today)->y;
    }

    // Getters and Setters
    public function getId(): ?string
    {
        return $this->id;
    }
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }
    public function setFirstname(?string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getMiddlename(): ?string
    {
        return $this->middlename;
    }
    public function setMiddlename(?string $middlename): void
    {
        $this->middlename = $middlename;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }
    public function setSurname(?string $surname): void
    {
        $this->surname = $surname;
    }

    public function getDob(): ?string
    {
        return $this->dob;
    }
    public function setDob(?string $dob): void
    {
        $this->dob = $dob;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }
    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }
    public function setContact(?string $contact): void
    {
        $this->contact = $contact;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
