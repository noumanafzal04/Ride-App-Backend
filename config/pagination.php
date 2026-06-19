<?php

return [
    // Default number of records per page when no ?limit= is supplied.
    // (Was previously unset → resolveLimit() fell back to 1, so every
    //  paginated list returned only a single row.)
    'default' => 20,

    // Hard ceiling a client can request via ?limit=.
    'max' => 100,
];
