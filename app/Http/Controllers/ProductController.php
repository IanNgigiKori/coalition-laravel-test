<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    protected $filePath = 'products.json';

    public function index()
    {
        return view('products');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $this->loadData();
        $newProduct = [
            'id' => count($data) + 1,
            'name' => $request->name,
            'quantity' => (int) $request->quantity,
            'price' => (float) $request->price,
            'datetime' => now()->format('Y-m-d H:i:s'),
            'total' => (int) $request->quantity * (float) $request->price,
        ];
        $data[] = $newProduct;
        $this->saveData($data);

        return response()->json(['success' => true, 'product' => $newProduct]);
    }

    public function getProducts()
    {
        $data = $this->loadData();
        $totalSum = array_sum(array_column($data, 'total'));
        return response()->json(['products' => $data, 'totalSum' => $totalSum]);
    }

    public function update(Request $request, $id)
{
    \Log::info('Received update data:', $request->all());
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'quantity' => 'required|numeric|min:0',
        'price' => 'required|numeric|min:0',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $data = $this->loadData();
    foreach ($data as &$product) {
        if ($product['id'] == $id) {
            $product['name'] = $request->name;
            $product['quantity'] = (int) $request->quantity;
            $product['price'] = (float) $request->price;
            $product['total'] = $product['quantity'] * $product['price'];
            break;
        }
    }
    $this->saveData($data);

    return response()->json(['success' => true, 'product' => $product]);
}

    protected function loadData()
    {
        if (Storage::exists($this->filePath)) {
            return json_decode(Storage::get($this->filePath), true);
        }
        return [];
    }

    protected function saveData($data)
    {
        Storage::put($this->filePath, json_encode($data, JSON_PRETTY_PRINT));
    }
}