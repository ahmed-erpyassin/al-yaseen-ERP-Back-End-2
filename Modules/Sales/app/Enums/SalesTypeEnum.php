<?php

namespace Modules\Sales\app\Enums;

class SalesTypeEnum
{
    const QUOTATION = 'quotation';
    const INCOMING_ORDER = 'incoming_order';
    const OUTGOING_SHIPMENT = 'outgoing_shipment';
    const INVOICE = 'invoice';
    const SERVICE = 'service';
    const RETURN_INVOICE = 'return_invoice';
}
