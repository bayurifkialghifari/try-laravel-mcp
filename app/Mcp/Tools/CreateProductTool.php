<?php

namespace App\Mcp\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class CreateProductTool extends Tool
{

    /**
     * The tool's name.
     */
    protected string $name = 'create-product';

    /**
     * The tool's title.
     */
    protected string $title = 'Create Product';
    /**
     * The tool's description.
     */
    protected string $description = 'A tool to create a new product in the products table.';

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'nullable|integer',
            'category' => 'nullable|string',
            'sku' => 'required|string',
            'image' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            Product::create($validated);
        } catch (\Exception $e) {
            return Response::text('Error creating product: ' . $e->getMessage());
        }

        return Response::text('Product created successfully.');
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\Contracts\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('The name of the product to create.'),
            'description' => $schema->string()->description('The description of the product to create.')->nullable(),
            'price' => $schema->number()->description('The price of the product to create.'),
            'stock' => $schema->integer()->description('The stock quantity of the product to create.')->default(0),
            'category' => $schema->string()->description('The category of the product to create.')->nullable(),
            'sku' => $schema->string()->description('The unique SKU of the product to create.'),
            'image' => $schema->string()->description('The image URL/path of the product to create.')->nullable(),
            'is_active' => $schema->boolean()->description('The active status of the product to create.')->default(true),
        ];
    }
}
