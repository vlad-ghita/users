<?php

declare(strict_types=1);

namespace Bolt\UsersExtension\Exclude;

use Bolt\Configuration\Config;
use Bolt\Configuration\Content\ContentType;
use Bolt\Controller\Frontend\DetailController;
use Bolt\UsersExtension\Controller\AccessAwareController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProtectedDetailController extends AccessAwareController
{
    /** @var DetailController */
    private $detailController;

    /** @var Config */
    private $config;

    public function __construct(DetailController $detailController, Config $config)
    {
        $this->detailController = $detailController;
        $this->config = $config;
    }

    /**
     * @Route(
     *     "/{contentTypeSlug}/{slugOrId}",
     *     name="record",
     *     requirements={"contentTypeSlug"="%bolt.requirement.contenttypes%"},
     *     methods={"GET|POST"})
     * @Route(
     *     "/{_locale}/{contentTypeSlug}/{slugOrId}",
     *     name="record_locale",
     *     requirements={"contentTypeSlug"="%bolt.requirement.contenttypes%", "_locale": "%app_locales%"},
     *     methods={"GET|POST"})
     *
     * @param string|int $slugOrId
     */
    public function record($slugOrId, ?string $contentTypeSlug = null, bool $requirePublished = true): Response
    {
        $contentType = ContentType::factory($contentTypeSlug, $this->config->get('contenttypes'));

        if ($contentType->contains('allow_for_groups')) {
            $this->denyAccessUnlessGranted($contentType->get('allow_for_groups'));
        }

        return $this->detailController->record($slugOrId, $contentTypeSlug, $requirePublished);
    }
}
