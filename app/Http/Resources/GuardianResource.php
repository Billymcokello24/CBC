<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class GuardianResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'id_number' => $this->id_number,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'email' => $this->email,
            'phone' => $this->phone,
            'alternate_phone' => $this->alternate_phone,
            'occupation' => $this->occupation,
            'employer' => $this->employer,
            'work_address' => $this->work_address,
            'home_address' => $this->home_address,
            'county' => $this->county,
            'sub_county' => $this->sub_county,
            'relationship_type' => $this->relationship_type,
            'photo' => $this->photo,
            'is_emergency_contact' => $this->is_emergency_contact,
            'can_pickup' => $this->can_pickup,
            'receives_communication' => $this->receives_communication,
            'is_active' => $this->is_active,
            'school' => $this->whenLoaded('school', fn() => [
                'id' => $this->school->id,
                'name' => $this->school->name,
            ]),
            'students' => $this->whenLoaded('students', fn() =>
                $this->students->map(fn($s) => [
                    'id' => $s->id,
                    'full_name' => $s->full_name,
                    'admission_number' => $s->admission_number,
                    'relationship' => $s->pivot->relationship,
                    'is_primary_contact' => $s->pivot->is_primary_contact,
                ])
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
