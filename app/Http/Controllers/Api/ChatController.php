<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewChatMessageNotification;
use App\Services\AutoReplyService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $conversations = Conversation::with(['latestMessage', 'admin'])
            ->withCount([
                'messages as unread_count' => function ($query) use ($userId) {
                    $query->where('sender_id', '!=', $userId)
                        ->where('is_seen', false);
                }
            ])
            ->where('user_id', $userId)
            ->latest('last_message_at')
            ->latest('id')
            ->get()
            ->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'subject' => $conversation->subject,
                    'status' => $conversation->status,
                    'admin' => $conversation->admin ? [
                        'id' => $conversation->admin->id,
                        'name' => $conversation->admin->name,
                        'email' => $conversation->admin->email,
                        'avatar' => $conversation->admin->avatar ? asset('storage/' . $conversation->admin->avatar) : null,
                    ] : null,
                    'last_message_at' => $conversation->last_message_at,
                    'has_unread' => $conversation->unread_count > 0,
                    'unread_count' => (int) $conversation->unread_count,
                    'latest_message' => $conversation->latestMessage ? [
                        'id' => $conversation->latestMessage->id,
                        'message' => $conversation->latestMessage->message,
                        'sender_id' => $conversation->latestMessage->sender_id,
                        'created_at' => $conversation->latestMessage->created_at,
                    ] : null,
                ];
            });

        return response()->json($conversations);
    }

    public function show(Request $request, Conversation $conversation)
    {
        if ($conversation->user_id !== $request->user()->id) {
            abort(403);
        }

        $conversation->messages()
            ->where('sender_id', '!=', $request->user()->id)
            ->where('is_seen', false)
            ->update([
                'is_seen' => true,
                'seen_at' => now(),
            ]);

        $conversation->load(['messages.sender', 'admin']);

        return response()->json([
            'id' => $conversation->id,
            'subject' => $conversation->subject,
            'status' => $conversation->status,
            'admin' => $conversation->admin ? [
                'id' => $conversation->admin->id,
                'name' => $conversation->admin->name,
                'email' => $conversation->admin->email,
                'avatar' => $conversation->admin->avatar ? asset('storage/' . $conversation->admin->avatar) : null,
            ] : null,
            'messages' => $conversation->messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender?->name,
                    'sender_avatar' => $message->sender?->avatar ? asset('storage/' . $message->sender->avatar) : null,
                    'message' => $message->message,
                    'attachment' => $message->attachment ? asset('storage/' . $message->attachment) : null,
                    'is_seen' => (bool) $message->is_seen,
                    'seen_at' => $message->seen_at,
                    'created_at' => $message->created_at,
                ];
            }),
        ]);
    }

    public function store(Request $request, AutoReplyService $autoReplyService)
    {
        $data = $request->validate([
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $user = $request->user();

        $conversation = Conversation::firstOrCreate(
            [
                'user_id' => $user->id,
                'status' => 'open',
            ],
            [
                'subject' => $data['subject'] ?? 'Customer Support',
                'last_message_at' => now(),
            ]
        );

        if (blank($conversation->subject) && ! empty($data['subject'])) {
            $conversation->update([
                'subject' => $data['subject'],
            ]);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $data['message'],
            'is_seen' => false,
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'status' => 'open',
        ]);

        if ($conversation->messages()->count() === 1) {
            $autoReplyService->sendInitialReplies($conversation);
        }

        User::adminRecipients()->each(function ($admin) use ($conversation, $message) {
            $admin->notify(new NewChatMessageNotification($conversation, $message));
        });

        return response()->json([
            'message' => 'Message sent successfully.',
            'conversation_id' => $conversation->id,
            'chat_message_id' => $message->id,
        ], 201);
    }

    public function reply(Request $request, Conversation $conversation)
    {
        if ($conversation->user_id !== $request->user()->id) {
            abort(403);
        }

        $data = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $request->user()->id,
            'message' => $data['message'],
            'is_seen' => false,
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'status' => 'open',
        ]);

        User::adminRecipients()->each(function ($admin) use ($conversation, $message) {
            $admin->notify(new NewChatMessageNotification($conversation, $message));
        });

        return response()->json([
            'message' => 'Reply sent successfully.',
            'conversation_id' => $conversation->id,
            'chat_message_id' => $message->id,
        ], 201);
    }
    // حذف محادثة
public function destroy(Request $request, Conversation $conversation)
{
    if ($conversation->user_id !== $request->user()->id) {
        abort(403);
    }

    $conversation->delete();

    return response()->json([
        'message' => 'Conversation deleted successfully.',
    ]);
}


// تعديل اسم المحادثة
public function update(Request $request, Conversation $conversation)
{
    if ($conversation->user_id !== $request->user()->id) {
        abort(403);
    }

    $data = $request->validate([
        'subject' => ['required', 'string', 'max:255'],
    ]);

    $conversation->update([
        'subject' => $data['subject'],
    ]);

    return response()->json([
        'message' => 'Conversation updated successfully.',
        'subject' => $conversation->subject,
    ]);
}


public function sendAttachment(Request $request, Conversation $conversation)
{
    if ($conversation->user_id !== $request->user()->id) {
        abort(403);
    }

    $data = $request->validate([
        'attachment' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        'message' => ['nullable', 'string'],
    ]);

    $path = $request->file('attachment')->store('chat', 'public');

    $message = Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $request->user()->id,
        'message' => $data['message'] ?? '',
        'attachment' => $path,
        'is_seen' => false,
    ]);

    $conversation->update([
        'last_message_at' => now(),
        'status' => 'open',
    ]);

    User::adminRecipients()->each(function ($admin) use ($conversation, $message) {
        $admin->notify(new NewChatMessageNotification($conversation, $message));
    });

    return response()->json([
        'message' => 'Image sent successfully.',
        'data' => [
            'id' => $message->id,
            'attachment' => asset('storage/' . $path),
        ]
    ]);
}
}