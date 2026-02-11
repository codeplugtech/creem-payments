<?php

namespace Codeplugtech\CreemPayments;

use Codeplugtech\CreemPayments\Concerns\ManageSubscriptions;
use Codeplugtech\CreemPayments\Concerns\ManageTransactions;

trait Billable
{
    use ManageSubscriptions;
    use ManageTransactions;
}
