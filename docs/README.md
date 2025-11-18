HỆ THỐNG WEBSITE QUẢNG BÁ NHÀ HÀNG VÀ ĐẶT BÀN TRỰC TUYẾN
## Tổng Quan
Hệ thống Website Quảng Bá Nhà Hàng và Đặt Bàn Trực Tuyến là một nền tảng giúp khách hàng xem thông tin về nhà hàng, thực đơn, chương trình khuyến mãi và đặt bàn trực tiếp thông qua Internet.
Hệ thống bao gồm 3 nhóm người dùng chính: Quản trị viên, Nhân viên nhà hàng, và Khách hàng.
Quản trị viên và nhân viên có thể quản lý nội dung trang web, món ăn, khuyến mãi, lịch đặt bàn, và thông tin khách hàng.
Khách hàng có thể xem thông tin nhà hàng, duyệt menu, xem khuyến mãi, đặt bàn và theo dõi lịch sử đặt bàn.
## Yêu Cầu Hệ Thống
Yêu Cầu Kỹ Thuật
•	PHP 7.4 hoặc cao hơn
•	MySQL 5.7 hoặc cao hơn
•	Máy chủ web (Apache/XAMPP)
•	MySQLi hoặc PDO
•	Trình duyệt web hiện đại hỗ trợ JavaScript
•	Công cụ phát triển: VSCode, phpMyAdmin
Cấu Hình Cơ Sở Dữ Liệu
•	Host: localhost
•	Tên Database: restaurant_db
•	Username: root
•	Password: (trống)
•	File cấu hình: /restaurant_db.sql
## Vai Trò Người Dùng và Quyền Truy Cập
Quản Trị Viên
•	Quản lý người dùng (thêm, sửa, xóa, phân quyền)
•	Quản lý thực đơn (thêm, sửa, xóa món ăn)
•	Quản lý khuyến mãi (thêm, sửa, xóa khuyến mãi)
•	Quản lý thông tin trang chủ, banner, hình ảnh
•	Quản lý đặt bàn (xem, duyệt)
•	Toàn quyền chỉnh sửa nội dung website
Nhân Viên
•	Đăng nhập hệ thống quản trị
•	Quản lý thực đơn
•	Quản lý khuyến mãi
•	Xem và duyệt yêu cầu đặt bàn
•	Quản lý trạng thái món ăn
Khách Hàng
•	Đăng ký tài khoản
•	Đăng nhập / Đăng xuất
•	Quên mật khẩu
•	Xem giới thiệu nhà hàng
•	Xem menu & khuyến mãi
•	Đặt bàn trực tuyến
•	Nhận email xác nhận
•	Theo dõi lịch sử đặt bàn
## Use Cases (Trường Hợp Sử Dụng)
#### Use Cases Xác Thực
Đăng Nhập
Tác nhân: Quản trị viên, Nhân viên, Khách hàng
Mô tả: Người dùng đăng nhập bằng email và mật khẩu
Luồng chính:
1.	Người dùng truy cập trang đăng nhập
2.	Nhập email và mật khẩu
3.	Hệ thống kiểm tra thông tin
4.	Chuyển hướng đến trang phù hợp với vai trò
#### Đăng Ký
Tác nhân: Khách hàng
Mô tả: Tạo tài khoản khách hàng
Luồng chính:
1.	Truy cập trang đăng ký
2.	Nhập thông tin cá nhân
3.	Nhận email xác thực
4.	Kích hoạt tài khoản
5.	Đăng nhập
#### Đăng Xuất
Tác nhân: Người dùng đã đăng nhập
Mô tả: Đăng xuất khỏi hệ thống
Luồng chính:
1.	Nhấn nút đăng xuất
2.	Hệ thống hủy phiên đăng nhập
3.	Chuyển hướng về trang chủ
### Use Cases Khách Hàng
#### Xem Trang Chủ
Tác nhân: Khách hàng
Mô tả: Xem thông tin giới thiệu về nhà hàng
Luồng chính:
1.	Truy cập trang chủ
2.	Xem banner giới thiệu
3.	Xem hình ảnh nhà hàng
4.	Đọc mô tả về nhà hàng
5.	Xem thông tin liên hệ và địa chỉ
#### Xem Menu
Tác nhân: Khách hàng
Mô tả: Xem danh sách món ăn theo danh mục
Luồng chính:
1.	Truy cập trang menu
2.	Xem danh sách danh mục món (Khai vị, Món chính, Tráng miệng, Đồ uống)
3.	Lọc món theo danh mục
4.	Xem thông tin chi tiết món ăn:
#### Xem Khuyến Mãi
Tác nhân: Khách hàng 
Mô tả: Xem các chương trình khuyến mãi hiện có
Luồng chính:
1.	Truy cập trang khuyến mãi
2.	Xem danh sách các ưu đãi đang áp dụng
3.	Xem chi tiết khuyến mãi:
#### Đặt Bàn Trực Tuyến
Tác nhân: Khách hàng
Mô tả: Khách hàng đã đăng nhập đặt bàn cho một khoảng thời gian cụ thể
Luồng chính:
1.	Khách đăng nhập vào hệ thống
2.	Truy cập trang đặt bàn
3.	Điền form đặt bàn: 
4.	Xác nhận thông tin
5.	Hệ thống lưu đặt bàn với trạng thái "Pending" (Chờ xác nhận)
6.	Gửi email xác nhận cho khách hàng
7.	Hiển thị thông báo thành công
Luồng thay thế:
4a. Thông tin không hợp lệ → Hiển thị lỗi validation
#### Xem Lịch Sử Đặt Bàn
Tác nhân: Khách hàng
Mô tả: Xem danh sách các lần đặt bàn của mình
Luồng chính:
1.	Khách hang đăng nhập
2.	Truy cập trang quản lý đặt bàn
3.	Xem danh sách đặt bàn kèm trạng thái đặt bàn
#### Quản Lý Người Dùng
Tác nhân: Quản trị viên
Mô tả: Quản lý tài khoản admin và khách hàng
Luồng chính:
1.	Admin đăng nhập vào hệ thống quản trị
2.	Truy cập trang quản lý người dùng
3.	Xem danh sách người dùng (Admin/Staff và Customers)
4.	Thêm người dùng mới
5.	Chỉnh sửa thông tin người dung
6.	Xóa người dùng
#### Quản Lý thực đơn
Tác nhân: Quản trị viên
Mô tả: Quản lý danh sách món ăn
Luồng chính:
1.	Truy cập trang quản lý thực đơn
2.	Xem danh sách món ăn hiện có
3.	Thực hiện các thao tác
4.	Thêm món mới
5.	Chỉnh sửa món
6.	Xóa món
7.	Cập nhật trạng thái món
#### Quản Lý Khuyến Mãi
Tác nhân: Quản trị viên
Mô tả: Tạo và quản lý các chương trình khuyến mãi
Luồng chính:
1.	Truy cập trang quản lý khuyến mãi
2.	Xem danh sách khuyến mãi
3.	Thêm khuyến mãi mới
4.	Chỉnh sửa khuyến mãi
5.	Xóa khuyến mãi
#### Quản Lý Đặt Bàn
Tác nhân: Quản trị viên
Mô tả: Xem và xử lý các yêu cầu đặt bàn
Luồng chính:
1.	Truy cập trang quản lý đặt bàn
2.	Xem danh sách đặt bàn 
3.	Xác nhận/hủy đặt bàn
#### Quản Lý thông tin
Tác nhân: Quản trị viên
Mô tả: Chỉnh sửa nội dung trang chủ và thông tin liên hệ
Luồng chính:
1.	Truy cập trang quản lý thông tin
2.	Chọn phần quản lý trang chủ
3.	Xem thông tin hiện tại
4.	Chỉnh sửa
5.	Lưu thay đổi
Luồng thay thế:
2a. Chọn phần quản lý thông tin liên hệ
## Mô Hình Dữ Liệu
customers
•	id
•	full_name
•	e-mail
•	phone
•	password_hash
•	created_at
•	email_verified_at
users
•	id
•	username
•	e-mail
•	password_hash
•	role (admin/staff)
•	created_at
admin_invite_tokens
•	id
•	user_id
•	token
•	expires_at
•	used_at
user_activation_tokens
•	id
•	user_id
•	token
•	expires_at
•	used_at
customer_email_verification_tokens
•	id
•	customer_id
•	token
•	expires_at
•	used_at
password_resets
•	id
•	e-mail
•	token
•	expires_at
•	used_at
reservations
•	id
•	full_name
•	phone
•	reservation_date
•	people_count
•	note
•	status (pending/confirmed/cancelled)
•	confirmation_code
•	created_at
•	table_type
•	customer_id
•	confirmed_by
menu_items
•	id
•	name
•	description
•	price
•	image_url
•	category (appetizer/main/dessert/drink)
•	is_special
•	is_available
promotions
•	id
•	title
•	description
•	discount_type (percent/fixed)
•	discount_value
•	apply_to_menu_ids
•	apply_to_all
•	coupon_code
•	image_url
•	start_at
•	end_at
•	active
•	created_at
settings
•	id
•	restaurant_name
•	address
•	phone
•	e-mail
•	open_hours
•	social_links
home_settings
•	id
•	title
•	description
•	intro_images
•	banner_image
•	created_at
## Tính Năng Chính
Hệ Thống Xác Thực
•	Đăng ký tài khoản khách hàng
•	Đăng nhập cho Admin và Khách hàng
•	Đăng xuất an toàn
•	Khôi phục mật khẩu qua email
•	Xác thực email (với token)
•	Phiên đăng nhập bảo mật
•	Mã hóa mật khẩu sử dụng password_hash() của PHP
Quản Lý Menu
•	CRUD món ăn (Create, Read, Update, Delete)
•	Upload và quản lý hình ảnh món
•	Phân loại món theo danh mục (Khai vị, Món chính, Tráng miệng, Đồ uống)
•	Đánh dấu món đặc biệt
•	Cập nhật tình trạng món (còn/hết)
•	Tìm kiếm và lọc món ăn
•	Hiển thị menu theo danh mục trên website
Quản Lý Khuyến Mãi
•	Tạo chương trình giảm giá
•	Hỗ trợ 2 loại giảm giá: 
o	Giảm theo phần trăm
o	Giảm số tiền cố định
•	Áp dụng cho món cụ thể hoặc toàn bộ menu
•	Quản lý mã coupon
•	Thiết lập thời gian bắt đầu và kết thúc
•	Tự động ngừng khuyến mãi hết hạn
•	Kích hoạt/vô hiệu hóa khuyến mãi
Hệ Thống Đặt Bàn
•	Form đặt bàn trực tuyến
•	Chọn loại bàn (Bàn thường/Bàn VIP)
•	Xác định số lượng người
•	Chọn ngày giờ đặt bàn
•	Ghi chú yêu cầu đặc biệt
•	3 trạng thái đơn: 
o	Pending: Chờ xác nhận
o	Confirmed: Đã xác nhận
o	Cancelled: Đã hủy
•	Gửi email xác nhận tự động
•	Lịch sử đặt bàn cho khách hàng
•	Quản lý và duyệt đặt bàn cho Admin
Trang Thông Tin Nhà Hàng
•	Banner giới thiệu với hình ảnh
•	Mô tả về nhà hàng
•	Thư viện hình ảnh (gallery)
•	Thông tin liên hệ: 
	Địa chỉ
	Số điện thoại
	Email
	Giờ mở cửa
