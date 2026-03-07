<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Repositories\OrderRepository;
use Carbon\Carbon;

class OrderService
{
    public function __construct(protected OrderRepository $repo)
    {
    }

    public function markPaid(Order $order, ?int $byUserId = null): Order
    {
        $order->payment_status = PaymentStatus::PAID->value;
        $order->paid_at = Carbon::now();
        $order->status = OrderStatus::PAID->value;
        $order->save();

        // create history entry
        $order->statusHistories()->create([
            'from_status' => null,
            'to_status' => OrderStatus::PAID->value,
            'changed_by' => $byUserId,
            'note' => 'Payment received',
        ]);

        return $order->refresh();
    }

    public function updateStatus(Order $order, OrderStatus $toStatus, ?int $byUserId = null, ?string $note = null): Order
    {
        return $this->repo->updateStatus($order, $toStatus->value, $byUserId, $note);
    }
}
