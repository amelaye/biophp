<?php
namespace Tests\DependencyInjection;

use Amelaye\BioPHP\Api\AminoApi;
use Amelaye\BioPHP\DependencyInjection\AmelayeBioPHPExtension;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\DoctrineExtension;
use JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Boots a real Symfony DI container with AmelayeBioPHPExtension registered
 * alongside the bundles a real host application would provide (Doctrine,
 * JMS Serializer). Unlike the rest of the suite, which mocks the Guzzle
 * client and EntityManager directly, this exercises the actual bundle
 * wiring (services.xml, Doctrine attribute mapping) end to end - the only
 * thing that would have caught the getAlias() Symfony 6 incompatibility.
 */
class AmelayeBioPHPExtensionTest extends TestCase
{
    public function testContainerCompilesAndServicesAreWired(): void
    {
        $container = $this->buildContainer();

        static::assertTrue($container->has('amelaye_biophp.api_amino'));
        static::assertTrue($container->has('amelaye_biophp.guzzle.client.bioapi'));
        static::assertTrue($container->has(\Amelaye\BioPHP\Api\Interfaces\AminoApiAdapter::class));

        $aminoApi = $container->get('amelaye_biophp.api_amino');
        static::assertInstanceOf(AminoApi::class, $aminoApi);
    }

    public function testDoctrineEntityMetadataLoadsThroughTheRealBridge(): void
    {
        $container = $this->buildContainer();

        $em = $container->get('doctrine.orm.default_entity_manager');
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        static::assertCount(11, $metadata);
    }

    private function buildContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', false);
        $container->setParameter('kernel.project_dir', dirname(__DIR__, 2));
        $container->setParameter('kernel.cache_dir', sys_get_temp_dir());
        $container->setParameter('kernel.build_dir', sys_get_temp_dir());
        $container->setParameter('kernel.environment', 'test');
        $container->setParameter('kernel.bundles', []);
        $container->setParameter('kernel.bundles_metadata', []);
        $container->setParameter('kernel.charset', 'UTF-8');
        $container->setParameter('kernel.container_class', 'BioPHPTestContainer');

        $container->registerExtension(new DoctrineExtension());
        $container->registerExtension(new JMSSerializerExtension());
        $container->registerExtension(new AmelayeBioPHPExtension());

        // A real host app activates each bundle via its own (possibly
        // empty) config file, e.g. config/packages/amelaye_biophp.yaml
        // containing "amelaye_biophp: ~". Without at least one queued
        // config entry for an extension's own alias, Symfony's
        // MergeExtensionConfigurationPass treats it as "not configured"
        // and never calls its load() method at all.
        $container->loadFromExtension('doctrine', [
            'dbal' => [
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ],
        ]);
        $container->loadFromExtension('jms_serializer', []);
        $container->loadFromExtension('amelaye_biophp', []);

        // Everything in our services.xml is <defaults public="false">, so
        // the removal pass would prune anything this test doesn't itself
        // reference. Keep what we need to assert on public just before
        // that pass runs.
        $idsToKeep = [
            'amelaye_biophp.api_amino',
            'amelaye_biophp.guzzle.client.bioapi',
            \Amelaye\BioPHP\Api\Interfaces\AminoApiAdapter::class,
        ];
        $container->addCompilerPass(new class ($idsToKeep) implements CompilerPassInterface {
            private $ids;

            public function __construct(array $ids)
            {
                $this->ids = $ids;
            }

            public function process(ContainerBuilder $container): void
            {
                foreach ($this->ids as $id) {
                    if ($container->hasDefinition($id)) {
                        $container->getDefinition($id)->setPublic(true);
                    } elseif ($container->hasAlias($id)) {
                        $container->getAlias($id)->setPublic(true);
                    }
                }
            }
        }, PassConfig::TYPE_BEFORE_REMOVING);

        $container->compile();

        return $container;
    }
}
