<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'admission_number' => $this->admission_number,
            'upi' => $this->upi,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'age' => $this->age,
            'nationality' => $this->nationality,
            'religion' => $this->religion,
            'home_address' => $this->home_address,
            'county' => $this->county,
            'sub_county' => $this->sub_county,
            'photo' => $this->photo,
            'admission_date' => $this->admission_date?->format('Y-m-d'),
            'blood_group' => $this->blood_group,
            'has_special_needs' => $this->has_special_needs,
            'boarding_status' => $this->boarding_status,
            'status' => $this->status,
            'school' => $this->whenLoaded('school', fn() => [
                'id' => $this->school->id,
                'name' => $this->school->name,
                'code' => $this->school->code,
            ]),
            'current_class' => $this->whenLoaded('currentClass', fn() => [
                'id' => $this->currentClass->id,
                'name' => $this->currentClass->name,
                'code' => $this->currentClass->code,
                'grade_level' => $this->currentClass->gradeLevel?->name,
                'stream' => $this->currentClass->stream?->name,
            ]),
            'guardians' => $this->whenLoaded('guardians', fn() => 
                $this->guardians->map(fn($g) => [
                    'id' => $g->id,
                    'full_name' => $g->full_name,
                    'phone' => $g->phone,
                    'email' => $g->email,
                    'relationship' => $g->pivot->relationship,
                    'is_primary_contact' => $g->pivot->is_primary_contact,
                ])
            ),
            'enrollments' => $this->whenLoaded('enrollments'),
            'documents' => $this->whenLoaded('documents'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
