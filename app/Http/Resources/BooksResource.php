<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BooksResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * 
     * A resource is basically a transformer that converts the data of your Eloquent model into the desired JSON object you want for your API.
     * The transformation happens through the toArray method, where you define both the structure and values for your members according to the attributes on your Eloquent model.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Change the response format to adhere to the JSON:API specification
        return [
            'id' => (string)$this->id,
            'type' => 'books',
            'attributes' => [
                'title' => $this->title,
                'description' => $this->description,
                'publication_year' => $this->publication_year,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'relationships' => [
                'authors' => [
                    'links' => [
                        'self' => route('books.relationships.authors', ['book' => $this->id]),
                        'related' => route('books.authors', ['book' => $this->id]),
                    ],
                    'data' => AuthorsIdentifierResource::collection($this->authors),
                    // 'data'  => AuthorsIdentifierResource::collection($this->whenLoaded('authors')),
                ],
            ]
        ];
        
    }

    public function with($request)
    {
        $with = [];
        if ($this->included($request)->isNotEmpty()) {
            $with['included'] = $this->included($request);
        }
        return $with;
    }

    public function included($request)
    {
        return collect($this->relations())
            ->filter(function ($resource) {
                return $resource->collection !== null;
            })->flatMap->toArray($request);
    }

    // Through this, we either get a collection of resource objects for authors or an empty collection.
    private function relations()
    {
        return [
            AuthorsResource::collection($this->whenLoaded('authors')),
        ];
    }

}