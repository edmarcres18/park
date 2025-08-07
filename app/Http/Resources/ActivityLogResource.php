<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Activity Log Resource
 *
 * Transforms activity log models into structured JSON responses
 * for API consumption.
 */
class ActivityLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'log_name' => $this->log_name,
            'description' => $this->description,
            'causer' => $this->when($this->causer, [
                'id' => $this->causer_id,
                'name' => $this->causer?->name ?? 'System',
            ]),
            'properties' => [
                'ip' => $this->properties['ip'] ?? null,
                'location' => $this->properties['location'] ?? null,
                'changes' => [
                    'old' => $this->properties['old'] ?? null,
                    'attributes' => $this->properties['attributes'] ?? null,
                ],
                'user_agent' => $this->properties['user_agent'] ?? null,
            ],
            'subject' => [
                'id' => $this->subject_id,
                'type' => $this->getModelName($this->subject_type),
            ],
            'timestamp' => $this->created_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    /**
     * Extract model name from full class path
     *
     * @param string|null $modelType
     * @return string|null
     */
    private function getModelName(?string $modelType): ?string
    {
        if (!$modelType) {
            return null;
        }

        $parts = explode('\\', $modelType);
        return end($parts);
    }
}
