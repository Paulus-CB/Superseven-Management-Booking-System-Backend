<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Http\Resources\Json\JsonResource;

class AddEmployeeWorkloadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $booking = Booking::find($request->id);

        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'selected' => $booking->employees()->wherePivot('user_id', $this->id)->exists(),
        ];
    }
}
