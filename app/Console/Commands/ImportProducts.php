<?php

namespace App\Console\Commands;

use App\Category;
use App\Product;
use App\ProductVariation;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tortuga:import:products {--clean}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from /storage/app/products.json';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // load file
        try {
            $products = json_decode(file_get_contents(storage_path('app/products.json')), true);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }

        // first, possibly delete everything
        if ($this->option('clean')) {
            ProductVariation::whereNotNull('id')->delete();
            Product::whereNotNull('id')->delete();
            Category::whereNotNull('id')->delete();
        }

        $categorySequence = 0;
        foreach ($products['categories'] as $category) {
            $slug             = Str::slug($category['title']);
            $categorySequence += 100;

            $categoryRecord = Category::firstOrCreate(
                ['slug' => $slug],
                ['title' => $category['title'], 'sequence' => $categorySequence, 'emoji' => '']
            );

            $this->info('Category: ' . $categoryRecord->title);

            $productSequence = 0;
            foreach ($category['products'] as $product) {
                $productSequence += 100;
                $productRecord   = Product::firstOrCreate(['slug' => Str::slug($product['title'])], [
                    'title'       => $product['title'],
                    'sequence'    => $productSequence,
                    'heat'        => 0,
                    'description' => $product['description'] ?: null,
                    'category_id' => $categoryRecord->id,
                ]);

                $this->info('Product: ' . $productRecord->title);

                $productVariationSequence = 0;
                foreach ($product['variations'] as $variation) {
                    $productVariationSequence += 100;
                    $productVariationRecord   = ProductVariation::create([
                        'product_id'  => $productRecord->id,
                        'active'      => 1,
                        'sequence'    => $productVariationSequence,
                        'title'       => $variation['title'],
                        'slug'        => Str::slug($variation['title']),
                        'description' => $variation['description'] ?: null,
                        'price'       => $variation['price'],
                        'currency'    => 'CZK',
                    ]);

                    $this->info('Product Variation: ' . $productVariationRecord->title . ' (' .
                        $productVariationRecord->price . ')');
                }

                $this->info('Product ' . $productRecord->title . ' has ' . ($productVariationSequence / 100) .
                    ' variations.');
            }

            $this->info('Category: ' . $categoryRecord->title . ' has ' . ($productSequence / 100) . ' products.');
        }
    }
}
