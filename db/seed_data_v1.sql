-- ============================================================
-- SEED DATA cho database `flower_shop_db`
-- ------------------------------------------------------------
-- Chạy SAU file flower_shop_db_v1.sql (file đó đã tạo bảng và
-- các tài khoản admins / customers, mật khẩu đều là: 1)
--
--   admins    : admin (1), quanly (2)
--   customers : user1 (1), user2 (2), user3 (3)
--
-- Ảnh sản phẩm lấy từ thư mục uploads/ (chỉ lưu tên file).
-- File này có thể chạy lại nhiều lần (sẽ xóa dữ liệu cũ của
-- các bảng bên dưới, KHÔNG đụng tới admins / customers).
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = "+00:00";
-- Xóa dữ liệu cũ (bảng con trước, bảng cha sau) rồi reset AUTO_INCREMENT
DELETE FROM `comments`;
DELETE FROM `order_details`;
DELETE FROM `orders`;
DELETE FROM `cart`;
DELETE FROM `product_promotions`;
DELETE FROM `promotions`;
DELETE FROM `products`;
DELETE FROM `categories`;

ALTER TABLE `comments` AUTO_INCREMENT = 1;
ALTER TABLE `order_details` AUTO_INCREMENT = 1;
ALTER TABLE `orders` AUTO_INCREMENT = 1;
ALTER TABLE `cart` AUTO_INCREMENT = 1;
ALTER TABLE `product_promotions` AUTO_INCREMENT = 1;
ALTER TABLE `promotions` AUTO_INCREMENT = 1;
ALTER TABLE `products` AUTO_INCREMENT = 1;
ALTER TABLE `categories` AUTO_INCREMENT = 1;

START TRANSACTION;

-- ------------------------------------------------------------
-- categories
-- ------------------------------------------------------------
INSERT INTO `categories` (`category_id`, `category_name`, `description`) VALUES
(1, 'Hoa Sinh Nhật', 'Những bó hoa tươi tắn, rực rỡ dành tặng người thân, bạn bè trong ngày sinh nhật.'),
(2, 'Hoa Tình Yêu', 'Hoa hồng và các mẫu hoa lãng mạn dành cho người thương, dịp kỷ niệm, Valentine.'),
(3, 'Hoa Hướng Dương', 'Hoa hướng dương mang ý nghĩa may mắn, năng lượng tích cực và niềm tin.'),
(4, 'Hoa Tulip', 'Tulip nhập khẩu sang trọng, tinh tế, phù hợp làm quà tặng cao cấp.'),
(5, 'Hộp Hoa Mica', 'Hoa được cắm trong hộp mica hiện đại, giữ form đẹp và bền lâu.'),
(6, 'Hoa Chúc Mừng', 'Hoa chúc mừng khai trương, tốt nghiệp, thăng chức và các dịp đặc biệt.');

