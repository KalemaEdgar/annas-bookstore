<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BooksResource extends JsonResource
{
    /**
     * Transform the resource into an array.
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
                    'data' => AuthorsIdentifierResource::collection(
                        $this->authors),
                ],
            ]
        ];
        
    }

}