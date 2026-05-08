<x-filament-panels::page>
    <style>
        .chat-page {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 24px;
            align-items: start;
        }

        .chat-card {
            background: #ffffff;
            border: 1px solid #d7dde5;
            border-radius: 22px;
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .chat-card-header {
            padding: 18px 22px;
            border-bottom: 1px solid #d7dde5;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .chat-card-title {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        .chat-card-subtitle {
            margin-top: 4px;
            font-size: 13px;
            color: #475569;
        }

        .chat-card-body {
            padding: 22px;
        }

        .messages-panel {
            min-height: 520px;
            background: #f8fafc;
        }

        .messages-scroll {
            max-height: 620px;
            overflow-y: auto;
            padding: 22px;
            background:
                radial-gradient(circle at top left, rgba(0, 77, 128, 0.08), transparent 28%),
                linear-gradient(180deg, #f8fafc 0%, #eef3f7 100%);
        }

        .message-row {
            display: flex;
            margin-bottom: 18px;
        }

        .message-row.admin {
            justify-content: flex-end;
        }

        .message-row.customer {
            justify-content: flex-start;
        }

        .message-bubble {
            width: fit-content;
            max-width: min(680px, 82%);
            border-radius: 20px;
            padding: 14px 16px;
            border: 1px solid #d7dde5;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
        }

        .message-row.admin .message-bubble {
            background: #e8f4fb;
            border-color: #b9dced;
            border-bottom-right-radius: 6px;
        }

        .message-row.customer .message-bubble {
            background: #ffffff;
            border-color: #d7dde5;
            border-bottom-left-radius: 6px;
        }

        .message-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 8px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.25);
            padding-bottom: 8px;
        }

        .message-name {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
        }

        .message-time {
            font-size: 11px;
            color: #64748b;
            white-space: nowrap;
        }

        .message-text {
            font-size: 14px;
            line-height: 1.8;
            color: #1e293b;
            white-space: pre-line;
        }

        .message-status {
            margin-top: 10px;
            font-size: 11px;
            font-weight: 700;
        }

        .status-seen {
            color: #16a34a;
        }

        .status-unread {
            color: #d97706;
        }

        .chat-attachment {
            max-width: 320px;
            max-height: 260px;
            object-fit: cover;
            border-radius: 16px;
            border: 1px solid #cbd5e1;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
            cursor: pointer;
        }

        .reply-panel {
            border-top: 1px solid #d7dde5;
            background: #ffffff;
        }

        .send-button-wrap {
            margin-top: 16px;
            display: flex;
            justify-content: flex-end;
        }

        @media (max-width: 1100px) {
            .chat-page {
                grid-template-columns: 1fr;
            }

            .message-bubble {
                max-width: 92%;
            }
        }
    </style>

    <div class="chat-page">
        <div class="chat-card">
            <div class="chat-card-header">
                <div class="chat-card-title">Conversation Details</div>
                <div class="chat-card-subtitle">Customer, status, assigned admin and last activity.</div>
            </div>

            <div class="chat-card-body">
                {{ $this->form }}
            </div>
        </div>

        <div class="chat-card messages-panel">
            <div class="chat-card-header">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <div class="chat-card-title">Messages</div>
                        <div class="chat-card-subtitle">
                            Full conversation history with customer.
                        </div>
                    </div>

                    <span style="background:#004D80;color:white;border-radius:999px;padding:6px 12px;font-size:12px;font-weight:800;">
                        {{ $this->record->messages()->count() }} messages
                    </span>
                </div>
            </div>

            <div class="messages-scroll">
                @forelse($this->record->messages()->with('sender')->oldest()->get() as $message)
                    @php
                        $isAdmin = $message->sender_id === auth()->id();
                        $attachmentUrl = $message->attachment ? asset('storage/' . $message->attachment) : null;
                    @endphp

                    <div class="message-row {{ $isAdmin ? 'admin' : 'customer' }}">
                        <div class="message-bubble">
                            <div class="message-meta">
                                <div class="message-name">
                                    {{ $message->sender?->name ?? 'Unknown' }}
                                </div>

                                <div class="message-time">
                                    {{ $message->created_at?->format('Y-m-d h:i A') }}
                                </div>
                            </div>

                            @if(!blank($message->message))
                                <div class="message-text">
                                    {{ $message->message }}
                                </div>
                            @endif

                            @if($attachmentUrl)
                                <div class="mt-3">
                                    <img
                                        src="{{ $attachmentUrl }}"
                                        alt="Chat attachment"
                                        class="chat-attachment"
                                        onclick="openChatImageModal('{{ $attachmentUrl }}')"
                                    >

                                    <div class="mt-2 text-xs" style="color:#004D80;font-weight:700;">
                                        Click image to preview
                                    </div>
                                </div>
                            @endif

                            @if(blank($message->message) && ! $attachmentUrl)
                                <div class="message-text" style="color:#94a3b8;font-style:italic;">
                                    Empty message
                                </div>
                            @endif

                            <div class="message-status">
                                @if($message->is_seen)
                                    <span class="status-seen">Seen</span>
                                @else
                                    <span class="status-unread">Unread</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="color:#64748b;background:white;border:1px dashed #cbd5e1;border-radius:18px;padding:24px;text-align:center;">
                        No messages yet.
                    </div>
                @endforelse
            </div>

            <div class="chat-card-body reply-panel">
                <div class="chat-card-title" style="margin-bottom:14px;">Send Reply</div>

                {{ $this->replyForm }}

                <div class="send-button-wrap">
                    <x-filament::button wire:click="sendReply">
                        Send Reply
                    </x-filament::button>
                </div>
            </div>
        </div>
    </div>

    <div
        id="chatImageModal"
        onclick="closeChatImageModal()"
        style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center; padding:24px;"
    >
        <div onclick="event.stopPropagation()" style="position:relative; max-width:92vw; max-height:90vh;">
            <button
                type="button"
                onclick="closeChatImageModal()"
                style="position:absolute; top:-46px; right:0; background:white; color:#111827; border:none; width:36px; height:36px; border-radius:999px; cursor:pointer; font-size:20px; font-weight:bold; line-height:36px;"
                aria-label="Close image preview"
            >
                ×
            </button>

            <img
                id="chatModalImage"
                src=""
                alt="Full chat attachment"
                style="max-width:92vw; max-height:90vh; border-radius:14px; object-fit:contain; box-shadow:0 20px 60px rgba(0,0,0,0.45);"
            >
        </div>
    </div>

    <script>
        function openChatImageModal(src) {
            const modal = document.getElementById('chatImageModal');
            const image = document.getElementById('chatModalImage');

            image.src = src;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeChatImageModal() {
            const modal = document.getElementById('chatImageModal');
            const image = document.getElementById('chatModalImage');

            modal.style.display = 'none';
            image.src = '';
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeChatImageModal();
            }
        });
    </script>
</x-filament-panels::page>