-- ------------------------------------------------------------
-- products (image_url = tên file trong thư mục uploads/)
-- ------------------------------------------------------------
INSERT INTO `products` (`product_id`, `product_name`, `price`, `description`, `image_url`, `stock_quantity`, `status`, `category_id`) VALUES
(1, 'Bó Hoa Hồng Thanh Xuân', 450000.00, 'Bó hoa hồng phấn kết hợp baby trắng, gói giấy Hàn Quốc tông pastel nhẹ nhàng, thích hợp tặng sinh nhật bạn bè, người thân.', '1776937797_Bo-Hoa-Hong-Thanh-Xuan-Tang-Sinh-Nhat-Vuon-Hoa-Tuoi-768x1024.jpg', 25, 'available', 1),
(2, 'Bó Hoa Tươi Cotton Candy Hug', 520000.00, 'Tông màu kẹo bông ngọt ngào với hồng da, cẩm chướng và cúc mẫu đơn, mang đến cảm giác ấm áp như một cái ôm.', '1776937879_Bo-Hoa-Tuoi-Cotton-Candy-Hug-Tang-Sinh-Nhat-Vuon-Hoa-Tuoi-1-768x1024.jpg', 18, 'available', 1),
(3, 'Bó Hoa Hướng Dương Sunset Pinky', 480000.00, 'Hướng dương rực rỡ phối cùng hồng pinky và lá bạc, tượng trưng cho niềm vui, sự lạc quan và thành công.', '1776937973_Bo-Hoa-Huong-Duong-Sunset-Pinky-768x1024.jpg', 20, 'available', 3),
(4, 'Bó Hoa Pastel Dịu Dàng', 390000.00, 'Bó hoa mix tông pastel nhẹ nhàng gồm hồng, cát tường và baby, phù hợp chúc mừng nhiều dịp.', '1776938112_4-768x768.jpg', 30, 'available', 6),
(5, 'Bó Hoa Hồng Sophia Romantic White', 650000.00, 'Hồng trắng Sophia thanh khiết kết hợp lá bạc, mang vẻ đẹp tinh tế, sang trọng và lãng mạn.', '1776938267_Bo-Hoa-Hong-Sophia-Romatic-White-768x1024.jpg', 15, 'available', 2),
(6, 'Bó Hoa Baby Mix Hồng', 350000.00, 'Bó hoa baby mix hồng xinh xắn, nhỏ gọn, giá tốt — món quà dễ thương cho mọi dịp.', '1776938349_14-1-png.jpg', 40, 'available', 1),
(7, 'Bó Hoa Hồng Garden Of Love', 750000.00, 'Khu vườn tình yêu với nhiều loại hồng ngoại nhập phối màu hài hòa, dành tặng người thương ngày kỷ niệm.', '1776953516_Bo-Hoa-Hong-Garden-Of-Love-2-Vuon-Hoa-Tuoi-768x1024.jpg', 12, 'available', 2),
(8, 'Bó Hoa Hồng Đỏ LuvHeart', 890000.00, 'Hồng đỏ Ecuador cắm hình trái tim nổi bật, lời tỏ tình ngọt ngào và nồng nhiệt nhất.', '1776953632_bo-hoa-hong-do-LuvHeart-png.jpg', 10, 'available', 2),
(9, 'Bó Hoa Hydra Blue & Tulip White', 980000.00, 'Cẩm tú cầu xanh Hydra kết hợp tulip trắng nhập khẩu Hà Lan, sang trọng và thanh lịch.', '1777394050_Hydra-Blue-and-Tulip-White.jpg', 8, 'available', 4),
(10, 'Bó Hoa Tulip Pink Vintage', 850000.00, 'Tulip hồng phong cách vintage, gói giấy kraft cổ điển, phù hợp tặng người yêu hoặc mẹ.', '1777394183_Bo-Hoa-Tuoi-Tulip-Pink-Tulip-Vintage-Vuon-Hoa-Tuoi-2.jpg', 14, 'available', 4),
(11, 'Hộp Hoa Mica Radiant Emotions', 690000.00, 'Hộp hoa mica trong suốt với hồng, cẩm chướng và hoa lá phụ, giữ form đẹp, dễ trưng bày.', '1777394965_Mica-FlowerBox-Radiant-Emotions-2-768x1024.jpg', 16, 'available', 5),
(12, 'Bó Hoa Tươi Flower B', 420000.00, 'Bó hoa tươi phối màu trẻ trung, gói giấy hiện đại, thích hợp chúc mừng tốt nghiệp, khai trương.', '1777524440_Bo-Hoa-Tuoi-Flower-B-2-667x800.jpg', 0, 'out_of_stock', 6),
(13, 'Bó Hoa Hồng Shimmer Hạnh Phúc', 560000.00, 'Hồng shimmer lấp lánh mang thông điệp hạnh phúc, rạng rỡ — lựa chọn tuyệt vời cho ngày sinh nhật.', '1777525246_Bo-Hoa-Hong-Shimmer-Hanh-Phuc-2-768x1024.jpg', 22, 'available', 1);

