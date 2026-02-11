<?php

namespace Codeplugtech\CreemPayments\Enum;

enum SubscriptionStatusEnum: string
{
    case ACTIVE = 'active';
    case CANCELED = 'canceled';
    case UNPAID = 'unpaid';
    case PAUSED = 'paused';
    case TRIALING = 'trialing';
    case SCHEDULED_CANCEL = 'scheduled_cancel';
}
