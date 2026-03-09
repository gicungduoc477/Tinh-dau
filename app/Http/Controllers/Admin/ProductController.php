<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
// THÊM DÒNG NÀY ĐỂ DÙNG CLOUDINARY
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,gif,jfif|max:2048',
        ]);

        $product = new Product();
        $this->saveProduct($product, $request);

        return redirect()->route('admin.product.index')->with('message', 'Thêm sản phẩm thành công!');
    }

    /**
     * 3. Trang chỉnh sửa
     */
    public function edit($id) 
    {
        $product = Product::findOrFail($id); 
        $categories = Category::all();
        return view('admin.pages.edit_product', compact('product', 'categories'));
    }

    /**
     * 4. Cập nhật sản phẩm
     */
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

    /**
     * 5. Xóa sản phẩm
     */
    public function destroy($id) 
    {
        $product = Product::findOrFail($id);

        // Lưu ý: Với Cloudinary, việc xóa ảnh cần Public ID. 
        // Tạm thời xóa record trong DB, ảnh trên Cloud sẽ quản lý sau.
        $product->delete();
        return redirect()->route('admin.product.index')->with('message', 'Đã xóa sản phẩm thành công!');
    }

    /**
     * Hàm lưu dữ liệu (Sử dụng Cloudinary cho Render Free)
     */
    private function saveProduct(Product $product, Request $request)
    {
        $product->name = $request->name;
        $product->price = $request->price;
        $product->stock = $request->stock ?? 0; // Luôn dùng 'stock' để tránh lỗi 'quantity'
        $product->category_id = $request->category_id;
        $product->classification = $request->classification;
        $product->description = $request->description;

        if ($request->hasFile('image')) {
            // Defensive check: Only attempt to upload if Cloudinary is configured.
            // This prevents crashes in environments where credentials are not set.
            if (config('cloudinary.cloud_url')) {
                // Upload to Cloudinary and get the secure URL
                $uploadedFileUrl = Cloudinary::upload($request->file('image')->getRealPath(), [
                    'folder' => 'tinh_dau_shop/products',
                ])->getSecurePath();
                
                // Save the URL directly to the image column in the database
                $product->image = $uploadedFileUrl;
            }
            // If Cloudinary is not configured, the image upload is silently skipped.
            // The product is still updated, but the image is not.
        }

        $product->slug = $this->createUniqueSlug($request->name, $product->id ?? 0);
        $product->save();
    }

    /**
     * Tạo Slug không trùng lặp
     */
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