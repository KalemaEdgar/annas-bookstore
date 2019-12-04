<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BooksCollection extends ResourceCollection
{

    public $collects = BooksResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            // 'included' => $this->mergeIncludedRelations($request),
        ];
        // return parent::toArray($request);
    }

    private function mergeIncludedRelations($request)
    {
        $includes = $this->collection->flatMap->included($request)->unique()->values();
        return $includes->isNotEmpty() ? $includes : new MissingValue();
    }
}
