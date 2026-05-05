<?php
namespace App\Http\Controllers\Pos;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with("category")->latest()->get();
        return view("pos.products.index", compact("products"));
    }
    public function create()
    {
        $categories = Category::all();
        return view("pos.products.form", compact("categories"));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            "code" => "required|max:50|unique:products",
            "name" => "required|max:200",
            "category_id" => "required|exists:categories,id",
            "purchase_price" => "required|numeric|min:0",
            "selling_price" => "required|numeric|min:0",
            "stock" => "required|integer|min:0",
            "unit" => "required|max:20",
            "description" => "nullable",
        ]);
        Product::create($validated);
        return redirect()->route("products.index")->with("success", "Produk berhasil ditambahkan");
    }
    public function edit(Product $product)
    {
        $categories = Category::all();
        return view("pos.products.form", compact("product", "categories"));
    }
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            "code" => "required|max:50|unique:products,code," . $product->id,
            "name" => "required|max:200",
            "category_id" => "required|exists:categories,id",
            "purchase_price" => "required|numeric|min:0",
            "selling_price" => "required|numeric|min:0",
            "stock" => "required|integer|min:0",
            "unit" => "required|max:20",
            "description" => "nullable",
        ]);
        $product->update($validated);
        return redirect()->route("products.index")->with("success", "Produk berhasil diupdate");
    }
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route("products.index")->with("success", "Produk berhasil dihapus");
    }
}
