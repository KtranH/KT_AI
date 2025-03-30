<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Image;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class CommentService
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
    
    private function getReplies(int $commentId, int $page): array
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
    
    private function getMainComments(int $imageId, int $page): array
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
        
        return [
            'comments' => $comments->items(),
            'hasMore' => $comments->hasMorePages()
        ];
    }
    
    public function storeComment(array $data): Comment
    {
        $comment = Comment::create([
            'user_id' => Auth::id(),
            'image_id' => $data['image_id'],
            'parent_id' => $data['parent_id'] ?? null,
            'content' => $data['content'],
            'sum_like' => 0,
            'list_like' => json_encode([])
        ]);

        $this->incrementImageCommentCount($data['image_id']);

        $comment->load(['user', 'replies.user']);
        
        return $comment;
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
        $listLike = json_decode($comment->list_like ?? '[]', true);
        
        if (in_array($userId, $listLike)) {
            $listLike = array_diff($listLike, [$userId]);
            $comment->sum_like = max(0, $comment->sum_like - 1);
        } else {
            $listLike[] = $userId;
            $comment->sum_like += 1;
        }
        
        $comment->list_like = json_encode($listLike);
        $comment->save();
        
        return [
            'likes' => $comment->sum_like,
            'isLiked' => in_array($userId, $listLike)
        ];
    }
    
    private function incrementImageCommentCount(int $imageId): void
    {
        $image = Image::find($imageId);
        if ($image) {
            $image->increment('sum_comment');
        }
    }
    
    private function decrementImageCommentCount(int $imageId): void
    {
        $image = Image::find($imageId);
        if ($image) {
            $image->decrement('sum_comment');
        }
    }
} 