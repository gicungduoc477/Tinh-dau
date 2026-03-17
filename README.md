# BÁO CÁO DỰ ÁN WEBSITE THƯƠNG MẠI ĐIỆN TỬ (NATURE SHOP)

> **Người thực hiện:** [Bùi Văn Hiếu - 501240260]
> **Môn học:** PHP2

## 1. Giới thiệu dự án

### Mô tả tổng quan
Dự án **Nature Shop** là một hệ thống thương mại điện tử B2C (Business to Customer) hoàn chỉnh, chuyên kinh doanh các sản phẩm thiên nhiên/tinh dầu. Hệ thống được xây dựng trên nền tảng **Laravel Framework** mạnh mẽ, tập trung giải quyết các bài toán cốt lõi của việc bán hàng trực tuyến hiện đại.

Không chỉ dừng lại ở việc đặt hàng, hệ thống cung cấp một quy trình nghiệp vụ khép kín (End-to-End Workflow):
1.  **Khách hàng:** Tìm kiếm -> Đặt hàng -> Thanh toán Online (VietQR/PayOS) -> Theo dõi vận đơn -> Đánh giá sản phẩm.
2.  **Quản trị viên:** Quản lý tồn kho -> Xử lý đơn hàng -> Giải quyết khiếu nại/Hoàn tiền -> Phân tích báo cáo doanh thu và chất lượng dịch vụ.

### Công nghệ sử dụng
Hệ thống sử dụng các công nghệ tiên tiến và ổn định nhất hiện nay trong hệ sinh thái PHP:

#### Backend & Core
-   **Ngôn ngữ:** PHP 8.1+ (Tận dụng các tính năng mới như Enums, Readonly properties).
-   **Framework:** Laravel 10.x/11.x.
-   **Database:** MySQL 8.0 (Sử dụng InnoDB engine để hỗ trợ Transaction).
-   **Server Environment:** Laragon (Apache/Nginx).

#### Frontend
-   **Template Engine:** Laravel Blade Templates.
-   **Styling:** Bootstrap 5 kết hợp Tailwind CSS cho các component tùy biến.
-   **Scripting:** JavaScript (ES6), jQuery, Ajax (Xử lý các thao tác không cần reload trang như Thêm giỏ hàng, Lọc sản phẩm).

#### Dịch vụ bên thứ 3 (Third-party APIs)
-   **Payment Gateway:** PayOS (Tích hợp thanh toán QR Code tự động xác nhận), VietQR (Tạo mã chuyển khoản nhanh).
-   **Cloud Storage:** Cloudinary (Lưu trữ và tối ưu hóa ảnh sản phẩm, ảnh minh chứng khiếu nại).
-   **Mail Server:** SMTP (Gmail/Mailtrap) với hàng đợi (Queue) để gửi email bất đồng bộ.

---

## 2. Kiến trúc hệ thống

### Mô hình MVC
Hệ thống được thiết kế tuân thủ chặt chẽ mô hình **Model-View-Controller (MVC)**, giúp tách biệt logic xử lý, giao diện và dữ liệu, dễ dàng bảo trì và mở rộng:

1.  **Model (M):**
    -   Đại diện cho các thực thể dữ liệu (`Order`, `Product`, `User`, `Review`).
    -   Chứa các logic nghiệp vụ liên quan trực tiếp đến dữ liệu (Ví dụ: `Order::needsRefund()`, `Product::inStock()`).
    -   Sử dụng **Eloquent ORM** để tương tác với Database an toàn và hiệu quả.

2.  **View (V):**
    -   Nằm trong thư mục `resources/views`.
    -   Hiển thị dữ liệu nhận được từ Controller.
    -   Sử dụng **Blade Component** (`<x-input>`, `<x-button>`) để tái sử dụng code giao diện.

3.  **Controller (C):**
    -   Điều phối luồng dữ liệu.
    -   Nhận request từ người dùng, gọi Model xử lý và trả về View tương ứng.
    -   Các Controller chính: `Admin\OrderController`, `Admin\ReviewController`, `Frontend\CheckoutController`.

