<?php

use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/product', \App\Mcp\Servers\ProductServer::class);
