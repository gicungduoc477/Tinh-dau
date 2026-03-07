<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * 1. Hiển thị danh sách sản phẩm
     */
    public function index(Request $request) 
    {
        $query = Product::query();
        
        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('class')) {
            $classification = $request->class;
            if ($classification === 'Tinh dầu nguyên chất') {
                $query->whereIn('classification', ['Tinh dầu nguyên chất', 'PURE OIL']);
            } elseif ($classification === 'Hương liệu pha') {
                $query->whereIn('classification', ['Hương liệu pha', 'FRAGRANCE']);
            } else {
                $query->where('classification', $classification);
            }
        }

        // Lấy sản phẩm mới nhất, phân trang 12 cái
        $products = $query->latest()->paginate(12)->withQueryString();
        $categories = Category::all();

        return view('admin.pages.product', compact('products', 'categories'));
    }

    /**
     * 2. Lưu sản phẩm mới
     */
    public function store(Request $request) 
    {
        $request->validate([
            'name'           => 'required|max:255',
            'price'          => 'required|numeric|min:0',
            'stock'          => 'nullable|integer|min:0',
            'category_id'    => 'nullable|exists:categories,id',
            'classification' => 'nullable|string', 
            // BỔ SUNG: Chấp nhận thêm đuôi jfif
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,gif,jfif|max:2048',
        ]);

        $product = new Product();
        $this->saveProduct($product, $request);

        return redirect()->route('admin.product.index')->with('message', 'Thêm sản phẩm thành công!');
    }

    public function edit($id) 
    {
        $product = Product::findOrFail($id); 
        $categories = Category::all();
        return view('admin.pages.edit_product', compact('product', 'categories'));
    }

    public function update(Request $request, $id) 
    {
        $product = Product::findOrFail($id);
        
        $request->validate([
            'name'           => 'required|max:255',
            'price'          => 'required|numeric|min:0',
            'stock'          => 'nullable|integer|min:0',
            'category_id'    => 'nullable|exists:categories,id',
            'classification' => 'nullable|string',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,gif,jfif|max:2048',
        ]);

        $this->saveProduct($product, $request);

        return redirect()->route('admin.product.index')->with('message', 'Cập nhật sản phẩm thành công!');
    }

    public function destroy($id) 
    {
        $product = Product::findOrFail($id);

        if ($product->image) {
            $imagePath = public_path('uploads/product/' . $product->image);
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
        }

        $product->delete();
        return redirect()->route('admin.product.index')->with('message', 'Đã xóa sản phẩm thành công!');
    }

    /**
     * Hàm dùng chung để Lưu/Cập nhật dữ liệu
     */
    private function saveProduct(Product $product, Request $request)
    {
        $product->name = $request->name;
        $product->price = $request->price;
        $product->stock = $request->stock ?? 0;
        $product->category_id = $request->category_id;
        $product->classification = $request->classification;
        $product->description = $request->description;

        if ($request->hasFile('image')) {
            $uploadPath = public_path('uploads/product');

            if (!File::isDirectory($uploadPath)) {
                File::makeDirectory($uploadPath, 0777, true, true);
            }

            // Xóa ảnh cũ nếu đang cập nhật
            if ($product->image) {
                $oldPath = $uploadPath . '/' . $product->image;
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $file = $request->file('image');
            // Tối ưu tên file: timestamp + tên file sạch (không dấu/khoảng trắng)
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $extension;
            
            $file->move($uploadPath, $filename);
            $product->image = $filename;
        }

        $product->slug = $this->createUniqueSlug($request->name, $product->id ?? 0);
        $product->save();
    }

    private function createUniqueSlug($name, $id = 0)
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;
        while (Product::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $original . '-' . $i++;
        }
        return $slug;
    }
}