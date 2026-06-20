<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ApiResource extends JsonResource
{
    protected string $responseMessage = 'Success';
    protected ?int $statusCode = null;
    protected ?string $wrapKey = null; // e.g., 'company', 'service', etc.
    protected array $additionalMeta = []; // Custom meta data
    protected array $onlyFields = [];

    /**
     * Select only specific fields from the resource
     */
    public function only(array $fields): static
    {
        $this->onlyFields = $fields;
        return $this;
    }

    /**
     * Apply field filtering
     */
    protected function filterFields(array $data): array
    {
        if (empty($this->onlyFields)) {
            return $data;
        }

        return collect($data)
            ->only($this->onlyFields)
            ->toArray();
    }
    
    /**
     * Set custom message
     */
    public function message(string $message): self
    {
        $this->responseMessage = $message;
        return $this;
    }

    /**
     * Set status code
     */
    public function status(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Set the key to wrap the data (e.g., 'company', 'service')
     */
    public function wrapWith(string $key): self
    {
        $this->wrapKey = $key;
        return $this;
    }

    /**
     * Add custom meta data to the data.meta section
     */
    public function withMeta(array $meta): self
    {
        $this->additionalMeta = $meta;
        return $this;
    }

    /**
     * Create new collection with custom collection class
     */
    public static function collection($resource)
    {
        return new ApiResourceCollection($resource, static::class);
    }

    /**
     * Override response to wrap data and include status code
     */
    public function toResponse($request)
    {
        // Get the original response
        $data = $this->resolve($request);
        
        // Build data section
        $dataSection = [];
        
        if ($this->wrapKey) {
            $dataSection[$this->wrapKey] = $data;
        } else {
            $dataSection = $data;
        }
        
        // Add meta inside data section if we have additional meta or a wrap key
        if (!empty($this->additionalMeta) || $this->wrapKey) {        
            $dataSection['meta'] = (object) ($this->additionalMeta ?? []);
        }
        
        // Build the full response
        $response = [
            'success' => true,
            'message' => $this->responseMessage,
            'data' => $dataSection,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'version'   => $request->attributes->get('version'),
                'trace_id'  => $request->attributes->get('trace_id'),
            ],
        ];
        
        $jsonResponse = response()->json($response);
        
        if ($this->statusCode) {
            $jsonResponse->setStatusCode($this->statusCode);
        }
        
        return $jsonResponse;
    }

    /**
     * Disable default wrapping
     */
    public function with($request): array
    {
        return [];
    }
}
