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
            'field' => 'nullable|string',
            'action' => 'required|string|in:get,count',
            'search' => 'required_if:action,get|string',
        ]);

        $list = Product::query()
            ->when($validated['field'] ?? null && $validated['search'] ?? null, function ($query, $field) use ($validated) {
                $query->whereLike($field, '%' . $validated['search'] . '%');
            })
            ->when($validated['search'] ?? null, function ($query) use ($validated) {
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

        $tableRows = '';

        foreach ($list as $product) {
            $tableRows .= "{$product->id}, {$product->name}, {$product->description}, {$product->price}, {$product->stock}, {$product->category}, {$product->sku}, {$product->image}, " . ($product->is_active ? 'Active' : 'Inactive') . "\n";
        }

        return Response::text($tableRows);
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
