<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JSONAPIResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => (string)$this->id,
            'type' => $this->type(),
            'attributes' => $this->allowedAttributes(),
            // 'relationships' => $this->prepareRelationships(),
        ];
    }

    private function prepareRelationships() 
    {
        $collection = collect(config("jsonapi.resources.{$this->type()}.relationships"))->flatMap(function($related) {
            $relatedType = $related['type'];
            $relationship = $related['method'];

            return [
                $relatedType => [
                    'links' => [
                        'self' => route(
                            "{$this->type()}.relationships.{$relatedType}",
                            [substr($this->type(), 0, -1) => $this->id], // Will pick the singular version of the type and use that as the named parameter in the route. eg type() returns comments but the route requires comment so substr($this->type(), 0, -1) gives us comment
                            // ['id' => $this->id], Was this before I implemented the above
                        ),
                        'related' => route(
                            "{$this->type()}.{$relatedType}",
                            [substr($this->type(), 0, -1) => $this->id],
                            // ['id' => $this->id], Was this before I implemented the above
                        ),
                    ],
                    'data' => $this->prepareRelationshipData($relatedType, $relationship),
                ],
            ];
        });

        return $collection->count() > 0 ? $collection : new MissingValue();
    }

    private function prepareRelationshipData($relatedType, $relationship) 
    {
        if ($this->whenLoaded($relationship) instanceof MissingValue) 
        {
            return new MissingValue();
        }

        if ($this->$relationship() instanceof BelongsTo) 
        {
            return new JSONAPIIdentifierResource($this->$relationship);
        }

        return JSONAPIIdentifierResource::collection($this->$relationship);
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

    private function relations()
    {
        return collect(config("jsonapi.resources.{$this->type()}.relationships"))->map(function($relation){
            $modelOrCollection = $this->whenLoaded($relation['method']);

            if($modelOrCollection instanceof Model){
                $modelOrCollection = collect([new JSONAPIResource($modelOrCollection)]);
            }

            return JSONAPIResource::collection($modelOrCollection);
        });
    }

}
