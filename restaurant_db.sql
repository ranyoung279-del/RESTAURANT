-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 18, 2025 lúc 12:54 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `restaurant_db`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin_invite_tokens`
--

CREATE TABLE `admin_invite_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `admin_invite_tokens`
--

INSERT INTO `admin_invite_tokens` (`id`, `user_id`, `token`, `expires_at`, `used_at`) VALUES
(1, 16, 'dd1d55c7d3fc59fea5d94fcad825327e09827ab09b22c01eee4030dc6ddf5419', '2025-11-19 15:53:05', '2025-11-18 15:53:23'),
(2, 20, '7e8f44a300411dd56b98a666dc1c48d6a8e2e4849c4e589ddb9ca164da89bbab', '2025-11-19 18:02:54', '2025-11-18 18:03:39');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email_verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `customers`
--

INSERT INTO `customers` (`id`, `full_name`, `email`, `phone`, `password_hash`, `created_at`, `email_verified_at`) VALUES
(7, 'Nguyễn Thị Ngọc Ánh', 'ranyoung279@gmail.com', '09876543312', '$2y$10$PYDlMgdyBuU42LcNzzaCHOHMdfLUGMRuPKsKppaetKqFRMHNaG85K', '2025-10-24 03:07:14', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customer_email_verification_tokens`
--

CREATE TABLE `customer_email_verification_tokens` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `customer_email_verification_tokens`
--

INSERT INTO `customer_email_verification_tokens` (`id`, `customer_id`, `token`, `expires_at`, `used_at`) VALUES
(1, 8, '6bd0b3089fcbcff412e7b669f63469f084b081b67d34966033b87fd01c6365be', '2025-11-19 08:52:26', '2025-11-18 14:52:50'),
(2, 9, '749652657bccde588852bea14fed4d35101e6ce86805ac8009718c2cea9f93bf', '2025-11-19 09:17:17', '2025-11-18 15:17:29'),
(3, 10, '868d84ec0a0f0d3e77b4f19fc6fc02df0d606cebe58774b5bc15a25e93175219', '2025-11-19 09:37:59', '2025-11-18 15:38:17'),
(4, 11, 'a7b2a8797b8cb31d1a6cd43b2945f7050ebd3028986e85e97d636d664e17e94f', '2025-11-19 15:59:41', '2025-11-18 15:59:54');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `home_settings`
--

CREATE TABLE `home_settings` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `intro_images` text DEFAULT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `home_settings`
--

INSERT INTO `home_settings` (`id`, `title`, `description`, `intro_images`, `banner_image`, `created_at`) VALUES
(3, 'About us', 'Nhà hàng Madame Lân Đà Nẵng ra đời vào năm 2012 và là địa điểm được nhiều du khách lựa chọn để dừng chân trải nghiệm ẩm thực trọn vẹn bên bờ sông Hàn - trái tim của Đà Nẵng, nơi góc đường giao cắt giữa ba con phố Bạch Đằng, Trần Phú và Trần Quý Cáp. Được ấp ủ bởi những con người nó niềm đam mê và tình yêu cháy bỏng với ẩm thực Việt nên sứ mệnh của nhà hàng là tái hiện lại bầu không khí đầm ấm của những bữa ăn truyền thống trong không gian hài hòa với thiên nhiên.', '', 'uploads/home/1763389454_qu__n_q.jpg', '2025-05-30 10:04:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `category` enum('appetizer','main','dessert','drink') NOT NULL,
  `is_special` tinyint(1) DEFAULT 0,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `description`, `price`, `image_url`, `category`, `is_special`, `is_available`) VALUES
(2, 'Nạc Nọng Phú Quý Nướng', 'Miếng thịt nạc heo phú quý, được mệnh danh là phần ngon nhất của heo, được nướng vàng trên than hồng, mang đến hương vị đặc trưng khó quên. Thịt giòn, thớ thịt mềm mọng, được tẩm ướp đơn giản với muối ớt và lá chanh, càng thêm đậm đà khi chấm cùng nước sốt chua ngọt.', 300000.00, 'uploads/1763388407_n___c_n___ng.jpg', 'main', 1, 1),
(3, 'Rau Bầu Xào Nghêu', 'Món ăn mát lành cho những ngày hè oi bức. Rau bầu xanh ngọt kết hợp cùng nghêu béo ngọt, tạo nên một món ăn giòn ngọt, thanh mát. Hương húng quế man mát, nước bầu thơm lừng.', 275000.00, 'uploads/1763388393_rau_b___u.jpg', 'main', 1, 1),
(4, 'Bắp Bò Ngâm Tương', 'Món ăn lạnh, nhưng đầy hấp dẫn với miếng bắp bò thấm đều gia vị, phần gân giòn mà không dai. Được ủ lạnh nhiều tiếng, miếng thịt ánh lên màu nâu đẹp mắt, thơm dậy mùi bò đặc trưng, ăn kèm xì dầu, rất hợp vị.', 350000.00, 'uploads/1763388369_b___p_b___ng__m_t____ng.jpg', 'main', 1, 1),
(5, 'Đậu Que Xào Thịt Bằm', 'Đậu que giòn ngọt, xào với thịt bằm và sốt sa tế đặc biệt, kết hợp vị hải sản đặc trưng và hương thơm sa tế, tôm khô. Món ăn đậm đà, bùng vị, rất hợp khi dùng kèm cơm trắng.', 300000.00, 'uploads/1763388342______u_que_x__o.jpg', 'main', 1, 1),
(6, 'Canh Bí Xanh Nấu Tôm', 'Món canh thanh mát, giải nhiệt cho ngày hè với nước canh trong, bí giòn ngọt, tôm khô dậy hương đặc trưng. Món ăn đơn giản nhưng ngon miệng, mát lịm.', 325000.00, 'uploads/1763388330_canh_b__.jpg', 'main', 1, 1),
(7, 'Mì Quảng', 'Mộc mạc như chính con người xứ Quảng, tô mì là sự hòa quyện giữa sợi mì mềm dai, nước dùng sóng sánh, đậu phộng, rau sống và bánh tráng nướng giòn rụm – một bản giao hưởng vừa đủ để đánh thức mọi giác quan.', 400000.00, 'uploads/1763388318_m___qu___ng.jpg', 'appetizer', 1, 1),
(8, 'Bánh hỏi thịt nướng', 'Lớp bánh hỏi mỏng tang, thơm mùi gạo mới, quấn cùng thịt nướng đậm đà và rau sống xanh mát. Chấm nhẹ nước mắm chua ngọt là đủ để thấy được sự khéo léo, tinh tế của ẩm thực miền Trung.', 260000.00, 'uploads/1763388304_b__nh_h___i.jpg', 'appetizer', 0, 1),
(9, 'Cá nướng song vị', 'Cá tươi nướng than hoa giòn rụm, một nửa thơm lừng lá é – bạc hà, một nửa cay nồng vị muối ớt, tỏi và hành tím. Ăn kèm rau cải, lá tía tô, bạc hà... Chấm sốt ớt xiêm chua cay đặc biệt – tất cả hòa quyện trong từng miếng cắn đậm vị.', 400000.00, 'uploads/1763388289_c___n_____ng_song_v___.jpg', 'main', 1, 1),
(10, 'Chè dừa non thạch lá dứa', 'Một món tráng miệng ngon rất tuyệt vời', 50000.00, 'uploads/1763388276_ch___d___a.jpg', 'dessert', 0, 1),
(11, 'Chè dừa dầm', '', 50000.00, 'uploads/1763388260_ch___d___a_d___m.jpg', 'dessert', 0, 1),
(12, 'Kem bơ', '', 60000.00, 'uploads/1763388248_kem_b__.jpg', 'dessert', 0, 1),
(13, 'Panna cotta', 'Món tráng miệng ngọt này thường các đơn vị cung cấp Catering Service lựa chọn trong menu các bữa tiệc ngọt bởi hương vị thơm ngon và có thể kết hợp với nhiều loại trái cây khác.', 50000.00, 'uploads/1763388217_panna.jpg', 'dessert', 0, 1),
(14, 'Kem dâu', 'Dù mùa hè hay mùa đông thì kem lạnh vẫn là một món tráng miệng được nhiều người ưa thích', 50000.00, 'uploads/1763388202_kem_d__u.jpg', 'dessert', 0, 1),
(15, 'Sinh tố bơ', 'Sinh tố bơ là một trong những loại đồ uống đang được ưa chuộng vì chứa nhiều Kali và Natri, giúp cân bằng các chất điện giải trong cơ thể, giảm nguy cơ mắc các bệnh về tim mạch và huyết áp cho người dùng.', 50000.00, 'uploads/1763388192_sinh_t____b__.jpg', 'drink', 0, 1),
(16, 'Nước ép cam', 'Theo các nhà nghiên cứu đã chỉ ra rằng trong nước cam chứa hàm lượng Vitamin cực lớn, vì vậy nó mang đến khả năng tăng cường miễn dịch, tăng sức đề kháng.', 50000.00, 'uploads/1763388491_n_____c___p_cam.jpg', 'drink', 0, 1),
(17, 'Mojito chanh dây', 'Vị chua ngọt tự nhiên hoà lẫn với một chút nồng nàn từ rượu, thanh mát từ lá bạc hà hoà tan trong những viên đá lạnh sẽ khiến bạn hoàn toàn bị chinh phục ngay từ lần thưởng thức đầu tiên.', 50000.00, 'uploads/1763388165_chanh_d__y.jpg', 'drink', 0, 1),
(18, 'Saigon Special Lager Beer', 'Thành phần bia Saigon Lager này bao gồm nước, lúa mạch và hoa bia, mà không có chứa các loại ngũ cốc khác nên khi uống không có vị đắng nhiều, dễ uống.', 50000.00, 'uploads/1763388069_saigon.jpg', 'drink', 1, 1),
(19, 'Sữa nóng', 'Sữa đậu nành ít chất béo, thanh đạm, phù hợp với đủ mọi lứa tuổi. Một ly sữa đậu nành nóng, thơm, ăn cùng một chút bánh mì ngọt hay mặn đều giúp bạn có một ngày mới nhiều năng lượng hơn.', 20000.00, 'uploads/1763388047_s___a_n__ng.jpg', 'drink', 0, 1),
(20, 'Nước ép bưởi', 'Bưởi là loại trái cây ăn tráng miệng được rất nhiều người yêu thích, nên nước ép bưởi là một lựa chọn bạn nên cân nhắc cho vào menu quán. Ngoài nước ép cam, nước ép bưởi cũng là nguồn cung cấp vitamin C dồi dào, kèm theo hàm lượng cap chất xơ, vitamin A và kali.', 50000.00, 'uploads/1763388034_n_____c___p_b_____i1.jpg', 'drink', 0, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `used_at`) VALUES
(1, 'oanh1@gmail.com', 'b998a0173d6032e39e7b42fd4cd923e1', '2025-11-18 09:53:33', NULL),
(2, 'phuong@gmail.com', '$2y$10$/dYhSCLj845LSH.6JLc7ZumxZU06ysJQmZZzTngEEfpnDbIvr2Yt2', '2025-11-18 10:20:43', NULL),
(3, 'phuong@gmail.com', '6ea41727e1e5af59dcd5dd5893a5f510', '2025-11-18 10:25:43', NULL),
(4, 'phuong@gmail.com', 'b3de5e0eb44a8d9a993212210d85f354', '2025-11-18 10:25:54', NULL),
(5, 'phuong@gmail.com', 'c4f852c95bb519f298f344ea50507a56', '2025-11-18 10:25:56', NULL),
(6, 'phuong@gmail.com', '40fbd959206810c67bf6eaa14173306c', '2025-11-18 10:28:03', NULL),
(7, 'phuong@gmail.com', '07d6dc40e913ba168bca0b3fe7371f0d', '2025-11-18 10:28:09', NULL),
(8, 'oanh@gmail.com', '1f7acbe77782a27f0d12bb8d4ea6d5d8', '2025-11-18 10:36:55', NULL),
(9, 'phuong@gmail.com', '9f26a53d21cffb2b5928e7fcb95b5ab3', '2025-11-18 10:40:24', NULL),
(10, 'oanh@gmail.com', 'ddc68f9a662bb4e03463be272171e432', '2025-11-19 03:30:28', NULL),
(11, 'o@gmail.com', '7b695ca83148642a7dd5a76f23752729', '2025-11-18 16:54:16', '2025-11-18 15:54:33'),
(12, 'oanh@gmail.com', '1363389f16b610f18bfe9c6e90403bc0', '2025-11-18 16:54:49', NULL),
(13, 'a@gmail.com', 'bdfd46157cafb03aaf9051582fbb3cf4', '2025-11-18 17:00:48', '2025-11-18 16:01:07'),
(14, 'ranyoung279@gmail.com', 'ddded42830524f0482923c7c854a963f', '2025-11-18 18:19:59', '2025-11-18 17:22:20'),
(15, 'haha@gmail.com', '2d98be0a9ce3e9acef07949ab0e14469', '2025-11-18 18:20:12', NULL),
(16, 'ranyoung279@gmail.com', 'c6b3094262fc3a287e20b234b16bd8a4', '2025-11-18 18:31:35', '2025-11-18 17:33:04'),
(17, 'daemne@gmail.com', '32d0019e4d4b9007e79ae6acf0381f89', '2025-11-18 18:37:32', NULL),
(18, 'daemne@gmail.com', 'ceecce663a4f7a35d666458c769a129d', '2025-11-18 18:41:39', NULL),
(19, 'thune@gmail.com', '82eb7ef8844513769f9c899e318f77f1', '2025-11-18 19:05:03', '2025-11-18 18:05:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percent','fixed') NOT NULL DEFAULT 'percent',
  `discount_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `apply_to_menu_ids` text DEFAULT NULL,
  `apply_to_all` tinyint(1) NOT NULL DEFAULT 0,
  `coupon_code` varchar(50) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `promotions`
--

INSERT INTO `promotions` (`id`, `title`, `description`, `discount_type`, `discount_value`, `apply_to_menu_ids`, `apply_to_all`, `coupon_code`, `image_url`, `start_at`, `end_at`, `active`, `created_at`) VALUES
(1, 'Giảm 30% cho mùa Giáng Sinh', 'Ưu đãi đặc biệt duy nhất hôm nay cho tất cả đơn hàng!', 'percent', 30.00, NULL, 1, 'MERRY30', 'uploads/promo1.jpg', NULL, '2025-12-31 23:59:00', 1, '2025-11-17 08:48:02'),
(2, 'Giảm 50.000đ cho đơn từ 300.000đ', 'Áp dụng cho tất cả sản phẩm trong cửa hàng.', 'fixed', 50000.00, NULL, 1, 'SALE50K', 'uploads/promo2.jpg', '2025-12-10 00:00:00', '2025-12-31 23:59:59', 1, '2025-11-17 08:48:02'),
(3, 'Ưu đãi 20% cho thành viên mới', 'Chào mừng bạn đến với cửa hàng của chúng tôi!', 'percent', 20.00, NULL, 0, 'WELCOME20', '', '2025-11-01 09:43:00', '2025-11-22 09:40:00', 1, '2025-11-17 08:48:02'),
(4, 'Giảm 100.000đ đơn từ 500.000đ', 'Ưu đãi giới hạn, nhanh tay sử dụng!', 'fixed', 100000.00, NULL, 0, 'HOT100K', NULL, '2025-12-15 00:00:00', '2026-01-10 23:59:59', 1, '2025-11-17 08:48:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `reservation_date` datetime NOT NULL,
  `people_count` int(11) NOT NULL,
  `note` text DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `confirmation_code` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `table_type` varchar(20) NOT NULL DEFAULT 'Bàn thường',
  `customer_id` int(11) DEFAULT NULL,
  `confirmed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `reservations`
--

INSERT INTO `reservations` (`id`, `full_name`, `phone`, `reservation_date`, `people_count`, `note`, `status`, `confirmation_code`, `created_at`, `table_type`, `customer_id`, `confirmed_by`) VALUES
(8, 'Nguyễn Thị Ngọc Ánh', '098765432', '2025-06-29 14:30:00', 4, '', 'confirmed', NULL, '2025-06-18 10:22:06', 'Bàn VIP', NULL, 8),
(9, 'Ánh', '098765432', '2025-06-29 10:27:00', 4, '', 'confirmed', NULL, '2025-06-18 10:23:33', 'Bàn VIP', NULL, 8),
(10, 'Nguyễn Thị Ngọc Ánh', '0898140163', '2025-10-31 18:49:00', 3, 'Chuẩn bị đúng giờ', 'pending', NULL, '2025-10-24 18:46:40', 'Bàn VIP', 7, NULL),
(11, 'Nguyễn Thị Ngọc Ánh', '0987654321', '2025-10-31 02:44:00', 10, '', 'pending', NULL, '2025-10-25 10:40:03', 'Bàn thường', 7, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `restaurant_name` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `open_hours` varchar(100) DEFAULT NULL,
  `social_links` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `settings`
--

INSERT INTO `settings` (`id`, `restaurant_name`, `address`, `phone`, `email`, `open_hours`, `social_links`) VALUES
(3, 'Madame Lân Đà Nẵng', 'Số 04 Bạch Đằng, Phường Thạch Thang, Q. Hải Châu, TP. Đà Nẵng', '0236 3616 234 - 0905', 'sale1@madamelan.vn', '6:30 – 21:30 thứ Hai đến Chủ nhật hằng tuần', '{\"facebook\":\"https://www.facebook.com/wenzhuhna\",\"instagram\":\"https://www.instagram.com/_a__n__h_zh/\",\"tiktok\":\"https://www.tiktok.com/@22x_zhu_\"}');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','staff') DEFAULT 'staff',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `created_at`) VALUES
(8, 'Nguyen Thi Lan Anh', '', '$2y$10$9odEdIPmiyZOvWZHqCdesuUSA/1iEEAlu7zsH7JfLFBfqJ7iI/z32', 'admin', '2025-05-30 16:04:57'),
(14, 'ngathu', 'nga@gmail.com', '$2y$10$d6wZrI/7D05ayGQfNHS.u.hzKx2Ta3p5EqLgoWFrqUAn/H.IofZ4G', 'admin', '2025-11-15 10:37:44'),
(15, 'admin', 'adminmoi@gmail.com', '$2y$10$4yYtS.7VoHC2VPrqWD8iTuKERXU8hHde02hSECLV1AuDDbgbviyRO', 'admin', '2025-11-16 18:53:24'),
(20, 'hahaha', 'thune@gmail.com', '$2y$10$L/19B9TrTaqk16/JMRmq/O99Rd.d0NYShy3oDhXwiLgDS.Y9SmKQu', 'staff', '2025-11-18 18:02:54');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `admin_invite_tokens`
--
ALTER TABLE `admin_invite_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_unique` (`token`),
  ADD KEY `fk_ait_user` (`user_id`);

--
-- Chỉ mục cho bảng `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `customer_email_verification_tokens`
--
ALTER TABLE `customer_email_verification_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_unique` (`token`),
  ADD KEY `fk_cevt_customer` (`customer_id`);

--
-- Chỉ mục cho bảng `home_settings`
--
ALTER TABLE `home_settings`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_unique` (`token`),
  ADD KEY `idx_pr_email` (`email`);

--
-- Chỉ mục cho bảng `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reservation_customer` (`customer_id`),
  ADD KEY `fk_reservations_confirmed_by` (`confirmed_by`);

--
-- Chỉ mục cho bảng `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `admin_invite_tokens`
--
ALTER TABLE `admin_invite_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `customer_email_verification_tokens`
--
ALTER TABLE `customer_email_verification_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `home_settings`
--
ALTER TABLE `home_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT cho bảng `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
