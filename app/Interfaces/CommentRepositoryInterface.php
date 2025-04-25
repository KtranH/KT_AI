<?php

namespace App\Interfaces;

use App\Models\Comment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CommentRepositoryInterface
{
    // Lấy bình luận theo image_id với phân trang
    public function getComments(int $imageId, ?int $commentId = null, int $page): array;
    
    // Lấy bình luận gốc theo image_id với phân trang
    public function getMainComments(int $imageId, int $page): array;
    
    // Lấy các bình luận trả lời theo parent_id với phân trang
    public function getReplies(int $commentId, int $page): array;
    
    // Tạo bình luận mới
    public function storeComment(array $data): Comment;
    
    // Tạo phản hồi cho một bình luận
    // Comment $comment có thể là bình luận gốc hoặc phản hồi khác (cho phản hồi lồng nhau)
    public function storeReply(array $data, Comment $comment): Comment;
    
    // Cập nhật bình luận
    public function updateComment(Comment $comment, string $content): Comment;
    
    // Xóa bình luận
    public function deleteComment(Comment $comment): void;
        
    // Tăng số lượng bình luận cho một hình ảnh
    public function incrementImageCommentCount(int $imageId): void;
    
    // Giảm số lượng bình luận cho một hình ảnh
    public function decrementImageCommentCount(int $imageId): void;
} 