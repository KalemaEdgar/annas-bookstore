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

    public function createResource(string $modelClass, array $attributes)
    {
        $model = $modelClass::create($attributes);

        return (new JSONAPIResource($model))
            ->response()
            ->header('Location', route("{$model->type()}.show", [
                Str::singular($model->type()) => $model,
            ]));
    }

    public function updateResource($model, $attributes)
    {
        $model->update($attributes);
        return new JSONAPIResource($model);
    }

    public function deleteResource($model)
    {
        $model->delete();
        return response(null, 204);
    }

    public function fetchRelationship($model, string $relationship)
    {
        return JSONAPIIdentifierResource::collection($model->$relationship);
    }

    public function updateManyToManyRelationships($model, $relationship, $ids)
    {
        $model->$relationship()->sync($ids);
        return response(null, 204);
    }

    public function fetchRelated($model, $relationship)
    {
        return new JSONAPICollection($model->$relationship);
    }

}