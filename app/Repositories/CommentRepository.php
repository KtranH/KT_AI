<?php

namespace App\Repositories;

use App\Interfaces\CommentRepositoryInterface;
use App\Models\Comment;
use App\Models\Image;
use Illuminate\Support\Facades\Auth;

class CommentRepository implements CommentRepositoryInterface
{
    private const COMMENTS_PER_PAGE = 5;
    private const REPLIES_PER_PAGE = 3;

    /**
     * Lấy comment theo ID
     * @param int $commentId ID của comment
     * @return Comment Comment
     */
    public function getCommentByID(int $commentId): Comment
    {
        return Comment::with(['user', 'replies.user'])->findOrFail($commentId);
    }

    /**
     * Lấy các comment của một ảnh
     * @param int $imageId ID của ảnh
     * @param int|null $commentId ID của comment
     * @param int $page Trang hiện tại
     * @return array Danh sách comment
     */
    public function getComments(int $imageId, ?int $commentId = null, int $page = 1): array
    {
        if ($commentId) {
            return $this->getReplies($commentId, $page);
        }
        return $this->getMainComments($imageId, $page);
    }

    /**
     * Lấy các phản hồi của một comment
     * @param int $commentId ID của comment
     * @param int $page Trang hiện tại
     * @return array Danh sách phản hồi
     */
    public function getReplies(int $commentId, int $page): array
    {
        $replies = Comment::with(['user'])
            ->where('parent_id', $commentId)
            ->latest()
            ->paginate(self::REPLIES_PER_PAGE, ['*'], 'page', $page);
        return [
            'replies' => $replies->items(),
            'hasMore' => $replies->hasMorePages()
        ];
    }

    /**
     * Lấy các comment gốc
     * @param int $imageId ID của ảnh
     * @param int $page Trang hiện tại
     * @return array Danh sách comment gốc
     */
    public function getMainComments(int $imageId, int $page): array
    {
        $comments = Comment::with(['user', 'replies' => function ($query) {
            $query->with('user')
                ->latest()
                ->paginate(self::REPLIES_PER_PAGE);
        }])
        ->where('image_id', $imageId)
        ->whereNull('parent_id')
        ->latest()
        ->paginate(self::COMMENTS_PER_PAGE, ['*'], 'page', $page);

        // Sắp xếp lại replies để các phản hồi lồng nhau được hiển thị đúng
        $commentsWithNestedReplies = $comments->items();
        foreach ($commentsWithNestedReplies as $comment) {
            $this->processReplies($comment);
        }

        return [
            'comments' => $commentsWithNestedReplies,
            'hasMore' => $comments->hasMorePages()
        ];
    }

    /**
     * Xử lý các phản hồi lồng nhau
     * @param Comment $comment Comment
     * @return void
     */
    private function processReplies($comment): void
    {
        // Không làm gì nếu không có replies
        if ($comment->replies->isEmpty()) {
            return;
        }

        // Lấy tất cả ID của replies trực tiếp
        $replyIds = $comment->replies->pluck('id')->toArray();

        // Lấy tất cả phản hồi lồng nhau dựa trên parent_id
        $allNestedReplies = $this->getAllNestedReplies($replyIds);

        // Gộp các phản hồi và sắp xếp theo thời gian tạo
        $allReplies = $comment->replies->concat($allNestedReplies)
            ->unique('id') // Loại bỏ các reply trùng lặp
            ->sortBy('created_at'); // Sắp xếp theo thời gian tạo mới nhất

        // Thay thế collection replies bằng collection mới đã gộp và sắp xếp
        $comment->setRelation('replies', $allReplies);
    }

    /**
     * Lấy tất cả các phản hồi lồng nhau
     * @param array $parentIds ID của các phản hồi cha
     * @return \Illuminate\Support\Collection Các phản hồi lồng nhau
     */
    private function getAllNestedReplies(array $parentIds): \Illuminate\Support\Collection
    {
        if (empty($parentIds)) {
            return collect([]);
        }

        // Lấy tất cả phản hồi cho các parent_id
        $replies = Comment::with('user')
            ->whereIn('parent_id', $parentIds)
            ->get();

        // Lấy ID của tất cả các replies đã tìm được
        $replyIds = $replies->pluck('id')->toArray();

        // Nếu có replies, tiếp tục đệ quy để lấy các replies lồng nhau sâu hơn
        if (!empty($replyIds)) {
            // Gọi đệ quy để lấy các replies của replies
            $nestedReplies = $this->getAllNestedReplies($replyIds);

            // Gộp replies hiện tại với các replies lồng nhau
            return $replies->concat($nestedReplies);
        }

        return $replies;
    }