•	Liên kết mạng xã hội (Facebook, Instagram, TikTok)
Quản Lý Khách Hàng
•	Xem danh sách khách hàng
•	Quản lý thông tin khách hàng
## Yêu Cầu Giao Diện Người Dùng
Thiết Kế Responsive
•	Tương thích với máy tính để bàn
•	Tương thích với tablet
•	Tương thích với điện thoại di động
•	Sử dụng Bootstrap hoặc framework CSS tương tự
Trải Nghiệm Người Dùng
•	Điều hướng trực quan và dễ sử dụng
•	Menu navigation rõ ràng
•	Breadcrumb cho các trang con
•	Thông báo rõ ràng (success, error, warning)
•	Form validation trực quan
•	Loading indicator khi xử lý
•	Xác nhận trước khi xóa dữ liệu quan trọng
Giao Diện Khách Hàng
•	Trang chủ bắt mắt với banner và hình ảnh
•	Menu món ăn với card layout đẹp mắt
•	Trang khuyến mãi nổi bật
•	Form đặt bàn đơn giản, dễ điền
•	Trang quản lý tài khoản cá nhân
Giao Diện Admin
•	Sidebar navigation
•	Form nhập liệu rõ ràng
Hỗ Trợ Ngôn Ngữ
•	Giao diện tiếng Việt
•	Định dạng ngày giờ theo chuẩn Việt Nam
## Cấu Trúc Dự Án
RESTAURANT/
`/admin/`                           # Thư mục quản trị
`/assets/`                          # Tài nguyên tĩnh
  `//css/`                          # File CSS
