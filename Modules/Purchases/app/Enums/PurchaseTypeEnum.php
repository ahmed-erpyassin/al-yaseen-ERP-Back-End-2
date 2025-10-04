<?php

namespace Modules\Purchases\app\Enums;

interface PurchaseTypeEnum
{
    const QUOTATION = 'quotation';
    const ORDER = 'order';
    const OUTGOING_ORDER = 'order';
    const INCOMING_SHIPMENT = 'shipment';
    const INVOICE = 'invoice';
    const EXPENSE = 'expense';
    const RETURN_INVOICE = 'return_invoice';
}