### Cấu trúc thư mục dự án
```text
CDPHP/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/       # Logic quản trị (Order, Product, Review)
│   │   │   ├── Auth/        # Xác thực (Login, Register)
│   │   │   └── Frontend/    # Logic người dùng (Cart, Checkout)
│   │   ├── Middleware/      # Kiểm soát quyền truy cập (Admin/User role)
│   │   └── Requests/        # Validation Rules riêng biệt
│   ├── Models/              # Eloquent Models (User, Order, Review...)
│   └── Mail/                # Các class gửi email (OrderPlaced, WelcomeUser)
├── database/
│   ├── migrations/          # Quản lý version database
│   └── seeders/             # Dữ liệu mẫu (Fake data)
├── resources/
│   └── views/               # Giao diện Blade templates
└── routes/
    └── web.php              # Định tuyến đường dẫn
```

### Sơ đồ Class Diagram (Mô tả)
-   **User**: Quản lý thông tin khách hàng và Admin.
    -   Quan hệ: `1-n` với `Order`, `1-n` với `Review`.
-   **Product**: Sản phẩm kinh doanh.
    -   Quan hệ: `1-n` với `OrderItem`, `1-n` với `Review`.
-   **Order**: Đơn hàng tổng quát.
    -   Chứa thông tin: Tổng tiền, trạng thái (`status`), phương thức thanh toán.
    -   Quan hệ: `1-n` với `OrderItem`, `1-n` với `OrderStatusHistory`.
-   **OrderItem**: Chi tiết đơn hàng.
    -   Lưu snapshot (bản chụp) giá và số lượng sản phẩm tại thời điểm mua (tránh việc giá sản phẩm thay đổi làm sai lệch lịch sử đơn).
-   **Review**: Đánh giá sản phẩm.
    -   Chứa nội dung đánh giá, số sao (`rating`), ảnh/video media.

### Sơ đồ ERD Database
Mô tả mối quan hệ thực thể:
> `users` (1) --- (n) `orders`
> `orders` (1) --- (n) `order_items` (n) --- (1) `products`
> `orders` (1) --- (n) `order_status_histories`
> `users` (1) --- (n) `reviews` (n) --- (1) `products`

---

## 3. Lập trình hướng đối tượng (OOP)
Hệ thống áp dụng triệt để các nguyên lý OOP để đảm bảo code "Sạch" (Clean Code) và dễ bảo trì.

1. **Kế thừa (Inheritance):**
    -   Tất cả các Model (`Order`, `Product`...) đều kế thừa từ `Illuminate\Database\Eloquent\Model`.
    -   Điều này giúp các class con thừa hưởng toàn bộ sức mạnh của Eloquent như: Active Record pattern, quan hệ (Relationships), và các event (created, updated).
    ```php
    use Illuminate\Database\Eloquent\Model;
    class Order extends Model {
        // Thừa hưởng các phương thức find(), save(), delete()...
    }
    ```

2. **Đóng gói (Encapsulation):**
    -   Bảo vệ dữ liệu: Sử dụng `protected $fillable` để ngăn chặn tấn công Mass Assignment.
    -   Ẩn logic phức tạp: Các phương thức xử lý kho hàng được đặt là `private` trong Controller để đảm bảo chỉ được gọi trong quy trình xử lý đơn hàng, không thể bị gọi tùy tiện từ bên ngoài.
    ```php
    // App/Http/Controllers/Admin/OrderController.php
    
    // Phương thức này là private, đóng gói logic trừ kho
    private function handleStock(Order $order, $action) {
        foreach ($order->items as $item) {
            // ... logic cập nhật kho
        }
    }
    ```

3. **Đa hình & Abstraction (Accessors):**
    -   Sử dụng **Accessors** để biến đổi dữ liệu khi lấy ra mà không làm thay đổi dữ liệu gốc trong Database.
    -   Ví dụ: Tự động format đường dẫn ảnh trong `Order` model dù ảnh lưu ở local hay Cloud.
    ```php
    // App/Models/Order.php
    public function getReturnImageUrlAttribute(): ?string
    {
        // Logic trừu tượng hóa việc lấy ảnh
        if (filter_var($this->return_image, FILTER_VALIDATE_URL)) {
            return $this->return_image; // Link online
        }
        return asset('storage/' . $this->return_image); // Link local
    }
    ```

