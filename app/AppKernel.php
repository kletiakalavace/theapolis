<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * AppKernel
 *
 * The Kernel of the App
 *
 */
class AppKernel extends Kernel
{

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\HttpKernel\KernelInterface::registerBundles()
     */
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Knp\Bundle\SnappyBundle\KnpSnappyBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
            new Sp\BowerBundle\SpBowerBundle(),
            new Fkr\CssURLRewriteBundle\FkrCssURLRewriteBundle(),
            new Braincrafted\Bundle\BootstrapBundle\BraincraftedBootstrapBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new Theaterjobs\MainBundle\TheaterjobsMainBundle(),
            new Theaterjobs\UserBundle\TheaterjobsUserBundle(),
            new Theaterjobs\AdminBundle\TheaterjobsAdminBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new A2lix\TranslationFormBundle\A2lixTranslationFormBundle(),
            new JMS\JobQueueBundle\JMSJobQueueBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\TranslationBundle\JMSTranslationBundle(),
            new Theaterjobs\ProfileBundle\TheaterjobsProfileBundle(),
            new Theaterjobs\MembershipBundle\TheaterjobsMembershipBundle(),
            new Theaterjobs\CategoryBundle\TheaterjobsCategoryBundle(),
            new Theaterjobs\StatsBundle\TheaterjobsStatsBundle(),
            new Theaterjobs\MessageBundle\TheaterjobsMessageBundle(),
            new FOS\MessageBundle\FOSMessageBundle(),
            new Snc\RedisBundle\SncRedisBundle(),
            new Theaterjobs\VATBundle\TheaterjobsVATBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new FOS\ElasticaBundle\FOSElasticaBundle(),
            new Theaterjobs\NewsBundle\TheaterjobsNewsBundle(),
            new Theaterjobs\InserateBundle\TheaterjobsInserateBundle(),
            new Vich\UploaderBundle\VichUploaderBundle(),
            new cspoo\Swiftmailer\MailgunBundle\cspooSwiftmailerMailgunBundle(),
            new Knp\Bundle\TimeBundle\KnpTimeBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle(),
            new Gfreeau\Bundle\GetJWTBundle\GfreeauGetJWTBundle(),
            new Gesdinet\JWTRefreshTokenBundle\GesdinetJWTRefreshTokenBundle(),
            new Noxlogic\RateLimitBundle\NoxlogicRateLimitBundle(),
            new Lsw\MemcacheBundle\LswMemcacheBundle(),
            new Ambta\DoctrineEncryptBundle\AmbtaDoctrineEncryptBundle(),
            new Cron\CronBundle\CronCronBundle(),
            new Sonata\SeoBundle\SonataSeoBundle(),
            new Symfony\Cmf\Bundle\SeoBundle\CmfSeoBundle(),
            new Theaterjobs\FileSystemBundle\TheaterjobsFileSystemBundle(),
            new Enqueue\Bundle\EnqueueBundle(),
            new Enqueue\ElasticaBundle\EnqueueElasticaBundle(),
            new JMS\I18nRoutingBundle\JMSI18nRoutingBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'])) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
        }

        return $bundles;
    }

    /**
     * @param LoaderInterface $loader
     * @throws Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yml');
    }

    /**
     * AppKernel constructor.
     * @param $environment
     * @param $debug
     */
    public function __construct($environment, $debug)
    {
        date_default_timezone_set('Europe/Berlin');
        parent::__construct($environment, $debug);
    }

    /**
     * @return bool|string
     */
    public function getProjectDir()
    {
        return realpath(__DIR__ . '/../');
    }

    /**
     * @return bool|string
     */
    public function getWebDir()
    {
        return realpath(__DIR__ . '/../web/');
    }

    /**
     * @return bool|string
     */
    public function getUploadDir()
    {
        return realpath(__DIR__ . '/../uploads/');
    }

    public function getCacheDir()
    {
        // better performance while running in vm
        if (in_array($this->getEnvironment(), ['dev', 'test'])) {
            return '/tmp/symfony/cache/' . $this->environment;
        }

        return parent::getCacheDir();
    }

    public function getLogDir()
    {
        // better performance while running in vm
        if (in_array($this->getEnvironment(), ['dev', 'test'])) {
            return '/tmp/symfony/cache/' . $this->environment;
        }

        return parent::getLogDir();
    }
}
