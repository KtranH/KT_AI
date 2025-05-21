# KT_AI - Ứng dụng tạo ảnh AI

![Banner KT_AI](https://scontent.fsgn5-8.fna.fbcdn.net/v/t39.30808-6/499546024_713133724434637_2671795591277910537_n.jpg?_nc_cat=109&ccb=1-7&_nc_sid=127cfc&_nc_ohc=-7V0imz1FWQQ7kNvwEghme2&_nc_oc=Adn49snlvTm4QJwBkTARidT2ggo9euub_fwxf8I-DScYfTP_ycKoXPE2eowKbD5IlkyucwNnTLjZErvmBy5kLCQV&_nc_zt=23&_nc_ht=scontent.fsgn5-8.fna&_nc_gid=TczMluLaitgOBEtqNCXdaw&oh=00_AfLCh4oemYVPee-VsBI8Z_7gLixco1UViSaILZa6CP3QgA&oe=6833B216)

## Giới thiệu

KT_AI là một ứng dụng web hiện đại cho phép người dùng tạo và quản lý hình ảnh sử dụng trí tuệ nhân tạo thông qua ComfyUI. Dự án này kết hợp sức mạnh của các công nghệ web tiên tiến với hệ thống AI, mang lại trải nghiệm người dùng.

Ứng dụng cung cấp giao diện thân thiện với người dùng, cho phép tùy chỉnh nhiều tham số để tạo ra hình ảnh chất lượng cao theo nhu cầu. Người dùng có thể tạo ra nhiều phong cách nghệ thuật khác nhau như chân thực, phim hoạt hình, phác họa, anime, màu nước, sơn dầu, nghệ thuật số và trừu tượng.

## Công nghệ sử dụng

### Backend
- **Laravel 11**: Framework PHP hiện đại với kiến trúc MVC mạnh mẽ
- **PHP 8.2+**: Tận dụng các tính năng mới nhất của PHP 
- **MySQL**: Hệ quản trị cơ sở dữ liệu
- **Redis**: Cache và quản lý hàng đợi
- **Laravel Sanctum**: Xác thực API
- **Laravel Socialite**: Đăng nhập qua mạng xã hội
- **ComfyUI API**: Tích hợp với API của ComfyUI để tạo hình ảnh AI
- **R2 Storage**: Lưu trữ hình ảnh trên nền tảng đám mây
- **Laravel Websockets/Pusher**: Cung cấp WebSocket server để giao tiếp hai chiều giữa máy chủ và trình duyệt
- **Laravel Events & Broadcasting**: Hệ thống phát sóng sự kiện realtime
- **Laravel Queue & Jobs**: Xử lý tác vụ nặng bất đồng bộ

### Frontend
- **Vue.js 3**: Framework JavaScript sử dụng Composition API
- **Tailwind CSS**: Framework CSS tiện lợi
- **PrimeVue**: Thư viện component UI cho Vue
- **Pinia**: Quản lý state cho Vue 3
- **Vite**: Công cụ build nhanh cho frontend
- **Chart.js**: Hiển thị dữ liệu thống kê
- **Laravel Echo**: Thư viện JavaScript cho kết nối WebSocket và nhận sự kiện từ Laravel
- **Socket.io-client/Pusher JS**: Client WebSocket để nhận thông báo realtime

### CI/CD & Deployment
- **Git**: Quản lý phiên bản
- **GitHub Actions**: Tự động hóa quy trình CI/CD

## Điểm đặc biệt

### Tối ưu hóa hiệu suất ComfyUI
Một trong những điểm mạnh nổi bật của KT_AI là việc tối ưu hóa quy trình làm việc với ComfyUI, cho phép xử lý yêu cầu tạo ảnh **dưới 5 giây** cho mỗi request. Điều này đạt được nhờ:

- Sử dụng các model và thông số hợp lý để tạo ảnh
- Tối ưu hóa templates JSON cho ComfyUI
- Xử lý bất đồng bộ thông qua hàng đợi Laravel
- Cache thông minh giảm thời gian xử lý
- Kết nối trực tiếp với ComfyUI thông qua API riêng biệt

![Ví dụ về workflow](https://scontent.fsgn5-11.fna.fbcdn.net/v/t39.30808-6/499932214_713133881101288_7601055074360330534_n.jpg?_nc_cat=111&ccb=1-7&_nc_sid=127cfc&_nc_ohc=zJCLoVTp8u4Q7kNvwHMQTSD&_nc_oc=AdlTMOKuCuIzL2U_Q_IBNEplkdvJvB__RKnfpO3_BbwMugrPdq2-IAa6-7JS9zgfGZfvlgpHt0GSE2XDxwQ8yQIs&_nc_zt=23&_nc_ht=scontent.fsgn5-11.fna&_nc_gid=U-t7niAJS5ES1aDetGaMsQ&oh=00_AfLwxH649umnaNwRNVJN2aJS6PMadDPf0WRiGJwdk4I34g&oe=6833C12E)
![Minh họa tốc độ](https://scontent.fsgn5-8.fna.fbcdn.net/v/t39.30808-6/500228039_713133321101344_2862498387972226819_n.jpg?_nc_cat=109&ccb=1-7&_nc_sid=127cfc&_nc_ohc=p24tOuir8TAQ7kNvwHg5Veh&_nc_oc=AdkFb1HYm9k3iiuyVG-WG1oOHwCUIY2cH17PRjypR5EnQpMaGkRG6GtolL5_sJfaGlfyNLkiX2SEXy2boqpI8kPU&_nc_zt=23&_nc_ht=scontent.fsgn5-8.fna&_nc_gid=Ty0IO02CaRHm6Q6hvIMqYA&oh=00_AfIVgQ85jllXomd0yZXeXcPR6Qodc4ftsiDr1jZDTVIKKw&oe=6833CED5)

### Một số tính năng tạo ảnh với Comfyui
Dưới đây là một số tính năng tạo ảnh được sử dụng trong project:

| STT | Tên | Ảnh | Miêu tả |
|-----|-----|-----|---------|
| 1 | IPAdapter | ![IPAdapter](https://scontent.fsgn5-5.fna.fbcdn.net/v/t39.30808-6/500070968_713133387768004_5082475620504107495_n.jpg?_nc_cat=108&ccb=1-7&_nc_sid=127cfc&_nc_ohc=L5LtjzGCtXgQ7kNvwEkXNrm&_nc_oc=AdlUfv4Bti2J5SVlB-swbsHy4rjJiQ2zlL8xf_TTeGJ8PW65yxuzzPF6hzOKJrgyY3OjRCBBOXeSTl5x_A-zWz1l&_nc_zt=23&_nc_ht=scontent.fsgn5-5.fna&_nc_gid=wWg-1YS46zFx5rvBnrBwnQ&oh=00_AfLYCGp6YRDeJ1xOxjicLfftURfssI0Hv27soFKxF5PPEg&oe=6833A07A) | Sao chép phong cách ảnh từ người dùng tải lên |
| 2 | HyperLora | ![HyperLora](https://scontent.fsgn5-10.fna.fbcdn.net/v/t39.30808-6/500226163_713133521101324_5798570757775338567_n.jpg?_nc_cat=110&ccb=1-7&_nc_sid=127cfc&_nc_ohc=SE9RrC_5e90Q7kNvwE7Yvi9&_nc_oc=AdlrD9B87mTHI6KdOMmuP-CE5Qpjk9S8mZK5LcL4nXff-lr5l30qZ6wkMZ3zHszh27QNEMXK0gLCZJvSj8lfI0K_&_nc_zt=23&_nc_ht=scontent.fsgn5-10.fna&_nc_gid=doof716O6kiUOJsLmlQmhQ&oh=00_AfJvd41rIFxPHTomfl7EzjY4CpBCX4uZIcrMs88VuWoDsg&oe=6833C171) | Sao chép khuôn mặt từ duy nhất một ảnh mà người dùng tải lên |
| 3 | ACE++ | ![ACE++](https://scontent.fsgn5-10.fna.fbcdn.net/v/t39.30808-6/500660732_713133661101310_312358734598843290_n.jpg?_nc_cat=106&ccb=1-7&_nc_sid=127cfc&_nc_ohc=cruJUzXfxTwQ7kNvwFTX9Dw&_nc_oc=AdnbLj8fFOK7f_8weiCp1LeKsv3lWk63QzeYx0xvCRI0SVb6RlQPVFFMAZpZjqoRkJQH6lFws8vp3zkA4lQLgsQk&_nc_zt=23&_nc_ht=scontent.fsgn5-10.fna&_nc_gid=M6nwIq40REDUR7sK_zCVkg&oh=00_AfKjNMLUKjdYDD78N355aFxq04FmaJDf7db9jCQePn8RsA&oe=6833D401) | Sao chép và thay đổi quần áo |
| 4 | HyperLora + ACE++ | ![HyperLora + ACE++](https://scontent.fsgn5-3.fna.fbcdn.net/v/t39.30808-6/499921723_713133517767991_8735616934866260113_n.jpg?_nc_cat=104&ccb=1-7&_nc_sid=127cfc&_nc_ohc=OKM4wxZ3XFIQ7kNvwFe4n8s&_nc_oc=AdlGt4L6xVfZOHgnktsU8A3lnktDa7KMjZmJwZqiDBF83hBQYGg5R9g7ET_4gvftNqiPRZb1Dj1EsG4endmk7Opw&_nc_zt=23&_nc_ht=scontent.fsgn5-3.fna&_nc_gid=PDtUYP3dxr4HaaY4XcVzvQ&oh=00_AfKgUdmCs2zGzr9ExmelpP-z_aT0YY4RXicDsC9IpxWUzg&oe=6833C198) | Sao chép quần áo và khuôn mặt từ 2 ảnh mà người dùng tải lên |
| 5 | IPAdapter Mix Styles | ![IPAdapter Mix Styles](https://scontent.fsgn5-3.fna.fbcdn.net/v/t39.30808-6/500090115_713133524434657_5876669314109378599_n.jpg?_nc_cat=104&ccb=1-7&_nc_sid=127cfc&_nc_ohc=eVtWyebpSnIQ7kNvwFKPhns&_nc_oc=AdlMHf_p_-5YDVpMzjS1-Q0jPWNAak73CAwOtischTYWlMjp03sAQ9OSdWwruxYchqbb7Y_-H0w5gIrpuf8QtzAP&_nc_zt=23&_nc_ht=scontent.fsgn5-3.fna&_nc_gid=CZ4nHlEZWW4HVv8wpCozmg&oh=00_AfJUqOiT54Kh4fckzj3BKEckV7HycdgRKXqSEzSmaarSvg&oe=6833C6BF) | Sao chép và kết hợp nhiều phong cách hình ảnh khác nhau |


### Tính năng nổi bật
- **Đa dạng phong cách**: Hỗ trợ nhiều phong cách nghệ thuật khác nhau
- **Tùy chỉnh cao**: Kiểm soát chi tiết thông số như kích thước, prompt, seed
- **Theo dõi tiến trình thời gian thực**: Cập nhật tiến độ tạo ảnh theo thời gian thực qua WebSockets
- **Thông báo realtime**: Nhận thông báo tức thì khi ảnh được tạo thành công hoặc có lỗi
- **Cập nhật tương tác realtime**: Hiển thị ngay lập tức các lượt thích và bình luận mới
- **Trạng thái người dùng realtime**: Hiển thị người dùng đang online/offline
- **Lưu trữ đám mây**: Tự động lưu trữ hình ảnh trên R2 Storage
- **Thông báo**: Hệ thống thông báo khi hoàn thành hoặc xảy ra lỗi
- **Lịch sử rõ ràng**: Quản lý lịch sử tạo ảnh của người dùng
- **Tương tác cộng đồng**: Bình luận và thích hình ảnh trong cộng đồng

## Cài đặt và Cấu hình

### Yêu cầu hệ thống
- PHP 8.2 hoặc mới hơn
- Composer
- Node.js và npm
- MySQL 8.0+
- ComfyUI đã cài đặt (máy chủ độc lập)
- Redis (tùy chọn, nhưng được khuyến nghị)

### Các bước cài đặt

1. **Clone repository:**
   ```bash
   git clone https://github.com/yourusername/kt_ai.git
   cd kt_ai
   ```

2. **Cài đặt dependencies PHP:**
   ```bash
   composer install
   ```

3. **Cài đặt dependencies JavaScript:**
   ```bash
   npm install
   ```

4. **Cấu hình môi trường:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Cấu hình cơ sở dữ liệu:**
   Chỉnh sửa file `.env` với thông tin cơ sở dữ liệu của bạn:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=kt_ai
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Cấu hình ComfyUI API:**
   Thêm vào file `.env`:
   ```
   COMFYUI_URL=http://localhost:8188
   ```

7. **Cấu hình R2 Storage:**
   ```
   R2_ACCESS_KEY=your_access_key
   R2_SECRET_KEY=your_secret_key
   R2_ENDPOINT=your_endpoint
   R2_BUCKET=your_bucket
   ```

8. **Chạy migration và seeder:**
   ```bash
   php artisan migrate --seed
   ```

9. **Biên dịch tài nguyên frontend:**
   ```bash
   npm run build
   ```

10. **Khởi động máy chủ development:**
    ```bash
    php artisan serve
    ```

11. **Chạy queue worker để xử lý tạo ảnh:**
    ```bash
    php artisan queue:work
    ```

## Sử dụng

### Tạo ảnh AI

1. Đăng nhập vào hệ thống
2. Truy cập tab "Tạo ảnh" trong menu chính
3. Chọn loại ảnh và phong cách mong muốn
4. Nhập prompt mô tả nội dung ảnh
5. Tùy chỉnh các thông số như kích thước, seed
6. Tải lên ảnh gốc nếu muốn (tùy chọn)
7. Nhấn "Tạo ảnh" và đợi kết quả

### Quản lý ảnh

- Xem tất cả ảnh đã tạo trong tab "Thư viện của tôi"
- Chia sẻ ảnh với cộng đồng
- Tải xuống ảnh với chất lượng cao
- Xem chi tiết mọi thông số đã sử dụng để tạo ảnh

## Screenshots

| STT | Tên | Ảnh | Miêu tả |
|-----|-----|-----|---------|
| 1 | Trang chủ | ![Trang chủ](https://scontent.fsgn5-10.fna.fbcdn.net/v/t39.30808-6/499963778_713133424434667_1281378474063140510_n.jpg?_nc_cat=106&ccb=1-7&_nc_sid=127cfc&_nc_ohc=bye25UnvjdAQ7kNvwH98qJc&_nc_oc=Adlzt3XQ3hCB0qle4Ste8H7vKJCMEUOrth4oApivCqipH7PBXygRIhL5xBOB6mvdSJYVdYZEFl9fQMvVnVlwT9zC&_nc_zt=23&_nc_ht=scontent.fsgn5-10.fna&_nc_gid=FureVav30fz1Dy74uNTVBw&oh=00_AfJW8MQ4bBjjnIXb4N5_ji1QsYrd152zSIo3xn96plPn5Q&oe=6833B1A3) | Giao diện quản lý tài khoản, quản lý các hình ảnh tải lên từ tài khoản người dùng |
| 2 | Tạo ảnh | ![Tạo ảnh](https://scontent.fsgn5-8.fna.fbcdn.net/v/t39.30808-6/500097025_713133727767970_6084443867449313766_n.jpg?_nc_cat=109&ccb=1-7&_nc_sid=127cfc&_nc_ohc=B1D4pYRJ-I0Q7kNvwFsji7Q&_nc_oc=AdlfQKYUA4m-gve6bg2j6pUu__hKMZOtnRuxJs-EcbbaVloDpCRlI2hHG5nZgPDWeymJcNuX5tXywLBhUUgZjsgl&_nc_zt=23&_nc_ht=scontent.fsgn5-8.fna&_nc_gid=fopulKUo_oIGRYpUrVGWmQ&oh=00_AfKc_zqqP9aQh9NoiUPIpp9nfl6s9rafqZWxS8rsI_86Og&oe=6833BC2A) | Giao diện cho phép người dùng nhập prompt, điều chỉnh tham số và tạo ảnh AI |
| 3 | Đăng tải ảnh | ![Đăng tải ảnh](https://scontent.fsgn5-8.fna.fbcdn.net/v/t39.30808-6/499967487_713133841101292_477631550619519945_n.jpg?_nc_cat=109&ccb=1-7&_nc_sid=127cfc&_nc_ohc=rM1VFyoLPQ0Q7kNvwERrZpv&_nc_oc=AdkZRSJHGsf171aL2YqLtOjp4YWLgPwFlg_589zB3rShefEILCmojJWQcMya2ERej-AUxXkAndQ4H--qn6gy-UA8&_nc_zt=23&_nc_ht=scontent.fsgn5-8.fna&_nc_gid=wTT-k-ydNXxR7FwQp8GdJw&oh=00_AfJxhn8vGF-e53PNMbsCf0B0f_C9mQDSsf2A9NWkTiYkOA&oe=6833B622) | Hiển thị tất cả ảnh đã tải lên, tùy chỉnh nội dung sẽ đăng lên |
| 4 | Thông báo Realtime | ![Thông báo Realtime](https://scontent.fsgn5-10.fna.fbcdn.net/v/t39.30808-6/500195486_713133324434677_2663533459817671717_n.jpg?_nc_cat=101&ccb=1-7&_nc_sid=127cfc&_nc_ohc=AiKPDh_-tVQQ7kNvwGn4c4x&_nc_oc=Adm3DxTMl_L4Yx28iOZM7kz0IGTuEy1Kwig3harSOXbFy4CIgJ6h8CNk7kBbK2XUzEK8QwOG14l9oDcjaZbQZteZ&_nc_zt=23&_nc_ht=scontent.fsgn5-10.fna&_nc_gid=UOzHT0zXwD14mWIy7PfqYw&oh=00_AfI6Ft-SIoGaIAPa-xRaEx7f2zqV_ZBXwBRCHCNLc7tF7w&oe=6833C282) | Hiển thị thông báo Realtime đến người dùng |
| 5 | Tương tác bài đăng | ![Tương tác bài đăng](https://scontent.fsgn5-10.fna.fbcdn.net/v/t39.30808-6/500130286_713133427768000_4307341529051674178_n.jpg?_nc_cat=107&ccb=1-7&_nc_sid=127cfc&_nc_ohc=ZVtSO7weElQQ7kNvwGjBhxs&_nc_oc=AdkmgZscS1Acyj0HFICo0Mqs4yWnaISZHxdVT641DMQliKXExJ7eMDhQMuSeOZvJkh4weI5pd_iXfbG9Cz2o9q4N&_nc_zt=23&_nc_ht=scontent.fsgn5-10.fna&_nc_gid=u5-U3pWBdZRKvO7o6G9-UA&oh=00_AfKz7JkMCBb2DMrbdfJohT8iOqz_hUXfrQJ4eQHR41PuXw&oe=6833B078) | Hiển thị hình ảnh đăng và lượt tương tác như comment, reply, like... |
| 6 | Quản lý thông báo | ![Quản lý thông báo](https://scontent.fsgn5-9.fna.fbcdn.net/v/t39.30808-6/500139311_713133791101297_4910110875925901225_n.jpg?_nc_cat=102&ccb=1-7&_nc_sid=127cfc&_nc_ohc=BfvVjV2dZkUQ7kNvwGlXerw&_nc_oc=AdkXX7ouFspKa4g4_Vv-hdS9ne_IR3QIkUMlVwg23DE6uCC2fIp-7Q3L6fwz6tPvkNrY2y4jh6q9IlsebizbOmyF&_nc_zt=23&_nc_ht=scontent.fsgn5-9.fna&_nc_gid=zCIWV1ykcGT3M8oiz4IPrA&oh=00_AfIxYcvw8o_PO3zfhCwe-SyECPPP3Qk4UeqWuUrMjnkLuQ&oe=68339F0D) | Hiển thị các thông báo đã đọc và chưa đọc cũng như chuông thông báo |
| 7 | Quản lý tiến trình | ![Quản lý tiến trình](https://scontent.fsgn5-11.fna.fbcdn.net/v/t39.30808-6/500201218_713133817767961_223294507456341947_n.jpg?_nc_cat=111&ccb=1-7&_nc_sid=127cfc&_nc_ohc=PqEv4pvqzLkQ7kNvwEypLqz&_nc_oc=AdnPPWb5wJK1nY3kLEtznqVpQzRi3lCVEjMKgYooEccGoznnOxIJ14o4RE2YcmpqEcLfSWFDdg9jnWCYJZfVZsKN&_nc_zt=23&_nc_ht=scontent.fsgn5-11.fna&_nc_gid=YhQ5GJgUb2eyT-DJ3OQlKg&oh=00_AfIoO5daeQHMYFS3BcSLtdnHWDJ6Uucnj_aaG94oIZzjjg&oe=6833CCD3) | Hiển thị các tiến trình đang thực thi, đã thực thi, thực thi thất bại |
| 8 | Chế độ xác thực email | ![Chế độ xác thực email](https://scontent.fsgn5-9.fna.fbcdn.net/v/t39.30808-6/500000764_713133377768005_4327869314769536234_n.jpg?_nc_cat=102&ccb=1-7&_nc_sid=127cfc&_nc_ohc=touISGEul-8Q7kNvwE3sm6q&_nc_oc=Adm__GawYN6wfv0gEihWcvByGpvVPeD3rxeZog4Suq4vzpHdJOASiNN4ggkM390yq3DY22ObfeHakiRTSlkYQY4w&_nc_zt=23&_nc_ht=scontent.fsgn5-9.fna&_nc_gid=4dC0Vqefm024xRC0fHTYXg&oh=00_AfJgd-JYPoMks3diPLvyiRBfayjdpmca0noU7wZlUF21ag&oe=6833CA85) | Sử dụng Redis cho cơ chế xác thực email, đổi mật khẩu, quên mật khẩu... |


## Đóng góp

Dự án này được phát triển bởi Khôi Trần. Mọi đóng góp và góp ý đều được chào đón.

## Hướng dẫn sử dụng Queue

Ứng dụng này sử dụng hệ thống Queue để xử lý các tiến trình tạo ảnh bất đồng bộ. Dưới đây là cách thiết lập và sử dụng:

### Thiết lập Database

Đảm bảo bạn đã chạy migration để tạo bảng jobs:

```bash
php artisan migrate
```

### Khởi động Queue Worker

Có 2 cách để khởi động queue worker:

#### 1. Sử dụng lệnh tích hợp

```bash
php artisan queue:start-worker --daemon
```

Các tùy chọn:
- `--conn=database` - Kết nối queue sử dụng (mặc định: database)
- `--queue=default,image-processing,image-processing-low` - Danh sách queue cần xử lý
- `--sleep=3` - Số giây nghỉ khi không có job
- `--tries=3` - Số lần thử lại khi job lỗi
- `--timeout=60` - Thời gian tối đa cho mỗi job (giây)

#### 2. Sử dụng Supervisor (khuyến nghị cho môi trường production)

Tạo file cấu hình Supervisor:

```bash
php artisan queue:make-supervisor-config
```

Các tùy chọn:
- `--file=laravel-worker` - Tên file cấu hình
- `--processes=2` - Số lượng worker
- `--memory=128` - Giới hạn bộ nhớ (MB)

Sau khi tạo file cấu hình, sao chép file này vào thư mục `/etc/supervisor/conf.d/` trên máy chủ production và khởi động Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### Theo dõi Queue

Kiểm tra trạng thái Queue:

```bash
php artisan queue:monitor
php artisan queue:size
php artisan queue:failed
```

Dọn dẹp Queue:

```bash
php artisan queue:prune-batches --hours=48
php artisan queue:prune-failed --hours=24
```

### Các Queue được sử dụng

- `default` - Các job thông thường
- `image-processing` - Các job xử lý hình ảnh ưu tiên cao
- `image-processing-low` - Các job xử lý hình ảnh ưu tiên thấp (kiểm tra trạng thái)

## Cấu hình WebSockets và Realtime

Ứng dụng sử dụng WebSockets để cung cấp các tính năng realtime như thông báo và cập nhật tiến trình. Dưới đây là cách thiết lập:

### Thiết lập Laravel WebSockets

1. **Cài đặt Laravel WebSockets**:
   ```bash
   php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="migrations"
   php artisan migrate
   php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="config"
   ```

2. **Cấu hình .env**:
   ```
   BROADCAST_DRIVER=pusher
   PUSHER_APP_ID=your_app_id
   PUSHER_APP_KEY=your_app_key
   PUSHER_APP_SECRET=your_app_secret
   PUSHER_HOST=127.0.0.1
   PUSHER_PORT=6001
   PUSHER_SCHEME=http
   PUSHER_APP_CLUSTER=mt1
   ```

3. **Khởi động WebSocket Server**:
   ```bash
   php artisan websockets:serve
   ```

### Sử dụng Laravel Echo (Frontend)

Trong file JavaScript chính:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    wsHost: process.env.MIX_PUSHER_HOST || window.location.hostname,
    wsPort: process.env.MIX_PUSHER_PORT || 6001,
    forceTLS: false,
    disableStats: true,
});

// Lắng nghe sự kiện cập nhật tiến trình
window.Echo.private(`image.processing.${userId}`)
    .listen('ImageProcessingProgress', (e) => {
        console.log(e.progress);
        // Cập nhật UI với thông tin tiến trình
    });

// Lắng nghe thông báo
window.Echo.private(`notifications.${userId}`)
    .listen('NewNotification', (e) => {
        // Hiển thị thông báo mới
    });
```

### Phát sóng sự kiện từ Laravel

Ví dụ về cách phát sóng sự kiện tiến trình:

```php
event(new ImageProcessingProgress($userId, $percentComplete, $imageId));
```

Các loại kênh được sử dụng:
- `private-image.processing.{userId}`: Cập nhật tiến trình tạo ảnh
- `private-notifications.{userId}`: Thông báo cá nhân
- `presence-online`: Trạng thái người dùng online
- `public-interactions.{postId}`: Tương tác trên các bài đăng