---

## 4. Chức năng chính

### 4.1. Quy trình Đặt hàng & Thanh toán (Checkout)
Đây là chức năng quan trọng nhất, yêu cầu độ chính xác tuyệt đối về dữ liệu.
-   **Mô tả:** Người dùng chọn sản phẩm -> Giỏ hàng -> Điền thông tin giao hàng -> Chọn phương thức thanh toán.
-   **Xử lý kỹ thuật (`CheckoutController`):**
    -   **Atomic Transaction:** Sử dụng `DB::beginTransaction()` bao bọc toàn bộ quá trình. Nếu bước trừ tồn kho (`decrement`) thất bại, đơn hàng sẽ không được tạo để tránh lệch kho.
    -   **Validation:** Kiểm tra số lượng tồn kho theo thời gian thực ngay trước khi tạo đơn.
    -   **Thông báo:** Kích hoạt gửi email xác nhận (`OrderPlacedMail`) vào Queue để không làm chậm trải nghiệm người dùng.

### 4.2. Tích hợp Thanh toán Online (PayOS/VietQR)
-   **Mô tả:** Tạo mã QR tự động chứa thông tin số tiền và nội dung chuyển khoản chính xác.
-   **Bảo mật:**
    -   Tạo **Signature (Chữ ký điện tử)** sử dụng thuật toán HMAC_SHA256 khi giao tiếp với API thanh toán để chống giả mạo request.
    -   Webhook lắng nghe trạng thái thanh toán từ cổng thanh toán để cập nhật `payment_status = 'paid'` tự động.

### 4.3. Quản lý Đơn hàng & State Machine
Hệ thống quản lý trạng thái đơn hàng phức tạp với 11 trạng thái khác nhau (`Order::$statuses`).

**Luồng xử lý (`OrderController@updateStatus`):**
1.  **Xử lý Kho hàng:**
    -   Trạng thái `confirmed` (Xác nhận): Hệ thống tự động **trừ** kho.
    -   Trạng thái `canceled` (Hủy) hoặc `returned` (Trả hàng): Hệ thống tự động **cộng** lại kho.
    -   *Điểm đặc biệt:* Trạng thái trung gian `returning_confirmed` (Đồng ý hoàn hàng) chưa cộng kho ngay, mà phải chờ khi Admin xác nhận `returned` (Đã nhận hàng hoàn) để đảm bảo an toàn vật lý.
2.  **Audit Log (Nhật ký hệ thống):**
    -   Mọi thay đổi trạng thái đều được ghi vào bảng `order_status_histories` kèm theo: Người thực hiện, thời gian, và lý do thay đổi.
3.  **Hệ thống Email tự động:**
    -   Gửi mail thông báo tương ứng cho khách (Đã xác nhận, Đang giao, Đã hoàn tiền...).

### 4.4. Đăng ký & Xác thực thành viên
-   **Bảo mật:** Mật khẩu được mã hóa Bcrypt.
-   **Quy trình:**
    -   Validation input chặt chẽ.
    -   Gửi email chào mừng (`WelcomeUserMail`) sử dụng `try-catch` để đảm bảo nếu mail server lỗi, người dùng vẫn đăng ký thành công (Soft fail).
    -   Phân quyền (Authorization) dựa trên cột `role` trong bảng `users`.

### 4.5. Hệ thống Đánh giá & Phản hồi (Review & Rating)
Một module mạnh mẽ giúp tăng độ uy tín cho sản phẩm.

**Chức năng chi tiết (`ReviewController`):**
1.  **Thống kê & Analytics:**
    -   Tính toán các chỉ số KPI: Điểm đánh giá trung bình (`avg_rating`), Tỷ lệ phản hồi (`reply_rate`), Thời gian phản hồi trung bình (`avg_reply_time`).
    -   Cảnh báo các sản phẩm bị đánh giá thấp (< 3 sao) để Admin có kế hoạch cải thiện chất lượng.
2.  **Quản lý Phản hồi (Admin Reply):**
    -   **Quick Replies:** Hỗ trợ Admin chọn các mẫu câu trả lời soạn sẵn để tiết kiệm thời gian.
    -   Ghi chú nội bộ (`admin_note`): Admin có thể note lại tình trạng xử lý review mà khách hàng không nhìn thấy.
