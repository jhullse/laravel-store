<?php

namespace App\Http\Controllers;

use App\Category;
use App\Importation;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::paginate();

        return view('products.index', ['products' => $products]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();

        return view('products.create', ['categories' => $categories]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'category' => 'required|integer|exists:categories,id',
            'description' => 'required|string'
        ]);

        $product = new Product($request->all());
        $product->category()->associate($request->category);
        $product->save();

        return redirect()
            ->route('products.index')
            ->with('alert', [
                'type' => 'success',
                'message' => 'Product created'
            ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $categories = Category::all();

        return view('products.edit', [
            'categories' => $categories,
            'product' => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'category' => 'required|integer|exists:categories,id',
            'description' => 'required|string'
        ]);

        $product->fill($request->all());
        $product->category()->associate($request->category);
        $product->save();

        return redirect()
            ->route('products.index')
            ->with('alert', [
                'type' => 'success',
                'message' => 'Product updated'
            ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('alert', [
                'type' => 'success',
                'message' => 'Product deleted'
            ]);
    }

    /**
     * Upload importation file.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|mimes:csv,txt|max:5120'
        ]);

        $location = 'upload/importations';
        $filename = Uuid::uuid4()->toString() . '.csv';

        $importation = new Importation(['path' => "app/${location}/${filename}"]);
        $importation->user()->associate(Auth::user()->id);
        $importation->save();

        $request->file->storeAs($location, $filename);

        return redirect()
            ->route('products.index')
            ->with('alert', [
                'type' => 'success',
                'message' => 'File uploaded. You will be notified by email when importation finish'
            ]);
    }
}
