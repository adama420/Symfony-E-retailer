<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    /**
     * @var SluggerInterface
     */
    private $slugger;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(SluggerInterface $slugger,
                                UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->slugger = $slugger;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        //on génére les USERS
        $users = [];
        for($i = 1; $i <= 10; $i++){

            $email =(1 === $i) ? 'adambroquet@orange.fr': $faker->email;
            $roles =(1 === $i) ? ['ROLE_ADMIN'] : ['ROLE_USER'];

            $user = new User();
            $user->setUsername($email);
            $user->setPassword(
                $this->passwordEncoder->encodePassword($user, 'test')
            );
            $user->setRoles($roles);
            $manager->persist($user);
            $users[]= $user;
        }

        //on génére les CATEGORIES

        $categorys = [];
        for($i = 1; $i <= 5; $i++){
            $category = new Category();
            $category->setName($faker->word);
            $manager->persist($category);
            $categorys[] = $category;
        }

        //on génére les PRODUCTS
        for($i = 1; $i<=100; $i++){
            $product = new Product();
            $product->setName($faker->firstNameMale);
            $product->setDescription($faker->sentence(9,false));
            $product->setPrice($faker->randomDigitNotNull);
            $product->setSlug($this->slugger->slug($product->getName())->lower());
            $product->setFavorited($faker->boolean);
            $product->setCreatedAt($faker->dateTime($max = 'now', $timezone = null));
            $product->setColors($faker->randomElements($array = array ('red','green','blue','orange','yellow'), $count = 2));
            $product->setDiscount(rand(1,9)*10);
            $product->setUser($users[rand(0, 9)]);
            $product->setCategory($categorys[rand(0,4)]);
            $manager->persist($product);
        }

        $manager->flush();
    }


}
