<?php
namespace Bolo\Bolo\Models;


class ExportOrders extends \Backend\Models\ExportModel
{
    public function exportData($columns, $sessionKey = null)
    {
        $orders = Order::all();
        $orders->each(function($order) use ($columns) {
            $order->addVisible($columns);
        });
        return $orders->toArray();
    }

}