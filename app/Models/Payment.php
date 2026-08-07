<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Enums\PaymentStatusEnum;

class Payment extends Model
{
    use SoftDeletes;

    protected $table = "payment";

    protected $fillable = [
        'order_id',
        'payment_status',
        'paid_at'
    ];

    protected $casts = [
        'payment_status' => PaymentStatusEnum::class,
        'reserved_at' => 'datetime'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function markStatus(PaymentStatusEnum $status, mixed $now): void
    {
        $this->payment_status = $status;

        if ($status === PaymentStatusEnum::PAID) {
            $this->paid_at = $now;
        }

        $this->save();
    }

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatusEnum::PAID;
    }

    public function isFailed(): bool
    {
        return $this->payment_status === PaymentStatusEnum::FAILED;
    }

    public function compensate(): bool
    {
        try {
            $this->order->cancel();
            return $this->refund();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    protected function refund(): mixed
    {
        if ($this->isPaid()) {
            $this->payment_status = PaymentStatusEnum::REFUNDED;
            $this->save();
            return true;
        }

        return false;
    }
}
