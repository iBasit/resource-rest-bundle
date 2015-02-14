<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\ResourceRestBundle\Serializer\EventSubscriber;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use PHPCR\NodeInterface;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use Puli\Repository\Api\ResourceCollection;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Puli\Repository\Api\Resource\Resource;
use Symfony\Cmf\Component\Resource\RepositoryRegistryInterface;
use Symfony\Cmf\Bundle\ResourceRestBundle\Registry\PayloadAliasRegistry;
use Symfony\Cmf\Bundle\ResourceRestBundle\Registry\EnhancerRegistry;

/**
 * Force instaces of ResourceCollection to type "ResourceCollection"
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ResourceSubscriber implements EventSubscriberInterface
{
    private $registry;
    private $payloadAliasRegistry;
    private $enhancerRegistry;

    public function __construct(
        RepositoryRegistryInterface $registry,
        PayloadAliasRegistry $payloadAliasRegistry,
        EnhancerRegistry $enhancerRegistry
    ) {
        $this->registry = $registry;
        $this->payloadAliasRegistry = $payloadAliasRegistry;
        $this->enhancerRegistry = $enhancerRegistry;
    }

    public static function getSubscribedEvents()
    {
        return array(
            array(
                'event' => Events::POST_SERIALIZE,
                'method' => 'onPostSerialize',
            ),
            array(
                'event' => Events::PRE_SERIALIZE,
                'method' => 'onPreSerialize',
            ),
        );
    }

    /**
     * @param PreSerializeEvent $event
     */
    public function onPreSerialize(PreSerializeEvent $event)
    {
        $object = $event->getObject();

        if ($object instanceof Resource) {
            $event->setType('Puli\Repository\Api\Resource\Resource');
        }
    }

    /**
     * @param PreSerializeEvent $event
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        $object = $event->getObject();

        if (!$object instanceof Resource) {
            return;
        }

        $visitor = $event->getVisitor();
        $context = $event->getContext();
        $repositoryAlias = $this->registry->getRepositoryAlias($object->getRepository());

        $visitor->addData('repository_alias', $repositoryAlias);
        $visitor->addData('repository_type', $this->registry->getRepositoryType($object->getRepository()));
        $visitor->addData('payload_alias', $this->payloadAliasRegistry->getPayloadAlias($object));
        $visitor->addData('payload_type', $object->getPayloadType());
        $visitor->addData('path', $object->getPath());
        $visitor->addData('repository_path', $object->getRepositoryPath());
        $visitor->addData('children', $context->accept($object->listChildren()));

        $enhancers = $this->enhancerRegistry->getEnhancers($repositoryAlias);

        foreach ($enhancers as $enhancer) {
            $enhancer->enhance($context, $object);
        }
    }
}