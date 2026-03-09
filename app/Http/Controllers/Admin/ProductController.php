<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;

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
        $product->delete();
        return redirect()->route('admin.product.index')->with('message', 'Đã xóa sản phẩm thành công!');
    }

    /**
     * Hàm lưu dữ liệu (Ép buộc Cloudinary trên Render)
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
            // Kiểm tra xem có cấu hình Cloudinary không hoặc đang ở Production (Render)
            if (env('CLOUDINARY_URL') || app()->environment('production')) {
                try {
                    $uploadedFileUrl = Cloudinary::upload($request->file('image')->getRealPath(), [
                        'folder' => 'tinh_dau_shop/products',
                    ])->getSecurePath();
                    
                    $product->image = $uploadedFileUrl;
                } catch (\Exception $e) {
                    Log::error("Cloudinary Error: " . $e->getMessage());
                    
                    // Nếu ở Local (máy nhà) mà Cloudinary lỗi thì mới dùng Local
                    if (app()->environment('local')) {
                        $this->saveLocalImage($product, $request);
                    } else {
                        // Nếu đang ở Render mà lỗi Cloudinary, dừng lại báo lỗi để check cấu hình Environment
                        throw new \Exception("Lỗi cấu hình Cloudinary trên Render: " . $e->getMessage());
                    }
                }
            } 
            else {
                $this->saveLocalImage($product, $request);
            }
        }

        $product->slug = $this->createUniqueSlug($request->name, $product->id ?? 0);
        $product->save();
    }

    /**
     * Lưu ảnh local (Chỉ dành cho máy nhà Laragon)
     */
    private function saveLocalImage(Product $product, Request $request)
    {
        try {
            $file = $request->file('image');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            
            $path = public_path('uploads/product');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $file->move($path, $filename);
            $product->image = 'uploads/product/' . $filename;
        } catch (\Exception $e) {
            Log::error("Local Upload Error: " . $e->getMessage());
        }
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