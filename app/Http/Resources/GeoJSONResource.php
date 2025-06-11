<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeoJSONResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "type" => "Feature",
            "properties" => collect($this->resource)->except('geojson', 'geom')->toArray(),
            "geometry" => json_decode($this->geojson),
        ];
    }
}
