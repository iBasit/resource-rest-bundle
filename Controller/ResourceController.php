<?php

namespace Symfony\Cmf\Bundle\ResourceRestBundle\Controller;

use Symfony\Cmf\Component\Resource\RepositoryRegistryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Puli\Repository\Api\ResourceNotFoundException;

class ResourceController
{
    /**
     * @var RepositoryRegistryInterface
     */
    private $registry;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param RepositoryInterface
     */
    public function __construct(
        SerializerInterface $serializer,
        RepositoryRegistryInterface $registry
    ) {
        $this->serializer = $serializer;
        $this->registry = $registry;
    }

    public function resourceAction($repositoryName, $path)
    {
        try {
            $repository = $this->registry->get($repositoryName);
            $resource = $repository->get('/' . $path);

            $context = SerializationContext::create();
            $context->enableMaxDepthChecks();
            $context->setSerializeNull(true);
            $json = $this->serializer->serialize(
                $resource,
                'json',
                $context
            );

            $response = new Response($json);
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } catch (ResourceNotFoundException $e) {
            throw new NotFoundHttpException(
                sprintf('No resource found at path "%s" for repository "%s"', $path, $repositoryName),
                $e
            );
        }
    }
}
