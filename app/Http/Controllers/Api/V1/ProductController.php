<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\ApiResponse;

class ProductController extends Controller
{
    /**
     * Obtener productos activos disponibles
     * para el registro de facturas.
     */
    public function index()
    {
        $products = Product::query()
            ->with([
                'brand:id,name',
                'category:id,name',
            ])
            ->where('is_active', true)
            ->whereHas('brand', function ($query) {
                $query->where('name', 'EOFERTIL');
            })
            ->orderBy('name')
            ->get()
            ->map(function (Product $product) {
                return [
                    'id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'description' => $product->description,

                    'brand' => $product->brand
                        ? [
                            'id' => $product->brand->id,
                            'name' => $product->brand->name,
                        ]
                        : null,

                    'category' => $product->category
                        ? [
                            'id' => $product->category->id,
                            'name' => $product->category->name,
                        ]
                        : null,

                    'image_url' => $product->image_url,
                ];
            })
            ->values();

        return ApiResponse::success(
            $products,
            'Productos obtenidos correctamente.'
        );
    }
}