3.  **Xử lý Media:**
    -   Hỗ trợ khách hàng upload nhiều ảnh và video review.
    -   Xử lý xóa file vật lý (`Storage::delete`) khi Admin xóa review để giải phóng dung lượng server.
4.  **Bộ lọc nâng cao:**
    -   Lọc theo số sao (1-5 sao), trạng thái (Ẩn/Hiện), trạng thái xử lý (Đã trả lời/Chưa trả lời).

---

## 5. Database & Bảo mật

### Database Design
Cơ sở dữ liệu được thiết kế chuẩn hóa (Normalized) để đảm bảo tính toàn vẹn dữ liệu.

1.  **`users`**:
    -   `id`, `name`, `email`, `password`, `role` (admin/user).
2.  **`products`**:
    -   `id`, `name`, `price`, `stock` (quản lý tồn kho), `description`.
3.  **`orders`**:
    -   `id`, `order_code` (Unique), `total_price`, `status`, `payment_status`.
    -   Các cột phục vụ hoàn tiền: `return_reason`, `return_image`, `return_requested_at`.
4.  **`order_items`**:
    -   Lưu `price` và `quantity` riêng biệt, không tham chiếu giá từ bảng product để giữ lịch sử giá chính xác.
5.  **`reviews`**:
    -   `rating` (1-5), `comment`, `image`, `video`, `reply` (phản hồi từ admin).
6.  **`order_status_histories`**:
    -   Audit Log: `from_status`, `to_status`, `user_id` (người sửa), `note`.

### Các biện pháp bảo mật đã áp dụng
1.  **Database Transaction:**
    Áp dụng ở tất cả các nghiệp vụ ghi/sửa dữ liệu quan trọng (Đặt hàng, Duyệt đơn, Đăng ký). Ngăn chặn lỗi Race Condition và dữ liệu rác.
    ```php
    DB::beginTransaction();
    try {
        $order->save();
        $this->handleStock($order, 'decrease'); // Nếu hàm này lỗi
        DB::commit();                           // Dòng này sẽ không chạy
    } catch (\Exception $e) {
        DB::rollBack();                         // Và mọi thứ hoàn tác
    }
    ```
2.  **Validation & Sanitization:**
    -   Sử dụng `FormRequest` để validate dữ liệu đầu vào.
    -   Blade Template (`{{ $variable }}`) tự động escape dữ liệu để chống XSS.
3.  **Authentication & Authorization:**
    -   Hệ thống phân quyền rõ ràng: Admin truy cập `/admin/*`, User truy cập `/account/*`.
    -   Middleware chặn các request trái phép.
4.  **Secure Payment:**
    -   Kiểm tra chữ ký (Signature) trong Webhook PayOS để đảm bảo request đến từ PayOS chứ không phải hacker giả mạo.

---

-----
## 6. Hướng dẫn sử dụng & Cài đặt

### Yêu cầu hệ thống
-   **PHP**: Phiên bản >= 8.1
-   **Cơ sở dữ liệu**: MySQL hoặc MariaDB.
-   **Composer**: Để quản lý thư viện PHP.
-   **Node.js & NPM**: (Tùy chọn) Để build assets nếu cần chỉnh sửa CSS/JS.

### Các bước cài đặt chi tiết

1.  **Clone dự án:**
    ```bash
    git clone <repo_url>
    cd CDPHP
    ```

2.  **Cài đặt thư viện:**
    ```bash
    composer install
    ```

3.  **Cấu hình môi trường:**
    -   Copy file `.env.example` thành `.env`.
    -   Cập nhật thông tin cấu hình:
        ```env
        APP_URL=http://localhost:8000
        
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_DATABASE=cdphp
        DB_USERNAME=root
        
        MAIL_MAILER=smtp
        MAIL_HOST=smtp.gmail.com
        # ... Cấu hình mật khẩu ứng dụng Email
        
        # Cấu hình PayOS và Cloudinary (Nếu có)
        CLOUDINARY_URL=...
        PAYOS_CLIENT_ID=...
        ```

