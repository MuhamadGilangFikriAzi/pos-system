<?php
namespace App\Http\Controllers\Pos;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->get();
        return view("pos.categories.index", compact("categories"));
    }
    public function create()
    {
        return view("pos.categories.form");
    }
    public function store(Request $request)
    {
        $validated = $request->validate(["name" => "required|max:100", "description" => "nullable"]);
        Category::create($validated);
        return redirect()->route("categories.index")->with("success", "Kategori berhasil ditambahkan");
    }
    public function edit(Category $category)
    {
        return view("pos.categories.form", compact("category"));
    }
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate(["name" => "required|max:100", "description" => "nullable"]);
        $category->update($validated);
        return redirect()->route("categories.index")->with("success", "Kategori berhasil diupdate");
    }
    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route("categories.index")->with("success", "Kategori berhasil dihapus");
    }
}
