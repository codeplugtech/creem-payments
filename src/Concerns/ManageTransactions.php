<?php

namespace Codeplugtech\CreemPayments\Concerns;

use Codeplugtech\CreemPayments\CreemPayments;
use Codeplugtech\CreemPayments\Transaction;
use Codeplugtech\DodoPayments\DodoPayments;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait ManageTransactions
{
    /**
     * Get all of the transactions for the model.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(CreemPayments::$transactionModel, $this->getForeignKey())->orderBy('billed_at', 'desc');
    }
}
