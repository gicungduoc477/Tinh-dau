<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

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

        try {
            $product = new Product();
            $this->saveProduct($product, $request);
            return redirect()->route('admin.product.index')->with('message', 'Thêm sản phẩm thành công!');
        } catch (\Exception $e) {
            Log::error("Store Product Error: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Lỗi khi thêm: ' . $e->getMessage());
        }
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

        try {
            $this->saveProduct($product, $request);
            return redirect()->route('admin.product.index')->with('message', 'Cập nhật sản phẩm thành công!');
        } catch (\Exception $e) {
            Log::error("Update Product Error: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Lỗi cập nhật: ' . $e->getMessage());
        }
    }

    /**
     * 5. Xóa sản phẩm
     */
    public function destroy($id) 
    {
        $product = Product::findOrFail($id);
        
        // Chỉ xóa ảnh nếu là file cục bộ, không xóa link Cloudinary
        if ($product->image && !filter_var($product->image, FILTER_VALIDATE_URL)) {
            $oldPath = public_path($product->image);
            if (File::exists($oldPath)) {
                File::delete($oldPath);
            }
        }
        
        $product->delete();
        return redirect()->route('admin.product.index')->with('message', 'Đã xóa sản phẩm thành công!');
    }

    /**
     * Hàm lưu dữ liệu chính (Tối ưu cho cả Local và Production)
     */
    private function saveProduct(Product $product, Request $request)
    {
        $product->name = $request->name;
        $product->price = $request->price;
        $product->stock = $request->stock ?? 0; 
        $product->category_id = $request->category_id;
        $product->classification = $request->classification;
        $product->description = $request->description;

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $uploadedPath = null;

            // Đọc cấu hình từ config thay vì env trực tiếp để tránh lỗi khi chạy artisan config:cache
            $cloudinaryUrl = config('cloudinary.cloud_url');

            if ($cloudinaryUrl) {
                try {
                    $result = Cloudinary::upload($request->file('image')->getRealPath(), [
                        'folder'    => 'tinh_dau_shop/products',
                        'overwrite' => true,
                        'resource_type' => 'auto'
                    ]);
                    $uploadedPath = $result->getSecurePath();
                } catch (\Exception $e) {
                    Log::error("Cloudinary Upload Error: " . $e->getMessage());
                }
            }

            // Nếu không có Cloudinary hoặc upload lỗi, lưu cục bộ (dành cho Local)
            if (!$uploadedPath) {
                $uploadedPath = $this->handleLocalUpload($request->file('image'));
            }

            if ($uploadedPath) {
                $product->image = $uploadedPath;
            }
        }

        $product->slug = $this->createUniqueSlug($request->name, $product->id ?? 0);
        $product->save();
    }

    private function handleLocalUpload($file)
    {
        try {
            $extension = strtolower($file->getClientOriginalExtension());
            $filename = time() . '_' . Str::random(8) . '.' . $extension;
            $destinationPath = public_path('uploads/product');
            
            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true, true);
            }

            $file->move($destinationPath, $filename);
            return 'uploads/product/' . $filename;
        } catch (\Exception $e) {
            Log::error("Local Upload Error: " . $e->getMessage());
            return null;
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