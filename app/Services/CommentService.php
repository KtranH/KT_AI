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
        // Tạo bình luận mới
        $comment = $this->commentRepository->storeComment($request->validated());

        // Đánh dấu là bình luận mới để hiển thị highlight
        $comment->is_new = true;

        // Gửi thông báo khi bình luận mới (chỉ khi bình luận từ người khác)
        if ($comment && $comment->image) {
            $imageOwnerId = $comment->image->user_id;
            $commenterId = Auth::id();

            // Chỉ gửi thông báo nếu người bình luận không phải là chủ ảnh
            if ($imageOwnerId !== $commenterId) {
                $commenter = User::select('id', 'name', 'avatar_url')->find($commenterId);
                $imageOwner = User::find($imageOwnerId);

                if ($commenter instanceof User && $imageOwner instanceof User) {
                    try {
                        // Đảm bảo avatar_url có giá trị
                        if ($commenter->avatar_url === null) {
                            $commenter->avatar_url = "https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png";
                        }

                        // Gửi thông báo cho chủ ảnh
                        $imageOwner->notify(new \App\Notifications\AddCommentNotification($commenter, $comment));

                        \Log::info('Đã gửi thông báo bình luận mới từ ' . $commenter->name . ' đến ' . $imageOwner->name);
                    } catch (\Exception $e) {
                        \Log::error('Lỗi khi gửi thông báo bình luận: ' . $e->getMessage());
                    }
                }
            }
        }

        return $comment;
    }
    public function storeReply(StoreReplyRequest $request, Comment $comment)
    {
        // Tìm parent_id đúng (có thể là comment chính hoặc một phản hồi)
        $parentId = $comment->id;

        // Tạo phản hồi mới
        $reply = $this->commentRepository->storeReply($request->validated(), $comment);

        // Đánh dấu là phản hồi mới để hiển thị highlight
        $reply->is_new = true;

        // Gửi thông báo khi phản hồi (chỉ khi phản hồi từ người khác)
        if ($reply) {
            $commentOwnerId = $comment->user_id;
            $replierId = Auth::id();

            // Chỉ gửi thông báo nếu người phản hồi không phải là chủ bình luận
            if ($commentOwnerId !== $replierId) {
                $replier = User::select('id', 'name', 'avatar_url')->find($replierId);
                $commentOwner = User::find($commentOwnerId);

                if ($replier instanceof User && $commentOwner instanceof User) {
                    try {
                        // Đảm bảo avatar_url có giá trị
                        if ($replier->avatar_url === null) {
                            $replier->avatar_url = "https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png";
                        }

                        // Gửi thông báo cho chủ bình luận
                        $commentOwner->notify(new \App\Notifications\AddReplyNotification($replier, $reply));

                        \Log::info('Đã gửi thông báo phản hồi mới từ ' . $replier->name . ' đến ' . $commentOwner->name);
                    } catch (\Exception $e) {
                        \Log::error('Lỗi khi gửi thông báo phản hồi: ' . $e->getMessage());
                    }
                }
            }

            // Nếu có origin_comment, đưa bình luận gốc lên đầu danh sách
            if ($reply->origin_comment) {
                try {
                    // Tìm bình luận gốc
                    $originComment = Comment::find($reply->origin_comment);
                    if ($originComment) {
                        // Cập nhật thời gian để đưa lên đầu (chỉ cập nhật updated_at, không thay đổi created_at)
                        $originComment->touch();
                        \Log::info('Đã đưa bình luận gốc ID:' . $originComment->id . ' lên đầu danh sách');
                    }
                } catch (\Exception $e) {
                    \Log::error('Lỗi khi đưa bình luận gốc lên đầu: ' . $e->getMessage());
                }
            }
        }

        return $reply;
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
        $wasLiked = in_array($userId, $listLike);

        if ($wasLiked) {
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

                // Nếu đây là một like mới (không phải unlike) và có origin_comment
                if (!$wasLiked && $comment->origin_comment) {
                    try {
                        // Tìm bình luận gốc
                        $originComment = Comment::find($comment->origin_comment);
                        if ($originComment) {
                            // Cập nhật thời gian để đưa lên đầu (chỉ cập nhật updated_at, không thay đổi created_at)
                            $originComment->touch();
                            \Log::info('Đã đưa bình luận gốc ID:' . $originComment->id . ' lên đầu danh sách sau khi like');
                        }
                    } catch (\Exception $e) {
                        \Log::error('Lỗi khi đưa bình luận gốc lên đầu sau khi like: ' . $e->getMessage());
                    }
                }
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