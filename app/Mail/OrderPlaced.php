<?php

namespace App\Mail;

use App\Models\Order; // Đảm bảo bạn có model Order
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlaced extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Biến public này sẽ tự động được truyền vào view email.
     */
    public $order;

    /**
     * Tạo một instance Mailable mới.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Lấy "phong bì" (Tiêu đề, Người gửi)
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Xác Nhận Đơn Hàng #' . $this->order->id,
        );
    }

    /**
     * Lấy nội dung (File Blade nào sẽ là template)
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.placed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
