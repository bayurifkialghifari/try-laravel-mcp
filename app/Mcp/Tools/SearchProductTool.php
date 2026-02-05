<?php

namespace App\Mcp\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class SearchProductTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'search-product';

    /**
     * The tool's title.
     */
    protected string $title = 'Search Product';

    /**
     * The tool's description.
     */
    protected string $description = 'A tool to search for products in the products table.';

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'search' => 'required|string',
            'field' => 'nullable|string',
            'action' => 'required|string|in:get,count',
        ]);

        $list = Product::query()
            ->when($validated['field'] ?? null, function ($query, $field) use ($validated) {
                $query->whereLike($field, '%' . $validated['search'] . '%');
            }, function ($query) use ($validated) {
                $query->where(function ($q) use ($validated) {
                    $q->whereLike('name', '%' . $validated['search'] . '%')
                        ->orwhereLike('description', '%' . $validated['search'] . '%')
                        ->orwhereLike('sku', '%' . $validated['search'] . '%')
                        ->orwhereLike('category', '%' . $validated['search'] . '%');
                });
            })->{$validated['action']}();

        if ($validated['action'] === 'count') {
            return Response::text("Total products found: {$list}");
        }

        if ($list->isEmpty()) {
            return Response::text('No products found matching your criteria. Please try different keywords or check for typos.');
        }

        $tableHeader = "| ID | SKU | Name | Category | Price | Stock | Status |\n";
        $tableHeader .= "|----|-----|------|----------|-------|-------|--------|\n";
        $tableRows = '';

        foreach ($list as $product) {
            $status = $product->is_active ? 'Active' : '*Inactive*';
            if ($product->stock == 0) {
                $status .= ' / *Out of Stock*';
            }
            $tableRows .= "| {$product->id} | {$product->sku} | {$product->name} | {$product->category} | $" . number_format($product->price, 2) . " | {$product->stock} | {$status} |\n";
        }

        $markdownTable = $tableHeader . $tableRows;
        return Response::text($markdownTable);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\Contracts\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('The keyword or value to search for.'),
            'field' => $schema->string()
                ->enum(['name', 'description', 'sku', 'category'])
                ->description('Optional: Search only in a specific field. Leave empty to search all fields.')
                ->nullable(), // Izinkan null agar AI tidak terbebani memilih field jika tidak yakin
            'action' => $schema->string()
                ->enum(['get', 'count'])
                ->description('The action to perform.')
                ->default('get'),
        ];
    }
}
