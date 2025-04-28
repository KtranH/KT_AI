<?php

namespace App\Services;
use App\Interfaces\CommentRepositoryInterface;
use App\Notifications\LikeCommentNotification;
use App\Models\Comment;
use App\Models\User;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Reply\StoreReplyRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
    public function storeComment(StoreCommentRequest $request)
    {
        return $this->commentRepository->storeComment($request->validated());
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
        
        // Kiểm tra thông tin liker
        $liker = User::find($userId);
        if($liker instanceof User)
        {
            if($comment->user_id !== $userId) {
                $this->sendNotification($comment, $liker);
            }
        }

        $comment->list_like = $listLike;
        $comment->save();
        
        return [
            'likes' => $comment->sum_like,
            'isLiked' => in_array($userId, $listLike)
        ];
    }
    public function sendNotification(Comment $comment, User $liker)
    {
        try
        {
            // Lấy thông tin người sở hữu bình luận
            $commentOwner = User::find($comment->user_id);
            if (!$commentOwner instanceof User) {
                return;
            }

            // Đảm bảo tải đầy đủ thông tin người dùng
            $fullLikerInfo = User::select('id', 'name', 'avatar_url')->find($liker->id);
            if (!$fullLikerInfo instanceof User) {
                return;
            }

            // Nếu avatar_url là null, gán giá trị mặc định
            if ($fullLikerInfo->avatar_url === null) {
                $fullLikerInfo->avatar_url = "https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png";
            }

            // Gửi thông báo tới chủ sở hữu bình luận
            $commentOwner->notify(new LikeCommentNotification($fullLikerInfo, $comment));
        } catch (\Exception $exception) {
            \Log::error('Lỗi khi gửi thông báo: ' . $exception->getMessage());
        }
    }
}