<?php

return [
    'roles' => [
        'super_admin' => 'Super Admin',
        'ops_admin' => 'Ops Admin',
        'doc_admin' => 'Document Admin',
        'finance' => 'Finance',
        'driver' => 'Driver',
        'customer' => 'Customer',
    ],
    'admin_roles' => ['super_admin', 'ops_admin', 'doc_admin', 'finance'],

    // Forward-only status machine + operational side states.
    'transitions' => [
        'pending' => ['confirmed', 'cancelled', 'on_hold'],
        'confirmed' => ['assigned', 'cancelled', 'on_hold'],
        'assigned' => ['pickup', 'cancelled', 'on_hold'],
        'pickup' => ['in_transit', 'exception', 'on_hold'],
        'in_transit' => ['delivered', 'exception', 'on_hold', 'returning_empty'],
        'delivered' => ['completed'],
        'completed' => [],
        'cancelled' => [],
        'on_hold' => ['pending', 'confirmed', 'assigned', 'cancelled'],
        'returning_empty' => ['completed', 'exception'],
        'exception' => ['in_transit', 'returning_empty', 'cancelled', 'on_hold'],
    ],

    'status_labels' => [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'assigned' => 'Assigned',
        'pickup' => 'Pickup',
        'in_transit' => 'In Transit',
        'delivered' => 'Delivered',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'on_hold' => 'On Hold',
        'returning_empty' => 'Returning Empty',
        'exception' => 'Exception',
    ],

    'service_types' => [
        'trucking' => 'Container Trucking',
        'freight' => 'Freight Forwarding',
        'warehousing' => 'Warehousing',
        'logistics' => 'Logistics',
    ],

    'container_sizes' => ['20ft', '40ft', '40hc', '45hc'],

    'document_types' => [
        'do' => 'Delivery Order',
        'surat_jalan' => 'Surat Jalan',
        'pod' => 'Proof of Delivery',
        'invoice' => 'Invoice',
        'other' => 'Other',
    ],
];