-- ------------------------------------------------------------
-- promotions
-- ------------------------------------------------------------
INSERT INTO `promotions` (`promotion_id`, `promotion_code`, `promotion_name`, `description`, `discount_value`, `discount_type`, `start_date`, `end_date`, `status`) VALUES
(1, 'NOEL2026', 'Giáng Sinh An Lành', 'Giảm 15% cho các mẫu hoa Tulip và hộp hoa mùa Giáng Sinh.', 15.00, 'percentage', '2026-12-01', '2026-12-31', 'active'),
(2, 'BLACKFRIDAY', 'Black Friday Sale', 'Giảm 20% cho các mẫu hoa sinh nhật và hướng dương dịp Black Friday.', 20.00, 'percentage', '2026-11-20', '2026-11-30', 'active'),
(3, 'GIAM50K', 'Giảm 50K Hoa Tình Yêu', 'Giảm trực tiếp 50.000đ cho các bó hồng đỏ, hồng ngoại nhập.', 50000.00, 'fixed_amount', '2026-11-01', '2027-01-31', 'active'),
(4, 'TET2027', 'Đón Tết Bính Ngọ', 'Giảm 10% cho các mẫu hoa cao cấp dịp Tết Nguyên Đán.', 10.00, 'percentage', '2027-01-15', '2027-02-20', 'inactive'),
(5, 'VALENTINE26', 'Valentine Ngọt Ngào', 'Giảm 20% cho hoa tình yêu dịp Valentine 14/2.', 20.00, 'percentage', '2026-02-01', '2026-02-15', 'expired'),
(6, 'PHUNU83', 'Mừng Ngày Phụ Nữ 8/3', 'Giảm 30.000đ cho mọi bó hoa dịp Quốc tế Phụ nữ.', 30000.00, 'fixed_amount', '2026-03-01', '2026-03-08', 'expired');

-- ------------------------------------------------------------
-- product_promotions
-- ------------------------------------------------------------
INSERT INTO `product_promotions` (`product_promotion_id`, `product_id`, `promotion_id`) VALUES
(1, 2, 2),
(2, 3, 2),
(3, 13, 2),
(4, 7, 3),
(5, 8, 3),
(6, 10, 1),
(7, 11, 1),
(8, 9, 4),
(9, 5, 5),
(10, 6, 6);

-- ------------------------------------------------------------
-- cart
-- ------------------------------------------------------------
INSERT INTO `cart` (`cart_id`, `customer_id`, `product_id`, `quantity`) VALUES
(1, 1, 3, 1),
(2, 1, 8, 2),
(3, 2, 10, 1),
(4, 3, 1, 1),
(5, 3, 11, 1),
(6, 2, 13, 2);

-- ------------------------------------------------------------
-- orders
-- ------------------------------------------------------------
INSERT INTO `orders` (`order_id`, `order_code`, `customer_id`, `total_amount`, `receiver_name`, `receiver_phone`, `delivery_address`, `note`, `order_status`, `payment_status`, `payment_method`, `order_date`) VALUES
(1, 'ORD-A1F3C9', 1, 1150000.00, 'Nguyễn Văn An', '0912345671', '12 Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh', 'Giao trước 10h sáng giúp mình.', 'completed', 'paid', 'cod', '2026-10-05 08:30:00'),
(2, 'ORD-B72D41', 1, 890000.00, 'Nguyễn Thị Lan', '0987654321', '30 Pasteur, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh', 'Tặng bạn gái, nhớ kèm thiệp chúc mừng.', 'shipping', 'unpaid', 'cod', '2026-11-18 14:15:00'),
(3, 'ORD-C9E082', 2, 1410000.00, 'Trần Thị Bình', '0912345672', '45 Lê Lợi, Phường Bến Thành, Quận 1, TP. Hồ Chí Minh', NULL, 'confirmed', 'paid', 'bank_transfer', '2026-11-15 09:45:00'),
(4, 'ORD-D4A6B3', 2, 650000.00, 'Trần Văn Hùng', '0977111222', '102 Hai Bà Trưng, Phường Đa Kao, Quận 1, TP. Hồ Chí Minh', 'Gọi trước khi giao.', 'pending', 'unpaid', 'cod', '2026-11-21 19:20:00'),
(5, 'ORD-E5B7C4', 3, 1500000.00, 'Lê Văn Cường', '0912345673', '88 Trần Hưng Đạo, Quận Hoàn Kiếm, Hà Nội', 'Hoa kỷ niệm ngày cưới.', 'completed', 'paid', 'e_wallet', '2026-09-20 10:00:00'),
(6, 'ORD-F6C8D5', 3, 980000.00, 'Lê Văn Cường', '0912345673', '88 Trần Hưng Đạo, Quận Hoàn Kiếm, Hà Nội', NULL, 'cancelled', 'refunded', 'e_wallet', '2026-10-12 16:40:00'),
(7, 'ORD-G7D9E6', 1, 910000.00, 'Phạm Thị Dung', '0912345674', '23 Bạch Đằng, Quận Hải Châu, Đà Nẵng', 'Chúc mừng sinh nhật mẹ.', 'completed', 'paid', 'cod', '2026-11-02 07:50:00'),
(8, 'ORD-H8E0F7', 1, 690000.00, 'Nguyễn Văn An', '0912345671', '12 Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh', NULL, 'pending', 'unpaid', 'bank_transfer', '2026-11-22 11:05:00'),
(9, 'ORD-J9F1A8', 2, 1440000.00, 'Trần Thị Bình', '0912345672', '45 Lê Lợi, Phường Bến Thành, Quận 1, TP. Hồ Chí Minh', 'Hoa khai trương cửa hàng.', 'completed', 'paid', 'cod', '2026-08-14 08:00:00'),
(10, 'ORD-K0A2B9', 2, 870000.00, 'Hoàng Thị Mai', '0933444555', '77 Cách Mạng Tháng 8, Phường 6, Quận 3, TP. Hồ Chí Minh', 'Giao giờ hành chính.', 'shipping', 'paid', 'bank_transfer', '2026-11-19 13:30:00');

