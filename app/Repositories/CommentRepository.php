<?php

namespace App\Repositories;

use App\Interfaces\CommentRepositoryInterface;
use App\Models\Comment;
use App\Models\Image;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CommentRepository implements CommentRepositoryInterface
{
    private const COMMENTS_PER_PAGE = 5;
    private const REPLIES_PER_PAGE = 3;

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
                ->limit(self::REPLIES_PER_PAGE);
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
     * Xử lý phản hồi lồng nhau
     */
    private function processReplies($comment): void
    {
        // Lấy tất cả ID của replies
        $replyIds = $comment->replies->pluck('id')->toArray();
        
        // Nếu không có replies, không cần xử lý
        if (empty($replyIds)) {
            return;
        }
        
        // Lấy danh sách tất cả nested replies (những phản hồi có parent_id là một trong các replies hiện tại)
        $nestedReplies = Comment::with('user')
            ->whereIn('parent_id', $replyIds)
            ->get();
            
        // Nếu không có nested replies, không cần xử lý thêm
        if ($nestedReplies->isEmpty()) {
            return;
        }
        
        // Thêm nested_replies attribute vào mỗi reply
        foreach ($comment->replies as $reply) {
            $reply->nested_replies = $nestedReplies->where('parent_id', $reply->id)->values();
        }
    }
    
    public function storeComment(array $data): Comment
    {
        $comment = Comment::create([
            'user_id' => Auth::id(),
            'image_id' => $data['image_id'],
            'parent_id' => $data['parent_id'] ?? null,
            'content' => $data['content'],
            'sum_like' => 0,
            'list_like' => []
        ]);

        $this->incrementImageCommentCount($data['image_id']);

        $comment->load(['user', 'replies.user']);
        
        return $comment;
    }

    public function storeReply(array $data, Comment $comment): Comment
    {
        $reply = Comment::create([
            'user_id' => Auth::id(),
            'image_id' => $comment->image_id,
            'parent_id' => $comment->id,
            'content' => $data['content'],
            'sum_like' => 0,
            'list_like' => []
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
    
    public function toggleLike(Comment $comment): array
    {
        $userId = Auth::id();
        $listLike = $comment->list_like ?? [];
        
        if (in_array($userId, $listLike)) {
            $listLike = array_diff($listLike, [$userId]);
            $comment->sum_like = max(0, $comment->sum_like - 1);
        } else {
            $listLike[] = $userId;
            $comment->sum_like += 1;
        }
        
        $comment->list_like = $listLike;
        $comment->save();
        
        return [
            'likes' => $comment->sum_like,
            'isLiked' => in_array($userId, $listLike)
        ];
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
}