    /**
     * Lưu comment
     * @param array $data Dữ liệu của comment
     * @return Comment Comment
     */
    public function storeComment(array $data): Comment
    {
        $comment = Comment::create([
            'user_id' => Auth::id(),
            'image_id' => $data['image_id'],
            'parent_id' => $data['parent_id'] ?? null,
            'origin_comment' => $data['origin_comment'] ?? null,
            'content' => $data['content'],
            'sum_like' => 0,
            'list_like' => [],
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        $this->incrementImageCommentCount($data['image_id']);
        $comment->load(['user', 'replies.user']);

        return $comment;
    }

    /**
     * Lưu phản hồi
     * @param array $data Dữ liệu của phản hồi
     * @param Comment $comment Comment
     * @return Comment Comment
     */
    public function storeReply(array $data, Comment $comment): Comment
    {
        // Determine the origin comment
        $originCommentId = null;

        // If the comment we're replying to is already a reply (has a parent_id)
        // then we need to find the original root comment
        if ($comment->parent_id !== null) {
            // If the comment already has an origin_comment, use that
            if ($comment->origin_comment !== null) {
                $originCommentId = $comment->origin_comment;
            } else {
                // Otherwise, the parent is the origin
                $originCommentId = $comment->parent_id;
            }
        } else {
            // If we're replying to a root comment, it becomes the origin
            $originCommentId = $comment->id;
        }

        $reply = Comment::create([
            'user_id' => Auth::id(),
            'image_id' => $comment->image_id,
            'parent_id' => $comment->id,
            'origin_comment' => $originCommentId,
            'content' => $data['content'],
            'sum_like' => 0,
            'list_like' => [],
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        $this->incrementImageCommentCount($comment->image_id);

        $reply->load(['user']);

        return $reply;
    }

    /**
     * Cập nhật comment
     * @param Comment $comment Comment
     * @param string $content Nội dung của comment
     * @return Comment Comment
     */
    public function updateComment(Comment $comment, string $content): Comment
    {
        $comment->update(['content' => $content]);
        return $comment;
    }

    /**
     * Xóa comment
     * @param Comment $comment Comment
     * @return void
     */
    public function deleteComment(Comment $comment): void
    {
        if ($comment->parent_id === null) {
            $comment->replies()->delete();
        }

        $this->decrementImageCommentCount($comment->image_id);
        $comment->delete();
    }

    /**
     * Tăng số lượng comment của ảnh
     * @param int $imageId ID của ảnh
     * @return void
     */
    public function incrementImageCommentCount(int $imageId): void
    {
        $image = Image::find($imageId);
        if ($image) {
            $image->increment('sum_comment');
        }
    }

    /**
     * Giảm số lượng comment của ảnh
     * @param int $imageId ID của ảnh
     * @return void
     */
    public function decrementImageCommentCount(int $imageId): void
    {
        $image = Image::find($imageId);
        if ($image) {
            $image->decrement('sum_comment');
        }
    }

    /**
     * Cập nhật comment
     * @param Comment $comment Comment
     * @return Comment Comment
     */
    public function update(Comment $comment)
    {
        $comment->save();
        return $comment;
    }
    
    /**
     * Tìm comment theo ID
     * @param int $commentId ID của comment
     * @return Comment|null Comment
     */
    public function findById(int $commentId): ?Comment
    {
        return Comment::find($commentId);
    }
    
    /**
     * Đếm số lượng comment của một ảnh
     * @param int $imageId ID của ảnh
     * @return int Số lượng comment
     */
    public function countByImageId(int $imageId): int
    {
        return Comment::where('image_id', $imageId)
            ->where('parent_id', null)
            ->count();
    }
    
    /**
     * Đưa comment gốc lên đầu bằng cách touch updated_at
     * @param int $commentId ID của comment
     * @return bool Kết quả
     */
    public function touchComment(int $commentId): bool
    {
        $comment = Comment::find($commentId);
        if ($comment) {
            $comment->touch();
            return true;
        }
        return false;
    }
}

