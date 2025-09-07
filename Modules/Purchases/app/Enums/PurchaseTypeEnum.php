<?php

namespace Modules\Purchases\app\Enums;

interface PurchaseTypeEnum
{
    const QUOTATION = 'quotation';
    const OUTGOING_ORDER = 'outgoing_order';
    const INCOMING_SHIPMENT = 'incoming_shipment';
    const INVOICE = 'invoice';
    const SERVICE = 'service';
    const RETURN_INVOICE = 'return_invoice';
}
