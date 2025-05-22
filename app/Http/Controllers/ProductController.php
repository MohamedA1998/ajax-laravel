<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page = request()->get('page', 1);
        $perPage = 10;

        $query = Product::latest('id');

        $query->when(request('search'), function ($query) {
            $query->where('name', 'like', '%' . request('search') . '%');
        })
        ->when(request('parent_id'), function ($query) {
            $query->where('parent_id', request('parent_id'));
        });
        
        $allProducts = $query->get()->where('parent_id', '!=', null);
        $total = $allProducts->count();
        $products = $allProducts->slice(($page - 1) * $perPage, $perPage)->values();

        $pagination = [
            'current_page' => (int)$page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
        ];

        if(request()->ajax()) {
            return response()->json([
                'products' => $products,
                'productsParent' => $query->whereNull('parent_id')->get(),
                'pagination' => $pagination,
            ]);
        }

        return view('products.index', [
            'products' => $products,
            'productsParent' => $query->whereNull('parent_id')->get(),
            'pagination' => $pagination,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'parent_id' => 'nullable|exists:products,id',
            'description' => 'nullable|string',
        ]);

        $product = Product::create($request->all());

        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'parent_id' => 'nullable|exists:products,id',
            'description' => 'nullable|string',
        ]);

        $product->update($request->all());

        return response()->json($product);
    }
    

    public function bulkDelete(Request $request)
    {
        Product::whereIn('id', $request->ids)->delete();

        return response()->json(['success' => true]);
    }
}
