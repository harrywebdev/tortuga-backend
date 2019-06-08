<?php

use App\Category;
use App\Product;
use Illuminate\Database\Seeder;

class CategoriesAndProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories       = ['Hlavni jidlo', 'Priloha', 'Piti'];
        $categoriesEmojis = ['🍔', '🍟', '💧'];

        $sequence = 0;
        foreach ($categories as $key => $category) {
            $slug     = str_slug($category);
            $sequence += 100;

            Category::firstOrCreate(['slug' => $slug],
                ['title' => $category, 'sequence' => $sequence, 'emoji' => $categoriesEmojis[$key]]);
        }

        $mainCategory = Category::ofSlug('hlavni-jidlo')->first();

        /** @var Product $admiral */
        $admiral = Product::firstOrCreate(['slug' => 'admiral'], [
            'title'       => 'Admiral',
            'sequence'    => 100,
            'heat'        => 100,
            'description' => 'Tortuga signature burger',
            'category_id' => $mainCategory->id,
        ]);

        $admiralVariations = [
            'Jednopatrovy {KLASIK}' => 150,
            'Jednopatrovy {HOT}'    => 160,
            'Dvoupatrovy {KLASIK}'  => 190,
            'Dvoupatrovy {HOT}'     => 200,
        ];

        $this->createProductVariationsForProduct($admiralVariations, $admiral);

        $bucaneer = Product::firstOrCreate(['slug' => 'bucaneer'], [
            'title'       => 'Bucaneer',
            'sequence'    => 200,
            'heat'        => 150,
            'description' => 'Tortuga VIP burger',
            'category_id' => $mainCategory->id,
        ]);

        $bucaneerVariations = [
            'Klasicka naloz {KLASIK}' => 145,
            'Klasicka naloz {HOT}'    => 155,
            'S vejcem {KLASIK}'       => 165,
            'S vejcem {HOT}'          => 275,
        ];

        $this->createProductVariationsForProduct($bucaneerVariations, $bucaneer);

        $havana = Product::firstOrCreate(['slug' => 'havana'], [
            'title'       => 'Havana',
            'sequence'    => 300,
            'heat'        => 100,
            'description' => 'Tortuga masakr sendvic',
            'category_id' => $mainCategory->id,
        ]);

        $havanaVariations = [
            'Jednom jedna varianta' => 200,
        ];

        $this->createProductVariationsForProduct($havanaVariations, $havana);

        $sidesCategory = Category::ofSlug('priloha')->first();

        /** @var Product $potatoes */
        $potatoes = Product::firstOrCreate(['slug' => 'grilovane-bramburky'], [
            'title'       => 'Grilovane bramburky',
            'sequence'    => 100,
            'heat'        => 50,
            'description' => 'Bramburky ve slupce s cesnekovym dipem',
            'category_id' => $sidesCategory->id,
        ]);

        $potatoesVariations = [
            'Jenom jedna varianta' => 45,
        ];

        $this->createProductVariationsForProduct($potatoesVariations, $potatoes);

        $drinksCategory = Category::ofSlug('piti')->first();

        /** @var Product $potatoes */
        $beers = Product::firstOrCreate(['slug' => 'pilsner-urquell'], [
            'title'       => 'Pilsner Urquell',
            'sequence'    => 100,
            'heat'        => 10,
            'description' => 'Nejlepsi lahvac',
            'category_id' => $drinksCategory->id,
        ]);

        $beersVariations = [
            'Jedno pivko' => 45,
        ];

        $this->createProductVariationsForProduct($beersVariations, $beers);
    }

    /**
     * @param array   $productVariations
     * @param Product $product
     */
    private function createProductVariationsForProduct($productVariations, $product)
    {
        $previousVariation = null;
        $sequence          = 0;
        foreach ($productVariations as $variation => $price) {
            $titleWithoutBraces = str_replace(['{', '}'], '', $variation);
            $slug               = str_slug($titleWithoutBraces);
            $sequence           += 100;

            $previousVariation = $product->variations()->firstOrCreate(['slug' => $slug], [
                'parent_id' => $sequence % 200 ? null : $previousVariation->id,
                'active'    => true,
                'sequence'  => $sequence,
                'title'     => $variation,
                'price'     => $price * 100,
            ]);
        }
    }
}
