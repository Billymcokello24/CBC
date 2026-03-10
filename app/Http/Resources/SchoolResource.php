<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class SchoolResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'registration_number' => $this->registration_number,
            'school_type' => $this->whenLoaded('schoolType', fn() => [
                'id' => $this->schoolType->id,
                'name' => $this->schoolType->name,
                'code' => $this->schoolType->code,
            ]),
            'school_level' => $this->whenLoaded('schoolLevel', fn() => [
                'id' => $this->schoolLevel->id,
                'name' => $this->schoolLevel->name,
                'code' => $this->schoolLevel->code,
            ]),
            'logo' => $this->logo,
            'motto' => $this->motto,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'county' => $this->county,
            'sub_county' => $this->sub_county,
            'gender_type' => $this->gender_type,
            'boarding_type' => $this->boarding_type,
            'status' => $this->status,
            'branches' => $this->whenLoaded('branches'),
            'contacts' => $this->whenLoaded('contacts'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
