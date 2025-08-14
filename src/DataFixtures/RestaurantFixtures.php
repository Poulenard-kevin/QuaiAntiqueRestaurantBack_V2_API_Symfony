<?php

namespace App\DataFixtures;

use App\Entity\Restaurant;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker\Generator;

class RestaurantFixtures extends Fixture
{
    public const RESTAURANT_REFERENCE = 'restaurant';
    public const Restaurant_NB_TUPLES = 20;

    public function __construct(private Generator $faker) {}

    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::Restaurant_NB_TUPLES; $i++) {
            $restaurant = (new Restaurant())
                ->setName($this->faker->company())
                ->setDescription($this->buildRestaurantDescription($this->faker))
                ->setAmOpeningTime([])
                ->setPmOpeningTime([])
                ->setMaxGuest(random_int(10, 50))
                ->setCreatedAt(new DateTimeImmutable());

            $manager->persist($restaurant);
            $this->addReference(self::RESTAURANT_REFERENCE . $i, $restaurant);
        }

        $manager->flush();
    }

    private function buildRestaurantDescription(Generator $faker): string
    {
        $types = ["bistronomique", "traditionnelle", "gastronomique", "de quartier", "méditerranéenne", "fusion", "de saison"];
        $cuisines = ["française", "italienne", "japonaise", "libanaise", "thaïlandaise", "espagnole", "portugaise", "indienne", "marocaine"];
        $ambiances = ["chaleureuse", "conviviale", "cosy", "élégante", "familiale", "décontractée", "romantique", "branchée"];
        $plats = ["risotto aux champignons", "tartare de bœuf", "magret de canard", "dorade rôtie", "ramen maison", "tagliatelles aux truffes", "curry de légumes", "tajine d'agneau", "paella"];
        $atouts = ["produits locaux", "circuits courts", "carte courte et de saison", "cave sélectionnée", "pâtisseries maison", "options végétariennes", "plats sans gluten", "menu du marché"];
        $services = ["sur place", "à emporter", "click & collect", "livraison", "terrasse", "privatisation", "accès PMR", "wifi"];

        $type = $faker->randomElement($types);
        $cuisine = $faker->randomElement($cuisines);
        $ambiance = $faker->randomElement($ambiances);
        $plat = $faker->randomElement($plats);
        $atout1 = $faker->randomElement($atouts);
        $atout2 = $faker->randomElement(array_diff($atouts, [$atout1]));
        $serviceList = $faker->randomElements($services, $faker->numberBetween(2, 4));

        $intro = sprintf(
            "Cuisine %s %s dans une ambiance %s au cœur de %s.",
            $cuisine,
            $type,
            $ambiance,
            $faker->city()
        );

        $corps = sprintf(
            "Notre spécialité, %s, est préparée chaque jour par le chef avec des %s. ",
            $plat,
            $atout1
        );

        $plus = sprintf(
            "Nous proposons également %s. ",
            $atout2
        );

        $servicesTxt = "Services: " . implode(', ', $serviceList) . ".";

        return $intro . " " . $corps . $plus . $servicesTxt;
    }
}