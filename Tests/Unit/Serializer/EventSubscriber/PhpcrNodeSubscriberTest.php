<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\ResourceRestBundle\Tests\Serializer\EventSubscriber;

use Prophecy\PhpUnit\ProphecyTestCase;
use Symfony\Cmf\Bundle\ResourceRestBundle\Serializer\EventSubscriber\PhpcrNodeSubscriber;

class PhpcrNodeSubscriberTest extends ProphecyTestCase
{
    private $node;
    private $event;
    private $subscriber;

    public function setUp()
    {
        parent::setUp();

        $this->node = $this->prophesize('PHPCR\NodeInterface');
        $this->event = $this->prophesize('JMS\Serializer\EventDispatcher\PreSerializeEvent');
        $this->subscriber = new PhpcrNodeSubscriber();
    }

    public function testPreSerialize()
    {
        $this->event->getObject()->willReturn($this->node->reveal());
        $this->event->setType('PHPCR\NodeInterface')->shouldBeCalled();
        $this->subscriber->onPreSerialize($this->event->reveal());
    }
}
