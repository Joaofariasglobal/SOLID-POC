<?php

namespace App\Presenters;

use App\Domain\BaseTransaction;

class TransactionPresenter
{
    public function toArray(BaseTransaction $tx): array
    {
        return [
            'id'            => $tx->id,
            'type'          => $tx->getType(),
            'category'      => $tx->category,
            'description'   => $tx->description,
            'amount'        => number_format($tx->amount, 2, '.', ''),
            'signed_amount' => number_format($tx->getSignedAmount(), 2, '.', ''),
            'occurred_at'   => $tx->occurredAt,
            'icon'          => $tx->getIcon(),
            'color'         => $tx->getColor(),
        ];
    }
}