-- ------------------------------------------------------------
-- order_details (subtotal = quantity * unit_price; tổng khớp total_amount)
-- ------------------------------------------------------------
INSERT INTO `order_details` (`order_detail_id`, `order_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 1, 1, 450000.00, 450000.00),
(2, 1, 6, 2, 350000.00, 700000.00),
(3, 2, 8, 1, 890000.00, 890000.00),
(4, 3, 10, 1, 850000.00, 850000.00),
(5, 3, 13, 1, 560000.00, 560000.00),
(6, 4, 5, 1, 650000.00, 650000.00),
(7, 5, 7, 2, 750000.00, 1500000.00),
(8, 6, 9, 1, 980000.00, 980000.00),
(9, 7, 2, 1, 520000.00, 520000.00),
(10, 7, 4, 1, 390000.00, 390000.00),
(11, 8, 11, 1, 690000.00, 690000.00),
(12, 9, 3, 3, 480000.00, 1440000.00),
(13, 10, 12, 1, 420000.00, 420000.00),
(14, 10, 1, 1, 450000.00, 450000.00);

-- ------------------------------------------------------------
-- comments (parent_comment_id != NULL là trả lời bình luận)
-- ------------------------------------------------------------
INSERT INTO `comments` (`comment_id`, `product_id`, `customer_id`, `content`, `comment_date`, `parent_comment_id`) VALUES
(1, 1, 1, 'Hoa rất tươi, gói đẹp, giao đúng giờ. Bạn mình nhận rất thích!', '2026-10-06 09:00:00', NULL),
(2, 1, 3, 'Mình cũng đặt mẫu này rồi, công nhận màu hồng ngoài đẹp hơn ảnh.', '2026-10-07 20:15:00', 1),
(3, 7, 3, 'Đặt tặng vợ ngày kỷ niệm, hoa to và thơm, rất đáng tiền.', '2026-09-21 18:30:00', NULL),
(4, 3, 2, 'Hướng dương nở đẹp, để được gần một tuần. Sẽ ủng hộ shop tiếp.', '2026-08-16 10:20:00', NULL),
(5, 8, 2, 'Mẫu trái tim hồng đỏ này có cắm thêm được thiệp không shop?', '2026-11-10 15:45:00', NULL),
(6, 10, 2, 'Tulip màu hồng vintage rất xinh, mẹ mình rất thích.', '2026-11-16 12:00:00', NULL),
(7, 10, 1, 'Tulip để được lâu không bạn? Mình đang định đặt cho mẹ.', '2026-11-17 08:40:00', 6),
(8, 10, 2, 'Để mát, thay nước mỗi ngày thì được khoảng 5-6 ngày nha bạn.', '2026-11-17 10:05:00', 7),
(9, 2, 1, 'Màu cotton candy dễ thương lắm, đóng gói cẩn thận.', '2026-11-03 19:10:00', NULL),
(10, 11, 1, 'Hộp mica sang, trưng trên bàn làm việc rất đẹp.', '2026-11-12 14:25:00', NULL),
(11, 13, 2, 'Hoa shimmer lấp lánh nhìn rất sang, giá hợp lý.', '2026-11-20 09:35:00', NULL),
(12, 5, 3, 'Hồng trắng tinh khôi, phù hợp tặng dịp trang trọng.', '2026-10-25 17:00:00', NULL);

COMMIT;
