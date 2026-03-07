<?php

namespace App\Repositories;

use App\Models\Order;

class OrderRepository
{
    public function findWithItems(int $id): ?Order
    {
        return Order::with('items.product', 'statusHistories')->find($id);
    }

    public function paginateForUser(int $userId, int $perPage = 10)
    {
        return Order::where('user_id', $userId)->withCount('items')->latest()->paginate($perPage);
    }

    public function updateStatus(Order $order, string $toStatus, ?int $by = null, ?string $note = null): Order
    {
        $from = $order->status;
        $order->status = $toStatus;
        $order->save();

        $order->statusHistories()->create([
            'from_status' => $from,
            'to_status' => $toStatus,
            'changed_by' => $by,
            'note' => $note,
        ]);

        return $order->refresh();
    }
}
