<?php

namespace App\Interfaces;

use App\Models\Comment;

interface CommentRepositoryInterface
{
    /**
    * Lấy bình luận theo ID
    * @param int $commentId ID của bình luận
    * @return Comment Bình luận
    */
    public function getCommentByID(int $commentId): Comment;
    
    /**
    * Lấy các bình luận theo image_id với phân trang
    * @param int $imageId ID của hình ảnh
    * @param int $commentId ID của bình luận
    * @param int $page Trang hiện tại
    * @return array Danh sách các bình luận
    */
    public function getComments(int $imageId, ?int $commentId = null, int $page): array;
    
    /**
    * Lấy bình luận gốc theo image_id với phân trang
    * @param int $imageId ID của hình ảnh
    * @param int $page Trang hiện tại
    * @return array Danh sách các bình luận gốc
    */
    public function getMainComments(int $imageId, int $page): array;
    
    /**
    * Lấy các bình luận trả lời theo parent_id với phân trang
    * @param int $commentId ID của bình luận
    * @param int $page Trang hiện tại
    * @return array Danh sách các bình luận trả lời
    */
    public function getReplies(int $commentId, int $page): array;

    /**
    * Tạo bình luận
    * @param array $data Dữ liệu của bình luận
    * @return Comment Bình luận
    */
    public function storeComment(array $data): Comment;
    
    /**
    * Tạo phản hồi cho một bình luận
    * @param array $data Dữ liệu của phản hồi
    * @param Comment $comment Bình luận
    * @return Comment Phản hồi
    */
    public function storeReply(array $data, Comment $comment): Comment;
    
    /**
    * Cập nhật bình luận
    * @param Comment $comment Bình luận
    * @param string $content Nội dung của bình luận
    * @return Comment Bình luận
    */
    public function updateComment(Comment $comment, string $content): Comment;
    
    /**
    * Xóa bình luận
    * @param Comment $comment Bình luận
    * @return void
    */
    public function deleteComment(Comment $comment): void;
        
    /**
    * Tăng số lượng bình luận cho một hình ảnh
    * @param int $imageId ID của hình ảnh
    * @return void
    */
    public function incrementImageCommentCount(int $imageId): void;
    
    /**
    * Giảm số lượng bình luận cho một hình ảnh
    * @param int $imageId ID của hình ảnh
    * @return void
    */
    public function decrementImageCommentCount(int $imageId): void;
} 