<?php namespace Datlv\Enum\Tests\Stubs;
/**
 * Class IntergrationTestCase
 * @package Datlv\Enum\Tests\Stubs
 * @author Minh Bang
 */
class IntergrationTestCase extends \Datlv\Kit\Testing\IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--realpath' => realpath(__DIR__ . '/../migrations'),
        ]);
        app('enum')
            ->register(Model1::class, [
                'security' => ['title' => 'Security', 'attr' => 'security_id'],
                'author' => ['title' => 'Author', 'attr' => ['author1_id', 'author2_id']],
                'multi' => ['title' => 'Multi values'],
            ])
            ->register(Model2::class, ['place' => ['title' => 'Place', 'attr' => 'place_id']])
            ->shared(Model2::class, Model1::class, ['author' => 'author_id', 'security', 'multi' => []]);
    }
    /**
     * @param  \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return array_merge(
            parent::getPackageProviders($app),
            [
                \Datlv\Enum\ServiceProvider::class,
            ]
        );
    }
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app->bind('path.public', function () {
            return __DIR__ . '/public';
        });
    }
}