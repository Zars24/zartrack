<?php

namespace App\Notifications;

use App\Models\Container;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ShipmentStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public Container $container)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Update pengiriman '.$this->container->container_number,
            'message' => 'Status sekarang: '.$this->container->status_label,
            'url' => $notifiable->role === 'customer'
                ? route('customer.orders.show', $this->container->order_id)
                : route('admin.tracking.show', $this->container->id),
        ];
    }
}
