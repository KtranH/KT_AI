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
    public function getCommentByID(int $commentId): Comment
    {
        return Comment::with(['user', 'replies.user'])->findOrFail($commentId);
    }
    public function getComments(int $imageId, ?int $commentId = null, int $page = 1): array
    {
        if ($commentId) {
            return $this->getReplies($commentId, $page);
        }
        return $this->getMainComments($imageId, $page);
    }
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
    public function updateComment(Comment $comment, string $content): Comment
    {
        $comment->update(['content' => $content]);
        return $comment;
    }
    public function deleteComment(Comment $comment): void
    {
        if ($comment->parent_id === null) {
            $comment->replies()->delete();
        }

        $this->decrementImageCommentCount($comment->image_id);
        $comment->delete();
    }
    public function incrementImageCommentCount(int $imageId): void
    {
        $image = Image::find($imageId);
        if ($image) {
            $image->increment('sum_comment');
        }
    }
    public function decrementImageCommentCount(int $imageId): void
    {
        $image = Image::find($imageId);
        if ($image) {
            $image->decrement('sum_comment');
        }
    }
    public function update(Comment $comment)
    {
        $comment->save();
        return $comment;
    }
    
    /**
     * Tìm comment theo ID
     */
    public function findById(int $commentId): ?Comment
    {
        return Comment::find($commentId);
    }
    
    /**
     * Đếm số lượng comment của một ảnh
     */
    public function countByImageId(int $imageId): int
    {
        return Comment::where('image_id', $imageId)
            ->where('parent_id', null)
            ->count();
    }
    
    /**
     * Đưa comment gốc lên đầu bằng cách touch updated_at
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

