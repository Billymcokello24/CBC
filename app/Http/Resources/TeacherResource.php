<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class TeacherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'staff_number' => $this->staff_number,
            'tsc_number' => $this->tsc_number,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'nationality' => $this->nationality,
            'email' => $this->email,
            'phone' => $this->phone,
            'alternate_phone' => $this->alternate_phone,
            'address' => $this->address,
            'county' => $this->county,
            'photo' => $this->photo,
            'date_joined' => $this->date_joined?->format('Y-m-d'),
            'contract_type' => $this->contract_type,
            'employment_type' => $this->employment_type,
            'status' => $this->status,
            'school' => $this->whenLoaded('school', fn() => [
                'id' => $this->school->id,
                'name' => $this->school->name,
                'code' => $this->school->code,
            ]),
            'department' => $this->whenLoaded('department', fn() => [
                'id' => $this->department->id,
                'name' => $this->department->name,
                'code' => $this->department->code,
            ]),
            'staff_category' => $this->whenLoaded('staffCategory'),
            'staff_designation' => $this->whenLoaded('staffDesignation'),
            'qualifications' => $this->whenLoaded('qualifications'),
            'certifications' => $this->whenLoaded('certifications'),
            'subject_assignments' => $this->whenLoaded('subjectAssignments'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
