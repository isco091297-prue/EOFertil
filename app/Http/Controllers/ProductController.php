<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->get('search', ''));
        $brandId = $request->get('brand_id');
        $categoryId = $request->get('category_id');

        $products = Product::with(['brand', 'category'])

            ->when($search !== '', function ($query) use ($search) {

                $query->where(function ($q) use ($search) {

                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })

            ->when($brandId !== null && $brandId !== '', function ($query) use ($brandId) {

                $query->where('brand_id', $brandId);
            })

            ->when($categoryId !== null && $categoryId !== '', function ($query) use ($categoryId) {

                $query->where('category_id', $categoryId);
            })

            ->orderBy('name')

            ->paginate(10)

            ->withQueryString();

        $brands = Brand::where('is_active', true)
            ->orderBy('name')
            ->get();

        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'products.index',
            compact(
                'products',
                'search',
                'brandId',
                'categoryId',
                'brands',
                'categories'
            )
        );
    }
    public function create(): View
    {
        $brands = Brand::where('is_active', true)
            ->orderBy('name')
            ->get();

        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'products.create',
            compact('brands', 'categories')
        );
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {

            $data['image_path'] = $request->file('image')
                ->store('products', 'public');
        }

        unset($data['image']);

        Product::create($data);

        return redirect()

            ->route('products.index')

            ->with(
                'success',
                'Producto creado correctamente.'
            );
    }

    public function show(Product $product): View
    {
        $product->load(['brand', 'category']);

        return view(
            'products.show',
            compact('product')
        );
    }

    public function edit(Product $product): View
    {
        $brands = Brand::where('is_active', true)
            ->orderBy('name')
            ->get();

        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'products.edit',
            compact(
                'product',
                'brands',
                'categories'
            )
        );
    }

    public function update(
        UpdateProductRequest $request,
        Product $product
    ): RedirectResponse {

        $data = $request->validated();

        if ($request->hasFile('image')) {

            if (
                $product->image_path &&
                Storage::disk('public')->exists($product->image_path)
            ) {

                Storage::disk('public')
                    ->delete($product->image_path);
            }

            $data['image_path'] = $request->file('image')
                ->store('products', 'public');
        }

        unset($data['image']);

        $product->update($data);

        return redirect()

            ->route('products.index')

            ->with(
                'success',
                'Producto actualizado correctamente.'
            );
    }

    public function destroy(Product $product): RedirectResponse
    {
        if (
            $product->image_path &&
            Storage::disk('public')->exists($product->image_path)
        ) {

            Storage::disk('public')
                ->delete($product->image_path);
        }

        $product->delete();

        return redirect()

            ->route('products.index')

            ->with(
                'success',
                'Producto eliminado correctamente.'
            );
    }
}
