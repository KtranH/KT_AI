<?php

namespace App\Services;
use App\Interfaces\CommentRepositoryInterface;
use App\Models\Comment;
use App\Http\Requests\Reply\StoreReplyRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentService
{
    protected CommentRepositoryInterface $commentRepository;
    public function __construct(CommentRepositoryInterface $commentRepository)
    {
        $this->commentRepository = $commentRepository;
    }
    public function getComments(int $imageId, Request $request)
    {
        $page = $request->input('page', 1);
        $commentId = $request->input('comment_id');
        return $this->commentRepository->getComments($imageId, $commentId, $page);
    }
    public function storeReply(StoreReplyRequest $request, Comment $comment)
    {
        // Tìm parent_id đúng (có thể là comment chính hoặc một phản hồi)
        $parentId = $comment->id;
        // Tạo phản hồi mới
        return $this->commentRepository->storeReply($request->validated(), $comment);
    }
    public function destroy(Comment $comment)
    {
        if (Gate::denies('delete', $comment)) {
            return false;
        }
        $this->commentRepository->deleteComment($comment);
        return true;
    }
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        if (Gate::denies('update', $comment)) {
            return response()->json(['message' => 'Không được phép cập nhật bình luận này'], 403);
        }
        return $this->commentRepository->updateComment($comment, $request->input('content'));
    }
    public function toggleLike(Comment $comment)
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
}