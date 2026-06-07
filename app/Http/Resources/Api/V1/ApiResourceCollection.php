<?php 

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use stdClass;

class ApiResourceCollection extends ResourceCollection
{
    protected string $resourceClass;
    protected string $message = 'Success';
    protected ?int $statusCode = null;
    protected ?string $wrapKey = null; // e.g., 'companies', 'services', etc.
    protected array $additionalMeta = []; // Custom meta data    

    public function __construct($resource, string $resourceClass)
    {
        $this->resourceClass = $resourceClass;
        parent::__construct($resource);

         // Convert all items to resource instances automatically
        $this->collection = $this->collection->map(fn($item) => new $this->resourceClass($item));
    }

    /**
     * Propagate 'onlyFields' to each child resource
     */
    public function only(array $fields): static
    {
        $this->collection->transform(fn($resource) => $resource->only($fields));
        return $this;
    }

    /**
     * Transform collection
     */
    public function toArray($request)
    {
        //return $this->collection->map(fn($item) => (new $this->resourceClass($item))->toArray($request))->all();
        return $this->collection->map(fn($resource) => $resource->toArray($request))->all();
    }

    /**
     * Get pagination meta if resource is paginated
     */
    protected function getPaginationMeta(): array
    {
        if ($this->resource instanceof LengthAwarePaginator) {
            return array_merge($this->additionalMeta ?? [], [
                'current_page' => $this->resource->currentPage(),
                'per_page'     => $this->resource->perPage(),
                'total'        => $this->resource->total(),
                'last_page'    => $this->resource->lastPage(),
            ]);
        }

        return $this->additionalMeta ?? [];
    }
    
    /**
     * Set custom message
     */
    public function message(string $message): self
    {
        $this->message = $message;
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
     * Set the key to wrap the collection (e.g., 'companies', 'services')
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
     * Override response to wrap data and include status code
     */
    public function toResponse($request)
    {
        // Get the collection data
        $collectionData = $this->resolve($request);
        
        // Build data section with meta inside
        $dataSection = [];
        
        if ($this->wrapKey) {
            $dataSection[$this->wrapKey] = $collectionData;
        } else {
            $dataSection = $collectionData;
        }
        
        // Add meta inside data section if we have additional meta or a wrap key
        if (!empty($this->additionalMeta) || $this->wrapKey) {
            //$dataMeta = $this->additionalMeta;
            
            $dataSection['meta'] = (object) $this->getPaginationMeta();
        }
        
        // Build the full response
        $response = [
            'success' => true,
            'message' => $this->message,
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