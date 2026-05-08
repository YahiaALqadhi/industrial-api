<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Str;

class AutoReplyService
{
    public function sendInitialReplies(Conversation $conversation): void
    {
        $systemSender = $this->getSystemSender();

        if (! $systemSender) {
            return;
        }

        if ($conversation->messages()->count() > 1) {
            return;
        }

        // 🧠 آخر رسالة من المستخدم
        $userMessage = $conversation->messages()->latest()->first()?->message ?? '';

        // ✨ رسالة ترحيب
        $this->sendMessage(
            $conversation,
            $systemSender->id,
            $this->getWelcomeMessage()
        );

        // 🧠 تحليل الرسالة
        $replies = $this->generateSmartReplies($userMessage);

        foreach ($replies as $reply) {
            $this->sendMessage($conversation, $systemSender->id, $reply);
        }

        $conversation->update([
            'admin_id' => $conversation->admin_id ?: $systemSender->id,
            'last_message_at' => now(),
            'status' => 'pending',
        ]);
    }

    private function getSystemSender(): ?User
    {
        return User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['super_admin', 'admin']);
        })->orderBy('id')->first();
    }

    private function getWelcomeMessage(): string
    {
        return Setting::getValue(
            'auto_reply_message',
            '👋 Welcome to NINGBO PASAFEITE support. Our team will assist you shortly.'
        );
    }

    private function generateSmartReplies(string $message): array
    {
        $message = Str::lower($message);

        $replies = [];

        // 🔍 keywords detection
        if (Str::contains($message, ['price', 'cost'])) {
            $replies[] = '💰 Pricing depends on the product type and specifications. Please tell us which product you are interested in.';
        }

        if (Str::contains($message, ['order'])) {
            $replies[] = '📦 You can track your order from the Orders section in your account.';
        }

        if (Str::contains($message, ['shipping', 'delivery'])) {
            $replies[] = '🚚 Shipping time varies depending on your location. Our team will confirm exact delivery time.';
        }

        if (Str::contains($message, ['product'])) {
            $replies[] = '🏭 We offer a wide range of industrial solutions. Please specify the product for better assistance.';
        }

        if (Str::contains($message, ['payment'])) {
            $replies[] = '💳 We support multiple payment methods. Our team will guide you through the available options.';
        }

        if (Str::contains($message, ['address'])) {
            $replies[] = '📍 You can manage your shipping addresses from your account settings.';
        }

        // 🧠 إذا لم يتم فهم شيء
        if (empty($replies)) {
            $replies[] = '🤖 Thank you for your message. Our support team will review your request and respond shortly.';
        }

        return $replies;
    }

    private function sendMessage(Conversation $conversation, int $senderId, string $text): void
    {
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'message' => $text,
            'is_seen' => false,
        ]);
    }
}