4.  **Khởi tạo Database:**
    ```bash
    php artisan key:generate
    php artisan migrate
    php artisan db:seed  # Quan trọng: Lệnh này tạo tài khoản Admin và User mẫu
    ```

5.  **Chạy server:**
    ```bash
    php artisan serve
    ```
    Truy cập: `http://127.0.0.1:8000`

### Troubleshooting (Sửa lỗi thường gặp)
-   **Lỗi "Unknown database":** Hãy chắc chắn bạn đã tạo database tên `cdphp` trong PHPMyAdmin hoặc Workbench trước khi chạy lệnh migrate.
-   **Lỗi gửi mail:**
    -   Kiểm tra `MAIL_PASSWORD`: Với Gmail, bạn phải dùng "App Password" (Mật khẩu ứng dụng) chứ không phải mật khẩu đăng nhập Gmail thường.
    -   Hệ thống đã tích hợp `try-catch`, nên nếu lỗi mail, đơn hàng vẫn được tạo nhưng sẽ ghi log lỗi vào `storage/logs/laravel.log`.
-   **Lỗi 403 Forbidden khi vào Admin:** Kiểm tra cột `role` trong bảng `users`, đảm bảo tài khoản có giá trị là `admin`.

---

## 7. Tài khoản kiểm thử (Test Accounts)

Dưới đây là các tài khoản mặc định được tạo ra bởi lệnh `db:seed`.

| Vai trò (Role) | Email | Mật khẩu | Quyền hạn |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@gmail.com` | `hieu1234@A` | Toàn quyền quản trị hệ thống, xử lý đơn hàng, xem báo cáo. |
| **User** | `user@gmail.com` | `hieu4321@A` | Đặt hàng, xem lịch sử đơn, gửi đánh giá sản phẩm. |

*Lưu ý: Nếu đăng nhập không được, vui lòng chạy lại lệnh `php artisan db:seed` để reset mật khẩu.*

---

## 8. Kết luận

### Đánh giá dự án
Dự án **Nature Shop** đã hoàn thiện các chức năng cốt lõi của một sàn thương mại điện tử hiện đại, đáp ứng tốt nhu cầu quản lý bán hàng thực tế:

1.  **Quy trình nghiệp vụ hoàn chỉnh:** Từ lúc khách xem hàng đến lúc Admin chốt đơn và xử lý hậu mãi (trả hàng/hoàn tiền).
2.  **Trải nghiệm người dùng tốt:** Tốc độ tải trang nhanh, thông báo email rõ ràng, hỗ trợ thanh toán QR tiện lợi.
3.  **Chất lượng mã nguồn:**
    -   Code được tổ chức clear, tuân thủ chuẩn PSR.
    -   Áp dụng các Design Pattern cơ bản.
    -   Xử lý ngoại lệ (Exception Handling) tốt, tránh crash ứng dụng.

### Khó khăn gặp phải
-   **Đồng bộ kho hàng:** Việc xử lý logic cộng/trừ kho hàng khi có nhiều trạng thái phức tạp (như "Chờ nhận hàng hoàn") đòi hỏi tư duy logic chặt chẽ để tránh sai lệch số liệu.
-   **Tích hợp Payment:** Việc xác thực chữ ký điện tử (HMAC) của bên thứ 3 (PayOS) ban đầu gặp khó khăn do sai lệch định dạng dữ liệu.
-   **Xử lý Media:** Quản lý việc upload/xóa ảnh rác khi khách hàng chỉnh sửa đánh giá nhiều lần.

### Hướng phát triển
Để dự án phát triển thành một hệ thống lớn hơn, hướng phát triển tiếp theo sẽ là:
1.  **API Development:** Xây dựng bộ RESTful API chuẩn để phát triển thêm Mobile App (React Native/Flutter).
2.  **Recommendation System:** Tích hợp AI để gợi ý sản phẩm dựa trên lịch sử xem và mua hàng của user.
3.  **Vận chuyển:** Tích hợp API của Giao Hàng Nhanh/Viettel Post để tính phí ship tự động theo địa chỉ thực tế thay vì phí cố định.
4.  **Flash Sale:** Xây dựng module quản lý các khung giờ giảm giá sốc với Redis để chịu tải cao.
