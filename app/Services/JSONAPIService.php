<?php

namespace App\Services;

use App\Http\Resources\JSONAPICollection;
use App\Http\Resources\JSONAPIIdentifierResource;
use App\Http\Resources\JSONAPIResource;
use Illuminate\Support\Str;

class JSONAPIService 
{
    // Contents from the AuthorsController show method
    public function fetchResource($model)
    {
        return new JSONAPIResource($model);
    }

    // public function fetchResource($model, $id = 0, $type = '')
    // {
    //     if ($model instanceof Model) 
    //     {
    //         // dd('asdasdad');
    //         return new JSONAPIResource($model);
    //     }

    //     // dd($model, $id, $type);
    //     return new JSONAPIResource($id);
    // }

    public function fetchResources(string $modelClass)
    {
        $authors = $modelClass::all();
        // return AuthorsResource::collection($authors);
        return new JSONAPICollection($authors);
    }

    public function createResource(string $modelClass, array $attributes, array $relationships = null)
    {
        $model = $modelClass::create($attributes);

        if ($relationships) {
            $this->handleRelationship($relationships, $model);
        }

        return (new JSONAPIResource($model))
            ->response()
            ->header('Location', route("{$model->type()}.show", [
                Str::singular($model->type()) => $model,
            ]));
    }

    protected function handleRelationship(array $relationships, $model): void
    {
        foreach ($relationships as $relationshipName => $contents) {
            if ($model->$relationshipName() instanceof BelongsTo) {
                $this->updateToOneRelationship($model, $relationshipName, $contents['data']['id']);
            }
            if ($model->$relationshipName() instanceof BelongsToMany) {
                $this->updateManyToManyRelationships($model, $relationshipName, collect($contents['data'])->pluck('id'));
            }
        }
        
        // Model needs to know about the relationships
        $model->load(array_keys($relationships));
    }

    public function updateResource($model, $attributes, $relationships = null)
    {
        $model->update($attributes);
        if ($relationships) {
            $this->handleRelationship($relationships, $model);
        }
        return new JSONAPIResource($model);
    }

    public function deleteResource($model)
    {
        $model->delete();
        return response(null, 204);
    }

    // public function fetchRelationship($model, string $relationship)
    // {
    //     return JSONAPIIdentifierResource::collection($model->$relationship);
    // }

    public function fetchRelationship($model, string $relationship)
    {
        // Check for a single instance of a model and act accordingly
        if ($model->$relationship instanceof Model) 
        {
            return new JSONAPIIdentifierResource($model->$relationship);
        }

        return JSONAPIIdentifierResource::collection($model->$relationship);
    }

    public function updateToOneRelationship($model, $relationship, $id)
    {
        // Get the related model
        $relatedModel = $model->$relationship()->getRelated();
        // Disassociate any existing relations
        $model->$relationship()->dissociate();

        // If the id is given, associate the model
        if ($id) {
            // If an id is given, try to find it and associate that.
            $newModel = $relatedModel->newQuery()->findOrFail($id);
            $model->$relationship()->associate($newModel);
        }
        // Otherwise, just remove the associated model and return
        $model->save();
        return response(null, 204);
    }

    public function updateToManyRelationships($model, $relationship, $ids)
    {
        $foreignKey = $model->$relationship()->getForeignKeyName();
        $relatedModel = $model->$relationship()->getRelated();

        $relatedModel->newQuery()->findOrFail($ids);

        $relatedModel->newQuery()->where($foreignKey, $model->id)->update([
            $foreignKey => null,
        ]);

        $relatedModel->newQuery()->whereIn('id', $ids)->update([
            $foreignKey => $model->id,
        ]);

        return response(null, 204);
    }

    /**
     * @param $model
     * @param $relationship
     * @param $ids
     *
     * @return Response
     */
    public function updateManyToManyRelationships($model, $relationship, $ids)
    {
        $model->$relationship()->sync($ids);
        return response(null, 204);
    }

    // public function fetchRelated($model, $relationship)
    // {
    //     return new JSONAPICollection($model->$relationship);
    // }
    public function fetchRelated($model, $relationship)
    {
        if ($model->$relationship instanceof Model) 
        {
            return new JSONAPIResource($model->$relationship);
        }

        return new JSONAPICollection($model->$relationship);
    }

}