`/controllers/`                     # Controllers xử lý logic
  `//AuthController.php`            # Xử lý xác thực
  `//BaseController.php`            # Controller cơ sở
  `//CustomerController.php`        # Xử lý thông tin khách hàng
  `//HomeController.php`            # Xử lý thông tin trang chủ
  `//InfoController.php`            # Xử lý thông tin chung nhà hàng
  `//MenuController.php`            # Xử lý menu món ăn
  `//PromotionController.php`       # Xử lý khuyến mãi
  `//ReservationController.php`     # Xử lý đặt bàn
  `//SettingController.php`         # Xử lý đặt bàn
  `//UserController.php`            # Xử lý user
`/docs/`                            # Tài liệu
`/includes/`                        # File dùng chung
  `//classes/`                      # Các class PHP
    `///auth.php`                   # Class xác thực
    `///components.php`             # Components tái sử dụng
    `///csrf.php`                   # Bảo mật CSRF
    `///db.php`                     # Kết nối database
    `///email.php`                  # Xử lý email
    `///models.php`                 # Models dữ liệu
`/logs/`                            # File log
`.htaccess`                         # Cấu hình Apache
`account.php`                       # Quản lý tài khoản
`activate_staff.php`                # Kích hoạt tài khoản staff
`address.php`                       # Quản lý địa chỉ
`db_health.php`                     # Kiểm tra kết nối database
`detail.php`                        # Chi tiết món ăn
`footer.php`                        # Footer chung
`forgot_password.php`               # Quên mật khẩu
`header.php`                        # Header chung
`index.php`                         # Trang chủ
`login_cus.php`                     # Đăng nhập khách hàng
`logout.php`                        # Đăng xuất
`menu.php`                          # Menu điều hướng
`promotion.php`                     # Trang khuyến mãi
`registration.php`                  # Đăng ký tài khoản
`reservation.php`                   # Đặt bàn
`reset_password.php`                # Đặt lại mật khẩu
`simulated_mailbox.txt`             # Simulation mailbox
`verify_email.php`                  # Xác thực email
`viewer.php`                        # Viewer
## Chi Tiết Triển Khai
•	Áp dụng kiến trúc MVC
•	Routing thân thiện thông qua .htaccess
•	MySQLi/PDO cho truy vấn an toàn
•	Chia nhỏ code theo Controller – Model – View
•	Sử dụng Session để quản lý đăng nhập
## Các Tuyến Đường (Routes) Chính
Quản Trị Viên
`/login` – Đăng nhập
`/admin/dashboard` – Trang tổng quan
`/admin/menu` – Quản lý món ăn
`/admin/promotions` – Quản lý khuyến mãi
`/admin/reservations` – Quản lý đặt bàn
`/admin/users` – Quản lý người dùng
`/admin/settings` – Cấu hình thông tin trang
Nhân Viên
`/login` – Đăng nhập
`/admin/menu` – Quản lý món ăn
`/admin/promotions` – Quản lý khuyến mãi
`/admin/reservations` – Phê duyệt đặt bàn
Khách Hàng
`/view` – Trang chủ
`/menu` – Danh sách món
`/detail/{id}` – Chi tiết món ăn
`/promotion` – Khuyến mãi
`/reservation` – Đặt bàn
`/login_cus` – Đăng nhập
`/registration` – Đăng ký
`/account` – Tài khoản khách hàng
## Hướng Dẫn Sử Dụng
Tài liệu hướng dẫn sử dụng hệ thống được lưu tại thư mục /docs

